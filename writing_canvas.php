<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Calligrapher';
$letters = ['أ', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'هـ', 'و', 'ي'];
$target = $_GET['letter'] ?? 'أ';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Writing Atelier</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            margin: 0; font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white; height: 100vh; overflow: hidden;
            display: flex; flex-direction: column;
        }
        .canvas-header {
            padding: 15px 40px; background: rgba(0,0,0,0.3);
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .exit-btn { background:none; border:none; color:white; text-decoration:none; font-weight:700; cursor:pointer; }
        .main-layout { display: flex; flex: 1; overflow: hidden; }
        
        .letter-grid {
            width: 250px; padding: 15px; background: rgba(0,0,0,0.2);
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            overflow-y: auto; border-right: 1px solid rgba(255,255,255,0.1);
        }
        .letter-btn {
            background: var(--glass); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: white; padding: 10px; font-family: 'Amiri';
            font-size: 1.5rem; cursor: pointer; transition: 0.2s; text-align: center;
            text-decoration: none;
        }
        .letter-btn:hover, .letter-btn.active {
            background: var(--accent); color: #333; border-color: var(--accent);
        }

        .canvas-area {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: flex-start; padding: 20px; position: relative;
        }
        #writing-canvas {
            background: white; border-radius: 20px; cursor: crosshair;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            touch-action: none;
        }
        .canvas-tools {
            margin-top: 20px; display: flex; gap: 15px; z-index: 10;
        }
        .btn-tool {
            padding: 10px 25px; border-radius: 50px; border: none;
            font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 0.9rem;
        }
        .btn-clear { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); }
        .btn-submit { background: var(--accent); color: #333; }
        .btn-tool:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        .tutor-panel {
            width: 300px; padding: 30px; background: rgba(0,0,0,0.3);
            border-left: 1px solid rgba(255,255,255,0.1);
            display: flex; flex-direction: column; gap: 15px;
        }
        .target-display {
            background: var(--glass); border-radius: 20px; padding: 20px;
            text-align: center; border: 1px solid rgba(255,255,255,0.1);
            display: flex; flex-direction: column; align-items: center; gap: 10px;
        }
        .target-letter { font-family: 'Amiri'; font-size: 5rem; line-height: 1; margin: 0; }
        .feedback-box {
            background: rgba(46,204,113,0.1); border: 1px solid #2ecc71;
            border-radius: 15px; padding: 15px; font-size: 0.85rem;
            display: none; animation: fadeIn 0.4s ease;
        }
        .feedback-box.error { background: rgba(231,76,60,0.1); border-color: #e74c3c; color: #ff7675; }

        #writing-canvas.ghost {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='450'><text x='50%' y='55%' font-family='Amiri, serif' font-size='250' text-anchor='middle' fill='rgba(0,0,0,0.05)'><?= $target ?></text></svg>");
            background-repeat: no-repeat; background-position: center;
        }

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
        .btn-stay { background: var(--accent); color: #333; }
        .btn-exit { background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>

    <div class="canvas-header">
        <button onclick="confirmExit()" class="exit-btn">← Exit to Suite</button>
        <h3 style="margin:0;">Faseeh Writing Atelier</h3>
        <div style="opacity:0.6; font-size:0.8rem;">Master Calligraphy</div>
    </div>

    <div class="main-layout">
        <div class="letter-grid">
            <?php foreach($letters as $l): ?>
                <a href="?letter=<?= urlencode($l) ?>" class="letter-btn <?= ($target == $l) ? 'active' : '' ?>">
                    <?= $l ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="canvas-area">
            <canvas id="writing-canvas" width="600" height="420" class="ghost"></canvas>
            <div class="canvas-tools">
                <button class="btn-tool btn-clear" onclick="clearCanvas()">Clear</button>
                <button id="ghost-toggle" class="btn-tool btn-clear" onclick="toggleGhost()" style="background:var(--accent); color:#333;">Hide Ghost</button>
                <button class="btn-tool btn-submit" onclick="submitDrawing()">Analyze</button>
            </div>
        </div>
        <div class="tutor-panel">
            <div class="target-display">
                <div style="font-size:0.65rem; opacity:0.5; text-transform:uppercase;">Practice This</div>
                <div class="target-letter"><?= $target ?></div>
                <button class="btn-tool btn-clear" onclick="responsiveVoice.speak('<?= $target ?>', 'Arabic Male')" style="padding:5px 12px; font-size:0.75rem;">🔊 Listen</button>
            </div>
            <div id="feedback" class="feedback-box">
                <h4 id="feedback-title" style="margin:0 0 8px 0; color:#2ecc71;">✨ Feedback</h4>
                <p id="feedback-text" style="margin:0; line-height:1.4;"></p>
            </div>
        </div>
    </div>

    <!-- EXIT CONFIRMATION MODAL -->
    <div id="exitModal" class="modal-overlay">
        <div class="modal-card">
            <div style="font-size: 3rem; margin-bottom: 10px;">🎨</div>
            <div class="modal-title">Stopping so soon, <?= htmlspecialchars($username) ?>?</div>
            <div class="modal-desc">
                Calligraphy is a meditative art! Your practice so far is already recorded. Ready to head back to the Hub or want to master one more letter?
            </div>
            <div class="modal-actions">
                <button class="modal-btn btn-exit" onclick="location.href='academic_hub.php'">Exit Atelier</button>
                <button class="modal-btn btn-stay" onclick="document.getElementById('exitModal').style.display='none'">Keep Practicing</button>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('writing-canvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        function confirmExit() { document.getElementById('exitModal').style.display = 'flex'; }
        function toggleGhost() {
            canvas.classList.toggle('ghost');
            document.getElementById('ghost-toggle').innerText = canvas.classList.contains('ghost') ? 'Hide Ghost' : 'Show Ghost';
        }

        ctx.lineWidth = 14; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#2d3436';

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        
        canvas.addEventListener('touchstart', (e) => {
            const t = e.touches[0]; const rect = canvas.getBoundingClientRect();
            isDrawing = true; ctx.beginPath(); ctx.moveTo(t.clientX - rect.left, t.clientY - rect.top);
            e.preventDefault();
        });
        canvas.addEventListener('touchmove', (e) => {
            if(!isDrawing) return;
            const t = e.touches[0]; const rect = canvas.getBoundingClientRect();
            ctx.lineTo(t.clientX - rect.left, t.clientY - rect.top); ctx.stroke();
            e.preventDefault();
        });

        function startDrawing(e) {
            isDrawing = true; const rect = canvas.getBoundingClientRect();
            ctx.beginPath(); ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        }
        function draw(e) {
            if (!isDrawing) return; const rect = canvas.getBoundingClientRect();
            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top); ctx.stroke();
        }
        function stopDrawing() { isDrawing = false; }
        function clearCanvas() { ctx.clearRect(0, 0, canvas.width, canvas.height); document.getElementById('feedback').style.display = 'none'; }

        async function submitDrawing() {
            const btn = document.querySelector('.btn-submit');
            const feedback = document.getElementById('feedback');
            btn.innerText = 'Wait...'; btn.disabled = true;

            const userData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            let totalUser = 0;
            for (let i = 3; i < userData.length; i += 4) { if (userData[i] > 10) totalUser++; }
            
            feedback.style.display = 'block'; feedback.classList.remove('error');
            
            if (totalUser < 50) {
                feedback.classList.add('error');
                document.getElementById('feedback-title').innerText = "Empty Board";
                document.getElementById('feedback-text').innerText = "Please draw the letter first!";
                btn.innerText = 'Analyze'; btn.disabled = false;
                return;
            }

            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width; tempCanvas.height = canvas.height;
            const tCtx = tempCanvas.getContext('2d');
            tCtx.fillStyle = '#ffffff';
            tCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            tCtx.drawImage(canvas, 0, 0);
            
            const dataUrl = tempCanvas.toDataURL('image/png');

            try {
                const response = await fetch('api/grade_calligraphy.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ letter: '<?= $target ?>', image: dataUrl })
                });
                
                const result = await response.json();
                
                if (result.error) {
                    feedback.classList.add('error');
                    document.getElementById('feedback-title').innerText = "Error";
                    document.getElementById('feedback-text').innerText = result.error;
                } else if (result.score < 50) {
                    feedback.classList.add('error');
                    document.getElementById('feedback-title').innerText = "Try Again (" + result.score + "%)";
                    document.getElementById('feedback-text').innerText = result.feedback;
                } else {
                    document.getElementById('feedback-title').innerText = "Exquisite! (" + result.score + "%)";
                    document.getElementById('feedback-text').innerText = result.feedback + (result.xp_earned > 0 ? " +" + result.xp_earned + " XP!" : "");
                    
                    setTimeout(() => {
                        const current = document.querySelector('.letter-btn.active');
                        if (current && current.nextElementSibling) current.nextElementSibling.click();
                    }, 2500);
                }
            } catch (e) {
                feedback.classList.add('error');
                document.getElementById('feedback-title').innerText = "Network Error";
                document.getElementById('feedback-text').innerText = "Could not reach Professor Faseeh.";
            }
            
            btn.innerText = 'Analyze'; btn.disabled = false;
        }
    </script>
</body>
</html>
