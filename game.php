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

// Fetch current stats for the scoreboard
$stmt = $pdo->prepare("SELECT p.*, u.selected_level FROM progress p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
if(!$stats) {
    // Fallback if JOIN failed (no user record)
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
    $stmt->execute([$uid]);
    $stats = $stmt->fetch();
}
if(!$stats) $stats = ['total_score'=>0, 'wins'=>0, 'losses'=>0, 'current_streak'=>0, 'daily_streak'=>0, 'longest_streak'=>0, 'xp'=>0, 'accuracy_total'=>0, 'accuracy_correct'=>0, 'attempts'=>0, 'points_lost'=>0, 'selected_level'=>'beginner'];

$currentLevel = $stats['selected_level'] ?? $_SESSION['level'] ?? 'beginner';
$_SESSION['level'] = $currentLevel; // Sync session with DB

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
            color: white; min-height: 100vh;
        }

        /* Navbar */
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
        .nav-link { color: white; text-decoration: none; font-weight: 600; font-size: 0.8rem; padding: 6px 14px; border: 1px solid var(--glass-border); border-radius: 20px; transition: 0.3s; }
        .nav-link:hover { background: white; color: var(--bg-start); }

        /* Game Layout */
        .game-container {
            display: grid; grid-template-columns: 200px 1fr;
            gap: 15px; padding: 15px; height: calc(100vh - 65px);
            overflow: hidden;
        }

        /* Sidebar */
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
        .time-text {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 1.5rem; font-weight: 800;
        }
        .time-text.danger { color: var(--danger); }
        .hangman-img { width: 100%; max-height: 140px; filter: brightness(0) invert(1) opacity(0.8); }
        .error-text { font-size: 0.8rem; opacity: 0.6; }

        /* Main Game Area */
        .main-area {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 20px; padding: 15px;
            display: flex; flex-direction: column; align-items: center;
            justify-content: flex-start; overflow: hidden;
        }
        .level-badge {
            background: rgba(255,255,255,0.08); padding: 3px 12px; border-radius: 20px;
            font-weight: 600; font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.1);
            text-transform: capitalize; margin-bottom: 5px;
        }
        .hint-text { font-size: 1rem; opacity: 0.7; margin-bottom: 5px; text-align: center; }
        .speak-game-btn {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            color: white; padding: 4px 12px; border-radius: 20px;
            cursor: pointer; font-size: 0.75rem; transition: 0.3s; margin-bottom: 8px;
        }
        .speak-game-btn:hover { background: rgba(255,255,255,0.1); border-color: white; color: white; transform: scale(1.05); }

        /* Toast notification */
        #game-toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 10px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #game-toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }

        /* Word Slots */
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
        @keyframes slotPop { 0% { transform: scale(0.8); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }

        /* Keyboard */
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

        /* Result Modal */
        .modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px);
            z-index: 999; display: flex; justify-content: center; align-items: center;
            visibility: hidden; opacity: 0; transition: 0.3s;
        }
        .modal.show { visibility: visible; opacity: 1; }
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 40px; border-radius: 25px; text-align: center;
            width: 420px; max-width: 90%; color: white;
        }
        .modal.show .modal-content { animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .modal:not(.show) .modal-content { display: none; }
        @keyframes modalPop { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .arabic-res { font-family: 'Amiri', serif; font-size: 2.5rem; color: var(--accent); display: block; margin: 10px 0; }
        .btn-gold {
            background: linear-gradient(45deg, var(--gold), #FDB931);
            border: none; padding: 14px 35px; border-radius: 50px;
            font-weight: 700; cursor: pointer; color: #333; font-size: 1rem;
            transition: 0.3s; margin: 5px;
        }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(255,215,0,0.4); }
        .btn-outline {
            background: transparent; border: 2px solid rgba(255,255,255,0.2);
            padding: 12px 30px; border-radius: 50px; color: white;
            font-weight: 600; cursor: pointer; transition: 0.3s; margin: 5px;
            text-decoration: none; display: inline-block; font-size: 0.9rem;
        }
        .btn-outline:hover { border-color: white; background: rgba(255,255,255,0.1); }

        /* Achievement Toast */
        .ach-toast {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            background: rgba(255,215,0,0.12); border: 1px solid rgba(255,215,0,0.3);
            backdrop-filter: blur(20px); padding: 15px 25px; border-radius: 15px;
            display: flex; align-items: center; gap: 12px;
            animation: toastIn 0.5s ease, toastOut 0.5s ease 3.5s forwards; z-index: 1000;
        }
        .ach-toast-icon { font-size: 1.5rem; }
        .ach-toast-title { font-weight: 700; color: var(--gold); font-size: 0.85rem; }
        @keyframes toastIn { from { opacity: 0; transform: translateX(-50%) translateY(30px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        @keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateX(-50%) translateY(30px); } }

        /* MOBILE RESPONSIVE */
        @media (max-width: 768px) {
            .game-container { grid-template-columns: 1fr; padding: 10px; gap: 10px; }
            .sidebar {
                flex-direction: row; flex-wrap: wrap; justify-content: center;
                padding: 15px; gap: 10px;
            }
            .timer-box { width: 70px; height: 70px; }
            .time-text { font-size: 1.3rem; }
            .hangman-img { max-height: 80px; width: 80px; }
            .main-area { padding: 20px 12px; }
            .keyboard { grid-template-columns: repeat(6, 1fr); gap: 5px; }
            .key-btn { padding: 10px 3px; font-size: 1.1rem; min-height: 48px; }
            .letter-slot { width: 36px; height: 44px; font-size: 1.4rem; }
            .word-slots { gap: 5px; }
            .hint-text { font-size: 0.95rem; }
            .nav-stats { gap: 8px; font-size: 0.7rem; }
        }
        @media (max-width: 400px) {
            .keyboard { grid-template-columns: repeat(6, 1fr); }
            .letter-slot { width: 30px; height: 38px; font-size: 1.2rem; }
            .modal-content { padding: 25px 18px; }
        }
        @keyframes fadein { from { bottom: 0; opacity: 0; } to { bottom: 30px; opacity: 1; } }
        @keyframes fadeout { from { bottom: 30px; opacity: 1; } to { bottom: 0; opacity: 0; } }
    </style>
</head>
<body>
    <div id="game-toast">Please solve the word first! 🤫</div>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </a>
        <div class="nav-stats">
            <div class="nav-stat">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <div class="nav-stat" style="color:var(--gold);">⭐ <span id="score-val"><?php echo intval($stats['total_score'] ?? 0); ?></span></div>
            <div class="nav-stat" style="color:var(--accent);">🔥 <span id="streak-val"><?php echo intval($stats['current_streak'] ?? 0); ?></span></div>
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
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
            <!-- SLEEK SCOREBOARD -->
            <div style="background: rgba(255,255,255,0.03); padding: 8px 20px; border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: center; gap: 25px; border: 1px solid var(--glass-border); width: auto;">
                <span style="background: var(--accent); color: #333; padding: 2px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;"><?php echo $_SESSION['level'] ?? 'Beginner'; ?></span>
                <span style="font-size: 0.8rem; font-weight: 500;">⭐ <strong id="ui-score" style="color:var(--gold);"><?php echo intval($stats['total_score'] ?? 0); ?></strong></span>
                <span style="font-size: 0.8rem; font-weight: 500;">✅ <strong id="ui-wins" style="color: var(--success);"><?php echo intval($stats['wins'] ?? 0); ?></strong></span>
                <span style="font-size: 0.8rem; font-weight: 500;">❌ <strong id="ui-losses" style="color: var(--danger);"><?php echo intval($stats['losses'] ?? 0); ?></strong></span>
            </div>

            <h2 id="hint-display" class="hint-text">Loading Question...</h2>
            <button class="speak-game-btn" id="speak-btn" onclick="speakCurrentWord()">🔊 Listen</button>
            <ul id="word-display" class="word-slots"></ul>
            <div id="keyboard" class="keyboard"></div>
        </div>
    </div>

    <!-- RESUME / RESTART MODAL -->
    <?php if ($hasActiveSession): ?>
    <div id="resume-modal" class="modal show">
        <div class="modal-content" style="border-color: var(--accent);">
            <h2 style="color: var(--accent); margin-bottom: 15px;">🎮 Unfinished Game</h2>
            <p style="margin-bottom: 25px; opacity: 0.8;">You have an active session at <b>Word <?= $session['questions_completed'] ?>/<?= $session['total_target'] ?></b>. What would you like to do?</p>
            <button onclick="document.getElementById('resume-modal').classList.remove('show'); initGame();" class="btn-gold" style="width: 100%; margin-bottom: 10px;">Resume Game</button>
            <a href="level_select.php?restart_hangman=1" class="btn-outline" style="width: 100%; text-align: center;">Start Fresh</a>
        </div>
    </div>
    <?php endif; ?>

    <div id="result-modal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">TITLE</h2>
            <span id="final-word" class="arabic-res"></span>
            <p>Root: <span id="final-root" style="font-family:'Amiri'; color:var(--primary);"></span></p>
            <button onclick="speakArabic(document.getElementById('final-word').innerText)" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); padding:10px 25px; border-radius:20px; cursor:pointer; color:white; margin-bottom:15px;">🔊 Listen</button>
            <br>
            <button onclick="initGame()" class="btn-gold">Next Question ➔</button>
            <a href="dashboard.php" class="btn-outline">📊 Dashboard</a>
        </div>
    </div>

    <div id="toast-container"></div>

    <script>
    // =========================================================
    // 🎵 AUDIO
    // =========================================================
    const sounds = {
        correct: new Audio('assets/sounds/correct.mp3'),
        wrong:   new Audio('assets/sounds/wrong.mp3'),
        win:     new Audio('assets/sounds/win.mp3'),
        lose:    new Audio('assets/sounds/lose.mp3')
    };

    const playSound = (name) => {
        const s = sounds[name];
        if (s) { s.currentTime = 0; s.play().catch(()=>{}); }
    };

    // TTS
    function speakArabic(text) {
        if(!text) return;
        if(window.responsiveVoice) {
            responsiveVoice.speak(text, "Arabic Male");
        }
    }
    
    function showToast(msg) {
        const t = document.getElementById("game-toast");
        t.innerText = msg;
        t.className = "show";
        setTimeout(() => { t.className = t.className.replace("show", ""); }, 3000);
    }
    function speakCurrentWord() {
        if (!isGameOver) {
            showToast("Please solve the word first! 🤫");
            return;
        }
        if (currentWord) speakArabic(currentWord);
    }

    const wordDisplay = document.getElementById("word-display");
    const keyboardDiv = document.getElementById("keyboard");
    const hangmanImg = document.getElementById("hangman-img");
    const timerText = document.getElementById("timer");
    const timerCircle = document.getElementById("timer-circle");
    const modal = document.getElementById("result-modal");
    const hintDisplay = document.getElementById("hint-display");
    const speakBtn = document.getElementById("speak-btn");

    const arabicLetters = ["ا", "ب", "ت", "ث", "ج", "ح", "خ", "د", "ذ", "ر", "ز", "س", "ش", "ص", "ض", "ط", "ظ", "ع", "غ", "ف", "ق", "ك", "ل", "م", "ن", "ه", "ة", "و", "ي", "ء", "ئ", "ؤ", "ى", "أ", "إ", "آ"];

    let currentWord, currentWordID, timer;
    let isGameOver = false;
    let timeLeft = 30;
    let correctLetters = [];
    let wrongCount = 0;
    let maxGuesses = 6;

    // --- INIT GAME ---
    const initGame = async () => {
        clearInterval(timer);
        timeLeft = 30;
        wrongCount = 0;
        correctLetters = [];
        modal.classList.remove("show");
        
        hangmanImg.src = `assets/images/hangman-0.svg`;
        timerText.innerText = timeLeft;
        timerText.classList.remove('danger');
        timerCircle.classList.remove('danger');
        document.getElementById("wrong-count").innerText = 0;
        wordDisplay.innerHTML = ""; 
        keyboardDiv.innerHTML = "";
        isGameOver = false;

        try {
            const res = await fetch('api/get_word.php');
            const data = await res.json();

            if(data.completed) {
                hintDisplay.innerText = "🎉 LEVEL COMPLETE! 🎉";
                hintDisplay.style.color = "var(--success)";
                wordDisplay.innerHTML = `<a href="level_select.php" class="btn-gold" style="text-decoration:none;">Choose Next Level</a>`;
                playSound('win');
                // Clear session on completion
                fetch('api/clear_session.php?mode=hangman');
                return; 
            }

            // Sync session progress UI
            try {
                const sessRes = await fetch('api/get_session_info.php?mode=hangman');
                const sessData = await sessRes.json();
                if(sessData.questions_completed >= sessData.total_target) {
                     hintDisplay.innerText = "🏁 SESSION COMPLETE! 🏁";
                     wordDisplay.innerHTML = `<a href="level_select.php?restart_hangman=1" class="btn-gold" style="text-decoration:none;">Start New Session</a>`;
                     return;
                }
            } catch(e) {}

            if(data.error) { hintDisplay.innerText = data.error; return; }

            currentWord = data.arabic_word;
            currentWordID = data.id; 

            hintDisplay.innerText = `Translate: "${data.meaning}"`;
            document.getElementById("final-word").innerText = currentWord;
            document.getElementById("final-root").innerText = data.root;

            wordDisplay.innerHTML = currentWord.split("").map(char => {
                if (char === " ") return `<li class="letter-slot filled" style="border:none;"> </li>`;
                return `<li class="letter-slot"></li>`;
            }).join("");

            arabicLetters.forEach(char => {
                const btn = document.createElement("button");
                btn.innerText = char;
                btn.className = "key-btn";
                btn.onclick = () => handleGuess(btn, char);
                keyboardDiv.appendChild(btn);
            });

            startTimer();

        } catch (e) { console.error(e); }
    };

    // --- HANDLE GUESS ---
    const handleGuess = (btn, char) => {
        btn.disabled = true;

        if (currentWord.includes(char)) {
            playSound('correct');
            btn.classList.add('correct');

            [...currentWord].forEach((val, index) => {
                if (val === char) {
                    correctLetters.push(val);
                    const slot = wordDisplay.querySelectorAll("li")[index];
                    if(slot) {
                        slot.innerText = val;
                        slot.classList.add("filled");
                    }
                }
            });
            
            const cleanWord = [...new Set(currentWord.replace(/ /g, ''))];
            const cleanGuess = [...new Set(correctLetters)];
            
            if (cleanWord.length === cleanGuess.length) endGame(true);
        } else {
            playSound('wrong');
            btn.classList.add('wrong');
            wrongCount++;
            if(wrongCount <= maxGuesses) hangmanImg.src = `assets/images/hangman-${wrongCount}.svg`;
            document.getElementById("wrong-count").innerText = wrongCount;
            if (wrongCount >= maxGuesses) endGame(false);
        }
    };

    const startTimer = () => {
        timer = setInterval(() => {
            timeLeft--;
            timerText.innerText = timeLeft;
            const pct = (timeLeft / 30) * 100;
            timerCircle.style.strokeDasharray = `${pct}, 100`;
            if (timeLeft <= 10) {
                timerText.classList.add('danger');
                timerCircle.classList.add('danger');
            }
            if(timeLeft <= 0) endGame(false);
        }, 1000);
    };

    const endGame = async (win) => {
        clearInterval(timer);
        isGameOver = true;
        playSound(win ? 'win' : 'lose');
        
        // Auto-speak removed per user request - manual click only
        // speakArabic(currentWord);

        document.getElementById("modal-title").innerText = win ? "MUMTAZ! 🎉" : "Game Over 😔";
        document.getElementById("modal-title").style.color = win ? "var(--success)" : "var(--danger)";
        modal.classList.add("show");

        const timeTaken = 30 - timeLeft;

        try {
            const r = await fetch('api/update_progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ result: win ? 'win' : 'lose', word_id: win ? (currentWordID || 0) : 0, time_taken: timeTaken })
            });
            const res = await r.json();
            
            if (res.status === 'error') {
                alert('⚠️ Save Error: ' + res.message);
                console.error('Server error:', res.message);
                return;
            }

            // Update ALL UI elements with fresh data from server
            if (res.status === 'success') {
                document.getElementById("ui-score").innerText = res.total_score ?? 0;
                document.getElementById("ui-wins").innerText = res.wins ?? 0;
                document.getElementById("ui-losses").innerText = res.losses ?? 0;
                document.getElementById("score-val").innerText = res.total_score ?? 0;
                document.getElementById("streak-val").innerText = res.current_streak ?? 0;
            }

            // Check achievements
            try {
                const achRes = await fetch('api/check_achievements.php');
                const achData = await achRes.json();
                if (achData.unlocked && achData.unlocked.length > 0) {
                    achData.unlocked.forEach(a => showAchievementToast(a));
                }
            } catch(ae) {}
        } catch(e) { console.error('Progress save error:', e); }
    };

    function showAchievementToast(ach) {
        const toast = document.createElement('div');
        toast.className = 'ach-toast';
        toast.innerHTML = `<div class="ach-toast-icon">${ach.icon}</div><div><div class="ach-toast-title">🏅 ${ach.title} Unlocked!</div><div style="font-size:0.75rem; opacity:0.6;">+${ach.xp_reward} XP</div></div>`;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => toast.remove(), 4500);
    }

    initGame();
    </script>
</body>
</html>