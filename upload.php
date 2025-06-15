<?php
require_once("inc/db.php");

// Sanitize input
$title = $conn->real_escape_string($_POST["title"]);
$subtitle = $conn->real_escape_string($_POST["subtitle"]);
$subcategory_id = intval($_POST["subcategory_id"]);
$authors = $_POST["authors"] ?? [];
$publisher = $conn->real_escape_string($_POST["publisher"]);
$publication_year = intval($_POST["publication_year"]);
$edition = $conn->real_escape_string($_POST["edition"]);
$isbn = $conn->real_escape_string($_POST["isbn"]);
$language_id = intval($_POST["language_id"]);
$page_count = intval($_POST["page_count"]);

$status_id = (!empty($_POST["status_id"]) && $_POST["status_id"] != 0) ? intval($_POST["status_id"]) : 1;

$summary = $conn->real_escape_string($_POST["summary"]);
$keywords = $_POST["keywords"] ?? [];

// Upload Document File
$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$allowed_types = [
    'application/pdf',
    'application/epub+zip',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain'
];

$tmp_file = $_FILES["document_file"]["tmp_name"];
$original_filename = basename($_FILES["document_file"]["name"]);
$file_type = mime_content_type($tmp_file);

if (!in_array($file_type, $allowed_types)) {
    error_log("Disallowed file type: $file_type");
    die(json_encode(["error" => "Invalid file type."]));
} else {
    switch ($file_type) {
        case 'application/pdf': $format_id = 1; break;
        case 'application/epub+zip': $format_id = 2; break;
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': $format_id = 4; break;
        case 'text/plain': $format_id = 5; break;
    }
}
$fileId = uniqid("doc_");
$filename = $fileId . "_" . preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $title);
$target_file = $upload_dir . $filename;

if (!move_uploaded_file($tmp_file, $target_file)) {
    error_log("Failed to move uploaded file.");
    die(json_encode(["error" => "Failed to upload file."]));
}

// Upload Cover Image
$cover_dir = "covers/";
if (!file_exists($cover_dir)) {
    mkdir($cover_dir, 0777, true);
}

$cover_image_path = 'covers/default.png';

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $cover_tmp = $_FILES['cover_image']['tmp_name'];
    $cover_type = mime_content_type($cover_tmp);
    $valid_image_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (in_array($cover_type, $valid_image_types)) {
        $cover_name = uniqid("cover_") . "_" . preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $_FILES['cover_image']['name']);
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

// Insert into documents table
$insertDoc = $conn->prepare("
    INSERT INTO documents 
    (title, subtitle, subcategory_id, publisher, publication_year, edition, isbn, language_id, page_count, format_id, status_id, fileId, file_path, summary, cover_image)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insertDoc->bind_param(
    "ssisissiiiissss",
    $title,
    $subtitle,
    $subcategory_id,
    $publisher,
    $publication_year,
    $edition,
    $isbn,
    $language_id,
    $page_count,
    $format_id,
    $status_id,
    $fileId
    $target_file,
    $summary,
    $cover_image_path
);

if (!$insertDoc->execute()) {
    error_log("Document insert failed: " . $insertDoc->error);
    die(json_encode(["error" => "Failed to save document."]));
}

$document_id = $insertDoc->insert_id;
$insertDoc->close();

// Handle Authors
$authorStmt = $conn->prepare("SELECT id FROM authors WHERE name = ?");
$insertAuthorStmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
$linkAuthorStmt = $conn->prepare("INSERT INTO document_authors (document_id, author_id) VALUES (?, ?)");

foreach ($authors as $authorName) {
    $authorName = trim($authorName);
    if ($authorName === "") continue;

    $authorStmt->bind_param("s", $authorName);
    $authorStmt->execute();
    $authorResult = $authorStmt->get_result();

    if ($authorResult->num_rows > 0) {
        $author_id = $authorResult->fetch_assoc()["id"];
    } else {
        $insertAuthorStmt->bind_param("s", $authorName);
        $insertAuthorStmt->execute();
        $author_id = $insertAuthorStmt->insert_id;
    }

    $linkAuthorStmt->bind_param("ii", $document_id, $author_id);
    $linkAuthorStmt->execute();
}

// Handle Keywords
$keywordStmt = $conn->prepare("SELECT id FROM keywords WHERE name = ?");
$insertKeywordStmt = $conn->prepare("INSERT INTO keywords (name) VALUES (?)");
$linkKeywordStmt = $conn->prepare("INSERT INTO document_keywords (document_id, keyword_id) VALUES (?, ?)");

foreach ($keywords as $keyword) {
    $keyword = trim($keyword);
    if ($keyword === "") continue;

    $keywordStmt->bind_param("s", $keyword);
    $keywordStmt->execute();
    $keywordResult = $keywordStmt->get_result();

    if ($keywordResult->num_rows > 0) {
        $keyword_id = $keywordResult->fetch_assoc()["id"];
    } else {
        $insertKeywordStmt->bind_param("s", $keyword);
        $insertKeywordStmt->execute();
        $keyword_id = $insertKeywordStmt->insert_id;
    }

    $linkKeywordStmt->bind_param("ii", $document_id, $keyword_id);
    $linkKeywordStmt->execute();
}

echo json_encode(["success" => true, "message" => "Document uploaded and saved successfully!"]);
?>
