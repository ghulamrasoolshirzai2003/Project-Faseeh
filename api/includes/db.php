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
} catch (PDOException $e) {
    die("<h2 style='color:red'>Database Connection Failed!</h2>" . 
        "<p>Server Host: <b>$hostName</b></p>" .
        "<p>Target Driver: <b>$driver</b></p>" .
        "<p>Error: " . $e->getMessage() . "</p>");
}
?>