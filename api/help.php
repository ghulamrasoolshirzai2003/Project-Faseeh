<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faseeh - System Documentation</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        .help-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            color: white;
        }
        .help-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        h2 { color: #FFD700; border-bottom: 1px solid rgba(255,215,0,0.3); padding-bottom: 10px; margin-top: 30px; }
        .feature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .feature-item { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; }
        .feature-item strong { color: #FFD700; display: block; margin-bottom: 5px; }
        .back-link { margin-top: 30px; display: inline-block; color: white; text-decoration: none; font-weight: bold; opacity: 0.7; transition: 0.3s; }
        .back-link:hover { opacity: 1; color: #FFD700; }
    </style>
</head>
<body class="center-screen" style="overflow-y: auto; justify-content: flex-start;">
    <div class="help-container">
        <div class="help-card">
            <h1 style="text-align:center; font-weight: 800; margin-bottom: 10px;">🏆 System Documentation</h1>
            <p style="text-align:center; opacity: 0.8; margin-bottom: 30px;">Innovative Arabic Learning Competition - "Faseeh"</p>

            <h2>🎮 Game Mechanics</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <strong>Adaptive Levels</strong>
                    Choose from Beginner, Intermediate, or Advanced. Each level contains 20 specific words.
                </div>
                <div class="feature-item">
                    <strong>30-Second Challenge</strong>
                    Users must solve the word before the timer reaches zero to prevent a penalty.
                </div>
                <div class="feature-item">
                    <strong>Scoring System</strong>
                    Win: +10 Points. Loss/Mistake: -5 Points. This ensures accuracy is prioritized.
                </div>
                <div class="feature-item">
                    <strong>Tashkeel Cleaning</strong>
                    The system automatically cleans Arabic diacritics to ensure the game remains fair and playable on a standard keyboard.
                </div>
            </div>

            <h2>👨‍🏫 Administrator Features</h2>
            <p>The Admin Portal allows judges to monitor the competition in real-time:</p>
            <ul>
                <li><strong>Live Ranking:</strong> Scores update every 10 seconds.</li>
                <li><strong>Penalty Tracking:</strong> See exactly how many marks students have lost.</li>
                <li><strong>Data Export:</strong> Download full results as a CSV for final grading.</li>
            </ul>

            <h2>🛠️ Technical Stack</h2>
            <p>
                Built on a solid foundation of <strong>HTML5</strong> and <strong>Modern CSS3</strong> for a responsive, cinematic interface. 
                The system utilizes <strong>Vanilla JavaScript</strong> for logic, with a secure <strong>PHP 8</strong> and <strong>MySQL</strong> backend. 
                No heavy frameworks were used, ensuring ultra-fast performance on any device.
            </p>

            <div style="text-align:center;">
                <a href="level_select.php" class="back-link">← Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>