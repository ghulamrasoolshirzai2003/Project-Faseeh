<?php
// Silence errors to protect JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require '../includes/db.php';

// HELPER: Remove Vowels
function removeTashkeel($text) {
    $tashkeel = ['ِ', 'ُ', 'َ', 'ْ', 'ّ', 'ٍ', 'ٌ', 'ً'];
    return str_replace($tashkeel, '', $text);
}

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(["error" => "Not logged in"]); 
    exit; 
}

$uid = $_SESSION['user_id'];
// Fetch level from session. Match the casing in your DB (e.g., 'beginner')
$level = $_SESSION['level'] ?? 'beginner';
$lang = $_SESSION['lang'] ?? 'en';

try {
    // This query selects a word that is in the correct level 
    // AND has NOT been recorded in the user_progress table for this user.
    $sql = "SELECT * FROM words 
            WHERE level = ? 
            AND id NOT IN (SELECT word_id FROM user_progress WHERE user_id = ?)
            ORDER BY RAND() 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$level, $uid]);
    $word = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($word) {
        $clean_arabic = removeTashkeel($word['arabic_word']);
        
        $response = [
            "id" => $word['id'],
            "arabic_word" => $clean_arabic,
            "meaning" => ($lang == 'my') ? $word['meaning_my'] : $word['meaning_en'],
            "root" => $word['root'],
            "audio_file" => $word['audio_file']
        ];
        echo json_encode($response);
    } else {
        // No more words left for this level
        echo json_encode(["completed" => true]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>