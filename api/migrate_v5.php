<?php
/**
 * Faseeh v5.0 Migration - Academic Tracking & Expanded Seeds
 * Adds tracking for answered questions to prevent repeats.
 */
require 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Starting Faseeh v5.0 Migration...</h2>";

    // 1. Create table to track answered questions so they don't repeat
    $sql = "CREATE TABLE IF NOT EXISTS `user_answered` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `mode` varchar(50) NOT NULL,
        `question_id` int(11) NOT NULL,
        `answered_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_answer` (`user_id`, `mode`, `question_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "✅ Tracking table 'user_answered' created.<br>";

    // We can also add a column for academic_xp in the progress table if it doesn't exist
    $checkCol = $pdo->query("SHOW COLUMNS FROM `progress` LIKE 'academic_xp'");
    if($checkCol->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `progress` ADD `academic_xp` INT(11) DEFAULT 0 AFTER `xp`");
        echo "✅ Added 'academic_xp' to progress tracking.<br>";
    }

    echo "<h3 style='color:green'>Migration v5.0 Complete! The system will no longer repeat answered questions.</h3>";
    echo "<a href='academic_hub.php' style='display:inline-block; padding:10px 20px; background:#f2994a; color:white; text-decoration:none; border-radius:10px;'>Go to Academic Hub</a>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Migration Failed:</h3> " . $e->getMessage());
}
?>
