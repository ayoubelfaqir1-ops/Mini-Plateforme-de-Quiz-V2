<?php 
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Result.php';

// For AJAX endpoints return JSON on auth failure instead of redirect
header('Content-Type: application/json');
if (!Security::isStudent()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
if (!isset($_GET['etudiant_id'])) {
    echo json_encode(['success'=>false, 'error'=>"etudiant_id not provided"]);
    exit();
}

$etudiantId = intval($_GET['etudiant_id']);
try {
    $resultObj = new Result();
    $stats = $resultObj->getMyStats($etudiantId);
    $results = $resultObj->getMyResults($etudiantId);
    echo json_encode(['success' => true, 'stats' => $stats, 'results' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>

