<?php
/**
 * API: Check and unlock achievements
 * Called after each game to see if new achievements were earned
 */
session_start();
require '../includes/db.php';
require_once '../includes/progress.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid = $_SESSION['user_id'];
$newlyUnlocked = [];

try {
    // Get current stats
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch();

    if (!$stats) {
        echo json_encode(['unlocked' => []]);
        exit;
    }

    // Get words learned count
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_solved_words WHERE user_id = ?");
    $stmt->execute([$uid]);
    $wordsLearned = $stmt->fetch()['cnt'] ?? 0;

    // Get all achievements not yet unlocked by this user
    $stmt = $pdo->prepare("
        SELECT a.* FROM achievements a 
        WHERE a.id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?)
    ");
    $stmt->execute([$uid]);
    $pending = $stmt->fetchAll();

    foreach ($pending as $ach) {
        $earned = false;

        switch ($ach['requirement_type']) {
            case 'total_words':
                $earned = $wordsLearned >= $ach['requirement_value'];
                break;
            case 'daily_streak':
                $earned = ($stats['daily_streak'] ?? 0) >= $ach['requirement_value'];
                break;
            case 'wins':
                $earned = ($stats['wins'] ?? 0) >= $ach['requirement_value'];
                break;
            case 'total_score':
                $earned = ($stats['total_score'] ?? 0) >= $ach['requirement_value'];
                break;
            case 'accuracy':
                $total = $stats['accuracy_total'] ?? 0;
                $correct = $stats['accuracy_correct'] ?? 0;
                if ($total >= 20) { // Minimum 20 attempts to qualify
                    $acc = round(($correct / $total) * 100);
                    $earned = $acc >= $ach['requirement_value'];
                }
                break;
            // speed and perfect_mcq are checked directly in-game via separate calls
        }

        if ($earned) {
            // Unlock achievement
            $pdo->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)")
                ->execute([$uid, $ach['id']]);
            
            // Award XP
            saveUserProgress($pdo, $uid, $ach['xp_reward'], 'general');

            $newlyUnlocked[] = [
                'slug' => $ach['slug'],
                'title' => $ach['title'],
                'description' => $ach['description'],
                'icon' => $ach['icon'],
                'xp_reward' => $ach['xp_reward']
            ];
        }
    }

    echo json_encode(['unlocked' => $newlyUnlocked]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
