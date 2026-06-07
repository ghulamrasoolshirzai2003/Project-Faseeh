<?php
session_start();
require 'includes/db.php';

// 1. KICK ADMINS OUT
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php");
    exit;
}

// 2. KICK GUESTS OUT
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 3. LANGUAGE SETTING & STATS
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
$uid = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Student';

// Fetch current stats for the scoreboard
$stmt = $pdo->prepare("SELECT p.*, u.selected_level FROM progress p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
if(!$stats) {
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch();
}
if(!$stats) $stats = ['total_score'=>0, 'wins'=>0, 'losses'=>0, 'current_streak'=>0, 'daily_streak'=>0, 'longest_streak'=>0, 'xp'=>0, 'accuracy_total'=>0, 'accuracy_correct'=>0, 'attempts'=>0, 'points_lost'=>0, 'selected_level'=>'beginner'];

$currentLevel = $stats['selected_level'] ?? $_SESSION['level'] ?? 'beginner';
$_SESSION['level'] = $currentLevel;

// Check for active Hangman session
$session = null;
$hasActiveSession = false;
try {
    $stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'hangman'");
    $stmt->execute([$uid]);
    $session = $stmt->fetch();
    $hasActiveSession = ($session && $session['questions_completed'] > 0 && $session['questions_completed'] < $session['total_target']);
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Hangman</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --primary: #5E63BA;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894; --danger: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; overflow-x: hidden;
        }

        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 20px; background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border); position: sticky; top: 0; z-index: 100;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .mini-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .mini-letter { font-family: 'Amiri', serif; font-size: 17px; color: white; margin-top: -2px; }
        .mini-text { font-size: 1.1rem; font-weight: 800; color: white; margin: 0; }
        .nav-stats { display: flex; gap: 12px; font-size: 0.8rem; font-weight: 600; }
        .nav-stat { display: flex; align-items: center; gap: 3px; }
        .nav-link { background:none; border:none; cursor:pointer; color: white; text-decoration: none; font-weight: 600; font-size: 0.8rem; padding: 6px 14px; border: 1px solid var(--glass-border); border-radius: 20px; transition: 0.3s; }
        .nav-link:hover { background: white; color: var(--bg-start); }

        .game-container {
            display: grid; grid-template-columns: 200px 1fr;
            gap: 15px; padding: 15px; height: calc(100vh - 65px);
            overflow: hidden;
        }

        .sidebar {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 15px;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
        }
        .timer-box { position: relative; width: 80px; height: 80px; }
        .circular-chart { display: block; margin: 0 auto; max-width: 100%; max-height: 100%; }
        .circle-bg { fill: none; stroke: rgba(255,255,255,0.1); stroke-width: 3.8; }
        .circle { fill: none; stroke-width: 2.8; stroke: var(--gold); stroke-linecap: round; transition: stroke-dasharray 1s; }
        .circle.danger { stroke: var(--danger); }
        .time-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; font-weight: 800; }
        .hangman-img { width: 100%; max-height: 140px; filter: brightness(0) invert(1) opacity(0.8); }

        .main-area {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 15px;
            display: flex; flex-direction: column; align-items: center;
            justify-content: flex-start; overflow: hidden;
        }

        .word-slots {
            display: flex; gap: 6px; margin-bottom: 10px; direction: rtl;
            list-style: none; flex-wrap: wrap; justify-content: center;
        }
        .letter-slot {
            width: 38px; height: 44px; border-bottom: 3px solid var(--primary);
            font-family: 'Amiri', serif; font-size: 1.4rem;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .letter-slot.filled { color: var(--accent); border-color: transparent; animation: slotPop 0.3s ease; }

        .keyboard {
            display: grid; grid-template-columns: repeat(9, 1fr); gap: 8px; direction: rtl;
            max-width: 850px; width: 95%; margin-top: 10px;
        }
        .key-btn {
            padding: 12px 5px; font-family: 'Amiri', serif; font-size: 1.4rem;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; cursor: pointer; transition: 0.2s; color: white;
            min-height: 48px;
        }
        .key-btn:hover:not(:disabled) { background: var(--primary); border-color: var(--primary); transform: scale(1.05); }
        .key-btn:disabled { opacity: 0.2; cursor: not-allowed; }
        .key-btn.correct { background: rgba(0,184,148,0.3); border-color: var(--success); color: var(--success); }
        .key-btn.wrong { background: rgba(231,76,60,0.2); border-color: var(--danger); color: var(--danger); }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px);
            display: none; align-items: center; justify-content: center; z-index: 2000;
        }
        .modal-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 1px solid rgba(255,255,255,0.1); padding: 40px; border-radius: 25px;
            text-align: center; width: 90%; max-width: 450px; color: white;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .modal-title { font-size: 2rem; font-weight: 800; margin-bottom: 15px; color: var(--gold); }
        .modal-desc { opacity: 0.7; margin-bottom: 30px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 15px; }
        .modal-btn { flex: 1; padding: 15px; border-radius: 15px; font-weight: 700; cursor: pointer; border: none; transition: 0.3s; }
        .btn-stay { background: var(--gold); color: #333; }
        .btn-exit { background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); }

        .arabic-res { font-family: 'Amiri', serif; font-size: 2.5rem; color: var(--accent); display: block; margin: 10px 0; }
        
        @keyframes slotPop { 0% { transform: scale(0.8); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }

        @media (max-width: 768px) {
            .game-container { grid-template-columns: 1fr; height: auto; padding: 10px; gap: 10px; }
            .sidebar { flex-direction: row; flex-wrap: wrap; justify-content: center; padding: 15px; gap: 10px; }
            .keyboard { grid-template-columns: repeat(6, 1fr); gap: 5px; }
            .key-btn { padding: 10px 3px; font-size: 1.1rem; min-height: 48px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <button onclick="confirmExit()" class="nav-brand" style="background:none; border:none; cursor:pointer;">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </button>
        <div class="nav-stats">
            <div class="nav-stat">👤 <?php echo htmlspecialchars($username); ?></div>
            <div class="nav-stat" style="color:var(--gold);">⭐ <span id="score-val"><?php echo intval($stats['total_score'] ?? 0); ?></span></div>
            <div class="nav-stat" style="color:var(--accent);">🔥 <span id="streak-val"><?php echo intval($stats['current_streak'] ?? 0); ?></span></div>
        </div>
        <button onclick="confirmExit()" class="nav-link">Exit Game</button>
    </nav>

    <div class="game-container">
        <div class="sidebar">
            <div class="timer-box">
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="circle" id="timer-circle" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="time-text" id="timer">30</div>
            </div>
            <img src="assets/images/hangman-0.svg" id="hangman-img" class="hangman-img" alt="Hangman">
            <div class="error-text">Errors: <span id="wrong-count">0</span>/6</div>
        </div>

        <div class="main-area">
            <div style="background: rgba(255,255,255,0.03); padding: 8px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: center; gap: 25px; border: 1px solid var(--glass-border); width: auto;">
                <span style="background: var(--accent); color: #333; padding: 2px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;"><?php echo $_SESSION['level'] ?? 'Beginner'; ?></span>
                <span style="font-size: 0.8rem; font-weight: 500;">⭐ <strong id="ui-score" style="color:var(--gold);"><?php echo intval($stats['total_score'] ?? 0); ?></strong></span>
                <span style="font-size: 0.8rem; font-weight: 500;">✅ <strong id="ui-wins" style="color: var(--success);"><?php echo intval($stats['wins'] ?? 0); ?></strong></span>
            </div>

            <h2 id="hint-display" class="hint-text" style="font-size: 1.1rem; opacity: 0.8; margin-bottom: 15px;">Loading...</h2>
            <button class="nav-link" id="speak-btn" onclick="speakCurrentWord()" style="margin-bottom: 15px; display:none;">🔊 Listen</button>
            <ul id="word-display" class="word-slots"></ul>
            <div id="keyboard" class="keyboard"></div>
        </div>
    </div>

    <!-- EXIT CONFIRMATION MODAL -->
    <div id="exitModal" class="modal-overlay">
        <div class="modal-card">
            <div style="font-size: 3rem; margin-bottom: 10px;">🎯</div>
            <div class="modal-title">Stopping so soon?</div>
            <div class="modal-desc">
                Your progress is already saved, <?= htmlspecialchars($username) ?>. Finish the word to claim your bonus XP, or head back to the hub!
            </div>
            <div class="modal-actions">
                <button class="modal-btn btn-exit" onclick="location.href='level_select.php'">Exit Game</button>
                <button class="modal-btn btn-stay" onclick="document.getElementById('exitModal').style.display='none'; gameActive=true;">Keep Playing</button>
            </div>
        </div>
    </div>

    <!-- RESULT MODAL -->
    <div id="result-modal" class="modal-overlay">
        <div class="modal-card">
            <h2 id="modal-title" class="modal-title">TITLE</h2>
            <span id="final-word" class="arabic-res"></span>
            <p style="margin-bottom: 20px;">Root: <span id="final-root" style="font-family:'Amiri'; color:var(--accent);"></span></p>
            <div class="modal-actions">
                <button onclick="initGame()" class="modal-btn btn-stay">Next Word ➔</button>
                <a href="level_select.php" class="modal-btn btn-exit" style="text-decoration:none;">To Hub</a>
            </div>
        </div>
    </div>

    <script>
        const sounds = {
            correct: new Audio('assets/sounds/correct.mp3'),
            wrong:   new Audio('assets/sounds/wrong.mp3'),
            win:     new Audio('assets/sounds/win.mp3'),
            lose:    new Audio('assets/sounds/lose.mp3')
        };
        const playSound = (name) => { const s = sounds[name]; if (s) { s.currentTime = 0; s.play().catch(()=>{}); } };

        const arabicLetters = ["ا", "ب", "ت", "ث", "ج", "ح", "خ", "د", "ذ", "ر", "ز", "س", "ش", "ص", "ض", "ط", "ظ", "ع", "غ", "ف", "ق", "ك", "ل", "م", "ن", "ه", "ة", "و", "ي", "ء", "ئ", "ؤ", "ى", "أ", "إ", "آ"];
        let currentWord, currentWordID, timer;
        let isGameOver = false, gameActive = true;
        let timeLeft = 30, wrongCount = 0, correctLetters = [];

        function confirmExit() { gameActive = false; document.getElementById('exitModal').style.display = 'flex'; }

        async function initGame() {
            clearInterval(timer);
            timeLeft = 30; wrongCount = 0; correctLetters = [];
            document.getElementById('result-modal').style.display = 'none';
            document.getElementById('hangman-img').src = `assets/images/hangman-0.svg`;
            document.getElementById('wrong-count').innerText = 0;
            document.getElementById('word-display').innerHTML = ""; 
            document.getElementById('keyboard').innerHTML = "";
            isGameOver = false; gameActive = true;

            try {
                const res = await fetch('api/get_word.php');
                const data = await res.json();
                if(data.completed) {
                    document.getElementById('hint-display').innerText = "🎉 LEVEL COMPLETE!";
                    return;
                }
                currentWord = data.arabic_word;
                currentWordID = data.id; 
                document.getElementById('hint-display').innerText = `Translate: "${data.meaning}"`;
                document.getElementById('final-word').innerText = currentWord;
                document.getElementById('final-root').innerText = data.root;

                document.getElementById('word-display').innerHTML = currentWord.split("").map(char => {
                    return char === " " ? `<li class="letter-slot filled" style="border:none;"> </li>` : `<li class="letter-slot"></li>`;
                }).join("");

                arabicLetters.forEach(char => {
                    const btn = document.createElement("button");
                    btn.innerText = char; btn.className = "key-btn";
                    btn.onclick = () => handleGuess(btn, char);
                    document.getElementById('keyboard').appendChild(btn);
                });
                startTimer();
            } catch (e) { console.error(e); }
        }

        function handleGuess(btn, char) {
            if(!gameActive || isGameOver) return;
            btn.disabled = true;
            if (currentWord.includes(char)) {
                playSound('correct'); btn.classList.add('correct');
                [...currentWord].forEach((val, index) => {
                    if (val === char) {
                        correctLetters.push(val);
                        const slot = document.getElementById('word-display').querySelectorAll("li")[index];
                        if(slot) { slot.innerText = val; slot.classList.add("filled"); }
                    }
                });
                const cleanWord = [...new Set(currentWord.replace(/ /g, ''))];
                const cleanGuess = [...new Set(correctLetters)];
                if (cleanWord.length === cleanGuess.length) endGame(true);
            } else {
                playSound('wrong'); btn.classList.add('wrong');
                wrongCount++;
                if(wrongCount <= 6) document.getElementById('hangman-img').src = `assets/images/hangman-${wrongCount}.svg`;
                document.getElementById('wrong-count').innerText = wrongCount;
                if (wrongCount >= 6) endGame(false);
            }
        }

        function startTimer() {
            timer = setInterval(() => {
                if(!gameActive || isGameOver) return;
                timeLeft--;
                document.getElementById('timer').innerText = timeLeft;
                document.getElementById('timer-circle').style.strokeDasharray = `${(timeLeft/30)*100}, 100`;
                if(timeLeft <= 0) endGame(false);
            }, 1000);
        }

        async function endGame(win) {
            clearInterval(timer); isGameOver = true;
            playSound(win ? 'win' : 'lose');
            document.getElementById("modal-title").innerText = win ? "MUMTAZ! 🎉" : "Game Over 😔";
            document.getElementById("modal-title").style.color = win ? "var(--success)" : "var(--danger)";
            document.getElementById('result-modal').style.display = 'flex';

            fetch('api/update_progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ result: win ? 'win' : 'lose', word_id: win ? (currentWordID || 0) : 0, time_taken: 30-timeLeft })
            }).then(r => r.json()).then(res => {
                if(res.status === 'success') {
                    document.getElementById("ui-score").innerText = res.total_score;
                    document.getElementById("score-val").innerText = res.total_score;
                }
            });
        }

        function speakCurrentWord() { if(window.responsiveVoice && currentWord) responsiveVoice.speak(currentWord, "Arabic Male"); }
        initGame();
    </script>
</body>
</html>