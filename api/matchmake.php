<?php
// api/matchmake.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$userId = $_SESSION['user_id'];

// Self-healing DB
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS live_battles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        player1_id INT NOT NULL,
        player2_id INT DEFAULT NULL,
        p1_score INT DEFAULT 0,
        p2_score INT DEFAULT 0,
        status ENUM('waiting', 'playing', 'finished') DEFAULT 'waiting',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Clean up stale waiting games (> 1 min old)
$pdo->exec("UPDATE live_battles SET status = 'finished' WHERE status = 'waiting' AND created_at < NOW() - INTERVAL 1 MINUTE");

// Check if user is already in a waiting or playing game
$stmt = $pdo->prepare("SELECT * FROM live_battles WHERE (player1_id = ? OR player2_id = ?) AND status != 'finished' ORDER BY id DESC LIMIT 1");
$stmt->execute([$userId, $userId]);
$myGame = $stmt->fetch(PDO::FETCH_ASSOC);

if ($myGame) {
    echo json_encode(["status" => "success", "game" => $myGame]);
    exit;
}

// Check for any waiting games created by someone else
$stmt = $pdo->prepare("SELECT * FROM live_battles WHERE status = 'waiting' AND player1_id != ? ORDER BY id ASC LIMIT 1");
$stmt->execute([$userId]);
$waitingGame = $stmt->fetch(PDO::FETCH_ASSOC);

if ($waitingGame) {
    // Join it!
    $stmt = $pdo->prepare("UPDATE live_battles SET player2_id = ?, status = 'playing' WHERE id = ? AND status = 'waiting'");
    $stmt->execute([$userId, $waitingGame['id']]);
    
    // Fetch updated
    $stmt = $pdo->prepare("SELECT * FROM live_battles WHERE id = ?");
    $stmt->execute([$waitingGame['id']]);
    $joined = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "game" => $joined]);
    exit;
}

// Create new game
$stmt = $pdo->prepare("INSERT INTO live_battles (player1_id) VALUES (?)");
$stmt->execute([$userId]);
$newId = $pdo->lastInsertId();

$stmt = $pdo->prepare("SELECT * FROM live_battles WHERE id = ?");
$stmt->execute([$newId]);
$newGame = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "game" => $newGame]);
?>
