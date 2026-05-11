<?php
/**
 * ============================================================
 * FASEEH — ELITE DATABASE CONNECTION (POSTGRES / NEON)
 * ============================================================
 */

// Detect if we are on Vercel (Production) or Local (XAMPP)
// We check for the VERCEL environment variable or the server name
$isVercel = isset($_SERVER['VERCEL']) || (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'vercel.app') !== false);

if (!$isVercel) {
    // 🏠 LOCAL SETTINGS (MySQL for XAMPP)
    $host = 'localhost';
    $db   = 'faseeh_db';
    $user = 'root';
    $pass = '';
    $driver = 'mysql';
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
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Show a more helpful message if it fails
    die("<h2 style='color:red'>Database Connection Failed!</h2>" . 
        "<p>Current Environment: " . ($isVercel ? 'Vercel (Production)' : 'Localhost') . "</p>" .
        "<p>Error: " . $e->getMessage() . "</p>");
}
?>