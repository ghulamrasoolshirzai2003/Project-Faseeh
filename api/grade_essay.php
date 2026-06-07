<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/progress.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$essay = trim($data['essay'] ?? '');
$prompt_id = $data['prompt_id'] ?? 0;

if (mb_strlen($essay) < 10) {
    echo json_encode(["error" => "Essay is too short. Please write at least a few sentences."]);
    exit;
}

// ============================================================
// AI ESSAY ANALYSIS ENGINE (Merged with analyze_writing.php)
// ============================================================

// Check if it's actually Arabic text (at least 50% Arabic characters)
$arabic_chars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $essay);
$total_chars = mb_strlen(preg_replace('/\s+/', '', $essay));
if ($total_chars > 0 && ($arabic_chars / $total_chars) < 0.5) {
    echo json_encode(["error" => "Invalid input. Please write your essay in Arabic."]);
    exit;
}

$prompt = "You are Professor Faseeh, an expert PhD Arabic tutor. You are grading an Arabic essay written by a student.
The student wrote: \"$essay\"

You must analyze this essay and return ONLY a valid JSON object matching this exact structure:
{
    \"overall_score\": [Number from 0 to 100],
    \"grade\": \"[Letter grade: A+, A, B+, B, C, D, or F]\",
    \"word_count\": [Actual word count],
    \"sentence_count\": [Actual sentence count],
    \"unique_words\": [Actual unique word count],
    \"avg_sentence_length\": [Number],
    \"scores\": {
        \"vocabulary\": [Number 0-100],
        \"connectors\": [Number 0-100],
        \"academic\": [Number 0-100],
        \"structure\": [Number 0-100],
        \"length\": [Number 0-100]
    },
    \"found_connectors\": [Array of objects like {\"ar\": \"لكن\", \"en\": \"but\"}],
    \"found_academic\": [Array of strings containing academic words found],
    \"tips\": [
        {\"type\": \"success\"|\"warning\"|\"info\", \"text\": \"Tip text here...\"}
    ]
}

Make sure the feedback is encouraging but accurate. Do not include any markdown formatting (like ```json), just return the raw JSON object.";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
$payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http === 200) {
    $data = json_decode($response, true);
    $raw = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Extract only the JSON block between curly braces
    if (preg_match('/\{.*\}/s', $raw, $matches)) {
        $clean = $matches[0];
    } else {
        $clean = preg_replace('/```(json)?/', '', $raw);
    }
    
    $aiResult = json_decode(trim($clean), true);
    
    if (!$aiResult) {
        echo json_encode(["error" => "Professor Faseeh had trouble understanding your essay formatting. Please try again."]);
        exit;
    }
    
    $overall = $aiResult['overall_score'] ?? 0;
    $grade = $aiResult['grade'] ?? 'F';
    $word_count = $aiResult['word_count'] ?? 0;
    $sentence_count = $aiResult['sentence_count'] ?? 0;
    $unique_words = $aiResult['unique_words'] ?? 0;
    $avg_sentence_length = $aiResult['avg_sentence_length'] ?? 0;
    $vocab_score = $aiResult['scores']['vocabulary'] ?? 0;
    $connector_score = $aiResult['scores']['connectors'] ?? 0;
    $academic_score = $aiResult['scores']['academic'] ?? 0;
    $structure_score = $aiResult['scores']['structure'] ?? 0;
    $length_score = $aiResult['scores']['length'] ?? 0;
    $found_connectors = $aiResult['found_connectors'] ?? [];
    $found_academic = $aiResult['found_academic'] ?? [];
    $tips = $aiResult['tips'] ?? [];
} else {
    echo json_encode(["error" => "Professor Faseeh is resting right now. Please try again in a moment."]);
    exit;
}

// 9. Award XP based on score
$xp = 0;
if ($overall >= 70) $xp = 30;
elseif ($overall >= 50) $xp = 20;
elseif ($overall >= 30) $xp = 10;

$userId = $_SESSION['user_id'];
$correct = ($overall >= 60) ? 1 : 0;
$wrong = ($overall < 60) ? 1 : 0;
saveUserProgress($pdo, $userId, $xp, 'essay', $correct, $wrong);

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
