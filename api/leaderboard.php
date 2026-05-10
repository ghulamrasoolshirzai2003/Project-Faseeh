<?php
session_start();
// Enable error reporting to catch hidden database bugs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/db.php';

try {
    // LEFT JOIN ensures every student shows up even if they haven't played yet.
    // COALESCE shows '0' instead of a blank space for new players.
    $sql = "SELECT u.username, u.full_name, 
                   COALESCE(p.total_score, 0) as total_score, 
                   COALESCE(p.current_streak, 0) as current_streak, 
                   u.selected_level
            FROM users u 
            LEFT JOIN progress p ON u.id = p.user_id 
            WHERE u.role != 'admin' 
            ORDER BY total_score DESC LIMIT 10";
            
    $stmt = $pdo->query($sql);
    $top_players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='color:white; background:#e74c3c; padding:20px; border-radius:10px; font-family:sans-serif;'>
            <strong>Database Error:</strong> " . $e->getMessage() . "
         </div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🏆 Global Champions - Leaderboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        .leaderboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 30px; width: 100%; max-width: 600px;
            color: white; box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .rank-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px; border-radius: 15px; margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.05); transition: 0.3s;
        }
        /* Top 3 Podium Highlighting */
        .gold { border: 2px solid #FFD700; background: rgba(255, 215, 0, 0.1); }
        .silver { border: 2px solid #C0C0C0; background: rgba(192, 192, 192, 0.1); }
        .bronze { border: 2px solid #CD7F32; background: rgba(205, 127, 50, 0.1); }
        
        .rank-num { font-size: 1.5rem; font-weight: 800; width: 40px; }
        .player-info { flex-grow: 1; margin-left: 15px; }
        .player-name { font-weight: 700; font-size: 1.1rem; display: block; }
        .player-score { font-size: 1.4rem; font-weight: 800; color: #FFD700; }
    </style>
</head>
<body class="center-screen">
    <div class="leaderboard-card">
        <h1 style="text-align:center; margin-bottom: 30px; font-weight: 800;">🏆 Global Champions</h1>
        
        <?php if (!empty($top_players)): ?>
            <?php 
            $rank = 1;
            foreach($top_players as $player): 
                $podium = ($rank == 1) ? 'gold' : (($rank == 2) ? 'silver' : (($rank == 3) ? 'bronze' : ''));
            ?>
            <div class="rank-row <?php echo $podium; ?>">
                <div class="rank-num">#<?php echo $rank; ?></div>
                <div class="player-info">
                    <span class="player-name"><?php echo htmlspecialchars($player['full_name'] ?: $player['username']); ?></span>
                    <span style="font-size: 0.8rem; opacity: 0.7; text-transform: uppercase;">
                        <?php echo htmlspecialchars($player['selected_level'] ?: 'Beginner'); ?> • 🔥 <?php echo $player['current_streak']; ?> Streak
                    </span>
                </div>
                <div class="player-score"><?php echo number_format($player['total_score']); ?></div>
            </div>
            <?php $rank++; endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; opacity: 0.6;">No players found. Register and play to see the board!</p>
        <?php endif; ?>

        <div style="text-align:center; margin-top: 30px;">
            <a href="level_select.php" class="btn-gold" style="text-decoration:none; display:inline-block; padding: 12px 35px;">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>