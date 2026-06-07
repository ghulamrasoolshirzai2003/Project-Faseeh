<?php
require __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    // Try to get a random word. We assume a 'words' table exists.
    // If your table has different names, this will catch the error.
    $seed = date('Ymd');
    $sql = "SELECT arabic_word, meaning_en as meaning, category, level FROM words ORDER BY RAND($seed) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$challenge) {
        // Fallback if table is empty
        $challenge = [
            'arabic_word' => 'كِتاب',
            'meaning'    => 'book',
            'audio_url'  => ''
        ];
    }
    echo json_encode($challenge);

} catch (Exception $e) {
    // Return the actual database error so we can fix it!
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'arabic_word' => 'Error',
        'meaning' => 'Check console'
    ]);
}
?>
