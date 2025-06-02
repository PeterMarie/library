<?php
    require_once("../inc/db.php");

    $res = $conn->query("SELECT id, name FROM languages ORDER BY name");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));

?>