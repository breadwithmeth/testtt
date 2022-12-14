<?php
class Staff{
    private $db;
    
    public $id;
    public $role;
    public $isLoggedIn = false;
    public $token;
    function __construct($db) {
        $this->db = $db;
        session_start();
        if(isset($_SERVER['HTTP_AUTH'])){
            $result = $this->checkKeyAuth($_SERVER['HTTP_AUTH']);
            if($result){
                $this->isLoggedIn = true;
                //echo $_SERVER['HTTP_AUTH'];
            }else{
                $this->isLoggedIn = false;
            	// echo 'Ты дурак?';
            	//exit();
            	//return false;
            }
        }
    }

    public function checkKeyAuth($token){
        $stmt = $this->db->prepare("SELECT * FROM `employees` WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result == NULL) {
            return false;
        }else{
            //var_dump($result);
    	    return $result;
        }
    }


    function login($login, $password){
        if (empty($password)) {
            echo false;
            header("HTTP/1.1 400 login is empty");

        }elseif (empty($login)) {
            echo false;
            header("HTTP/1.1 400 password is empty");

        // }
        // elseif (empty($area_id)) {
        //     echo false;
        //     header("HTTP/1.1 400 area_id is empty");
        }else{
            // $query = "SELECT `w`.`login`, `w`.`password`, `w`.`first_name`, `w`.`middle_name`, `w`.`last_name`, `w`.`idDeleted`, `w`.`photo`, `w`.`position_id`, `p`.`title`, `p`.`access_level`  FROM `workers` `w` LEFT JOIN `possitions` `p` ON `w`.`position_id` = `p`.`position_id` WHERE `w`.`login` = '$login'";
            // $result = $this->db->query($query);
            // $result = $result->fetch_assoc();
                $stmt = $this->db->prepare("SELECT `e`.`id`, `e`.`first_name`, `e`.`last_name`, `e`.`middle_name` FROM employees `e` WHERE `e`.`phone_number`=? and `e`.`password`=? ");
            $stmt->bind_param("ss", $login, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row == NULL) {
                header("HTTP/1.1 403 User not found");
                return json_encode(false);
            }else {
                if (true) {
                    $uuid = crypt(time(), "3c6b8f7676");
                    $this->isLoggedIn = true;
                    $stmt = $this->db->prepare("UPDATE `employees` SET `token` = '{$uuid}' WHERE `id` = '{$row['id']}'");
                    $result = $stmt->execute();
                    if ($result) {
                        $row['token'] = $uuid;
                        return $row;    
                    }
                }else{
                    header("HTTP/1.1 403 User is delited");
                    return json_encode(true);
                }
            // }else{
            //     header("HTTP/1.1 403 Access denied");
            //     return json_encode(false);

            }
            
            
            

        }
        
    }


    

    

    
}