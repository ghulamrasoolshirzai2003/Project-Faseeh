<?php
require __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

try {
    // --- AUTO-INSTALLER ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        username VARCHAR(100),
        activity_type VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Seed with a few fake ones if empty so it looks busy at first
    $count = $pdo->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
    if ($count == 0) {
        $fake = [
            [0, 'FaseehBot', 'welcome', 'Welcome to the new Faseeh Academy! 🎓'],
            [0, 'System', 'update', 'The Writing Atelier is now open for practice! ✍️']
        ];
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, username, activity_type, description) VALUES (?, ?, ?, ?)");
        foreach($fake as $f) $stmt->execute($f);
    }
    // --- END AUTO-INSTALLER ---

    // Fetch last 10 activities
    $stmt = $pdo->prepare("SELECT username, activity_type, description, created_at FROM activity_log ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($activities);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
