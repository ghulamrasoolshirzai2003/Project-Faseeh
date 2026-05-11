<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { echo json_encode(["error" => "Invalid data"]); exit; }

$userId = $_SESSION['user_id'];
$mode = $data['mode'] ?? '';
$questionId = $data['question_id'] ?? 0;
$isCorrect = $data['is_correct'] ?? false;
$points = $data['points'] ?? 10;

try {
    // 1. Log that the user saw/answered this question to prevent repeats
    if ($mode && $questionId) {
        $stmt = $pdo->prepare("INSERT INTO user_answered (user_id, mode, question_id) VALUES (?, ?, ?) ON CONFLICT DO NOTHING");
        $stmt->execute([$userId, $mode, $questionId]);
    }

    // Create academic stats tracking table if not exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS academic_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            mode VARCHAR(50),
            correct_answers INT DEFAULT 0,
            wrong_answers INT DEFAULT 0,
            UNIQUE KEY(user_id, mode)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch(Exception $e){}

    // CRITICAL FIX: Ensure record exists in main progress table before updating
    $check = $pdo->prepare("SELECT id FROM progress WHERE user_id = ?");
    $check->execute([$userId]);
    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak, longest_streak, wins, losses, mcq_wins, mcq_losses, accuracy_total, accuracy_correct, points_lost, attempts) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)")->execute([$userId]);
    }

    // 2. Award XP and Sync with Main Stats
    $today = date('Y-m-d');
    if ($isCorrect) {
        // Update main progress (Score, XP, Accuracy, Academic Counter)
        $stmt = $pdo->prepare("UPDATE progress SET 
            total_score = total_score + ?, 
            xp = xp + ?, 
            accuracy_total = accuracy_total + 1, 
            accuracy_correct = accuracy_correct + 1,
            academic_correct_count = academic_correct_count + 1 
            WHERE user_id = ?");
        $stmt->execute([$points, $points, $userId]);

        // Update Academic Report
        $stmt = $pdo->prepare("INSERT INTO academic_stats (user_id, mode, correct_answers) VALUES (?, ?, 1) ON CONFLICT (user_id, mode) DO UPDATE SET correct_answers = academic_stats.correct_answers + 1");
        $stmt->execute([$userId, $mode]);

        // Update Daily Goal
        $pdo->prepare("INSERT INTO daily_goals (user_id, goal_date, words_completed, xp_earned) VALUES (?, ?, 1, ?) ON CONFLICT (user_id, goal_date) DO UPDATE SET words_completed = daily_goals.words_completed + 1, xp_earned = daily_goals.xp_earned + ?")
            ->execute([$userId, $today, $points, $points]);
    } else {
        // Update Accuracy (Total only) even for wrong answers
        $stmt = $pdo->prepare("UPDATE progress SET accuracy_total = accuracy_total + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Update Academic Report
        $stmt = $pdo->prepare("INSERT INTO academic_stats (user_id, mode, wrong_answers) VALUES (?, ?, 1) ON CONFLICT (user_id, mode) DO UPDATE SET wrong_answers = academic_stats.wrong_answers + 1");
        $stmt->execute([$userId, $mode]);
    }

    // 3. Update active session tracking
    if ($mode) {
        $stmt = $pdo->prepare("UPDATE user_active_sessions SET questions_completed = questions_completed + 1 WHERE user_id = ? AND mode = ?");
        $stmt->execute([$userId, $mode]);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
