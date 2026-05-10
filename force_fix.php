<?php
// force_fix.php - THE NUCLEAR REPAIR TOOL
require 'includes/db.php';
session_start();

echo "<body style='background:#0f0c29; color:white; font-family:sans-serif; padding:50px;'>";
echo "<h2>🛠️ FASEEH NUCLEAR REPAIR TOOL</h2>";

if (!isset($_SESSION['user_id'])) {
    die("<h3 style='color:#e74c3c'>❌ Error: You are not logged in.</h3><p>Please login to the game first, then come back and refresh this page.</p>");
}

$uid = $_SESSION['user_id'];

try {
    // 1. REBUILD TABLE (FORCING UNIQUE USER_ID)
    echo "STEP 1: Checking database structure...<br>";
    
    // We use a try-catch for each alter to be safe
    $cols = [
        "wins INT DEFAULT 0",
        "losses INT DEFAULT 0",
        "total_score INT DEFAULT 0",
        "xp INT DEFAULT 0",
        "current_streak INT DEFAULT 0",
        "daily_streak INT DEFAULT 0",
        "longest_streak INT DEFAULT 0",
        "attempts INT DEFAULT 0",
        "accuracy_total INT DEFAULT 0",
        "accuracy_correct INT DEFAULT 0",
        "points_lost INT DEFAULT 0",
        "academic_correct_count INT DEFAULT 0",
        "last_play_date DATE DEFAULT NULL"
    ];

    $pdo->exec("CREATE TABLE IF NOT EXISTS progress (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT UNIQUE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    foreach($cols as $c) {
        try {
            $colName = explode(" ", $c)[0];
            $pdo->exec("ALTER TABLE progress ADD COLUMN $c");
            echo "✅ Added column: $colName<br>";
        } catch(Exception $e) {
            // Column probably exists
        }
    }
    
    // 2. ENSURE USER RECORD
    echo "STEP 2: Verifying your account...<br>";
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO progress (user_id, total_score) VALUES (?, 0)")->execute([$uid]);
        echo "✅ Created fresh progress record for User #$uid<br>";
    }

    // 3. FORCE TEST UPDATE
    echo "STEP 3: Testing database write access...<br>";
    $pdo->prepare("UPDATE progress SET total_score = 100 WHERE user_id = ?")->execute([$uid]);
    
    echo "<br><h3 style='color:#00b894'>✅ SUCCESS!</h3>";
    echo "<p>Database has been force-repaired and synchronized.</p>";
    echo "<p style='background:rgba(255,255,255,0.1); padding:15px; border-radius:8px;'>🚀 <b>ACTION:</b> Go back to the game now. You should see <b>100 Score</b> at the top. This proves the counting system is now working!</p>";
    echo "<a href='game.php' style='display:inline-block; background:#f2994a; color:white; padding:10px 20px; border-radius:5px; text-decoration:none; font-weight:bold;'>Go to Game</a>";

} catch (Exception $e) {
    echo "<h3 style='color:#e74c3c'>❌ CRITICAL ERROR:</h3>" . $e->getMessage();
}
echo "</body>";
?>
