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
    // Get words due for review
    $stmt = $pdo->prepare("
        SELECT w.id, w.arabic_word, w.$meaningCol as meaning, w.root, w.audio_file,
               rq.ease_factor, rq.interval_days, rq.repetitions, rq.next_review
        FROM review_queue rq
        JOIN words w ON rq.word_id = w.id
        WHERE rq.user_id = ? AND rq.next_review <= ?
        ORDER BY rq.next_review ASC
        LIMIT 10
    ");
    $stmt->execute([$uid, $today]);
    $words = $stmt->fetchAll();

    // Clean tashkeel
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
