<?php
/**
 * API: Submit review rating (SM-2 algorithm)
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']); exit;
}

$uid = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$wordId = $input['word_id'] ?? 0;
$rating = $input['rating'] ?? 'ok'; // hard, ok, easy

if ($wordId <= 0) {
    echo json_encode(['error' => 'Invalid word']); exit;
}

try {
    // Get current review data
    $stmt = $pdo->prepare("SELECT * FROM review_queue WHERE user_id = ? AND word_id = ?");
    $stmt->execute([$uid, $wordId]);
    $review = $stmt->fetch();

    if (!$review) {
        echo json_encode(['error' => 'Word not in review queue']); exit;
    }

    $ef = (float)$review['ease_factor'];
    $interval = (int)$review['interval_days'];
    $reps = (int)$review['repetitions'];

    // SM-2 inspired adjustments
    switch ($rating) {
        case 'hard':
            $ef = max(1.30, $ef - 0.30);
            $interval = 1;
            $reps = 0;
            break;
        case 'ok':
            $ef = max(1.30, $ef - 0.05);
            $interval = max(1, round($interval * $ef * 0.8));
            $reps += 1;
            break;
        case 'easy':
            $ef = min(3.00, $ef + 0.15);
            $interval = max(1, round($interval * $ef));
            $reps += 1;
            break;
    }

    // Cap interval at 60 days
    $interval = min(60, $interval);
    $nextReview = date('Y-m-d', strtotime("+{$interval} days"));

    // Update
    $update = $pdo->prepare("UPDATE review_queue SET ease_factor=?, interval_days=?, repetitions=?, next_review=?, last_reviewed=NOW() WHERE user_id=? AND word_id=?");
    $update->execute([$ef, $interval, $reps, $nextReview, $uid, $wordId]);

    // Log game session
    $pdo->prepare("INSERT INTO game_sessions (user_id, game_type, word_id, result, time_taken, score_change) VALUES (?, 'review', ?, 'win', 0, 0)")
        ->execute([$uid, $wordId]);

    echo json_encode([
        'status' => 'success',
        'next_review' => $nextReview,
        'interval' => $interval,
        'ease_factor' => $ef
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
