<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Master Arabic Through Gaming</title>
    <meta name="description" content="Faseeh is a gamified Arabic language learning platform with hangman, quizzes, spaced repetition, and real-time leaderboards.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: 100%; height: 100%; overflow: hidden;
            font-family: 'Poppins', sans-serif; color: white;
        }
        body {
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            position: relative;
        }

        /* Floating Arabic Letters */
        .floating-letter {
            position: absolute; color: rgba(255,255,255,0.04);
            font-family: 'Amiri', serif; user-select: none; z-index: 0;
            opacity: 0; animation: floatUp linear infinite; pointer-events: none;
        }
        .l1 { left: 5%; bottom: -100px; font-size: 100px; animation-duration: 18s; }
        .l2 { left: 90%; bottom: -100px; font-size: 140px; animation-duration: 22s; animation-delay: 3s; }
        .l3 { left: 25%; bottom: -100px; font-size: 70px; animation-duration: 14s; animation-delay: 6s; }
        .l4 { left: 75%; bottom: -100px; font-size: 110px; animation-duration: 20s; animation-delay: 1s; }
        .l5 { left: 50%; bottom: -100px; font-size: 80px; animation-duration: 16s; animation-delay: 8s; }
        .l6 { left: 40%; bottom: -100px; font-size: 90px; animation-duration: 19s; animation-delay: 4s; }

        @keyframes floatUp {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            15% { opacity: 0.06; }
            85% { opacity: 0.06; }
            100% { transform: translateY(-120vh) rotate(360deg); opacity: 0; }
        }

        /* Particle dots */
        .particles { position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0; }
        .particle {
            position: absolute; width: 3px; height: 3px; background: rgba(255,255,255,0.15);
            border-radius: 50%; animation: twinkle 3s infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.5); }
        }

        /* Top Buttons */
        .how-to-play {
            position: absolute; top: 25px; left: 25px;
            padding: 10px 22px; border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05);
            font-size: 0.8rem; color: white; cursor: pointer;
            text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
            transition: 0.3s; z-index: 20; display: flex; align-items: center; gap: 8px;
            backdrop-filter: blur(10px);
        }
        .how-to-play:hover { background: white; color: var(--bg-start); }

        .settings-btn {
            position: absolute; top: 25px; right: 25px;
            width: 42px; height: 42px; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.3s; color: rgba(255,255,255,0.8); z-index: 20;
            backdrop-filter: blur(10px); font-size: 1.1rem;
        }
        .settings-btn:hover { background: white; color: var(--bg-start); transform: rotate(90deg); }

        /* Hero */
        .content-wrapper {
            z-index: 10; display: flex; flex-direction: column; align-items: center;
            transform: translateY(-15px);
        }

        .logo-box {
            position: relative; width: 130px; height: 130px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 60px rgba(242,153,74,0.4), 0 0 120px rgba(242,153,74,0.15);
            margin-bottom: 25px; animation: pulseLogo 3s infinite ease-in-out;
        }
        .logo-box::after {
            content: ''; position: absolute; width: 85%; height: 85%;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: transparent; border-radius: 50%;
            animation: spinRing 10s linear infinite;
        }
        .logo-box::before {
            content: ''; position: absolute; width: 110%; height: 110%;
            border: 1px solid rgba(255,255,255,0.08);
            border-bottom-color: transparent; border-right-color: transparent;
            border-radius: 50%; animation: spinRing 15s linear infinite reverse;
        }
        .logo-letter {
            font-family: 'Amiri', serif; font-size: 65px;
            color: white; margin-top: -10px; text-shadow: 0 2px 15px rgba(0,0,0,0.2);
        }

        h1 {
            font-size: 3.5rem; margin: 0; font-weight: 900; letter-spacing: -1px;
            animation: fadeUp 0.8s ease-out;
            background: linear-gradient(to right, #fff 30%, var(--gold) 50%, #fff 70%);
            background-size: 200% auto; color: transparent;
            -webkit-background-clip: text; background-clip: text;
            animation: fadeUp 0.8s ease-out, shine 4s linear infinite;
        }
        p.subtitle {
            font-size: 0.95rem; letter-spacing: 3px; text-transform: uppercase;
            margin-bottom: 35px; opacity: 0.5; font-weight: 300;
            animation: fadeUp 1s ease-out;
        }

        /* CTA Button */
        .btn-start {
            display: inline-block; padding: 18px 50px;
            text-align: center; text-decoration: none; border-radius: 50px;
            font-weight: 700; font-size: 1.1rem; cursor: pointer; letter-spacing: 0.5px;
            background: linear-gradient(to right, var(--accent), var(--accent2)); color: white;
            border: none; box-shadow: 0 10px 40px rgba(242,153,74,0.3);
            transition: 0.3s; animation: fadeUp 1.2s ease-out; margin-bottom: 12px;
            position: relative; overflow: hidden;
        }
        .btn-start::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }
        .btn-start:hover { transform: translateY(-3px) scale(1.03); box-shadow: 0 20px 50px rgba(242,153,74,0.5); }

        .btn-secondary {
            display: inline-block; padding: 12px 35px; border-radius: 50px;
            text-decoration: none; color: rgba(255,255,255,0.6); font-weight: 500;
            font-size: 0.85rem; transition: 0.3s; border: 1px solid rgba(255,255,255,0.1);
            animation: fadeUp 1.4s ease-out; margin-bottom: 30px;
        }
        .btn-secondary:hover { color: white; border-color: rgba(255,255,255,0.3); background: rgba(255,255,255,0.05); }

        /* Feature Row */
        .features-row { display: flex; gap: 35px; animation: fadeUp 1.5s ease-out; }
        .feature-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .feat-icon {
            font-size: 1.3rem; background: rgba(255,255,255,0.06);
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,0.08); transition: 0.3s;
        }
        .feature-item:hover .feat-icon { background: rgba(242,153,74,0.15); border-color: rgba(242,153,74,0.3); transform: translateY(-3px); }
        .feat-text { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); font-weight: 600; }

        /* Footer */
        .game-footer {
            position: absolute; bottom: 18px; z-index: 10;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            font-size: 0.75rem; color: rgba(255,255,255,0.25); width: 100%;
        }
        .footer-links-row { display: flex; gap: 15px; align-items: center; }
        .footer-link { cursor: pointer; transition: 0.3s; text-decoration: none; color: rgba(255,255,255,0.35); }
        .footer-link:hover { color: white; }
        .separator { opacity: 0.2; }
        .copyright-text { font-size: 0.65rem; opacity: 0.3; font-family: monospace; letter-spacing: 1px; }

        /* Modals */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px);
            display: flex; justify-content: center; align-items: center;
            opacity: 0; visibility: hidden; transition: 0.3s; z-index: 100;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            width: 500px; max-width: 90%; border-radius: 20px; padding: 35px;
            color: white; border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            transform: translateY(30px); transition: 0.4s;
        }
        .modal-overlay.active .modal-card { transform: translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .modal-header h2 { margin: 0; font-size: 1.2rem; }
        .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(255,255,255,0.4); transition: 0.3s; }
        .close-btn:hover { color: white; }
        .rule-item { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .rule-icon {
            width: 32px; height: 32px; background: rgba(255,255,255,0.06);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 0.9rem;
        }
        .rule-text { font-size: 0.85rem; color: rgba(255,255,255,0.7); line-height: 1.5; }
        .rule-text b { color: white; }

        @keyframes spinRing { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulseLogo {
            0% { transform: scale(1); box-shadow: 0 0 40px rgba(242,153,74,0.4); }
            50% { transform: scale(1.04); box-shadow: 0 0 80px rgba(242,153,74,0.6); }
            100% { transform: scale(1); box-shadow: 0 0 40px rgba(242,153,74,0.4); }
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shine { to { background-position: 200% center; } }
        @keyframes shimmer { 0% { left: -100%; } 100% { left: 200%; } }

        @media (max-height: 750px) {
            .logo-box { width: 90px; height: 90px; margin-bottom: 15px; }
            .logo-letter { font-size: 45px; }
            h1 { font-size: 2.5rem; }
            p.subtitle { margin-bottom: 20px; }
            .btn-start { padding: 14px 40px; margin-bottom: 8px; }
        }
        @media (max-width: 500px) {
            h1 { font-size: 2.2rem; }
            .features-row { gap: 20px; }
            .btn-start { padding: 15px 35px; font-size: 1rem; }
        }
    </style>
</head>
<body>

    <div class="floating-letter l1">ف</div>
    <div class="floating-letter l2">س</div>
    <div class="floating-letter l3">ي</div>
    <div class="floating-letter l4">ح</div>
    <div class="floating-letter l5">ع</div>
    <div class="floating-letter l6">ل</div>

    <div class="particles" id="particles"></div>

    <div class="how-to-play" onclick="toggleModal('rules-modal')">
        <span>📜</span> How to Play
    </div>
    <div class="settings-btn" onclick="toggleModal('settings-modal')">⚙️</div>

    <div class="content-wrapper">
        <div class="logo-box">
            <div class="logo-letter">ف</div>
        </div>
        <h1>Faseeh</h1>
        <p class="subtitle">Master Arabic Through Gaming</p>

        <a href="index.php" class="btn-start">Start Your Journey 🚀</a>
        <a href="leaderboard.php" class="btn-secondary">🏆 View Leaderboard</a>

        <div class="features-row">
            <div class="feature-item"><div class="feat-icon">🎯</div><span class="feat-text">Hangman</span></div>
            <div class="feature-item"><div class="feat-icon">🧩</div><span class="feat-text">Quiz</span></div>
            <div class="feature-item"><div class="feat-icon">🧠</div><span class="feat-text">Review</span></div>
            <div class="feature-item"><div class="feat-icon">🏆</div><span class="feat-text">Compete</span></div>
            <div class="feature-item"><div class="feat-icon">🔥</div><span class="feat-text">Streaks</span></div>
        </div>
    </div>

    <div class="game-footer">
        <div class="footer-links-row">
            <span class="footer-link" onclick="toggleModal('legal-modal')">Privacy & Terms</span>
            <span class="separator">|</span>
            <span class="footer-link" onclick="toggleModal('fair-play-modal')">Fair Play</span>
            <span class="separator">|</span>
            <span style="color: rgba(255,255,255,0.3);">v2.0</span>
        </div>
        <div class="copyright-text">© 2025 Smart Developers. All Rights Reserved.</div>
    </div>

    <!-- Modals -->
    <div class="modal-overlay" id="rules-modal">
        <div class="modal-card">
            <div class="modal-header"><h2>📜 How to Play</h2><button class="close-btn" onclick="toggleModal('rules-modal')">×</button></div>
            <div class="rule-item"><div class="rule-icon">🎯</div><div class="rule-text"><b>Hangman:</b> Guess Arabic words letter by letter before time runs out.</div></div>
            <div class="rule-item"><div class="rule-icon">🧩</div><div class="rule-text"><b>Quiz Mode:</b> Multiple choice — 10 questions, 15 seconds each.</div></div>
            <div class="rule-item"><div class="rule-icon">🧠</div><div class="rule-text"><b>Review:</b> Spaced repetition flashcards reinforce what you've learned.</div></div>
            <div class="rule-item"><div class="rule-icon">🔥</div><div class="rule-text"><b>Daily Streak:</b> Play every day to build your streak and earn bonus XP.</div></div>
            <div class="rule-item"><div class="rule-icon">🏅</div><div class="rule-text"><b>Achievements:</b> Unlock 18 badges by reaching milestones.</div></div>
            <button class="btn-start" style="width:100%; margin-top:15px;" onclick="toggleModal('rules-modal')">Got It! ✓</button>
        </div>
    </div>

    <div class="modal-overlay" id="fair-play-modal">
        <div class="modal-card">
            <div class="modal-header"><h2>⚖️ Fair Play</h2><button class="close-btn" onclick="toggleModal('fair-play-modal')">×</button></div>
            <div class="rule-item"><div class="rule-icon">🚫</div><div class="rule-text"><b>No bots or scripts.</b> All answers must be from you.</div></div>
            <div class="rule-item"><div class="rule-icon">👤</div><div class="rule-text"><b>One account per student.</b> Multi-accounting is prohibited.</div></div>
        </div>
    </div>

    <div class="modal-overlay" id="legal-modal">
        <div class="modal-card">
            <div class="modal-header"><h2>🔒 Privacy & Terms</h2><button class="close-btn" onclick="toggleModal('legal-modal')">×</button></div>
            <div class="rule-item"><div class="rule-icon">🛡️</div><div class="rule-text"><b>Data:</b> We only store academic progress and usernames.</div></div>
            <div class="rule-item"><div class="rule-icon">🍪</div><div class="rule-text"><b>Cookies:</b> Secure HTTP-only session cookies.</div></div>
        </div>
    </div>

    <div class="modal-overlay" id="settings-modal">
        <div class="modal-card">
            <div class="modal-header"><h2>⚙️ Preferences</h2><button class="close-btn" onclick="toggleModal('settings-modal')">×</button></div>
            <div class="rule-item" style="justify-content:space-between;">
                <span class="rule-text">🔊 Sound Effects</span>
                <input type="checkbox" checked style="width:20px; height:20px; accent-color: var(--accent);">
            </div>
            <div class="rule-item" style="justify-content:space-between;">
                <span class="rule-text">🗣️ Auto-Pronunciation</span>
                <input type="checkbox" checked style="width:20px; height:20px; accent-color: var(--accent);">
            </div>
            <div style="text-align:center; margin-top:25px; padding-top:15px; border-top:1px solid rgba(255,255,255,0.1);">
                <p style="font-size:0.8rem; opacity:0.4; margin-bottom:8px;">Faseeh v2.0</p>
                <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.05); padding:8px 18px; border-radius:20px; font-size:0.8rem; opacity:0.6;">
                    <span>&lt;/&gt;</span> Smart Developers
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.scrollTo(0,0); }
        function toggleModal(id) { document.getElementById(id).classList.toggle('active'); }

        // Generate particles
        const container = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.top = Math.random() * 100 + '%';
            p.style.animationDelay = Math.random() * 3 + 's';
            p.style.animationDuration = (2 + Math.random() * 3) + 's';
            container.appendChild(p);
        }
    </script>
</body>
</html>