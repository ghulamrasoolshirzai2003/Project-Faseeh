<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$track = $data['track'] ?? 'msa';
if (!in_array($track, ['msa', 'quranic'])) {
    echo json_encode(["error" => "Invalid track"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Safely ensure learning_track column exists
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN learning_track VARCHAR(20) DEFAULT 'msa'");
} catch(Exception $e){}

try {
    $stmt = $pdo->prepare("UPDATE users SET learning_track = ? WHERE id = ?");
    $stmt->execute([$track, $userId]);
    
    $_SESSION['learning_track'] = $track;
    echo json_encode(["success" => true, "track" => $track]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
