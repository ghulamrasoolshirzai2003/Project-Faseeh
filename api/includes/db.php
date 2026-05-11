<?php
/**
 * ============================================================
 * FASEEH — ELITE DATABASE CONNECTION (POSTGRES / NEON)
 * ============================================================
 */

// If we are NOT on localhost, we are on the LIVE server (Neon)
$hostName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
$isLocal = str_contains($hostName, 'localhost') || str_contains($hostName, '127.0.0.1') || str_contains($hostName, '192.168.');

if ($isLocal) {
    // 🏠 LOCAL SETTINGS (MySQL for XAMPP)
    $host = 'localhost';
    $db   = 'faseeh_db';
    $user = 'root';
    $pass = '';
    $driver = 'mysql';
} else {
    // 🌍 LIVE SETTINGS (Neon Postgres for Vercel)
    // We FORCE this for anything that isn't localhost
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
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- 🚀 DATABASE SESSION HANDLER (FIX FOR VERCEL) ---
    require_once __DIR__ . '/session_handler.php';
    
    // Ensure sessions table exists (one-time setup check)
    $createTable = ($driver === 'pgsql') 
        ? "CREATE TABLE IF NOT EXISTS sessions (id VARCHAR(128) NOT NULL PRIMARY KEY, data TEXT NOT NULL, last_access INTEGER NOT NULL)"
        : "CREATE TABLE IF NOT EXISTS `sessions` (`id` varchar(128) NOT NULL, `data` text NOT NULL, `last_access` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($createTable);

    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);

} catch (PDOException $e) {
    die("<h2 style='color:red'>Database Connection Failed!</h2>" . 
        "<p>Server Host: <b>$hostName</b></p>" .
        "<p>Target Driver: <b>$driver</b></p>" .
        "<p>Error: " . $e->getMessage() . "</p>");
}
?>