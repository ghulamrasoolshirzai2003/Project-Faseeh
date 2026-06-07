<?php
session_start();
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Lost in Translation | Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&family=Syne:wght@800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0e0c1e;
            --accent: #f5a623;
            --accent2: #7c5cbf;
            --text: #f0eeff;
            --text-muted: #8b87b0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        .container { position: relative; z-index: 10; padding: 20px; }
        
        .error-code {
            font-family: 'Syne', sans-serif;
            font-size: 10rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(to bottom, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            opacity: 0.15;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            letter-spacing: -10px;
        }
        
        .branding {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }
        .mini-icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #f2994a, #f2c94c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 30px rgba(242,153,74,0.3);
            margin-bottom: 20px;
        }
        .mini-icon::after {
            content: ''; position: absolute; width: 68px; height: 68px;
            border: 3px solid rgba(255,255,255,0.4); border-top-color: transparent;
            border-radius: 50%; animation: spin 8s linear infinite;
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 42px; color: white; margin-top: -5px; z-index: 2; }
        
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        h1 { font-family: 'Syne', sans-serif; font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
        p { color: var(--text-muted); font-size: 1.1rem; margin-bottom: 35px; max-width: 450px; line-height: 1.6; }
        
        .btn-home {
            display: inline-block;
            padding: 16px 36px;
            background: linear-gradient(to right, var(--accent), #e8862a);
            color: #1a0f00;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 10px 30px rgba(245,166,35,0.3);
            transition: 0.3s;
        }
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(245,166,35,0.45); }
        
        .arabic-hint {
            font-family: 'Amiri', serif;
            font-size: 1.5rem;
            color: var(--accent);
            margin-top: 40px;
            opacity: 0.6;
        }

        /* Ambient glow */
        .glow {
            position: absolute; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(124,92,191,0.15) 0%, transparent 70%);
            border-radius: 50%; z-index: -1;
        }
    </style>
</head>
<body>
    <div class="glow" style="top: -100px; left: -100px;"></div>
    <div class="glow" style="bottom: -100px; right: -100px;"></div>

    <div class="container">
        <div class="error-code">404</div>
        
        <div class="branding">
            <div class="mini-icon">
                <div class="mini-letter">ف</div>
            </div>
            <h1>Lost in Translation</h1>
        </div>

        <p>The page you are looking for has vanished into the vast desert of the internet. Let's get you back to your Arabic journey.</p>
        
        <a href="index.php" class="btn-home">Return to Safety →</a>

        <div class="arabic-hint">لَا تَقْلَقْ، الْعَوْدَةُ سَهْلَةٌ</div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">(Don't worry, the return is easy)</div>
    </div>
</body>
</html>
