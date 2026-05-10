<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'vocab_match'");
$stmt->execute([$userId]);
$session = $stmt->fetch();
$questions_completed = $session['questions_completed'] ?? 0;
$quiz_length = $session['total_target'] ?? 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vocab Match-Up - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e; --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c; --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; }
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; text-align: center; flex: 1; width: 100%; }
        .top-bar { display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: 600; }
        .score-box, .timer-box { background: var(--glass); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--glass-border); }
        .timer-box { color: var(--accent); }
        .game-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        
        .match-grid { display: flex; justify-content: space-between; gap: 40px; margin-top: 30px; }
        .col { flex: 1; display: flex; flex-direction: column; gap: 15px; }
        
        .match-btn { padding: 20px; background: var(--glass); border: 2px solid var(--glass-border); border-radius: 15px; color: white; font-size: 1.2rem; cursor: pointer; transition: 0.3s; font-weight: 600; }
        .match-btn.ar { font-family: 'Amiri', serif; font-size: 1.8rem; }
        .match-btn:hover:not(.matched) { background: rgba(255,255,255,0.1); transform: translateY(-2px); }
        .match-btn.selected { border-color: var(--accent); background: rgba(242,153,74,0.2); transform: scale(1.05); }
        .match-btn.matched { background: rgba(46, 204, 113, 0.2); border-color: var(--success); color: var(--success); opacity: 0.5; pointer-events: none; }
        .match-btn.wrong { animation: shake 0.5s; background: rgba(231, 76, 60, 0.2); border-color: var(--danger); }
        
        @keyframes shake { 0%, 100% {transform: translateX(0);} 25% {transform: translateX(-5px);} 75% {transform: translateX(5px);} }
        
        .next-btn { display: none; width: 100%; padding: 15px; background: var(--accent); border: none; border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="nav"><a href="academic_hub.php">← Back to Hub</a><div style="font-weight: 700; color: var(--accent);">VOCAB MATCH-UP</div></div>
    <div class="container">
        <div class="top-bar">
            <div class="score-box">XP: <span id="score">0</span></div>
            <div class="score-box">Set: <span id="q-counter"><?= $questions_completed+1 ?></span>/<?= $quiz_length ?></div>
            <div class="timer-box">⏱ <span id="timer">45</span>s</div>
        </div>
        <div id="loading" style="font-size:1.5rem; opacity:0.6; padding:50px;">Loading...</div>
        <div id="game-area" class="game-card" style="display:none;">
            <div style="opacity: 0.7; margin-bottom: 10px;">Select an Arabic term, then select its English meaning.</div>
            <div class="match-grid">
                <div class="col" id="col-ar"></div>
                <div class="col" id="col-en"></div>
            </div>
            <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Set ➔</button>
        </div>
    </div>
    <script>
        const sounds = { correct: new Audio('assets/sounds/correct.mp3'), wrong: new Audio('assets/sounds/wrong.mp3') };
        let currentQuestion = null; let score = 0; let timeLeft = 45; let timerInterval;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>; let questionsAnswered = <?php echo $questions_completed; ?>;
        
        let selectedAr = null;
        let selectedEn = null;
        let pairsMatched = 0;

        function startTimer() {
            timeLeft = 45; document.getElementById('timer').textContent = timeLeft;
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--; document.getElementById('timer').textContent = timeLeft;
                if(timeLeft <= 0) { clearInterval(timerInterval); handleTimeout(); }
            }, 1000);
        }

        async function loadQuestion() {
            if (questionsAnswered >= TOTAL_QUESTIONS) {
                document.getElementById('game-area').innerHTML = `<div style="text-align:center; padding: 40px 0;"><h1>🏆 Session Complete!</h1><br><a href="academic_hub.php" class="next-btn" style="display:inline-block; width:auto; padding: 15px 40px; text-decoration:none;">Back to Hub</a></div>`;
                return;
            }
            document.getElementById('loading').style.display = 'block'; document.getElementById('game-area').style.display = 'none'; document.getElementById('next-btn').style.display = 'none';
            try {
                const response = await fetch('api/get_vocab_match.php'); const data = await response.json();
                if (data.error || data.completed) { document.getElementById('game-area').innerHTML = `<h3>Bank Exhausted</h3><br><a href="academic_hub.php" class="next-btn" style="display:block;">Hub</a>`; document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block'; return; }
                currentQuestion = data; renderQuestion();
            } catch (e) { console.error(e); }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block';
            pairsMatched = 0; selectedAr = null; selectedEn = null;
            
            const colAr = document.getElementById('col-ar'); colAr.innerHTML = '';
            const colEn = document.getElementById('col-en'); colEn.innerHTML = '';
            
            currentQuestion.ar_terms.forEach(t => {
                const btn = document.createElement('button'); btn.className = 'match-btn ar'; btn.textContent = t.text; btn.dataset.id = t.id;
                btn.onclick = () => handleSelect('ar', btn, t.id); colAr.appendChild(btn);
            });
            currentQuestion.en_terms.forEach(t => {
                const btn = document.createElement('button'); btn.className = 'match-btn en'; btn.textContent = t.text; btn.dataset.id = t.id;
                btn.onclick = () => handleSelect('en', btn, t.id); colEn.appendChild(btn);
            });
            
            questionsAnswered++; document.getElementById('q-counter').textContent = questionsAnswered; startTimer();
        }
        
        function handleSelect(type, btn, id) {
            if (type === 'ar') {
                if (selectedAr) selectedAr.btn.classList.remove('selected');
                selectedAr = {btn, id}; btn.classList.add('selected');
            } else {
                if (selectedEn) selectedEn.btn.classList.remove('selected');
                selectedEn = {btn, id}; btn.classList.add('selected');
            }
            
            if (selectedAr && selectedEn) {
                checkMatch();
            }
        }
        
        function checkMatch() {
            const arBtn = selectedAr.btn; const enBtn = selectedEn.btn;
            if (selectedAr.id === selectedEn.id) {
                sounds.correct.play();
                arBtn.classList.remove('selected'); enBtn.classList.remove('selected');
                arBtn.classList.add('matched'); enBtn.classList.add('matched');
                score += 5; document.getElementById('score').textContent = score;
                pairsMatched++;
                selectedAr = null; selectedEn = null;
                if (pairsMatched === 5) { clearInterval(timerInterval); submitResult(true); }
            } else {
                sounds.wrong.play();
                arBtn.classList.add('wrong'); enBtn.classList.add('wrong');
                setTimeout(() => {
                    arBtn.classList.remove('wrong', 'selected'); enBtn.classList.remove('wrong', 'selected');
                    selectedAr = null; selectedEn = null;
                }, 500);
            }
        }

        function handleTimeout() {
            document.querySelectorAll('.match-btn').forEach(b => b.classList.add('matched'));
            submitResult(false);
        }

        async function submitResult(isCorrect) {
            document.getElementById('next-btn').style.display = 'block';
            try { await fetch('api/submit_academic.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mode: 'vocab_match', question_id: currentQuestion.id, is_correct: isCorrect, points: 25 }) }); } catch (e) {}
        }
        window.onload = loadQuestion;
    </script>
</body>
</html>
