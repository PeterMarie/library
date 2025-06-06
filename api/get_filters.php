<?php
    require_once("../inc/db.php");

    header("Content-Type: application/json");

    $data = [];

    function fetchTable($conn, $table, $orderBy = "name") {
        $result = $conn->query("SELECT id, $orderBy as name FROM $table ORDER BY $orderBy ASC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    // Categories and subcategories
    $categories = $conn->query("SELECT c.id, c.name, s.id AS sub_id, s.name AS sub_name 
                                FROM categories c 
                                JOIN subcategories s ON c.id = s.category_id 
                                ORDER BY c.name, s.name");

    $catMap = [];
    while ($row = $categories->fetch_assoc()) {
        $cat_id = $row['id'];
        if (!isset($catMap[$cat_id])) {
            $catMap[$cat_id] = [
                "id" => $cat_id,
                "name" => $row['name'],
                "subcategories" => []
            ];
        }
        $catMap[$cat_id]["subcategories"][] = [
            "id" => $row["sub_id"],
            "name" => $row["sub_name"]
        ];
    }

    $data["categories"] = array_values($catMap);
    $data["languages"] = fetchTable($conn, "languages");
    $data["formats"] = fetchTable($conn, "formats", "type");
    $data["reading_status"] = fetchTable($conn, "reading_status", "status");

    echo json_encode($data);
?>
