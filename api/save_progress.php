<?php
require __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$xp_gained = (int)($input['xp'] ?? 0);
$mode = $input['mode'] ?? 'general';
$correct = (int)($input['correct'] ?? 0);
$wrong = (int)($input['wrong'] ?? 0);

require_once __DIR__ . '/../includes/progress.php';

try {
    saveUserProgress($pdo, $user_id, $xp_gained, $mode, $correct, $wrong);

    // Activity Log for Shouts
    if ($mode === 'word_sprint_shout') {
        $username = $_SESSION['username'] ?? 'A student';
        $pdo->prepare("INSERT INTO activity_log (user_id, username, activity_type, description) VALUES (?, ?, 'sprint', 'just smashed a ⚡ Word Sprint high score!')")
            ->execute([$user_id, $username]);
    }

    echo json_encode(['status' => 'success', 'xp_added' => $xp_gained]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
