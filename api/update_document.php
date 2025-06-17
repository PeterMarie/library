<?php
require_once("../inc/db.php");
header("Content-Type: application/json");

$response = ["success" => false];

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

if (!isset($_POST["id"])) {
    echo json_encode(["success" => false, "message" => "Document ID is required"]);
    exit;
}

$documentId = intval($_POST["id"]);

$title = trim($_POST["title"] ?? "");
$subtitle = trim($_POST["subtitle"] ?? "");
$series_title = trim($_POST["series_title"] ?? "N/A");
$publisher = trim($_POST["publisher"] ?? "");
$publication_year = intval($_POST["publication_year"] ?? 0);
$page_count = intval($_POST["page_count"] ?? 0);
$language_id = intval($_POST["language_id"] ?? 0);
$format_id = intval($_POST["format_id"] ?? 0);
$subcategory_id = intval($_POST["subcategory_id"] ?? 0);
$summary = trim($_POST["summary"] ?? "");
$file_path = trim($_POST["file_path"] ?? "");
$fileId = trim($_POST["fileId"] ?? "");
$cover_image = trim($_POST["cover_image"] ?? "covers/default.png");

// Handle cover image upload
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/covers/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['cover_image']['tmp_name'];
    $originalName = basename($_FILES['cover_image']['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $newFileName = uniqid("cover_") . "." . strtolower($ext);
    $destination = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpName, $destination)) {
        $cover_image = str_replace("../", "", $destination);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save uploaded image."]);
        exit;
    }
}

// === Check if document exists ===
$check = $conn->prepare("SELECT id FROM documents WHERE id = ?");
$check->bind_param("i", $documentId);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Update failed: Book not found"]);
    $check->close();
    exit;
}
$check->close();

// === Update document ===
$updateQuery = "
    UPDATE documents 
    SET title = ?, subtitle = ?, series_title = ?, publisher = ?, 
        publication_year = ?, page_count = ?, 
        language_id = ?, format_id = ?, subcategory_id = ?, 
        summary = ?, file_path = ?, fileId = ?, cover_image = ?
    WHERE id = ?
";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param(
    "ssssiiiiissssi",
    $title, $subtitle, $series_title, $publisher, $publication_year, $page_count,
    $language_id, $format_id, $subcategory_id,
    $summary, $file_path, $fileId, $cover_image, $documentId
);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
    exit;
}
$stmt->close();

// === Sync Authors ===
if (isset($_POST["authors"])) {
    $submittedAuthors = array_map("trim", explode(",", $_POST["authors"]));
    $submittedAuthors = array_filter($submittedAuthors);

    $existingAuthors = [];
    $res = $conn->query("SELECT a.name FROM authors a JOIN document_authors da ON a.id = da.author_id WHERE da.document_id = $documentId");
    while ($row = $res->fetch_assoc()) {
        $existingAuthors[] = $row["name"];
    }

    $toAdd = array_diff($submittedAuthors, $existingAuthors);
    $toRemove = array_diff($existingAuthors, $submittedAuthors);

    foreach ($toAdd as $authorName) {
        $authorName = trim($authorName);
        if ($authorName === "") continue;

        $stmt = $conn->prepare("INSERT IGNORE INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $authorName);
        $stmt->execute();
        $stmt->close();

        $result = $conn->query("SELECT id FROM authors WHERE name = '" . $conn->real_escape_string($authorName) . "' LIMIT 1");
        if ($row = $result->fetch_assoc()) {
            $authorId = $row["id"];
            $conn->query("INSERT IGNORE INTO document_authors (document_id, author_id) VALUES ($documentId, $authorId)");
        }
    }

    foreach ($toRemove as $authorName) {
        $authorName = $conn->real_escape_string($authorName);
        $result = $conn->query("SELECT id FROM authors WHERE name = '$authorName' LIMIT 1");
        if ($row = $result->fetch_assoc()) {
            $authorId = $row["id"];
            $conn->query("DELETE FROM document_authors WHERE document_id = $documentId AND author_id = $authorId");
        }
    }
}

// === Sync Keywords ===
if (isset($_POST["keywords"])) {
    $submittedKeywords = array_map("trim", explode(",", $_POST["keywords"]));
    $submittedKeywords = array_filter($submittedKeywords);

    $existingKeywords = [];
    $res = $conn->query("SELECT k.name FROM keywords k JOIN document_keywords dk ON k.id = dk.keyword_id WHERE dk.document_id = $documentId");
    while ($row = $res->fetch_assoc()) {
        $existingKeywords[] = $row["name"];
    }

    $toAdd = array_diff($submittedKeywords, $existingKeywords);
    $toRemove = array_diff($existingKeywords, $submittedKeywords);

    foreach ($toAdd as $keyword) {
        $keyword = trim($keyword);
        if ($keyword === "") continue;

        $stmt = $conn->prepare("INSERT IGNORE INTO keywords (name) VALUES (?)");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $stmt->close();

        $result = $conn->query("SELECT id FROM keywords WHERE name = '" . $conn->real_escape_string($keyword) . "' LIMIT 1");
        if ($row = $result->fetch_assoc()) {
            $keywordId = $row["id"];
            $conn->query("INSERT IGNORE INTO document_keywords (document_id, keyword_id) VALUES ($documentId, $keywordId)");
        }
    }

    foreach ($toRemove as $keyword) {
        $keyword = $conn->real_escape_string($keyword);
        $result = $conn->query("SELECT id FROM keywords WHERE name = '$keyword' LIMIT 1");
        if ($row = $result->fetch_assoc()) {
            $keywordId = $row["id"];
            $conn->query("DELETE FROM document_keywords WHERE document_id = $documentId AND keyword_id = $keywordId");
        }
    }
}

// Final success response
$response["success"] = true;
$response["message"] = "Book updated successfully with authors and keywords.";
echo json_encode($response);
?>
