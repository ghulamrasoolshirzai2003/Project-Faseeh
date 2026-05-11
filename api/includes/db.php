<?php
/**
 * ============================================================
 * FASEEH — ELITE DATABASE CONNECTION (POSTGRES / NEON)
 * ============================================================
 */

// Detect if we are on Vercel or Local
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);

if ($isLocal) {
    // LOCAL SETTINGS (MySQL for XAMPP)
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
    die("<h2 style='color:red'>Database Connection Failed!</h2><br>" . $e->getMessage());
}
?>