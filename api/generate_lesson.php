<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$type = $data['type'] ?? 'reading';

// In a production environment, you would call Gemini/OpenAI API here.
// For now, we simulate a sophisticated AI generation logic.

$topics = ['History', 'Science', 'Culture', 'Philosophy', 'Economics', 'Poetry'];
$topic = $topics[array_rand($topics)];

$lesson_data = [];

if ($type == 'reading') {
    $lesson_data = [
        'title' => "AI Insight: $topic in the Arab World",
        'arabic_title' => "رؤية آلية: $topic في العالم العربي",
        'content' => "تعتبر اللغة العربية وعاءً حضارياً استوعب كافة العلوم والفنون عبر العصور.",
        'translation' => "The Arabic language is considered a cultural vessel that has accommodated all sciences and arts throughout the ages.",
        'metadata' => [
            'words' => [
                'حضارياً' => ['root' => 'ح ض ر', 'mean' => 'Civilizational', 'grammar' => 'Adjective/Adverbial'],
                'استوعب' => ['root' => 'و ع ب', 'mean' => 'Accommodated/Absorbed', 'grammar' => 'Verb, Past']
            ]
        ]
    ];
} elseif ($type == 'writing') {
    $lesson_data = [
        'title' => "AI Prompt: $topic Analysis",
        'arabic_title' => "تحليل $topic",
        'content' => "Write an academic reflection on how $topic has shaped modern Arabic identity.",
        'metadata' => []
    ];
} else {
    // Fallback for other types
    $lesson_data = [
        'title' => "AI Generated $type: $topic",
        'arabic_title' => "درس $type آلي",
        'content' => "محتوى مقترح للتدريب على $type في مجال $topic.",
        'metadata' => []
    ];
}

try {
    $stmt = $pdo->prepare("INSERT INTO academy_lessons (type, level, title, arabic_title, content, translation, metadata) VALUES (?, 'intermediate', ?, ?, ?, ?, ?)");
    $stmt->execute([
        $type,
        $lesson_data['title'],
        $lesson_data['arabic_title'],
        $lesson_data['content'],
        $lesson_data['translation'] ?? null,
        json_encode($lesson_data['metadata'])
    ]);
    
    $newId = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') 
        ? $pdo->lastInsertId('academy_lessons_id_seq') 
        : $pdo->lastInsertId();
    echo json_encode(['status' => 'success', 'lesson_id' => $newId, 'title' => $lesson_data['title']]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
