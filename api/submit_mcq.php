<?php
/**
 * API: Submit MCQ answer
 * Handles scoring, daily goals, streak, achievements, spaced repetition
 */
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'No data received']);
    exit;
}

$wordId = $input['word_id'] ?? 0;
$isCorrect = $input['correct'] ?? false;
$timeTaken = $input['time_taken'] ?? 10;

try {
    // Ensure progress record exists
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch();

    if (!$stats) {
        $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak, wins, losses, mcq_wins, mcq_losses, accuracy_total, accuracy_correct, points_lost, attempts) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)")
            ->execute([$uid]);
        $stats = ['total_score'=>0,'xp'=>0,'current_streak'=>0,'daily_streak'=>0,'wins'=>0,'losses'=>0,'mcq_wins'=>0,'mcq_losses'=>0,'accuracy_total'=>0,'accuracy_correct'=>0,'points_lost'=>0,'attempts'=>0,'longest_streak'=>0,'last_play_date'=>null];
    }

    $score = $stats['total_score'];
    $xp = $stats['xp'] ?? 0;
    $streak = $stats['current_streak'];
    $mcqWins = $stats['mcq_wins'] ?? 0;
    $mcqLosses = $stats['mcq_losses'] ?? 0;
    $accTotal = ($stats['accuracy_total'] ?? 0) + 1;
    $accCorrect = $stats['accuracy_correct'] ?? 0;
    $pointsLost = $stats['points_lost'] ?? 0;
    $scoreChange = 0;

    if ($isCorrect) {
        $scoreChange = 10 + max(0, (10 - $timeTaken)); // Speed bonus
        $score += $scoreChange;
        $xp += $scoreChange;
        $streak += 1;
        $mcqWins += 1;
        $accCorrect += 1;

        // Record as learned
        if ($wordId > 0) {
            $check = $pdo->prepare("SELECT id FROM user_solved_words WHERE user_id=? AND word_id=?");
            $check->execute([$uid, $wordId]);
            if ($check->rowCount() == 0) {
                $pdo->prepare("INSERT INTO user_solved_words (user_id, word_id, solved_at) VALUES (?, ?, NOW())")
                    ->execute([$uid, $wordId]);
            }

            // Add to spaced repetition queue
            $today = date('Y-m-d');
            $nextReview = date('Y-m-d', strtotime('+1 day'));
            $pdo->prepare("INSERT INTO review_queue (user_id, word_id, next_review, last_reviewed) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE repetitions = repetitions + 1, interval_days = LEAST(interval_days * 2, 30), next_review = DATE_ADD(CURDATE(), INTERVAL LEAST(interval_days * 2, 30) DAY), last_reviewed = NOW()")
                ->execute([$uid, $wordId, $nextReview]);
        }
    } else {
        $scoreChange = -5;
        $score = max(0, $score - 5);
        $streak = 0;
        $mcqLosses += 1;
        $pointsLost += 5;

        // Wrong answer — schedule sooner review
        if ($wordId > 0) {
            $nextReview = date('Y-m-d', strtotime('+1 day'));
            $pdo->prepare("INSERT INTO review_queue (user_id, word_id, next_review, ease_factor, interval_days) VALUES (?, ?, ?, 1.50, 1) ON DUPLICATE KEY UPDATE ease_factor = GREATEST(1.30, ease_factor - 0.20), interval_days = 1, next_review = ?, last_reviewed = NOW()")
                ->execute([$uid, $wordId, $nextReview, $nextReview]);
        }
    }

    // Update daily streak
    $today = date('Y-m-d');
    $lastPlay = $stats['last_play_date'] ?? null;
    $dailyStreak = $stats['daily_streak'] ?? 0;
    $longestStreak = $stats['longest_streak'] ?? 0;

    if ($lastPlay !== $today) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($lastPlay === $yesterday) {
            $dailyStreak += 1;
        } elseif ($lastPlay === null || $lastPlay < $yesterday) {
            $dailyStreak = 1; // Reset streak
        }
        if ($dailyStreak > $longestStreak) {
            $longestStreak = $dailyStreak;
        }
    }

    // Update daily goal
    $pdo->prepare("INSERT INTO daily_goals (user_id, goal_date, words_completed, xp_earned) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE words_completed = words_completed + 1, xp_earned = xp_earned + ?")
        ->execute([$uid, $today, max(0, $scoreChange), max(0, $scoreChange)]);

    // Check if daily goal completed
    $stmt = $pdo->prepare("SELECT * FROM daily_goals WHERE user_id = ? AND goal_date = ?");
    $stmt->execute([$uid, $today]);
    $goal = $stmt->fetch();
    if ($goal && $goal['words_completed'] >= $goal['words_target'] && !$goal['completed']) {
        // Award bonus XP for completing daily goal
        $xp += 25;
        $pdo->prepare("UPDATE daily_goals SET completed = 1 WHERE id = ?")->execute([$goal['id']]);
    }

    // Save progress
    $update = $pdo->prepare("UPDATE progress SET total_score=?, xp=?, current_streak=?, daily_streak=?, longest_streak=?, mcq_wins=?, mcq_losses=?, accuracy_total=?, accuracy_correct=?, points_lost=?, last_active=NOW(), last_play_date=?, attempts=attempts+1 WHERE user_id=?");
    $update->execute([$score, $xp, $streak, $dailyStreak, $longestStreak, $mcqWins, $mcqLosses, $accTotal, $accCorrect, $pointsLost, $today, $uid]);

    // PERSISTENCE: Increment session count
    $pdo->prepare("UPDATE user_active_sessions SET questions_completed = questions_completed + 1 WHERE user_id = ? AND mode = 'quiz'")
        ->execute([$uid]);

    // Log game session
    $pdo->prepare("INSERT INTO game_sessions (user_id, game_type, word_id, result, time_taken, score_change) VALUES (?, 'mcq', ?, ?, ?, ?)")
        ->execute([$uid, $wordId, $isCorrect ? 'win' : 'lose', $timeTaken, $scoreChange]);

    echo json_encode([
        'status' => 'success',
        'correct' => $isCorrect,
        'total_score' => $score,
        'xp' => $xp,
        'current_streak' => $streak,
        'daily_streak' => $dailyStreak,
        'score_change' => $scoreChange
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
