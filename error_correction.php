<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT questions_completed, total_target FROM user_active_sessions WHERE user_id = ? AND mode = 'error_correction'");
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
    <title>Error Correction - Faseeh Academic</title>
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

        .instruction { opacity: 0.7; margin-bottom: 30px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-size: 0.9rem; }
        
        .sentence-display { direction: rtl; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 40px; }
        .word-token { font-family: 'Amiri', serif; font-size: 3rem; font-weight: 700; cursor: pointer; transition: 0.3s; padding: 5px 15px; border-radius: 10px; }
        .word-token:hover { background: rgba(255,255,255,0.1); color: var(--accent); }
        .word-token.selected { background: rgba(231, 76, 60, 0.2); color: var(--danger); border: 2px solid var(--danger); }
        .word-token.success { background: rgba(46, 204, 113, 0.2); color: var(--success); border: 2px solid var(--success); cursor: default; }

        .correction-box { display: none; margin-bottom: 30px; animation: slideUp 0.3s ease; }
        .correction-input { width: 100%; max-width: 300px; padding: 15px; font-family: 'Amiri', serif; font-size: 2rem; text-align: center; border-radius: 15px; border: 2px solid var(--glass-border); background: rgba(0,0,0,0.3); color: white; margin-bottom: 15px; outline: none; }
        .correction-input:focus { border-color: var(--accent); }
        
        .btn-check { padding: 15px 40px; border-radius: 15px; background: var(--success); color: white; font-weight: 700; border: none; cursor: pointer; font-size: 1.1rem; transition: 0.3s; width: 100%; margin-bottom: 10px; }
        .btn-check:hover { background: #27ae60; transform: translateY(-2px); }

        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .opt-btn { background: rgba(255,255,255,0.05); border: 2px solid var(--glass-border); border-radius: 12px; padding: 15px; color: white; font-family: 'Amiri', serif; font-size: 1.8rem; cursor: pointer; transition: 0.3s; }
        .opt-btn:hover { border-color: var(--accent); background: rgba(242,153,74,0.1); }

        .feedback { display: none; margin-top: 20px; text-align: left; background: rgba(0,0,0,0.3); padding: 20px; border-radius: 15px; border-left: 5px solid var(--accent); }
        .feedback-title { font-weight: 700; color: var(--accent); margin-bottom: 5px; font-size: 1.1rem; }
        
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
    <div id="toast">Please correct the error first! 🤫</div>

    <div class="nav">
        <a href="academic_hub.php">← Back to Hub</a>
        <div style="font-weight: 700; color: var(--accent);">ERROR CORRECTION</div>
    </div>

    <div class="game-container" id="game-area">
        <div style="background: rgba(242,153,74,0.1); padding: 15px; border-radius: 15px; margin-bottom: 25px; font-size: 0.85rem;">
            <strong>Step 1:</strong> Click the word that looks wrong.<br>
            <strong>Step 2:</strong> Type the correct version (No vowels/Tashkeel needed!)
        </div>
        
        <div class="sentence-display" id="sentence-display"></div>
        <p style="opacity: 0.6; margin-bottom: 20px;" id="translation"></p>

        <div class="correction-box" id="correction-box">
            <p style="margin-bottom: 5px; font-weight: 600;">You selected: <span id="selected-preview" style="color:var(--danger)"></span></p>
            <p style="margin-bottom: 15px; font-size: 0.85rem; opacity: 0.7;">Select the correct version to fix it:</p>
            <div class="options-grid" id="correction-options"></div>
        </div>

        <div id="feedback" class="feedback">
            <div class="feedback-title" id="fb-title">Incorrect!</div>
            <div id="fb-desc"></div>
            <button id="speak-btn" class="speak-btn" onclick="speakArabic()" title="Listen to correct sentence">🔊</button>
        </div>

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
        let selectedWordToken = null;
        const TOTAL_QUESTIONS = <?php echo $quiz_length; ?>;
        let questionsAnswered = <?php echo $questions_completed; ?>;
        let isCorrectAnswered = false;
        let attemptCount = 0;

        function showToast(msg) {
            const t = document.getElementById("toast");
            t.innerText = msg;
            t.className = "show";
            setTimeout(() => { t.className = t.className.replace("show", ""); }, 3000);
        }

        function giveHint() {
            if(!currentData) return;
            const hintArea = document.getElementById('hint-area');
            const correct = currentData.correct_word;
            // First hint: Length of word
            // Second hint: First letter
            if(attemptCount >= 2 && attemptCount < 4) {
                hintArea.textContent = `Hint: The correct word has ${correct.length} characters.`;
            } else {
                hintArea.textContent = `Hint: It starts with "${correct[0]}"...`;
            }
            hintArea.style.display = 'block';
        }

        // Helper to remove Arabic diacritics and normalize Alifs
        function normalizeArabic(text) {
            if(!text) return "";
            return text
                .replace(/[\u064B-\u0652]/g, "") // Remove all Tashkeel
                .replace(/[أإآ]/g, "ا")           // Normalize Alif
                .replace(/ة/g, "ه")               // Normalize Te Marbuta (optional, but makes it easier)
                .trim();
        }

        async function loadQuestion() {
            if (questionsAnswered >= TOTAL_QUESTIONS) {
                document.getElementById('game-area').innerHTML = `
                    <div style="text-align:center; padding: 40px 0;">
                        <div style="font-size:4rem; margin-bottom:20px;">🏆</div>
                        <h2>Session Complete!</h2>
                        <p style="opacity:0.7; margin-bottom: 20px;">You've corrected ${questionsAnswered} sentences.</p>
                        <a href="academic_hub.php" class="next-btn" style="display:inline-block; width:auto; padding: 15px 40px; text-decoration:none;">Back to Hub</a>
                    </div>
                `;
                return;
            }

            document.getElementById('correction-box').style.display = 'none';
            document.getElementById('feedback').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('hint-btn').style.display = 'none';
            document.getElementById('hint-area').style.display = 'none';
            isCorrectAnswered = false;
            attemptCount = 0;
            document.getElementById('sentence-display').innerHTML = '';
            
            try {
                const res = await fetch('api/get_tashih.php');
                const data = await res.json();
                
                if (data.completed || data.error) {
                    document.querySelector('.game-container').innerHTML = `<h3>Incredible! You've completed all Error Correction modules for this level.</h3><br><a href="academic_hub.php" class="btn-check" style="text-decoration:none; display:inline-block;">Return to Hub</a>`;
                    return;
                }

                currentData = data;
                document.getElementById('translation').textContent = `"${data.translation_en}"`;

                // Split sentence into words and render as clickable tokens
                const words = data.wrong_sentence.split(' ');
                const sentenceBox = document.getElementById('sentence-display');
                words.forEach((word, index) => {
                    const t = document.createElement('span');
                    t.className = 'word-token';
                    t.textContent = word;
                    t.dataset.index = index;
                    t.onclick = () => selectWord(t, word);
                    sentenceBox.appendChild(t);
                });
                
                questionsAnswered++;

            } catch(e) {
                console.error(e);
            }
        }

        function selectWord(span, word) {
            if (isCorrectAnswered) return;
            // Deselect all
            document.querySelectorAll('.word-token').forEach(el => el.classList.remove('selected'));
            
            // Select current
            span.classList.add('selected');
            selectedWordToken = span;
            document.getElementById('selected-preview').textContent = word;

            // Generate 4 options (1 correct, 3 similar but wrong)
            const options = [currentData.correct_word];
            // Generate contextual grammatical variations (gender/number suffixes)
            const baseWord = currentData.correct_word;
            const variations = [
                word, // Original wrong word
                baseWord + 'ة',
                baseWord + 'ون',
                baseWord + 'ات'
            ];
            
            // Build the options grid
            const grid = document.getElementById('correction-options');
            grid.innerHTML = '';
            
            const finalOpts = [...new Set([currentData.correct_word, ...variations])].slice(0, 4);
            // Shuffle
            finalOpts.sort(() => Math.random() - 0.5);

            finalOpts.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'opt-btn';
                btn.textContent = opt;
                btn.onclick = () => checkCorrection(opt);
                grid.appendChild(btn);
            });

            // Show input box
            const box = document.getElementById('correction-box');
            box.style.display = 'block';
        }

        async function checkCorrection(selectedOption) {
            const selectedWord = selectedWordToken ? selectedWordToken.textContent : '';
            
            const feedback = document.getElementById('feedback');
            const fbTitle = document.getElementById('fb-title');
            const fbDesc = document.getElementById('fb-desc');

            // 1. Did they select the correct wrong word?
            if (selectedWord !== currentData.wrong_word) {
                playSound('wrong');
                fbTitle.textContent = "Wrong Target!";
                fbTitle.style.color = "var(--danger)";
                feedback.style.borderLeftColor = "var(--danger)";
                fbDesc.textContent = "That word is actually grammatically correct. Try finding the real error.";
                feedback.style.display = 'block';
                return;
            }

            if (selectedOption === currentData.correct_word) {
                // Success!
                playSound('correct');
                document.getElementById('correction-box').style.display = 'none';
                selectedWordToken.classList.remove('selected');
                selectedWordToken.classList.add('success');
                selectedWordToken.textContent = currentData.correct_word; // visually fix it

                fbDesc.textContent = currentData.grammar_rule;
                
                document.getElementById('next-btn').style.display = 'block';
                isCorrectAnswered = true;
                
                // Add XP (Higher XP for this hard mode)
                await fetch('api/submit_academic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'tashih', question_id: currentData.id, is_correct: true, points: 25 })
                });

            } else {
                attemptCount++;
                playSound('wrong');
                fbTitle.textContent = "Almost there!";
                fbTitle.style.color = "var(--accent)";
                feedback.style.borderLeftColor = "var(--accent)";
                fbDesc.textContent = "That's not the right form. Look at the subject for clues and try again!";

                if(attemptCount >= 2) {
                    document.getElementById('hint-btn').style.display = 'block';
                }
            }
            
            feedback.style.display = 'block';
        }

        function speakArabic() {
            if(!currentData) return;
            if(!isCorrectAnswered) {
                showToast("Correct the error first to unlock the audio! 🤫");
                return;
            }
            const correctSentence = currentData.wrong_sentence.replace(currentData.wrong_word, currentData.correct_word);
            if(window.responsiveVoice) {
                responsiveVoice.speak(correctSentence, "Arabic Male");
            }
        }

        loadQuestion();
    </script>
</body>
</html>
