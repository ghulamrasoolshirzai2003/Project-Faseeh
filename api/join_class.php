<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$student_id = $_SESSION['user_id'];
$class_code = strtoupper(trim($_POST['class_code']));

if (empty($class_code)) {
    exit(json_encode(['success' => false, 'message' => 'Please enter a code.']));
}

// 1. Find teacher with this code
$stmt = $pdo->prepare("SELECT id FROM users WHERE class_code = ? AND role = 'teacher'");
$stmt->execute([$class_code]);
$teacher = $stmt->fetch();

if (!$teacher) {
    exit(json_encode(['success' => false, 'message' => 'Invalid class code.']));
}

$teacher_id = $teacher['id'];

// 2. Check if already joined
$stmt = $pdo->prepare("SELECT id FROM user_relationships WHERE student_id = ? AND parent_id = ? AND relationship_type = 'teacher_of'");
$stmt->execute([$student_id, $teacher_id]);
if ($stmt->fetch()) {
    exit(json_encode(['success' => false, 'message' => 'You are already in this class!']));
}

// 3. Join Class
$stmt = $pdo->prepare("INSERT INTO user_relationships (student_id, parent_id, relationship_type) VALUES (?, ?, 'teacher_of')");
if ($stmt->execute([$student_id, $teacher_id])) {
    echo json_encode(['success' => true, 'message' => 'Successfully joined the class!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
