<?php
/**
 * API: Get admin analytics data for charts
 * Returns: daily active users, word difficulty, game type stats, etc.
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // 1. Daily Active Users (last 30 days)
    $dau = $pdo->query("
        SELECT DATE(played_at) as date, COUNT(DISTINCT user_id) as users
        FROM game_sessions
        WHERE played_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(played_at)
        ORDER BY date
    ")->fetchAll();

    // 2. Games played per type
    $gameTypes = $pdo->query("
        SELECT game_type, COUNT(*) as total,
               SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins
        FROM game_sessions
        GROUP BY game_type
    ")->fetchAll();

    // 3. Hardest words (most failures)
    $hardWords = $pdo->query("
        SELECT w.arabic_word, w.meaning_en, w.level,
               COUNT(*) as attempts,
               SUM(CASE WHEN gs.result = 'lose' THEN 1 ELSE 0 END) as failures
        FROM game_sessions gs
        JOIN words w ON gs.word_id = w.id
        GROUP BY gs.word_id
        ORDER BY failures DESC
        LIMIT 10
    ")->fetchAll();

    // 4. Registrations per day (last 30 days)
    $registrations = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as signups
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND role != 'admin'
        GROUP BY DATE(created_at)
        ORDER BY date
    ")->fetchAll();

    // 5. Level distribution
    $levelDist = $pdo->query("
        SELECT selected_level as level, COUNT(*) as count
        FROM users WHERE role != 'admin'
        GROUP BY selected_level
    ")->fetchAll();

    // 6. Overall stats
    $totalUsers = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE role != 'admin'")->fetch()['cnt'];
    $totalGames = $pdo->query("SELECT COUNT(*) as cnt FROM game_sessions")->fetch()['cnt'];
    $todayActive = $pdo->query("SELECT COUNT(DISTINCT user_id) as cnt FROM game_sessions WHERE DATE(played_at) = CURDATE()")->fetch()['cnt'];
    $avgScore = $pdo->query("SELECT AVG(total_score) as avg FROM progress")->fetch()['avg'];

    echo json_encode([
        'dau' => $dau,
        'game_types' => $gameTypes,
        'hard_words' => $hardWords,
        'registrations' => $registrations,
        'level_distribution' => $levelDist,
        'summary' => [
            'total_users' => (int)$totalUsers,
            'total_games' => (int)$totalGames,
            'today_active' => (int)$todayActive,
            'avg_score' => round((float)$avgScore)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
