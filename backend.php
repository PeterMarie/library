<?php
    require_once("inc/db.php");

    if(isset($_POST)){
        $return = array();
        $user = new Users($connection);

        switch ($_POST['task']) {
            case 'search':
                # code to access database

                break;
            
            default:
                # code...
                break;
        }
    }

    class Books {

    }
?>