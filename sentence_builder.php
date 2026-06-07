<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Scholar';

$stmt = $pdo->prepare("SELECT xp FROM progress WHERE user_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch();
$xp = $stats['xp'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentence Builder — Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg: #0f0c29; --accent: #f2994a; --glass: rgba(255,255,255,0.05);
            --success: #2ecc71; --danger: #e74c3c; --gold: #f1c40f;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white; min-height: 100vh; display: flex; flex-direction: column;
            overflow-x: hidden;
        }

        .header {
            padding: 20px 40px; background: rgba(0,0,0,0.3);
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px);
        }
        .exit-btn { background:none; border:none; color: white; text-decoration: none; font-weight: 700; font-size: 0.9rem; opacity: 0.7; transition: 0.3s; cursor:pointer; }
        .exit-btn:hover { opacity: 1; transform: translateX(-5px); }

        .container {
            max-width: 900px; width: 95%; margin: 40px auto;
            background: var(--glass); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 30px; padding: 40px; backdrop-filter: blur(30px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3); animation: slideUp 0.6s ease;
            text-align: center;
        }

        .label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 3px; color: var(--accent); font-weight: 800; margin-bottom: 15px; display: block; }
        .translation { font-size: 1.5rem; font-weight: 300; margin-bottom: 30px; line-height: 1.4; opacity: 0.9; font-style: italic; }

        .drop-zone {
            min-height: 100px; padding: 20px; background: rgba(0,0,0,0.2);
            border: 2px dashed rgba(255,255,255,0.1); border-radius: 20px;
            display: flex; flex-direction: row-reverse; flex-wrap: wrap;
            justify-content: center; gap: 12px; margin-bottom: 30px;
            transition: 0.3s;
        }
        .drop-zone.active { border-color: var(--accent); background: rgba(242,153,74,0.05); }

        .word-bank {
            display: flex; flex-direction: row-reverse; flex-wrap: wrap;
            justify-content: center; gap: 15px; min-height: 100px;
            padding: 20px;
        }

        .word-chip {
            background: white; color: #333; font-family: 'Amiri', serif;
            font-size: 1.8rem; font-weight: 700; padding: 10px 25px;
            border-radius: 15px; cursor: pointer; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none;
            user-select: none; animation: popIn 0.4s ease;
        }
        .word-chip:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .word-chip.in-sentence { background: var(--accent); color: white; }

        .controls { display: flex; gap: 15px; justify-content: center; margin-top: 20px; }
        .btn {
            padding: 15px 40px; border-radius: 50px; border: none;
            font-weight: 800; cursor: pointer; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-check { background: var(--accent); color: #333; flex: 2; }
        .btn-reset { background: rgba(255,255,255,0.1); color: white; flex: 1; border: 1px solid rgba(255,255,255,0.1); }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }

        .feedback-box {
            display: none; margin-top: 25px; padding: 20px; border-radius: 20px;
            font-weight: 700; animation: fadeIn 0.4s ease;
        }
        .feedback-box.correct { background: rgba(46,204,113,0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .feedback-box.wrong { background: rgba(231,76,60,0.2); color: #ff7675; border: 1px solid #e74c3c; }

        .next-btn {
            display: none; width: 100%; padding: 18px; margin-top: 25px;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            border: none; border-radius: 50px; color: white; font-weight: 800;
            font-size: 1.1rem; cursor: pointer; animation: bounce 1s infinite;
        }

        .speak-btn {
            background: var(--glass); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 10px 20px; border-radius: 50px;
            cursor: pointer; transition: 0.3s; margin-top: 15px; display: inline-flex; align-items: center; gap: 10px;
        }
        .speak-btn:hover { background: rgba(255,255,255,0.15); border-color: var(--accent); }

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
        .modal-btn { flex: 1; padding: 15px; border-radius: 15px; font-weight: 700; cursor: pointer; border: none; transition: 0.3s; }
        .btn-stay { background: var(--accent); color: white; }
        .btn-exit { background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); }

        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        #toast-container { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 1000; }
        .toast { background: #333; color: white; padding: 15px 30px; border-radius: 12px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-top: 10px; animation: slideUp 0.3s ease; }
    </style>
</head>
<body>

    <div class="header">
        <button onclick="confirmExit()" class="exit-btn">← BACK TO SUITE</button>
        <div style="text-align: center;">
            <h3 style="margin:0; font-size: 1.1rem;">Sentence Builder</h3>
            <div style="font-size: 0.7rem; opacity: 0.5;">Faseeh Academic Suite</div>
        </div>
        <div style="font-weight: 800; color: var(--accent);">⭐ <span id="xp-display"><?= $xp ?></span> XP</div>
    </div>

    <div class="container">
        <span class="label">Translate to Arabic</span>
        <div class="translation" id="translation">Loading your challenge...</div>
        <div class="drop-zone" id="sentence-area"></div>
        <div class="word-bank" id="word-bank"></div>
        <div class="controls" id="action-btns">
            <button class="btn btn-reset" onclick="resetWords()">Reset</button>
            <button class="btn btn-check" onclick="checkAnswer()">Verify Sentence</button>
        </div>
        <div id="feedback" class="feedback-box"></div>
        <div style="text-align: center;">
            <button id="speak-btn" class="speak-btn" onclick="speakArabic()" style="display:none;">🔊 Listen to Correct Order</button>
            <button id="next-btn" class="next-btn" onclick="loadQuestion()">Next Sentence →</button>
        </div>
    </div>

    <!-- EXIT CONFIRMATION MODAL -->
    <div id="exitModal" class="modal-overlay">
        <div class="modal-card">
            <div style="font-size: 3rem; margin-bottom: 10px;">🏘️</div>
            <div class="modal-title">Leaving the Hub, <?= htmlspecialchars($username) ?>?</div>
            <div class="modal-desc">
                Your syntax skills are improving! All sentences you've built so far are already saved. Ready to finish the level or heading back to the Suite?
            </div>
            <div class="modal-actions">
                <button class="modal-btn btn-exit" onclick="location.href='academic_hub.php'">Exit Hub</button>
                <button class="modal-btn btn-stay" onclick="document.getElementById('exitModal').style.display='none'">Keep Building</button>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <script>
        let currentData = null;
        let solved = false;

        function showToast(msg) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = msg;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function confirmExit() {
            document.getElementById('exitModal').style.display = 'flex';
        }

        async function loadQuestion() {
            const feedback = document.getElementById('feedback');
            feedback.style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('speak-btn').style.display = 'none';
            document.getElementById('action-btns').style.display = 'flex';
            document.getElementById('sentence-area').innerHTML = '';
            document.getElementById('word-bank').innerHTML = '';
            solved = false;

            try {
                const res = await fetch('api/get_tarkib.php');
                const data = await res.json();
                
                if (data.completed) {
                    document.querySelector('.container').innerHTML = `
                        <div style="padding:40px 0;">
                            <div style="font-size:4rem; margin-bottom:20px;">🏆</div>
                            <h2 class="modal-title" style="color:var(--success);">LEVEL MASTERED!</h2>
                            <p style="opacity:0.7; margin-bottom: 30px;">Fantastic work, ${<?= json_encode($username) ?>}! You've built every sentence in this level perfectly.</p>
                            <a href="academic_hub.php" class="btn btn-check" style="text-decoration:none; display:inline-block;">Return to Suite</a>
                        </div>
                    `;
                    return;
                }
                currentData = data;
                document.getElementById('translation').innerText = `"${data.translation_en}"`;
                const words = JSON.parse(data.scrambled_words);
                words.sort(() => Math.random() - 0.5);
                words.forEach(word => {
                    const chip = document.createElement('button');
                    chip.className = 'word-chip';
                    chip.textContent = word;
                    chip.onclick = () => moveWord(chip);
                    document.getElementById('word-bank').appendChild(chip);
                });
            } catch(e) { console.error(e); }
        }

        function moveWord(chip) {
            if(solved) return;
            const target = (chip.parentElement.id === 'word-bank') ? 'sentence-area' : 'word-bank';
            chip.classList.toggle('in-sentence');
            document.getElementById(target).appendChild(chip);
        }

        function resetWords() {
            Array.from(document.getElementById('sentence-area').children).forEach(c => {
                c.classList.remove('in-sentence');
                document.getElementById('word-bank').appendChild(c);
            });
        }

        async function checkAnswer() {
            const chips = Array.from(document.getElementById('sentence-area').children);
            if(chips.length === 0) { showToast("Build the sentence first! 🤫"); return; }
            const userSentence = chips.map(c => c.textContent).join(' ');
            const isCorrect = (userSentence === currentData.correct_sentence);
            const feedback = document.getElementById('feedback');

            if (isCorrect) {
                solved = true;
                feedback.innerHTML = "✨ Brilliant! Correct Syntax.";
                feedback.className = "feedback-box correct";
                document.getElementById('action-btns').style.display = 'none';
                document.getElementById('next-btn').style.display = 'block';
                document.getElementById('speak-btn').style.display = 'inline-flex';
                
                fetch('api/save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ xp: 20, mode: 'sentence_builder', correct: 1 })
                }).then(r => r.json()).then(res => {
                    if(res.xp_added) showToast("+20 XP Awarded! 🏅");
                });
                speakArabic();
            } else {
                feedback.innerHTML = "❌ Not quite. Check the word order!";
                feedback.className = "feedback-box wrong";
                fetch('api/save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ xp: 0, mode: 'sentence_builder', wrong: 1 })
                });
            }
            feedback.style.display = 'block';
        }

        function speakArabic() {
            if(window.responsiveVoice && currentData) {
                responsiveVoice.speak(currentData.correct_sentence, "Arabic Male");
            }
        }
        loadQuestion();
    </script>
</body>
</html>
