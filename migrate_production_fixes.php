<?php
/**
 * Faseeh Production Migration Plan & Schema Fixes
 * 
 * This script:
 * 1. Renames and isolates the user solved words table from daily XP logs.
 * 2. Unifies spaced repetition review tables into 'review_queue'.
 * 3. Migrates all existing student historical progress so nothing is lost.
 * 4. Adds security recovery question support to the 'users' table.
 */

require 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Starting Faseeh Production Database Migration...</h2>";

    // 1. Create the new user_solved_words table (replaces user_progress for solved words)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_solved_words` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `word_id` INT NOT NULL,
        `solved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_user_word_solved` (`user_id`, `word_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`word_id`) REFERENCES `words` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "✅ Table 'user_solved_words' created or verified.<br>";

    // 2. Create the new user_xp_history table (replaces user_progress for daily XP charts)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_xp_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `xp_gained` INT NOT NULL,
        `progress_date` DATE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "✅ Table 'user_xp_history' created or verified.<br>";

    // 3. Migrate data from old 'user_progress' table if it exists and has data
    $checkOldProgress = $pdo->query("SHOW TABLES LIKE 'user_progress'");
    if ($checkOldProgress->rowCount() > 0) {
        // Check columns in old table
        $cols = $pdo->query("DESCRIBE `user_progress`")->fetchAll(PDO::FETCH_COLUMN);
        
        // Migrate solved words if 'word_id' exists
        if (in_array('word_id', $cols)) {
            $pdo->exec("INSERT IGNORE INTO `user_solved_words` (user_id, word_id, solved_at) 
                        SELECT user_id, word_id, solved_at FROM `user_progress` WHERE word_id IS NOT NULL");
            echo "✅ Migrated solved word logs from old 'user_progress' table.<br>";
        }

        // Migrate XP history if 'xp_gained' or 'progress_date' exists
        if (in_array('xp_gained', $cols) && in_array('progress_date', $cols)) {
            $pdo->exec("INSERT IGNORE INTO `user_xp_history` (user_id, xp_gained, progress_date) 
                        SELECT user_id, xp_gained, progress_date FROM `user_progress` WHERE xp_gained > 0");
            echo "✅ Migrated weekly/monthly XP logs from old 'user_progress' table.<br>";
        }

        // Now drop the old table to resolve naming collisions
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS `user_progress`;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "✅ Old 'user_progress' table dropped to clear namespace conflicts.<br>";
    }

    // 4. Set up the unified review_queue table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `review_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `word_id` INT NOT NULL,
        `ease_factor` FLOAT DEFAULT 2.5,
        `interval_days` INT DEFAULT 1,
        `repetitions` INT DEFAULT 0,
        `next_review` DATE NOT NULL,
        `last_reviewed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_user_word_review` (`user_id`, `word_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`word_id`) REFERENCES `words` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "✅ Unified table 'review_queue' verified.<br>";

    // 5. Migrate user_srs_words to review_queue if it exists
    $checkSRS = $pdo->query("SHOW TABLES LIKE 'user_srs_words'");
    if ($checkSRS->rowCount() > 0) {
        $pdo->exec("INSERT IGNORE INTO `review_queue` (user_id, word_id, ease_factor, interval_days, repetitions, next_review)
                    SELECT user_id, word_id, ease_factor, interval_days, repetitions, DATE(next_review) FROM `user_srs_words`");
        echo "✅ Migrated student review files from 'user_srs_words' to 'review_queue'.<br>";

        // Drop user_srs_words table to complete unification
        $pdo->exec("DROP TABLE IF EXISTS `user_srs_words`;");
        echo "✅ Redundant 'user_srs_words' table dropped.<br>";
    }

    // 6. Add recovery question columns to users table
    $checkQ = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'recovery_question'");
    if ($checkQ->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `recovery_question` VARCHAR(255) DEFAULT NULL");
        echo "✅ Added 'recovery_question' column to users.<br>";
    }
    $checkA = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'recovery_answer'");
    if ($checkA->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `recovery_answer` VARCHAR(255) DEFAULT NULL");
        echo "✅ Added 'recovery_answer' column to users.<br>";
    }

    echo "<h3 style='color:green'>Database Refactoring Migration Complete! 🎉</h3>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Migration Failed:</h3> " . $e->getMessage());
}
?>
