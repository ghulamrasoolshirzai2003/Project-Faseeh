<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch stats for the header
$stmt = $pdo->prepare("SELECT total_score, xp FROM progress WHERE user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
$xp = $stats['xp'] ?? 0;
$level = floor($xp / 100) + 1;

$learning_modules = [
    [
        'id' => 'reading',
        'title' => 'Reading Sanctuary',
        'arabic' => 'محراب القراءة',
        'desc' => 'Interactive texts with AI-powered translation and linguistic analysis.',
        'icon' => '📚',
        'color' => '#3498db',
        'count' => '12 Lessons'
    ],
    [
        'id' => 'writing',
        'title' => 'Writing Atelier',
        'arabic' => 'محترف الكتابة',
        'desc' => 'Master the art of calligraphy and composition with real-time AI feedback.',
        'icon' => '✍️',
        'color' => '#e67e22',
        'count' => '8 Lessons'
    ],
    [
        'id' => 'speaking',
        'title' => 'Speaking Studio',
        'arabic' => 'استوديو الكلام',
        'desc' => 'Perfect your accent and fluency using advanced voice recognition.',
        'icon' => '🗣️',
        'color' => '#2ecc71',
        'count' => '15 Lessons'
    ],
    [
        'id' => 'listening',
        'title' => 'Listening Lounge',
        'arabic' => 'رواق الاستماع',
        'desc' => 'Immerse yourself in authentic Arabic audio with dynamic comprehension.',
        'icon' => '🎧',
        'color' => '#9b59b6',
        'count' => '10 Lessons'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh Academy — Master Arabic Skills</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.05); --glass-border: rgba(255,255,255,0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; overflow-x: hidden;
        }

        /* --- NAVBAR --- */
        .navbar {
            padding: 20px 40px; display: flex; justify-content: space-between; align-items: center;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border); position: sticky; top: 0; z-index: 100;
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .mini-icon { width: 45px; height: 45px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .mini-letter { font-family: 'Amiri', serif; font-size: 24px; font-weight: bold; }
        .nav-links { display: flex; gap: 25px; align-items: center; }
        .nav-link { text-decoration: none; color: white; opacity: 0.7; font-size: 0.9rem; font-weight: 500; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { opacity: 1; color: var(--accent); }

        .container { max-width: 1200px; margin: 50px auto; padding: 0 30px; }

        .header { text-align: center; margin-bottom: 60px; animation: fadeInDown 0.8s ease-out; }
        .header h1 { font-size: 3.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }
        .header p { font-size: 1.1rem; opacity: 0.6; max-width: 600px; margin: 0 auto; line-height: 1.6; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; }
        
        .module-card {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px;
            padding: 40px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none; color: white; position: relative; overflow: hidden;
            display: flex; flex-direction: column; height: 100%;
        }
        .module-card:hover {
            transform: translateY(-10px); background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.2);
        }
        .module-card::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, var(--module-color) 0%, transparent 70%);
            opacity: 0.05; transition: 0.4s;
        }
        .module-card:hover::before { opacity: 0.15; }

        .module-icon { font-size: 3rem; margin-bottom: 25px; display: block; }
        .module-arabic { font-family: 'Amiri', serif; font-size: 1.4rem; color: var(--accent2); margin-bottom: 5px; }
        .module-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 15px; }
        .module-desc { font-size: 0.9rem; opacity: 0.6; line-height: 1.6; flex-grow: 1; margin-bottom: 25px; }
        
        .module-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 20px; }
        .module-count { font-size: 0.8rem; font-weight: 600; opacity: 0.5; }
        .btn-enter { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .module-card:hover .btn-enter { background: var(--accent); transform: rotate(-45deg); }

        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .header h1 { font-size: 2.5rem; }
            .navbar { padding: 15px 20px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 style="font-size: 1.4rem; font-weight: 800;">Faseeh Academy</h1>
        </a>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="level_select.php" class="nav-link">Play Games</a>
            <a href="academy.php" class="nav-link active">Academy</a>
            <a href="leaderboard.php" class="nav-link">Leaderboard</a>
        </div>
        <div style="background: var(--glass); padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; border: 1px solid var(--glass-border);">
            Lvl <?= $level ?>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Master the Language</h1>
            <p>Step into our specialized studios to refine your Arabic skills with the help of Faseeh AI Tutors.</p>
        </div>

        <div class="grid">
            <?php foreach ($learning_modules as $mod): ?>
                <a href="academy_module.php?type=<?= $mod['id'] ?>" class="module-card" style="--module-color: <?= $mod['color'] ?>">
                    <span class="module-icon"><?= $mod['icon'] ?></span>
                    <div class="module-arabic"><?= $mod['arabic'] ?></div>
                    <div class="module-title"><?= $mod['title'] ?></div>
                    <p class="module-desc"><?= $mod['desc'] ?></p>
                    <div class="module-footer">
                        <span class="module-count"><?= $mod['count'] ?></span>
                        <div class="btn-enter">→</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>
