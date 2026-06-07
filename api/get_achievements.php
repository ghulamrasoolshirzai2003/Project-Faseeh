<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { echo json_encode(['error' => 'Not logged in']); exit; }

try {
    // --- AUTO-INSTALLER: Tables ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100),
        description TEXT,
        icon VARCHAR(50),
        requirement_type VARCHAR(50),
        requirement_value INT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_achievements (
        user_id INT,
        achievement_id INT,
        unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, achievement_id)
    )");

    // --- AUTO-INSTALLER: Seed achievements if empty ---
    $count = $pdo->query("SELECT COUNT(*) FROM achievements")->fetchColumn();
    if ($count == 0) {
        $seeds = [
            ['First Steps', 'Win your first game', '🌱', 'wins', 1],
            ['Hangman Pro', 'Win 10 Hangman games', '🎯', 'hangman_wins', 10],
            ['XP Collector', 'Reach 500 total XP', '✨', 'xp', 500],
            ['Daily Habit', 'Maintain a 3-day streak', '🔥', 'streak', 3],
            ['Polyglot', 'Master 20 words', '📚', 'words_mastered', 20],
            ['Grammar King', 'Complete 5 Sentence Builder challenges', '🧩', 'sentence_wins', 5]
        ];
        $stmt = $pdo->prepare("INSERT INTO achievements (title, description, icon, requirement_type, requirement_value) VALUES (?, ?, ?, ?, ?)");
        foreach ($seeds as $s) $stmt->execute($s);
    }

    // Fetch user stats
    $stmt = $pdo->prepare("SELECT xp, total_score as wins FROM progress WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();
    $xp = $stats['xp'] ?? 0;
    $wins = $stats['wins'] ?? 0;

    // Get all achievements
    $all = $pdo->query("SELECT * FROM achievements")->fetchAll();
    
    // Get already earned
    $stmt = $pdo->prepare("SELECT achievement_id FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $earned_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $earned = [];
    $locked = [];

    foreach ($all as $a) {
        $is_earned = in_array($a['id'], $earned_ids);
        
        // Auto-award logic
        if (!$is_earned) {
            $award = false;
            if ($a['requirement_type'] == 'xp' && $xp >= $a['requirement_value']) $award = true;
            if ($a['requirement_type'] == 'wins' && $wins >= $a['requirement_value']) $award = true;
            
            if ($award) {
                $pdo->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)")->execute([$user_id, $a['id']]);
                $is_earned = true;
            }
        }
        
        $item = [
            'id' => $a['id'],
            'title' => $a['title'],
            'description' => $a['description'],
            'icon' => $a['icon']
        ];

        if ($is_earned) {
            $earned[] = $item;
        } else {
            $locked[] = $item;
        }
    }

    echo json_encode(['earned' => $earned, 'locked' => $locked]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
