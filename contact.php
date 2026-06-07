<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Support — Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&family=Syne:wght@800&display=swap" rel="stylesheet">
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
        .container { max-width: 600px; margin: 0 auto; padding: 120px 30px 100px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-family: 'Syne', sans-serif; font-size: 2.8rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px; }
        .header p { font-size: 1.1rem; color: var(--text-muted); line-height: 1.8; }
        
        .content-box {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px;
            padding: 40px; backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 500; color: rgba(255,255,255,0.8); }
        input, select, textarea {
            width: 100%; padding: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 12px; color: white; font-family: 'Poppins', sans-serif; font-size: 1rem; transition: 0.3s; outline: none;
        }
        input:focus, textarea:focus, select:focus { border-color: var(--accent); background: rgba(0,0,0,0.4); }
        textarea { resize: vertical; min-height: 120px; }
        
        .btn-submit { display: block; width: 100%; padding: 16px; background: var(--accent); color: #000; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }
        
        .contact-info { text-align: center; margin-top: 40px; font-size: 0.95rem; color: var(--text-muted); line-height: 1.8; }
        .contact-info a { color: var(--accent); text-decoration: none; font-weight: 600; }
        
        @keyframes spinLogo { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shineText { to { background-position: 200% center; } }
    </style>
</head>
<body>
    <!-- Fixed top navigation -->
    <nav style="position: fixed; top: 0; left: 0; right: 0; display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: rgba(14, 12, 30, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.05); z-index: 1000;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div class="mini-icon" style="width: 38px; height: 38px; background: linear-gradient(135deg, #f2994a, #f2c94c); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4);">
          <div style="font-family: 'Amiri', serif; font-size: 18px; color: white;">ف</div>
          <div style="content: ''; position: absolute; width: 26px; height: 26px; border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent; border-radius: 50%; animation: spinLogo 8s linear infinite;"></div>
        </div>
        <h1 style="font-size: 1.2rem; font-weight: 800; margin: 0; background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shineText 3s linear infinite; font-family: 'Syne', sans-serif;">Faseeh</h1>
      </div>
      <a href="index.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; border: 1px solid var(--glass-border); padding: 8px 16px; border-radius: 12px; transition: 0.3s;">← Back to Home</a>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Help & Support</h1>
            <p>Found a bug or having trouble with your account? Our support team is here to help you get back to learning.</p>
        </div>
        
        <div class="content-box">
            <form onsubmit="event.preventDefault(); alert('Ticket created! Our support team will respond to your email within 24 hours.'); window.location.href='index.php';">
                <div class="form-group">
                    <label>Faseeh Username (optional)</label>
                    <input type="text" placeholder="e.g. ArabicLearner99">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" placeholder="john@example.com" required>
                </div>
                <div class="form-group">
                    <label>Issue Category</label>
                    <select required>
                        <option value="login">Login / Account Issue</option>
                        <option value="bug">Bug Report (Something is broken)</option>
                        <option value="content">Lesson / Translation Correction</option>
                        <option value="other">Other Inquiry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea placeholder="Describe exactly what happened..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Support Ticket →</button>
            </form>
        </div>
        
        <div class="contact-info">
            <p>Direct Email: <a href="mailto:hello@faseeh.com">hello@faseeh.com</a></p>
            <p>Support Hours: Monday - Friday, 9am - 6pm (GMT)</p>
        </div>
    </div>
</body>
</html>
