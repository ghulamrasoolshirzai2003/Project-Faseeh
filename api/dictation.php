<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'dictation'");
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
    <title>Audio Dictation - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root { --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e; --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c; --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; }
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; text-align: center; flex: 1; width: 100%; }
        .top-bar { display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: 600; }
        .score-box, .timer-box { background: var(--glass); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--glass-border); }
        .timer-box { color: var(--accent); }
        .game-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        
        .play-btn { background: var(--accent); border: none; width: 100px; height: 100px; border-radius: 50%; color: white; font-size: 3rem; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(242,153,74,0.4); margin-bottom: 30px; display: flex; justify-content: center; align-items: center; margin-left: auto; margin-right: auto; }
        .play-btn:hover { transform: scale(1.1); box-shadow: 0 15px 30px rgba(242,153,74,0.6); }
        
        .dictation-input { width: 100%; padding: 20px; font-family: 'Amiri', serif; font-size: 2rem; border-radius: 15px; border: 2px solid var(--glass-border); background: var(--glass); color: white; text-align: center; margin-bottom: 20px; outline: none; transition: 0.3s; direction: rtl; }
        .dictation-input:focus { border-color: var(--accent); background: rgba(255,255,255,0.1); }
        
        .submit-btn { width: 100%; padding: 15px; background: var(--success); border: none; border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; margin-bottom: 20px; }
        .submit-btn:hover { background: #27ae60; }
        
        .result-box { display: none; padding: 20px; border-radius: 15px; margin-bottom: 20px; }
        .result-box.correct { background: rgba(46, 204, 113, 0.2); border: 1px solid var(--success); color: var(--success); }
        .result-box.wrong { background: rgba(231, 76, 60, 0.2); border: 1px solid var(--danger); color: var(--danger); }
        
        .correct-answer-text { font-family: 'Amiri', serif; font-size: 2.5rem; margin-top: 10px; color: white; }
        
        .next-btn { display: none; width: 100%; padding: 15px; background: var(--accent); border: none; border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="nav"><a href="academic_hub.php">← Back to Hub</a><div style="font-weight: 700; color: var(--accent);">AUDIO DICTATION</div></div>
    <div class="container">
        <div class="top-bar">
            <div class="score-box">XP: <span id="score">0</span></div>
            <div class="score-box">Question: <span id="q-counter"><?= $questions_completed+1 ?></span>/<?= $quiz_length ?></div>
            <div class="timer-box">⏱ <span id="timer">30</span>s</div>
        </div>
        <div id="loading" style="font-size:1.5rem; opacity:0.6; padding:50px;">Loading...</div>
        <div id="game-area" class="game-card" style="display:none;">
            <button class="play-btn" onclick="playAudio()" title="Listen">🔊</button>
            <div style="opacity: 0.7; margin-bottom: 20px; font-size: 0.9rem;">Listen carefully and type exactly what you hear in Arabic.</div>
            
            <input type="text" id="dictation-input" class="dictation-input" placeholder="اكتب ما تسمع هنا..." autocomplete="off">
            <button id="submit-btn" class="submit-btn" onclick="checkAnswer()">Submit Answer</button>
            
            <div id="result-box" class="result-box">
                <div id="result-msg" style="font-weight: 700; font-size: 1.2rem;"></div>
                <div id="correct-answer" class="correct-answer-text"></div>
                <div id="translation" style="opacity: 0.8; margin-top: 10px; color: white;"></div>
            </div>
            
            <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Question ➔</button>
        </div>
    </div>
    <script>
        const sounds = { correct: new Audio('assets/sounds/correct.mp3'), wrong: new Audio('assets/sounds/wrong.mp3') };
        let currentQuestion = null; let score = 0; let timeLeft = 30; let timerInterval;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>; let questionsAnswered = <?php echo $questions_completed; ?>;

        function startTimer() {
            timeLeft = 30; document.getElementById('timer').textContent = timeLeft;
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
            document.getElementById('loading').style.display = 'block'; document.getElementById('game-area').style.display = 'none'; 
            document.getElementById('next-btn').style.display = 'none'; document.getElementById('result-box').style.display = 'none';
            document.getElementById('submit-btn').style.display = 'block'; document.getElementById('dictation-input').value = '';
            document.getElementById('dictation-input').disabled = false;
            
            try {
                const response = await fetch('api/get_dictation.php'); const data = await response.json();
                if (data.error || data.completed) { document.getElementById('game-area').innerHTML = `<h3>Bank Exhausted</h3><br><a href="academic_hub.php" class="next-btn" style="display:block;">Hub</a>`; document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block'; return; }
                currentQuestion = data; renderQuestion();
            } catch (e) { console.error(e); }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block';
            document.getElementById('dictation-input').focus();
            playAudio();
            questionsAnswered++; document.getElementById('q-counter').textContent = questionsAnswered; startTimer();
        }
        
        function playAudio() {
            if(responsiveVoice.isPlaying()) responsiveVoice.cancel();
            responsiveVoice.speak(currentQuestion.sentence_ar, "Arabic Male", {rate: 0.8});
        }

        function handleTimeout() {
            checkAnswer(true);
        }

        // Clean string for comparison (remove punctuation and extra spaces)
        function cleanString(str) {
            return str.replace(/[.,\/#!$%\^&\*;:{}=\-_`~()؟،]/g,"").replace(/\s{2,}/g," ").trim();
        }

        function checkAnswer(isTimeout = false) {
            clearInterval(timerInterval);
            document.getElementById('submit-btn').style.display = 'none';
            document.getElementById('dictation-input').disabled = true;
            
            const userInput = cleanString(document.getElementById('dictation-input').value);
            const correctAnswer = cleanString(currentQuestion.sentence_ar);
            
            const isCorrect = !isTimeout && (userInput === correctAnswer);
            
            const resBox = document.getElementById('result-box');
            resBox.style.display = 'block';
            resBox.className = 'result-box ' + (isCorrect ? 'correct' : 'wrong');
            
            document.getElementById('result-msg').textContent = isCorrect ? 'Perfectly Typed! ✅' : (isTimeout ? 'Time is up! ❌' : 'Incorrect ❌');
            document.getElementById('correct-answer').textContent = currentQuestion.sentence_ar;
            document.getElementById('translation').textContent = currentQuestion.translation_en;
            
            if (isCorrect) { sounds.correct.play(); score += 20; document.getElementById('score').textContent = score; submitResult(true); } 
            else { sounds.wrong.play(); submitResult(false); }
        }

        document.getElementById('dictation-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { checkAnswer(); }
        });

        async function submitResult(isCorrect) {
            document.getElementById('next-btn').style.display = 'block';
            try { await fetch('api/submit_academic.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mode: 'dictation', question_id: currentQuestion.id, is_correct: isCorrect, points: 20 }) }); } catch (e) {}
        }
        window.onload = loadQuestion;
    </script>
</body>
</html>
