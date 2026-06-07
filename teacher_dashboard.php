<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: dashboard.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];

// 1. Get Teacher Info (Class Code)
$stmt = $pdo->prepare("SELECT class_code, full_name FROM users WHERE id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

// 2. Class Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_relationships WHERE parent_id = ? AND relationship_type = 'teacher_of'");
$stmt->execute([$teacher_id]);
$total_students = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT AVG(p.xp) 
    FROM progress p
    JOIN user_relationships ur ON p.user_id = ur.student_id
    WHERE ur.parent_id = ? AND ur.relationship_type = 'teacher_of'
");
$stmt->execute([$teacher_id]);
$avg_xp = round($stmt->fetchColumn() ?? 0);

$stmt = $pdo->prepare("
    SELECT u.full_name, p.xp
    FROM users u
    JOIN progress p ON u.id = p.user_id
    JOIN user_relationships ur ON u.id = ur.student_id
    WHERE ur.parent_id = ? AND ur.relationship_type = 'teacher_of'
    ORDER BY p.xp DESC LIMIT 1
");
$stmt->execute([$teacher_id]);
$top_student = $stmt->fetch();

// 3. Students List
$filter = $_GET['filter'] ?? 'all';
$sql = "
    SELECT u.id, u.full_name, u.username, u.selected_level, p.* 
    FROM users u
    JOIN user_relationships ur ON u.id = ur.student_id
    LEFT JOIN progress p ON u.id = p.user_id
    WHERE ur.parent_id = ? AND ur.relationship_type = 'teacher_of'
";

if ($filter === 'at_risk') {
    $sql .= " AND (p.daily_streak = 0 OR p.xp < 50)";
} elseif ($filter === 'top') {
    $sql .= " ORDER BY p.xp DESC";
} else {
    $sql .= " ORDER BY u.full_name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$teacher_id]);
$students = $stmt->fetchAll();

$view = $_GET['view'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher OS — Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0e0c1e; --bg-side: #121026; --bg-card: #161430; 
            --accent: #f5a623; --accent-glow: rgba(245, 166, 35, 0.2);
            --purple: #7c5cbf; --green: #3ecf8e; --red: #ff4757;
            --text: #f0eeff; --muted: #8b87b0; --border: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; display: flex; min-height: 100vh; }
        
        .sidebar { width: 260px; background: var(--bg-side); border-right: 1px solid var(--border); padding: 30px 20px; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .logo-area { text-decoration: none; display: flex; flex-direction: column; align-items: center; margin-bottom: 40px; width: 100%; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 12px; text-decoration: none; color: var(--muted); font-weight: 500; transition: 0.3s; margin-bottom: 8px; }
        .nav-item:hover { background: rgba(255,255,255,0.03); color: var(--text); }
        .nav-item.active { background: var(--purple); color: #fff; box-shadow: 0 10px 20px rgba(124,92,191,0.3); }

        .main { flex: 1; margin-left: 260px; padding: 40px 60px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 24px; }
        .stat-label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; margin-bottom: 8px; display: block; }
        .stat-value { font-size: 1.8rem; font-weight: 800; font-family: 'Syne', sans-serif; }

        .class-roster { background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; padding: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; color: var(--muted); font-size: 0.75rem; border-bottom: 1px solid var(--border); }
        td { padding: 20px 12px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        
        .btn-add { background: var(--accent); color: #000; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245,166,35,0.4); }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
        .modal-content { background: var(--bg-card); padding: 40px; border-radius: 24px; text-align: center; max-width: 450px; border: 1px solid var(--border); }
        .code-box { background: rgba(0,0,0,0.3); border: 2px dashed var(--accent); padding: 20px; border-radius: 15px; font-family: 'Syne', sans-serif; font-size: 2rem; color: var(--accent); letter-spacing: 5px; margin: 20px 0; }

        .status-pill { padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .on-track { background: rgba(62,207,142,0.1); color: var(--green); }
        .at-risk { background: rgba(255,71,87,0.1); color: var(--red); }
        
        @keyframes spinLogo { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shineText { to { background-position: 200% center; } }
        h1, h2, h3 { font-family: 'Syne', sans-serif; letter-spacing: -0.5px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="teacher_dashboard.php" class="logo-area">
            <div class="mini-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #f2994a, #f2c94c); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 20px rgba(242,153,74,0.4); margin-bottom: 10px;">
                <div style="font-family: 'Amiri', serif; font-size: 24px; color: white; margin-top: -2px; z-index: 2;">ف</div>
                <div style="content: ''; position: absolute; width: 36px; height: 36px; border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent; border-radius: 50%; animation: spinLogo 8s linear infinite;"></div>
            </div>
            <h1 class="mini-text" style="font-size: 1.6rem; font-weight: 800; margin: 0; background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shineText 3s linear infinite; font-family: 'Syne', sans-serif;">Faseeh</h1>
            <span style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 3px; color: var(--accent); opacity: 0.8; font-weight: 800; margin-top: 5px;">Educator OS</span>
        </a>
        <nav>
            <a href="?view=overview" class="nav-item <?= $view == 'overview' ? 'active' : '' ?>"><span>📊</span> Overview</a>
            <a href="?view=leaderboard" class="nav-item <?= $view == 'leaderboard' ? 'active' : '' ?>"><span>🏆</span> Leaderboards</a>
        </nav>
        <div style="margin-top: auto;">
            <a href="index.php?logout=true" class="nav-item" style="color:var(--red); opacity: 0.7;"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <main class="main">
        <header class="top-bar">
            <div>
                <h1><?= $view == 'leaderboard' ? 'Class Leaderboard' : 'Class Overview' ?></h1>
                <p style="color:var(--muted);">Welcome back, Prof. <?= explode(' ', $teacher['full_name'])[0] ?></p>
            </div>
            <button class="btn-add" onclick="showInviteModal()">+ Add Student</button>
        </header>

        <?php if ($view == 'overview'): ?>
            <section class="stats-grid">
                <div class="stat-card"><span class="stat-label">Active Students</span><div class="stat-value"><?= $total_students ?></div></div>
                <div class="stat-card"><span class="stat-label">Class Avg XP</span><div class="stat-value"><?= number_format($avg_xp) ?></div></div>
                <div class="stat-card"><span class="stat-label">Top Performer</span><div class="stat-value" style="font-size: 1rem;"><?= $top_student['full_name'] ?? 'N/A' ?></div></div>
                <div class="stat-card"><span class="stat-label">Class Code</span><div class="stat-value" style="color: var(--accent);"><?= $teacher['class_code'] ?></div></div>
            </section>

            <div class="class-roster">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3>Student Roster</h3>
                    <select onchange="window.location.href='?filter='+this.value" style="background:none; color:white; border:1px solid var(--border); padding:5px; border-radius:5px;">
                        <option value="all" <?= $filter=='all'?'selected':'' ?>>All Students</option>
                        <option value="at_risk" <?= $filter=='at_risk'?'selected':'' ?>>At Risk</option>
                        <option value="top" <?= $filter=='top'?'selected':'' ?>>Top Earners</option>
                    </select>
                </div>
                <?php if (empty($students)): ?>
                    <div style="text-align:center; padding:50px;">
                        <span style="font-size:3rem;">📚</span>
                        <p>No students yet. Give them your code: <strong><?= $teacher['class_code'] ?></strong></p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead><tr><th>Student</th><th>Status</th><th>XP</th><th>Streak</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['full_name']) ?></strong><br><small style="opacity:0.5;">@<?= $s['username'] ?></small></td>
                                    <td><span class="status-pill <?= ($s['daily_streak'] > 0) ? 'on-track' : 'at-risk' ?>"><?= ($s['daily_streak'] > 0) ? 'Active' : 'Idle' ?></span></td>
                                    <td><?= number_format($s['xp']) ?></td>
                                    <td>🔥 <?= $s['daily_streak'] ?></td>
                                    <td><button onclick="window.location.href='teacher_student_report.php?id=<?= $s['id'] ?>'" style="background:none; border:1px solid var(--border); color:var(--muted); padding:5px 10px; border-radius:5px; cursor:pointer;">View</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="class-roster">
                <table>
                    <thead><tr><th>Rank</th><th>Student</th><th>Total XP</th></tr></thead>
                    <tbody>
                        <?php 
                        $stmt = $pdo->prepare("
                            SELECT u.full_name, p.xp 
                            FROM users u 
                            JOIN progress p ON u.id = p.user_id 
                            JOIN user_relationships ur ON u.id = ur.student_id 
                            WHERE ur.parent_id = ? ORDER BY p.xp DESC
                        ");
                        $stmt->execute([$teacher_id]);
                        $rank = 1;
                        while($row = $stmt->fetch()): ?>
                            <tr><td>#<?= $rank++ ?></td><td><?= htmlspecialchars($row['full_name']) ?></td><td style="color:var(--accent); font-weight:800;"><?= number_format($row['xp']) ?> XP</td></tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <!-- Invite Modal -->
    <div id="inviteModal" class="modal">
        <div class="modal-content">
            <h2>Add Students</h2>
            <p style="color:var(--muted); font-size:0.9rem; margin-top:10px;">Give this code to your students. They can enter it in their dashboard to join your class.</p>
            <div class="code-box"><?= $teacher['class_code'] ?></div>
            <button class="btn-add" style="width:100%;" onclick="closeInviteModal()">Got it!</button>
        </div>
    </div>

    <!-- Welcome Modal -->
    <?php if (isset($_GET['welcome'])): ?>
    <div id="welcomeModal" class="modal" style="display: flex;">
        <div class="modal-content" style="max-width: 550px;">
            <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
            <h1>Welcome to the Academy!</h1>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 35px;">You are now officially a Faseeh Educator. Here's how to start:</p>
            <div style="text-align: left; margin-bottom: 40px;">
                <div style="display: flex; gap: 20px; margin-bottom: 20px;"><div style="width: 30px; height: 30px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800;">1</div><p>Share your <strong>Class Code</strong> with your students.</p></div>
                <div style="display: flex; gap: 20px; margin-bottom: 20px;"><div style="width: 30px; height: 30px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800;">2</div><p>Students join using the code on their dashboard.</p></div>
                <div style="display: flex; gap: 20px;"><div style="width: 30px; height: 30px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800;">3</div><p>Track their <strong>XP, Streaks, and Level</strong> in real-time!</p></div>
            </div>
            <button onclick="document.getElementById('welcomeModal').style.display='none'" class="btn-add" style="width: 100%;">Let's Start Educating →</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function showInviteModal() { document.getElementById('inviteModal').style.display = 'flex'; }
        function closeInviteModal() { document.getElementById('inviteModal').style.display = 'none'; }
    </script>
</body>
</html>
