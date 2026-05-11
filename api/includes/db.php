<?php
/**
 * ============================================================
 * FASEEH — ELITE DATABASE CONNECTION & SESSION MASTER
 * ============================================================
 */

// Force error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$hostName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
$isLocal = str_contains($hostName, 'localhost') || str_contains($hostName, '127.0.0.1') || str_contains($hostName, '192.168.');

if ($isLocal) {
    // 🏠 LOCAL SETTINGS (MySQL for XAMPP / MariaDB)
    $host = '127.0.0.1';
    $db   = 'faseeh_db';
    $user = 'root';
    $pass = '';
    $driver = 'mysql';
    $port = '3306';
} else {
    // 🌍 LIVE SETTINGS (Neon Postgres for Vercel)
    $host = 'ep-restless-frog-aovh263w-pooler.c-2.ap-southeast-1.aws.neon.tech';
    $db   = 'neondb';
    $user = 'neondb_owner';
    $pass = 'npg_tZOJr92BhRaS';
    $port = '5432';
    $driver = 'pgsql';
}

try {
    if ($driver === 'pgsql') {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
    } else {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5, 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- 🚀 AUTOMATIC TABLE VERIFICATION (FUNDAMENTAL FIX) ---
    $tables = [
        "sessions" => ($driver === 'pgsql') 
            ? "id VARCHAR(128) NOT NULL PRIMARY KEY, data TEXT NOT NULL, last_access INTEGER NOT NULL"
            : "`id` varchar(128) NOT NULL, `data` text NOT NULL, `last_access` int(11) NOT NULL, PRIMARY KEY (`id`)",
        "users" => ($driver === 'pgsql')
            ? "id SERIAL PRIMARY KEY, full_name VARCHAR(100), username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, selected_level VARCHAR(20) DEFAULT 'beginner', role VARCHAR(20) DEFAULT 'student', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
            : "id int(11) NOT NULL AUTO_INCREMENT, full_name varchar(100), username varchar(50) NOT NULL UNIQUE, email varchar(100) NOT NULL UNIQUE, password varchar(255) NOT NULL, selected_level varchar(20) DEFAULT 'beginner', role varchar(20) DEFAULT 'student', created_at timestamp DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)",
        "words" => ($driver === 'pgsql')
            ? "id SERIAL PRIMARY KEY, arabic_word VARCHAR(100) NOT NULL, meaning_en VARCHAR(200) NOT NULL, meaning_my VARCHAR(200), root VARCHAR(50), level VARCHAR(20) DEFAULT 'beginner', category VARCHAR(50) DEFAULT 'general', audio_file VARCHAR(100)"
            : "id int(11) NOT NULL AUTO_INCREMENT, arabic_word varchar(100) NOT NULL, meaning_en varchar(200) NOT NULL, meaning_my varchar(200), root varchar(50), level varchar(20) DEFAULT 'beginner', category varchar(50) DEFAULT 'general', audio_file varchar(100), PRIMARY KEY (id)",
        "progress" => ($driver === 'pgsql')
            ? "id SERIAL PRIMARY KEY, user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, total_score INTEGER DEFAULT 0, xp INTEGER DEFAULT 0, current_streak INTEGER DEFAULT 0, daily_streak INTEGER DEFAULT 0, longest_streak INTEGER DEFAULT 0, last_active TIMESTAMP, last_play_date DATE, wins INTEGER DEFAULT 0, losses INTEGER DEFAULT 0"
            : "id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL, total_score int(11) DEFAULT 0, xp int(11) DEFAULT 0, current_streak int(11) DEFAULT 0, daily_streak int(11) DEFAULT 0, longest_streak int(11) DEFAULT 0, last_active datetime, last_play_date date, wins int(11) DEFAULT 0, losses int(11) DEFAULT 0, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE"
    ];

    foreach ($tables as $name => $schema) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS $name ($schema)");
    }

    // --- 🚀 DATABASE SESSION MASTER (FIX FOR VERCEL) ---
    require_once __DIR__ . '/session_handler.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
        $handler = new DatabaseSessionHandler($pdo);
        session_set_save_handler($handler, true);
        session_start();
    }

} catch (PDOException $e) {
    die("<h2 style='color:red'>Database Connection Failed!</h2>" . 
        "<p>Server Host: <b>$hostName</b></p>" .
        "<p>Target Driver: <b>$driver</b></p>" .
        "<p>Error: " . $e->getMessage() . "</p>");
}
?>