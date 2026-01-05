<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Result.php';

header('Content-Type: application/json');
// 1. Read raw request body
$raw = file_get_contents("php://input");
// 2. Convert JSON → PHP array/object
$data = json_decode($raw, true);

if(!$_SERVER['REQUEST_METHOD'] == 'POST' || !isset($data['quiz_id']))
{
    echo json_encode(['success' => false,'error'=>'invalid request']);
    exit;
}
Security::requireStudent();
$quiz_id = $data['quiz_id'];
$etudiantId = $_SESSION['user_id'];
$score = $data['score'];
$totalquest = $data['total_questions'];
$finishtime = $data['finishtime'];

$resultobj = new Result();
$insert = $resultobj->save($quiz_id,$etudiantId,$score,$totalquest,$finishtime);
if($insert)
{
    echo json_encode(['success' => true]);
}else{
    echo json_encode(['success' => false, 'error'=> "insert error"]);
}
?>