<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']); exit;
}

try {
    $lang = $_SESSION['lang'] ?? 'en';
    $meaningCol = ($lang == 'my') ? 'meaning_my' : 'meaning_en';

    // Fetch 50 random words to ensure the game doesn't run out during the 60s
    $stmt = $pdo->prepare("
        SELECT id, arabic_word, $meaningCol as meaning, level 
        FROM words 
        ORDER BY RAND() 
        LIMIT 50
    ");
    $stmt->execute();
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($words);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
