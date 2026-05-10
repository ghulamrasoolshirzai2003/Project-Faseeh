<?php
/**
 * ============================================================
 * FASEEH — DATABASE SCHEMA EXPORT
 * ============================================================
 * This generates the complete SQL schema for Faseeh v2.0.
 * Run this once to get the SQL, then import it into your
 * live hosting's phpMyAdmin.
 * 
 * Usage: Visit /export_schema.php in browser, copy the SQL.
 * Then DELETE this file.
 * ============================================================
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faseeh — Database Schema</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Fira+Code&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); color: white; padding: 40px; min-height: 100vh; }
        .card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; max-width: 900px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 10px; }
        p.sub { text-align: center; opacity: 0.5; font-size: 0.85rem; margin-bottom: 25px; }
        textarea { width: 100%; height: 500px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #a0ffa0; font-family: 'Fira Code', monospace; font-size: 0.8rem; padding: 20px; resize: vertical; }
        .steps { margin-top: 25px; }
        .step { display: flex; gap: 12px; margin-bottom: 12px; }
        .step-num { width: 30px; height: 30px; background: rgba(242,153,74,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; color: #f2994a; font-size: 0.85rem; }
        .step-text { font-size: 0.85rem; opacity: 0.8; }
        .copy-btn { display: block; margin: 15px auto 0; padding: 12px 30px; border-radius: 25px; border: none; background: linear-gradient(to right, #f2994a, #f2c94c); color: white; font-weight: 700; cursor: pointer; font-size: 0.9rem; transition: 0.3s; }
        .copy-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(242,153,74,0.4); }
        .warning { background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); padding: 12px 18px; border-radius: 12px; margin-top: 20px; text-align: center; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🗄️ Faseeh v2.0 — Database Schema</h1>
        <p class="sub">Copy this SQL and paste it into your live hosting's phpMyAdmin</p>

        <textarea id="sql-output" readonly>-- ============================================================
-- FASEEH v2.0 COMPLETE DATABASE SCHEMA
-- Generated: <?php echo date('Y-m-d H:i:s'); ?>

-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- 1. USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `dob` DATE DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `selected_level` VARCHAR(20) DEFAULT 'beginner',
    `role` ENUM('student','admin') DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. WORDS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `words` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `arabic_word` VARCHAR(100) NOT NULL,
    `meaning_en` VARCHAR(200) NOT NULL,
    `meaning_my` VARCHAR(200) DEFAULT NULL,
    `root` VARCHAR(50) DEFAULT NULL,
    `level` VARCHAR(20) DEFAULT 'beginner',
    `category` VARCHAR(50) DEFAULT 'general',
    `audio_file` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. PROGRESS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `total_score` INT DEFAULT 0,
    `xp` INT DEFAULT 0,
    `current_streak` INT DEFAULT 0,
    `daily_streak` INT DEFAULT 0,
    `longest_streak` INT DEFAULT 0,
    `total_words_learned` INT DEFAULT 0,
    `accuracy_total` INT DEFAULT 0,
    `accuracy_correct` INT DEFAULT 0,
    `wins` INT DEFAULT 0,
    `mcq_wins` INT DEFAULT 0,
    `mcq_losses` INT DEFAULT 0,
    `losses` INT DEFAULT 0,
    `points_lost` INT DEFAULT 0,
    `attempts` INT DEFAULT 0,
    `last_active` DATETIME DEFAULT NULL,
    `last_play_date` DATE DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. USER PROGRESS (SOLVED WORDS)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `word_id` INT NOT NULL,
    `solved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_word` (`user_id`, `word_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`word_id`) REFERENCES `words`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ACHIEVEMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) UNIQUE NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(10) DEFAULT '🏅',
    `xp_reward` INT DEFAULT 0,
    `requirement_type` VARCHAR(50),
    `requirement_value` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. USER ACHIEVEMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_achievements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `achievement_id` INT NOT NULL,
    `unlocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_ua` (`user_id`, `achievement_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. DAILY GOALS
-- ============================================================
CREATE TABLE IF NOT EXISTS `daily_goals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `goal_date` DATE NOT NULL,
    `words_target` INT DEFAULT 5,
    `words_completed` INT DEFAULT 0,
    `xp_earned` INT DEFAULT 0,
    `completed` TINYINT DEFAULT 0,
    UNIQUE KEY `unique_daily` (`user_id`, `goal_date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. REVIEW QUEUE (SPACED REPETITION)
-- ============================================================
CREATE TABLE IF NOT EXISTS `review_queue` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `word_id` INT NOT NULL,
    `ease_factor` DECIMAL(3,2) DEFAULT 2.50,
    `interval_days` INT DEFAULT 1,
    `repetitions` INT DEFAULT 0,
    `next_review` DATE NOT NULL,
    `last_reviewed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_review` (`user_id`, `word_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`word_id`) REFERENCES `words`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. GAME SESSIONS (ANALYTICS)
-- ============================================================
CREATE TABLE IF NOT EXISTS `game_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `game_type` ENUM('hangman','mcq','review') DEFAULT 'hangman',
    `word_id` INT DEFAULT NULL,
    `result` ENUM('win','lose') NOT NULL,
    `time_taken` INT DEFAULT 0,
    `score_change` INT DEFAULT 0,
    `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. INDEXES FOR PERFORMANCE
-- ============================================================
ALTER TABLE `game_sessions` ADD INDEX `idx_gs_user_date` (`user_id`, `played_at`);
ALTER TABLE `review_queue` ADD INDEX `idx_rq_next` (`user_id`, `next_review`);
ALTER TABLE `daily_goals` ADD INDEX `idx_dg_date` (`user_id`, `goal_date`);

-- ============================================================
-- 11. SEED ACHIEVEMENTS DATA
-- ============================================================
INSERT IGNORE INTO `achievements` (`slug`, `title`, `description`, `icon`, `xp_reward`, `requirement_type`, `requirement_value`) VALUES
('first_word', 'First Word', 'Complete your first word', '🌟', 10, 'total_words', 1),
('ten_words', 'Vocabulary Builder', 'Learn 10 words', '📚', 25, 'total_words', 10),
('fifty_words', 'Word Scholar', 'Learn 50 words', '🎓', 100, 'total_words', 50),
('hundred_words', 'Century', 'Learn 100 words', '💯', 250, 'total_words', 100),
('streak_3', 'Getting Warm', '3 day daily streak', '🔥', 15, 'daily_streak', 3),
('streak_7', 'On Fire', '7 day daily streak', '🔥', 50, 'daily_streak', 7),
('streak_30', 'Unstoppable', '30 day daily streak', '💎', 200, 'daily_streak', 30),
('first_win', 'First Victory', 'Win your first game', '🏆', 10, 'wins', 1),
('ten_wins', 'Champion', 'Win 10 games', '🏆', 50, 'wins', 10),
('fifty_wins', 'Grandmaster', 'Win 50 games', '👑', 150, 'wins', 50),
('speed_demon', 'Speed Demon', 'Complete a word in under 10 seconds', '⚡', 30, 'speed', 10),
('perfect_mcq', 'Perfect Round', 'Get 10/10 in MCQ mode', '✨', 75, 'perfect_mcq', 1),
('accuracy_90', 'Sharp Mind', 'Maintain 90% accuracy', '🎯', 100, 'accuracy', 90),
('score_500', 'Rising Star', 'Reach 500 total score', '⭐', 50, 'total_score', 500),
('score_1000', 'Legend', 'Reach 1000 total score', '🌟', 150, 'total_score', 1000),
('all_beginner', 'Beginner Complete', 'Complete all beginner words', '🌱', 100, 'level_complete', 1),
('all_intermediate', 'Intermediate Complete', 'Complete all intermediate words', '🚀', 200, 'level_complete', 2),
('all_advanced', 'Advanced Complete', 'Complete all advanced words', '🔥', 500, 'level_complete', 3);

-- ============================================================
-- 12. CREATE DEFAULT ADMIN ACCOUNT
-- Password: admin123 (change this immediately!)
-- ============================================================
INSERT IGNORE INTO `users` (`full_name`, `username`, `password`, `role`) 
VALUES ('Admin', 'admin', '$2y$10$8K1p/a0dL1LXMIgoEDFrQOufMZaHUKcVqiJ9A2.HlHh3Ib8F2dMlO', 'admin');

COMMIT;</textarea>

        <button class="copy-btn" onclick="copySQL()">📋 Copy SQL to Clipboard</button>

        <div class="steps">
            <h3 style="margin-bottom: 15px;">📋 Deployment Steps:</h3>
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text"><b>Sign up</b> at <a href="https://www.infinityfree.com" target="_blank" style="color:#f2994a;">InfinityFree.com</a> (100% free, no credit card)</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text"><b>Create hosting account</b> → you'll get a subdomain like <code>yourname.infinityfreeapp.com</code></div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text"><b>Create MySQL database</b> in control panel → note down the Host, DB Name, Username, Password</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-text"><b>Open phpMyAdmin</b> → go to SQL tab → paste the SQL above → click Go</div>
            </div>
            <div class="step">
                <div class="step-num">5</div>
                <div class="step-text"><b>Update <code>includes/db.php</code></b> — put your live MySQL credentials in the LIVE SETTINGS section</div>
            </div>
            <div class="step">
                <div class="step-num">6</div>
                <div class="step-text"><b>Upload all files</b> via File Manager or FTP (FileZilla) to <code>htdocs/</code> folder</div>
            </div>
            <div class="step">
                <div class="step-num">7</div>
                <div class="step-text"><b>Visit your site!</b> 🎉 It's live.</div>
            </div>
        </div>

        <div class="warning">
            ⚠️ <b>Delete this file</b> and <code>migrate.php</code> after deployment!
        </div>
    </div>

    <script>
    function copySQL() {
        const ta = document.getElementById('sql-output');
        ta.select();
        document.execCommand('copy');
        const btn = document.querySelector('.copy-btn');
        btn.innerText = '✅ Copied!';
        setTimeout(() => btn.innerText = '📋 Copy SQL to Clipboard', 2000);
    }
    </script>
</body>
</html>
