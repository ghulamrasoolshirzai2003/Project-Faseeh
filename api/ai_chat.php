<?php
// api/ai_chat.php — Backend API for AI Conversation Partner
session_start();
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$history = $data['history'] ?? [];
$system  = $data['system']  ?? 'You are a PhD Arabic tutor. Conduct a natural conversation in Arabic.';

// GEMINI CONFIG
$geminiKey = GEMINI_API_KEY;

// Format history for Gemini
$contents = [
    ['role' => 'user', 'parts' => [['text' => "SYSTEM INSTRUCTIONS: $system"]]],
];

foreach ($history as $msg) {
    $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => $msg['content']]]
    ];
}

$payload = json_encode(['contents' => $contents]);

// --- CALL GEMINI ---
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$geminiKey");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $res = json_decode($response, true);
    $reply = $res['candidates'][0]['content']['parts'][0]['text'] ?? '';
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['error' => 'AI Tutor is resting. Please try again in a moment.']);
}
?>
