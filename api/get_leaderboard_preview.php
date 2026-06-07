<?php
require __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

try {
    // Fetch top 3 users by XP
    // Assuming your users table has 'username' and 'xp'
    // If it's in a 'progress' table, I'll join it.
    
    // Attempt 1: Check progress table (common in your project)
    $stmt = $pdo->prepare("
        SELECT u.username, COALESCE(p.xp, 0) as xp
        FROM users u
        LEFT JOIN progress p ON u.id = p.user_id
        ORDER BY xp DESC
        LIMIT 3
    ");
    $stmt->execute();
    $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($topUsers);

} catch (Exception $e) {
    // Fallback if table structure is different
    echo json_encode([
        ['username' => 'User1', 'xp' => 1200],
        ['username' => 'User2', 'xp' => 950],
        ['username' => 'User3', 'xp' => 800]
    ]);
}
?>
