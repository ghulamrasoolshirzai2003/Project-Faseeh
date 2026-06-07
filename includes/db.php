<?php
/**
 * ============================================================
 * FASEEH — DATABASE CONNECTION
 * ============================================================
 * This file auto-detects whether you're running locally (XAMPP)
 * or on a live server (InfinityFree / any hosting).
 * 
 * For live hosting: Update the LIVE SETTINGS below with your
 * hosting control panel's MySQL credentials.
 * ============================================================
 */

// =========================================================
// ENVIRONMENT DETECTION
// =========================================================
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) 
        || (($_SERVER['SERVER_PORT'] ?? 80) == 8080)
        || (php_sapi_name() === 'cli');

if ($isLocal) {
    // =====================================================
    // LOCAL / XAMPP SETTINGS (Don't touch these)
    // =====================================================
    $host = 'localhost';
    $db   = 'faseeh_db';
    $user = 'root';
    $pass = '';
} else {
    // =====================================================
    // 🌍 LIVE HOSTING SETTINGS 
    // Update these with your hosting panel credentials!
    // =====================================================
    $host = 'sql312.infinityfree.com';  // ← Your MySQL host from control panel
    $db   = 'if0_41852118_faseehdatabase';       // ← Your database name from control panel  
    $user = 'if0_41852118';              // ← Your MySQL username from control panel
    $pass = 'Shirzai3343';        // ← Your MySQL password from control panel
}

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    // FORCE ERROR REPORTING
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    if ($isLocal) {
        die("<h2 style='color:red'>Database Connection Failed!</h2><br>" . htmlspecialchars($e->getMessage()));
    } else {
        die("<h2 style='color:red'>Temporary Server Error</h2><p>Our language servers are undergoing maintenance. Please check back in a few minutes.</p>");
    }
}
?>