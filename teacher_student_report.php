<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: dashboard.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header('Location: teacher_dashboard.php');
    exit;
}

// 1. Verify this student belongs to this teacher
$stmt = $pdo->prepare("SELECT id FROM user_relationships WHERE student_id = ? AND parent_id = ? AND relationship_type = 'teacher_of'");
$stmt->execute([$student_id, $teacher_id]);
if (!$stmt->fetch()) {
    die("Unauthorized access to student data.");
}

// 2. Fetch Student Info
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report — <?= htmlspecialchars($student['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0e0c1e; --bg-card: #161430; --accent: #f5a623;
            --purple: #7c5cbf; --red: #ff4757; --green: #3ecf8e;
            --text: #f0eeff; --muted: #8b87b0; --border: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; padding: 40px; }
        
        .container { max-width: 900px; margin: 0 auto; }
        .back-link { color: var(--muted); text-decoration: none; display: flex; align-items: center; gap: 8px; margin-bottom: 30px; font-weight: 500; }
        
        .header { display: flex; align-items: center; gap: 30px; background: var(--bg-card); border: 1px solid var(--border); padding: 40px; border-radius: 30px; margin-bottom: 30px; }
        .avatar { width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; border: 2px solid var(--accent); }
        .info h1 { font-family: 'Syne', sans-serif; font-size: 2.2rem; }
        .badge { display: inline-block; padding: 4px 12px; background: var(--purple); border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-top: 10px; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border); padding: 25px; border-radius: 24px; text-align: center; }
        .stat-val { font-size: 1.8rem; font-weight: 800; font-family: 'Syne', sans-serif; color: var(--accent); }
        .stat-lab { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; margin-top: 5px; }

        .detail-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 30px; padding: 40px; margin-bottom: 30px; }
        .section-title { font-family: 'Syne', sans-serif; margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }

        .badge-list { display: flex; gap: 15px; flex-wrap: wrap; }
        .badge-item { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 15px; border: 1px solid var(--border); text-align: center; width: 100px; }
        
        .btn-danger { background: rgba(255,71,87,0.1); color: var(--red); border: 1px solid var(--red); padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-danger:hover { background: var(--red); color: white; }

        .progress-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .progress-bar { height: 12px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; border: 1px solid var(--border); }
        .progress-fill { height: 100%; background: linear-gradient(to right, var(--purple), #9b7de3); border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="teacher_dashboard.php" class="back-link">← Back to Classroom</a>

        <div class="header">
            <div class="avatar"><?= $student['avatar'] ?: '👨‍🎓' ?></div>
            <div class="info">
                <h1><?= htmlspecialchars($student['full_name']) ?></h1>
                <p style="opacity: 0.6;">@<?= $student['username'] ?></p>
                <div class="badge"><?= ucfirst($student['selected_level']) ?> Path</div>
            </div>
            <div style="margin-left: auto;">
                <button class="btn-danger" onclick="confirmRemove()">Remove from Class</button>
            </div>
        </div>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-val"><?= number_format($student['xp'] + $student['academic_xp']) ?></div>
                <div class="stat-lab">Total XP</div>
            </div>
            <div class="stat-card">
                <div class="stat-val">🔥 <?= $student['daily_streak'] ?></div>
                <div class="stat-lab">Day Streak</div>
            </div>
            <div class="stat-card">
                <div class="stat-val"><?= $student['wins'] ?></div>
                <div class="stat-lab">Games Won</div>
            </div>
        </section>

        <div style="text-align: center; margin-bottom: 30px;">
            <a href="b2b_dashboard.php?student_id=<?= $student_id ?>" style="display: inline-block; background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000; padding: 12px 30px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.95rem; box-shadow: 0 5px 15px rgba(243,156,18,0.2); transition: 0.3s;">
                📊 View Advanced Analytical Charts
            </a>
        </div>

        <div class="detail-section">
            <h3 class="section-title">📊 Mastery Analytics</h3>
            <div class="progress-row">
                <span>Academic Questions Correct</span>
                <span style="color: var(--green);"><?= $student['academic_xp'] ?></span>
            </div>
            <div class="progress-row">
                <span>Hangman Accuracy</span>
                <span><?= ($student['wins'] + $student['losses']) > 0 ? round(($student['wins'] / ($student['wins'] + $student['losses'])) * 100) : 0 ?>%</span>
            </div>
            <div class="progress-bar">
                <?php $acc = ($student['wins'] + $student['losses']) > 0 ? ($student['wins'] / ($student['wins'] + $student['losses'])) * 100 : 0; ?>
                <div class="progress-fill" style="width: <?= $acc ?>%;"></div>
            </div>
        </div>

        <div class="detail-section">
            <h3 class="section-title">🏆 Achievement Progress</h3>
            <div class="badge-list">
                <?php if (empty($achievements)): ?>
                    <p style="color: var(--muted); font-style: italic;">No badges earned yet.</p>
                <?php else: ?>
                    <?php foreach ($achievements as $ach): ?>
                        <div class="badge-item">
                            <div style="font-size: 2rem;"><?= $ach['icon'] ?></div>
                            <div style="font-size: 0.6rem; font-weight: 700; margin-top: 5px;"><?= $ach['title'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmRemove() {
            if (confirm("🚨 Are you sure you want to remove <?= htmlspecialchars($student['full_name']) ?> from your class? They will no longer appear in your roster.")) {
                fetch('api/remove_student.php?id=<?= $student_id ?>', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Student removed successfully.");
                        window.location.href = 'teacher_dashboard.php';
                    } else {
                        alert("Error: " + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
