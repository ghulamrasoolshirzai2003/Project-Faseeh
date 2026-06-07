<?php
// includes/progress.php
// Centralized Engine for XP and Progress Distribution

function saveUserProgress($pdo, $user_id, $xp_gained, $mode = 'general', $correct = 0, $wrong = 0) {
    if (!$user_id || ($xp_gained == 0 && $correct == 0 && $wrong == 0)) {
        return false;
    }

    try {
        $today = date('Y-m-d');
        
        // 1. Update Global Chart Table (XP History)
        if ($xp_gained > 0) {
            $stmt = $pdo->prepare("INSERT INTO user_xp_history (user_id, xp_gained, progress_date) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $xp_gained, $today]);
        }

        // 2. Update Main Progress
        $total_attempts = $correct + $wrong;
        
        // Ensure academic columns exist safely
        try {
            $pdo->exec("ALTER TABLE progress ADD COLUMN academic_xp INT DEFAULT 0, ADD COLUMN academic_correct_count INT DEFAULT 0");
        } catch(Exception $ex) {}
        
        $academic_xp_add = ($mode === 'essay' || $mode === 'majlis') ? $xp_gained : 0;
        $academic_correct_add = ($mode === 'essay' && $correct > 0) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE progress SET 
            xp = xp + ?, 
            total_score = total_score + ?, 
            academic_xp = academic_xp + ?,
            academic_correct_count = academic_correct_count + ?,
            wins = wins + ?, 
            accuracy_total = accuracy_total + ?,
            accuracy_correct = accuracy_correct + ?,
            last_active = NOW() 
            WHERE user_id = ?");
        $stmt->execute([$xp_gained, $xp_gained, $academic_xp_add, $academic_correct_add, $correct, $total_attempts, $correct, $user_id]);

        // 3. Update Daily Goals
        if ($correct > 0 || $xp_gained > 0) {
            $pdo->prepare("
                INSERT INTO daily_goals (user_id, goal_date, words_completed, xp_earned) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                words_completed = words_completed + ?, 
                xp_earned = xp_earned + ?
            ")->execute([$user_id, $today, $correct, $xp_gained, $correct, $xp_gained]);
        }

        // 4. Update Academic Stats
        if ($mode !== 'general' && ($correct > 0 || $wrong > 0)) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS academic_stats (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT, mode VARCHAR(50),
                    correct_answers INT DEFAULT 0, wrong_answers INT DEFAULT 0,
                    UNIQUE KEY(user_id, mode)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            } catch(Exception $ex) {}

            $pdo->prepare("
                INSERT INTO academic_stats (user_id, mode, correct_answers, wrong_answers) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                correct_answers = correct_answers + ?, 
                wrong_answers = wrong_answers + ?
            ")->execute([$user_id, $mode, $correct, $wrong, $correct, $wrong]);
        }
        
        // 5. Complete Student Assignments (Auto-Hook)
        try {
            // Find active assignment matching mode for user
            $stmt = $pdo->prepare("
                SELECT sa.id 
                FROM student_assignments sa
                JOIN classroom_assignments ca ON sa.assignment_id = ca.id
                WHERE sa.student_id = ? AND sa.status = 'assigned' AND ca.game_mode = ? AND ca.due_date >= NOW()
                LIMIT 1
            ");
            $stmt->execute([$user_id, $mode]);
            $assign = $stmt->fetch();
            if ($assign) {
                $completed = $pdo->prepare("
                    UPDATE student_assignments 
                    SET status = 'completed', score = ?, completed_at = NOW() 
                    WHERE id = ?
                ");
                $completed->execute([($correct * 10) + $xp_gained, $assign['id']]);
            }
        } catch(Exception $ex) {}
        
        return true;
    } catch (Exception $e) {
        error_log("saveUserProgress Error: " . $e->getMessage());
        return false;
    }
}
?>
