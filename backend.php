<?php
    require_once("inc/db.php");

    if(isset($_POST)){
        $return = array();
        //$book = new Book($connection);

        switch ($_POST['task']) {
            case 'search':
                # code to access database
                $return['status'] = "success";
                $return['key'] = $_POST['key'];
                break;
            
            default:
                # code...
                break;
        }

        echo json_encode($return);
    }

    class Book {
        private $conn;
        private $book_id;

        public function __construct($db){
            $this->conn = $db;
        }

        public function search_database(){
            $result_array = array();
            $result_array['categories'] = array();
            $result_array['books'] = array();
            $result_array['tags'] = array();
            $result_array['authors'] = array();

            $key = "%" . $return['key'] . "%";
            
            #query to search categories
            $count = 0;
            $query = "SELECT * FROM categories WHERE name LIKE ? LIMIT 5 ORDER BY name";
            $search = $this->conn->prepare($query);
            $search->bind_param("s", $key);
            $search->execute();
            $result = $search->get_result();
            while($search_result = $result->fetch_assoc()){
                $count++;
                $category = array();
                $category['name'] = $search_result['name'];
                $result_array['categories'][$count] = $category;
            }

            #query to search book titles or subtitles
            $count = 0;
            $query = "SELECT * FROM books WHERE title LIKE ? OR subtitle LIKE ? LIMIT 5 ORDER BY title ";
            $search = $this->conn->prepare($query);
            $search->bind_param("ss", $key, $key);
            $search->execute();
            $result = $search->get_result();
            while($search_result = $result->fetch_assoc()){
                $count++;
                $book = array();
                $book['title'] = $search_result['title'];
                $result_array['books'][$count] = $book;
            }
            #query to search book tags
            #query to search authors

            return $result;
        }
    }
?>