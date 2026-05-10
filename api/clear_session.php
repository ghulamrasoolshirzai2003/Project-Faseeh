<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { exit; }

$mode = $_GET['mode'] ?? 'hangman';
$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE user_active_sessions SET questions_completed = 0 WHERE user_id = ? AND mode = ?");
$stmt->execute([$uid, $mode]);

echo json_encode(["success" => true]);
?>
