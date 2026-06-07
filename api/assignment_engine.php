<?php
// api/assignment_engine.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

// --- SELF-HEALING DB TABLES ---
try {
    // 1. Classroom Assignments
    $pdo->exec("CREATE TABLE IF NOT EXISTS classroom_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        student_id INT NULL, -- Can assign to a specific student
        game_mode VARCHAR(50) NOT NULL,
        level VARCHAR(50) NOT NULL,
        due_date DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Student Assignments (Completion track)
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('assigned', 'completed') DEFAULT 'assigned',
        score INT DEFAULT 0,
        completed_at TIMESTAMP NULL,
        UNIQUE KEY unique_student_assign (assignment_id, student_id)
    )");
} catch (Exception $e) {}

$data = json_decode(file_get_contents("php://input"), true) ?? $_REQUEST;
$action = $data['action'] ?? '';

switch ($action) {
    case 'create_assignment':
        // Teacher authentication
        $isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        if (!$isTeacher && !$isAdmin) {
            echo json_encode(["error" => "Only teachers or administrators can issue assignments."]);
            exit;
        }

        $teacherId = $_SESSION['user_id'];
        $studentId = (int)($data['student_id'] ?? 0);
        $gameMode = trim($data['game_mode'] ?? '');
        $level = trim($data['level'] ?? 'beginner');
        $dueDate = trim($data['due_date'] ?? '');

        if (!$studentId || !$gameMode || !$dueDate) {
            echo json_encode(["error" => "Missing required fields (student, game mode, or due date)"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO classroom_assignments (teacher_id, student_id, game_mode, level, due_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$teacherId, $studentId, $gameMode, $level, $dueDate]);
            $assignId = $pdo->lastInsertId();

            // Insert matching row in completion track
            $track = $pdo->prepare("INSERT INTO student_assignments (assignment_id, student_id, status) VALUES (?, ?, 'assigned')");
            $track->execute([$assignId, $studentId]);

            echo json_encode(["success" => true, "message" => "Assignment successfully issued!"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'get_student_assignments':
        $studentId = $_SESSION['user_id'] ?? 0;
        if (!$studentId) {
            echo json_encode(["error" => "Not logged in"]);
            exit;
        }

        try {
            // Fetch all assigned active homework tasks that are not yet past due or completed
            $stmt = $pdo->prepare("
                SELECT sa.id as track_id, sa.status, ca.game_mode, ca.level, ca.due_date, u.full_name as teacher_name
                FROM student_assignments sa
                JOIN classroom_assignments ca ON sa.assignment_id = ca.id
                JOIN users u ON ca.teacher_id = u.id
                WHERE sa.student_id = ? AND sa.status = 'assigned' AND ca.due_date >= NOW()
                ORDER BY ca.due_date ASC
            ");
            $stmt->execute([$studentId]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "assignments" => $assignments]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'get_teacher_reports':
        $teacherId = $_SESSION['user_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("
                SELECT ca.id, ca.game_mode, ca.level, ca.due_date, u.username as student_name, sa.status, sa.score, sa.completed_at
                FROM classroom_assignments ca
                JOIN student_assignments sa ON ca.id = sa.assignment_id
                JOIN users u ON sa.student_id = u.id
                WHERE ca.teacher_id = ?
                ORDER BY ca.created_at DESC
            ");
            $stmt->execute([$teacherId]);
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "reports" => $reports]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid assignment engine action"]);
}
?>
