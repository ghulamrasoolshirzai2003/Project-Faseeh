<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

try {
    $level = $_SESSION['academic_level'] ?? 'beginner';
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT * FROM root_word_questions 
        WHERE level = ? 
        AND id NOT IN (SELECT question_id FROM user_answered WHERE user_id = ? AND mode = 'root_word')
        ORDER BY RAND() LIMIT 1
    ");
    $stmt->execute([$level, $userId]);
    $q = $stmt->fetch();

    if ($q) {
        $options = [
            $q['correct_root'],
            $q['wrong_option_1'],
            $q['wrong_option_2'],
            $q['wrong_option_3']
        ];
        shuffle($options);

        echo json_encode([
            "id" => $q['id'],
            "complex_word" => $q['complex_word'],
            "translation_en" => $q['translation_en'],
            "correct_answer" => $q['correct_root'],
            "options" => $options
        ]);
    } else {
        echo json_encode(["completed" => true]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
