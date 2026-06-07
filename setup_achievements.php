<?php
require 'includes/db.php';

try {
    // 1. Create achievements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(50),
        requirement_type VARCHAR(50), -- 'streak', 'xp', 'words', 'accuracy'
        requirement_value INT,
        xp_reward INT DEFAULT 50,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create user_achievements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        achievement_id INT,
        earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_earn (user_id, achievement_id)
    )");

    // 3. Seed some initial achievements
    $achievements = [
        ['Early Bird', 'Complete a lesson before 8 AM', '🌅', 'early_bird', 1, 50],
        ['Night Owl', 'Complete a lesson after 10 PM', '🦉', 'night_owl', 1, 50],
        ['Streak Starter', 'Maintain a 3-day streak', '🔥', 'streak', 3, 100],
        ['Streak Master', 'Maintain a 7-day streak', '🦁', 'streak', 7, 250],
        ['XP Rookie', 'Reach 100 total XP', '🥉', 'xp', 100, 50],
        ['XP Veteran', 'Reach 1,000 total XP', '🥈', 'xp', 1000, 200],
        ['XP Legend', 'Reach 5,000 total XP', '🥇', 'xp', 5000, 500],
        ['Word Collector', 'Learn 50 unique words', '📚', 'words', 50, 150],
        ['Perfect Score', 'Get 100% accuracy in a quiz', '🎯', 'accuracy', 100, 50],
        ['Socialite', 'Visit the leaderboard 5 times', '👥', 'visits', 5, 30]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO achievements (title, description, icon, requirement_type, requirement_value, xp_reward) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($achievements as $ach) {
        $stmt->execute($ach);
    }

    echo "✅ Achievement System Database Ready!";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
