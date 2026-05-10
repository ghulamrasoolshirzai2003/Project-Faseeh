<?php
/**
 * API: Get user stats for the dashboard
 * Returns: score, xp, streaks, accuracy, words learned, achievements, daily goal
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];

try {
    // 1. Basic progress stats
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $progress = $stmt->fetch();

    if (!$progress) {
        $progress = [
            'total_score' => 0, 'xp' => 0, 'current_streak' => 0,
            'daily_streak' => 0, 'longest_streak' => 0,
            'wins' => 0, 'losses' => 0, 'mcq_wins' => 0, 'mcq_losses' => 0,
            'total_words_learned' => 0, 'accuracy_total' => 0, 'accuracy_correct' => 0
        ];
    }

    // 2. Words learned count
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $wordsLearned = $stmt->fetch()['cnt'] ?? 0;

    // 3. Total words available per level
    $stmt = $pdo->prepare("
        SELECT level, COUNT(*) as total,
               (SELECT COUNT(*) FROM user_progress up 
                JOIN words w2 ON up.word_id = w2.id 
                WHERE up.user_id = ? AND w2.level = words.level) as completed
        FROM words 
        GROUP BY level
    ");
    $stmt->execute([$uid]);
    $levelProgress = $stmt->fetchAll();

    // 4. Accuracy
    $totalAttempts = ($progress['accuracy_total'] ?? 0);
    $correctAttempts = ($progress['accuracy_correct'] ?? 0);
    $accuracy = $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100) : 0;

    // 5. Unlocked achievements
    $stmt = $pdo->prepare("
        SELECT a.slug, a.title, a.description, a.icon, a.xp_reward, ua.unlocked_at
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.id
        WHERE ua.user_id = ?
        ORDER BY ua.unlocked_at DESC
    ");
    $stmt->execute([$uid]);
    $achievements = $stmt->fetchAll();

    // 6. Total achievements available
    $totalAchievements = $pdo->query("SELECT COUNT(*) as cnt FROM achievements")->fetch()['cnt'];

    // 7. Daily goal
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM daily_goals WHERE user_id = ? AND goal_date = ?");
    $stmt->execute([$uid, $today]);
    $dailyGoal = $stmt->fetch();

    if (!$dailyGoal) {
        // Auto-create today's goal
        $pdo->prepare("INSERT IGNORE INTO daily_goals (user_id, goal_date, words_target) VALUES (?, ?, 5)")
            ->execute([$uid, $today]);
        $dailyGoal = ['words_target' => 5, 'words_completed' => 0, 'xp_earned' => 0, 'completed' => 0];
    }

    // 8. Review words due
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM review_queue WHERE user_id = ? AND next_review <= ?");
    $stmt->execute([$uid, $today]);
    $reviewsDue = $stmt->fetch()['cnt'] ?? 0;

    // 9. Recent activity (last 7 days)
    $stmt = $pdo->prepare("
        SELECT DATE(played_at) as play_date, 
               COUNT(*) as games_played,
               SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
               SUM(score_change) as score_gained
        FROM game_sessions 
        WHERE user_id = ? AND played_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(played_at)
        ORDER BY play_date DESC
    ");
    $stmt->execute([$uid]);
    $weeklyActivity = $stmt->fetchAll();

    // 10. User info
    $stmt = $pdo->prepare("SELECT username, full_name, selected_level, created_at FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();

    echo json_encode([
        'user' => $user,
        'stats' => [
            'total_score' => (int)($progress['total_score'] ?? 0),
            'xp' => (int)($progress['xp'] ?? 0),
            'current_streak' => (int)($progress['current_streak'] ?? 0),
            'daily_streak' => (int)($progress['daily_streak'] ?? 0),
            'longest_streak' => (int)($progress['longest_streak'] ?? 0),
            'wins' => (int)($progress['wins'] ?? 0),
            'losses' => (int)($progress['losses'] ?? 0),
            'mcq_wins' => (int)($progress['mcq_wins'] ?? 0),
            'mcq_losses' => (int)($progress['mcq_losses'] ?? 0),
            'words_learned' => (int)$wordsLearned,
            'accuracy' => $accuracy,
        ],
        'level_progress' => $levelProgress,
        'achievements' => $achievements,
        'total_achievements' => (int)$totalAchievements,
        'daily_goal' => $dailyGoal,
        'reviews_due' => (int)$reviewsDue,
        'weekly_activity' => $weeklyActivity,
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
