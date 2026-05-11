<?php
/**
 * FASEEH — MASTER RESET TOOL
 * This script wipes the database clean for a fresh start.
 */
require 'includes/db.php';

try {
    // 1. Wipe all data from tables
    $pdo->exec("TRUNCATE TABLE user_answered CASCADE");
    $pdo->exec("TRUNCATE TABLE academic_stats CASCADE");
    $pdo->exec("TRUNCATE TABLE daily_goals CASCADE");
    $pdo->exec("TRUNCATE TABLE review_queue CASCADE");
    $pdo->exec("TRUNCATE TABLE game_sessions CASCADE");
    $pdo->exec("TRUNCATE TABLE progress CASCADE");
    $pdo->exec("TRUNCATE TABLE users CASCADE");

    echo "<h2 style='color:green; font-family:sans-serif;'>✅ Database Wiped Successfully!</h2>";
    echo "<p style='font-family:sans-serif;'>All old accounts are gone. You can now <a href='index.php'>Register a New Account</a>.</p>";
} catch (Exception $e) {
    echo "<h2 style='color:red; font-family:sans-serif;'>❌ Reset Failed</h2>";
    echo "<p style='font-family:sans-serif;'>" . $e->getMessage() . "</p>";
}
?>
