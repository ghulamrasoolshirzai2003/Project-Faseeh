<?php
require 'includes/db.php';

try {
    $pass = password_hash('123', PASSWORD_DEFAULT);
    // Force update the admin password
    $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$pass]);
    
    echo "<h1>✅ Password Reset!</h1>";
    echo "<p>You can now login with <b>admin</b> / <b>123</b></p>";
    echo "<a href='admin_login.php'>Go to Login</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>