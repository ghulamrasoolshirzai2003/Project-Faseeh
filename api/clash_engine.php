<?php
// api/clash_engine.php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';

// --- SELF-HEALING DB TABLES ---
try {
    // 1. Clash Rooms
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        room_pin VARCHAR(10) NOT NULL UNIQUE,
        status ENUM('lobby', 'active', 'show_answer', 'finished') DEFAULT 'lobby',
        current_question_index INT DEFAULT 0,
        question_set VARCHAR(50) DEFAULT 'beginner',
        timer_started_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Clash Players
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        user_id INT NULL,
        nickname VARCHAR(50) NOT NULL,
        score INT DEFAULT 0,
        streak INT DEFAULT 0,
        last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // 3. Clash Answers
    $pdo->exec("CREATE TABLE IF NOT EXISTS clash_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        player_id INT NOT NULL,
        question_index INT NOT NULL,
        is_correct TINYINT DEFAULT 0,
        time_taken FLOAT DEFAULT 0,
        score_awarded INT DEFAULT 0,
        choice VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_player_q (room_id, player_id, question_index)
    )");
} catch (Exception $e) {}

$data = json_decode(file_get_contents("php://input"), true) ?? $_REQUEST;
$action = $data['action'] ?? '';

// --- HELPER TO GET DYNAMIC MCQ OPTIONS FROM WORDS ---
function getQuestionForRoom($pdo, $level, $index) {
    // 1. Fetch 10 stable words for this clash room to maintain order
    // To keep it deterministic but fresh per room, we seed it with the room's creation ID or just limit it
    $stmt = $pdo->prepare("SELECT id, arabic_word, meaning FROM words WHERE level = ? LIMIT 10 OFFSET ?");
    $stmt->execute([$level, $index]);
    $correctWord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$correctWord) {
        return null; // Game complete!
    }

    // 2. Fetch 3 random decoy meanings from other words of the same level
    $decoyStmt = $pdo->prepare("SELECT DISTINCT meaning FROM words WHERE level = ? AND id != ? ORDER BY RAND() LIMIT 3");
    $decoyStmt->execute([$level, $correctWord['id']]);
    $decoys = $decoyStmt->fetchAll(PDO::FETCH_COLUMN);

    // Pad decoys if database has too few words
    while (count($decoys) < 3) {
        $decoys[] = "Decoy Translation " . (count($decoys) + 1);
    }

    $options = array_merge([$correctWord['meaning']], $decoys);
    shuffle($options); // Shuffle order

    return [
        "arabic_word" => $correctWord['arabic_word'],
        "correct_meaning" => $correctWord['meaning'],
        "options" => $options
    ];
}

