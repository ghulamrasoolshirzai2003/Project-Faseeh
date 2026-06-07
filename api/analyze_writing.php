<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$text = $data['text'] ?? '';

if (empty(trim($text))) {
    echo json_encode(['status' => 'error', 'message' => 'Please write something first!']);
    exit;
}

$geminiKey = GEMINI_API_KEY;
$openRouterKey = OPENROUTER_API_KEY;

$prompt = "You are Professor Faseeh, a PhD Arabic tutor. Analyze the following student writing: \"$text\". 
Return ONLY a JSON object: {\"status\": \"success\" or \"warning\", \"message\": \"Your feedback\"}";

// --- TRY GEMINI (PRIMARY) ---
$result = callAI($prompt, "gemini", $geminiKey);

// --- FALLBACK TO OPENROUTER ---
if (!$result) {
    $result = callAI($prompt, "openrouter", $openRouterKey);
}

if ($result) {
    echo $result;
} else {
    // Show a user-friendly error message
    echo json_encode(['status' => 'error', 'message' => "Professor Faseeh is resting right now. Please try again in a moment."]);
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, $provider == "gemini" ? ['Content-Type: application/json'] : ['Content-Type: application/json', 'Authorization: Bearer ' . $key]);
    
    // --- CRITICAL FIXES FOR SHARED HOSTING ---
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http === 200) {
        $data = json_decode($response, true);
        $raw = ($provider == "gemini") ? ($data['candidates'][0]['content']['parts'][0]['text'] ?? '') : ($data['choices'][0]['message']['content'] ?? '');
        $clean = preg_replace('/```json|```/', '', $raw);
        return trim($clean);
    } else {
        $_SESSION['last_ai_error'] = "$provider failed (Code $http): $error";
    }
    return null;
}
?>
