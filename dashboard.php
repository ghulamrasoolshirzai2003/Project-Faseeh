<?php
session_start();
require 'includes/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php"); exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    header("Location: teacher_dashboard.php"); exit;
}
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit;
}

$uid = $_SESSION['user_id'];

// Fetch basic info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
if(!$stats) $stats = ['total_score'=>0,'xp'=>0,'current_streak'=>0,'daily_streak'=>0,'longest_streak'=>0,'wins'=>0,'losses'=>0,'mcq_wins'=>0,'mcq_losses'=>0,'total_words_learned'=>0,'accuracy_total'=>0,'accuracy_correct'=>0, 'attempts'=>0];

// Words learned: Combined Hangman unique words + Total Correct from Academic Suite
$wordsLearned = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_solved_words WHERE user_id = ? AND word_id IS NOT NULL");
$wordsLearned->execute([$uid]);
$hangmanCount = $wordsLearned->fetch()['cnt'] ?? 0;

$acadTotal = $pdo->prepare("SELECT SUM(correct_answers) as total FROM academic_stats WHERE user_id = ?");
$acadTotal->execute([$uid]);
$suiteCount = $acadTotal->fetch()['total'] ?? 0;

$wordsCount = $hangmanCount + $suiteCount;

// Achievements
$achStmt = $pdo->prepare("SELECT a.* FROM user_achievements ua JOIN achievements a ON ua.achievement_id = a.id WHERE ua.user_id = ? ORDER BY ua.unlocked_at DESC");
$achStmt->execute([$uid]);
$achievements = $achStmt->fetchAll();
$totalAch = $pdo->query("SELECT COUNT(*) as cnt FROM achievements")->fetch()['cnt'] ?? 0;

// Today's goal
$today = date('Y-m-d');
$goalStmt = $pdo->prepare("SELECT * FROM daily_goals WHERE user_id = ? AND goal_date = ?");
$goalStmt->execute([$uid, $today]);
$goal = $goalStmt->fetch();
if(!$goal) $goal = ['words_target'=>5,'words_completed'=>0,'completed'=>0,'xp_earned'=>0];





// Accuracy
$accTotal = $stats['accuracy_total'] ?? 0;
$accCorrect = $stats['accuracy_correct'] ?? 0;
$accuracy = $accTotal > 0 ? round(($accCorrect / $accTotal) * 100) : 0;

// XP Level calc
$xp = $stats['xp'] ?? 0;
$xpLevel = floor($xp / 100) + 1;
$xpInLevel = $xp % 100;

// Academic Rank Calc (Based ONLY on Correct Answers)
try { 
    $repair_cols = [
        'academic_correct_count' => 'INT DEFAULT 0',
        'wins' => 'INT DEFAULT 0',
        'losses' => 'INT DEFAULT 0',
        'attempts' => 'INT DEFAULT 0',
        'xp' => 'INT DEFAULT 0',
        'total_score' => 'INT DEFAULT 0'
    ];
    foreach($repair_cols as $c => $t) {
        try { $pdo->exec("ALTER TABLE progress ADD COLUMN $c $t"); } catch(Exception $e){}
    }
} catch(Exception $e){}
$ansStmt = $pdo->prepare("SELECT academic_correct_count FROM progress WHERE user_id = ?");
$ansStmt->execute([$uid]);
$academic_q_count = $ansStmt->fetch()['academic_correct_count'] ?? 0;

$academic_ranks = [
    ["name" => "مبتدئ (Novice)", "min_q" => 0, "color" => "#bdc3c7"],
    ["name" => "متدرب (Apprentice)", "min_q" => 50, "color" => "#2ecc71"],
    ["name" => "طالب علم (Scholar)", "min_q" => 150, "color" => "#3498db"],
    ["name" => "نحوي (Grammarian)", "min_q" => 300, "color" => "#9b59b6"],
    ["name" => "لغوي (Linguist)", "min_q" => 600, "color" => "#e67e22"],
    ["name" => "أستاذ (Master)", "min_q" => 1000, "color" => "#e74c3c"],
    ["name" => "فصيح (Legend)", "min_q" => 1500, "color" => "#f1c40f"]
];
$current_rank = $academic_ranks[0];
$next_rank = $academic_ranks[1] ?? null;
foreach ($academic_ranks as $i => $r) {
    if ($academic_q_count >= $r['min_q']) {
        $current_rank = $r;
        $next_rank = $academic_ranks[$i + 1] ?? null;
    }
}

// Fetch Academic Stats Report
$academic_report = [];
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        mode VARCHAR(50),
        correct_answers INT DEFAULT 0,
        wrong_answers INT DEFAULT 0,
        UNIQUE KEY(user_id, mode)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    $acadStmt = $pdo->prepare("SELECT * FROM academic_stats WHERE user_id = ?");
    $acadStmt->execute([$uid]);
    while ($row = $acadStmt->fetch()) {
        $academic_report[$row['mode']] = $row;
    }
} catch (Exception $e) {}

