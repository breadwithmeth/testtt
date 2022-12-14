<?php

class bibliographic_description{
    private $db;
    function __construct($db)
    {
        $this->db = $db;
    }
    function add(){
        try {
            if(!empty($_FILES['image'])){
                $file = $_FILES['image'];
                $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));
                $file_name = time() . "." . $file_ext;
                $fileName = $file['name'];
                $file_path = __DIR__."/".$file_name;
                $temp_name = $_FILES['image']['tmp_name'][0];
                if (move_uploaded_file($temp_name,$file_path)) {
                    echo 12121221;
                }
                
                $_POST['cover'] = $file_name;
                
        
            }else{
                $_POST['cover'] = "DEFAULT";
        
            }
            if(!empty($_FILES['file'])){
                $file = $_FILES['file'];
                $file_ext=strtolower(end(explode('.',$_FILES['file']['name'])));
                $file_name = time() . "." . $file_ext;
                $fileName = $file['name'];
                $file_path = '/file/'.$file_name;
                move_uploaded_file($file['tmp_name'],$file_path);
                $_POST['file_link'] = $file_name;
        
            }else {
                $_POST['file'] = "DEFAULT";
            }

            $bind = str_repeat("s", 30);
            $prep = mb_substr(str_repeat("?,", 30), 0, -1);
            $_POST['staff_id'] = 1;
            $stmt = $this->db->prepare("INSERT INTO `books_description`(`title`, `cover_url`, `file_url`, `catalog`, `classifier`, `language`, `desctiption`, `part_number`, `part_name`, `title_information`, `liability_information`, `place_of_publication`, `publication_information`, `publisher`, `publication_year`, `number_of_pages`, `by_content`, `binding`, `series`, `bibliographic_level`, `card_file`, `author_sign`, `employees_notes`, `price`, `ISBN`, `book_circulation`, `by branches_of_knowledge`, `by_branches`, `type_of_document`, `dop_links`) VALUES ({$prep})");
            $stmt->prepare($bind, 
            $_POST('title'), $_POST('cover_url'), $_POST('file_url'), $_POST('catalog'), $_POST('classifier'), $_POST('language'), $_POST('desctiption'), $_POST('part_number'), $_POST('part_name'), $_POST('title_information'), $_POST('liability_information'), $_POST('place_of_publication'), $_POST('publication_information'), $_POST('publisher'), $_POST('publication_year'), $_POST('number_of_pages'), $_POST('by_content'), $_POST('binding'), $_POST('series'), $_POST('bibliographic_level'), $_POST('card_file'), $_POST('author_sign'), $_POST('employees_notes'), $_POST('price'), $_POST('ISBN'), $_POST('book_circulation'), $_POST('by branches_of_knowledge'), $_POST('by_branches'), $_POST('type_of_document'), $_POST('dop_links')
        );


            $stmt = $this->db->prepare("INSERT INTO `bibliographic_descriptions`(`title`, `genre_id`, `cover`, `publisher_id`, `book_series`, `year_of_publishing`, `chapter_number`, `chapter_name`, `place_of_publication`, `book_circulation`, `rare_fund`, `header_information`, `publishing_information`, `number_of_pages`, `kids_ediition`, `brief`, `file_link`, `extra_link`, `binding`, `by_content`, `branche_of_knowledge`, `type_of_document`, `extra_title`, `isbn`, `price`, `classifier`, `copyright_sign`, `note`, `bibliographic_level`, `card_file`, `staff_id`, `responsible_information`, `comment`) VALUES ({$prep})");
            $stmt->bind_param($bind, $_POST['title'], $_POST['genre_id'], $_POST['cover'], $_POST['publisher_id'], $_POST['book_series'], $_POST['year_of_publishing'], $_POST['chapter_number'], $_POST['chapter_name'], $_POST['place_of_publication'], $_POST['book_circulation'],  $_POST['rare_fund'], $_POST['header_information'], $_POST['publishing_information'], $_POST['number_of_pages'], $_POST['kids_ediition'], $_POST['brief'], $_POST['file_link'], $_POST['extra_link'], $_POST['binding'], $_POST['by_content'], $_POST['branche_of_knowledge'], $_POST['type_of_document'], $_POST['extra_title'], $_POST['isbn'], $_POST['price'], $_POST['classifier'], $_POST['copyright_sign'], $_POST['note'], $_POST['bibliographic_level'], $_POST['card_file'], $_POST['staff_id'], $_POST['responsible_information'], $_POST['comment']);
            $result = $stmt->execute();


