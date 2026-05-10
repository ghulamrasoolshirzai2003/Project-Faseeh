<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { exit; }

$mode = $_GET['mode'] ?? 'hangman';
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = ?");
$stmt->execute([$uid, $mode]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    echo json_encode(["questions_completed" => 0, "total_target" => 10]);
} else {
    echo json_encode($res);
}
?>
