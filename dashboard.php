<?php
session_start();
require 'includes/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php"); exit;
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

// Words learned
$wordsLearned = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_progress WHERE user_id = ?");
$wordsLearned->execute([$uid]);
$wordsCount = $wordsLearned->fetch()['cnt'] ?? 0;

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

// Reviews due
$revStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM review_queue WHERE user_id = ? AND next_review <= ?");
$revStmt->execute([$uid, $today]);
$reviewsDue = $revStmt->fetch()['cnt'] ?? 0;

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
    'essay' => 'AI Essay Grader'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — My Dashboard</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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

        /* --- NAVBAR --- */
        .navbar {
            width: 100%; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 100;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .mini-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4);
        }
        .mini-icon::after {
            content: ''; position: absolute; width: 34px; height: 34px;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent;
            border-radius: 50%; animation: spin 8s linear infinite;
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 20px; color: white; margin-top: -3px; z-index: 2; }
        .mini-text {
            font-size: 1.4rem; font-weight: 800; margin: 0;
            background: linear-gradient(to right, #fff 20%, var(--gold) 50%, #fff 80%);
            background-size: 200% auto; color: transparent;
            -webkit-background-clip: text; background-clip: text;
            animation: shine 3s linear infinite;
        }
        .nav-links { display: flex; gap: 10px; align-items: center; }
        .nav-link {
            color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.85rem;
            padding: 8px 18px; border-radius: 25px; transition: 0.3s; font-weight: 500;
            border: 1px solid transparent;
        }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); border-color: var(--glass-border); }
        .nav-link.active { background: rgba(242,153,74,0.2); color: var(--accent); border-color: rgba(242,153,74,0.3); }
        .nav-link.logout { border-color: rgba(255,255,255,0.15); }
        .nav-link.logout:hover { background: white; color: #1e3c72; }

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
            gap: 10px;
        }
        .ach-badge {
            text-align: center; padding: 10px 5px;
            background: rgba(255,255,255,0.04); border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            transition: 0.3s; cursor: default;
        }
        .ach-badge:hover { transform: scale(1.1); border-color: var(--gold); }
        .ach-icon { font-size: 1.5rem; margin-bottom: 3px; }
        .ach-name { font-size: 0.55rem; opacity: 0.6; line-height: 1.2; }
        .ach-counter {
            text-align: center; font-size: 0.8rem; opacity: 0.5; margin-top: 10px;
        }

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
        .action-icon { font-size: 1.8rem; }
        .action-badge {
            position: absolute; top: 8px; right: 8px;
            background: var(--danger); color: white; font-size: 0.65rem;
            padding: 2px 7px; border-radius: 10px; font-weight: 700;
        }

        /* Review reminder */
        .review-banner {
            background: linear-gradient(135deg, rgba(0,184,148,0.2), rgba(0,184,148,0.05));
            border: 1px solid rgba(0,184,148,0.3); border-radius: 15px;
            padding: 15px 20px; display: flex; align-items: center; gap: 15px;
            margin-bottom: 20px; cursor: pointer; transition: 0.3s;
        }
        .review-banner:hover { transform: translateY(-2px); border-color: var(--success); }
        .review-banner .icon { font-size: 1.5rem; }
        .review-banner .text { flex: 1; }
        .review-banner .label { font-weight: 700; font-size: 0.9rem; }
        .review-banner .sub { font-size: 0.75rem; opacity: 0.6; }
        .review-banner .count { font-size: 1.5rem; font-weight: 800; color: var(--success); }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shine { to { background-position: 200% center; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .dash-container > * { animation: fadeInUp 0.6s ease-out forwards; }

        /* Mobile */
        @media (max-width: 768px) {
            .dash-grid { grid-template-columns: 1fr; }
            .nav-links { gap: 5px; }
            .nav-link { font-size: 0.75rem; padding: 6px 12px; }
            .dash-header h1 { font-size: 1.5rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
            .xp-bar-container { flex-direction: column; text-align: center; }
        }
        @media (max-width: 480px) {
            .navbar { padding: 10px 15px; }
            .mini-text { font-size: 1.1rem; }
            .dash-container { padding: 80px 12px 30px; }
            .nav-link span.hide-mobile { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="level_select.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </a>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link active">📊 <span class="hide-mobile">Dashboard</span></a>
            <a href="academy.php" class="nav-link">🎓 <span class="hide-mobile">Academy</span></a>
            <a href="level_select.php" class="nav-link">🎮 <span class="hide-mobile">Play</span></a>
            <a href="leaderboard.php" class="nav-link">🏆 <span class="hide-mobile">Rankings</span></a>
            <a href="profile.php" class="nav-link" style="border-color: rgba(255,255,255,0.15);">👤 <span class="hide-mobile">Profile</span></a>
        </div>
    </nav>

    <div class="dash-container">

        <!-- HEADER -->
        <div class="dash-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>! 👋</h1>
            <p style="font-size: 1.1rem; margin-top: 5px;">Academic Rank: <strong style="color: <?php echo $current_rank['color']; ?>;"><?php echo $current_rank['name']; ?></strong> (<?php echo $academic_q_count; ?> correct answers)</p>
            <?php if($next_rank): ?>
                <div style="font-size: 0.8rem; opacity: 0.6; margin-top: 5px;">Get <?php echo ($next_rank['min_q'] - $academic_q_count); ?> more correct answers to reach <?php echo $next_rank['name']; ?>!</div>
            <?php endif; ?>
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

        <!-- REVIEW BANNER -->
        <?php if ($reviewsDue > 0): ?>
        <a href="review.php" style="text-decoration:none;">
            <div class="review-banner">
                <div class="icon">🧠</div>
                <div class="text">
                    <div class="label">Words to Review</div>
                    <div class="sub">Spaced repetition keeps your memory sharp</div>
                </div>
                <div class="count"><?php echo $reviewsDue; ?></div>
            </div>
        </a>
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

            <!-- QUICK ACADEMY ACTION -->
            <div class="card" style="background: linear-gradient(135deg, rgba(242,153,74,0.15), transparent); border-color: var(--accent);">
                <div class="card-title">🚀 Fast Track Learning</div>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 3.5rem;">🎓</div>
                    <div style="flex: 1;">
                        <h3 style="margin-bottom: 5px;">Faseeh Academy</h3>
                        <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 15px;">Master Reading, Writing, Speaking & Listening with AI Tutors.</p>
                        <a href="academy.php" class="action-btn" style="background: var(--accent); color: #333; padding: 10px; width: 100%; border: none;">Enter Academy</a>
                    </div>
                </div>
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

            <!-- QUICK ACTIONS -->
            <div class="card">
                <div class="card-title">🚀 Quick Actions</div>
                <div class="actions-grid">
                    <a href="level_select.php" class="action-btn">
                        <div class="action-icon">🎯</div>
                        Hangman
                    </a>
                    <a href="mcq.php" class="action-btn">
                        <div class="action-icon">🧩</div>
                        Quiz Mode
                    </a>
                    <a href="review.php" class="action-btn">
                        <div class="action-icon">🧠</div>
                        Review
                        <?php if($reviewsDue > 0): ?><span class="action-badge"><?php echo $reviewsDue; ?></span><?php endif; ?>
                    </a>
                    <a href="leaderboard.php" class="action-btn">
                        <div class="action-icon">🏆</div>
                        Rankings
                    </a>
                </div>
            </div>

            <!-- ACHIEVEMENTS -->
            <div class="card full-width">
                <div class="card-title">🏅 Achievements</div>
                <?php if (count($achievements) > 0): ?>
                <div class="achievement-grid">
                    <?php foreach($achievements as $ach): ?>
                    <div class="ach-badge" title="<?php echo htmlspecialchars($ach['description']); ?>">
                        <div class="ach-icon"><?php echo $ach['icon']; ?></div>
                        <div class="ach-name"><?php echo htmlspecialchars($ach['title']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align:center; opacity:0.4; font-size:0.85rem;">Play games to unlock achievements! 🎮</p>
                <?php endif; ?>
                <div class="ach-counter"><?php echo count($achievements); ?> / <?php echo $totalAch; ?> unlocked</div>
            </div>

        </div>
    </div>

</body>
</html>
