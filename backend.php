<?php
    require_once("inc/db.php");

    if(isset($_POST)){
        $return = array();
        $book = new Book($conn);

        switch ($_POST['task']) {
            case 'search':
                $return = $book->search_database();
                $return['status'] = "success";
                $return['key'] = $_POST['key'];
                break;

            case 'retrieve':
                $return = $book->retrieve($_POST['data']);
                break;

            case 'add_book':
                $return = $_POST;
                break;

            default:
                $return['status'] = "failed";
                $return['error'] = "Unknown error";
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

            if(empty($_POST['key'])){
                /*
                    If no search query is sent, no calls to the database will be made.
                    The returned object will be similar to that of n unfound query.
                */
            } else {
                $key = "%" . $_POST['key'] . "%";

                #query to search categories
                $count = 0;
                $query = "SELECT * FROM categories WHERE name LIKE ? ORDER BY name LIMIT 5";
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
                $query = "SELECT * FROM books WHERE title LIKE ? OR subtitle LIKE ? ORDER BY title LIMIT 5 ";
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
                $count = 0;
                $query = "SELECT * FROM tags WHERE name LIKE ? ORDER BY name LIMIT 5 ";
                $search = $this->conn->prepare($query);
                $search->bind_param("s", $key);
                $search->execute();
                $result = $search->get_result();
                while($search_result = $result->fetch_assoc()){
                    $count++;
                    $tag = array();
                    $tag['name'] = $search_result['name'];
                    $result_array['tags'][$count] = $tags;
                }

                #query to search authors
                $count = 0;
                $query = "SELECT * FROM authors WHERE name LIKE ? ORDER BY name LIMIT 5 ";
                $search = $this->conn->prepare($query);
                $search->bind_param("s", $key);
                $search->execute();
                $result = $search->get_result();
                while($search_result = $result->fetch_assoc()){
                    $count++;
                    $author = array();
                    $author['name'] = $search_result['name'];
                    $result_array['authors'][$count] = $author;
                }
            }

            return $result_array;
        }

        public function retrieve($data){
            $result_array = array();
            $result_array[$data] = array();

            if(empty($_POST['key'])){
                /*
                    If no search query is sent, no calls to the database will be made.
                    The returned object will be similar to that of n unfound query.
                */
            } else {
                $key = "%" . $_POST['key'] . "%";
            }

            $count = 0;
            $query = "SELECT * FROM {$data} WHERE name LIKE ? ";
            $get_suggestions = $this->conn->prepare($query);
            $get_suggestions->bind_param("s", $key);
            $get_suggestions->execute();
            $result = $get_suggestions->get_result();
            while($suggestions = $result->fetch_assoc()){
                $count++;
                $suggestion = array();
                $suggestion['name'] = $suggestions['name'];
                $result_array[$data][$count] = $suggestion;
            }
            return $result_array;
        }
    }
?>