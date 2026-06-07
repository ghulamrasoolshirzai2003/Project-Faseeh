<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/progress.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$letter = trim($data['letter'] ?? '');
$imageBase64 = $data['image'] ?? '';

if (empty($letter) || empty($imageBase64)) {
    echo json_encode(["error" => "Missing letter or image."]);
    exit;
}

// Remove data URI prefix
if (strpos($imageBase64, ',') !== false) {
    $imageBase64 = explode(',', $imageBase64)[1];
}

$prompt = "You are Professor Faseeh, an expert Arabic calligrapher and teacher. The provided image is a student's attempt at hand-drawing the Arabic letter '$letter'.
Analyze the drawing. Does it legibly resemble the letter '$letter'?
Return ONLY a valid JSON object matching this structure:
{
    \"score\": [Number from 0 to 100 representing accuracy, legibility, and shape],
    \"feedback\": \"[A short, encouraging sentence evaluating their stroke]\"
}";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => "image/png",
                        "data" => $imageBase64
                    ]
                ]
            ]
        ]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http === 200) {
    $resData = json_decode($response, true);
    $raw = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Extract only the JSON block between curly braces
    if (preg_match('/\{.*\}/s', $raw, $matches)) {
        $clean = $matches[0];
    } else {
        $clean = preg_replace('/```(json)?/', '', $raw);
    }
    
    $aiResult = json_decode(trim($clean), true);
    
    if (!$aiResult) {
        echo json_encode(["error" => "Could not evaluate calligraphy formatting."]);
        exit;
    }
    
    $score = $aiResult['score'] ?? 0;
    $feedback = $aiResult['feedback'] ?? "Keep practicing!";
    
    $xp = 0;
    if ($score >= 80) $xp = 25;
    elseif ($score >= 50) $xp = 10;
    
    $correct = ($score >= 60) ? 1 : 0;
    $wrong = ($score < 60) ? 1 : 0;
    
    saveUserProgress($pdo, $_SESSION['user_id'], $xp, 'writing_canvas', $correct, $wrong);
    
    echo json_encode([
        "score" => $score,
        "feedback" => $feedback,
        "xp_earned" => $xp
    ]);
} else {
    echo json_encode(["error" => "Professor Faseeh is resting. Please try again."]);
}
?>
