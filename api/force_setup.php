<?php
/**
 * FASEEH — EMERGENCY DATABASE REPAIR
 * This script ensures all required tables exist.
 */
require 'includes/db.php';

echo "<h2 style='font-family:sans-serif;'>🛠️ Database Repair Mode</h2>";

$tables = [
    "sessions" => "id VARCHAR(128) NOT NULL PRIMARY KEY, data TEXT NOT NULL, last_access INTEGER NOT NULL",
    "users" => "id SERIAL PRIMARY KEY, full_name VARCHAR(100), username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, selected_level VARCHAR(20) DEFAULT 'beginner', role VARCHAR(20) DEFAULT 'student', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "words" => "id SERIAL PRIMARY KEY, arabic_word VARCHAR(100) NOT NULL, meaning_en VARCHAR(200) NOT NULL, meaning_my VARCHAR(200), root VARCHAR(50), level VARCHAR(20) DEFAULT 'beginner', category VARCHAR(50) DEFAULT 'general', audio_file VARCHAR(100)",
    "progress" => "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, total_score INTEGER DEFAULT 0, xp INTEGER DEFAULT 0, current_streak INTEGER DEFAULT 0, daily_streak INTEGER DEFAULT 0, longest_streak INTEGER DEFAULT 0, last_active TIMESTAMP, last_play_date DATE, wins INTEGER DEFAULT 0, losses INTEGER DEFAULT 0",
    "user_progress" => "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, word_id INTEGER REFERENCES words(id) ON DELETE CASCADE, solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "achievements" => "id SERIAL PRIMARY KEY, slug VARCHAR(50) NOT NULL UNIQUE, title VARCHAR(100) NOT NULL, description TEXT, icon VARCHAR(10) DEFAULT '🏅', xp_reward INTEGER DEFAULT 0, requirement_type VARCHAR(50), requirement_value INTEGER DEFAULT 0",
    "user_achievements" => "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, achievement_id INTEGER REFERENCES achievements(id) ON DELETE CASCADE, unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
];

foreach ($tables as $name => $schema) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS $name ($schema)");
        echo "<p style='color:green; font-family:sans-serif;'>✅ Table '$name' is ready.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red; font-family:sans-serif;'>❌ Error creating '$name': " . $e->getMessage() . "</p>";
    }
}

// Seed basic words if empty
$check = $pdo->query("SELECT COUNT(*) FROM words")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT INTO words (arabic_word, meaning_en, meaning_my, level) VALUES 
        ('كِتَاب', 'Book', 'Buku', 'beginner'),
        ('قَلَم', 'Pen', 'Pena', 'beginner'),
        ('بَيْت', 'House', 'Rumah', 'beginner')");
    echo "<p style='color:blue; font-family:sans-serif;'>📦 Seeded 3 starter words.</p>";
}

echo "<hr><p style='font-family:sans-serif;'>Done! Try <a href='index.php'>logging in again</a>.</p>";
?>
