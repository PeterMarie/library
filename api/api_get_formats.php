<?php
    require_once("../inc/db.php");
    
    $res = $conn->query("SELECT id, type AS name FROM formats ORDER BY type");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));

?>