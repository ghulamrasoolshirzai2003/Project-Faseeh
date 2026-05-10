<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

try {
    $level = $_SESSION['academic_level'] ?? 'beginner';
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT * FROM sentence_builder 
        WHERE level = ? 
        AND id NOT IN (SELECT question_id FROM user_answered WHERE user_id = ? AND mode = 'tarkib')
        ORDER BY RAND() LIMIT 1
    ");
    $stmt->execute([$level, $userId]);
    $q = $stmt->fetch();

    if ($q) {
        echo json_encode([
            "id" => $q['id'],
            "correct_sentence" => $q['correct_sentence'],
            "scrambled_words" => $q['scrambled_words'],
            "translation_en" => $q['translation_en']
        ]);
    } else {
        echo json_encode(["completed" => true]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
