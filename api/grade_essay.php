<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$essay = trim($data['essay'] ?? '');
$prompt_id = $data['prompt_id'] ?? 0;

if (mb_strlen($essay) < 10) {
    echo json_encode(["error" => "Essay is too short. Please write at least a few sentences."]);
    exit;
}

// ============================================================
// ANTI-GIBBERISH FILTER
// ============================================================

// 1. Check if it's actually Arabic text (at least 70% Arabic characters)
$arabic_chars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $essay);
$total_chars = mb_strlen(preg_replace('/\s+/', '', $essay));
if ($total_chars > 0 && ($arabic_chars / $total_chars) < 0.7) {
    echo json_encode(["error" => "Invalid input. Please write your essay in Arabic."]);
    exit;
}

$words = preg_split('/\s+/u', $essay);
$word_count = count($words);

// 2. Check for keyboard mashing (impossibly long words)
foreach ($words as $word) {
    if (mb_strlen($word) > 16) {
        echo json_encode(["error" => "Invalid text detected. Real Arabic words are not that long. Please stop mashing the keyboard!"]);
        exit;
    }
}

// 3. Check for structural grammar (glue words)
// Any real essay of 20+ words will naturally contain basic prepositions/pronouns.
$glue_words = ['في', 'من', 'على', 'إلى', 'عن', 'أن', 'إن', 'هذا', 'هذه', 'الذي', 'التي', 'هو', 'هي', 'لا', 'ما', 'مع'];
$glue_count = 0;
foreach ($glue_words as $gw) {
    if (preg_match("/\b" . $gw . "\b/u", $essay)) {
        $glue_count++;
    }
}

if ($word_count > 15 && $glue_count == 0) {
    echo json_encode(["error" => "This doesn't look like a real Arabic essay. It lacks basic grammatical structure (like في, من, على). Nice try!"]);
    exit;
}

// ============================================================
// ARABIC ESSAY ANALYSIS ENGINE
// ============================================================

// 1. Basic metrics
$sentences = preg_split('/[.!?،؟\n]+/u', $essay);
$sentences = array_filter($sentences, fn($s) => mb_strlen(trim($s)) > 0);
$sentence_count = count($sentences);
$unique_words = count(array_unique($words));
$avg_sentence_length = $sentence_count > 0 ? round($word_count / $sentence_count, 1) : 0;

// 2. Vocabulary Diversity Score (0-100)
$vocab_ratio = $word_count > 0 ? ($unique_words / $word_count) : 0;
$vocab_score = min(100, round($vocab_ratio * 130)); // Scale up slightly

// 3. Connector usage (Arabic linking words)
$connectors = [
    'و' => 'and', 'ف' => 'so/then', 'ثم' => 'then', 'لكن' => 'but', 'لأن' => 'because',
    'إذا' => 'if', 'عندما' => 'when', 'حتى' => 'until/so that', 'بينما' => 'while',
    'كذلك' => 'likewise', 'أيضاً' => 'also', 'بالإضافة' => 'in addition',
    'علاوة' => 'moreover', 'نتيجة' => 'as a result', 'بسبب' => 'because of',
    'رغم' => 'despite', 'مع' => 'with', 'خلال' => 'during', 'قبل' => 'before',
    'بعد' => 'after', 'منذ' => 'since', 'حيث' => 'where/since', 'إن' => 'indeed',
    'لذلك' => 'therefore', 'هكذا' => 'thus', 'أما' => 'as for', 'أولاً' => 'firstly',
    'ثانياً' => 'secondly', 'أخيراً' => 'finally'
];
$found_connectors = [];
foreach ($connectors as $ar => $en) {
    if (mb_strpos($essay, $ar) !== false) {
        $found_connectors[] = ["ar" => $ar, "en" => $en];
    }
}
$connector_score = min(100, round((count($found_connectors) / 8) * 100));

// 4. Academic vocabulary check
$academic_words = [
    'المجتمع', 'الاقتصاد', 'التنمية', 'التعليم', 'الثقافة', 'السياسة', 'البيئة',
    'التكنولوجيا', 'الاستراتيجية', 'التطوير', 'المؤسسات', 'الحكومة', 'المواطنين',
    'الاستثمار', 'الابتكار', 'المعرفة', 'البحث', 'الدراسات', 'التحديات', 'الفرص',
    'التعاون', 'المشاركة', 'الإنتاج', 'الخدمات', 'الموارد', 'الطاقة', 'الصناعة',
    'العدالة', 'الحرية', 'الديمقراطية', 'القانون', 'الحقوق', 'الواجبات', 'المسؤولية',
    'التواصل', 'الإعلام', 'الصحة', 'العلوم', 'الفلسفة', 'الأدب', 'التاريخ',
    'يساهم', 'يؤثر', 'يتطلب', 'يعتمد', 'يواجه', 'يحقق', 'يعزز', 'يدعم'
];
$found_academic = [];
foreach ($academic_words as $aw) {
    if (mb_strpos($essay, $aw) !== false) {
        $found_academic[] = $aw;
    }
}
$academic_score = min(100, round((count($found_academic) / 6) * 100));

