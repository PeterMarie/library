<?php
require_once("../inc/db.php");
header("Content-Type: application/json");

function getAuthors($conn, $documentId) {
    $authors = [];
    $stmt = $conn->prepare("SELECT a.name FROM document_authors da JOIN authors a ON da.author_id = a.id WHERE da.document_id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $authors[] = $row["name"];
    }
    $stmt->close();
    return empty($authors) ? ["Unknown"] : $authors;
}

function getKeywords($conn, $documentId) {
    $keywords = [];
    $stmt = $conn->prepare("SELECT k.name FROM document_keywords dk JOIN keywords k ON dk.keyword_id = k.id WHERE dk.document_id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $keywords[] = $row["name"];
    }
    $stmt->close();
    return $keywords;
}

function addCoverImages(&$doc) {
    $baseDir = "/covers/";
    $thumbDir = "/thumbnails/";
    if (!empty($doc["cover_image"])) {
        $doc["cover_image_url"] = $baseDir . $doc["cover_image"];
        $doc["thumbnail_url"] = $thumbDir . $doc["cover_image"];
    } else {
        $doc["cover_image_url"] = null;
        $doc["thumbnail_url"] = null;
    }
}

$fileId = isset($_GET["fileId"]) ? trim($_GET["fileId"]) : "";

if ($fileId === "") {
    echo json_encode(["error" => "fileId is required"]);
    exit;
}

$query = "
    SELECT d.id, d.title, d.subtitle, d.series_title, d.publisher, d.publication_year, d.page_count, 
           d.file_path, d.summary, d.cover_image, d.fileId, 
           l.name AS language, f.type AS format, r.status AS reading_status,
           s.name AS subcategory, c.name AS category,
           c.id AS category_id, s.id AS subcategory_id, l.id AS language_id, f.id AS format_id, r.id AS status_id
    FROM documents d
    JOIN subcategories s ON d.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    JOIN languages l ON d.language_id = l.id
    JOIN formats f ON d.format_id = f.id
    JOIN reading_status r ON d.status_id = r.id
    WHERE d.fileId = ?
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($book = $result->fetch_assoc()) {
    $book["authors"] = getAuthors($conn, $book["id"]);
    $book["keywords"] = getKeywords($conn, $book["id"]);
    addCoverImages($book);

    echo json_encode(["success" => true, "book" => $book, "message" => "Book retrieved succesfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Book not found"]);
}

$stmt->close();
$conn->close();
?>
