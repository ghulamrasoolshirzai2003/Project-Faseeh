<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(["error" => "Not logged in"]); 
    exit; 
}

try {
    $level = $_SESSION['academic_level'] ?? 'beginner';
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT * FROM grammar_questions 
        WHERE level = ? 
        AND id NOT IN (SELECT question_id FROM user_answered WHERE user_id = ? AND mode = 'grammar')
        ORDER BY RAND() LIMIT 1
    ");
    $stmt->execute([$level, $userId]);
    $q = $stmt->fetch();

    if ($q) {
        $options = [
            $q['correct_answer'],
            $q['wrong_option_1'],
            $q['wrong_option_2'],
            $q['wrong_option_3']
        ];
        // Shuffle the options so correct_answer isn't always first
        shuffle($options);

        echo json_encode([
            "id" => $q['id'],
            "sentence_ar" => $q['sentence_ar'],
            "translation_en" => $q['translation_en'],
            "grammar_rule" => $q['grammar_rule'],
            "correct_answer" => $q['correct_answer'],
            "options" => $options
        ]);
    } else {
        echo json_encode(["completed" => true]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
