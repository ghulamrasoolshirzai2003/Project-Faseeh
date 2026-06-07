<?php
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

$level = $_SESSION['level'] ?? 'beginner';
$lang = $_SESSION['lang'] ?? 'en';
$meaningCol = ($lang == 'my') ? 'meaning_my' : 'meaning_en';

try {
    // 1. Try to get a word from the user's level
    $stmt = $pdo->prepare("SELECT * FROM words WHERE level = ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$level]);
    $word = $stmt->fetch();

    // 2. FALLBACK: If no words in that level, get ANY word from the database
    if (!$word) {
        $word = $pdo->query("SELECT * FROM words ORDER BY RAND() LIMIT 1")->fetch();
    }

    if (!$word) {
        echo json_encode(['error' => 'Database is empty. Please add words to play!']);
        exit;
    }

    $correctMeaning = $word[$meaningCol] ?? $word['meaning_en'];

    // Get 3 random distractors
    $stmt = $pdo->prepare("SELECT $meaningCol as meaning FROM words WHERE id != ? AND $meaningCol != ? ORDER BY RAND() LIMIT 3");
    $stmt->execute([$word['id'], $correctMeaning]);
    $distractors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Padding if needed
    $fallbacks = ['Apple', 'Water', 'Book', 'House', 'Friend', 'School', 'Sun', 'Moon'];
    while (count($distractors) < 3) {
        $fb = $fallbacks[array_rand($fallbacks)];
        if ($fb !== $correctMeaning && !in_array($fb, $distractors)) {
            $distractors[] = $fb;
        }
    }

    $options = array_merge([$correctMeaning], $distractors);
    shuffle($options);

    echo json_encode([
        'id' => $word['id'],
        'arabic_word' => removeTashkeel($word['arabic_word']),
        'root' => $word['root'] ?? '',
        'correct_answer' => $correctMeaning,
        'options' => $options
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'API Error: ' . $e->getMessage()]);
}
?>
