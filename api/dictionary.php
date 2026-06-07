<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$word = $_GET['word'] ?? '';
if (empty($word)) {
    echo json_encode(['error' => 'No word provided']);
    exit;
}

$geminiKey = GEMINI_API_KEY;
$openRouterKey = OPENROUTER_API_KEY;

$prompt = "Analyze the Arabic word: \"$word\". Provide its linguistic Root (3 letters), its Primary English Meaning, and its Grammar (Noun/Verb/Participle). 
Return ONLY a JSON object: {\"root\": \"...\", \"mean\": \"...\", \"grammar\": \"...\"}";

$result = callAI($prompt, "gemini", $geminiKey);

if (!$result) {
    $result = callAI($prompt, "openrouter", $openRouterKey);
}

if ($result) {
    echo $result;
} else {
    echo json_encode(['root' => 'N/A', 'mean' => 'Click for details', 'grammar' => 'Unknown']);
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
    
    // --- HOSTING FIXES ---
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http === 200) {
        $data = json_decode($response, true);
        $raw = ($provider == "gemini") ? ($data['candidates'][0]['content']['parts'][0]['text'] ?? '') : ($data['choices'][0]['message']['content'] ?? '');
        
        // Extract only the JSON block between curly braces
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            return $matches[0];
        }
        return preg_replace('/```(json)?/', '', $raw);
    }
    return null;
}
?>
