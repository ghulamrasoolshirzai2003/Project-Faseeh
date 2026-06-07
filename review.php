<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Review Mode</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894; --danger: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; display: flex; flex-direction: column;
        }
        .navbar {
            width: 100%; padding: 15px 25px;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0,0,0,0.3); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: white; }
        .mini-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .mini-letter { font-family: 'Amiri', serif; font-size: 18px; color: white; margin-top: -2px; }
        .mini-text { font-size: 1.2rem; font-weight: 800; margin: 0; }

        .game-wrap {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 20px;
        }
        .review-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 25px; padding: 40px; max-width: 500px; width: 100%;
            text-align: center; backdrop-filter: blur(15px);
            transition: transform 0.6s; transform-style: preserve-3d;
            min-height: 350px; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            animation: slideUp 0.4s ease;
        }
        .review-card.flipped { transform: rotateY(180deg); }

        .counter-text { font-size: 0.75rem; opacity: 0.4; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; }
        .arabic-big { font-family: 'Amiri', serif; font-size: 3.5rem; direction: rtl; margin-bottom: 10px; }
        .root-text { font-size: 0.85rem; opacity: 0.4; font-family: 'Amiri', serif; margin-bottom: 10px; }
        .speak-btn { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-size: 0.85rem; transition: 0.3s; margin-bottom: 15px; }
        .speak-btn:hover { background: rgba(255,255,255,0.2); }
        .tap-hint { font-size: 0.8rem; opacity: 0.3; margin-top: 15px; }

        .meaning-reveal {
            font-size: 1.3rem; font-weight: 700; margin: 15px 0;
            padding: 15px 25px; background: rgba(255,255,255,0.08); border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .rating-row { display: flex; gap: 8px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
        .rate-btn {
            padding: 10px 18px; border-radius: 12px; border: 2px solid transparent;
            font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .rate-btn.hard { background: rgba(231,76,60,0.2); color: var(--danger); border-color: rgba(231,76,60,0.3); }
        .rate-btn.hard:hover { background: rgba(231,76,60,0.4); }
        .rate-btn.ok { background: rgba(242,153,74,0.2); color: var(--accent); border-color: rgba(242,153,74,0.3); }
        .rate-btn.ok:hover { background: rgba(242,153,74,0.4); }
        .rate-btn.easy { background: rgba(0,184,148,0.2); color: var(--success); border-color: rgba(0,184,148,0.3); }
        .rate-btn.easy:hover { background: rgba(0,184,148,0.4); }

        .empty-state { text-align: center; opacity: 0.5; }
        .empty-state .icon { font-size: 3rem; margin-bottom: 15px; }
        .btn-primary { background: linear-gradient(to right, var(--accent), var(--accent2)); border: none; color: white; padding: 14px 35px; border-radius: 50px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; margin-top: 20px; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(242,153,74,0.4); }

        .complete-state { text-align: center; }
        .complete-state .icon { font-size: 4rem; margin-bottom: 15px; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 500px) {
            .review-card { padding: 25px 18px; }
            .arabic-big { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="level_select.php" class="nav-brand">
            <div class="mini-icon"><div class="mini-letter">ف</div></div>
            <h1 class="mini-text">Faseeh</h1>
        </a>
        <span style="font-size:0.85rem; opacity:0.6;">🧠 Review Mode</span>
        <a href="level_select.php" style="color:white; text-decoration:none; font-weight:600; font-size:0.85rem;">✕ Done</a>
    </nav>

    <div class="game-wrap">
        <div class="review-card" id="review-card">
            <div style="opacity:0.5;">Loading review words...</div>
        </div>
    </div>

    <script>
    let words = [];
    let currentIdx = 0;
    let revealed = false;
    const card = document.getElementById('review-card');

    function speakArabic(text) {
        if(!text) return;
        if(window.responsiveVoice) {
            responsiveVoice.speak(text, "Arabic Male");
        } else if ('speechSynthesis' in window) {
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'ar-SA'; u.rate = 0.85;
            speechSynthesis.speak(u);
        }
    }

    async function loadReviews() {
        try {
            const res = await fetch('api/get_reviews.php');
            const data = await res.json();
            if (data.error) { showEmpty(data.error); return; }
            words = data.words || [];
            if (words.length === 0) { showEmpty(); return; }
            showWord();
        } catch(e) { showEmpty('Error loading reviews'); }
    }

    function showEmpty(msg) {
        card.innerHTML = `
            <div class="empty-state">
                <div class="icon">✨</div>
                <h2>All Caught Up!</h2>
                <p>${msg || 'No words to review right now. Play more games to build your review queue.'}</p>
                <a href="level_select.php" class="btn-primary">🎮 Play Hangman</a>
                <a href="sentence_builder.php" class="btn-primary" style="background: linear-gradient(to right, #f2994a, #f2c94c); color:#333;">🧩 Build Sentences</a>
            </div>
        `;
    }

    function showWord() {
        if (currentIdx >= words.length) { showComplete(); return; }
        revealed = false;
        const w = words[currentIdx];

        card.innerHTML = `
            <div class="counter-text">Review ${currentIdx + 1} of ${words.length}</div>
            <div class="arabic-big">${w.arabic_word}</div>
            ${w.root ? `<div class="root-text">Root: ${w.root}</div>` : ''}
            <button class="speak-btn" onclick="event.stopPropagation(); speakArabic('${w.arabic_word}')">🔊 Listen</button>
            <div class="tap-hint">Tap the card to reveal the meaning</div>
        `;

        card.onclick = () => revealMeaning(w);
        speakArabic(w.arabic_word);
    }

    function revealMeaning(w) {
        if (revealed) return;
        revealed = true;

        card.innerHTML = `
            <div class="counter-text">Review ${currentIdx + 1} of ${words.length}</div>
            <div class="arabic-big">${w.arabic_word}</div>
            <div class="meaning-reveal">${w.meaning}</div>
            <button class="speak-btn" onclick="event.stopPropagation(); speakArabic('${w.arabic_word}')">🔊 Listen Again</button>
            <p style="font-size:0.8rem; opacity:0.5; margin-top:10px;">How well did you know this?</p>
            <div class="rating-row">
                <button class="rate-btn hard" onclick="rateWord(${w.id}, 'hard')">😓 Hard</button>
                <button class="rate-btn ok" onclick="rateWord(${w.id}, 'ok')">🤔 Okay</button>
                <button class="rate-btn easy" onclick="rateWord(${w.id}, 'easy')">😎 Easy</button>
            </div>
        `;
        card.onclick = null;
    }

    async function rateWord(wordId, rating) {
        // Calculate next review interval based on SM-2 algorithm
        let intervalDays = 1;
        let easeFactor = 0;
        switch(rating) {
            case 'hard': intervalDays = 1; easeFactor = -0.3; break;
            case 'ok':   intervalDays = 3; easeFactor = 0; break;
            case 'easy': intervalDays = 7; easeFactor = 0.2; break;
        }

        try {
            await fetch('api/submit_review.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ word_id: wordId, rating: rating })
            });
        } catch(e) { console.error(e); }

        currentIdx++;
        showWord();
    }

    function showComplete() {
        card.innerHTML = `
            <div class="complete-state">
                <div class="icon">🎉</div>
                <h2>Review Complete!</h2>
                <p style="opacity:0.6; margin-top:10px;">You reviewed ${words.length} word${words.length > 1 ? 's' : ''}. Great work!</p>
                <a href="level_select.php" class="btn-primary">🎮 Play Menu</a>
                <a href="sentence_builder.php" class="btn-primary" style="background: linear-gradient(to right, #f2994a, #f2c94c); color:#333;">🧩 Sentence Builder</a>
            </div>
        `;
        card.onclick = null;
    }

    loadReviews();
    </script>
</body>
</html>
