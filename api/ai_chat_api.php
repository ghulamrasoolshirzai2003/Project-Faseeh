<?php
// ai_chat_api.php — Backend API for AI Conversation Partner
// Updated to use existing Gemini/OpenRouter keys from the project
session_start();
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

$scenario = htmlspecialchars($body['scenario'] ?? 'market');
$history  = $body['history']  ?? [];
$system   = $body['system']   ?? 'You are a helpful Arabic language tutor.';

// Keys from analyze_writing.php
$geminiKey = GEMINI_API_KEY;
$openRouterKey = OPENROUTER_API_KEY;

// Format history for Gemini/OpenRouter
$messages = [];
foreach ($history as $msg) {
    $role = ($msg['role'] === 'assistant') ? 'assistant' : 'user';
    $messages[] = ['role' => $role, 'content' => $msg['content']];
}

$prompt = "SYSTEM: $system\n\nCONVERSATION HISTORY:\n";
foreach($messages as $m) {
    $prompt .= strtoupper($m['role']) . ": " . $m['content'] . "\n";
}
$prompt .= "\nRespond in the required format: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or 'None']";

// --- TRY GEMINI (PRIMARY) ---
$reply = callAI($prompt, "gemini", $geminiKey);

// --- FALLBACK TO OPENROUTER ---
if (!$reply) {
    $reply = callAI($prompt, "openrouter", $openRouterKey);
}

if ($reply) {
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['error' => 'The AI tutor is currently unavailable. Please try again later.']);
}

function callAI($prompt, $provider, $key) {
    if ($provider == "gemini") {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;
        $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];
    } else {
        $url = "https://openrouter.ai/api/v1/chat/completions";
        $payload = ["model" => "google/gemini-flash-1.5", "messages" => [["role" => "user", "content" => $prompt]]];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', ($provider == "openrouter" ? 'Authorization: Bearer ' . $key : '')]);
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http === 200) {
        $data = json_decode($response, true);
        if ($provider == "gemini") {
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } else {
            return $data['choices'][0]['message']['content'] ?? null;
        }
    }
    return null;
}
