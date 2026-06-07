<?php
session_start();
require 'includes/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$msg = "";

// =========================================================
// 1. EXPORT TO EXCEL (CSV) LOGIC
// =========================================================
if (isset($_POST['export_excel'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=faseeh_report_' . date('Y-m-d_H-i') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Rank', 'Full Name', 'Username', 'Level', 'Score', 'XP', 'Streak', 'Wins', 'Losses', 'MCQ Wins', 'Accuracy', 'Last Active']);
    
    $sql = "SELECT u.full_name, u.username, u.selected_level, 
                   COALESCE(p.total_score, 0) as total_score, 
                   COALESCE(p.xp, 0) as xp,
                   COALESCE(p.daily_streak, 0) as daily_streak,
                   COALESCE(p.wins, 0) as wins, 
                   COALESCE(p.losses, 0) as losses,
                   COALESCE(p.mcq_wins, 0) as mcq_wins,
                   COALESCE(p.accuracy_total, 0) as acc_total,
                   COALESCE(p.accuracy_correct, 0) as acc_correct,
                   p.last_active
            FROM users u 
            LEFT JOIN progress p ON u.id = p.user_id 
            WHERE u.role != 'admin' 
            ORDER BY total_score DESC";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $rank = 1;
    foreach ($rows as $row) {
        $acc = $row['acc_total'] > 0 ? round(($row['acc_correct'] / $row['acc_total']) * 100) . '%' : 'N/A';
        fputcsv($output, [
            $rank++, $row['full_name'], $row['username'], $row['selected_level'],
            $row['total_score'], $row['xp'], $row['daily_streak'],
            $row['wins'], $row['losses'], $row['mcq_wins'], $acc, $row['last_active']
        ]);
    }
    fclose($output);
    exit();
}

