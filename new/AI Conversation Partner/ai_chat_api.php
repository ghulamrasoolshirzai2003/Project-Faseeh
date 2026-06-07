<?php
// ai_chat_api.php — Backend API for AI Conversation Partner
// Called via fetch() from ai_chat.php
// Uses Anthropic Claude API

session_start();
header('Content-Type: application/json');

// ── Auth check ────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// ── Config ────────────────────────────────────────────────────────────────
// Store your API key in an env var or a config file outside webroot
// NEVER hard-code API keys in PHP files committed to git
$api_key = getenv('ANTHROPIC_API_KEY') ?: (
    file_exists(__DIR__ . '/../.env')
        ? (function() {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with($line, 'ANTHROPIC_API_KEY=')) {
                    return trim(explode('=', $line, 2)[1]);
                }
            }
            return null;
          })()
        : null
);

if (!$api_key) {
    echo json_encode(['error' => 'API key not configured. Set ANTHROPIC_API_KEY in your environment.']);
    exit;
}

// ── Parse request ─────────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

$scenario = htmlspecialchars($body['scenario'] ?? 'market');
$history  = $body['history']  ?? [];
$system   = $body['system']   ?? 'You are a helpful Arabic language tutor.';

// Sanitize history — only allow role + content
$messages = [];
foreach ($history as $msg) {
    $role    = in_array($msg['role'] ?? '', ['user','assistant']) ? $msg['role'] : 'user';
    $content = mb_substr(strip_tags($msg['content'] ?? ''), 0, 2000); // max 2000 chars per turn
    if ($content) $messages[] = ['role' => $role, 'content' => $content];
}

// Limit history to last 20 messages to control token usage
$messages = array_slice($messages, -20);

// ── Call Anthropic API ────────────────────────────────────────────────────
$payload = json_encode([
    'model'      => 'claude-opus-4-6',
    'max_tokens' => 600,
    'system'     => $system,
    'messages'   => $messages,
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $api_key,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response   = curl_exec($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo json_encode(['error' => 'Network error: ' . $curl_error]);
    exit;
}

$data = json_decode($response, true);

if ($http_code !== 200) {
    $err = $data['error']['message'] ?? 'API error (HTTP ' . $http_code . ')';
    echo json_encode(['error' => $err]);
    exit;
}

// ── Extract reply text ────────────────────────────────────────────────────
$reply = '';
foreach (($data['content'] ?? []) as $block) {
    if ($block['type'] === 'text') {
        $reply .= $block['text'];
    }
}

// ── Optionally save conversation turn to DB ───────────────────────────────
// Uncomment and adapt to your DB setup:
/*
$user_id = (int)$_SESSION['user_id'];
$pdo = require 'db.php'; // your PDO connection
$stmt = $pdo->prepare("INSERT INTO ai_conversations (user_id, scenario, role, content, created_at)
                        VALUES (?, ?, 'assistant', ?, NOW())");
$stmt->execute([$user_id, $scenario, mb_substr($reply, 0, 2000)]);
*/

echo json_encode(['reply' => $reply]);
