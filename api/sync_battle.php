<?php
// api/sync_battle.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$gameId = $data['game_id'] ?? 0;
$score = $data['score'] ?? 0;
$action = $data['action'] ?? 'sync'; // 'sync' or 'finish'

$stmt = $pdo->prepare("SELECT * FROM live_battles WHERE id = ? AND (player1_id = ? OR player2_id = ?)");
$stmt->execute([$gameId, $userId, $userId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    echo json_encode(["error" => "Game not found."]);
    exit;
}

if ($action === 'finish') {
    $pdo->prepare("UPDATE live_battles SET status = 'finished' WHERE id = ?")->execute([$gameId]);
    $game['status'] = 'finished';
}

$isPlayer1 = ($game['player1_id'] == $userId);
$updateCol = $isPlayer1 ? 'p1_score' : 'p2_score';
$oppScoreCol = $isPlayer1 ? 'p2_score' : 'p1_score';

if ($score > $game[$updateCol]) {
    $pdo->prepare("UPDATE live_battles SET $updateCol = ? WHERE id = ?")->execute([$score, $gameId]);
    $game[$updateCol] = $score;
}

// Fetch opponent's name if player2 has joined
$oppName = "Waiting for Opponent...";
if ($game['status'] === 'playing' || $game['status'] === 'finished') {
    $oppId = $isPlayer1 ? $game['player2_id'] : $game['player1_id'];
    if ($oppId) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$oppId]);
        $u = $stmt->fetch();
        if ($u) $oppName = $u['username'];
    }
}

echo json_encode([
    "status" => $game['status'],
    "my_score" => $game[$updateCol],
    "opp_score" => $game[$oppScoreCol],
    "opp_name" => $oppName
]);
?>