switch ($action) {
    case 'host_room':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["error" => "Teacher authentication required"]);
            exit;
        }
        $teacherId = $_SESSION['user_id'];
        $level = $data['level'] ?? 'beginner';
        $pin = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        try {
            // Check if pin is taken (very rare)
            $check = $pdo->prepare("SELECT id FROM clash_rooms WHERE room_pin = ? AND status != 'finished'");
            $check->execute([$pin]);
            if ($check->fetch()) {
                $pin = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            }

            $stmt = $pdo->prepare("INSERT INTO clash_rooms (teacher_id, room_pin, status, question_set, current_question_index) VALUES (?, ?, 'lobby', ?, 0)");
            $stmt->execute([$teacherId, $pin, $level]);
            $roomId = $pdo->lastInsertId();

            echo json_encode(["success" => true, "room_id" => $roomId, "pin" => $pin]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'get_players':
        $roomId = $data['room_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT id, nickname, score, streak FROM clash_players WHERE room_id = ? ORDER BY score DESC");
            $stmt->execute([$roomId]);
            $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "players" => $players]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'start_room':
        $roomId = $data['room_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("UPDATE clash_rooms SET status = 'active', current_question_index = 0, timer_started_at = NOW() WHERE id = ?");
            $stmt->execute([$roomId]);
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'get_room_state':
        $roomId = $data['room_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT * FROM clash_rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(["error" => "Room not found"]);
                exit;
            }

            $question = getQuestionForRoom($pdo, $room['question_set'], $room['current_question_index']);

            // Count answers submitted for this question
            $ansStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clash_answers WHERE room_id = ? AND question_index = ?");
            $ansStmt->execute([$roomId, $room['current_question_index']]);
            $answersCount = $ansStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            // Get total players
            $playStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clash_players WHERE room_id = ?");
            $playStmt->execute([$roomId]);
            $playersCount = $playStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            // Get answer distribution (for host dashboard chart)
            $dist = [];
            if ($room['status'] == 'show_answer') {
                $distStmt = $pdo->prepare("SELECT choice, COUNT(*) as count FROM clash_answers WHERE room_id = ? AND question_index = ? GROUP BY choice");
                $distStmt->execute([$roomId, $room['current_question_index']]);
                $dist = $distStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            echo json_encode([
                "success" => true,
                "status" => $room['status'],
                "current_question_index" => (int)$room['current_question_index'],
                "question" => $question,
                "answers_count" => (int)$answersCount,
                "players_count" => (int)$playersCount,
                "distribution" => $dist
            ]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'show_answer':
        $roomId = $data['room_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("UPDATE clash_rooms SET status = 'show_answer' WHERE id = ?");
            $stmt->execute([$roomId]);
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'next_question':
        $roomId = $data['room_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT current_question_index, question_set FROM clash_rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextIdx = $room['current_question_index'] + 1;
            // Check if there is a next question
            $nextQ = getQuestionForRoom($pdo, $room['question_set'], $nextIdx);

            if ($nextQ) {
                $update = $pdo->prepare("UPDATE clash_rooms SET status = 'active', current_question_index = ?, timer_started_at = NOW() WHERE id = ?");
                $update->execute([$nextIdx, $roomId]);
                echo json_encode(["success" => true, "finished" => false]);
            } else {
                $update = $pdo->prepare("UPDATE clash_rooms SET status = 'finished' WHERE id = ?");
                $update->execute([$roomId]);
                echo json_encode(["success" => true, "finished" => true]);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'join_room':
        $pin = trim($data['pin'] ?? '');
        $nickname = strip_tags(trim($data['nickname'] ?? ''));

        if (!$pin || !$nickname) {
            echo json_encode(["error" => "PIN and Nickname are required"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM clash_rooms WHERE room_pin = ? AND status != 'finished'");
            $stmt->execute([$pin]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(["error" => "No active Class Clash found with that PIN!"]);
                exit;
            }

            // Check if nickname already taken in this room
            $check = $pdo->prepare("SELECT id FROM clash_players WHERE room_id = ? AND nickname = ?");
            $check->execute([$room['id'], $nickname]);
            if ($check->fetch()) {
                echo json_encode(["error" => "Nickname already taken in this lobby"]);
                exit;
            }

            $userId = $_SESSION['user_id'] ?? null;

            $insert = $pdo->prepare("INSERT INTO clash_players (room_id, user_id, nickname, score, streak) VALUES (?, ?, ?, 0, 0)");
            $insert->execute([$room['id'], $userId, $nickname]);
            $playerId = $pdo->lastInsertId();

            $_SESSION['clash_room_id'] = $room['id'];
            $_SESSION['clash_player_id'] = $playerId;
            $_SESSION['clash_nickname'] = $nickname;

            echo json_encode([
                "success" => true,
                "room_id" => $room['id'],
                "player_id" => $playerId,
                "nickname" => $nickname
            ]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'check_student_state':
        $roomId = $_SESSION['clash_room_id'] ?? 0;
        $playerId = $_SESSION['clash_player_id'] ?? 0;

        if (!$roomId || !$playerId) {
            echo json_encode(["error" => "Not enrolled in any live clash"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM clash_rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(["error" => "Room dissolved"]);
                exit;
            }

            // Player stats
            $pStmt = $pdo->prepare("SELECT score, streak FROM clash_players WHERE id = ?");
            $pStmt->execute([$playerId]);
            $player = $pStmt->fetch(PDO::FETCH_ASSOC);

            // Active question
            $question = getQuestionForRoom($pdo, $room['question_set'], $room['current_question_index']);

            // Has player answered this question?
            $ansStmt = $pdo->prepare("SELECT choice, is_correct, score_awarded FROM clash_answers WHERE room_id = ? AND player_id = ? AND question_index = ?");
            $ansStmt->execute([$roomId, $playerId, $room['current_question_index']]);
            $answer = $ansStmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "status" => $room['status'],
                "current_question_index" => (int)$room['current_question_index'],
                "question" => $question,
                "player_score" => (int)($player['score'] ?? 0),
                "player_streak" => (int)($player['streak'] ?? 0),
                "has_answered" => $answer ? true : false,
                "is_correct" => $answer ? (bool)$answer['is_correct'] : false,
                "score_awarded" => $answer ? (int)$answer['score_awarded'] : 0
            ]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'submit_answer':
        $roomId = $_SESSION['clash_room_id'] ?? 0;
        $playerId = $_SESSION['clash_player_id'] ?? 0;
        $choice = trim($data['choice'] ?? '');
        $timeTaken = (float)($data['time_taken'] ?? 10.0);

        if (!$roomId || !$playerId || !$choice) {
            echo json_encode(["error" => "Missing credentials or choice"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM clash_rooms WHERE id = ?");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($room['status'] != 'active') {
                echo json_encode(["error" => "Timer has expired or question not active!"]);
                exit;
            }

            $question = getQuestionForRoom($pdo, $room['question_set'], $room['current_question_index']);
            $isCorrect = ($choice === $question['correct_meaning']) ? 1 : 0;

            // Score Logic: Kahoot-style!
            // Maximum base points = 1000. Reduces based on time taken (up to 20 seconds).
            // Fast answer: correct answers under 1 second get close to 1000 points.
            $scoreAwarded = 0;
            $newStreak = 0;

            $pStmt = $pdo->prepare("SELECT score, streak FROM clash_players WHERE id = ?");
            $pStmt->execute([$playerId]);
            $player = $pStmt->fetch(PDO::FETCH_ASSOC);

            if ($isCorrect) {
                $timeRatio = min(1, $timeTaken / 20.0);
                $basePoints = round(1000 * (1 - ($timeRatio / 2.0))); // min 500 base points for correct
                $newStreak = $player['streak'] + 1;
                $streakBonus = $newStreak * 100; // 100 bonus points per streak level
                $scoreAwarded = $basePoints + $streakBonus;
            } else {
                $newStreak = 0; // reset streak
            }

            // Insert into clash_answers
            $ans = $pdo->prepare("INSERT INTO clash_answers (room_id, player_id, question_index, is_correct, time_taken, score_awarded, choice) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ans->execute([$roomId, $playerId, $room['current_question_index'], $isCorrect, $timeTaken, $scoreAwarded, $choice]);

            // Update player score & streak
            $up = $pdo->prepare("UPDATE clash_players SET score = score + ?, streak = ? WHERE id = ?");
            $up->execute([$scoreAwarded, $newStreak, $playerId]);

            echo json_encode([
                "success" => true,
                "is_correct" => (bool)$isCorrect,
                "score_awarded" => $scoreAwarded,
                "new_streak" => $newStreak
            ]);
        } catch (Exception $e) {
            echo json_encode(["error" => "You have already answered this question!"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid engine action"]);
}
?>
