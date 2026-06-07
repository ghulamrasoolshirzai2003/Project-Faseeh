<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Blog & Updates</title>
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
        .container { max-width: 1000px; margin: 0 auto; padding: 80px 30px 100px; }
        .header { text-align: center; margin-bottom: 60px; }
        .header h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px; }
        .header p { font-size: 1.1rem; color: var(--text-muted); max-width: 700px; margin: 0 auto; line-height: 1.8; }
        
        .blog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .blog-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; }
        .blog-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }
        .blog-img { height: 180px; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
        .blog-content { padding: 30px; display: flex; flex-direction: column; flex-grow: 1; }
        .blog-tag { font-size: 0.8rem; color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .blog-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 15px; line-height: 1.4; }
        .blog-desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; flex-grow: 1; }
        .blog-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 15px; font-size: 0.85rem; color: var(--text-muted); }
        
        .btn-back { display: inline-block; margin-top: 60px; padding: 14px 28px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Faseeh Insights</h1>
            <p>News, updates, and deep dives into the science of learning the Arabic language.</p>
        </div>
        
        <div class="blog-grid">
            <div class="blog-card">
                <div class="blog-img">🤖</div>
                <div class="blog-content">
                    <div class="blog-tag">Product Update</div>
                    <div class="blog-title">How our AI Essay Grader corrects Arabic syntax in real-time</div>
                    <div class="blog-desc">A deep technical dive into how we built an AI tutor capable of understanding complex Arabic morphology and suggesting stylistic improvements.</div>
                    <div class="blog-footer"><span>Oct 12, 2026</span><span>5 min read</span></div>
                </div>
            </div>
            <div class="blog-card">
                <div class="blog-img">📖</div>
                <div class="blog-content">
                    <div class="blog-tag">Linguistics</div>
                    <div class="blog-title">Why the Root Word system is the key to Arabic fluency</div>
                    <div class="blog-desc">Forget memorizing endless vocabulary lists. Once you understand the 3-letter root system, you unlock the mathematical beauty of Arabic.</div>
                    <div class="blog-footer"><span>Sep 28, 2026</span><span>8 min read</span></div>
                </div>
            </div>
            <div class="blog-card">
                <div class="blog-img">🏆</div>
                <div class="blog-content">
                    <div class="blog-tag">Community</div>
                    <div class="blog-title">Celebrating 10,000 active learners on Faseeh</div>
                    <div class="blog-desc">Our community just hit a massive milestone! Let's look at the incredible streaks and XP you've all generated over the past year.</div>
                    <div class="blog-footer"><span>Sep 15, 2026</span><span>3 min read</span></div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="index.php" class="btn-back">← Back to Home</a>
        </div>
    </div>
</body>
</html>
