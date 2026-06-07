<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

try {
    // --- AUTO-INSTALLER: Create table if missing ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS sentence_builder (
        id INT AUTO_INCREMENT PRIMARY KEY,
        correct_sentence TEXT NOT NULL,
        scrambled_words TEXT NOT NULL,
        translation_en TEXT NOT NULL,
        level VARCHAR(20) DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // --- AUTO-INSTALLER: Seed if empty ---
    $count = $pdo->query("SELECT COUNT(*) FROM sentence_builder")->fetchColumn();
    if ($count == 0) {
        $seeds = [
            ['أنا أحب اللغة العربية', '["أحب", "أنا", "اللغة", "العربية"]', 'I love the Arabic language'],
            ['هذا كتاب جميل جداً', '["كتاب", "هذا", "جميل", "جداً"]', 'This is a very beautiful book'],
            ['أين تذهب يا صديقي؟', '["تذهب", "أين", "صديقي؟", "يا"]', 'Where are you going, my friend?'],
            ['السماء صافية اليوم والجو جميل', '["صافية", "السماء", "اليوم", "جميل", "والجو"]', 'The sky is clear today and the weather is beautiful'],
            ['أريد أن أشرب كوباً من الماء', '["أريد", "أشرب", "أن", "كوباً", "الماء", "من"]', 'I want to drink a cup of water'],
            ['أمي تطبخ طعاماً لذيذاً', '["طعاماً", "أمي", "تطبخ", "لذيذاً"]', 'My mother cooks delicious food'],
            ['ذهب الولد إلى المدرسة مبكراً', '["الولد", "ذهب", "إلى", "مبكراً", "المدرسة"]', 'The boy went to school early']
        ];
        $stmt = $pdo->prepare("INSERT INTO sentence_builder (correct_sentence, scrambled_words, translation_en) VALUES (?, ?, ?)");
        foreach ($seeds as $s) $stmt->execute($s);
    }

    $level = $_SESSION['academic_level'] ?? 'beginner';
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT * FROM sentence_builder 
        WHERE level = ? 
        ORDER BY RAND() LIMIT 1
    ");
    $stmt->execute([$level]);
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
