<?php
require 'includes/db.php';
echo "<h2>Local Database Check</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p style='color:green'>✅ Connection Successful!</p>";
    echo "<p>User Count: $count</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM words");
    $words = $stmt->fetchColumn();
    echo "<p>Word Count: $words</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