$mode_labels = [
    'hangman' => 'Hangman Vocabulary',
    'grammar' => 'Fill-in-the-Blanks (Grammar)',
    'sentence_builder' => 'Sentence Builder',
    'error_correction' => 'Error Correction',
    'root_word' => 'Root Word Finder',
    'conjugator' => 'Verb Conjugator',
    'dictation' => 'Audio Dictation',
    'vocab_match' => 'Vocab Match-Up',
    'reading' => 'Reading Comprehension',
    'essay' => 'AI Essay Grader',
    'word_sprint' => 'Word Sprint (Rapid-Fire)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — My Dashboard</title>
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <script>
        window.playArabic = function(text) {
            console.log("Speaking:", text);
            try {
                if (window.responsiveVoice && responsiveVoice.voiceSupport()) {
                    responsiveVoice.cancel();
                    responsiveVoice.speak(text, "Arabic Male", {rate: 0.9});
                } else {
                    const msg = new SpeechSynthesisUtterance(text);
                    msg.lang = 'ar-SA';
                    window.speechSynthesis.speak(msg);
                }
            } catch (e) {
                console.error("Audio error:", e);
                const msg = new SpeechSynthesisUtterance(text);
                msg.lang = 'ar-SA';
                window.speechSynthesis.speak(msg);
            }
        };
    </script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php include 'pwa_install.php'; ?>
    <style>
        :root {
            --bg-start: #0f0c29;
            --bg-mid: #302b63;
            --bg-end: #24243e;
            --accent: #f2994a;
            --accent2: #f2c94c;
            --gold: #FFD700;
            --glass: rgba(255,255,255,0.06);
            --glass-border: rgba(255,255,255,0.1);
            --success: #00b894;
            --danger: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh;
            overflow-x: hidden;
        }



        /* --- MAIN GRID --- */
        .dash-container {
            max-width: 1200px; margin: 0 auto; padding: 90px 20px 40px;
        }
        .dash-header { text-align: center; margin-bottom: 30px; }
        .dash-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .dash-header p { opacity: 0.6; font-size: 0.9rem; }

        /* XP Bar */
        .xp-bar-container {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 20px 25px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 20px;
        }
        .xp-level {
            width: 55px; height: 55px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1.3rem; flex-shrink: 0;
            box-shadow: 0 0 20px rgba(242,153,74,0.4);
        }
        .xp-info { flex: 1; }
        .xp-label { font-size: 0.8rem; opacity: 0.6; margin-bottom: 5px; }
        .xp-track { height: 10px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
        .xp-fill { height: 100%; background: linear-gradient(to right, var(--accent), var(--accent2)); border-radius: 10px; transition: width 1s ease; }
        .xp-text { font-size: 0.75rem; opacity: 0.5; margin-top: 4px; }

        /* Grid Layout */
        .dash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }
        .card:hover { border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }
        .card-title {
            font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px;
            opacity: 0.5; margin-bottom: 15px; font-weight: 600;
        }
        .card.full-width { grid-column: 1 / -1; }

        /* Stats Grid */
        .stats-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .stat-box {
            background: rgba(255,255,255,0.04); border-radius: 15px;
            padding: 15px; text-align: center; border: 1px solid rgba(255,255,255,0.05);
        }
        .stat-value { font-size: 1.8rem; font-weight: 800; line-height: 1; }
        .stat-value.gold { color: var(--gold); }
        .stat-value.green { color: var(--success); }
        .stat-value.orange { color: var(--accent); }
        .stat-label { font-size: 0.7rem; opacity: 0.5; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }

        /* Daily Goal */
        .goal-ring {
            width: 120px; height: 120px; margin: 0 auto 15px;
            position: relative;
        }
        .goal-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .goal-ring .bg { fill: none; stroke: rgba(255,255,255,0.1); stroke-width: 6; }
        .goal-ring .fill { fill: none; stroke: var(--success); stroke-width: 6; stroke-linecap: round; transition: stroke-dashoffset 1s ease; }
        .goal-center {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            text-align: center;
        }
        .goal-count { font-size: 1.5rem; font-weight: 800; }
        .goal-label { font-size: 0.65rem; opacity: 0.5; }
        .goal-status { text-align: center; font-size: 0.85rem; margin-top: 5px; }
        .goal-status.done { color: var(--success); font-weight: 700; }

        /* Streak Calendar */
        .streak-display {
            display: flex; align-items: center; justify-content: center; gap: 15px;
            margin-bottom: 15px;
        }
        .streak-flame { font-size: 3rem; filter: drop-shadow(0 0 10px rgba(255,100,0,0.5)); }
        .streak-num { font-size: 3rem; font-weight: 900; }
        .streak-sub { text-align: center; font-size: 0.8rem; opacity: 0.5; }
        .streak-badges { display: flex; gap: 6px; justify-content: center; margin-top: 15px; flex-wrap: wrap; }
        .streak-day {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 600;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
        }
        .streak-day.active { background: rgba(242,153,74,0.3); border-color: var(--accent); color: var(--accent); }
        .streak-day.today { background: var(--accent); color: white; }

        /* Achievement Badges */
        .achievement-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 10px; min-height: 80px;
        }
        .ach-badge {
            text-align: center; padding: 10px 5px;
            background: rgba(255,255,255,0.04); border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            transition: 0.3s; cursor: default;
        }
        .ach-badge:hover { transform: scale(1.1); border-color: var(--gold); }
        .ach-badge.locked { opacity: 0.3; filter: grayscale(1); }
        .ach-icon { font-size: 1.5rem; margin-bottom: 3px; }
        .ach-name { font-size: 0.55rem; opacity: 0.6; line-height: 1.2; }
        
        /* Leaderboard Preview */
        .rank-row {
            display: flex; align-items: center; gap: 15px;
            padding: 12px; background: rgba(255,255,255,0.03);
            border-radius: 12px; margin-bottom: 8px;
            transition: 0.3s;
        }
        .rank-row:hover { background: rgba(255,255,255,0.06); }
        .rank-num { font-weight: 800; font-size: 1.1rem; width: 25px; color: var(--accent); }
        .rank-name { flex: 1; font-weight: 600; font-size: 0.9rem; }
        .rank-xp { font-weight: 700; color: var(--gold); font-size: 0.85rem; }

        /* Quick Actions */
        .actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .action-btn {
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            padding: 20px 10px; border-radius: 15px;
            text-decoration: none; color: white; font-weight: 600; font-size: 0.85rem;
            border: 1px solid var(--glass-border); background: var(--glass);
            transition: 0.3s; cursor: pointer; position: relative;
        }
        .action-btn:hover { transform: translateY(-3px); border-color: var(--accent); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

        /* --- REVEAL HOVER EFFECT --- */
        .reveal-container { position: relative; height: 130px; cursor: pointer; overflow: hidden; }
        .reveal-front, .reveal-back {
            position: absolute; width: 100%; height: 100%;
            transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            backface-visibility: hidden;
            display: flex; flex-direction: column; justify-content: center;
        }
        .reveal-back { 
            transform: translateY(110%); opacity: 0;
            background: rgba(242,153,74,0.1); border-radius: 15px; padding: 10px;
        }
        .reveal-container:hover .reveal-front, .reveal-container.flipped .reveal-front { transform: translateY(-110%); opacity: 0; }
        .reveal-container:hover .reveal-back, .reveal-container.flipped .reveal-back { transform: translateY(0); opacity: 1; }
        
        .tag { 
            display: inline-block; padding: 2px 8px; border-radius: 10px; 
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            background: rgba(255,255,255,0.1); margin-right: 5px;
        }
        .tag.category { color: #f2994a; border: 1px solid rgba(242,153,74,0.3); }
        .tag.level { color: #2ecc71; border: 1px solid rgba(46,204,113,0.3); }

        /* Activity Feed */
        .activity-feed { max-height: 300px; overflow-y: auto; padding-right: 10px; }
        .activity-feed::-webkit-scrollbar { width: 4px; }
        .activity-feed::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .activity-item {
            padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.85rem; display: flex; align-items: center; gap: 10px;
        }
        .activity-item:last-child { border: none; }
        .activity-time { font-size: 0.7rem; opacity: 0.4; min-width: 60px; }
        .activity-text { flex: 1; }
        .activity-text strong { color: var(--accent); }
        .action-icon { font-size: 1.8rem; }
        .action-badge {
            position: absolute; top: 8px; right: 8px;
            background: var(--danger); color: white; font-size: 0.65rem;
            padding: 2px 7px; border-radius: 10px; font-weight: 700;
        }



        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shine { to { background-position: 200% center; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .dash-container > * { animation: fadeInUp 0.6s ease-out forwards; }

        @media (max-width: 768px) {
            .dash-container { max-width: 500px; margin: 0 auto; padding: 90px 20px 40px; }
            .dash-grid { grid-template-columns: 1fr !important; gap: 15px; }
            .dash-header h1 { font-size: 1.3rem; }
            .dash-header p { font-size: 0.8rem !important; }
            .xp-bar-container { flex-direction: column; text-align: center; gap: 10px; padding: 15px; }
            .xp-level { width: 45px; height: 45px; font-size: 1.1rem; }
            canvas { max-width: 100% !important; height: auto !important; }
        }
        @media (max-width: 480px) {
            .dash-container { padding: 70px 16px 30px; max-width: 100%; }
            .dash-grid { grid-template-columns: 1fr !important; gap: 12px; }
            .card { padding: 14px; border-radius: 14px; text-align: center; }
            .card:hover { transform: none; }
            .card-title { font-size: 0.7rem; letter-spacing: 1.5px; margin-bottom: 10px; }

            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px; }
            .stat-box { padding: 10px 6px; border-radius: 10px; }
            .stat-value { font-size: 1.15rem !important; font-weight: 800; }
            .stat-label { font-size: 0.55rem !important; letter-spacing: 0.5px; margin-top: 3px; }

            .actions-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px; }
            .action-btn { padding: 12px 4px; font-size: 0.7rem; border-radius: 10px; }
            .action-icon { font-size: 1.3rem; }

            .xp-bar-container { padding: 12px; border-radius: 14px; margin-bottom: 12px; }
            .xp-label { font-size: 0.7rem; }
            .xp-text { font-size: 0.65rem; }

            .streak-display { gap: 8px; }
            .streak-flame { font-size: 1.8rem; }
            .streak-num { font-size: 1.8rem; }
            .streak-sub { font-size: 0.7rem; }
            .streak-day { width: 28px; height: 28px; font-size: 0.6rem; }

            .goal-ring { width: 90px; height: 90px; }
            .goal-count { font-size: 1.2rem; }

            .achievement-grid { grid-template-columns: repeat(auto-fill, minmax(55px, 1fr)) !important; gap: 6px; }

            .reveal-container { height: 130px; }
            .reveal-front div:first-child { font-size: 1.6rem !important; }
            .reveal-front div:nth-child(2) { visibility: hidden; position: relative; }
            .reveal-front div:nth-child(2)::before {
                content: "Tap to reveal"; visibility: visible; position: absolute;
                left: 0; width: 100%; text-align: center;
                font-size: 0.75rem; font-weight: 600; color: var(--accent);
            }

            .rank-row { font-size: 0.8rem; padding: 6px 0; }
            .activity-item { padding: 8px 0; font-size: 0.8rem; }

            /* --- OVERRIDE ALL INLINE STYLES --- */
            /* Header inline font-size */
            .dash-header p { font-size: 0.8rem !important; }
            .dash-header div { font-size: 0.7rem !important; }

            /* Hangman Mastery card - inline grid & big emoji */
            .card[style*="grid-column"] { grid-column: auto !important; }
            .card > div[style*="grid-template-columns"] {
                display: flex !important; flex-direction: column !important; gap: 12px !important;
            }
            .card > div[style*="grid-template-columns"] > div[style*="font-size: 3rem"] {
                font-size: 1.8rem !important;
            }
            .card > div[style*="grid-template-columns"] > div[style*="font-size: 1.2rem"] {
                font-size: 0.9rem !important;
            }
            /* Win/Loss flex row */
            div[style*="justify-content: space-between"] {
                flex-direction: column !important; gap: 4px !important; font-size: 0.75rem !important;
            }
            /* Inner stat boxes in Hangman */
            div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr 1fr !important; gap: 8px !important;
            }
            div[style*="font-size: 1.3rem"] { font-size: 1rem !important; }

            /* Academic Performance table */
            table { font-size: 0.7rem !important; }
            table th, table td { padding: 6px 4px !important; font-size: 0.65rem !important; }
            table th:first-child, table td:first-child { max-width: 100px; word-wrap: break-word; overflow-wrap: break-word; }
            div[style*="overflow-x: auto"] { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }
    </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<body>

    <?php require 'includes/navbar.php'; ?>

    <div class="dash-container">

        <!-- HEADER -->
        <div class="dash-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>! 👋</h1>
                <p style="font-size: 1.1rem; margin-top: 5px;">Academic Rank: <strong style="color: <?php echo $current_rank['color']; ?>;"><?php echo $current_rank['name']; ?></strong> (<?php echo $academic_q_count; ?> correct answers)</p>
                <?php if($next_rank): ?>
                    <div style="font-size: 0.8rem; opacity: 0.6; margin-top: 5px;">Get <?php echo ($next_rank['min_q'] - $academic_q_count); ?> more correct answers to reach <?php echo $next_rank['name']; ?>!</div>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button onclick="showJoinModal()" style="background: linear-gradient(135deg, #7c5cbf, #9b7de3); border: none; color: white; padding: 12px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 20px rgba(124,92,191,0.2);">
                    <span>🏫</span> Join a Classroom
                </button>
            </div>
        </div>

        <!-- XP BAR -->
        <div class="xp-bar-container">
            <div class="xp-level"><?php echo $xpLevel; ?></div>
            <div class="xp-info">
                <div class="xp-label">Level <?php echo $xpLevel; ?> — <?php echo $xp; ?> XP total</div>
                <div class="xp-track"><div class="xp-fill" style="width: <?php echo $xpInLevel; ?>%"></div></div>
                <div class="xp-text"><?php echo $xpInLevel; ?>/100 XP to Level <?php echo $xpLevel + 1; ?></div>
            </div>
        </div>

        <?php
        // Fetch Active Student Assignments
        $homeworks = [];
        try {
            $hwStmt = $pdo->prepare("
                SELECT sa.id as track_id, ca.game_mode, ca.level, ca.due_date, u.full_name as teacher_name
                FROM student_assignments sa
                JOIN classroom_assignments ca ON sa.assignment_id = ca.id
                JOIN users u ON ca.teacher_id = u.id
                WHERE sa.student_id = ? AND sa.status = 'assigned' AND ca.due_date >= NOW()
                ORDER BY ca.due_date ASC
            ");
            $hwStmt->execute([$_SESSION['user_id']]);
            $homeworks = $hwStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
        ?>

        <?php if (!empty($homeworks)): ?>
        <div class="card full-width" style="background: linear-gradient(135deg, rgba(235, 77, 75, 0.12), rgba(48, 43, 99, 0.4)); border-color: #eb4d4b; margin-bottom: 25px; box-shadow: 0 0 20px rgba(235, 77, 75, 0.15); width: 100%;">
            <div class="card-title" style="color: #eb4d4b; font-weight: 800; letter-spacing: 2px;">📝 Active Homework Tasks</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 10px;">
                <?php foreach ($homeworks as $hw): 
                    $gameUrl = $hw['game_mode'] . ".php";
                    if ($hw['game_mode'] == 'vocab') $gameUrl = 'vocab_match.php';
                    if ($hw['game_mode'] == 'speaking') $gameUrl = 'speaking_practice.php';
                    
                    $modeLabel = ucwords(str_replace('_', ' ', $hw['game_mode']));
                    $dueTime = strtotime($hw['due_date']);
                    $timeLeft = $dueTime - time();
                    $hoursLeft = round($timeLeft / 3600);
                    $timeString = $hoursLeft > 24 ? round($hoursLeft / 24) . " days left" : $hoursLeft . " hours left";
                ?>
                    <div style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06); padding: 20px; border-radius: 15px; display: flex; flex-direction: column; gap: 12px; transition: 0.3s;" onmouseover="this.style.borderColor='rgba(235,77,75,0.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700; color: #eb4d4b; font-size: 1.1rem;"><?php echo $modeLabel; ?></span>
                            <span style="background: rgba(235, 77, 75, 0.2); color: #eb4d4b; font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($hw['level']); ?></span>
                        </div>
                        <div style="font-size: 0.85rem; opacity: 0.8;">Assigned by: <strong><?php echo htmlspecialchars($hw['teacher_name']); ?></strong></div>
                        <div style="font-size: 0.85rem; color: #f1c40f;">⏳ <?php echo $timeString; ?> (Due: <?php echo date('M d, h:i A', $dueTime); ?>)</div>
                        <a href="<?php echo $gameUrl; ?>" style="background: #eb4d4b; color: white; text-align: center; padding: 10px; border-radius: 8px; font-weight: bold; text-decoration: none; margin-top: auto; font-size: 0.85rem; transition: 0.3s; box-shadow: 0 4px 10px rgba(235,77,75,0.2);" onmouseover="this.style.background='#ff7675'" onmouseout="this.style.background='#eb4d4b'">Complete Homework ➔</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="dash-grid">

            <!-- STATS -->
            <div class="card">
                <div class="card-title">📈 Your Stats</div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-value gold"><?php echo number_format($stats['total_score'] ?? 0); ?></div>
                        <div class="stat-label">Total Score</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value green"><?php echo $wordsCount; ?></div>
                        <div class="stat-label">Words Learned</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value orange"><?php echo ($stats['wins'] ?? 0) + ($stats['mcq_wins'] ?? 0); ?></div>
                        <div class="stat-label">Total Wins</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" style="color: #a29bfe;"><?php echo $accuracy; ?>%</div>
                        <div class="stat-label">Accuracy</div>
                    </div>
                </div>
            </div>
            
            <!-- DAILY CHALLENGE -->
            <div class="card">
                <div class="card-title">🏅 Daily Challenge</div>
                <div id="dailyChallenge" class="card-content">Loading...</div>
            </div>

            <!-- WORD OF THE DAY -->
            <div class="card">
                <div class="card-title">💬 Word of the Day</div>
                <div id="wordOfTheDay" class="card-content">Loading...</div>
            </div>

            <!-- ACADEMIC REPORT -->
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-title">🎮 Hangman Mastery</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🎯</div>
                        <div style="font-size: 1.2rem; font-weight: 800; color: var(--gold);"><?= round(($stats['wins'] / max(1, ($stats['wins'] + $stats['losses']))) * 100) ?>% Win Rate</div>
                        <div style="font-size: 0.8rem; opacity: 0.5;">Overall Performance</div>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem; font-weight: 600;">
                            <span>Words Mastered: <strong style="color: var(--success);">✅ <?= $stats['wins'] ?></strong></span>
                            <span>Unsolved Mistakes: <strong style="color: var(--danger);">❌ <?= $stats['losses'] ?></strong></span>
                        </div>
                        <div style="height: 14px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; display: flex; border: 1px solid rgba(255,255,255,0.05);">
                            <?php 
                            $winPct = ($stats['wins'] + $stats['losses']) > 0 ? ($stats['wins'] / ($stats['wins'] + $stats['losses'])) * 100 : 0;
                            ?>
                            <div style="width: <?= $winPct ?>%; background: linear-gradient(to right, #00b894, #55efc4); height: 100%;"></div>
                            <div style="width: <?= 100 - $winPct ?>%; background: linear-gradient(to right, #d63031, #e17055); height: 100%;"></div>
                        </div>
                        <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; text-align: center; border: 1px solid var(--glass-border);">
                                <div style="font-size: 1.3rem; font-weight: 800; color: white;"><?= $stats['attempts'] ?></div>
                                <div style="font-size: 0.65rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px;">Total Questions Attempted</div>
                            </div>
                            <div style="background: rgba(242,153,74,0.1); padding: 12px; border-radius: 12px; text-align: center; border: 1px solid rgba(242,153,74,0.2);">
                                <div style="font-size: 1.3rem; font-weight: 800; color: var(--accent);"><?= $stats['current_streak'] ?></div>
                                <div style="font-size: 0.65rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px;">Current Winning Streak</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACADEMIC REPORT -->
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-title">🎓 Academic Performance Report</div>
                <div style="background: var(--glass); border-radius: 12px; padding: 15px; border: 1px solid var(--glass-border); overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border); opacity: 0.7; font-size: 0.85rem;">
                                <th style="padding: 10px;">Module</th>
                                <th style="padding: 10px; text-align: center;">Correct (✅)</th>
                                <th style="padding: 10px; text-align: center;">Wrong (❌)</th>
                                <th style="padding: 10px; text-align: center;">Accuracy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($mode_labels as $mode_key => $mode_name): 
                                $r = $academic_report[$mode_key] ?? ['correct_answers'=>0, 'wrong_answers'=>0];
                                $total = $r['correct_answers'] + $r['wrong_answers'];
                                $acc = $total > 0 ? round(($r['correct_answers']/$total)*100) : 0;
                            ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 15px 10px; font-weight: 600;"><?= $mode_name ?></td>
                                <td style="padding: 15px 10px; text-align: center; color: var(--success); font-weight: 700;"><?= $r['correct_answers'] ?></td>
                                <td style="padding: 15px 10px; text-align: center; color: var(--danger); font-weight: 700;"><?= $r['wrong_answers'] ?></td>
                                <td style="padding: 15px 10px; text-align: center;">
                                    <div style="background: rgba(255,255,255,0.1); border-radius: 10px; height: 6px; width: 100%; margin-top: 5px; position: relative;">
                                        <div style="background: <?= $acc >= 75 ? 'var(--success)' : ($acc >= 50 ? 'var(--accent)' : 'var(--danger)') ?>; width: <?= $acc ?>%; height: 100%; border-radius: 10px;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; opacity: 0.7;"><?= $acc ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DAILY GOAL -->
            <div class="card">
                <div class="card-title">🎯 Today's Goal</div>
                <?php 
                    $goalDone = $goal['words_completed'];
                    $goalTarget = $goal['words_target'];
                    $goalPct = min(100, round(($goalDone / max(1, $goalTarget)) * 100));
                    $circumference = 2 * 3.14159 * 45;
                    $offset = $circumference - ($circumference * $goalPct / 100);
                ?>
                <div class="goal-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="bg" cx="50" cy="50" r="45"/>
                        <circle class="fill" cx="50" cy="50" r="45" 
                                stroke-dasharray="<?php echo $circumference; ?>" 
                                stroke-dashoffset="<?php echo $offset; ?>"/>
                    </svg>
                    <div class="goal-center">
                        <div class="goal-count"><?php echo $goalDone; ?>/<?php echo $goalTarget; ?></div>
                        <div class="goal-label">words</div>
                    </div>
                </div>
                <div class="goal-status <?php echo $goal['completed'] ? 'done' : ''; ?>">
                    <?php echo $goal['completed'] ? '✅ Daily Goal Complete! +25 XP' : "Complete $goalTarget words today"; ?>
                </div>
            </div>

            <!-- STREAK -->
            <div class="card">
                <div class="card-title">🔥 Daily Streak</div>
                <div class="streak-display">
                    <div class="streak-flame">🔥</div>
                    <div class="streak-num"><?php echo $stats['daily_streak'] ?? 0; ?></div>
                </div>
                <div class="streak-sub">
                    <?php echo ($stats['daily_streak'] ?? 0) > 0 ? "days in a row! Keep it up!" : "Play today to start your streak!"; ?>
                </div>
                <div style="text-align:center; margin-top: 10px; font-size: 0.75rem; opacity: 0.4;">
                    Best: <?php echo $stats['longest_streak'] ?? 0; ?> days
                </div>


                <div class="streak-badges">
                    <?php 
                    $days = ['M','T','W','T','F','S','S'];
                    $todayIdx = (date('N') - 1); // 0=Mon
                    $ds = $stats['daily_streak'] ?? 0;
                    for($i = 0; $i < 7; $i++):
                        $isToday = ($i === $todayIdx);
                        $isActive = ($i <= $todayIdx && ($todayIdx - $i) < $ds);
                    ?>
                    <div class="streak-day <?php echo $isToday ? 'today' : ($isActive ? 'active' : ''); ?>">
                        <?php echo $days[$i]; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- PROGRESS CHART CARD -->
            <div class="card">
                <div class="card-title">📊 Weekly Progress</div>
                <canvas id="progressChart" width="400" height="200"></canvas>
            </div>



            <!-- QUICK ACTIONS -->
            <div class="card">
                <div class="card-title">🚀 Quick Actions</div>
                <div class="actions-grid">
                    <a href="level_select.php" class="action-btn">
                        <div class="action-icon">🎯</div>
                        Hangman
                    </a>
                    <a href="academic_hub.php" class="action-btn">
                        <div class="action-icon" style="background: rgba(155, 89, 182, 0.2); color: #9b59b6;">🏛️</div>
                        <span>Academic</span>
                    </a>
                    <a href="vocabulary_bank.php" class="action-btn">
                        <div class="action-icon">🧠</div>
                        SRS Review
                    </a>
                    <a href="writing_canvas.php" class="action-btn">
                        <div class="action-icon">✍️</div>
                        Writing
                    </a>
                    <a href="leaderboard.php" class="action-btn">
                        <div class="action-icon">🏆</div>
                        Rankings
                    </a>
                    <a href="majlis.php" class="action-btn" style="background: rgba(245, 166, 35, 0.1); border-color: rgba(245, 166, 35, 0.2); color: var(--accent);">
                        <div class="action-icon">🤖</div>
                        The Majlis
                    </a>

                </div>
            </div>

            <!-- ACHIEVEMENTS -->
            <div class="card">
                <div class="card-title">🏅 Achievements</div>
                <div class="achievement-grid">
                    <p style="opacity:0.5; font-size:0.8rem;">Checking earned badges...</p>
                </div>
            </div>

            <!-- TOP RANKINGS -->
            <div class="card">
                <div class="card-title">🏆 Top Rankings</div>
                <div id="leaderboard-preview">
                    <p style="opacity:0.5; font-size:0.8rem;">Loading leaderboard...</p>
                </div>
            </div>

            <!-- COMMUNITY FEED -->
            <div class="card">
                <div class="card-title">📢 Community Feed</div>
                <div class="activity-feed" id="activity-feed">
                    <p style="opacity:0.5; font-size:0.8rem;">Listening to academy updates...</p>
                </div>
            </div>

        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load progress chart
    fetch('api/get_progress.php?range=weekly')
        .then(r => {
            if (!r.ok) throw new Error('HTTP error! status: ' + r.status);
            return r.json();
        })
        .then(data => {
            const ctx = document.getElementById('progressChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'XP Gained',
                        data: data.xp,
                        borderColor: 'var(--accent)',
                        backgroundColor: 'rgba(242,153,74,0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        })
        .catch(e => {
            console.error('Progress fetch error', e);
            document.getElementById('progressChart').parentElement.innerHTML += `<p style="color:red; font-size:0.7rem; margin-top:10px;">Error: ${e.message}</p>`;
        });



    // Load daily challenge
    fetch('api/get_daily_challenge.php')
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('dailyChallenge');
            if (data.error) { el.innerText = data.error; return; }
            el.innerHTML = `
                <div class="reveal-container">
                    <div class="reveal-front">
                        <div style="font-size:2.8rem; font-family:'Amiri'; text-align:center;">${data.arabic_word}</div>
                        <div style="text-align:center; opacity:0.5; font-size:0.8rem; margin-top:5px;">Hover to reveal</div>
                    </div>
                    <div class="reveal-back">
                        <div style="font-weight:800; color:var(--accent); font-size:1.1rem;">${data.meaning}</div>
                        <div style="margin: 8px 0;">
                            <span class="tag category">${data.category || 'General'}</span>
                            <span class="tag level">${data.level || 'Beginner'}</span>
                        </div>
                        <div style="font-size:0.75rem; opacity:0.8; line-height:1.4;">
                            Example: The word <strong>${data.arabic_word}</strong> is a key term in ${data.category} vocabulary.
                        </div>
                    </div>
                </div>`;
        });

    // Load word of the day
    fetch('api/get_word_of_the_day.php')
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('wordOfTheDay');
            if (data.error) { el.innerText = data.error; return; }
            el.innerHTML = `
                <div class="reveal-container">
                    <div class="reveal-front">
                        <div style="font-size:2.8rem; font-family:'Amiri'; text-align:center;">${data.arabic_word}</div>
                        <div style="text-align:center; opacity:0.5; font-size:0.8rem; margin-top:5px;">Hover to reveal</div>
                    </div>
                    <div class="reveal-back">
                        <div style="font-weight:800; color:var(--accent); font-size:1.1rem;">${data.meaning}</div>
                        <div style="margin: 8px 0;">
                            <span class="tag category">${data.category || 'General'}</span>
                            <span class="tag level">${data.level || 'Beginner'}</span>
                        </div>
                        <div style="font-size:0.75rem; opacity:0.8; line-height:1.4;">
                            Note: This is an important <strong>${data.level}</strong> level word used in daily conversation.
                        </div>
                    </div>
                </div>`;
        });

    // Load achievements
    fetch('api/get_achievements.php')
        .then(r => r.json())
        .then(data => {
            const grid = document.querySelector('.achievement-grid');
            if (!grid) return;
            
            if (data.error) {
                grid.innerHTML = '<p style="opacity:0.5; font-size:0.8rem;">Ready to earn badges!</p>';
                return;
            }

            let html = '';
            if (data.earned && data.earned.length > 0) {
                data.earned.forEach(a => {
                    html += `<div class="ach-badge" title="${a.description}">
                                <div class="ach-icon">${a.icon}</div>
                                <div class="ach-name">${a.title}</div>
                             </div>`;
                });
            }
            
            if (data.locked && data.locked.length > 0) {
                // Show up to 3 locked ones as goals
                data.locked.slice(0, 3).forEach(a => {
                    html += `<div class="ach-badge locked" title="Locked: ${a.description}">
                                <div class="ach-icon">${a.icon}</div>
                                <div class="ach-name">${a.title}</div>
                             </div>`;
                });
            }

            grid.innerHTML = html || '<p style="opacity:0.5; font-size:0.8rem;">Play games to earn badges!</p>';
        })
        .catch(err => {
            const grid = document.querySelector('.achievement-grid');
            if (grid) grid.innerHTML = '<p style="opacity:0.5; font-size:0.8rem;">Start your journey!</p>';
        });

    // Load leaderboard preview
    fetch('api/get_leaderboard_preview.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('leaderboard-preview');
            if (!container || data.error) return;
            let html = '';
            data.forEach((user, index) => {
                const medals = ['🥇', '🥈', '🥉'];
                html += `
                    <div class="rank-row">
                        <div class="rank-num">${medals[index] || (index + 1)}</div>
                        <div class="rank-name">${user.username}</div>
                        <div class="rank-xp">${user.xp} XP</div>
                    </div>`;
            });
            container.innerHTML = html || '<p style="opacity:0.5; font-size:0.8rem;">No rankings yet.</p>';
        });

    // Load activity feed
    fetch('api/get_activity_feed.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('activity-feed');
            if (!container || data.error) return;
            let html = '';
            data.forEach(act => {
                html += `
                    <div class="activity-item">
                        <div class="activity-text"><strong>${act.username}</strong> ${act.description}</div>
                        <div class="activity-time">${new Date(act.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    </div>`;
            });
            container.innerHTML = html || '<p style="opacity:0.5; font-size:0.8rem;">No recent activity.</p>';
        });

    // Handle Tap-to-Reveal on mobile devices
    document.addEventListener('click', function(e) {
        const card = e.target.closest('.reveal-container');
        if (card) {
            card.classList.toggle('flipped');
        } else {
            // If they tap outside, close any open cards
            document.querySelectorAll('.reveal-container.flipped').forEach(c => c.classList.remove('flipped'));
        }
    });

});
</script>
    <!-- Join Class Modal -->
    <div id="joinModal" class="modal" style="display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.8); z-index: 10000; position: fixed; top: 0; left: 0; width: 100%; height: 100%; backdrop-filter: blur(10px);">
        <div class="modal-content" style="background: #161430; padding: 40px; border-radius: 24px; text-align: center; max-width: 400px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
            <div style="font-size: 3rem; margin-bottom: 20px;">🏫</div>
            <h2 style="font-family: 'Syne', sans-serif; color: white; margin-bottom: 10px;">Join Classroom</h2>
            <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 25px;">Enter the 6-digit code provided by your teacher to join their class.</p>
            
            <input type="text" id="classCodeInput" maxlength="6" placeholder="A1B2C3" style="text-align: center; font-size: 1.5rem; letter-spacing: 5px; text-transform: uppercase; padding: 15px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 100%; color: #f5a623; font-family: 'Syne', sans-serif; margin-bottom: 20px; outline: none;">
            
            <button onclick="submitJoinClass()" id="joinBtn" style="width: 100%; padding: 15px; border-radius: 12px; border: none; background: linear-gradient(to right, #f5a623, #f2c94c); color: #1a0f00; font-weight: 800; cursor: pointer; transition: 0.3s;">Join Now →</button>
            <button onclick="closeJoinModal()" style="background: none; border: none; color: rgba(255,255,255,0.4); margin-top: 15px; cursor: pointer; font-size: 0.85rem;">Cancel</button>
        </div>
    </div>

    <script>
        function showJoinModal() { document.getElementById('joinModal').style.display = 'flex'; }
        function closeJoinModal() { document.getElementById('joinModal').style.display = 'none'; }

        function submitJoinClass() {
            const code = document.getElementById('classCodeInput').value;
            const btn = document.getElementById('joinBtn');
            if (code.length < 6) return alert("Please enter a valid 6-digit code.");

            btn.disabled = true;
            btn.innerHTML = "Joining...";

            const formData = new FormData();
            formData.append('class_code', code);

            fetch('api/join_class.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("✅ " + data.message);
                    location.reload();
                } else {
                    alert("❌ " + data.message);
                    btn.disabled = false;
                    btn.innerHTML = "Join Now →";
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error connecting to server.");
                btn.disabled = false;
                btn.innerHTML = "Join Now →";
            });
        }
    </script>
</body>
</html>
