<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { header("Location: admin_panel.php"); exit; }
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';
if (!isset($_SESSION['level'])) $_SESSION['level'] = 'beginner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Quiz Mode</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
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
            color: white; min-height: 100vh; display: flex; flex-direction: column;
        }

        /* Navbar */
        .navbar {
            width: 100%; padding: 15px 25px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .mini-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 12px rgba(242,153,74,0.4);
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 18px; color: white; margin-top: -2px; }
        .mini-text { font-size: 1.2rem; font-weight: 800; color: white; margin: 0; }
        .nav-stats { display: flex; gap: 15px; font-size: 0.85rem; font-weight: 600; }
        .nav-stat { display: flex; align-items: center; gap: 4px; }

        /* Game Container */
        .game-wrap {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 20px;
        }
        .game-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 25px; padding: 40px; max-width: 550px; width: 100%;
            backdrop-filter: blur(15px); text-align: center;
            animation: slideUp 0.5s ease;
        }

        /* Progress */
        .progress-bar {
            display: flex; gap: 4px; margin-bottom: 25px;
        }
        .progress-dot {
            flex: 1; height: 5px; border-radius: 5px;
            background: rgba(255,255,255,0.1); transition: 0.3s;
        }
        .progress-dot.correct { background: var(--success); }
        .progress-dot.wrong { background: var(--danger); }
        .progress-dot.current { background: rgba(255,255,255,0.3); }

        /* Question */
        .q-counter {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px;
            opacity: 0.4; margin-bottom: 10px;
        }
        .arabic-word {
            font-family: 'Amiri', serif; font-size: 3.5rem; font-weight: 700;
            color: white; margin-bottom: 5px; direction: rtl;
            text-shadow: 0 0 30px rgba(242,153,74,0.3);
            animation: pulseWord 2s ease-in-out infinite;
        }
        .word-root {
            font-size: 0.8rem; opacity: 0.4; margin-bottom: 8px;
            font-family: 'Amiri', serif;
        }
        .speak-btn {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            color: white; padding: 6px 16px; border-radius: 20px;
            cursor: pointer; font-size: 0.8rem; transition: 0.3s; margin-bottom: 20px;
            display: inline-block;
        }
        .speak-btn:hover { background: rgba(255,255,255,0.2); }
        .q-label {
            font-size: 0.9rem; opacity: 0.6; margin-bottom: 20px;
        }

        /* Timer */
        .timer-row {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-bottom: 20px;
        }
        .timer-bar {
            flex: 1; max-width: 250px; height: 6px;
            background: rgba(255,255,255,0.1); border-radius: 6px; overflow: hidden;
        }
        .timer-fill {
            height: 100%; border-radius: 6px;
            background: linear-gradient(to right, var(--success), var(--accent));
            transition: width 0.3s linear;
        }
        .timer-fill.danger { background: linear-gradient(to right, var(--danger), #ff6b6b); }
        .timer-text { font-size: 0.9rem; font-weight: 700; min-width: 25px; }

        /* Options */
        .options-grid { display: grid; gap: 10px; }
        .option-btn {
            background: rgba(255,255,255,0.07); border: 2px solid rgba(255,255,255,0.12);
            color: white; padding: 16px 20px; border-radius: 15px;
            cursor: pointer; font-size: 1rem; font-weight: 500;
            transition: all 0.3s; text-align: left;
            display: flex; align-items: center; gap: 12px;
            font-family: 'Poppins', sans-serif;
        }
        .option-btn:hover:not(:disabled) {
            background: rgba(255,255,255,0.12); border-color: var(--accent);
            transform: translateX(5px);
        }
        .option-btn .letter {
            width: 30px; height: 30px; border-radius: 8px;
            background: rgba(255,255,255,0.1); display: flex;
            align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
        }
        .option-btn.correct {
            background: rgba(0,184,148,0.2); border-color: var(--success);
            animation: correctPulse 0.5s ease;
        }
        .option-btn.correct .letter { background: var(--success); }
        .option-btn.wrong {
            background: rgba(231,76,60,0.2); border-color: var(--danger);
            animation: shake 0.4s ease;
        }
        .option-btn.wrong .letter { background: var(--danger); }
        .option-btn:disabled { cursor: not-allowed; opacity: 0.5; }

        /* Result */
        .result-display {
            margin-top: 15px; padding: 15px; border-radius: 15px;
            font-weight: 600; animation: slideUp 0.3s ease;
        }
        .result-display.win { background: rgba(0,184,148,0.15); color: var(--success); }
        .result-display.lose { background: rgba(231,76,60,0.15); color: var(--danger); }

        /* Score popup */
        .score-popup {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 3rem; font-weight: 900; pointer-events: none;
            animation: scorePop 1s ease-out forwards; z-index: 999;
        }
        .score-popup.plus { color: var(--success); }
        .score-popup.minus { color: var(--danger); }

        /* Round complete */
        .round-complete {
            text-align: center; animation: slideUp 0.5s ease;
        }
        .round-score { font-size: 3rem; font-weight: 900; color: var(--gold); margin: 10px 0; }
        .round-stats { display: flex; gap: 20px; justify-content: center; margin: 20px 0; }
        .round-stat { text-align: center; }
        .round-stat-val { font-size: 1.5rem; font-weight: 800; }
        .round-stat-label { font-size: 0.7rem; opacity: 0.5; text-transform: uppercase; }
        .btn-play-again {
            background: linear-gradient(to right, var(--accent), var(--accent2));
            border: none; color: white; padding: 15px 40px; border-radius: 50px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: 0.3s; margin: 5px;
        }
        .btn-play-again:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(242,153,74,0.4); }
        .btn-secondary {
            background: transparent; border: 2px solid rgba(255,255,255,0.2);
            color: white; padding: 13px 35px; border-radius: 50px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: 0.3s; margin: 5px; text-decoration: none; display: inline-block;
        }
        .btn-secondary:hover { border-color: white; background: rgba(255,255,255,0.1); }

        /* Toast notification */
        #toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 10px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulseWord { 0%,100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        @keyframes correctPulse { 0% { transform: scale(1); } 50% { transform: scale(1.03); } 100% { transform: scale(1); } }
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }
        @keyframes scorePop { 0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); } 100% { opacity: 0; transform: translate(-50%, -150%) scale(1.5); } }
        @keyframes toastIn { from { opacity: 0; transform: translateX(-50%) translateY(30px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        @keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateX(-50%) translateY(30px); } }

        @media (max-width: 600px) {
            .game-card { padding: 25px 18px; }
            .arabic-word { font-size: 2.5rem; }
            .option-btn { padding: 14px 15px; font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div id="toast">Please answer the question first! 🤫</div>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </a>
        <div class="nav-stats">
            <div class="nav-stat">⭐ <span id="score-val">0</span></div>
            <div class="nav-stat">🔥 <span id="streak-val">0</span></div>
            <div class="nav-stat">📊 <span id="q-num">0</span>/10</div>
        </div>
        <a href="dashboard.php" style="color:white; text-decoration:none; font-weight:600; font-size:0.85rem;">✕ Quit</a>
    </nav>

    <div class="game-wrap">
        <div class="game-card" id="game-card">
            <!-- Filled by JS -->
            <div style="text-align:center; opacity:0.6;">Loading quiz...</div>
        </div>
    </div>

    <div id="toast-container"></div>

    <audio id="snd-correct" src="assets/sounds/correct.mp3" preload="auto"></audio>
    <audio id="snd-wrong" src="assets/sounds/wrong.mp3" preload="auto"></audio>
    <audio id="snd-win" src="assets/sounds/win.mp3" preload="auto"></audio>

    <?php
    $userId = $_SESSION['user_id'];
    
    // PERSISTENCE: Check if session exists, else create it
    $stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'quiz'");
    $stmt->execute([$userId]);
    $session = $stmt->fetch();
    
    if (!$session) {
        $stmt = $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'quiz', 0, 10)");
        $stmt->execute([$userId]);
        $q_completed = 0;
        $total_limit = 10;
    } else {
        $q_completed = $session['questions_completed'];
        $total_limit = $session['total_target'];
    }
    ?>
    const TOTAL_QUESTIONS = <?php echo $total_limit; ?>;
    let currentQ = <?php echo $q_completed; ?>;
    let totalScore = 0;
    let gameStreak = 0;
    let results = []; // 'correct' | 'wrong' per question
    let timer = null;
    let timeLeft = 15;
    let currentData = null;
    let answeredThisQ = false;

    const card = document.getElementById('game-card');

    // Sounds
    const sndCorrect = document.getElementById('snd-correct');
    const sndWrong = document.getElementById('snd-wrong');
    const sndWin = document.getElementById('snd-win');
    function playSound(snd) { snd.currentTime = 0; snd.play().catch(()=>{}); }

    // TTS
    function speakArabic(text) {
        if(!text) return;
        if(!answeredThisQ) {
            showToast("Answer the question first to unlock the audio! 🤫");
            return;
        }
        if(window.responsiveVoice) {
            responsiveVoice.speak(text, "Arabic Male");
        }
    }
    
    function showToast(msg) {
        const t = document.getElementById("toast");
        t.innerText = msg;
        t.className = "show";
        setTimeout(() => { t.className = t.className.replace("show", ""); }, 3000);
    }

    async function loadQuestion() {
        if (currentQ >= TOTAL_QUESTIONS) { showResults(); return; }
        answeredThisQ = false;

        try {
            const res = await fetch('api/get_mcq.php');
            const data = await res.json();
            if (data.error) { card.innerHTML = `<p style="color:var(--danger);">${data.error}</p>`; return; }
            currentData = data;
            renderQuestion(data);
        } catch (e) { console.error(e); }
    }

    function renderQuestion(data) {
        currentQ++;
        document.getElementById('q-num').innerText = currentQ;
        timeLeft = 15;

        const letters = ['A', 'B', 'C', 'D'];

        card.innerHTML = `
            <div class="progress-bar">
                ${Array.from({length: TOTAL_QUESTIONS}, (_, i) => {
                    let cls = '';
                    if (i < results.length) cls = results[i] === 'correct' ? 'correct' : 'wrong';
                    else if (i === results.length) cls = 'current';
                    return `<div class="progress-dot ${cls}"></div>`;
                }).join('')}
            </div>
            <div class="q-counter">Question ${currentQ} of ${TOTAL_QUESTIONS}</div>
            <div class="arabic-word">${data.arabic_word}</div>
            ${data.root ? `<div class="word-root">Root: ${data.root}</div>` : ''}
            <button class="speak-btn" id="speak-btn" onclick="speakArabic('${data.arabic_word}')">🔊 Listen to Pronunciation</button>
            <div class="q-label">What does this word mean?</div>
            <div class="timer-row">
                <div class="timer-bar"><div class="timer-fill" id="timer-fill" style="width:100%"></div></div>
                <div class="timer-text" id="timer-text">${timeLeft}</div>
            </div>
            <div class="options-grid" id="options">
                ${data.options.map((opt, i) => `
                    <button class="option-btn" onclick="submitAnswer(this, '${opt.replace(/'/g, "\\'")}', ${i})" data-answer="${opt}">
                        <div class="letter">${letters[i]}</div>
                        <span>${opt}</span>
                    </button>
                `).join('')}
            </div>
            <div id="result-area"></div>
        `;

        // Auto-speak removed to prevent cheating!
        // Start timer
        clearInterval(timer);
        timer = setInterval(() => {
            timeLeft--;
            const pct = (timeLeft / 15) * 100;
            const fill = document.getElementById('timer-fill');
            const text = document.getElementById('timer-text');
            if (fill) {
                fill.style.width = pct + '%';
                if (timeLeft <= 5) fill.classList.add('danger');
            }
            if (text) text.innerText = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                if (!answeredThisQ) timeoutAnswer();
            }
        }, 1000);
    }

    async function submitAnswer(btn, answer, index) {
        if (answeredThisQ) return;
        answeredThisQ = true;
        clearInterval(timer);

        const isCorrect = answer === currentData.correct_answer;
        const timeTaken = 15 - timeLeft;

        // Highlight buttons
        const buttons = document.querySelectorAll('.option-btn');
        buttons.forEach(b => {
            b.disabled = true;
            if (b.dataset.answer === currentData.correct_answer) b.classList.add('correct');
        });
        if (!isCorrect) btn.classList.add('wrong');

        // Show speak button and auto-play
        const spkBtn = document.getElementById('speak-btn');
        if (spkBtn) spkBtn.style.display = 'inline-block';
        speakArabic(currentData.arabic_word);

        // Sound
        playSound(isCorrect ? sndCorrect : sndWrong);

        // Score popup
        if (isCorrect) {
            const gained = 10 + Math.max(0, 10 - timeTaken);
            totalScore += gained;
            gameStreak++;
            showScorePopup('+' + gained, true);
        } else {
            gameStreak = 0;
            showScorePopup('-5', false);
        }
        results.push(isCorrect ? 'correct' : 'wrong');
        document.getElementById('score-val').innerText = totalScore;
        document.getElementById('streak-val').innerText = gameStreak;

        // Result text
        const area = document.getElementById('result-area');
        area.innerHTML = `<div class="result-display ${isCorrect ? 'win' : 'lose'}">
            ${isCorrect ? '✅ Correct!' : `❌ The answer was: ${currentData.correct_answer}`}
        </div>`;

        // API call
        try {
            await fetch('api/submit_mcq.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ word_id: currentData.id, correct: isCorrect, time_taken: timeTaken })
            });
            // Check achievements
            const achRes = await fetch('api/check_achievements.php');
            const achData = await achRes.json();
            if (achData.unlocked && achData.unlocked.length > 0) {
                achData.unlocked.forEach(a => showAchievementToast(a));
            }
        } catch(e) { console.error(e); }

        // Auto-advance
        setTimeout(() => loadQuestion(), 1800);
    }

    function timeoutAnswer() {
        answeredThisQ = true;
        results.push('wrong');
        playSound(sndWrong);

        const buttons = document.querySelectorAll('.option-btn');
        buttons.forEach(b => {
            b.disabled = true;
            if (b.dataset.answer === currentData.correct_answer) b.classList.add('correct');
        });

        const area = document.getElementById('result-area');
        area.innerHTML = `<div class="result-display lose">⏰ Time's up! The answer was: ${currentData.correct_answer}</div>`;

        fetch('api/submit_mcq.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ word_id: currentData.id, correct: false, time_taken: 15 })
        }).catch(()=>{});

        setTimeout(() => loadQuestion(), 2000);
    }

    function showResults() {
        clearInterval(timer);
        const correctCount = results.filter(r => r === 'correct').length;
        const accuracy = Math.round((correctCount / TOTAL_QUESTIONS) * 100);
        const isPerfect = correctCount === TOTAL_QUESTIONS;

        if (isPerfect || correctCount >= 7) playSound(sndWin);

        card.innerHTML = `
            <div class="round-complete">
                <div style="font-size:3rem; margin-bottom:10px;">${isPerfect ? '🌟' : correctCount >= 7 ? '🎉' : correctCount >= 4 ? '👍' : '😔'}</div>
                <h2>${isPerfect ? 'PERFECT ROUND!' : correctCount >= 7 ? 'Great Job!' : correctCount >= 4 ? 'Good Effort!' : 'Keep Practicing!'}</h2>
                <div class="round-score">${totalScore} pts</div>
                <div class="round-stats">
                    <div class="round-stat">
                        <div class="round-stat-val" style="color:var(--success);">${correctCount}</div>
                        <div class="round-stat-label">Correct</div>
                    </div>
                    <div class="round-stat">
                        <div class="round-stat-val" style="color:var(--danger);">${TOTAL_QUESTIONS - correctCount}</div>
                        <div class="round-stat-label">Wrong</div>
                    </div>
                    <div class="round-stat">
                        <div class="round-stat-val" style="color:var(--gold);">${accuracy}%</div>
                        <div class="round-stat-label">Accuracy</div>
                    </div>
                </div>
                <div class="progress-bar" style="margin-bottom:25px;">
                    ${results.map(r => `<div class="progress-dot ${r === 'correct' ? 'correct' : 'wrong'}"></div>`).join('')}
                </div>
                <button class="btn-play-again" onclick="resetGame()">Play Again 🔄</button>
                <a href="dashboard.php" class="btn-secondary">📊 Dashboard</a>
                <a href="leaderboard.php" class="btn-secondary">🏆 Rankings</a>
            </div>
        `;

        // Check for perfect MCQ achievement
        if (isPerfect) {
            fetch('api/check_achievements.php').then(r => r.json()).then(d => {
                if (d.unlocked) d.unlocked.forEach(a => showAchievementToast(a));
            }).catch(()=>{});
        }
    }

    function resetGame() {
        currentQ = 0; totalScore = 0; gameStreak = 0; results = [];
        document.getElementById('score-val').innerText = 0;
        document.getElementById('streak-val').innerText = 0;
        loadQuestion();
    }

    function showScorePopup(text, isPlus) {
        const popup = document.createElement('div');
        popup.className = 'score-popup ' + (isPlus ? 'plus' : 'minus');
        popup.innerText = text;
        document.body.appendChild(popup);
        setTimeout(() => popup.remove(), 1100);
    }

    function showAchievementToast(ach) {
        const toast = document.createElement('div');
        toast.className = 'ach-toast';
        toast.innerHTML = `
            <div class="ach-toast-icon">${ach.icon}</div>
            <div class="ach-toast-text">
                <div class="ach-toast-title">Achievement Unlocked!</div>
                <div>${ach.title} — +${ach.xp_reward} XP</div>
            </div>
        `;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => toast.remove(), 4500);
    }

    // Start
    loadQuestion();
    </script>
</body>
</html>
