<?php
require __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

// Publicly accessible Word of the Day API for landing page and dashboard
try {
    $seed = date('Ymd') + 1; 
    // Filter for Advanced and Intermediate words ONLY
    $sql = "SELECT arabic_word, meaning_en as meaning, category, level FROM words 
            WHERE level IN ('Advanced', 'Intermediate') 
            ORDER BY RAND($seed) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $wotd = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wotd) {
        $wotd = [
            'arabic_word' => 'سَماء',
            'meaning'    => 'sky',
            'audio_url'  => ''
        ];
    }
    echo json_encode($wotd);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'arabic_word' => 'Error',
        'meaning' => 'Check console'
    ]);
}
?>
