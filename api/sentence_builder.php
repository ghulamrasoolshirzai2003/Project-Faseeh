<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'sentence_builder'");
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
    <title>Sentence Builder - Faseeh Academic</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
        .nav a:hover { background: var(--glass); }

        .game-container { width: 100%; max-width: 800px; margin-top: 40px; padding: 40px; background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center; }

        .translation-box { font-size: 1.2rem; opacity: 0.8; margin-bottom: 30px; font-weight: 300; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 15px; }

        .drop-area { display: flex; flex-direction: row-reverse; flex-wrap: wrap; justify-content: center; gap: 10px; min-height: 80px; padding: 15px; background: rgba(255,255,255,0.05); border: 2px dashed var(--glass-border); border-radius: 15px; margin-bottom: 30px; }
        
        .word-bank { display: flex; flex-direction: row-reverse; flex-wrap: wrap; justify-content: center; gap: 15px; min-height: 80px; }

        .word-chip { background: var(--accent); color: white; font-family: 'Amiri', serif; font-size: 1.8rem; font-weight: 700; padding: 10px 25px; border-radius: 12px; cursor: pointer; transition: 0.2s; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none; }
        .word-chip:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 8px 20px rgba(242,153,74,0.4); }

        .action-btns { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        .btn { padding: 15px 30px; border-radius: 15px; border: none; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; }
        .btn-check { background: var(--success); color: white; flex: 2; }
        .btn-check:hover { background: #27ae60; transform: translateY(-2px); }
        .btn-reset { background: rgba(255,255,255,0.1); color: white; flex: 1; }
        .btn-reset:hover { background: rgba(255,255,255,0.2); }

        .feedback { display: none; margin-top: 20px; font-weight: 700; font-size: 1.2rem; padding: 15px; border-radius: 10px; }
        .feedback.correct { background: rgba(46, 204, 113, 0.2); color: var(--success); }
        .feedback.wrong { background: rgba(231, 76, 60, 0.2); color: var(--danger); }

        .speak-btn { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 50%; width: 50px; height: 50px; font-size: 1.5rem; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; margin-top: 15px; }
        .speak-btn:hover { transform: scale(1.1); background: rgba(255,255,255,0.1); }

        .next-btn { display: none; width: 100%; padding: 15px; margin-top: 20px; background: linear-gradient(to right, var(--accent), #f2c94c); border: none; border-radius: 15px; color: white; font-weight: 700; font-size: 1.1rem; cursor: pointer; }
        
        /* Toast notification */
        #toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 10px; padding: 16px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>
    <div id="toast">Please build the sentence first! 🤫</div>

    <div class="nav">
        <a href="academic_hub.php">← Back to Hub</a>
        <div style="font-weight: 700; color: var(--accent);">SENTENCE BUILDER</div>
    </div>

    <div class="game-container" id="game-area">
        <div class="translation-box" id="translation">Loading...</div>

        <!-- Sentence Area -->
        <div class="drop-area" id="sentence-area"></div>

        <!-- Word Bank -->
        <div class="word-bank" id="word-bank"></div>

        <div class="action-btns" id="action-btns">
            <button class="btn btn-reset" onclick="resetWords()">Reset</button>
            <button class="btn btn-check" onclick="checkAnswer()">Check Answer</button>
        </div>

        <div id="feedback" class="feedback"></div>
        <button id="speak-btn" class="speak-btn" onclick="speakArabic()" title="Listen to correct sentence">🔊</button>
        <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Sentence →</button>
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

        let currentData = null;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>;
        let questionsAnswered = <?php echo $questions_completed; ?>;
        let isCorrectAnswered = false;

        function showToast(msg) {
            const t = document.getElementById("toast");
            t.innerText = msg;
            t.className = "show";
            setTimeout(() => { t.className = t.className.replace("show", ""); }, 3000);
        }

        async function loadQuestion() {
            if (questionsAnswered >= TOTAL_QUESTIONS) {
                document.getElementById('game-area').innerHTML = `
                    <div style="text-align:center; padding: 40px 0;">
                        <div style="font-size:4rem; margin-bottom:20px;">🏆</div>
                        <h2>Session Complete!</h2>
                        <p style="opacity:0.7; margin-bottom: 20px;">You've built ${questionsAnswered} sentences.</p>
                        <a href="academic_hub.php" class="next-btn" style="display:inline-block; width:auto; padding: 15px 40px; text-decoration:none;">Back to Hub</a>
                    </div>
                `;
                return;
            }

            document.getElementById('feedback').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('action-btns').style.display = 'flex';
            isCorrectAnswered = false;
            document.getElementById('sentence-area').innerHTML = '';
            document.getElementById('word-bank').innerHTML = '';

            try {
                const res = await fetch('api/get_tarkib.php');
                const data = await res.json();
                
                if (data.completed || data.error) {
                    document.querySelector('.game-container').innerHTML = `<h3>Awesome! You've mastered all current sentence building exercises for this level.</h3><br><a href="academic_hub.php" class="btn btn-check" style="text-decoration:none; display:inline-block;">Return to Hub</a>`;
                    return;
                }

                currentData = data;
                document.getElementById('translation').textContent = `"${data.translation_en}"`;

                const words = JSON.parse(data.scrambled_words);
                words.forEach(word => {
                    const btn = document.createElement('button');
                    btn.className = 'word-chip';
                    btn.textContent = word;
                    btn.onclick = () => moveWord(btn);
                    document.getElementById('word-bank').appendChild(btn);
                });

            } catch(e) {
                console.error(e);
            }
        }

        function moveWord(btn) {
            const sentenceArea = document.getElementById('sentence-area');
            const wordBank = document.getElementById('word-bank');
            
            // Note: Since Arabic is RTL, we append to sentence area, and CSS flex-direction: row-reverse handles visual order!
            if (btn.parentElement.id === 'word-bank') {
                sentenceArea.appendChild(btn);
            } else {
                wordBank.appendChild(btn);
            }
        }

        function resetWords() {
            const sentenceArea = document.getElementById('sentence-area');
            const wordBank = document.getElementById('word-bank');
            while(sentenceArea.firstChild) {
                wordBank.appendChild(sentenceArea.firstChild);
            }
        }

        async function checkAnswer() {
            const sentenceArea = document.getElementById('sentence-area');
            const chips = Array.from(sentenceArea.children);
            
            // If empty, do nothing
            if(chips.length === 0) return;

            // Gather the words in the order they appear in the DOM
            // Since it's row-reverse, the first child in DOM is actually the right-most word visually
            // But Arabic text order in string is right-to-left. 
            // So joining them with space matching DOM order creates the correct Arabic string order.
            const userSentence = chips.map(c => c.textContent).join(' ');
            
            const isCorrect = (userSentence === currentData.correct_sentence);
            const feedback = document.getElementById('feedback');

            if (isCorrect) {
                playSound('correct');
                feedback.textContent = "✅ Excellent! Correct Syntax.";
                feedback.className = "feedback correct";
                document.getElementById('action-btns').style.display = 'none';
                document.getElementById('next-btn').style.display = 'block';
                isCorrectAnswered = true;
                
                // Add XP
                await fetch('api/submit_academic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'tarkib', question_id: currentData.id, is_correct: true, points: 20 })
                });

            } else {
                playSound('wrong');
                feedback.textContent = "❌ Incorrect order. Try again!";
                feedback.className = "feedback wrong";
            }
            feedback.style.display = 'block';
        }

        function speakArabic() {
            if(!currentData) return;
            if(!isCorrectAnswered) {
                showToast("Build the sentence correctly first to unlock audio! 🤫");
                return;
            }
            if(window.responsiveVoice) {
                responsiveVoice.speak(currentData.correct_sentence, "Arabic Male");
            }
        }

        loadQuestion();
    </script>
</body>
</html>
