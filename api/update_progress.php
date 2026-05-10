<?php
session_start();
// InfinityFree/Shared hosting fix: ensures errors are logged but not shown as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Use absolute path for safety on live servers
$dbPath = __DIR__ . '/../includes/db.php';
if (!file_exists($dbPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Database config missing at ' . $dbPath]);
    exit;
}
require_once $dbPath;

header('Content-Type: application/json');

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session lost. Please refresh and login again.']);
    exit;
}

$uid = $_SESSION['user_id'];
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['error' => 'No data received']);
    exit;
}

$result = $data['result'];
$word_id = $data['word_id'] ?? 0;
$time_taken = $data['time_taken'] ?? 30;

try {
    // 1.5 DEEP DATABASE REPAIR (Self-Healing)
    $columns = [
        'total_score' => 'INT DEFAULT 0',
        'xp' => 'INT DEFAULT 0',
        'current_streak' => 'INT DEFAULT 0',
        'daily_streak' => 'INT DEFAULT 0',
        'longest_streak' => 'INT DEFAULT 0',
        'wins' => 'INT DEFAULT 0',
        'losses' => 'INT DEFAULT 0',
        'accuracy_total' => 'INT DEFAULT 0',
        'accuracy_correct' => 'INT DEFAULT 0',
        'attempts' => 'INT DEFAULT 0',
        'points_lost' => 'INT DEFAULT 0',
        'academic_correct_count' => 'INT DEFAULT 0',
        'last_play_date' => 'DATE NULL'
    ];
    foreach ($columns as $col => $type) {
        try { $pdo->exec("ALTER TABLE progress ADD COLUMN $col $type"); } catch(Exception $e) {}
    }

    // 2. CHECK IF USER RECORD EXISTS IN PROGRESS TABLE
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch();

    if(!$stats) {
        $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak, longest_streak, wins, losses, mcq_wins, mcq_losses, accuracy_total, accuracy_correct, points_lost, attempts) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)")->execute([$uid]);
        $stats = ['total_score'=>0, 'xp'=>0, 'current_streak'=>0, 'daily_streak'=>0, 'longest_streak'=>0, 'wins'=>0, 'losses'=>0, 'points_lost'=>0, 'attempts'=>0, 'accuracy_total'=>0, 'accuracy_correct'=>0, 'last_play_date'=>null];
    }

    // 3. CALCULATE STATS
    $score = intval($stats['total_score'] ?? 0);
    $xp = intval($stats['xp'] ?? 0);
    $streak = intval($stats['current_streak'] ?? 0);
    $wins = intval($stats['wins'] ?? 0);
    $losses = intval($stats['losses'] ?? 0);
    $lost_p = intval($stats['points_lost'] ?? 0);
    $attempts = intval($stats['attempts'] ?? 0) + 1;
    $accTotal = intval($stats['accuracy_total'] ?? 0) + 1;
    $accCorrect = intval($stats['accuracy_correct'] ?? 0);
    $scoreChange = 0;

    if($result === 'win') {
        $scoreChange = 10;
        $score += $scoreChange;
        $xp += $scoreChange;
        $streak += 1;
        $wins += 1;
        $accCorrect += 1;
        
        // --- THE FIX FOR REPEATING WORDS ---
        if ($word_id > 0) {
            try {
                $check = $pdo->prepare("SELECT id FROM user_progress WHERE user_id=? AND word_id=?");
                $check->execute([$uid, $word_id]);
                if ($check->rowCount() == 0) {
                    $pdo->prepare("INSERT INTO user_progress (user_id, word_id, solved_at) VALUES (?, ?, NOW())")
                        ->execute([$uid, $word_id]);
                }

                // Self-heal: ensure review_queue columns exist
                $pdo->exec("ALTER TABLE review_queue ADD COLUMN IF NOT EXISTS last_reviewed TIMESTAMP NULL");
                $pdo->exec("ALTER TABLE review_queue ADD COLUMN IF NOT EXISTS repetitions INT DEFAULT 0");
                $pdo->exec("ALTER TABLE review_queue ADD COLUMN IF NOT EXISTS interval_days INT DEFAULT 1");

                // Add to spaced repetition queue
                $nextReview = date('Y-m-d', strtotime('+1 day'));
                $pdo->prepare("INSERT INTO review_queue (user_id, word_id, next_review, last_reviewed) VALUES (?, ?, ?, NOW()) 
                               ON DUPLICATE KEY UPDATE repetitions = repetitions + 1, interval_days = LEAST(interval_days * 2, 30), 
                               next_review = DATE_ADD(CURDATE(), INTERVAL LEAST(interval_days * 2, 30) DAY), last_reviewed = NOW()")
                    ->execute([$uid, $word_id, $nextReview]);
            } catch(Exception $e) {}
        }
    } else {
        $scoreChange = -5;
        $score = max(0, $score - 5);
        $streak = 0;
        $losses += 1;
        $lost_p += 5;

        // Wrong — add to review queue with short interval
        if ($word_id > 0) {
            try {
                $pdo->exec("ALTER TABLE review_queue ADD COLUMN IF NOT EXISTS last_reviewed TIMESTAMP NULL");
                $nextReview = date('Y-m-d', strtotime('+1 day'));
                $pdo->prepare("INSERT INTO review_queue (user_id, word_id, next_review, ease_factor, interval_days) VALUES (?, ?, ?, 1.50, 1) 
                               ON DUPLICATE KEY UPDATE ease_factor = GREATEST(1.30, ease_factor - 0.20), interval_days = 1, 
                               next_review = ?, last_reviewed = NOW()")
                    ->execute([$uid, $word_id, $nextReview, $nextReview]);
            } catch(Exception $e) {}
        }
    }

    // 4. DAILY STREAK LOGIC
    $today = date('Y-m-d');
    $lastPlay = $stats['last_play_date'] ?? null;
    $dailyStreak = $stats['daily_streak'] ?? 0;
    $longestStreak = $stats['longest_streak'] ?? 0;

    if ($lastPlay !== $today) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($lastPlay === $yesterday) {
            $dailyStreak += 1;
        } elseif ($lastPlay === null || $lastPlay < $yesterday) {
            $dailyStreak = 1;
        }
        if ($dailyStreak > $longestStreak) {
            $longestStreak = $dailyStreak;
        }
    }

    // 5. DAILY GOAL TRACKING (Wrapped in try-catch to prevent blocking main save)
    try {
        // Self-heal: ensure xp_earned column exists
        $pdo->exec("ALTER TABLE daily_goals ADD COLUMN IF NOT EXISTS xp_earned INT DEFAULT 0");
        
        $pdo->prepare("INSERT INTO daily_goals (user_id, goal_date, words_completed, xp_earned) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE words_completed = words_completed + 1, xp_earned = xp_earned + ?")
            ->execute([$uid, $today, max(0, $scoreChange), max(0, $scoreChange)]);

        $stmt = $pdo->prepare("SELECT * FROM daily_goals WHERE user_id = ? AND goal_date = ?");
        $stmt->execute([$uid, $today]);
        $goal = $stmt->fetch();
        $goalComplete = false;
        if ($goal && isset($goal['words_target']) && $goal['words_completed'] >= $goal['words_target'] && !$goal['completed']) {
            $pdo->prepare("UPDATE daily_goals SET completed = 1 WHERE id = ?")->execute([$goal['id']]);
            $goalComplete = true;
        }
    } catch(Exception $e) { $goalComplete = false; }

    // 6. SAVE TO DATABASE (Simplified for reliability)
    $stmt = $pdo->prepare("UPDATE progress SET 
        total_score = ?, wins = ?, losses = ?, current_streak = ?, daily_streak = ?, 
        attempts = attempts + 1, last_active = NOW(), last_play_date = ? 
        WHERE user_id = ?");
    
    $success = $stmt->execute([$score, $wins, $losses, $streak, $dailyStreak, $today, $uid]);

    if (!$success) {
        echo json_encode(['status' => 'error', 'message' => 'Database update returned false']);
        exit;
    }

    // 6.5 Update Academic Report
    try {
        $acad_col = ($result === 'win') ? 'correct_answers' : 'wrong_answers';
        $pdo->prepare("INSERT INTO academic_stats (user_id, mode, $acad_col) VALUES (?, 'hangman', 1) 
                       ON DUPLICATE KEY UPDATE $acad_col = $acad_col + 1")->execute([$uid]);
    } catch(Exception $e) {}

    echo json_encode([
        'status' => 'success',
        'total_score' => $score,
        'wins' => $wins,
        'losses' => $losses,
        'current_streak' => $streak
    ]);

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>