<?php
require_once("../inc/db.php");
header("Content-Type: application/json");

$response = ["success" => false];

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"])) {
    echo json_encode(["success" => false, "message" => "Document ID is required"]);
    exit;
}

$documentId = intval($data["id"]);

// Delete from pivot table first
$deleteAuthorsQuery = "DELETE FROM document_authors WHERE document_id = ?";
$authorStmt = $conn->prepare($deleteAuthorsQuery);
$authorStmt->bind_param("i", $documentId);
$authorStmt->execute();
$authorStmt->close();

// Then delete the document
$deleteDocQuery = "DELETE FROM documents WHERE id = ?";
$docStmt = $conn->prepare($deleteDocQuery);
$docStmt->bind_param("i", $documentId);

if ($docStmt->execute()) {
    $response["success"] = true;
} else {
    $response["message"] = "Delete failed: " . $docStmt->error;
}

$docStmt->close();
echo json_encode($response);
