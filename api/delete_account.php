<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 1. Delete progress
    $pdo->prepare("DELETE FROM progress WHERE user_id = ?")->execute([$userId]);
    
    // 2. Delete achievements
    $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?")->execute([$userId]);
    
    // 3. Delete relationships (Teacher/Parent links)
    $pdo->prepare("DELETE FROM user_relationships WHERE student_id = ? OR parent_id = ?")->execute([$userId, $userId]);
    
    // 4. Delete feedback (if any)
    $pdo->prepare("DELETE FROM parent_feedback WHERE student_id = ? OR parent_id = ?")->execute([$userId, $userId]);
    
    // 5. Delete from users table
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

    $pdo->commit();

    // Clear session and logout
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Account deleted permanently.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
