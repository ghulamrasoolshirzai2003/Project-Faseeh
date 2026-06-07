<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Privacy Policy</title>
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
            padding: 50px; line-height: 1.8; font-size: 1rem; color: rgba(255,255,255,0.85);
            backdrop-filter: blur(10px);
        }
        .content-box h2 { font-size: 1.5rem; color: var(--accent); margin: 35px 0 15px; font-weight: 700; }
        .content-box h2:first-child { margin-top: 0; }
        .content-box p { margin-bottom: 20px; }
        .content-box ul { margin-bottom: 20px; padding-left: 20px; }
        .content-box li { margin-bottom: 8px; }
        
        .btn-back { display: inline-block; margin-top: 40px; padding: 14px 28px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Privacy Policy</h1>
            <p>We respect your privacy. Here is a clear breakdown of how we handle your data.</p>
        </div>
        <div class="content-box">
            <h2>1. Information We Collect</h2>
            <p>When you register for Faseeh, we collect basic information including your email address, username, and learning progress data (such as XP, streaks, and module completion status). If you register using Google SSO, we receive your email and basic profile data from Google.</p>
            
            <h2>2. How We Use Your Information</h2>
            <p>Your data is used strictly to provide and improve the Faseeh learning experience. Specifically, we use it to:</p>
            <ul>
                <li>Track your Arabic learning progress and update your dashboard.</li>
                <li>Display your username and XP on the public leaderboards.</li>
                <li>Provide AI-driven feedback tailored to your learning level.</li>
                <li>Process secure payments via our trusted third-party gateway (Stripe). We do not store your credit card information.</li>
            </ul>

            <h2>3. AI Data Handling</h2>
            <p>When you use the AI Essay Grader or Conversation Partner, the text you submit is sent securely to our AI partner (e.g., OpenAI/Gemini) for linguistic analysis. We do not use your personal identifiable information in these requests, and the AI providers are restricted from using this data to train public models.</p>

            <h2>4. Data Sharing</h2>
            <p><strong>We will never sell your personal data to third parties.</strong> We only share data with trusted infrastructure partners (such as hosting and database providers) necessary to operate the platform securely.</p>
            
            <h2>5. Contact Us</h2>
            <p>If you have questions about your privacy or wish to delete your account data permanently, please reach out to our support team.</p>
            
            <div style="text-align: center;">
                <a href="index.php" class="btn-back">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