            if ($result) {
                $book_id = $this->db->insert_id;
                $authors = json_decode($_POST['authors'], true);
                $stmt = $this->db->prepare("INSERT INTO `book_author`(`author_id`, `book_id`, `type`) VALUES (?,?,?)");
                $resultAuthors = null;
                foreach ($authors as $value) {
                    $stmt->bind_param("iii", $value['author_id'], $book_id, $value['type']);
                    $resultAuthors = $stmt->execute();

                }
                if ($resultAuthors) {
                    $keywords = json_decode($_POST['keywords']);
                    $resultKeywords = null;
                    $stmt = $this->db->prepare("INSERT INTO `keywords_books`(`book_id`, `keyword`) VALUES (?,?)");
                    foreach ($keywords as $value) {
                        $stmt->bind_param("is", $book_id, $value);
                        $resultKeywords = $stmt->execute();
                    }
                    if (!$resultKeywords) {
                        header("HTTP/1.1 401 check keywords");
                        return false;
                        exit();
                    }
                }else {
                    header("HTTP/1.1 401 check authors");
                    return false;
                }
            }else{
                header("HTTP/1.1 401 check book");
                return false;
            }
            return true;
        } catch (\Throwable $th) {
            echo $th;
            header("HTTP/1.1 501 error during request");
            exit();
        }
        
        
    } 

    function get($data){
        $type_values = [];
        $query = "SELECT `bd`.`id`, `bd`.`title` `title`, `bd`.`number_of_pages`, `bd`.`author_sign`, (SELECT GROUP_CONCAT(a.name SEPARATOR ', ') FROM book_author `ba` LEFT JOIN authors `a` ON `a`.`id` = `ba`.`author_id` WHERE book_id = `bd`.`id`) `authors`, `bd`.`publication_year` FROM `books_description` `bd` ";
            try {
                $limit = 100;
            $offset = $_GET['page'] * 100;
            $books = [];
            if (isset($data['filter'])) {
                if (isset($data['filter'])) {
                    # code...
                }
                if (isset($data['filter']['sort_column'])) {
                    switch ($data['filter']['sort_column']) {
                        case 'by_title':
                            $query .= " ORDER BY `bd`.`title` ";
                            break;
                        case 'by_year':
                            $query .= " ORDER BY `bd`.`publication_year` ";
                            break;  
                            default:
                                header("HTTP/1.1 400 sort column was not specified");
                            return false;
                    }
                    switch ($data['filter']['sort_direction']) {
                        case 'desc':
                            $query .= " DESC";
                            break;
                        case 'asc':
                            $query .= " ASC";
                            break;
                        default:
                            # code...
                            break;
                    }
                }
                
                $query .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($query); 
                // call_user_func_array(array($stmt, bind_param), array("three", "four"));
                $stmt->bind_param("ii", $limit, $offset);

            }
            // $stmt = $this->db->prepare("SELECT `bd`.`id`, `bd`.`title`, `bd`.`number_of_pages`, `bd`.`author_sign`, (SELECT GROUP_CONCAT(a.name SEPARATOR ', ') FROM book_author `ba` LEFT JOIN authors `a` ON `a`.`id` = `ba`.`author_id` WHERE book_id = `bd`.`id`) `authors` FROM `books_description` `bd` 
            // LIMIT ? OFFSET ?");
            // $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                array_push($books, $row);
            }
            return $books;
            } catch (\Throwable $th) {
                echo $th;
            }
            

    }
}

