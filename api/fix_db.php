<?php
require 'includes/db.php';

try {
    // 1. Widen the password column to 255 characters (Fixes the cutoff bug)
    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");
    
    // 2. Ensure Role column exists
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'student'");

    // 3. Reset the Admin Account one last time
    $pdo->prepare("DELETE FROM users WHERE username = 'admin'")->execute();
    
    $pass = password_hash('123', PASSWORD_DEFAULT);
    // Explicitly set role to 'admin'
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, selected_level) VALUES ('System Admin', 'admin', ?, 'admin', 'Advanced')");
    $stmt->execute([$pass]);

    echo "<h1 style='color:green'>✅ Database Fixed!</h1>";
    echo "<p>Password column expanded. Admin reset.</p>";
    echo "<a href='admin_login.php'>Go to Admin Login</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>