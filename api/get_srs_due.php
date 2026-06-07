<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

try {
    $stmt = $pdo->prepare("
        SELECT w.*, s.word_id 
        FROM review_queue s
        JOIN words w ON s.word_id = w.id
        WHERE s.user_id = ? AND s.next_review <= ?
        ORDER BY s.next_review ASC
    ");
    $stmt->execute([$userId, $now]);
    $due = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($due);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
