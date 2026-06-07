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

// AUTOMATICALLY find children based on matching Guardian info provided by students
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.username, u.created_at, p.* 
    FROM users u
    LEFT JOIN progress p ON u.id = p.user_id
    WHERE u.role = 'student' 
    AND u.guardian_name = ? 
    AND u.guardian_dob = ?
");
$stmt->execute([$parent_name, $parent_dob]);
$children = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal — Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0e0c1e; --bg-card: #161430; --border: rgba(255,255,255,0.07);
            --accent: #f5a623; --accent2: #7c5cbf; --success: #3ecf8e; --danger: #e74c3c;
            --text: #f0eeff; --muted: #8b87b0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .header { text-align: center; margin-bottom: 50px; }
        h1 { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 2.5rem; margin-bottom: 10px; }
        
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; padding: 30px; margin-bottom: 30px; }
        
        .child-card { border: 1px solid var(--border); border-radius: 20px; padding: 25px; background: rgba(255,255,255,0.02); margin-bottom: 30px; }
        .child-header { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .child-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--accent2), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; }
        
        .stats-row { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 0px; }
        .stat-item { background: rgba(255,255,255,0.05); padding: 10px 20px; border-radius: 12px; text-align: center; flex: 1; min-width: 100px; }
        .stat-val { display: block; font-size: 1.2rem; font-weight: 700; color: var(--accent); }
        .stat-label { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; }

        .no-children { text-align: center; padding: 60px 20px; }
        .no-children-icon { font-size: 4rem; margin-bottom: 20px; display: block; }
        
        .logout-link { display: inline-block; margin-top: 20px; color: var(--muted); text-decoration: none; font-size: 0.85rem; }
        .logout-link:hover { color: var(--danger); }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: var(--accent); margin-bottom: 10px;">Guardian: <?= htmlspecialchars($parent_name) ?></div>
            <h1>Parent Portal</h1>
            <p style="color: var(--muted);">Real-time monitoring of your children's Arabic progress.</p>
        </div>

        <?php if (empty($children)): ?>
            <div class="card no-children">
                <span class="no-children-icon">🔍</span>
                <h3>No children found</h3>
                <p style="color: var(--muted); margin: 15px 0 25px; font-size: 0.9rem; max-width: 440px; margin-left: auto; margin-right: auto;">
                    We couldn't find any students who listed **<?= htmlspecialchars($parent_name) ?>** (Born: <?= date('M j, Y', strtotime($parent_dob)) ?>) as their guardian.
                </p>
                <p style="font-size: 0.8rem; opacity: 0.5;">Please ensure your child enters these exact details during registration.</p>
                <a href="parent_login.php" class="logout-link">Try again with different details</a>
            </div>
        <?php else: ?>
            <?php foreach ($children as $c): 
                $xp = $c['xp'] ?? 0;
                $level = floor($xp / 100) + 1;
            ?>
                <div class="child-card">
                    <div class="child-header">
                        <div class="child-avatar"><?= mb_substr($c['full_name'], 0, 1) ?></div>
                        <div>
                            <div style="font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700;"><?= htmlspecialchars($c['full_name']) ?></div>
                            <div style="color: var(--muted); font-size: 0.85rem;">@<?= htmlspecialchars($c['username']) ?> • Level <?= $level ?></div>
                        </div>
                    </div>
                    
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-val"><?= $xp ?></span>
                            <span class="stat-label">Total XP</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">🔥 <?= $c['daily_streak'] ?? 0 ?></span>
                            <span class="stat-label">Day Streak</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val"><?= ($c['wins'] ?? 0) + ($c['mcq_wins'] ?? 0) ?></span>
                            <span class="stat-label">Total Wins</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val"><?= $c['total_words_learned'] ?? 0 ?></span>
                            <span class="stat-label">Words Mastery</span>
                        </div>
                    </div>
                    
                    <div style="margin-top: 25px; text-align: center;">
                        <a href="parent_child_report.php?id=<?= $c['id'] ?>" style="display: inline-block; background: var(--purple); color: white; padding: 12px 30px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: 0.3s; box-shadow: 0 5px 15px rgba(124,92,191,0.2);">View Full Progress Report →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="logout-link">Exit Portal</a>
        </div>
    </div>
</body>
</html>
