<?php
require 'includes/db.php';

try {
    // Create the Academy Lessons Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS academy_lessons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('reading', 'writing', 'speaking', 'listening') NOT NULL,
        level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        title VARCHAR(255) NOT NULL,
        arabic_title VARCHAR(255),
        content TEXT NOT NULL,
        translation TEXT,
        metadata JSON, -- Stores words, quiz options, or prompts
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create Table for User Progress in Academy
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_academy_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        lesson_id INT,
        status ENUM('locked', 'unlocked', 'completed') DEFAULT 'locked',
        score INT DEFAULT 0,
        completed_at TIMESTAMP NULL,
        UNIQUE KEY user_lesson (user_id, lesson_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "✅ Success: Academy database tables created!<br>";
    echo "<a href='seed_academy.php'>Click here to Seed Massive Content</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
