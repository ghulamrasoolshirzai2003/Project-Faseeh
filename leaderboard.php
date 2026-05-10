<?php
session_start();
require 'includes/db.php';

$filter = $_GET['filter'] ?? 'all';
$period = $_GET['period'] ?? 'all';

// Build query
$whereLevel = '';
$params = [];
if ($filter !== 'all' && in_array($filter, ['beginner','intermediate','advanced'])) {
    $whereLevel = "AND u.selected_level = ?";
    $params[] = $filter;
}

$sql = "SELECT u.id, u.username, u.full_name, u.selected_level,
               COALESCE(p.total_score, 0) as total_score,
               COALESCE(p.current_streak, 0) as current_streak,
               COALESCE(p.daily_streak, 0) as daily_streak,
               COALESCE(p.wins, 0) as wins,
               COALESCE(p.xp, 0) as xp,
               (SELECT COUNT(*) FROM user_progress up WHERE up.user_id = u.id) as words_learned
        FROM users u
        LEFT JOIN progress p ON u.id = p.user_id
        WHERE u.role != 'admin' $whereLevel
        ORDER BY total_score DESC
        LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

// Current user rank
$myRank = 0;
$myId = $_SESSION['user_id'] ?? 0;
foreach ($players as $i => $p) {
    if ($p['id'] == $myId) { $myRank = $i + 1; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Leaderboard</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894;
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
            position: fixed; top: 0; z-index: 100;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .mini-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .mini-letter { font-family: 'Amiri', serif; font-size: 18px; color: white; margin-top: -2px; }
        .mini-text { font-size: 1.2rem; font-weight: 800; color: white; margin: 0; }
        .nav-links { display: flex; gap: 8px; }
        .nav-link { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.8rem; padding: 7px 15px; border-radius: 20px; transition: 0.3s; font-weight: 500; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { background: rgba(242,153,74,0.2); color: var(--accent); }

        .lb-container { max-width: 800px; margin: 0 auto; padding: 90px 20px 40px; }

        /* Header */
        .lb-header { text-align: center; margin-bottom: 25px; }
        .lb-header h1 { font-size: 2rem; font-weight: 800; }
        .lb-header p { opacity: 0.5; font-size: 0.85rem; }

        /* Podium */
        .podium { display: flex; justify-content: center; align-items: flex-end; gap: 15px; margin-bottom: 30px; }
        .podium-spot {
            text-align: center; padding: 20px 15px; border-radius: 20px;
            background: var(--glass); border: 1px solid var(--glass-border);
            min-width: 130px; transition: 0.3s; position: relative;
        }
        .podium-spot:hover { transform: translateY(-5px); }
        .podium-spot.gold { border-color: rgba(255,215,0,0.4); background: rgba(255,215,0,0.08); order: 2; padding-bottom: 35px; }
        .podium-spot.silver { order: 1; }
        .podium-spot.bronze { order: 3; }
        .podium-medal { font-size: 2rem; margin-bottom: 8px; }
        .podium-spot.gold .podium-medal { font-size: 2.5rem; filter: drop-shadow(0 0 10px rgba(255,215,0,0.5)); }
        .podium-name { font-weight: 700; font-size: 0.9rem; margin-bottom: 3px; }
        .podium-score { font-size: 1.3rem; font-weight: 800; color: var(--gold); }
        .podium-level { font-size: 0.65rem; opacity: 0.4; text-transform: uppercase; letter-spacing: 1px; }
        .podium-crown {
            position: absolute; top: -15px; left: 50%; transform: translateX(-50%);
            font-size: 1.5rem; filter: drop-shadow(0 0 5px rgba(255,215,0,0.5));
        }

        /* Filter tabs */
        .filter-row {
            display: flex; gap: 6px; justify-content: center; margin-bottom: 20px; flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 18px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6);
            font-size: 0.8rem; cursor: pointer; transition: 0.3s; text-decoration: none;
            font-weight: 500;
        }
        .filter-btn:hover { color: white; border-color: rgba(255,255,255,0.3); }
        .filter-btn.active { background: rgba(242,153,74,0.2); color: var(--accent); border-color: rgba(242,153,74,0.3); }

        /* My rank banner */
        .my-rank {
            background: linear-gradient(135deg, rgba(242,153,74,0.15), rgba(242,201,76,0.1));
            border: 1px solid rgba(242,153,74,0.25); border-radius: 15px;
            padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .my-rank-label { font-size: 0.85rem; font-weight: 600; }
        .my-rank-num { font-size: 1.3rem; font-weight: 800; color: var(--accent); }

        /* Rankings list */
        .rank-list { display: flex; flex-direction: column; gap: 6px; }
        .rank-row {
            display: flex; align-items: center; padding: 14px 18px;
            border-radius: 12px; background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.04); transition: 0.3s;
            gap: 15px;
        }
        .rank-row:hover { background: rgba(255,255,255,0.06); transform: translateX(3px); }
        .rank-row.me { border-color: rgba(242,153,74,0.3); background: rgba(242,153,74,0.08); }
        .rank-pos { font-weight: 800; font-size: 1rem; min-width: 35px; color: rgba(255,255,255,0.4); }
        .rank-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
        }
        .rank-info { flex: 1; }
        .rank-name { font-weight: 600; font-size: 0.9rem; }
        .rank-meta { font-size: 0.7rem; opacity: 0.4; }
        .rank-score { font-weight: 800; font-size: 1.1rem; color: var(--gold); }
        .rank-streak { font-size: 0.75rem; opacity: 0.6; margin-left: 8px; }

        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .rank-row { animation: fadeIn 0.3s ease forwards; }

        @media (max-width: 600px) {
            .podium { gap: 8px; }
            .podium-spot { min-width: 95px; padding: 15px 10px; }
            .podium-name { font-size: 0.75rem; }
            .podium-score { font-size: 1rem; }
            .rank-row { padding: 10px 12px; }
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
            <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
            <a href="level_select.php" class="nav-link">🎮 Play</a>
            <a href="leaderboard.php" class="nav-link active">🏆 Rankings</a>
            <a href="profile.php" class="nav-link" style="border-color: rgba(255,255,255,0.15);">👤 Profile</a>
        </div>
    </nav>

    <div class="lb-container">
        <div class="lb-header">
            <h1>🏆 Leaderboard</h1>
            <p>Top learners competing for Arabic mastery</p>
        </div>

        <!-- Filter -->
        <div class="filter-row">
            <a href="?filter=all" class="filter-btn <?php echo $filter==='all'?'active':''; ?>">All Levels</a>
            <a href="?filter=beginner" class="filter-btn <?php echo $filter==='beginner'?'active':''; ?>">🌱 Beginner</a>
            <a href="?filter=intermediate" class="filter-btn <?php echo $filter==='intermediate'?'active':''; ?>">🚀 Intermediate</a>
            <a href="?filter=advanced" class="filter-btn <?php echo $filter==='advanced'?'active':''; ?>">🔥 Advanced</a>
        </div>

        <!-- Podium (Top 3) -->
        <?php if (count($players) >= 3): ?>
        <div class="podium">
            <div class="podium-spot silver">
                <div class="podium-medal">🥈</div>
                <div class="podium-name"><?php echo htmlspecialchars($players[1]['full_name'] ?: $players[1]['username']); ?></div>
                <div class="podium-score"><?php echo number_format($players[1]['total_score']); ?></div>
                <div class="podium-level"><?php echo $players[1]['selected_level']; ?></div>
            </div>
            <div class="podium-spot gold">
                <div class="podium-crown">👑</div>
                <div class="podium-medal">🥇</div>
                <div class="podium-name"><?php echo htmlspecialchars($players[0]['full_name'] ?: $players[0]['username']); ?></div>
                <div class="podium-score"><?php echo number_format($players[0]['total_score']); ?></div>
                <div class="podium-level"><?php echo $players[0]['selected_level']; ?></div>
            </div>
            <div class="podium-spot bronze">
                <div class="podium-medal">🥉</div>
                <div class="podium-name"><?php echo htmlspecialchars($players[2]['full_name'] ?: $players[2]['username']); ?></div>
                <div class="podium-score"><?php echo number_format($players[2]['total_score']); ?></div>
                <div class="podium-level"><?php echo $players[2]['selected_level']; ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- My Rank -->
        <?php if ($myRank > 0): ?>
        <div class="my-rank">
            <div class="my-rank-label">📍 Your Rank</div>
            <div class="my-rank-num">#<?php echo $myRank; ?> of <?php echo count($players); ?></div>
        </div>
        <?php endif; ?>

        <!-- Rankings List (starts from #4) -->
        <div class="rank-list">
            <?php foreach ($players as $i => $p): if ($i < 3) continue; ?>
            <div class="rank-row <?php echo ($p['id'] == $myId) ? 'me' : ''; ?>" style="animation-delay: <?php echo ($i-3)*0.05; ?>s;">
                <div class="rank-pos">#<?php echo $i + 1; ?></div>
                <div class="rank-avatar"><?php echo strtoupper(substr($p['username'], 0, 2)); ?></div>
                <div class="rank-info">
                    <div class="rank-name"><?php echo htmlspecialchars($p['full_name'] ?: $p['username']); ?></div>
                    <div class="rank-meta"><?php echo ucfirst($p['selected_level']); ?> • <?php echo $p['words_learned']; ?> words</div>
                </div>
                <div class="rank-score"><?php echo number_format($p['total_score']); ?></div>
                <div class="rank-streak">🔥<?php echo $p['daily_streak']; ?></div>
            </div>
            <?php endforeach; ?>

            <?php if (count($players) === 0): ?>
            <div style="text-align:center; padding:40px; opacity:0.4;">
                <p style="font-size:2rem;">🏆</p>
                <p>No players yet. Be the first!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
