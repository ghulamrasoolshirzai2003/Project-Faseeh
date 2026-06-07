<?php
// api/clash_migrate.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

try {
    // 1. Clash Rooms
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        room_pin VARCHAR(10) NOT NULL UNIQUE,
        status ENUM('lobby', 'active', 'show_answer', 'finished') DEFAULT 'lobby',
        current_question_index INT DEFAULT 0,
        question_set VARCHAR(50) DEFAULT 'beginner',
        timer_started_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Clash Players
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        user_id INT NULL,
        nickname VARCHAR(50) NOT NULL,
        score INT DEFAULT 0,
        streak INT DEFAULT 0,
        last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 3. Clash Answers
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        player_id INT NOT NULL,
        question_index INT NOT NULL,
        is_correct TINYINT DEFAULT 0,
        time_taken FLOAT DEFAULT 0,
        score_awarded INT DEFAULT 0,
        choice VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_player_q (room_id, player_id, question_index)
    )");

    echo json_encode(["success" => true, "message" => "Faseeh Class Clash database tables successfully initialized!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
