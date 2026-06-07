<?php
// migrate_guardian_info.php — Add guardian info to users table
require 'includes/db.php';

try {
    // 1. Add guardian_name and guardian_dob to users table
    $pdo->exec("ALTER TABLE `users` 
                ADD COLUMN `guardian_name` VARCHAR(100) DEFAULT NULL,
                ADD COLUMN `guardian_dob` DATE DEFAULT NULL");
    echo "✅ Added guardian information columns to users table.\n";

    // 2. Create parent_feedback table for comments/questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS `parent_feedback` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `parent_id` INT NOT NULL,
        `student_id` INT NOT NULL,
        `message` TEXT NOT NULL,
        `status` ENUM('pending', 'read', 'replied') DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Created parent_feedback table.\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
