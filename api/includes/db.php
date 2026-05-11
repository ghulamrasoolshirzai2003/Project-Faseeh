<?php
/**
 * ============================================================
 * FASEEH — ELITE DATABASE CONNECTION (POSTGRES / NEON)
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
    
    // Debug point 1
    // echo "<!-- DEBUG: Connecting to $host... -->";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5, // Fast timeout for debugging
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- 🚀 SESSION HANDLER ---
    // Temporarily disabled custom handler to rule out issues
    /*
    require_once __DIR__ . '/session_handler.php';
    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);
    */

} catch (PDOException $e) {
    die("<h2 style='color:red'>Database Connection Failed!</h2>" . 
        "<p>Server Host: <b>$hostName</b></p>" .
        "<p>Target Driver: <b>$driver</b></p>" .
        "<p>Error: " . $e->getMessage() . "</p>");
}
?>