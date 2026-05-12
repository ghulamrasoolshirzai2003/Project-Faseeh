<?php
/**
 * FASEEH — DATABASE HARD RESET (POSTGRES / MYSQL)
 * Use this only if the database schema is corrupted or mismatched.
 * WARNING: This will delete ALL users and ALL progress.
 */
require 'includes/db.php';

echo "<h2 style='font-family:sans-serif;'>🔥 Database Hard Reset Mode</h2>";

// 1. DROP ALL TABLES WITH CASCADE (Postgres) or disable checks (MySQL)
if ($driver === 'pgsql') {
    $pdo->exec("DROP TABLE IF EXISTS sessions, user_active_sessions, review_queue, user_achievements, achievements, user_progress, progress, words, users CASCADE");
} else {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS sessions, user_active_sessions, review_queue, user_achievements, achievements, user_progress, progress, words, users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}
echo "<p style='color:orange;'>🗑️ Dropped all existing tables.</p>";

// 2. RECREATE TABLES WITH CORRECT TYPES
$tables = [
    "users" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, full_name VARCHAR(100), username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, selected_level VARCHAR(20) DEFAULT 'beginner', role VARCHAR(20) DEFAULT 'student', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        : "id int(11) NOT NULL AUTO_INCREMENT, full_name varchar(100), username varchar(50) NOT NULL UNIQUE, email varchar(100) NOT NULL UNIQUE, password varchar(255) NOT NULL, selected_level varchar(20) DEFAULT 'beginner', role varchar(20) DEFAULT 'student', created_at timestamp DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)",
    
    "words" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, arabic_word VARCHAR(100) NOT NULL, meaning_en VARCHAR(200) NOT NULL, meaning_my VARCHAR(200), root VARCHAR(50), level VARCHAR(20) DEFAULT 'beginner', category VARCHAR(50) DEFAULT 'general', audio_file VARCHAR(100)"
        : "id int(11) NOT NULL AUTO_INCREMENT, arabic_word varchar(100) NOT NULL, meaning_en varchar(200) NOT NULL, meaning_my varchar(200), root varchar(50), level varchar(20) DEFAULT 'beginner', category varchar(50) DEFAULT 'general', audio_file varchar(100), PRIMARY KEY (id)",
    
    "progress" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, total_score INTEGER DEFAULT 0, xp INTEGER DEFAULT 0, current_streak INTEGER DEFAULT 0, daily_streak INTEGER DEFAULT 0, longest_streak INTEGER DEFAULT 0, last_active TIMESTAMP, last_play_date DATE, wins INTEGER DEFAULT 0, losses INTEGER DEFAULT 0"
        : "id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL, total_score int(11) DEFAULT 0, xp int(11) DEFAULT 0, current_streak int(11) DEFAULT 0, daily_streak int(11) DEFAULT 0, longest_streak int(11) DEFAULT 0, last_active datetime, last_play_date date, wins int(11) DEFAULT 0, losses int(11) DEFAULT 0, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",

    "user_progress" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, word_id INTEGER REFERENCES words(id) ON DELETE CASCADE, solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        : "id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL, word_id int(11) NOT NULL, solved_at timestamp DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE",

    "user_active_sessions" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, mode VARCHAR(50), questions_completed INTEGER DEFAULT 0, total_target INTEGER DEFAULT 10, last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        : "id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL, mode varchar(50), questions_completed int(11) DEFAULT 0, total_target int(11) DEFAULT 10, last_active timestamp DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",

    "sessions" => ($driver === 'pgsql') 
        ? "id VARCHAR(128) NOT NULL PRIMARY KEY, data TEXT NOT NULL, last_access INTEGER NOT NULL"
        : "`id` varchar(128) NOT NULL, `data` text NOT NULL, `last_access` int(11) NOT NULL, PRIMARY KEY (`id`)",
        
    "review_queue" => ($driver === 'pgsql')
        ? "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, word_id INTEGER REFERENCES words(id) ON DELETE CASCADE, next_review DATE, ease_factor FLOAT DEFAULT 2.5, interval INTEGER DEFAULT 0"
        : "id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL, word_id int(11) NOT NULL, next_review date, ease_factor float DEFAULT 2.5, `interval` int(11) DEFAULT 0, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE"
];

foreach ($tables as $name => $schema) {
    try {
        $pdo->exec("CREATE TABLE $name ($schema)");
        echo "<p style='color:green;'>✅ Created table '$name'.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Error creating '$name': " . $e->getMessage() . "</p>";
    }
}

// 3. SEED STARTER DATA
$pdo->exec("INSERT INTO words (arabic_word, meaning_en, meaning_my, level) VALUES 
    ('كِتَاب', 'Book', 'Buku', 'beginner'),
    ('قَلَم', 'Pen', 'Pena', 'beginner'),
    ('بَيْت', 'House', 'Rumah', 'beginner')");
echo "<p style='color:blue;'>📦 Seeded 3 starter words.</p>";

echo "<hr><p>Done! <a href='index.php'>Go to Portal</a></p>";
?>
