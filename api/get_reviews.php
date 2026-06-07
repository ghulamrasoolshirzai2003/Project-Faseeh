<?php
/**
 * API: Get words due for review (Spaced Repetition)
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

function removeTashkeel($text) {
    $tashkeel = ['ِ', 'ُ', 'َ', 'ْ', 'ّ', 'ٍ', 'ٌ', 'ً'];
    return str_replace($tashkeel, '', $text);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];
$lang = $_SESSION['lang'] ?? 'en';
$meaningCol = ($lang == 'my') ? 'meaning_my' : 'meaning_en';
$today = date('Y-m-d');

try {
    // --- AUTO-INSTALLER: Create table if missing ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS review_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        word_id INT NOT NULL,
        ease_factor FLOAT DEFAULT 2.5,
        interval_days INT DEFAULT 0,
        repetitions INT DEFAULT 0,
        next_review DATE NOT NULL,
        last_reviewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_word (user_id, word_id)
    )");

    // --- AUTO-SEEDER: If queue is empty, add 10 fresh words ---
    $count = $pdo->prepare("SELECT COUNT(*) FROM review_queue WHERE user_id = ?");
    $count->execute([$uid]);
    if ($count->fetchColumn() == 0) {
        $freshWords = $pdo->query("SELECT id FROM words ORDER BY RAND() LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
        $insert = $pdo->prepare("INSERT INTO review_queue (user_id, word_id, next_review) VALUES (?, ?, ?)");
        foreach ($freshWords as $wid) {
            $insert->execute([$uid, $wid, $today]);
        }
    }

    // Get words due for review
    $stmt = $pdo->prepare("
        SELECT w.id, w.arabic_word, w.$meaningCol as meaning, w.root,
               rq.ease_factor, rq.interval_days, rq.repetitions, rq.next_review
        FROM review_queue rq
        JOIN words w ON rq.word_id = w.id
        WHERE rq.user_id = ? AND rq.next_review <= ?
        ORDER BY rq.next_review ASC
        LIMIT 15
    ");
    $stmt->execute([$uid, $today]);
    $words = $stmt->fetchAll();

    // Clean tashkeel for display clarity
    foreach ($words as &$w) {
        $w['arabic_word'] = removeTashkeel($w['arabic_word']);
    }

    echo json_encode([
        'words' => $words,
        'count' => count($words)
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
