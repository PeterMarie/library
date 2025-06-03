<?php
    require_once("../inc/db.php");

    $q = '%' . $conn->real_escape_string($_GET['q']) . '%';

    $stmt = $conn->prepare("SELECT id, name FROM publishers WHERE name LIKE ?");
    $stmt->bind_param("s", $q);
    $stmt->execute();

    $result = $stmt->get_result();
    $suggestions = [];

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($suggestions);
?>