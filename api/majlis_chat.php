<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/progress.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$scenario = $input['scenario'] ?? 'market';
$history = $input['history'] ?? []; // Optional: for future multi-turn support
$uid = $_SESSION['user_id'];

if (empty($message)) {
    echo json_encode(['error' => 'Empty message']);
    exit;
}

// --- AI CONFIGURATION ---
$geminiKey = GEMINI_API_KEY;
$openRouterKey = OPENROUTER_API_KEY;

// --- SCENARIO PROMPTS ---
$scenarios = [
    'market' => [
        'persona' => 'A friendly spice merchant (Atar) in a bustling Cairo market.',
        'task' => 'The user wants to buy high-quality saffron for their mother.',
        'starter' => 'السلام عليكم! أهلاً بك في دكاني المتواضع. هل تبحث عن الزعفران اليوم؟'
    ],
    'oasis' => [
        'persona' => 'A wise desert nomad resting by an oasis.',
        'task' => 'The user is lost and needs directions to the Golden City.',
        'starter' => 'تفضل يا ولدي، اشرب بعض الشاي. الصحراء واسعة وخطيرة، إلى أين تتجه؟'
    ],
    'library' => [
        'persona' => 'The head librarian of the House of Wisdom in Baghdad.',
        'task' => 'The user wants to find a rare manuscript about Arabic grammar.',
        'starter' => 'مرحباً بك في بيت الحكمة. العلم نور، والكتب هي الطريق. أي كتاب تطلب اليوم؟'
    ]
];

$activeScenario = $scenarios[$scenario] ?? $scenarios['market'];

// --- SYSTEM PROMPT ---
$systemPrompt = "You are an AI Arabic Tutor acting in a roleplay scenario. 
Persona: {$activeScenario['persona']}
Context: {$activeScenario['task']}
Rule 1: Always respond in high-quality Arabic first.
Rule 2: Keep the roleplay immersive.
Rule 3: After your Arabic response, provide an English translation/hint in brackets [like this].
Rule 4: Keep responses relatively short (1-3 sentences) to keep the chat flowing.
User's message: ";

// --- EXECUTION ENGINE (PRIMARY: GEMINI) ---
$responseBody = callGemini($message, $systemPrompt, $geminiKey);

// --- FALLBACK ENGINE (SECONDARY: OPENROUTER) ---
if (!$responseBody) {
    $responseBody = callOpenRouter($message, $systemPrompt, $openRouterKey);
}

// --- FINAL FALLBACK: SIMULATOR ---
if (!$responseBody) {
    $sim = simulateResponse($message, $scenario);
    $responseBody = ['text' => $sim['text'], 'hint' => $sim['hint']];
}

// --- PERSISTENCE & XP ---
saveUserProgress($pdo, $uid, 10, 'majlis', 1, 0);

// --- OUTPUT ---
echo json_encode([
    'response' => $responseBody['text'],
    'hint' => $responseBody['hint'] ?? '',
    'xp_earned' => 10
]);

/**
 * Call Google Gemini API
 */
function callGemini($userMsg, $systemPrompt, $key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;
    
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $systemPrompt . $userMsg]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 200
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $fullText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return parseAIResponse($fullText);
    }
    return null;
}

/**
 * Call OpenRouter API (Fallback)
 */
function callOpenRouter($userMsg, $systemPrompt, $key) {
    $url = "https://openrouter.ai/api/v1/chat/completions";
    
    $payload = [
        "model" => "google/gemini-flash-1.5", // Using Gemini through OpenRouter as fallback
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userMsg]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key,
        'HTTP-Referer: https://faseeh.great-site.net',
        'X-Title: Faseeh Academy'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $fullText = $result['choices'][0]['message']['content'] ?? '';
        return parseAIResponse($fullText);
    }
    return null;
}

/**
 * Splits AI text into [Arabic] and [Hint]
 */
function parseAIResponse($text) {
    if (preg_match('/(.*?)\[(.*?)\]/s', $text, $matches)) {
        return ['text' => trim($matches[1]), 'hint' => trim($matches[2])];
    }
    return ['text' => $text, 'hint' => ''];
}

/**
 * Emergency Fallback Simulator
 */
function simulateResponse($msg, $scen) {
    $msg = mb_strtolower($msg);
    if (strpos($msg, 'زعفران') !== false) return ['text' => 'أهلاً بك! الزعفران لدينا هو الأفضل.', 'hint' => 'Welcome! Our saffron is the best.'];
    return ['text' => 'مرحباً بك في مجلسنا.', 'hint' => 'Welcome to our council.'];
}
?>
