<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

$userId = $_SESSION['user_id'];
$wordId = (int)($data['word_id'] ?? 0);
$correct = (bool)($data['correct'] ?? false);

try {
    // Fetch current SRS status for this word
    $stmt = $pdo->prepare("SELECT * FROM review_queue WHERE user_id = ? AND word_id = ?");
    $stmt->execute([$userId, $wordId]);
    $srs = $stmt->fetch();

    if ($srs) {
        $ef = (float)($srs['ease_factor'] ?? 2.50);
        $interval = (int)($srs['interval_days'] ?? 1);
        $reps = (int)($srs['repetitions'] ?? 0);

        if ($correct) {
            $reps += 1;
            $interval = max(1, round($interval * $ef));
            $ef = min(3.00, $ef + 0.10);
        } else {
            $reps = 0;
            $interval = 1;
            $ef = max(1.30, $ef - 0.30);
        }
        
        $interval = min(60, $interval);
        $nextReview = date('Y-m-d', strtotime("+$interval days"));

        $up = $pdo->prepare("
            UPDATE review_queue 
            SET ease_factor = ?, interval_days = ?, repetitions = ?, next_review = ? 
            WHERE user_id = ? AND word_id = ?
        ");
        $up->execute([$ef, $interval, $reps, $nextReview, $userId, $wordId]);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
