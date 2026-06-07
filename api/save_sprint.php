<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$uid = $_SESSION['user_id'];
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$isCorrect = isset($data['correct']) && $data['correct'] > 0;
$xp = $isCorrect ? 5 : 0;
$today = date('Y-m-d');

try {
    // 1. Update Main Progress (INCLUDING GLOBAL ACCURACY)
    $acc_corr = $isCorrect ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE progress SET 
        xp = xp + ?, 
        total_score = total_score + ?, 
        accuracy_total = accuracy_total + 1,
        accuracy_correct = accuracy_correct + ?,
        last_active = NOW() 
        WHERE user_id = ?");
    $stmt->execute([$xp, $xp, $acc_corr, $uid]);

    // 2. Update Daily Goals
    if ($isCorrect) {
        $pdo->prepare("INSERT INTO daily_goals (user_id, goal_date, words_completed, xp_earned) VALUES (?, ?, 1, 5) 
                       ON DUPLICATE KEY UPDATE words_completed = words_completed + 1, xp_earned = xp_earned + 5")
            ->execute([$uid, $today]);
            
        // 2b. Add to user_xp_history (to track daily/weekly progress charts)
        $pdo->prepare("INSERT INTO user_xp_history (user_id, xp_gained, progress_date) VALUES (?, 5, ?)")
            ->execute([$uid, $today]);
    }

    // 3. Update Academic Stats
    $acad_col = $isCorrect ? 'correct_answers' : 'wrong_answers';
    $pdo->prepare("INSERT INTO academic_stats (user_id, mode, $acad_col) VALUES (?, 'word_sprint', 1) 
                   ON DUPLICATE KEY UPDATE $acad_col = $acad_col + 1")
        ->execute([$uid]);

    echo json_encode(['status' => 'success', 'xp' => $xp]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
