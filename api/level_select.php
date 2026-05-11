<?php
session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
session_start();
require 'includes/db.php';

// --- SECURITY CHECKS ---
// --- SECURITY CHECKS ---
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: /admin_panel.php");
    exit;
}
if (!isset($_SESSION['user_id'])) { 
    echo "<div style='background: #1e1e2e; color: white; padding: 30px; font-family: sans-serif; border-radius: 10px; margin: 50px auto; max-width: 600px; border: 1px solid #f2994a;'>";
    echo "<h2 style='color: #f2994a;'>🚫 Session Access Denied</h2>";
    echo "<p>Your session could not be verified. This usually happens if cookies are blocked or the session expired.</p>";
    echo "<hr style='border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;'>";
    echo "<strong>Session ID:</strong> <code>" . session_id() . "</code><br><br>";
    echo "<strong>Session Data:</strong> <pre style='background: rgba(0,0,0,0.3); padding: 15px; border-radius: 5px;'>" . print_r($_SESSION, true) . "</pre>";
    echo "<br><a href='/' style='background: #f2994a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Return to Login</a>";
    echo "</div>";
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
    
    $done = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_progress up JOIN words w ON up.word_id = w.id WHERE up.user_id = ? AND w.level = ?");
    $done->execute([$uid, $lv]);
    $doneCount = $done->fetch()['cnt'];
    
    $levelCounts[$lv] = ['total' => $totalCount, 'done' => $doneCount];
}

// Reviews due
$today = date('Y-m-d');
$revStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM review_queue WHERE user_id = ? AND next_review <= ?");
$revStmt->execute([$uid, $today]);
$reviewsDue = $revStmt->fetch()['cnt'] ?? 0;
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

        /* Navbar */
        .navbar {
            width: 100%; padding: 15px 25px;
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
        .mini-text { font-size: 1.4rem; font-weight: 800; margin: 0; color: white; }
        .nav-links { display: flex; gap: 8px; align-items: center; }
        .nav-link {
            color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.8rem;
            padding: 7px 16px; border-radius: 25px; transition: 0.3s; font-weight: 500;
            border: 1px solid transparent;
        }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { background: rgba(242,153,74,0.2); color: var(--accent); }
        .nav-link.logout { border-color: rgba(255,255,255,0.15); }
        .nav-link.logout:hover { background: white; color: var(--bg-start); }

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

        @media (max-width: 768px) {
            .center-screen { padding: 85px 12px 30px; }
            .welcome-title { font-size: 1.5rem; }
            .level-grid { grid-template-columns: 1fr; }
            .mode-grid { grid-template-columns: 1fr 1fr; }
            .nav-links { gap: 5px; }
            .nav-link { font-size: 0.7rem; padding: 5px 10px; }
        }
        @media (max-width: 480px) {
            .mode-grid { grid-template-columns: 1fr; }
            .navbar { padding: 10px 12px; }
            .mini-text { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </a>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">📊 <span class="hide-mobile">Dashboard</span></a>
            <a href="academy.php" class="nav-link">🎓 <span class="hide-mobile">Academy</span></a>
            <a href="leaderboard.php" class="nav-link">🏆 <span class="hide-mobile">Rankings</span></a>
            <a href="profile.php" class="nav-link" style="border-color: rgba(255,255,255,0.15);">👤 <span class="hide-mobile">Profile</span></a>
        </div>
    </nav>

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
                <div class="mode-card" style="cursor:default; border-color: rgba(242,153,74,0.2);">
                    <div class="mode-icon">🎯</div>
                    <div class="mode-title">Hangman</div>
                    <div class="mode-desc">Guess the Arabic word letter by letter</div>
                    <div class="mode-badge" style="background: var(--success);">👇 Select Level</div>
                </div>
                <a href="academy.php" class="mode-card" style="border-color: var(--accent);">
                    <div class="mode-icon">🎓</div>
                    <div class="mode-title">Academy</div>
                    <div class="mode-desc">Learn Reading, Writing, and Speaking Skills</div>
                    <div class="mode-badge" style="background: var(--success);">NEW</div>
                </a>
                <a href="mcq.php" class="mode-card">
                    <div class="mode-icon">🧩</div>
                    <div class="mode-title">Quiz Mode</div>
                    <div class="mode-desc">Multiple choice — 10 quick questions</div>
                    <div class="mode-badge">NEW</div>
                </a>
                <a href="review.php" class="mode-card">
                    <div class="mode-icon">🧠</div>
                    <div class="mode-title">Review</div>
                    <div class="mode-desc">Spaced repetition flashcards</div>
                    <?php if($reviewsDue > 0): ?>
                    <div class="mode-badge" style="background: var(--success);"><?php echo $reviewsDue; ?> due</div>
                    <?php endif; ?>
                </a>
                <a href="academic_hub.php" class="mode-card" style="background: rgba(94, 99, 186, 0.2); border-color: #5E63BA;">
                    <div class="mode-icon">🏛️</div>
                    <div class="mode-title">Academic Suite</div>
                    <div class="mode-desc">Grammar, Sentence Builder & More</div>
                    <div class="mode-badge" style="background: #5E63BA;">v4.0</div>
                </a>
            </div>
        </div>

        <!-- Level Selection -->
        <div class="section-title">🌱 Choose Hangman Level</div>
        <div class="level-grid">
            <a href="?level=beginner" class="level-card">
                <span class="lvl-icon">🌱</span>
                <span class="lvl-title">Beginner</span>
                <span class="lvl-desc">Basic Nouns & Numbers</span>
                <div class="lvl-progress">
                    <?php $pct = $levelCounts['beginner']['total'] > 0 ? round(($levelCounts['beginner']['done'] / $levelCounts['beginner']['total']) * 100) : 0; ?>
                    <div class="lvl-bar"><div class="lvl-fill" style="width:<?php echo $pct; ?>%"></div></div>
                    <div class="lvl-count"><?php echo $levelCounts['beginner']['done']; ?>/<?php echo $levelCounts['beginner']['total']; ?> words completed</div>
                </div>
            </a>

            <a href="?level=intermediate" class="level-card">
                <span class="lvl-icon">🚀</span>
                <span class="lvl-title">Intermediate</span>
                <span class="lvl-desc">Places & Verbs</span>
                <div class="lvl-progress">
                    <?php $pct = $levelCounts['intermediate']['total'] > 0 ? round(($levelCounts['intermediate']['done'] / $levelCounts['intermediate']['total']) * 100) : 0; ?>
                    <div class="lvl-bar"><div class="lvl-fill" style="width:<?php echo $pct; ?>%"></div></div>
                    <div class="lvl-count"><?php echo $levelCounts['intermediate']['done']; ?>/<?php echo $levelCounts['intermediate']['total']; ?> words completed</div>
                </div>
            </a>

            <a href="?level=advanced" class="level-card">
                <span class="lvl-icon">🔥</span>
                <span class="lvl-title">Advanced</span>
                <span class="lvl-desc">Abstract Concepts</span>
                <div class="lvl-progress">
                    <?php $pct = $levelCounts['advanced']['total'] > 0 ? round(($levelCounts['advanced']['done'] / $levelCounts['advanced']['total']) * 100) : 0; ?>
                    <div class="lvl-bar"><div class="lvl-fill" style="width:<?php echo $pct; ?>%"></div></div>
                    <div class="lvl-count"><?php echo $levelCounts['advanced']['done']; ?>/<?php echo $levelCounts['advanced']['total']; ?> words completed</div>
                </div>
            </a>
        </div>
    </div>
    
    <a href="help.php" class="help-fab" title="Help">?</a>

</body>
</html>