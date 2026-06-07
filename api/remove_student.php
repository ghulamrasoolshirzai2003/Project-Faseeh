<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'No student specified']);
    exit;
}

try {
    // Delete the relationship
    $stmt = $pdo->prepare("DELETE FROM user_relationships WHERE student_id = ? AND parent_id = ? AND relationship_type = 'teacher_of'");
    if ($stmt->execute([$student_id, $teacher_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
