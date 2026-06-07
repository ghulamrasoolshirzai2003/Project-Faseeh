<?php
session_start();
require 'includes/db.php';

// --- SECURITY CHECKS ---
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php");
    exit;
}
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit; 
}

// --- LOGIC ---
if (isset($_GET['set_lang'])) {
    $_SESSION['lang'] = $_GET['set_lang'];
    header("Location: level_select.php"); 
    exit;
}

// FIX: Handle Level Selection and force lowercase for consistency
if (isset($_GET['level'])) {
    $selected_level = strtolower($_GET['level']);
    $_SESSION['level'] = $selected_level;
    
    // Update the database level preference
    $stmt = $pdo->prepare("UPDATE users SET selected_level=? WHERE id=?");
    $stmt->execute([$selected_level, $_SESSION['user_id']]);
    
    // SESSION PERSISTENCE: Check if a Hangman session already exists
    $checkSess = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'hangman'");
    $checkSess->execute([$_SESSION['user_id']]);
    $sess = $checkSess->fetch();
    
    if (!$sess) {
        // Initialize a new 10-word session for Hangman
        $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'hangman', 0, 10)")
            ->execute([$_SESSION['user_id']]);
    }
    
    header("Location: game.php");
    exit;
}

// Handle Manual Restart
if (isset($_GET['restart_hangman'])) {
    $pdo->prepare("UPDATE user_active_sessions SET questions_completed = 0 WHERE user_id = ? AND mode = 'hangman'")
        ->execute([$_SESSION['user_id']]);
    header("Location: game.php");
    exit;
}

// Fetch Stats
$stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$score = $stats['total_score'] ?? 0;
$streak = $stats['daily_streak'] ?? 0;
$wins = $stats['wins'] ?? 0;
$xp = $stats['xp'] ?? 0;
$lang = $_SESSION['lang'] ?? 'en';

