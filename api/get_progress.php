<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$range   = $_GET['range'] ?? 'weekly';

if (!$user_id) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    // 1. Ensure table exists (Self-Heal)
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_xp_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        xp_gained INT NOT NULL,
        progress_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $today = new DateTime();
    if ($range === 'monthly') {
        $start = (clone $today)->modify('-30 days')->format('Y-m-d');
        $endDate = $today->format('Y-m-d');
    } else { // Rolling 7 Days
        $start = (clone $today)->modify('-6 days')->format('Y-m-d');
        $endDate = $today->format('Y-m-d');
    }
    
    $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), (new DateTime($endDate))->modify('+1 day'));

    // 2. Fetch data
    $stmt = $pdo->prepare("
        SELECT progress_date, SUM(xp_gained) as daily_xp 
        FROM user_xp_history 
        WHERE user_id = ? AND progress_date >= ? AND progress_date <= ?
        GROUP BY progress_date
    ");
    $stmt->execute([$user_id, $start, $endDate]);
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $labels = [];
    $xpData = [];

    foreach ($period as $dt) {
        $dateStr = $dt->format('Y-m-d');
        $labels[] = $dt->format('D'); // 'Mon', 'Tue', etc.
        $xpData[] = isset($results[$dateStr]) ? (int)$results[$dateStr] : 0;
    }

    echo json_encode(['labels' => $labels, 'xp' => $xpData]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
