<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$level = $_SESSION['academic_level'] ?? 'beginner';
$track = $_SESSION['learning_track'] ?? 'msa';

$trackDirective = "";
if ($track === 'quranic') {
    $trackDirective = "Make the story and vocabulary strictly themed around classical Quranic Arabic, containing spiritual motifs, classical script sentence flows, and relevant theological/moral themes suited for Quranic study.";
} else {
    $trackDirective = "Make the story and vocabulary themed around Modern Standard Arabic (MSA), dealing with modern everyday scenarios, travel, business, or technology.";
}

$prompt = "You are an expert Arabic teacher. Generate a unique, short, and highly engaging reading comprehension story in Arabic for a $level student.
$trackDirective
Provide the Arabic story, its English translation, one reading comprehension question in Arabic about the story, the correct answer in Arabic, and 3 completely wrong answers in Arabic.
Make the story creative and different every time.
Return ONLY valid JSON matching this exact structure:
{
    \"paragraph_ar\": \"...\",
    \"translation_en\": \"...\",
    \"question_ar\": \"...\",
    \"correct_answer\": \"...\",
    \"wrong_answers\": [\"...\", \"...\", \"...\"]
}
Do not include any markdown syntax or explanation, just the raw JSON.";

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
    $resData = json_decode($response, true);
    $raw = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $clean = preg_replace('/```json|```|json/', '', $raw);
    $q = json_decode(trim($clean), true);
    
    if ($q && isset($q['correct_answer']) && isset($q['wrong_answers']) && count($q['wrong_answers']) >= 3) {
        $options = [$q['correct_answer'], $q['wrong_answers'][0], $q['wrong_answers'][1], $q['wrong_answers'][2]];
        shuffle($options);
        echo json_encode([
            "id" => 0, // AI generated, no ID needed
            "paragraph_ar" => $q['paragraph_ar'],
            "translation_en" => $q['translation_en'],
            "question_ar" => $q['question_ar'],
            "correct_answer" => $q['correct_answer'],
            "options" => $options
        ]);
        exit;
    }
}

// Fallback to local DB if API fails or parsing fails
try {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM reading_comprehension WHERE level = ? AND id NOT IN (SELECT question_id FROM user_answered WHERE user_id = ? AND mode = 'reading') ORDER BY RAND() LIMIT 1");
    $stmt->execute([$level, $userId]);
    $q = $stmt->fetch();

    if ($q) {
        $options = [$q['correct_answer'], $q['wrong_1'], $q['wrong_2'], $q['wrong_3']];
        shuffle($options);
        echo json_encode([
            "id" => $q['id'],
            "paragraph_ar" => $q['paragraph_ar'],
            "translation_en" => $q['translation_en'],
            "question_ar" => $q['question_ar'],
            "correct_answer" => $q['correct_answer'],
            "options" => $options
        ]);
    } else {
        echo json_encode(["completed" => true]);
    }
} catch (Exception $e) { echo json_encode(["error" => "Database error: " . $e->getMessage()]); }
?>
