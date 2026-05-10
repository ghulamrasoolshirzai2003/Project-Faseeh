<?php
/**
 * API: Get MCQ (Multiple Choice Quiz) question
 * Returns an Arabic word with 4 options (1 correct + 3 distractors)
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

// Remove Tashkeel
function removeTashkeel($text) {
    $tashkeel = ['ِ', 'ُ', 'َ', 'ْ', 'ّ', 'ٍ', 'ٌ', 'ً'];
    return str_replace($tashkeel, '', $text);
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];
$level = $_SESSION['level'] ?? 'beginner';
$lang = $_SESSION['lang'] ?? 'en';
$meaningCol = ($lang == 'my') ? 'meaning_my' : 'meaning_en';

try {
    // Get a random word from the current level
    $stmt = $pdo->prepare("SELECT * FROM words WHERE level = ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$level]);
    $word = $stmt->fetch();

    if (!$word) {
        echo json_encode(['error' => 'No words available for this level']);
        exit;
    }

    $correctMeaning = $word[$meaningCol] ?? $word['meaning_en'];

    // Get 3 random WRONG meanings from the same level (or any level if not enough)
    $stmt = $pdo->prepare("
        SELECT $meaningCol as meaning FROM words 
        WHERE id != ? AND $meaningCol != ?
        ORDER BY RAND() 
        LIMIT 3
    ");
    $stmt->execute([$word['id'], $correctMeaning]);
    $distractors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // If not enough distractors, pad with generic ones
    $fallbacks = ['Water', 'House', 'Book', 'Friend', 'Garden', 'Night', 'Heart', 'Dream'];
    while (count($distractors) < 3) {
        $fb = $fallbacks[array_rand($fallbacks)];
        if ($fb !== $correctMeaning && !in_array($fb, $distractors)) {
            $distractors[] = $fb;
        }
    }

    // Build options array and shuffle
    $options = array_merge([$correctMeaning], $distractors);
    shuffle($options);

    $cleanArabic = removeTashkeel($word['arabic_word']);

    echo json_encode([
        'id' => $word['id'],
        'arabic_word' => $cleanArabic,
        'root' => $word['root'] ?? '',
        'category' => $word['category'] ?? 'general',
        'correct_answer' => $correctMeaning,
        'options' => $options,
        'audio_file' => $word['audio_file'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
