<?php
session_start();
header('Content-Type: application/json');
require 'includes/db.php';
require_once 'includes/progress.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$uid = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// --- SELF-HEALING DB TABLES ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS majlis_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255),
        scenario VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS majlis_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        role ENUM('user', 'ai'),
        content TEXT,
        hint TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(Exception $e) {}

// --- ACTIONS ---

// 1. GET USER STATS FOR PROMPT
if ($action === 'get_ai_context') {
    try {
        $stmt = $pdo->prepare("SELECT xp, total_score, wins FROM progress WHERE user_id = ?");
        $stmt->execute([$uid]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as words FROM user_solved_words WHERE user_id = ?");
        $stmt->execute([$uid]);
        $words = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'username' => $_SESSION['username'] ?? 'Student',
            'xp' => $stats['xp'] ?? 0,
            'total_score' => $stats['total_score'] ?? 0,
            'words_learned' => $words['words'] ?? 0
        ]);
    } catch(Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
    exit;
}

// 2. SAVE MESSAGE & PROGRESS
if ($action === 'save_message') {
    $chatId = $input['conversation_id'] ?? null;
    $msg = $input['message'] ?? '';
    $role = $input['role'] ?? 'user';
    $hint = $input['hint'] ?? '';
    $scenario = $input['scenario'] ?? 'market';

    try {
        // Create new conversation if needed
        if (!$chatId) {
            $stmt = $pdo->prepare("INSERT INTO majlis_conversations (user_id, title, scenario) VALUES (?, ?, ?)");
            $title = "Practice: " . ucfirst($scenario);
            $stmt->execute([$uid, $title, $scenario]);
            $chatId = $pdo->lastInsertId();
        }

        // Insert message
        $stmt = $pdo->prepare("INSERT INTO majlis_messages (conversation_id, role, content, hint) VALUES (?, ?, ?, ?)");
        $stmt->execute([$chatId, $role, $msg, $hint]);

        // If AI replied, update progress
        if ($role === 'ai') {
            saveUserProgress($pdo, $uid, 5, 'majlis', 1, 0);
        }

        echo json_encode(['success' => true, 'conversation_id' => $chatId]);
    } catch(Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
    exit;
}

// 3. GET HISTORY LIST
if ($action === 'get_history') {
    try {
        $stmt = $pdo->prepare("SELECT id, title, scenario, last_updated FROM majlis_conversations WHERE user_id = ? ORDER BY last_updated DESC LIMIT 15");
        $stmt->execute([$uid]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch(Exception $e) { echo json_encode([]); }
    exit;
}

// 4. LOAD CONVERSATION
if ($action === 'load_chat') {
    $chatId = $input['conversation_id'];
    try {
        $stmt = $pdo->prepare("SELECT role, content, hint FROM majlis_messages WHERE conversation_id = ? ORDER BY created_at ASC");
        $stmt->execute([$chatId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch(Exception $e) { echo json_encode([]); }
    exit;
}

// 5. UPDATE TITLE
if ($action === 'update_title') {
    $chatId = $input['conversation_id'];
    $title = $input['title'];
    try {
        $stmt = $pdo->prepare("UPDATE majlis_conversations SET title = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $chatId, $uid]);
        echo json_encode(['success' => true]);
    } catch(Exception $e) { echo json_encode(['error' => $e->getMessage()]); }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
?>