// 5. Structure score (based on length, sentence variety, paragraphs)
$paragraphs = preg_split('/\n\s*\n/u', $essay);
$paragraph_count = count(array_filter($paragraphs, fn($p) => mb_strlen(trim($p)) > 0));

$length_score = 0;
if ($word_count >= 100) $length_score = 100;
elseif ($word_count >= 70) $length_score = 85;
elseif ($word_count >= 40) $length_score = 70;
elseif ($word_count >= 20) $length_score = 50;
else $length_score = 30;

$structure_score = round(($length_score * 0.4) + (min(100, $paragraph_count * 33) * 0.3) + (min(100, $sentence_count * 12) * 0.3));

// 6. Overall Score
$overall = round(
    ($vocab_score * 0.25) +
    ($connector_score * 0.20) +
    ($academic_score * 0.20) +
    ($structure_score * 0.20) +
    ($length_score * 0.15)
);

// 7. Generate grade letter
$grade = 'F';
if ($overall >= 90) $grade = 'A+';
elseif ($overall >= 80) $grade = 'A';
elseif ($overall >= 70) $grade = 'B+';
elseif ($overall >= 60) $grade = 'B';
elseif ($overall >= 50) $grade = 'C';
elseif ($overall >= 40) $grade = 'D';

// 8. Generate feedback tips
$tips = [];
if ($word_count < 40) $tips[] = ["type" => "warning", "text" => "Your essay is quite short. Aim for at least 50-100 words to develop your ideas fully."];
if ($word_count >= 80) $tips[] = ["type" => "success", "text" => "Great length! You've written a substantial piece."];
if (count($found_connectors) < 3) $tips[] = ["type" => "warning", "text" => "Use more connectors (لكن، لأن، بالإضافة، لذلك) to link your ideas smoothly."];
if (count($found_connectors) >= 5) $tips[] = ["type" => "success", "text" => "Excellent use of Arabic connectors! Your essay flows well."];
if (count($found_academic) < 3) $tips[] = ["type" => "warning", "text" => "Try to include more academic vocabulary (المجتمع، التنمية، التعاون) to sound more professional."];
if (count($found_academic) >= 5) $tips[] = ["type" => "success", "text" => "Strong academic vocabulary usage! You write like a professional."];
if ($vocab_ratio < 0.5) $tips[] = ["type" => "warning", "text" => "You're repeating many words. Try using synonyms to enrich your vocabulary."];
if ($sentence_count < 3) $tips[] = ["type" => "warning", "text" => "Write more complete sentences to express your ideas clearly."];
if ($paragraph_count <= 1) $tips[] = ["type" => "info", "text" => "Consider breaking your essay into multiple paragraphs (introduction, body, conclusion)."];
if ($paragraph_count >= 3) $tips[] = ["type" => "success", "text" => "Well-structured paragraphs! Your essay has a clear flow."];

// 9. Award XP based on score
$xp = 0;
if ($overall >= 70) $xp = 30;
elseif ($overall >= 50) $xp = 20;
elseif ($overall >= 30) $xp = 10;

$userId = $_SESSION['user_id'];
if ($xp > 0) {
    try { $pdo->exec("ALTER TABLE progress ADD COLUMN academic_correct_count INT DEFAULT 0"); } catch(Exception $ex){}
    $stmt = $pdo->prepare("UPDATE progress SET xp = xp + ?, academic_xp = academic_xp + ? WHERE user_id = ?");
    $stmt->execute([$xp, $xp, $userId]);
    
    // Count as correct if score >= 60
    if ($overall >= 60) {
        $stmt = $pdo->prepare("UPDATE progress SET academic_correct_count = academic_correct_count + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}

// Track in academic_stats
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT, mode VARCHAR(50),
        correct_answers INT DEFAULT 0, wrong_answers INT DEFAULT 0,
        UNIQUE KEY(user_id, mode)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    if ($overall >= 60) {
        $pdo->prepare("INSERT INTO academic_stats (user_id, mode, correct_answers) VALUES (?, 'essay', 1) ON DUPLICATE KEY UPDATE correct_answers = correct_answers + 1")->execute([$userId]);
    } else {
        $pdo->prepare("INSERT INTO academic_stats (user_id, mode, wrong_answers) VALUES (?, 'essay', 1) ON DUPLICATE KEY UPDATE wrong_answers = wrong_answers + 1")->execute([$userId]);
    }
} catch(Exception $ex){}

echo json_encode([
    "overall_score" => $overall,
    "grade" => $grade,
    "xp_earned" => $xp,
    "word_count" => $word_count,
    "sentence_count" => $sentence_count,
    "unique_words" => $unique_words,
    "avg_sentence_length" => $avg_sentence_length,
    "scores" => [
        "vocabulary" => $vocab_score,
        "connectors" => $connector_score,
        "academic" => $academic_score,
        "structure" => $structure_score,
        "length" => $length_score
    ],
    "found_connectors" => $found_connectors,
    "found_academic" => $found_academic,
    "tips" => $tips
]);
?>
