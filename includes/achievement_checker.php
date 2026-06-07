<?php
/**
 * Achievement Checker Utility v2.0
 * Automatically checks and awards 10+ premium badges based on student milestones.
 */

function checkAchievements($pdo, $userId) {
    // 1. Fetch current progress
    $stmt = $pdo->prepare("SELECT xp, academic_xp, daily_streak, wins FROM progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $prog = $stmt->fetch();
    
    if (!$prog) return;

    $xp = (int)$prog['xp'];
    $academicXp = (int)$prog['academic_xp'];
    $streak = (int)$prog['daily_streak'];
    $wins = (int)$prog['wins'];
    $totalXp = $xp + $academicXp;

    // 2. Define Market-Ready Milestones
    $milestones = [
        // --- XP MILESTONES ---
        ['slug' => 'first_steps', 'icon' => '👣', 'title' => 'First Steps', 'desc' => 'Reached 100 Total XP', 'req' => $totalXp >= 100],
        ['slug' => 'fast_learner', 'icon' => '🚀', 'title' => 'Fast Learner', 'desc' => 'Reached 500 Total XP', 'req' => $totalXp >= 500],
        ['slug' => 'pro_learner', 'icon' => '🏆', 'title' => 'Pro Learner', 'desc' => 'Reached 1,000 Total XP', 'req' => $totalXp >= 1000],
        ['slug' => 'master_linguist', 'icon' => '💎', 'title' => 'Master Linguist', 'desc' => 'Reached 5,000 Total XP', 'req' => $totalXp >= 5000],

        // --- STREAK MILESTONES ---
        ['slug' => 'streak_star', 'icon' => '🔥', 'title' => 'Consistency King', 'desc' => 'Maintained a 5-day learning streak', 'req' => $streak >= 5],
        ['slug' => 'unstoppable', 'icon' => '⚡', 'title' => 'Unstoppable', 'desc' => 'Maintained a 15-day learning streak', 'req' => $streak >= 15],

        // --- ACADEMIC MILESTONES ---
        ['slug' => 'scholar', 'icon' => '🎓', 'title' => 'The Scholar', 'desc' => 'Earned 500 Academic XP from lessons', 'req' => $academicXp >= 500],
        ['slug' => 'professor', 'icon' => '📜', 'title' => 'Academy Professor', 'desc' => 'Earned 2,000 Academic XP', 'req' => $academicXp >= 2000],

        // --- GAME MILESTONES ---
        ['slug' => 'first_victory', 'icon' => '🥇', 'title' => 'First Victory', 'desc' => 'Won your first educational game', 'req' => $wins >= 1],
        ['slug' => 'champion', 'icon' => '🏟️', 'title' => 'Arena Champion', 'desc' => 'Won 50 educational games', 'req' => $wins >= 50],
        
        // --- SPECIAL ---
        ['slug' => 'early_bird', 'icon' => '🌅', 'title' => 'Early Bird', 'desc' => 'Earned XP before 9 AM', 'req' => (date('H') < 9 && $totalXp > 10)]
    ];

    foreach ($milestones as $m) {
        if ($m['req']) {
            // Check if user already has this achievement
            $stmt = $pdo->prepare("SELECT id FROM achievements WHERE title = ?");
            $stmt->execute([$m['title']]);
            $ach = $stmt->fetch();
            
            if ($ach) {
                $achId = $ach['id'];
                
                $stmt = $pdo->prepare("SELECT 1 FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
                $stmt->execute([$userId, $achId]);
                
                if (!$stmt->fetch()) {
                    $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id, unlocked_at) VALUES (?, ?, NOW())")
                        ->execute([$userId, $achId]);
                }
            } else {
                // Auto-create missing achievement
                $pdo->prepare("INSERT INTO achievements (title, icon, description) VALUES (?, ?, ?)")
                    ->execute([$m['title'], $m['icon'], $m['desc']]);
                
                $achId = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id, unlocked_at) VALUES (?, ?, NOW())")
                    ->execute([$userId, $achId]);
            }
        }
    }
}
?>
