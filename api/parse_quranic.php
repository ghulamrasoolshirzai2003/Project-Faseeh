<?php
// api/parse_quranic.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$sentence = trim($data['sentence'] ?? '');

if (empty($sentence)) {
    echo json_encode(["error" => "Please enter an Arabic sentence or verse to parse."]);
    exit;
}

$prompt = "You are an expert Classical Arabic Grammarian and Quranic Morphological Analyst.
Analyze the following Arabic sentence word-by-word. Break down every single word/token in order.
Sentence: \"$sentence\"

Return ONLY a valid JSON array of objects. Do NOT wrap in markdown formatting block or anything else.
Format structure:
[
    {
        \"word\": \"[The exact Arabic word]\",
        \"transliteration\": \"[English phonetics representation]\",
        \"root\": \"[The 3-letter Arabic root origin or 'None' if particle]\",
        \"type\": \"[Noun (اسم) / Verb (فعل) / Particle (حرف)]\",
        \"state\": \"[Nominative (مرفوع) / Accusative (منصوب) / Genitive (مجرور) / Jussive (مجزوم) / Indeclinable (مبني)]\",
        \"weight\": \"[The morphological scale weight, e.g. فَاعِل, مَفْعُول, or 'None']\",
        \"meaning\": \"[Concise contextual English translation]\",
        \"explanation\": \"[A short 1-sentence breakdown of the grammatical role in the sentence]\"
    }
]";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
$payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

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
    
    // Clean potential markdown wrappers
    if (preg_match('/\[.*\]/s', $raw, $matches)) {
        $clean = $matches[0];
    } elseif (preg_match('/\{.*\}/s', $raw, $matches)) {
        $clean = $matches[0];
    } else {
        $clean = preg_replace('/```(json)?/', '', $raw);
    }
    
    $parsed = json_decode(trim($clean), true);
    
    if (!$parsed) {
        echo json_encode(["error" => "Could not parse sentence. Please try again with a cleaner text."]);
        exit;
    }
    
    echo json_encode([
        "success" => true,
        "tokens" => $parsed
    ]);
} else {
    echo json_encode(["error" => "AI Grammatical Service currently resting. Please try again."]);
}
?>
