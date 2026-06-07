<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/achievement_checker.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$result = isset($data['result']) ? $data['result'] : 'lose';
$time_taken = isset($data['time_taken']) ? (int)$data['time_taken'] : 30;
$uid = $_SESSION['user_id'];

try {
    // 1. Check if user exists in progress table, if not, create them.
    $check = $pdo->prepare("SELECT id FROM progress WHERE user_id = ?");
    $check->execute([$uid]);
    if ($check->rowCount() == 0) {
        $init = $pdo->prepare("INSERT INTO progress (user_id, wins, losses, current_streak, total_score, xp) VALUES (?, 0, 0, 0, 0, 0)");
        $init->execute([$uid]);
    }

    // 2. Calculate Score (Win or Lose)
    if ($result === 'win') {
        // WIN: Base 100 + Speed Bonus
        $score_gained = 100 + max(0, (30 - $time_taken) * 2);

        $stmt = $pdo->prepare("UPDATE progress SET wins = wins + 1, current_streak = current_streak + 1, total_score = total_score + ?, xp = xp + ? WHERE user_id = ?");
        $stmt->execute([$score_gained, $score_gained, $uid]);
        
    } else {
        // LOSE: Penalty of 20 Points (But don't go below 0)
        $stmt = $pdo->prepare("UPDATE progress SET losses = losses + 1, current_streak = 0, total_score = GREATEST(0, total_score - 20), xp = GREATEST(0, xp - 20) WHERE user_id = ?");
        $stmt->execute([$uid]);
    }

    // 3. Return New Stats
    $stmt = $pdo->prepare("SELECT wins, losses, current_streak, total_score, xp FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "stats" => $stats]);

    // 4. Check for achievements
    checkAchievements($pdo, $uid);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>