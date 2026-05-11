<?php
/**
 * Faseeh v6.0 Migration - User Profiles & Social Features
 * Adds avatar and bio support to the users table.
 */
require 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Starting Faseeh v6.0 Migration (Profiles)...</h2>";

    // 1. Add Avatar column if it doesn't exist
    $checkAvatar = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'avatar'");
    if($checkAvatar->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `users` ADD `avatar` varchar(255) DEFAULT 'default_avatar.png' AFTER `full_name`");
        echo "✅ Added 'avatar' column to users.<br>";
    }

    // 2. Add Bio column
    $checkBio = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'bio'");
    if($checkBio->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `users` ADD `bio` varchar(500) DEFAULT 'I am learning Arabic on Faseeh!' AFTER `avatar`");
        echo "✅ Added 'bio' column to users.<br>";
    }

    echo "<h3 style='color:green'>Migration v6.0 Complete! The Social Profile system is ready.</h3>";
    echo "<a href='profile.php' style='display:inline-block; padding:10px 20px; background:#f2994a; color:white; text-decoration:none; border-radius:10px;'>Go to Profile</a>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Migration Failed:</h3> " . $e->getMessage());
}
?>
