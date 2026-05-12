<?php
/**
 * STANDALONE EMERGENCY RESET (NO DEPENDENCIES)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Live Settings
$host = 'ep-restless-frog-aovh263w-pooler.c-2.ap-southeast-1.aws.neon.tech';
$db   = 'neondb';
$user = 'neondb_owner';
$pass = 'npg_tZOJr92BhRaS';
$port = '5432';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "<h2>🔥 STANDALONE HARD RESET</h2>";

    // 1. DROP EVERYTHING
    $pdo->exec("DROP TABLE IF EXISTS sessions, user_active_sessions, review_queue, user_achievements, achievements, user_progress, progress, words, users CASCADE");
    echo "✅ Dropped all tables (including UUID ones).<br>";

    // 2. CREATE FRESH (ALL SERIAL/INTEGER)
    $queries = [
        "CREATE TABLE users (id SERIAL PRIMARY KEY, full_name VARCHAR(100), username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, selected_level VARCHAR(20) DEFAULT 'beginner', role VARCHAR(20) DEFAULT 'student', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE words (id SERIAL PRIMARY KEY, arabic_word VARCHAR(100) NOT NULL, meaning_en VARCHAR(200) NOT NULL, meaning_my VARCHAR(200), root VARCHAR(50), level VARCHAR(20) DEFAULT 'beginner', category VARCHAR(50) DEFAULT 'general', audio_file VARCHAR(100))",
        "CREATE TABLE progress (id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, total_score INTEGER DEFAULT 0, xp INTEGER DEFAULT 0, current_streak INTEGER DEFAULT 0, daily_streak INTEGER DEFAULT 0, longest_streak INTEGER DEFAULT 0, last_active TIMESTAMP, last_play_date DATE, wins INTEGER DEFAULT 0, losses INTEGER DEFAULT 0)",
        "CREATE TABLE user_progress (id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, word_id INTEGER REFERENCES words(id) ON DELETE CASCADE, solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE user_active_sessions (id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, mode VARCHAR(50), questions_completed INTEGER DEFAULT 0, total_target INTEGER DEFAULT 10, last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE sessions (id VARCHAR(128) NOT NULL PRIMARY KEY, data TEXT NOT NULL, last_access INTEGER NOT NULL)",
        "INSERT INTO words (arabic_word, meaning_en, meaning_my, level) VALUES ('كِتَاب', 'Book', 'Buku', 'beginner'), ('قَلَم', 'Pen', 'Pena', 'beginner')"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
        echo "✅ Executed: " . substr($q, 0, 30) . "...<br>";
    }

    echo "<br>🚀 **SYSTEM REPAIRED.** You can now go back to the home page.";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
