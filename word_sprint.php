<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Sprint ⚡ Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f0c29; --accent: #f2994a; --glass: rgba(255,255,255,0.06);
            --success: #2ecc71; --danger: #e74c3c; --gold: #f1c40f;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at center, #1a1a2e 0%, #0f0c29 100%);
            color: white; height: 100vh; display: flex; flex-direction: column; overflow: hidden;
        }

        .game-header {
            padding: 20px 40px; display: flex; justify-content: space-between; align-items: center;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .timer-container { width: 100%; height: 6px; background: rgba(255,255,255,0.1); position: relative; }
        #timer-bar { width: 100%; height: 100%; background: var(--accent); transition: width 0.1s linear; }

        .game-container {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; text-align: center;
        }

        .sprint-card {
            background: var(--glass); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 30px; padding: 40px; width: 100%; max-width: 500px;
            backdrop-filter: blur(30px); box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            animation: slideUp 0.5s ease;
        }

        .arabic-word { font-family: 'Amiri', serif; font-size: 4rem; margin-bottom: 20px; }
        
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%; margin-top: 20px; }
        .opt-btn {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 18px; border-radius: 20px; font-weight: 600;
            cursor: pointer; transition: 0.2s; font-size: 1rem; outline: none;
        }
        .opt-btn:hover { background: rgba(255,255,255,0.15); transform: scale(1.02); border-color: var(--accent); }
        .opt-btn.correct { background: var(--success) !important; color: white; border-color: var(--success); }
        .opt-btn.wrong { background: var(--danger) !important; color: white; border-color: var(--danger); }

        /* MODALS */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px);
            display: none; align-items: center; justify-content: center; z-index: 2000;
        }
        .modal-card {
            background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 25px; padding: 40px; width: 90%; max-width: 450px; text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .modal-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 15px; color: var(--gold); }
        .modal-desc { opacity: 0.7; margin-bottom: 30px; line-height: 1.6; }
        
        .modal-actions { display: flex; gap: 15px; }
        .modal-btn {
            flex: 1; padding: 15px; border-radius: 15px; font-weight: 700; cursor: pointer;
            border: none; transition: 0.3s; font-size: 0.9rem;
        }
        .btn-stay { background: var(--accent); color: white; }
        .btn-exit { background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); }
        .modal-btn:hover { transform: translateY(-3px); opacity: 0.9; }

        /* Result Screen Enhancements */
        .result-motivation { font-size: 1.1rem; font-weight: 600; color: var(--success); margin: 20px 0; }

        @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="game-header">
        <button onclick="confirmExit()" style="background:none; border:none; color:white; font-weight:700; cursor:pointer; font-size:0.8rem;">← EXIT SPRINT</button>
        <div style="font-weight: 800; color: var(--accent);">🔥 <span id="current-score">0</span></div>
        <div id="time-left" style="font-weight: 900; font-size: 1.5rem;">60s</div>
        <div id="opp-score-box" style="font-weight: 800; color: var(--danger); display:none;">⚔️ <span id="opp-name">Opp</span>: <span id="opp-score">0</span></div>
    </div>
    <div class="timer-container"><div id="timer-bar"></div></div>

    <div class="game-container">
        <div class="sprint-card">
            <div id="word-display" class="arabic-word">...</div>
            <div class="options-grid" id="options-container"></div>
        </div>
    </div>

    <!-- EXIT CONFIRMATION MODAL -->
    <div id="exitModal" class="modal-overlay">
        <div class="modal-card">
            <div style="font-size: 3rem; margin-bottom: 10px;">👋</div>
            <div class="modal-title">Wait, <?= htmlspecialchars($username) ?>!</div>
            <div class="modal-desc">
                You're doing great! Your <strong><span id="exit-score-preview">0</span> XP</strong> is already saved. 
                Want to finish the 60s sprint and smash your record?
            </div>
            <div class="modal-actions">
                <button class="modal-btn btn-exit" onclick="finalExit()">Yes, Quit</button>
                <button class="modal-btn btn-stay" onclick="closeModal()">Keep Sprinting!</button>
            </div>
        </div>
    </div>

    <!-- RESULT SCREEN -->
    <div id="result-screen" class="modal-overlay" style="display: none;">
        <div class="modal-card" style="max-width: 500px;">
            <div style="font-size: 4rem;">⚡</div>
            <div id="result-title" class="modal-title">SPRINT COMPLETE!</div>
            <div id="motivation-text" class="result-motivation">You were amazing!</div>
            <div style="font-size: 1.2rem; margin-bottom: 20px;">
                You: <span id="final-correct" style="color:var(--success);">0</span> 
                <span id="final-opp-wrap" style="display:none;">| Opponent: <span id="final-opp" style="color:var(--danger);">0</span></span><br>
                XP Earned: <span id="final-xp" style="color:var(--accent);">0</span>
            </div>
            <div class="modal-actions">
                <a href="level_select.php" class="modal-btn btn-exit" style="text-decoration:none;">To Hub</a>
                <button class="modal-btn btn-stay" onclick="location.reload()">Play Again</button>
            </div>
        </div>
    </div>

    <!-- MODE SELECT MODAL -->
    <div id="modeModal" class="modal-overlay" style="display: flex;">
        <div class="modal-card">
            <div style="font-size: 3rem; margin-bottom: 10px;">⚡</div>
            <div class="modal-title">Word Sprint</div>
            <div class="modal-desc">Race against the clock or battle a real opponent!</div>
            <div class="modal-actions" style="flex-direction: column;">
                <button class="modal-btn btn-stay" onclick="startSolo()">Play Solo</button>
                <button class="modal-btn" style="background: var(--danger); color: white;" onclick="startMultiplayer()">⚔️ 1v1 Live Battle</button>
            </div>
        </div>
    </div>

    <!-- MATCHMAKING MODAL -->
    <div id="matchmakeModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-title">Finding Opponent...</div>
            <div class="modal-desc">Searching the academy for a worthy challenger...</div>
            <div class="timer-container" style="margin-bottom:20px; overflow:hidden; border-radius:10px;">
                <div style="width:50%; height:100%; background:var(--danger); animation: scan 1s infinite alternate;"></div>
            </div>
            <button class="modal-btn btn-exit" onclick="cancelMatchmaking()">Cancel</button>
        </div>
    </div>
    <style> @keyframes scan { 0% { transform: translateX(0); } 100% { transform: translateX(100%); } } </style>

    <script>
        let words = [];
        let currentIndex = 0;
        let score = 0;
        let timeLeft = 60;
        let gameActive = false;
        let timerId = null;
        let isMultiplayer = false;
        let gameId = 0;
        let pollInterval = null;
        let matchmakeInterval = null;
        let oppScore = 0;

        function startSolo() {
            document.getElementById('modeModal').style.display = 'none';
            initGame();
        }

        async function startMultiplayer() {
            document.getElementById('modeModal').style.display = 'none';
            document.getElementById('matchmakeModal').style.display = 'flex';
            
            try {
                let res = await fetch('api/matchmake.php');
                let data = await res.json();
                if (data.status === 'success') {
                    gameId = data.game.id;
                    pollMatchmaking();
                }
            } catch (e) { alert("Matchmaking error."); }
        }

        function pollMatchmaking() {
            matchmakeInterval = setInterval(async () => {
                let res = await fetch('api/sync_battle.php', { method: 'POST', body: JSON.stringify({ game_id: gameId }) });
                let data = await res.json();
                if (data.status === 'playing') {
                    clearInterval(matchmakeInterval);
                    document.getElementById('matchmakeModal').style.display = 'none';
                    isMultiplayer = true;
                    document.getElementById('opp-score-box').style.display = 'block';
                    document.getElementById('opp-name').innerText = data.opp_name;
                    initGame();
                    startSyncing();
                }
            }, 2000);
        }

        function cancelMatchmaking() {
            clearInterval(matchmakeInterval);
            fetch('api/sync_battle.php', { method: 'POST', body: JSON.stringify({ game_id: gameId, action: 'finish' }) });
            location.reload();
        }

        function startSyncing() {
            pollInterval = setInterval(async () => {
                if (!gameActive) return;
                let res = await fetch('api/sync_battle.php', { method: 'POST', body: JSON.stringify({ game_id: gameId, score: score }) });
                let data = await res.json();
                oppScore = data.opp_score;
                document.getElementById('opp-score').innerText = oppScore;
            }, 1500);
        }

        async function initGame() {
            try {
                const res = await fetch('api/get_sprint.php');
                words = await res.json();
                gameActive = true;
                nextQuestion();
                startTimer();
            } catch (e) { console.error(e); }
        }

        function startTimer() {
            timerId = setInterval(() => {
                if (!gameActive) return;
                timeLeft -= 0.1;
                document.getElementById('timer-bar').style.width = (timeLeft / 60) * 100 + "%";
                document.getElementById('time-left').innerText = Math.ceil(timeLeft) + "s";
                if (timeLeft <= 0) endGame();
            }, 100);
        }

        function nextQuestion() {
            const current = words[currentIndex];
            document.getElementById('word-display').innerText = current.arabic_word;
            const correct = current.meaning;
            let options = [correct];
            while (options.length < 4) {
                const r = words[Math.floor(Math.random() * words.length)].meaning;
                if (!options.includes(r)) options.push(r);
            }
            options.sort(() => Math.random() - 0.5);
            
            const container = document.getElementById('options-container');
            container.innerHTML = '';
            options.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'opt-btn';
                btn.innerText = opt;
                btn.onclick = () => checkAnswer(btn, opt === correct);
                container.appendChild(btn);
            });
        }

        function checkAnswer(btn, isCorrect) {
            const btns = document.querySelectorAll('.opt-btn');
            btns.forEach(b => b.disabled = true);
            if (isCorrect) {
                btn.classList.add('correct');
                score++;
                document.getElementById('current-score').innerText = score;
                autoSave(true); // Save correct!
            } else {
                btn.classList.add('wrong');
                btns.forEach(b => { if (b.innerText === words[currentIndex].meaning) b.classList.add('correct'); });
                autoSave(false); // Save wrong!
            }
            setTimeout(() => { currentIndex++; nextQuestion(); }, 400);
        }

        function autoSave(isCorrect) {
            console.log(`📡 Saving Word Sprint: ${isCorrect ? "Correct" : "Wrong"}`);

            fetch('api/save_sprint.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    correct: isCorrect ? 1 : 0, 
                    wrong: isCorrect ? 0 : 1 
                })
            }).then(r => r.json()).then(data => {
                console.log("✅ Save Result:", data);
            }).catch(err => {
                console.error("❌ Save Error:", err);
            });
        }

        function confirmExit() {
            gameActive = false;
            document.getElementById('exit-score-preview').innerText = score * 5;
            document.getElementById('exitModal').style.display = 'flex';
        }

        function closeModal() {
            gameActive = true;
            document.getElementById('exitModal').style.display = 'none';
        }

        function finalExit() {
            window.location.href = 'level_select.php';
        }

        function endGame() {
            gameActive = false;
            clearInterval(timerId);
            if (pollInterval) clearInterval(pollInterval);
            
            if (isMultiplayer) {
                fetch('api/sync_battle.php', { method: 'POST', body: JSON.stringify({ game_id: gameId, score: score, action: 'finish' }) });
            }
            
            let msg = "Great effort! Consistency is key. 🗝️";
            if (score > 10) msg = "Wow! You're on fire today! 🔥";
            if (score > 20) msg = "Legendary! You're an Arabic master! 👑";
            
            document.getElementById('result-title').innerText = "SPRINT COMPLETE!";
            
            if (isMultiplayer) {
                document.getElementById('final-opp-wrap').style.display = 'inline';
                document.getElementById('final-opp').innerText = oppScore;
                if (score > oppScore) { msg = "🏆 YOU WON THE BATTLE!"; document.getElementById('result-title').innerText = "VICTORY!"; }
                else if (score < oppScore) { msg = "💀 YOU LOST!"; document.getElementById('result-title').innerText = "DEFEAT!"; }
                else { msg = "⚔️ IT'S A TIE!"; document.getElementById('result-title').innerText = "DRAW!"; }
            }
            
            let earnedXp = score * 5;
            if (isMultiplayer && score > oppScore) earnedXp += 20; // Win bonus
            
            document.getElementById('motivation-text').innerText = msg;
            document.getElementById('final-correct').innerText = score;
            document.getElementById('final-xp').innerText = earnedXp;
            document.getElementById('result-screen').style.display = 'flex';

            // Shout it to the community!
            if (score >= 4 || isMultiplayer) {
                let activityDesc = isMultiplayer ? (score > oppScore ? 'just won a Live 1v1 Battle! 🏆' : 'played a Live 1v1 Battle ⚔️') : 'just smashed a ⚡ Word Sprint high score!';
                fetch('api/save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ xp: (isMultiplayer && score > oppScore ? 20 : 0), mode: 'word_sprint_shout', extra_desc: activityDesc })
                });
            }
        }
    </script>
</body>
</html>
