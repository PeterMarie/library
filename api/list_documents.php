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

$page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
$page_size = isset($_POST['page_size']) ? max(1, intval($_POST['page_size'])) : 15;
$offset = ($page - 1) * $page_size;

$filterQuery = "
    FROM documents d
    JOIN subcategories s ON d.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    JOIN languages l ON d.language_id = l.id
    JOIN formats f ON d.format_id = f.id
    JOIN reading_status r ON d.status_id = r.id
    WHERE 1=1
";

$params = [];
$types = "";

// Filters
if (!empty($_POST["category_id"])) {
    $filterQuery .= " AND c.id = ?";
    $types .= "i";
    $params[] = intval($_POST["category_id"]);
}
if (!empty($_POST["subcategory_id"])) {
    $filterQuery .= " AND s.id = ?";
    $types .= "i";
    $params[] = intval($_POST["subcategory_id"]);
}
if (!empty($_POST["language_id"])) {
    $filterQuery .= " AND l.id = ?";
    $types .= "i";
    $params[] = intval($_POST["language_id"]);
}
if (!empty($_POST["format_id"])) {
    $filterQuery .= " AND f.id = ?";
    $types .= "i";
    $params[] = intval($_POST["format_id"]);
}
if (!empty($_POST["status_id"])) {
    $filterQuery .= " AND r.id = ?";
    $types .= "i";
    $params[] = intval($_POST["status_id"]);
}
if (!empty($_POST["min_year"])) {
    $filterQuery .= " AND d.publication_year >= ?";
    $types .= "i";
    $params[] = intval($_POST["min_year"]);
}

$search = !empty($_POST["search"]) ? trim($_POST["search"]) : "";

// Basic search in title and summary
if ($search !== "") {
    $filterQuery .= " AND (d.title LIKE CONCAT('%', ?, '%') OR d.summary LIKE CONCAT('%', ?, '%'))";
    $types .= "ss";
    $params[] = $search;
    $params[] = $search;
}

$countQuery = "SELECT COUNT(DISTINCT d.id) " . $filterQuery;
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_documents);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_documents / $page_size);

$mainQuery = "
    SELECT DISTINCT d.id, d.title, d.subtitle, d.series_title, d.publisher, d.publication_year, d.page_count, 
        d.file_path, d.summary, d.cover_image, d.fileId, 
        l.name AS language, f.type AS format, r.status AS reading_status,
        s.name AS subcategory, c.name AS category,
        c.id AS category_id, s.id AS subcategory_id, l.id AS language_id, f.id AS format_id, r.id AS status_id
    " . $filterQuery . " 
    LIMIT ? OFFSET ?
";

$mainTypes = $types . "ii";
$mainParams = array_merge($params, [$page_size, $offset]);

$stmt = $conn->prepare($mainQuery);
$stmt->bind_param($mainTypes, ...$mainParams);
$stmt->execute();
$result = $stmt->get_result();

$documents = [];
$documentIds = [];

while ($row = $result->fetch_assoc()) {
    $row["authors"] = getAuthors($conn, $row["id"]);
    $row["keywords"] = getKeywords($conn, $row["id"]);
    addCoverImages($row);
    $documents[] = $row;
    $documentIds[] = $row["id"];
}
$stmt->close();

// Extra search by author name and keyword
if ($search !== "") {
    $extraMatches = [];

    // --- Search by author name ---
    $authorSearchQuery = "
        SELECT DISTINCT d.id
        FROM documents d
        JOIN document_authors da ON d.id = da.document_id
        JOIN authors a ON da.author_id = a.id
        WHERE a.name LIKE CONCAT('%', ?, '%')
    ";
    $stmt = $conn->prepare($authorSearchQuery);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row["id"], $documentIds)) {
            $extraMatches[] = $row["id"];
        }
    }
    $stmt->close();

    // --- Search by keyword ---
    $keywordSearchQuery = "
        SELECT DISTINCT d.id
        FROM documents d
        JOIN document_keywords dk ON d.id = dk.document_id
        JOIN keywords k ON dk.keyword_id = k.id
        WHERE k.name LIKE CONCAT('%', ?, '%')
    ";
    $stmt = $conn->prepare($keywordSearchQuery);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row["id"], $documentIds)) {
            $extraMatches[] = $row["id"];
        }
    }
    $stmt->close();

    // Fetch documents from extraMatches
    foreach (array_unique($extraMatches) as $docId) {
        $docQuery = "
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
            WHERE d.id = ?
        ";

        $docStmt = $conn->prepare($docQuery);
        $docStmt->bind_param("i", $docId);
        $docStmt->execute();
        $docResult = $docStmt->get_result();

        if ($doc = $docResult->fetch_assoc()) {
            $match = true;
            if (!empty($_POST["category_id"]) && intval($_POST["category_id"]) !== intval($doc["category_id"])) $match = false;
            if (!empty($_POST["subcategory_id"]) && intval($_POST["subcategory_id"]) !== intval($doc["subcategory_id"])) $match = false;
            if (!empty($_POST["language_id"]) && intval($_POST["language_id"]) !== intval($doc["language_id"])) $match = false;
            if (!empty($_POST["format_id"]) && intval($_POST["format_id"]) !== intval($doc["format_id"])) $match = false;
            if (!empty($_POST["status_id"]) && intval($_POST["status_id"]) !== intval($doc["status_id"])) $match = false;
            if (!empty($_POST["min_year"]) && intval($doc["publication_year"]) < intval($_POST["min_year"])) $match = false;

            if ($match) {
                $doc["authors"] = getAuthors($conn, $doc["id"]);
                $doc["keywords"] = getKeywords($conn, $doc["id"]);
                addCoverImages($doc);
                $documents[] = $doc;
            }
        }
        $docStmt->close();
    }

    $total_documents = count($documents);
    $total_pages = ceil($total_documents / $page_size);
    $documents = array_slice($documents, $offset, $page_size);
}

echo json_encode([
    "documents" => $documents,
    "totalPages" => $total_pages,
    "current_page" => $page
]);
?>
