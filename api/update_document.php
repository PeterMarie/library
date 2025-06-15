<?php
require_once("../inc/db.php");
header("Content-Type: application/json");

$response = ["success" => false];

// Only accept multipart/form-data
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
   // exit;
}

if (!isset($_POST["id"])) {
    echo json_encode(["success" => false, "message" => "Document ID is required"]);
   // exit;
}

$documentId = intval($_POST["id"]);

$title = trim($_POST["title"] ?? "");
$publisher = trim($_POST["publisher"] ?? "");
$publication_year = intval($_POST["publication_year"] ?? 0);
$page_count = intval($_POST["page_count"] ?? 0);
$language_id = intval($_POST["language_id"] ?? 0);
$format_id = intval($_POST["format_id"] ?? 0);
//$status_id = intval($_POST["status_id"] ?? 0);
$subcategory_id = intval($_POST["subcategory_id"] ?? 0);
$summary = trim($_POST["summary"] ?? "");
$file_path = trim($_POST["file_path"] ?? "");
$fileId = trim($_POST["fileId"] ?? "");
$cover_image = trim($_POST["cover_image"] ?? "covers/default.png"); // fallback

// Handle file upload if provided
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
        $cover_image = str_replace("../", "", $destination); // Store relative path
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save uploaded image."]);
        exit;
    }
}

$updateQuery = "
    UPDATE documents 
    SET title = ?, publisher = ?, publication_year = ?, page_count = ?, 
        language_id = ?, format_id = ?, subcategory_id = ?, 
        summary = ?, file_path = ?, fileId = ?, cover_image = ?
    WHERE id = ?
";

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param(
    "ssiiiiissssi",
    $title, $publisher, $publication_year, $page_count,
    $language_id, $format_id, $subcategory_id,
    $summary, $file_path, $fileId, $cover_image, $documentId
);

if ($stmt->execute()) {
    if($stmt->affected_rows > 0){
        $response["success"] = true;
        $response["message"] = "Book updated successfully!";
    } else {
        $response["message"] = "Update failed: Book not found";
    }
} else {
    $response["message"] = "Update failed: " . $stmt->error;
}

$stmt->close();
echo json_encode($response);
?>
