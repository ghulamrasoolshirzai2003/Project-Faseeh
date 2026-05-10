<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$uid = $_SESSION['user_id'];

try {
    // RESET LOGIC:
    // 1. Set Score, Wins, Losses, Streak to 0
    // 2. INCREASE 'attempts' by 1 (To track that they reset)
    $stmt = $pdo->prepare("UPDATE progress SET wins=0, losses=0, current_streak=0, total_score=0, attempts = attempts + 1 WHERE user_id = ?");
    $stmt->execute([$uid]);

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>