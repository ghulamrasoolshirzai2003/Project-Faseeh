<?php
// migrate_portals.php — Add Teacher and Parent roles and relationships
require 'includes/db.php';

try {
    // 1. Update the role ENUM in users table
    // Note: Some MySQL versions don't like MODIFY for ENUM if data exists, 
    // but usually it works. If not, we'd use a more complex migration.
    $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('student', 'admin', 'teacher', 'parent') DEFAULT 'student'");
    echo "✅ Updated user roles to include teacher and parent.\n";

    // 2. Create user_relationships table
    // relationship_type: 'teacher_of', 'parent_of'
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_relationships` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `parent_id` INT NOT NULL, -- This is the 'owner' (teacher or parent)
        `student_id` INT NOT NULL,
        `relationship_type` ENUM('teacher_of', 'parent_of') NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_rel` (`parent_id`, `student_id`, `relationship_type`),
        FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Created user_relationships table.\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
