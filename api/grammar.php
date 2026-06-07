<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Grammar - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light));
            color: white; min-height: 100vh; display: flex; flex-direction: column; align-items: center;
        }

        .nav {
            width: 100%; padding: 20px 40px; display: flex; justify-content: space-between;
            align-items: center; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav a { color: white; text-decoration: none; font-weight: 600; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        .nav a:hover { background: var(--glass); }

        .game-container {
            width: 100%; max-width: 800px; margin-top: 40px; padding: 40px;
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center;
        }

        .header-section { margin-bottom: 30px; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: rgba(242,153,74,0.2); color: var(--accent); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
        
        .sentence-box { margin-bottom: 40px; }
        .arabic-sentence { font-family: 'Amiri', serif; font-size: 3.5rem; font-weight: 700; line-height: 1.6; direction: rtl; margin-bottom: 10px; }
        .english-translation { font-size: 1.1rem; opacity: 0.7; font-weight: 300; }

        .options-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;
        }

        .option-btn {
            background: rgba(0,0,0,0.3); border: 2px solid var(--glass-border);
            border-radius: 15px; padding: 20px; font-family: 'Amiri', serif;
            font-size: 2rem; font-weight: 700; color: white; cursor: pointer;
            transition: all 0.3s ease;
        }
        .option-btn:hover { border-color: var(--accent); background: rgba(242,153,74,0.1); transform: translateY(-3px); }
        .option-btn.correct { background: var(--success); border-color: var(--success); }
        .option-btn.wrong { background: var(--danger); border-color: var(--danger); }

        .feedback-area { min-height: 60px; }
        .rule-explanation {
            display: none; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 12px;
            border-left: 4px solid var(--accent); font-size: 0.95rem; line-height: 1.5; text-align: left;
        }

        .next-btn {
            display: none; width: 100%; padding: 16px; border-radius: 15px; border: none;
            background: linear-gradient(to right, var(--accent), #f2c94c);
            color: white; font-weight: 700; font-size: 1.1rem; cursor: pointer;
            transition: 0.3s; margin-top: 20px;
        }
        .next-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(242,153,74,0.3); }

        /* Loader */
        .loader { border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid var(--accent); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        @media (max-width: 600px) {
            .options-grid { grid-template-columns: 1fr; }
            .arabic-sentence { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <div class="nav">
        <a href="dashboard.php">← Back to Dashboard</a>
        <div style="font-weight: 700; color: var(--accent);">FASEEH ACADEMIC</div>
    </div>

    <div class="game-container">
        <div class="header-section">
            <div class="badge" id="rule-badge">Loading Grammar Rule...</div>
        </div>

        <div id="loading" class="loader"></div>

        <div id="game-area" style="display: none;">
            <div class="sentence-box">
                <div class="arabic-sentence" id="sentence-ar"></div>
                <div class="english-translation" id="sentence-en"></div>
            </div>

            <div class="options-grid" id="options-container">
                <!-- Buttons injected via JS -->
            </div>

            <div class="feedback-area">
                <div class="rule-explanation" id="explanation"></div>
            </div>

            <button class="next-btn" id="next-btn" onclick="loadQuestion()">Next Question →</button>
        </div>
    </div>

    <script>
        let currentQuestion = null;

        async function loadQuestion() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('explanation').style.display = 'none';

            try {
                const response = await fetch('api/get_grammar.php');
                const data = await response.json();

                if (data.error || data.completed) {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('game-area').style.display = 'block';
                    document.getElementById('game-area').innerHTML = `<h3>Awesome! You've completed all current grammar exercises.</h3><br><a href="dashboard.php" class="next-btn" style="display:block; text-align:center; text-decoration:none;">Return to Dashboard</a>`;
                    return;
                }

                currentQuestion = data;
                renderQuestion();
            } catch (error) {
                console.error("Error fetching question:", error);
            }
        }

        function renderQuestion() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';
            
            document.getElementById('rule-badge').textContent = currentQuestion.grammar_rule;
            document.getElementById('sentence-ar').textContent = currentQuestion.sentence_ar;
            document.getElementById('sentence-en').textContent = currentQuestion.translation_en;

            const container = document.getElementById('options-container');
            container.innerHTML = '';

            currentQuestion.options.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                btn.textContent = opt;
                btn.onclick = () => checkAnswer(btn, opt);
                container.appendChild(btn);
            });
        }

        function checkAnswer(btn, selected) {
            // Disable all buttons
            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(b => b.disabled = true);

            const isCorrect = (selected === currentQuestion.correct_answer);

            if (isCorrect) {
                btn.classList.add('correct');
                // Fill the blank in the sentence visually
                const sentenceEl = document.getElementById('sentence-ar');
                sentenceEl.innerHTML = currentQuestion.sentence_ar.replace('___', `<span style="color:var(--success); border-bottom: 2px solid var(--success); padding: 0 10px;">${selected}</span>`);
                submitResult(true);
            } else {
                btn.classList.add('wrong');
                // Highlight correct button
                buttons.forEach(b => {
                    if (b.textContent === currentQuestion.correct_answer) b.classList.add('correct');
                });
                submitResult(false);
            }

            // Show explanation
            const exp = document.getElementById('explanation');
            exp.innerHTML = `<strong>Rule Context:</strong> ${currentQuestion.grammar_rule}. <br>Correct answer: <strong>${currentQuestion.correct_answer}</strong>`;
            exp.style.display = 'block';

            document.getElementById('next-btn').style.display = 'block';
        }

        async function submitResult(isCorrect) {
            // Send xp update to backend (15 XP for grammar questions)
            if(isCorrect) {
                await fetch('api/submit_mcq.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_correct: true, points: 15, is_grammar: true })
                });
            }
        }

        // Start game
        loadQuestion();
    </script>
</body>
</html>
