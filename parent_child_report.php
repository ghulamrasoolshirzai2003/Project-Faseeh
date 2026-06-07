<?php
session_start();
require 'includes/db.php';

// Authorize via direct access session
if (!isset($_SESSION['parent_auth']) || $_SESSION['parent_auth'] !== true) {
    header('Location: parent_login.php');
    exit;
}

$parent_name = $_SESSION['parent_name'];
$parent_dob = $_SESSION['parent_dob'];
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header('Location: parent_dashboard.php');
    exit;
}

// 1. Verify this student lists this guardian
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND guardian_name = ? AND guardian_dob = ?");
$stmt->execute([$student_id, $parent_name, $parent_dob]);
if (!$stmt->fetch()) {
    die("Unauthorized access to child data.");
}

// 2. Fetch Student Info & Progress
$stmt = $pdo->prepare("SELECT u.full_name, u.username, u.avatar, u.selected_level, p.* 
                       FROM users u 
                       LEFT JOIN progress p ON u.id = p.user_id 
                       WHERE u.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// 3. Fetch Achievements
$stmt = $pdo->prepare("SELECT a.title, a.icon FROM user_achievements ua JOIN achievements a ON ua.achievement_id = a.id WHERE ua.user_id = ?");
$stmt->execute([$student_id]);
$achievements = $stmt->fetchAll();

// 4. Calculate Level
$xp = $student['xp'] ?? 0;
$level = floor($xp / 100) + 1;
$next_level_xp = $level * 100;
$prog_percent = ($xp % 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Progress — <?= htmlspecialchars($student['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0e0c1e; --bg-card: #161430; --accent: #f5a623;
            --purple: #7c5cbf; --success: #3ecf8e; --gold: #ffd700;
            --text: #f0eeff; --muted: #8b87b0; --border: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; padding: 40px; }
        
        .container { max-width: 900px; margin: 0 auto; }
        .back-link { color: var(--muted); text-decoration: none; display: flex; align-items: center; gap: 8px; margin-bottom: 30px; font-weight: 500; }
        
        .header { display: flex; align-items: center; gap: 30px; background: var(--bg-card); border: 1px solid var(--border); padding: 40px; border-radius: 30px; margin-bottom: 30px; position: relative; overflow: hidden; }
        .header::before { content: '🏆'; position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.05; transform: rotate(-15deg); }
        
        .avatar { width: 100px; height: 100px; background: linear-gradient(135deg, var(--purple), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; border: 3px solid rgba(255,255,255,0.1); }
        .info h1 { font-family: 'Syne', sans-serif; font-size: 2.2rem; }
        .level-badge { display: inline-block; padding: 6px 16px; background: var(--purple); border-radius: 50px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin-top: 10px; box-shadow: 0 5px 15px rgba(124,92,191,0.3); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border); padding: 25px; border-radius: 24px; text-align: center; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--accent); }
        .stat-val { font-size: 2rem; font-weight: 800; font-family: 'Syne', sans-serif; color: var(--accent); }
        .stat-lab { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; margin-top: 5px; letter-spacing: 1px; }

        .report-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 30px; padding: 40px; margin-bottom: 30px; }
        .section-title { font-family: 'Syne', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 1.4rem; }

        .progress-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-weight: 600; }
        .progress-bar-outer { height: 16px; background: rgba(0,0,0,0.3); border-radius: 20px; overflow: hidden; border: 1px solid var(--border); }
        .progress-bar-inner { height: 100%; background: linear-gradient(to right, #3ecf8e, #10b981); border-radius: 20px; }

        .achievement-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 20px; }
        .achievement-card { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 20px; border: 1px solid var(--border); text-align: center; }
        .ach-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .ach-title { font-size: 0.75rem; font-weight: 700; color: var(--text); }
        
        .quote-box { background: rgba(245, 166, 35, 0.05); border: 1px solid var(--accent); padding: 20px; border-radius: 20px; color: var(--accent); font-style: italic; margin-top: 40px; text-align: center; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="parent_dashboard.php" class="back-link">← Back to Child List</a>

        <div class="header">
            <div class="avatar"><?= mb_substr($student['full_name'], 0, 1) ?></div>
            <div class="info">
                <h1><?= htmlspecialchars($student['full_name']) ?></h1>
                <p style="opacity: 0.6;">Learning Path: <?= ucfirst($student['selected_level']) ?></p>
                <div class="level-badge">Academy Level <?= $level ?></div>
            </div>
        </div>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?= number_format($student['xp'] + $student['academic_xp']) ?></div>
                <div class="stat-lab">Total Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-val">🔥 <?= $student['daily_streak'] ?></div>
                <div class="stat-lab">Learning Streak</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= count($achievements) ?></div>
                <div class="stat-lab">Badges Earned</div>
            </div>
        </section>

        <div style="text-align: center; margin-bottom: 30px;">
            <a href="b2b_dashboard.php?student_id=<?= $student_id ?>" style="display: inline-block; background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000; padding: 12px 30px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.95rem; box-shadow: 0 5px 15px rgba(243,156,18,0.2); transition: 0.3s;">
                📊 View Advanced Analytical Charts
            </a>
        </div>

        <div class="report-section">
            <h3 class="section-title"><span>📈</span> Progression Path</h3>
            <div class="progress-row">
                <span>Next Level Progress</span>
                <span style="color: var(--success);"><?= $prog_percent ?>%</span>
            </div>
            <div class="progress-bar-outer">
                <div class="progress-bar-inner" style="width: <?= $prog_percent ?>%;"></div>
            </div>
            <p style="margin-top: 15px; font-size: 0.85rem; color: var(--muted); text-align: center;">
                <?= htmlspecialchars($student['full_name']) ?> needs <?= ($next_level_xp - $xp) ?> more XP to reach Level <?= ($level + 1) ?>!
            </p>
        </div>

        <div class="report-section">
            <h3 class="section-title"><span>🏆</span> Achievement Badges</h3>
            <div class="achievement-grid">
                <?php if (empty($achievements)): ?>
                    <p style="color: var(--muted); font-style: italic; grid-column: 1/-1; text-align: center;">No badges unlocked yet. Keep encouraging them!</p>
                <?php else: ?>
                    <?php foreach ($achievements as $ach): ?>
                        <div class="achievement-card">
                            <div class="ach-icon"><?= $ach['icon'] ?></div>
                            <div class="ach-title"><?= $ach['title'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="quote-box">
            "Education is not the filling of a pail, but the lighting of a fire." – Keep supporting <?= htmlspecialchars($student['full_name']) ?> on their Arabic journey!
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <p style="font-size: 0.8rem; color: var(--muted);">Guardian: <?= htmlspecialchars($parent_name) ?></p>
        </div>
    </div>
</body>
</html>
