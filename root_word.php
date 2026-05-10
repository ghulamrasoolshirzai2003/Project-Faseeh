<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];

// Load or initialize persistence
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'root_word'");
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
    <title>Root Word Finder - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; }
        
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 20px; text-align: center; flex: 1; width: 100%; }
        
        .top-bar { display: flex; justify-content: space-between; margin-bottom: 30px; font-weight: 600; }
        .score-box, .timer-box { background: var(--glass); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--glass-border); }
        .timer-box { color: var(--accent); }

        .game-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); position: relative; }
        
        .question-box { margin-bottom: 40px; }
        .rule-badge { display: inline-block; background: rgba(242, 153, 74, 0.2); color: var(--accent); padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 15px; border: 1px solid rgba(242, 153, 74, 0.3); }
        .sentence-ar { font-family: 'Amiri', serif; font-size: 4rem; line-height: 1.6; margin-bottom: 15px; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .sentence-en { font-size: 1.2rem; opacity: 0.7; font-weight: 300; }

        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .option-btn { font-family: 'Amiri', serif; font-size: 2.2rem; padding: 20px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; color: white; cursor: pointer; transition: 0.2s; }
        .option-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); transform: translateY(-3px); }
        .option-btn.correct { background: rgba(46, 204, 113, 0.2); border-color: var(--success); color: var(--success); }
        .option-btn.wrong { background: rgba(231, 76, 60, 0.2); border-color: var(--danger); color: var(--danger); }
        .option-btn:disabled { cursor: not-allowed; opacity: 0.8; }

        .next-btn { display: none; width: 100%; padding: 15px; margin-top: 20px; background: var(--accent); border: none; border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .next-btn:hover { background: #e67e22; box-shadow: 0 5px 15px rgba(230,126,34,0.4); }

        .speak-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.7; transition: 0.3s; padding: 10px; border-radius: 50%; }
        .speak-btn:hover { opacity: 1; background: var(--glass); }

        .loading { font-size: 1.5rem; opacity: 0.6; padding: 50px; }
    </style>
</head>
<body>

    <div class="nav">
        <a href="academic_hub.php">← Back to Hub</a>
        <div style="font-weight: 700; color: var(--accent);">ROOT WORD FINDER</div>
    </div>

    <div class="container">
        <div class="top-bar">
            <div class="score-box">XP: <span id="score">0</span></div>
            <div class="score-box">Question: <span id="q-counter"><?= $questions_completed+1 ?></span>/<?= $quiz_length ?></div>
            <div class="timer-box">⏱ <span id="timer">20</span>s</div>
        </div>

        <div id="loading" class="loading">Loading question...</div>

        <div id="game-area" class="game-card" style="display:none;">
            <div class="question-box">
                <div class="rule-badge">Extract the 3-letter Root</div>
                <div style="display:flex; justify-content:center; align-items:center; gap:15px;">
                    <div id="sentence-ar" class="sentence-ar"></div>
                    <button id="speak-btn" class="speak-btn" onclick="speakArabic()" title="Listen">🔊</button>
                </div>
                <div id="sentence-en" class="sentence-en"></div>
            </div>

            <div id="options-container" class="options-grid"></div>

            <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Question ➔</button>
        </div>
    </div>

    <script>
        const sounds = { correct: new Audio('assets/sounds/correct.mp3'), wrong: new Audio('assets/sounds/wrong.mp3') };
        const playSound = (name) => { const s = sounds[name]; if (s) { s.currentTime = 0; s.play().catch(()=>{}); } };

        let currentQuestion = null;
        let score = 0;
        let timeLeft = 20;
        let timerInterval;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>;
        let questionsAnswered = <?php echo $questions_completed; ?>;
        let isAnswered = false;

        function startTimer() {
            timeLeft = 20; document.getElementById('timer').textContent = timeLeft;
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--; document.getElementById('timer').textContent = timeLeft;
                if(timeLeft <= 0) { clearInterval(timerInterval); handleTimeout(); }
            }, 1000);
        }

        async function loadQuestion() {
            if (questionsAnswered >= TOTAL_QUESTIONS) {
                document.getElementById('game-area').style.display = 'block';
                document.getElementById('game-area').innerHTML = `
                    <div style="text-align:center; padding: 40px 0;">
                        <div style="font-size:4rem; margin-bottom:20px;">🏆</div>
                        <h2>Session Complete!</h2>
                        <p style="opacity:0.7; margin-bottom: 20px;">You've completed ${questionsAnswered} questions.</p>
                        <a href="academic_hub.php" class="next-btn" style="display:inline-block; width:auto; padding: 15px 40px; text-decoration:none;">Back to Hub</a>
                    </div>`;
                return;
            }

            document.getElementById('loading').style.display = 'block';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';

            try {
                const response = await fetch('api/get_root_word.php');
                const data = await response.json();

                if (data.error || data.completed) {
                    clearInterval(timerInterval);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('game-area').style.display = 'block';
                    document.getElementById('game-area').innerHTML = `<h3>Awesome! You've exhausted the question bank for this level.</h3><br><a href="academic_hub.php" class="next-btn" style="display:block; text-align:center; text-decoration:none;">Return to Hub</a>`;
                    return;
                }
                currentQuestion = data;
                renderQuestion();
            } catch (error) { console.error(error); }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';
            document.getElementById('sentence-ar').textContent = currentQuestion.complex_word;
            document.getElementById('sentence-en').textContent = currentQuestion.translation_en;
            isAnswered = false;

            const container = document.getElementById('options-container');
            container.innerHTML = '';

            currentQuestion.options.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                btn.textContent = opt;
                btn.onclick = () => checkAnswer(btn, opt);
                container.appendChild(btn);
            });
            
            questionsAnswered++;
            document.getElementById('q-counter').textContent = questionsAnswered;
            startTimer();
        }

        function handleTimeout() {
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => {
                b.disabled = true;
                if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct');
            });
            submitResult(false);
        }

        function checkAnswer(btn, selected) {
            clearInterval(timerInterval);
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => b.disabled = true);
            document.getElementById('next-btn').style.display = 'block';
            isAnswered = true;

            const isCorrect = (selected === currentQuestion.correct_answer);

            if (isCorrect) {
                playSound('correct'); btn.classList.add('correct');
                score += 15; document.getElementById('score').textContent = score;
                submitResult(true);
            } else {
                playSound('wrong'); btn.classList.add('wrong');
                buttons.forEach(b => { if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct'); });
                submitResult(false);
            }
        }

        function speakArabic() {
            if(responsiveVoice.isPlaying()) responsiveVoice.cancel();
            responsiveVoice.speak(currentQuestion.complex_word, "Arabic Male", {rate: 0.9});
        }

        async function submitResult(isCorrect) {
            document.getElementById('next-btn').style.display = 'block';
            try {
                await fetch('api/submit_academic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'root_word', question_id: currentQuestion.id, is_correct: isCorrect, points: 15 })
                });
            } catch (e) { console.error("Failed to submit result"); }
        }

        window.onload = loadQuestion;
    </script>
</body>
</html>
