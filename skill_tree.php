<?php
// skill_tree.php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
$stmt->execute([$userId]);
$prog = $stmt->fetch();
$xp = $prog['xp'] ?? 0;

$track = $_SESSION['learning_track'] ?? 'msa';

// City Chapters Definition (Geographical & Cultural Gamification)
$cities = [
    [
        "name" => "Mecca (مكة المكرمة)",
        "arabic" => "مهد الفصاحة",
        "description" => "Begin your spiritual and linguistic pilgrimage in Mecca, focusing on alphabets and Quranic fundamentals.",
        "nodes" => [
            ["id" => 1, "title" => "Alphabets", "icon" => "🔤", "req_xp" => 0, "link" => "writing_canvas.php"],
            ["id" => 2, "title" => "Basic Nouns", "icon" => "🍎", "req_xp" => 50, "link" => "vocab_match.php?level=beginner"]
        ]
    ],
    [
        "name" => "Cairo (القاهرة)",
        "arabic" => "سوق الكلمات",
        "description" => "Venture into the bustling historic markets of Cairo to expand your vocabulary with root words and fast-paced matching.",
        "nodes" => [
            ["id" => 3, "title" => "Root Words", "icon" => "🌳", "req_xp" => 150, "link" => "root_word.php"],
            ["id" => 4, "title" => "Bazaar Duel", "icon" => "👹", "req_xp" => 300, "link" => "word_sprint.php", "is_boss" => true]
        ]
    ],
    [
        "name" => "Baghdad (بغداد)",
        "arabic" => "بيت الحكمة",
        "description" => "Study the golden age foundations of grammar and syntax inside the grand House of Wisdom.",
        "nodes" => [
            ["id" => 5, "title" => "Verbs & Tenses", "icon" => "🏃", "req_xp" => 500, "link" => "conjugator.php"],
            ["id" => 6, "title" => "Sentence Builder", "icon" => "🧩", "req_xp" => 800, "link" => "grammar.php"]
        ]
    ],
    [
        "name" => "Damascus (دمشق)",
        "arabic" => "أكاديمية الفكر",
        "description" => "Test your listening and dictation skills, culminating in a formal presentation at the royal courts of Damascus.",
        "nodes" => [
            ["id" => 7, "title" => "Dictation", "icon" => "🎧", "req_xp" => 1200, "link" => "dictation.php"],
            ["id" => 8, "title" => "The Royal Majlis", "icon" => "🐉", "req_xp" => 1800, "link" => "majlis.php", "is_boss" => true]
        ]
    ],
    [
        "name" => "Dubai (دبي)",
        "arabic" => "مستقبل الضاد",
        "description" => "Conclude your journey in modern Dubai, mastering advanced reading comprehension and professional fluency.",
        "nodes" => [
            ["id" => 9, "title" => "Reading Mastery", "icon" => "📖", "req_xp" => 2500, "link" => "reading.php"]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Path of Cities | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: <?= $track == 'quranic' ? '#FFD700' : '#f2994a' ?>;
            --accent2: #f2c94c;
            --locked: #8b87b0; --boss: #e74c3c;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; padding-bottom: 80px;
        }
        
        .header { padding: 25px 40px; background: rgba(0,0,0,0.4); display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; z-index: 100; backdrop-filter: blur(15px); border-bottom: 1px solid var(--glass-border); }
        .header h1 { font-weight: 800; font-size: 1.6rem; }
        .xp-badge { background: rgba(0,0,0,0.5); padding: 8px 24px; border-radius: 50px; font-weight: 700; color: var(--accent); border: 1.5px solid var(--accent); }

        .tree-outer { margin-top: 130px; display: flex; flex-direction: column; align-items: center; max-width: 900px; margin-left: auto; margin-right: auto; padding: 0 20px; }

        /* Track Selector Card */
        .track-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            padding: 20px; border-radius: 20px; text-align: center; width: 100%;
            margin-bottom: 40px; backdrop-filter: blur(10px);
        }
        .track-btn {
            background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);
            color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700;
            margin: 5px; cursor: pointer; transition: 0.3s;
        }
        .track-btn.active {
            background: var(--accent); color: black; box-shadow: 0 5px 15px rgba(242, 153, 74, 0.3); border-color: transparent;
        }

        /* City Chapter Blocks */
        .city-block {
            width: 100%; background: rgba(255,255,255,0.02);
            border: 1px solid var(--glass-border); border-radius: 30px;
            padding: 40px 30px; margin-bottom: 50px; backdrop-filter: blur(10px);
            position: relative; overflow: hidden;
        }
        .city-block::before {
            content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
            background: var(--accent);
        }
        
        .city-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .city-title-group h2 { font-weight: 900; font-size: 1.8rem; }
        .city-ar { font-family: 'Amiri', serif; font-size: 2.2rem; color: var(--accent); line-height: 1; }

        /* Tree Nodes Visual Path */
        .nodes-path-container { display: flex; flex-direction: column; align-items: center; position: relative; margin-top: 20px; }
        .nodes-line { position: absolute; width: 6px; background: rgba(255,255,255,0.1); top: 30px; bottom: 30px; z-index: 1; }
        .node-row { display: flex; justify-content: center; width: 100%; margin-bottom: 40px; position: relative; z-index: 2; }
        .node-row:last-child { margin-bottom: 0; }

        .node-btn-wrapper { display: flex; flex-direction: column; align-items: center; text-decoration: none; }
        .node-icon {
            width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center; font-size: 2.2rem;
            border: 4px solid var(--glass-border); box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .node-title { margin-top: 12px; font-weight: 700; font-size: 0.95rem; color: var(--locked); text-transform: uppercase; letter-spacing: 1px; }

        .node-btn-wrapper.unlocked .node-icon { background: var(--accent); border-color: #fff; box-shadow: 0 0 25px rgba(242, 153, 74, 0.4); cursor: pointer; color: black; }
        .node-btn-wrapper.unlocked:hover .node-icon { transform: scale(1.15) translateY(-5px); }
        .node-btn-wrapper.unlocked .node-title { color: white; }

        /* Boss nodes */
        .node-btn-wrapper.boss .node-icon { border-radius: 20px; transform: rotate(45deg); width: 90px; height: 90px; }
        .node-btn-wrapper.boss .node-icon > div { transform: rotate(-45deg); }
        .node-btn-wrapper.boss.unlocked .node-icon { background: var(--boss); color: white; box-shadow: 0 0 30px rgba(255, 63, 52, 0.5); }
        .node-btn-wrapper.boss.unlocked:hover .node-icon { transform: scale(1.15) rotate(45deg); }

        .node-req { font-size: 0.75rem; background: rgba(0,0,0,0.6); padding: 3px 10px; border-radius: 50px; margin-top: 5px; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <a href="dashboard.php" style="color:white; text-decoration:none; font-weight:700; font-size: 0.9rem;">← Hub Dashboard</a>
        <h1 style="font-family: 'Amiri', serif;">مسار الفصاحة</h1>
        <div class="xp-badge">⭐ <?= number_format($xp) ?> XP</div>
    </div>

    <div class="tree-outer">
        <!-- Upgrade B: Track Selector -->
        <div class="track-card">
            <h3 style="margin-bottom: 12px; opacity: 0.8;">Choose Your Educational Track</h3>
            <button class="track-btn <?= $track == 'msa' ? 'active' : '' ?>" onclick="updateTrack('msa')">
                🌐 Modern Standard Arabic (MSA)
            </button>
            <button class="track-btn <?= $track == 'quranic' ? 'active' : '' ?>" onclick="updateTrack('quranic')" style="border-color: #ffd700;">
                📖 Quranic & Classical Arabic
            </button>
            <p style="font-size: 0.8rem; opacity: 0.5; margin-top: 10px;">
                Note: Switching tracks automatically customizes the AI Majlis and reading stories!
            </p>
        </div>

        <!-- Upgrade A: Cities of the Arab World Chapters -->
        <?php foreach ($cities as $city): ?>
            <div class="city-block">
                <div class="city-header">
                    <div class="city-title-group">
                        <h2><?= $city['name'] ?></h2>
                        <p style="opacity: 0.7; font-size: 0.9rem; margin-top: 5px; max-width: 500px;"><?= $city['description'] ?></p>
                    </div>
                    <div class="city-ar"><?= $city['arabic'] ?></div>
                </div>

                <div class="nodes-path-container">
                    <div class="nodes-line"></div>
                    <?php foreach ($city['nodes'] as $node): 
                        $isUnlocked = $xp >= $node['req_xp'];
                        $isBoss = isset($node['is_boss']);
                        $classes = "node-btn-wrapper";
                        if ($isUnlocked) $classes .= " unlocked";
                        if ($isBoss) $classes .= " boss";
                    ?>
                        <div class="node-row">
                            <a href="<?= $isUnlocked ? $node['link'] : '#' ?>" class="<?= $classes ?>" onclick="<?= !$isUnlocked ? "alert('🔒 Locked! You need {$node['req_xp']} XP to enter this City Level.'); return false;" : '' ?>">
                                <div class="node-icon"><div><?= $node['icon'] ?></div></div>
                                <div class="node-title"><?= $node['title'] ?></div>
                                <?php if(!$isUnlocked): ?>
                                    <div class="node-req">🔒 <?= $node['req_xp'] ?> XP</div>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        async function updateTrack(trackName) {
            const res = await fetch('api/update_track.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({track: trackName})
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert("Error setting track: " + data.error);
            }
        }
    </script>
</body>
</html>
