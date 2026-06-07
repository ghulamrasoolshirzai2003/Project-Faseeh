<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Fetch stats for the header
$stmt = $pdo->prepare("SELECT xp FROM progress WHERE user_id = ?");
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
        'title' => 'Writing Studio',
        'arabic' => 'محترف الكتابة',
        'desc' => 'Classic transcription and composition lessons with Professor Faseeh.',
        'icon' => '📝',
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
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c;
            --glass: rgba(255,255,255,0.05); --glass-border: rgba(255,255,255,0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; overflow-x: hidden;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 120px 30px 60px; }

        .header { text-align: center; margin-bottom: 60px; }
        .header h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 10px; }
        .header p { font-size: 1.1rem; opacity: 0.6; max-width: 600px; margin: 0 auto; line-height: 1.6; }

        .section-title { font-size: 2rem; font-weight: 700; margin: 60px 0 30px; display: flex; align-items: center; gap: 15px; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--glass-border); }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; }
        
        .module-card {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px;
            padding: 40px; transition: 0.4s;
            text-decoration: none; color: white; display: flex; flex-direction: column; height: 100%;
        }
        .module-card:hover { transform: translateY(-10px); background: rgba(255,255,255,0.08); border-color: var(--accent); }
        .module-icon { font-size: 3rem; margin-bottom: 25px; display: block; }
        .module-arabic { font-family: 'Amiri', serif; font-size: 1.4rem; color: var(--accent); margin-bottom: 5px; }
        .module-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 15px; }
        .module-desc { font-size: 0.9rem; opacity: 0.6; line-height: 1.6; flex-grow: 1; margin-bottom: 25px; }
        
        .module-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 20px; }
        .module-count { font-size: 0.8rem; font-weight: 600; opacity: 0.5; }
        .btn-enter { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .module-card:hover .btn-enter { background: var(--accent); color: #333; }

        @media (max-width: 768px) { .header h1 { font-size: 2.5rem; } }
    </style>
</head>
<body>

    <?php require 'includes/navbar.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Faseeh Academy</h1>
            <p>Master the Arabic language through specialized studios and interactive AI-powered challenges.</p>
        </div>

        <h2 class="section-title">🏛️ Learning Studios</h2>
        <div class="grid">
            <?php foreach ($learning_modules as $mod): ?>
                <a href="academy_module.php?type=<?= $mod['id'] ?>" class="module-card">
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

        <h2 class="section-title">🕹️ Game Zone</h2>
        <div class="grid">
            <div class="module-card" style="opacity:0.45; pointer-events:none; cursor:default; text-align:center;">
                <span class="module-icon">🚀</span>
                <div class="module-arabic">قريباً</div>
                <div class="module-title">New Games Coming Soon</div>
                <p class="module-desc">More interactive games are being crafted for this space. Stay tuned!</p>
                <div class="module-footer">
                    <span class="module-count">Coming Soon</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
