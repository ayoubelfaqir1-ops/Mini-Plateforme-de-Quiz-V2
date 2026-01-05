<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';

// Ensure the user is authorized for AJAX: return JSON on auth failure
if (!Security::isStudent()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['quiz_id'])) {
    echo json_encode(['error' => 'Quiz ID is required']);
    exit;
}

$quizId = intval($_GET['quiz_id']);
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC");
    $stmt->bindParam(':quiz_id', $quizId);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $quizData = [];

    foreach ($questions as $question) {
        $answers = [];
        if (!empty($question['option1'])) {
            $answers[] = ['id' => 1, 'text' => $question['option1']];
        }
        if (!empty($question['option2'])) {
            $answers[] = ['id' => 2, 'text' => $question['option2']];
        }
        if (!empty($question['option3'])) {
            $answers[] = ['id' => 3, 'text' => $question['option3']];
        }
        if (!empty($question['option4'])) {
            $answers[] = ['id' => 4, 'text' => $question['option4']];
        }
        $quizData[] = [
            'id' => $question['id'],
            'text' => $question['question'],
            'answers' => $answers,
            'reponse' => $question['correct_option']
        ];
    }

    echo json_encode(['success' => true, 'questions' => $quizData]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
