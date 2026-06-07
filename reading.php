<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'reading'");
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
    <title>Reading Comprehension - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root { --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e; --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c; --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1); }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; }
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; flex: 1; width: 100%; }
        .top-bar { display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: 600; }
        .score-box, .timer-box { background: var(--glass); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--glass-border); }
        .timer-box { color: var(--accent); }
        .game-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        
        .paragraph-box { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; padding: 30px; margin-bottom: 30px; position: relative; }
        .paragraph-ar { font-family: 'Amiri', serif; font-size: 2rem; line-height: 2; direction: rtl; }
        .paragraph-en { font-size: 1.1rem; opacity: 0.8; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--glass-border); display: none; }
        .toggle-en-btn { background: none; border: 1px solid var(--glass-border); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; position: absolute; top: 10px; left: 10px; opacity: 0.7; }
        .toggle-en-btn:hover { background: rgba(255,255,255,0.1); opacity: 1; }
        
        .question-ar { font-family: 'Amiri', serif; font-size: 1.8rem; margin-bottom: 20px; text-align: center; color: var(--accent); direction: rtl; }
        
        .options-grid { display: flex; flex-direction: column; gap: 15px; }
        .option-btn { font-family: 'Amiri', serif; font-size: 1.5rem; padding: 15px 20px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; color: white; cursor: pointer; transition: 0.2s; text-align: right; direction: rtl; }
        .option-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); transform: translateX(-5px); }
        .option-btn.correct { background: rgba(46, 204, 113, 0.2); border-color: var(--success); color: var(--success); }
        .option-btn.wrong { background: rgba(231, 76, 60, 0.2); border-color: var(--danger); color: var(--danger); }
        .option-btn:disabled { cursor: not-allowed; opacity: 0.8; }
        
        .next-btn { display: none; width: 100%; padding: 15px; margin-top: 30px; background: var(--accent); border: none; border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="nav"><a href="academic_hub.php">← Back to Hub</a><div style="font-weight: 700; color: var(--accent);">READING COMPREHENSION</div></div>
    <div class="container">
        <div class="top-bar">
            <div class="score-box">XP: <span id="score">0</span></div>
            <div class="score-box">Passage: <span id="q-counter"><?= $questions_completed+1 ?></span>/<?= $quiz_length ?></div>
            <div class="timer-box">⏱ <span id="timer">120</span>s</div>
        </div>
        <div id="loading" style="font-size:1.5rem; opacity:0.6; padding:50px; text-align:center;">Loading...</div>
        <div id="game-area" class="game-card" style="display:none;">
            <div class="paragraph-box">
                <button class="toggle-en-btn" onclick="toggleTranslation()">Show Translation</button>
                <div id="paragraph-ar" class="paragraph-ar"></div>
                <div id="paragraph-en" class="paragraph-en"></div>
            </div>
            <div id="question-ar" class="question-ar"></div>
            <div id="options-container" class="options-grid"></div>
            <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Passage ➔</button>
        </div>
    </div>
    <script>
        const sounds = { correct: new Audio('assets/sounds/correct.mp3'), wrong: new Audio('assets/sounds/wrong.mp3') };
        let currentQuestion = null; let score = 0; let timeLeft = 120; let timerInterval;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>; let questionsAnswered = <?php echo $questions_completed; ?>;

        function startTimer() {
            timeLeft = 120; document.getElementById('timer').textContent = timeLeft;
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
                const response = await fetch('api/get_reading.php'); const data = await response.json();
                if (data.error || data.completed) { document.getElementById('game-area').innerHTML = `<h3 style="text-align:center;">Bank Exhausted</h3><br><a href="academic_hub.php" class="next-btn" style="display:block; text-align:center;">Hub</a>`; document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block'; return; }
                currentQuestion = data; renderQuestion();
            } catch (e) { console.error(e); }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none'; document.getElementById('game-area').style.display = 'block';
            document.getElementById('paragraph-ar').textContent = currentQuestion.paragraph_ar;
            document.getElementById('paragraph-en').textContent = currentQuestion.translation_en;
            document.getElementById('paragraph-en').style.display = 'none';
            document.getElementById('question-ar').textContent = currentQuestion.question_ar;
            
            const container = document.getElementById('options-container'); container.innerHTML = '';
            currentQuestion.options.forEach(opt => {
                const btn = document.createElement('button'); btn.className = 'option-btn'; btn.textContent = opt;
                btn.onclick = () => checkAnswer(btn, opt); container.appendChild(btn);
            });
            questionsAnswered++; document.getElementById('q-counter').textContent = questionsAnswered; startTimer();
        }

        function toggleTranslation() {
            const el = document.getElementById('paragraph-en');
            el.style.display = (el.style.display === 'none') ? 'block' : 'none';
        }

        function handleTimeout() {
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => { b.disabled = true; if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct'); });
            submitResult(false);
        }

        function checkAnswer(btn, selected) {
            clearInterval(timerInterval);
            const buttons = document.querySelectorAll('.option-btn'); buttons.forEach(b => b.disabled = true);
            const isCorrect = (selected === currentQuestion.correct_answer);
            if (isCorrect) { sounds.correct.play(); btn.classList.add('correct'); score += 30; document.getElementById('score').textContent = score; submitResult(true); } 
            else { sounds.wrong.play(); btn.classList.add('wrong'); buttons.forEach(b => { if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct'); }); submitResult(false); }
        }

        async function submitResult(isCorrect) {
            document.getElementById('next-btn').style.display = 'block';
            try { await fetch('api/submit_academic.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mode: 'reading', question_id: currentQuestion.id, is_correct: isCorrect, points: 30 }) }); } catch (e) {}
        }
        window.onload = loadQuestion;
    </script>
</body>
</html>
