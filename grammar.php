<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];

// Load or initialize persistence
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'grammar'");
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
    <title>Academic Grammar - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; align-items: center; }

        .nav { width: 100%; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px); border-bottom: 1px solid var(--glass-border); }
        .nav a { color: white; text-decoration: none; font-weight: 600; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        
        /* Stats & Timer Bar */
        .hud { width: 100%; max-width: 800px; display: flex; justify-content: space-between; margin-top: 20px; padding: 0 20px; font-weight: 700; }
        .score-box { background: rgba(242,153,74,0.2); color: var(--accent); padding: 10px 20px; border-radius: 15px; }
        .timer-box { background: rgba(231,76,60,0.2); color: var(--danger); padding: 10px 20px; border-radius: 15px; display: flex; align-items: center; gap: 8px; }

        .game-container { width: 100%; max-width: 800px; margin-top: 20px; padding: 40px; background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center; }
        .header-section { margin-bottom: 30px; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: rgba(255,255,255,0.1); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        
        .sentence-box { margin-bottom: 40px; }
        .arabic-sentence { font-family: 'Amiri', serif; font-size: 3.5rem; font-weight: 700; line-height: 1.6; direction: rtl; margin-bottom: 10px; }
        .english-translation { font-size: 1.1rem; opacity: 0.7; font-weight: 300; }

        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .option-btn { background: rgba(0,0,0,0.3); border: 2px solid var(--glass-border); border-radius: 15px; padding: 20px; font-family: 'Amiri', serif; font-size: 2rem; font-weight: 700; color: white; cursor: pointer; transition: all 0.3s ease; }
        .option-btn:hover { border-color: var(--accent); transform: translateY(-3px); }
        .option-btn.correct { background: var(--success); border-color: var(--success); }
        .option-btn.wrong { background: var(--danger); border-color: var(--danger); }

        .feedback-area { min-height: 60px; }
        .rule-explanation { display: none; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 12px; border-left: 4px solid var(--accent); font-size: 0.95rem; line-height: 1.5; text-align: left; }

        .next-btn { display: none; width: 100%; padding: 16px; border-radius: 15px; border: none; background: linear-gradient(to right, var(--accent), #f2c94c); color: white; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: 0.3s; margin-top: 20px; }
        .loader { border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid var(--accent); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
        
        .speak-btn { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; transition: 0.2s; margin-right: 15px; display: inline-flex; align-items: center; justify-content: center; }
        .speak-btn:hover { transform: scale(1.1); background: rgba(255,255,255,0.1); }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Toast notification */
        #toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 10px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>
    <div id="toast">Please answer the question first! 🤫</div>

    <div class="nav">
        <a href="academic_hub.php">← Hub</a>
        <div style="font-weight: 700; color: var(--accent);">GRAMMAR MODE</div>
    </div>

    <div class="hud">
        <div class="score-box">Score: <span id="score">0</span></div>
        <div class="timer-box">⏱️ <span id="timer">20</span>s</div>
    </div>

    <div class="game-container">
        <div id="loading" class="loader"></div>
        <div id="game-area" style="display: none;">
            <div class="header-section"><div class="badge" id="rule-badge"></div></div>
            <div class="sentence-box">
                <div class="arabic-sentence">
                    <span id="sentence-ar"></span>
                    <button id="speak-btn" class="speak-btn" onclick="speakArabic()" title="Listen">🔊</button>
                </div>
                <div class="english-translation" id="sentence-en"></div>
            </div>
            <div class="options-grid" id="options-container"></div>
            <div class="feedback-area"><div class="rule-explanation" id="explanation"></div></div>
            <button class="next-btn" id="next-btn" onclick="loadQuestion()">Next Question →</button>
        </div>
    </div>

    <script>
        const sounds = {
            correct: new Audio('assets/sounds/correct.mp3'),
            wrong:   new Audio('assets/sounds/wrong.mp3')
        };

        const playSound = (name) => {
            const s = sounds[name];
            if (s) { s.currentTime = 0; s.play().catch(()=>{}); }
        };

        let currentQuestion = null;
        let score = 0;
        let timeLeft = 20;
        let timerInterval;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>;
        let questionsAnswered = <?php echo $questions_completed; ?>;
        let isAnswered = false;

        function showToast(msg) {
            const t = document.getElementById("toast");
            t.innerText = msg;
            t.className = "show";
            setTimeout(() => { t.className = t.className.replace("show", ""); }, 3000);
        }

        function startTimer() {
            timeLeft = 20;
            document.getElementById('timer').textContent = timeLeft;
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                if(timeLeft <= 0) {
                    clearInterval(timerInterval);
                    handleTimeout();
                }
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
                    </div>
                `;
                return;
            }

            document.getElementById('loading').style.display = 'block';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('explanation').style.display = 'none';

            try {
                const response = await fetch('api/get_grammar.php');
                const data = await response.json();

                if (data.error || data.completed) {
                    clearInterval(timerInterval);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('game-area').style.display = 'block';
                    document.getElementById('game-area').innerHTML = `<h3>Awesome! You've exhausted the question bank for this level.</h3><p>Your Final Score: ${score}</p><br><a href="academic_hub.php" class="next-btn" style="display:block; text-align:center; text-decoration:none;">Return to Hub</a>`;
                    return;
                }

                currentQuestion = data;
                renderQuestion();
            } catch (error) { console.error(error); }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';
            document.getElementById('rule-badge').textContent = currentQuestion.grammar_rule;
            document.getElementById('sentence-ar').textContent = currentQuestion.sentence_ar;
            
            let trans = currentQuestion.translation_en;
            if (trans.includes('[')) {
                currentQuestion.full_en = trans.replace('[', '').replace(']', '');
                document.getElementById('sentence-en').innerHTML = `<span style="opacity: 0.5">[ ... ]</span>` + trans.split(']')[1];
            } else {
                currentQuestion.full_en = trans;
                document.getElementById('sentence-en').textContent = trans;
            }
            
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
            startTimer();
        }

        function handleTimeout() {
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => {
                b.disabled = true;
                if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct');
            });
            submitResult(false);
            if(currentQuestion.full_en) document.getElementById('sentence-en').textContent = currentQuestion.full_en;
            showExplanation();
        }

        function checkAnswer(btn, selected) {
            clearInterval(timerInterval);
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => b.disabled = true);
            
            document.getElementById('next-btn').style.display = 'block';
            isAnswered = true;

            const isCorrect = (selected === currentQuestion.correct_answer);

            if (isCorrect) {
                playSound('correct');
                btn.classList.add('correct');
                const sentenceEl = document.getElementById('sentence-ar');
                sentenceEl.innerHTML = currentQuestion.sentence_ar.replace('___', `<span style="color:var(--success); border-bottom: 2px solid var(--success); padding: 0 10px;">${selected}</span>`);
                score += 15;
                document.getElementById('score').textContent = score;
                submitResult(true);
            } else {
                playSound('wrong');
                btn.classList.add('wrong');
                buttons.forEach(b => { if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct'); });
                submitResult(false);
            }
            
            if(currentQuestion.full_en) document.getElementById('sentence-en').textContent = currentQuestion.full_en;
            showExplanation();
        }

        function showExplanation() {
            const exp = document.getElementById('explanation');
            exp.innerHTML = `<strong>Rule Context:</strong> ${currentQuestion.grammar_rule}. <br>Correct answer: <strong>${currentQuestion.correct_answer}</strong>`;
            exp.style.display = 'block';
            document.getElementById('next-btn').style.display = 'block';
        }

        async function submitResult(isCorrect) {
            await fetch('api/submit_academic.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    mode: 'grammar', 
                    question_id: currentQuestion.id, 
                    is_correct: isCorrect, 
                    points: 15 
                })
            });
        }

        function speakArabic() {
            if(!currentQuestion) return;
            if(!isAnswered) {
                showToast("Answer the question first to unlock the audio! 🤫");
                return;
            }
            const fullSentence = currentQuestion.sentence_ar.replace('___', currentQuestion.correct_answer);
            
            // Use ResponsiveVoice for guaranteed Arabic audio fallback
            if(window.responsiveVoice) {
                responsiveVoice.speak(fullSentence, "Arabic Male");
            } else {
                console.error("ResponsiveVoice not loaded.");
            }
        }

        loadQuestion();
    </script>
</body>
</html>
