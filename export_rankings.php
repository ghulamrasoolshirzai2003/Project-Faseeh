<?php
session_start();
require 'includes/db.php';

// The new "Safe" check
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    die("Unauthorized Access: Please log out and log back in as an Admin.");
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Faseeh_Results_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Rank', 'Full Name', 'Username', 'Level', 'Total Score', 'Streak', 'Wins', 'Losses', 'Points Lost']);

try {
    $sql = "SELECT u.full_name, u.username, u.selected_level, 
                   COALESCE(p.total_score, 0) as total_score, 
                   COALESCE(p.current_streak, 0) as current_streak, 
                   COALESCE(p.wins, 0) as wins, 
                   COALESCE(p.losses, 0) as losses, 
                   COALESCE(p.points_lost, 0) as points_lost
            FROM users u 
            LEFT JOIN progress p ON u.id = p.user_id 
            WHERE u.role != 'admin' 
            ORDER BY total_score DESC";
            
    $stmt = $pdo->query($sql);
    $rank = 1;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$rank++, $row['full_name'], $row['username'], $row['selected_level'], $row['total_score'], $row['current_streak'], $row['wins'], $row['losses'], $row['points_lost']]);
    }
    fclose($output);
    exit;
} catch (PDOException $e) {
    die("Export Error: " . $e->getMessage());
}
?>