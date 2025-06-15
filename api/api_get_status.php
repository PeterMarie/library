<?php
    require_once("../inc/db.php");

    $res = $conn->query("SELECT id, status AS name FROM reading_status ORDER BY status");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));

?>