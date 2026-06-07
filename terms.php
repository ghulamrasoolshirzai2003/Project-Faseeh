<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh — Terms of Service</title>
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
        
        .btn-back { display: inline-block; margin-top: 40px; padding: 14px 28px; background: var(--glass); border: 1px solid var(--glass-border); color: var(--text); text-decoration: none; border-radius: 12px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Terms of Service</h1>
            <p>Please read these terms carefully before using Faseeh.</p>
        </div>
        <div class="content-box">
            <h2>1. Acceptance of Terms</h2>
            <p>By creating an account and accessing Faseeh Academy, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.</p>
            
            <h2>2. User Accounts & Security</h2>
            <p>You are responsible for maintaining the confidentiality of your account credentials. You agree not to share your account or login details with third parties. Any activity occurring under your account is your sole responsibility.</p>

            <h2>3. Intellectual Property</h2>
            <p>All content on this platform, including but not limited to the codebase, gamification mechanics, design elements, proprietary vocabulary lists, and Arabic curriculum structures, are the exclusive property of Faseeh Academy. You may not reproduce, distribute, or create derivative works without our explicit written permission.</p>

            <h2>4. Subscription & Billing</h2>
            <p>Faseeh offers Free, Premium, and Lifetime subscription tiers. Premium subscriptions are billed monthly and automatically renew unless canceled prior to the renewal date. Lifetime access requires a one-time payment. We reserve the right to change pricing, provided that current subscribers will be notified in advance.</p>

            <h2>5. Fair Use of AI Features</h2>
            <p>Our AI-powered tools (including the Essay Grader) are provided to assist your learning. Users engaging in abusive behavior, spamming the AI tools, or attempting to reverse-engineer our prompts will have their accounts terminated immediately without refund.</p>
            
            <h2>6. Limitation of Liability</h2>
            <p>Faseeh is provided "as is" without warranties of any kind. We do not guarantee uninterrupted access or error-free functionality, though we work tirelessly to maintain the highest standard of platform stability.</p>
            
            <div style="text-align: center;">
                <a href="index.php" class="btn-back">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
