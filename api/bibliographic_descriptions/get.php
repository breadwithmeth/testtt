<?php
include_once '../../config/Database.php';
include_once '../../config/headers.php';
include_once '../../models/staff.php';
include_once '../../models/bibliographic_descriptions.php';

$database = new Database();
$db = $database->connect();
$staff = new Staff($db);
$book = new bibliographic_description($db);
$data = json_decode(file_get_contents("php://input"), true);

echo json_encode($book->get($data));
// if ($staff->isLoggedIn) {
// }else{
//     header("HTTP/1.1 401 unauthorized");
// }
