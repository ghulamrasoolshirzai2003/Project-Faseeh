<?php
// vocabulary_bank.php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];

// Populate review_queue from user_solved_words to sync existing history
try {
    $pdo->exec("INSERT IGNORE INTO review_queue (user_id, word_id, next_review) 
                SELECT user_id, word_id, CURDATE() FROM user_solved_words WHERE user_id = $userId AND word_id IS NOT NULL");
} catch(Exception $e){}

// Fetch learned vocabulary details
$stmt = $pdo->prepare("
    SELECT w.*, s.next_review, s.interval_days 
    FROM review_queue s
    JOIN words w ON s.word_id = w.id
    WHERE s.user_id = ?
");
$stmt->execute([$userId]);
$learned = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate reviews due
$dueCount = 0;
$now = date('Y-m-d H:i:s');
foreach ($learned as $w) {
    if ($w['next_review'] <= $now) {
        $dueCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spaced Repetition (SRS) Bank | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light));
            color: white; min-height: 100vh; padding: 100px 20px 40px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-weight: 800; font-size: 2.2rem; }
        
        .due-badge { background: var(--accent); color: #000; padding: 5px 15px; border-radius: 50px; font-weight: 800; font-size: 0.9rem; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; backdrop-filter: blur(10px); position: relative; }
        .card .arabic { font-family: 'Amiri', serif; font-size: 2.5rem; text-align: right; margin-bottom: 10px; color: var(--accent); }
        .card .meaning { font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; }
        .card .meta { font-size: 0.8rem; opacity: 0.6; display: flex; justify-content: space-between; }

        .btn-review { background: var(--accent); border: none; color: black; padding: 12px 30px; border-radius: 12px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; cursor: pointer; }
        .btn-review:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(242,153,74,0.3); }

        /* Review Modal / Screen Overlay */
        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
        .modal { background: #1e1b4b; border: 2px solid var(--glass-border); border-radius: 25px; padding: 40px; max-width: 500px; width: 90%; text-align: center; }
        .modal .ar { font-family: 'Amiri', serif; font-size: 4rem; margin-bottom: 20px; color: var(--accent); }
        .modal .hint { font-size: 1.2rem; display: none; margin-bottom: 30px; }
        .modal-actions { display: flex; justify-content: center; gap: 15px; }
        .btn-show { background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); color: white; padding: 10px 25px; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .btn-score { padding: 10px 25px; border-radius: 10px; cursor: pointer; border: none; font-weight: 800; }
    </style>
</head>
<body>
    <?php require 'includes/navbar.php'; ?>

    <div class="container">
        <div class="header">
            <div>
                <h1>Spaced Repetition Bank</h1>
                <p style="opacity: 0.7; margin-top: 5px;">Your personal AI vocabulary learning scheduler</p>
            </div>
            <?php if ($dueCount > 0): ?>
                <button class="btn-review" onclick="startSRSReview()">
                    🧠 Review Due Now <span class="due-badge"><?= $dueCount ?></span>
                </button>
            <?php else: ?>
                <div class="due-badge" style="background: var(--success); color: white;">🎉 All caught up!</div>
            <?php endif; ?>
        </div>

        <div class="grid">
            <?php foreach($learned as $w): 
                $isDue = $w['next_review'] <= $now;
            ?>
            <div class="card" style="border-color: <?= $isDue ? 'var(--accent)' : 'var(--glass-border)' ?>;">
                <div class="arabic"><?= htmlspecialchars($w['arabic_word'] ?? '') ?></div>
                <div class="meaning"><?= htmlspecialchars($w['meaning'] ?? '') ?></div>
                <div class="meta">
                    <span>Interval: <?= $w['interval_days'] ?>d</span>
                    <span>Status: <?= $isDue ? '🔴 Review Due' : '🟢 Learned' ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Review Overlay -->
    <div id="reviewOverlay" class="overlay">
        <div class="modal">
            <h2 style="margin-bottom: 20px; opacity: 0.7;">Flashcard Review</h2>
            <div id="card-ar" class="ar"></div>
            <div id="card-meaning" class="hint"></div>
            
            <div id="show-phase">
                <button class="btn-show" onclick="revealCard()">Reveal Translation</button>
            </div>
            <div id="score-phase" style="display: none;" class="modal-actions">
                <button class="btn-score" style="background: var(--danger); color: white;" onclick="scoreCard(false)">❌ Forgot</button>
                <button class="btn-score" style="background: var(--success); color: white;" onclick="scoreCard(true)">✅ Got it!</button>
            </div>
        </div>
    </div>

    <script>
        let dueCards = [];
        let currentIndex = 0;

        async function startSRSReview() {
            const res = await fetch('api/get_srs_due.php');
            dueCards = await res.json();
            if (dueCards.length === 0) {
                alert("No cards due for review!");
                return;
            }
            currentIndex = 0;
            document.getElementById('reviewOverlay').style.display = 'flex';
            showNextCard();
        }

        function showNextCard() {
            if (currentIndex >= dueCards.length) {
                alert("🎉 Excellent! SRS review complete!");
                location.reload();
                return;
            }
            const card = dueCards[currentIndex];
            document.getElementById('card-ar').innerText = card.arabic_word;
            document.getElementById('card-meaning').innerText = card.meaning;
            document.getElementById('card-meaning').style.display = 'none';
            document.getElementById('show-phase').style.display = 'block';
            document.getElementById('score-phase').style.display = 'none';
        }

        function revealCard() {
            document.getElementById('card-meaning').style.display = 'block';
            document.getElementById('show-phase').style.display = 'none';
            document.getElementById('score-phase').style.display = 'flex';
        }

        async function scoreCard(correct) {
            const card = dueCards[currentIndex];
            await fetch('api/submit_srs.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ word_id: card.word_id, correct: correct })
            });
            currentIndex++;
            showNextCard();
        }
    </script>
</body>
</html>
