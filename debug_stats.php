<?php
require 'includes/db.php';
session_start();
$uid = $_SESSION['user_id'] ?? 0;

echo "<h3>Diagnostic for User ID: $uid</h3>";

try {
    echo "<h4>Academic Stats:</h4>";
    $stmt = $pdo->prepare("SELECT * FROM academic_stats WHERE user_id = ?");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($rows, true) . "</pre>";

    echo "<h4>Daily Goals (Today):</h4>";
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM daily_goals WHERE user_id = ? AND goal_date = ?");
    $stmt->execute([$uid, $today]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($goal, true) . "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
