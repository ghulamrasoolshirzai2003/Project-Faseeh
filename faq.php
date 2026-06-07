<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Frequently Asked Questions</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0e0c1e; --bg-mid: #161430; --bg-end: #1c1a38;
            --accent: #f5a623; --accent2: #7c5cbf;
            --glass: rgba(255,255,255,0.03); --glass-border: rgba(255,255,255,0.08);
            --text: #f0eeff; --text-muted: #8b87b0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: var(--text); min-height: 100vh; overflow-x: hidden;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 80px 30px 100px; }
        .header { text-align: center; margin-bottom: 60px; }
        .header h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px; }
        .header p { font-size: 1.1rem; color: var(--text-muted); max-width: 700px; margin: 0 auto; line-height: 1.8; }
        
        .content-box {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px;
            padding: 50px; backdrop-filter: blur(10px);
        }
        
        .faq-item { border-bottom: 1px solid var(--glass-border); padding: 25px 0; }
        .faq-item:last-child { border-bottom: none; }
        .faq-q { font-size: 1.25rem; font-weight: 600; color: var(--accent); margin-bottom: 10px; }
        .faq-a { font-size: 1rem; color: rgba(255,255,255,0.8); line-height: 1.7; }
        
        .btn-back { display: inline-block; margin-top: 40px; padding: 14px 28px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Frequently Asked Questions</h1>
            <p>Everything you need to know about learning Arabic with Faseeh.</p>
        </div>
        <div class="content-box">
            <div class="faq-item">
                <div class="faq-q">What is the Root-Based methodology?</div>
                <div class="faq-a">Most Arabic words are derived from a 3-letter core (the root). By learning just 100 essential roots, you can intuitively understand over 1,000 words. Faseeh's games and dictionary teach you to recognize these patterns instantly, saving you hundreds of hours of memorization.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">How does the AI Essay Grader work?</div>
                <div class="faq-a">Our built-in AI tutor analyzes your Arabic sentences in real-time. It doesn't just check for spelling; it evaluates complex grammar rules, verb conjugations, and stylistic choices, providing instant feedback explaining exactly why a correction was made.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Do you teach dialects or just Formal Arabic (Fusaha)?</div>
                <div class="faq-a">We currently offer a comprehensive track for Modern Standard Arabic (Fusaha) and a dedicated track for Quranic Arabic. We are actively developing Egyptian and Levantine dialect tracks which will be available to Premium members soon.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Is Faseeh really free?</div>
                <div class="faq-a">Yes! Our Free tier gives you permanent access to beginner modules, standard games, and the global leaderboard. The Premium tier unlocks advanced AI feedback, all 12 game modes, and full access to the Quranic track.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Is there a mobile app?</div>
                <div class="faq-a">Faseeh is built as a Progressive Web App (PWA). This means you can install it directly from your mobile browser (Safari or Chrome) to your home screen. It will look, feel, and function exactly like a native app without taking up massive storage space.</div>
            </div>
            
            <div style="text-align: center;">
                <a href="index.php" class="btn-back">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
