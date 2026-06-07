<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — About Us</title>
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
            padding: 50px; line-height: 1.8; font-size: 1.05rem;
            backdrop-filter: blur(10px);
        }
        .content-box h2 { font-size: 2rem; color: var(--accent); margin: 40px 0 20px; font-weight: 700; }
        .content-box h2:first-child { margin-top: 0; }
        .content-box p { margin-bottom: 24px; color: rgba(255,255,255,0.85); }
        .btn-back { display: inline-block; margin-top: 40px; padding: 14px 28px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Our Mission</h1>
            <p>Bridging the gap between ancient linguistic traditions and modern AI technology to create the ultimate Arabic learning platform.</p>
        </div>
        <div class="content-box">
            <h2>Who We Are</h2>
            <p>Faseeh Academy was born out of a profound passion for the Arabic language. We recognized that traditional learning methods often leave students feeling overwhelmed by complex grammar and disjointed vocabulary lists. We set out to change this narrative by building a platform that feels intuitive, engaging, and highly effective.</p>
            
            <h2>The Faseeh Methodology</h2>
            <p>Instead of rote memorization, Faseeh focuses on the <strong>Root System</strong> of Arabic. By understanding the core 3-letter roots, learners can mathematically decipher thousands of words. We combine this proven linguistic approach with gamification, AI-powered writing analysis, and spaced repetition to ensure long-term mastery.</p>
            
            <h2>For Every Learner</h2>
            <p>Whether you are a professional needing Modern Standard Arabic for the workplace, a student aiming to master Egyptian or Levantine dialects, or a believer striving to understand Quranic Arabic directly from the source, Faseeh provides a dedicated, immersive track for your journey.</p>
            
            <div style="text-align: center;">
                <a href="index.php" class="btn-back">← Return to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