// =========================================================
// 2. RESET LOGIC
// =========================================================
if (isset($_POST['reset_competition'])) {
    try {
        $pdo->exec("UPDATE progress SET total_score = 0, xp = 0, current_streak = 0, daily_streak = 0, longest_streak = 0, wins = 0, losses = 0, mcq_wins = 0, mcq_losses = 0, accuracy_total = 0, accuracy_correct = 0, points_lost = 0, last_active = '2000-01-01 00:00:00'");
        $pdo->exec("DELETE FROM user_solved_words");
        $pdo->exec("DELETE FROM user_xp_history");
        $pdo->exec("DELETE FROM game_sessions");
        $pdo->exec("DELETE FROM daily_goals");
        $pdo->exec("DELETE FROM review_queue");
        $pdo->exec("DELETE FROM user_achievements");
        $msg = "✅ Competition Reset Successfully!";
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// =========================================================
// 3. FETCH DISPLAY DATA
// =========================================================
try {
    $sql = "SELECT u.username, u.full_name, u.role, u.selected_level, 
                   COALESCE(p.total_score, 0) as total_score,
                   COALESCE(p.xp, 0) as xp,
                   COALESCE(p.current_streak, 0) as current_streak, 
                   COALESCE(p.daily_streak, 0) as daily_streak,
                   COALESCE(p.wins, 0) as wins, 
                   COALESCE(p.losses, 0) as losses,
                   COALESCE(p.mcq_wins, 0) as mcq_wins,
                   COALESCE(p.points_lost, 0) as points_lost,
                   COALESCE(p.accuracy_total, 0) as acc_total,
                   COALESCE(p.accuracy_correct, 0) as acc_correct,
                   IF(p.last_active > DATE_SUB(NOW(), INTERVAL 2 MINUTE), 1, 0) as is_online
            FROM users u 
            LEFT JOIN progress p ON u.id = p.user_id 
            WHERE u.role != 'admin' 
            ORDER BY total_score DESC"; 
            
    $rankings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading data: " . $e->getMessage());
}

// Summary stats
$totalUsers = count($rankings);
$totalGames = 0;
$todayActive = 0;
try {
    $totalGames = $pdo->query("SELECT COUNT(*) FROM game_sessions")->fetchColumn();
    $todayActive = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM game_sessions WHERE DATE(played_at) = CURDATE()")->fetchColumn();
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Faseeh</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894; --danger: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh;
        }

        .navbar {
            width: 100%; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 100;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .mini-icon { width: 45px; height: 45px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .mini-letter { font-family: 'Amiri', serif; font-size: 22px; color: white; margin-top: -3px; }
        .nav-title { display: flex; flex-direction: column; }
        .mini-text { font-size: 1.4rem; font-weight: 800; color: white; margin: 0; line-height: 1; }
        .sub-text { font-size: 0.65rem; opacity: 0.5; text-transform: uppercase; letter-spacing: 2px; }
        .nav-logout {
            color: white; text-decoration: none; font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2); padding: 8px 25px;
            border-radius: 30px; font-size: 0.85rem; transition: 0.3s;
            background: rgba(255,255,255,0.05);
        }
        .nav-logout:hover { background: white; color: var(--bg-start); }

        .admin-wrap { padding: 100px 25px 40px; max-width: 1300px; margin: 0 auto; }

        /* Summary Cards */
        .summary-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .summary-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 15px; padding: 20px; text-align: center;
        }
        .summary-val { font-size: 2rem; font-weight: 800; }
        .summary-val.gold { color: var(--gold); }
        .summary-val.green { color: var(--success); }
        .summary-val.accent { color: var(--accent); }
        .summary-label { font-size: 0.7rem; opacity: 0.4; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }

        /* Charts */
        .charts-row { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
        .chart-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 25px;
        }
        .chart-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.4; margin-bottom: 15px; font-weight: 600; }

        /* Action bar */
        .action-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--glass-border);
        }
        .action-bar h2 { font-size: 1.3rem; font-weight: 700; }
        .action-btns { display: flex; gap: 10px; }
        .action-btn {
            border: none; cursor: pointer; padding: 10px 22px; border-radius: 25px;
            font-weight: 700; font-size: 0.8rem; transition: 0.3s; font-family: 'Poppins', sans-serif;
        }
        .action-btn.export { background: var(--success); color: white; }
        .action-btn.export:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,184,148,0.4); }
        .action-btn.reset { background: var(--danger); color: white; }
        .action-btn.reset:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(231,76,60,0.4); }

        /* Table */
        .table-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
        th {
            background: rgba(94,99,186,0.3); color: white; padding: 12px 10px;
            text-align: center; font-weight: 600; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 1px;
        }
        th:first-child { border-radius: 8px 0 0 8px; }
        th:last-child { border-radius: 0 8px 8px 0; }
        tr.data-row { transition: 0.2s; }
        tr.data-row:hover { transform: translateX(3px); }
        td {
            padding: 12px 10px; text-align: center; font-size: 0.85rem;
            background: rgba(255,255,255,0.02); border: none;
        }
        td:first-child { border-radius: 8px 0 0 8px; }
        td:last-child { border-radius: 0 8px 8px 0; }
        .status-badge {
            padding: 4px 12px; border-radius: 15px; font-size: 0.7rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .status-badge.online { background: rgba(0,184,148,0.15); color: var(--success); }
        .status-badge.offline { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

        .msg-banner {
            background: rgba(0,184,148,0.15); border: 1px solid rgba(0,184,148,0.3);
            padding: 12px 20px; border-radius: 12px; margin-bottom: 15px; text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 900px) {
            .summary-row { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .admin-wrap { padding: 85px 12px 30px; }
            .summary-row { grid-template-columns: 1fr 1fr; gap: 8px; }
            .action-bar { flex-direction: column; gap: 10px; }
            th, td { padding: 8px 6px; font-size: 0.7rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <div class="nav-title">
                <h1 class="mini-text">Faseeh</h1>
                <span class="sub-text">Admin Portal</span>
            </div>
        </div>
        <a href="admin_login.php?logout=true" class="nav-logout">Logout</a>
    </nav>

    <div class="admin-wrap">

        <!-- Summary Stats -->
        <div class="summary-row">
            <div class="summary-card">
                <div class="summary-val accent"><?php echo $totalUsers; ?></div>
                <div class="summary-label">Total Students</div>
            </div>
            <div class="summary-card">
                <div class="summary-val gold"><?php echo number_format($totalGames); ?></div>
                <div class="summary-label">Total Games Played</div>
            </div>
            <div class="summary-card">
                <div class="summary-val green"><?php echo $todayActive; ?></div>
                <div class="summary-label">Active Today</div>
            </div>
            <div class="summary-card">
                <div class="summary-val" style="color: #a29bfe;">
                    <?php 
                    $avgScore = 0;
                    if ($totalUsers > 0) {
                        $sum = 0;
                        foreach($rankings as $r) $sum += $r['total_score'];
                        $avgScore = round($sum / $totalUsers);
                    }
                    echo $avgScore;
                    ?>
                </div>
                <div class="summary-label">Avg Score</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">📈 Daily Active Users (Last 30 Days)</div>
                <canvas id="dauChart" height="120"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">📊 Level Distribution</div>
                <canvas id="levelChart" height="200"></canvas>
            </div>
        </div>

        <!-- Rankings Table -->
        <div class="table-card">
            <div class="action-bar">
                <h2>📊 Student Rankings</h2>
                <div class="action-btns">
                    <form method="POST" style="display:inline;">
                        <button name="export_excel" class="action-btn export">📥 Export CSV</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ This will reset ALL progress. Are you sure?');">
                        <button name="reset_competition" class="action-btn reset">🗑️ Reset All</button>
                    </form>
                </div>
            </div>

            <?php if($msg) echo "<div class='msg-banner'>$msg</div>"; ?>

            <table>
                <thead>
                    <tr>
                        <th>Rank</th><th>Name</th><th>Level</th><th>Score</th><th>XP</th>
                        <th>Streak</th><th>Wins</th><th>MCQ</th><th>Accuracy</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($rankings) > 0): $i=1; foreach($rankings as $r): 
                        $acc = $r['acc_total'] > 0 ? round(($r['acc_correct'] / $r['acc_total']) * 100) . '%' : '—';
                    ?>
                    <tr class="data-row">
                        <td style="font-weight:800; color:<?php echo $i<=3 ? 'var(--gold)' : 'rgba(255,255,255,0.4)'; ?>;">
                            <?php echo $i <= 3 ? ['🥇','🥈','🥉'][$i-1] : '#'.$i; ?>
                        </td>
                        <td style="font-weight:600; text-align:left; padding-left:15px;"><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td><span style="background:rgba(255,255,255,0.08); padding:4px 12px; border-radius:12px; font-size:0.75rem;"><?php echo ucfirst($r['selected_level']); ?></span></td>
                        <td style="font-weight:800; color:var(--gold);"><?php echo $r['total_score']; ?></td>
                        <td style="color:var(--accent);"><?php echo $r['xp']; ?></td>
                        <td>🔥 <?php echo $r['daily_streak']; ?></td>
                        <td style="color:var(--success);"><?php echo $r['wins']; ?></td>
                        <td><?php echo $r['mcq_wins']; ?></td>
                        <td><?php echo $acc; ?></td>
                        <td>
                            <?php if ($r['is_online'] == 1): ?>
                                <span class="status-badge online"><span class="status-dot" style="background:var(--success);"></span> Online</span>
                            <?php else: ?>
                                <span class="status-badge offline"><span class="status-dot" style="background:rgba(255,255,255,0.3);"></span> Offline</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $i++; endforeach; else: ?>
                    <tr><td colspan="10" style="padding:40px; opacity:0.4; font-style:italic;">No students have joined yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Load analytics data for charts
    fetch('api/get_analytics.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) return;

            // DAU Chart
            const dauCtx = document.getElementById('dauChart').getContext('2d');
            new Chart(dauCtx, {
                type: 'line',
                data: {
                    labels: (data.dau || []).map(d => d.date),
                    datasets: [{
                        label: 'Active Users',
                        data: (data.dau || []).map(d => d.users),
                        borderColor: '#f2994a',
                        backgroundColor: 'rgba(242, 153, 74, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#f2994a'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: 'rgba(255,255,255,0.3)', maxTicksLimit: 7 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
                    }
                }
            });

            // Level Distribution
            const levelCtx = document.getElementById('levelChart').getContext('2d');
            const levelData = data.level_distribution || [];
            new Chart(levelCtx, {
                type: 'doughnut',
                data: {
                    labels: levelData.map(d => d.level || 'Unknown'),
                    datasets: [{
                        data: levelData.map(d => d.count),
                        backgroundColor: ['#00b894', '#f2994a', '#e74c3c', '#a29bfe'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.6)', padding: 15 } }
                    }
                }
            });
        })
        .catch(() => {});

    // Auto-refresh every 15 seconds
    setTimeout(() => window.location.reload(), 15000);
    </script>
</body>
</html>