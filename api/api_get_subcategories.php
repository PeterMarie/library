<?php
    require_once("../inc/db.php");

    $category_id = intval($_GET['category_id']);
    $res = $conn->query("SELECT id, name FROM subcategories WHERE category_id = $category_id ORDER BY name");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
?>