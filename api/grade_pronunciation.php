<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/progress.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$target = trim($data['target'] ?? '');
$transcript = trim($data['transcript'] ?? '');

if (empty($target) || empty($transcript)) {
    echo json_encode(["error" => "Missing target or transcript."]);
    exit;
}

$prompt = "You are an expert Arabic phonetic evaluator.
Target sentence the student was supposed to say: \"$target\"
What the speech-to-text algorithm heard the student say: \"$transcript\"

Analyze the pronunciation accuracy. Return ONLY a valid JSON object with:
{
    \"score\": [Number 0-100],
    \"feedback\": \"[Short encouraging message, pointing out any mispronounced words]\",
    \"mispronounced_words\": [Array of words from the target sentence they got wrong]
}";

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
    $aiResult = json_decode(trim($clean), true);
    
    if (!$aiResult) {
        echo json_encode(["error" => "Could not evaluate pronunciation."]);
        exit;
    }
    
    $score = $aiResult['score'] ?? 0;
    
    // Award XP
    $xp = 0;
    if ($score >= 80) $xp = 30;
    elseif ($score >= 50) $xp = 15;
    
    $correct = ($score >= 60) ? 1 : 0;
    $wrong = ($score < 60) ? 1 : 0;
    
    saveUserProgress($pdo, $_SESSION['user_id'], $xp, 'pronunciation', $correct, $wrong);
    
    echo json_encode([
        "score" => $score,
        "feedback" => $aiResult['feedback'] ?? '',
        "mispronounced" => $aiResult['mispronounced_words'] ?? [],
        "xp_earned" => $xp
    ]);
} else {
    echo json_encode(["error" => "Speech evaluation service resting."]);
}
?>
