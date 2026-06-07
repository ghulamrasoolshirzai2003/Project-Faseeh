<?php
/**
 * ============================================================
 * FASEEH v2.0 — DATABASE MIGRATION
 * ============================================================
 * Run this once to add all new tables and columns needed
 * for the v2 upgrade (achievements, daily streak, MCQ, etc.)
 *
 * Usage: Visit /migrate.php in the browser, then DELETE this file.
 * ============================================================
 */
require 'includes/db.php';

$results = [];

// Helper function
function runSQL($pdo, $desc, $sql) {
    global $results;
    try {
        $pdo->exec($sql);
        $results[] = "✅ $desc";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            $results[] = "⏭️ $desc (already exists)";
        } else {
            $results[] = "❌ $desc — " . $e->getMessage();
        }
    }
}

// =========================================================
// 0. Ensure 'users' table has v2.0 columns
// =========================================================
runSQL($pdo, "Ensure users table has email", 
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE AFTER username");

runSQL($pdo, "Ensure users table has full_name", 
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) AFTER id");

runSQL($pdo, "Ensure users table has selected_level", 
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS selected_level VARCHAR(20) DEFAULT 'beginner' AFTER password");

runSQL($pdo, "Ensure users table has role", 
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('student','admin') DEFAULT 'student' AFTER selected_level");

runSQL($pdo, "Expand password column for security", 
    "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");

// =========================================================
// 1. Add new columns to 'progress' table
// =========================================================
runSQL($pdo, "Add daily_streak to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS daily_streak INT DEFAULT 0 AFTER current_streak");

runSQL($pdo, "Add last_play_date to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS last_play_date DATE DEFAULT NULL AFTER last_active");

runSQL($pdo, "Add longest_streak to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS longest_streak INT DEFAULT 0 AFTER daily_streak");

runSQL($pdo, "Add total_words_learned to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS total_words_learned INT DEFAULT 0 AFTER longest_streak");

runSQL($pdo, "Add xp (experience points) to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS xp INT DEFAULT 0 AFTER total_score");

runSQL($pdo, "Add accuracy_total to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS accuracy_total INT DEFAULT 0 AFTER xp");

runSQL($pdo, "Add accuracy_correct to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS accuracy_correct INT DEFAULT 0 AFTER accuracy_total");

runSQL($pdo, "Add mcq_wins to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS mcq_wins INT DEFAULT 0 AFTER wins");

runSQL($pdo, "Add mcq_losses to progress", 
    "ALTER TABLE progress ADD COLUMN IF NOT EXISTS mcq_losses INT DEFAULT 0 AFTER mcq_wins");

// =========================================================
// 2. Add 'category' column to 'words' table
// =========================================================
runSQL($pdo, "Add category to words", 
    "ALTER TABLE words ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'general' AFTER level");

// =========================================================
// 3. Create achievements table
// =========================================================
runSQL($pdo, "Create achievements table", "
    CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(10) DEFAULT '🏅',
        xp_reward INT DEFAULT 0,
        requirement_type VARCHAR(50),
        requirement_value INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// =========================================================
// 4. Create user_achievements table
// =========================================================
runSQL($pdo, "Create user_achievements table", "
    CREATE TABLE IF NOT EXISTS user_achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        achievement_id INT NOT NULL,
        unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_ua (user_id, achievement_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
    )
");

// =========================================================
// 5. Create daily_goals table
// =========================================================
runSQL($pdo, "Create daily_goals table", "
    CREATE TABLE IF NOT EXISTS daily_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        goal_date DATE NOT NULL,
        words_target INT DEFAULT 5,
        words_completed INT DEFAULT 0,
        xp_earned INT DEFAULT 0,
        completed TINYINT DEFAULT 0,
        UNIQUE KEY unique_daily (user_id, goal_date),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// =========================================================
// 6. Create review_queue (spaced repetition) table
// =========================================================
runSQL($pdo, "Create review_queue table", "
    CREATE TABLE IF NOT EXISTS review_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        word_id INT NOT NULL,
        ease_factor DECIMAL(3,2) DEFAULT 2.50,
        interval_days INT DEFAULT 1,
        repetitions INT DEFAULT 0,
        next_review DATE NOT NULL,
        last_reviewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_review (user_id, word_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
    )
");

// =========================================================
// 7. Create game_sessions table (for analytics)
// =========================================================
runSQL($pdo, "Create game_sessions table", "
    CREATE TABLE IF NOT EXISTS game_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        game_type ENUM('hangman','mcq','review') DEFAULT 'hangman',
        word_id INT,
        result ENUM('win','lose') NOT NULL,
        time_taken INT DEFAULT 0,
        score_change INT DEFAULT 0,
        played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// =========================================================
// 8. Seed achievements data
// =========================================================
$achievements = [
    ['first_word', 'First Word', 'Complete your first word', '🌟', 10, 'total_words', 1],
    ['ten_words', 'Vocabulary Builder', 'Learn 10 words', '📚', 25, 'total_words', 10],
    ['fifty_words', 'Word Scholar', 'Learn 50 words', '🎓', 100, 'total_words', 50],
    ['hundred_words', 'Century', 'Learn 100 words', '💯', 250, 'total_words', 100],
    ['streak_3', 'Getting Warm', '3 day daily streak', '🔥', 15, 'daily_streak', 3],
    ['streak_7', 'On Fire', '7 day daily streak', '🔥', 50, 'daily_streak', 7],
    ['streak_30', 'Unstoppable', '30 day daily streak', '💎', 200, 'daily_streak', 30],
    ['first_win', 'First Victory', 'Win your first game', '🏆', 10, 'wins', 1],
    ['ten_wins', 'Champion', 'Win 10 games', '🏆', 50, 'wins', 10],
    ['fifty_wins', 'Grandmaster', 'Win 50 games', '👑', 150, 'wins', 50],
    ['speed_demon', 'Speed Demon', 'Complete a word in under 10 seconds', '⚡', 30, 'speed', 10],
    ['perfect_mcq', 'Perfect Round', 'Get 10/10 in MCQ mode', '✨', 75, 'perfect_mcq', 1],
    ['accuracy_90', 'Sharp Mind', 'Maintain 90% accuracy', '🎯', 100, 'accuracy', 90],
    ['score_500', 'Rising Star', 'Reach 500 total score', '⭐', 50, 'total_score', 500],
    ['score_1000', 'Legend', 'Reach 1000 total score', '🌟', 150, 'total_score', 1000],
    ['all_beginner', 'Beginner Complete', 'Complete all beginner words', '🌱', 100, 'level_complete', 1],
    ['all_intermediate', 'Intermediate Complete', 'Complete all intermediate words', '🚀', 200, 'level_complete', 2],
    ['all_advanced', 'Advanced Complete', 'Complete all advanced words', '🔥', 500, 'level_complete', 3],
];

foreach ($achievements as $a) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO achievements (slug, title, description, icon, xp_reward, requirement_type, requirement_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($a);
    } catch (PDOException $e) {
        // Ignore duplicates
    }
}
$results[] = "✅ Seeded " . count($achievements) . " achievements";

// =========================================================
// 9. Add indexes for performance
// =========================================================
runSQL($pdo, "Index on game_sessions(user_id, played_at)",
    "ALTER TABLE game_sessions ADD INDEX idx_gs_user_date (user_id, played_at)");

runSQL($pdo, "Index on review_queue(user_id, next_review)",
    "ALTER TABLE review_queue ADD INDEX idx_rq_next (user_id, next_review)");

runSQL($pdo, "Index on daily_goals(user_id, goal_date)",
    "ALTER TABLE daily_goals ADD INDEX idx_dg_date (user_id, goal_date)");

// =========================================================
// 10. Update existing progress records with defaults
// =========================================================
runSQL($pdo, "Backfill progress.daily_streak defaults",
    "UPDATE progress SET daily_streak = 0 WHERE daily_streak IS NULL");

runSQL($pdo, "Backfill progress.xp from total_score",
    "UPDATE progress SET xp = total_score WHERE xp = 0 AND total_score > 0");

// =========================================================
// DISPLAY RESULTS
// =========================================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faseeh v2.0 Migration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 40px; min-height: 100vh; }
        .card { background: rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; max-width: 700px; margin: 0 auto; backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.2); }
        h1 { text-align: center; margin-bottom: 30px; }
        .result { padding: 10px 15px; margin-bottom: 5px; background: rgba(255,255,255,0.05); border-radius: 10px; font-size: 0.9rem; }
        .warning { background: rgba(231,76,60,0.2); padding: 15px; border-radius: 10px; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Faseeh v2.0 Migration</h1>
        <?php foreach($results as $r): ?>
            <div class="result"><?php echo $r; ?></div>
        <?php endforeach; ?>
        <div class="warning">
            ⚠️ <strong>Delete this file</strong> after running! <code>migrate.php</code>
        </div>
    </div>
</body>
</html>