// Words per level
$uid = $_SESSION['user_id'];
$levelCounts = [];
foreach(['beginner','intermediate','advanced'] as $lv) {
    $total = $pdo->prepare("SELECT COUNT(*) as cnt FROM words WHERE level = ?");
    $total->execute([$lv]);
    $totalCount = $total->fetch()['cnt'];
    
    $done = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_solved_words up JOIN words w ON up.word_id = w.id WHERE up.user_id = ? AND w.level = ?");
    $done->execute([$uid, $lv]);
    $doneCount = $done->fetch()['cnt'];
    
    $levelCounts[$lv] = ['total' => $totalCount, 'done' => $doneCount];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Choose Your Path</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894; --primary: #5E63BA;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh;
        }



        .center-screen { padding: 100px 20px 40px; max-width: 1100px; margin: 0 auto; }

        /* Welcome */
        .welcome-header { text-align: center; margin-bottom: 25px; animation: fadeIn 0.6s ease; }
        .welcome-emoji { font-size: 3rem; margin-bottom: 5px; }
        .welcome-title { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .welcome-sub { opacity: 0.6; font-size: 0.9rem; }

        /* Stats */
        .stats-bar { display: flex; justify-content: center; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; animation: fadeIn 0.8s ease; }
        .stat-pill {
            background: var(--glass); border: 1px solid var(--glass-border);
            padding: 10px 22px; border-radius: 50px; font-weight: 600; font-size: 0.85rem;
            backdrop-filter: blur(5px);
        }

        /* Lang */
        .lang-switch-box { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .lang-opt {
            text-decoration: none; padding: 10px 25px; border-radius: 12px;
            background: var(--glass); color: rgba(255,255,255,0.6); font-weight: 600;
            transition: 0.3s; border: 1px solid var(--glass-border); font-size: 0.85rem;
        }
        .lang-opt:hover { color: white; background: rgba(255,255,255,0.1); }
        .lang-opt.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 0 15px rgba(94,99,186,0.4); }

        /* Game Mode Cards */
        .mode-section { margin-bottom: 35px; }
        .section-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.4; margin-bottom: 15px; font-weight: 600; }

        .mode-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .mode-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px; text-align: center;
            text-decoration: none; color: white; transition: 0.3s;
            position: relative; overflow: hidden;
        }
        .mode-card:hover { transform: translateY(-5px); border-color: var(--accent); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .mode-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .mode-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 5px; }
        .mode-desc { font-size: 0.75rem; opacity: 0.5; }
        .mode-badge {
            position: absolute; top: 12px; right: 12px; background: var(--accent);
            color: white; font-size: 0.65rem; padding: 3px 8px; border-radius: 10px; font-weight: 700;
        }

        /* Level Grid */
        .level-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .level-card {
            background: var(--glass); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border); border-radius: 25px;
            padding: 30px 20px; text-align: center; text-decoration: none; color: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative; overflow: hidden;
        }
        .level-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
            transform: translateX(-100%); transition: 0.5s;
        }
        .level-card:hover { transform: translateY(-10px); border-color: var(--gold); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .level-card:hover::before { transform: translateX(100%); }
        .lvl-icon { font-size: 3rem; margin-bottom: 10px; display: block; filter: drop-shadow(0 5px 10px rgba(0,0,0,0.3)); }
        .lvl-title { font-size: 1.4rem; font-weight: 800; display: block; margin-bottom: 5px; }
        .lvl-desc { font-size: 0.85rem; opacity: 0.6; display: block; margin-bottom: 12px; }

        /* Progress bar in level card */
        .lvl-progress { margin-top: 10px; }
        .lvl-bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 6px; overflow: hidden; }
        .lvl-fill { height: 100%; background: linear-gradient(to right, var(--accent), var(--accent2)); border-radius: 6px; transition: width 1s ease; }
        .lvl-count { font-size: 0.7rem; opacity: 0.4; margin-top: 5px; }

        .help-fab {
            position: fixed; bottom: 25px; right: 25px;
            width: 50px; height: 50px; background: var(--gold); color: #333;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 800; text-decoration: none;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3); transition: 0.3s; z-index: 1000;
        }
        .help-fab:hover { transform: scale(1.1) rotate(10deg); }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Glassmorphic Modal styling */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 12, 41, 0.75);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            display: none; align-items: center; justify-content: center;
            z-index: 1001;
            animation: fadeInModal 0.3s ease;
        }
        .modal-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 28px; padding: 35px 25px;
            max-width: 540px; width: 90%; text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            animation: scaleInModal 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        .modal-title {
            font-size: 1.6rem; font-weight: 800; margin-bottom: 8px;
            color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .modal-desc {
            font-size: 0.9rem; opacity: 0.7; margin-bottom: 25px;
            line-height: 1.4;
        }
        .modal-options {
            display: flex; flex-direction: column; gap: 12px;
        }
        .modal-btn {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-radius: 16px;
            text-decoration: none; color: white; font-weight: 600;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
            text-align: left;
        }
        .modal-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(242, 153, 74, 0.2);
        }
        .modal-btn-info {
            display: flex; align-items: center;
        }
        .modal-btn-icon {
            font-size: 1.4rem; margin-right: 12px;
        }
        .modal-btn-text-wrapper {
            display: flex; flex-direction: column;
        }
        .modal-btn-title {
            font-size: 1rem; font-weight: 700;
        }
        .modal-btn-desc {
            font-size: 0.75rem; opacity: 0.5; font-weight: 400;
        }
        .modal-btn-right {
            display: flex; align-items: center; gap: 15px; text-align: right;
        }
        .modal-btn-stats {
            display: flex; flex-direction: column; align-items: flex-end;
        }
        .modal-btn-pct {
            font-size: 0.9rem; font-weight: 700; color: var(--accent2);
        }
        .modal-btn-count {
            font-size: 0.65rem; opacity: 0.4; font-weight: 400; margin-top: 1px;
        }
        .modal-btn-arrow {
            font-size: 0.85rem; opacity: 0.5; transition: transform 0.2s;
        }
        .modal-btn:hover .modal-btn-arrow {
            transform: translateX(4px); opacity: 1;
        }
        .modal-close-btn {
            position: absolute; top: 15px; right: 15px;
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s; border: none; outline: none; line-height: 1; padding: 0;
        }
        .modal-close-btn:hover {
            background: rgba(255,255,255,0.15); transform: rotate(90deg);
        }
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleInModal {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 768px) {
            .center-screen { padding: 85px 12px 30px; }
            .welcome-title { font-size: 1.5rem; }
            .level-grid { grid-template-columns: 1fr; }
            .mode-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .mode-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php require 'includes/navbar.php'; ?>

    <div class="center-screen">

        <div class="welcome-header">
            <div class="welcome-emoji">🎓</div>
            <div class="welcome-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
            <p class="welcome-sub">Choose your game mode and level</p>
        </div>

        <div class="stats-bar">
            <div class="stat-pill">⭐ <?php echo number_format($score); ?> Points</div>
            <div class="stat-pill">🔥 <?php echo $streak; ?> Day Streak</div>
            <div class="stat-pill">🏆 <?php echo $wins; ?> Wins</div>
            <div class="stat-pill">✨ <?php echo number_format($xp); ?> XP</div>
        </div>

        <div class="lang-switch-box">
            <a href="?set_lang=en" class="lang-opt <?php echo ($lang=='en')?'active':''; ?>">🇬🇧 English</a>
            <a href="?set_lang=my" class="lang-opt <?php echo ($lang=='my')?'active':''; ?>">🇲🇾 Malay</a>
        </div>

        <!-- Game Modes -->
        <div class="mode-section">
            <div class="section-title">🎮 Game Modes</div>
            <div class="mode-grid">
                <div class="mode-card" onclick="openHangmanModal()" style="cursor:pointer; border-color: rgba(242,153,74,0.4);">
                    <div class="mode-icon">🎯</div>
                    <div class="mode-title">Hangman</div>
                    <div class="mode-desc">Guess the Arabic word letter by letter</div>
                    <div class="mode-badge" style="background: var(--success);">🎮 Play</div>
                </div>
                <a href="skill_tree.php" class="mode-card" style="background: rgba(255, 215, 0, 0.15); border-color: #FFD700; box-shadow: 0 0 15px rgba(255, 215, 0, 0.15);">
                    <div class="mode-icon">🕌</div>
                    <div class="mode-title" style="color: #FFD700;">Path of Cities</div>
                    <div class="mode-desc">Immersive journey across historical Arab capitals</div>
                    <div class="mode-badge" style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000;">FEATURED</div>
                </a>
                <a href="clash_join.php" class="mode-card" style="background: rgba(235, 77, 75, 0.15); border-color: #eb4d4b; box-shadow: 0 0 15px rgba(235, 77, 75, 0.15);">
                    <div class="mode-icon">⚔️</div>
                    <div class="mode-title" style="color: #eb4d4b;">Class Clash</div>
                    <div class="mode-desc">Join a live Kahoot-style quiz hosted by your teacher!</div>
                    <div class="mode-badge" style="background: var(--red);">LIVE</div>
                </a>
                <a href="word_sprint.php" class="mode-card" style="background: rgba(242, 153, 74, 0.1); border-color: var(--accent);">
                    <div class="mode-icon">⚡</div>
                    <div class="mode-title">Word Sprint</div>
                    <div class="mode-desc">60 seconds to translate as many words as you can!</div>
                    <div class="mode-badge" style="background: var(--accent);">NEW</div>
                </a>
                <a href="academic_hub.php" class="mode-card" style="background: rgba(94, 99, 186, 0.2); border-color: #5E63BA;">
                    <div class="mode-icon">🏛️</div>
                    <div class="mode-title">Academic Suite</div>
                    <div class="mode-desc">Sentence builder, calligraphy, grammar & more</div>
                    <div class="mode-badge" style="background:#5E63BA;">v4.0</div>
                </a>

                
            </div>
        </div>


    </div>

    <!-- Hangman Difficulty Selection Modal -->
    <div id="hangmanModal" class="modal-overlay">
        <div class="modal-card">
            <button class="modal-close-btn" onclick="closeHangmanModal()">&times;</button>
            <div class="modal-title">🎯 Hangman Mode</div>
            <div class="modal-desc">Select a difficulty level to begin your learning challenge:</div>
            
            <div class="modal-options">
                <a href="?level=beginner" class="modal-btn">
                    <div class="modal-btn-info">
                        <span class="modal-btn-icon">🌱</span>
                        <div class="modal-btn-text-wrapper">
                            <span class="modal-btn-title">Beginner</span>
                            <span class="modal-btn-desc">Basic Nouns & Numbers</span>
                        </div>
                    </div>
                    <div class="modal-btn-right">
                        <div class="modal-btn-stats">
                            <?php $pct = $levelCounts['beginner']['total'] > 0 ? round(($levelCounts['beginner']['done'] / $levelCounts['beginner']['total']) * 100) : 0; ?>
                            <span class="modal-btn-pct"><?php echo $pct; ?>%</span>
                            <span class="modal-btn-count"><?php echo $levelCounts['beginner']['done']; ?>/<?php echo $levelCounts['beginner']['total']; ?></span>
                        </div>
                        <span class="modal-btn-arrow">➔</span>
                    </div>
                </a>
                
                <a href="?level=intermediate" class="modal-btn">
                    <div class="modal-btn-info">
                        <span class="modal-btn-icon">🚀</span>
                        <div class="modal-btn-text-wrapper">
                            <span class="modal-btn-title">Intermediate</span>
                            <span class="modal-btn-desc">Places & Verbs</span>
                        </div>
                    </div>
                    <div class="modal-btn-right">
                        <div class="modal-btn-stats">
                            <?php $pct = $levelCounts['intermediate']['total'] > 0 ? round(($levelCounts['intermediate']['done'] / $levelCounts['intermediate']['total']) * 100) : 0; ?>
                            <span class="modal-btn-pct"><?php echo $pct; ?>%</span>
                            <span class="modal-btn-count"><?php echo $levelCounts['intermediate']['done']; ?>/<?php echo $levelCounts['intermediate']['total']; ?></span>
                        </div>
                        <span class="modal-btn-arrow">➔</span>
                    </div>
                </a>
                
                <a href="?level=advanced" class="modal-btn">
                    <div class="modal-btn-info">
                        <span class="modal-btn-icon">🔥</span>
                        <div class="modal-btn-text-wrapper">
                            <span class="modal-btn-title">Advanced</span>
                            <span class="modal-btn-desc">Abstract Concepts</span>
                        </div>
                    </div>
                    <div class="modal-btn-right">
                        <div class="modal-btn-stats">
                            <?php $pct = $levelCounts['advanced']['total'] > 0 ? round(($levelCounts['advanced']['done'] / $levelCounts['advanced']['total']) * 100) : 0; ?>
                            <span class="modal-btn-pct"><?php echo $pct; ?>%</span>
                            <span class="modal-btn-count"><?php echo $levelCounts['advanced']['done']; ?>/<?php echo $levelCounts['advanced']['total']; ?></span>
                        </div>
                        <span class="modal-btn-arrow">➔</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <a href="help.php" class="help-fab" title="Help">?</a>

    <script>
        function openHangmanModal() {
            document.getElementById('hangmanModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeHangmanModal() {
            document.getElementById('hangmanModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        // Close modal when clicking outside the card content
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('hangmanModal');
            if (event.target == modal) {
                closeHangmanModal();
            }
        });
    </script>

</body>
</html>