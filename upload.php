<?php
require_once("inc/db.php");

// Start a transaction
$conn->begin_transaction();

try {
    // Sanitize and assign input
    $title = $conn->real_escape_string($_POST["title"] ?? '');
    $subtitle = $conn->real_escape_string($_POST["subtitle"] ?? '');
    $series_title = $conn->real_escape_string($_POST["series_title"] ?? '');
    $subcategory_id = intval($_POST["subcategory_id"] ?? 0);
    $publisher = $conn->real_escape_string($_POST["publisher"] ?? '');
    $publication_year = intval($_POST["publication_year"] ?? 0);
    $edition = $conn->real_escape_string($_POST["edition"] ?? '');
    $isbn = $conn->real_escape_string($_POST["isbn"] ?? '');
    $language_id = intval($_POST["language_id"] ?? 0);
    $page_count = intval($_POST["page_count"] ?? 0);
    $status_id = (!empty($_POST["status_id"]) && $_POST["status_id"] != 0) ? intval($_POST["status_id"]) : 1;
    $summary = $conn->real_escape_string($_POST["summary"] ?? '');

    // Collect authors
    $authors = $_POST["authors"] ?? [];
    if (!empty($_POST["author"])) {
        $authors[] = $_POST["author"];
    }

    // Collect keywords
    $keywords = $_POST["keywords"] ?? [];
    if (!empty($_POST["keyword"])) {
        $keywords[] = $_POST["keyword"];
    }

    // === Handle File Upload ===
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    $tmp_file = $_FILES["document_file"]["tmp_name"];
    $original_filename = basename($_FILES["document_file"]["name"]);
    $file_type = mime_content_type($tmp_file);

    $allowed_types = [
        'application/pdf' => 1,
        'application/epub+zip' => 2,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 4,
        'text/plain' => 5
    ];

    if (!array_key_exists($file_type, $allowed_types)) {
        throw new Exception("Invalid file type: $file_type");
    }

    $format_id = $allowed_types[$file_type];
    $fileId = uniqid("doc_");
    $safe_title = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $title);
    $filename = $fileId . "_" . $safe_title;
    $target_file = $upload_dir . $filename;

    if (!move_uploaded_file($tmp_file, $target_file)) {
        throw new Exception("Failed to move uploaded file.");
    }

    // === Handle Cover Image Upload ===
    $cover_dir = "covers/";
    if (!file_exists($cover_dir)) mkdir($cover_dir, 0777, true);

    $cover_image_path = 'covers/default.png';
    if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $cover_tmp = $_FILES['cover_image']['tmp_name'];
        $cover_type = mime_content_type($cover_tmp);
        $valid_image_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (in_array($cover_type, $valid_image_types)) {
            $safe_cover_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $_FILES['cover_image']['name']);
            $cover_name = uniqid("cover_") . "_" . $safe_cover_name;
            $cover_target = $cover_dir . $cover_name;

            if (move_uploaded_file($cover_tmp, $cover_target)) {
                $cover_image_path = $cover_target;
            } else {
                error_log("Failed to move cover image. Using default.");
            }
        } else {
            error_log("Invalid cover image type: $cover_type. Using default.");
        }
    }

    // === Insert into documents table ===
    $insertDoc = $conn->prepare("
        INSERT INTO documents 
        (title, subtitle, series_title, subcategory_id, publisher, publication_year, edition, isbn, language_id, page_count, format_id, status_id, fileId, file_path, summary, cover_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertDoc->bind_param(
        "sssisissiiiissss",
        $title, $subtitle, $series_title, $subcategory_id, $publisher,
        $publication_year, $edition, $isbn, $language_id, $page_count,
        $format_id, $status_id, $fileId, $target_file, $summary, $cover_image_path
    );

    if (!$insertDoc->execute()) {
        throw new Exception("Failed to insert document: " . $insertDoc->error);
    }

    $document_id = $insertDoc->insert_id;
    $insertDoc->close();

    // === Handle Authors ===
    $authorStmt = $conn->prepare("SELECT id FROM authors WHERE name = ?");
    $insertAuthorStmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
    $linkAuthorStmt = $conn->prepare("INSERT INTO document_authors (document_id, author_id) VALUES (?, ?)");

    foreach ($authors as $authorName) {
        $authorName = trim($authorName);
        if ($authorName === "") continue;

        $authorStmt->bind_param("s", $authorName);
        $authorStmt->execute();
        $result = $authorStmt->get_result();

        if ($result->num_rows > 0) {
            $author_id = $result->fetch_assoc()["id"];
        } else {
            $insertAuthorStmt->bind_param("s", $authorName);
            $insertAuthorStmt->execute();
            $author_id = $insertAuthorStmt->insert_id;
        }

        $linkAuthorStmt->bind_param("ii", $document_id, $author_id);
        $linkAuthorStmt->execute();
    }

    // === Handle Keywords ===
    $keywordStmt = $conn->prepare("SELECT id FROM keywords WHERE name = ?");
    $insertKeywordStmt = $conn->prepare("INSERT INTO keywords (name) VALUES (?)");
    $linkKeywordStmt = $conn->prepare("INSERT INTO document_keywords (document_id, keyword_id) VALUES (?, ?)");

    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        if ($keyword === "") continue;

        $keywordStmt->bind_param("s", $keyword);
        $keywordStmt->execute();
        $result = $keywordStmt->get_result();

        if ($result->num_rows > 0) {
            $keyword_id = $result->fetch_assoc()["id"];
        } else {
            $insertKeywordStmt->bind_param("s", $keyword);
            $insertKeywordStmt->execute();
            $keyword_id = $insertKeywordStmt->insert_id;
        }

        $linkKeywordStmt->bind_param("ii", $document_id, $keyword_id);
        $linkKeywordStmt->execute();
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Document uploaded and saved successfully!"]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Upload failed: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
