<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutional Sales — Faseeh for Schools</title>
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
        .container { max-width: 650px; margin: 0 auto; padding: 120px 30px 100px; }
        .header { text-align: center; margin-bottom: 50px; }
        .header h1 { font-family: 'Syne', sans-serif; font-size: 3rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px; background: linear-gradient(to right, #fff, var(--accent)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .header p { font-size: 1.1rem; color: var(--text-muted); line-height: 1.8; }
        
        .content-box {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 32px;
            padding: 50px; backdrop-filter: blur(20px); box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 10px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--accent); }
        input, select, textarea {
            width: 100%; padding: 16px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border);
            border-radius: 14px; color: white; font-family: 'Poppins', sans-serif; font-size: 1rem; transition: 0.3s; outline: none;
        }
        input:focus, textarea:focus, select:focus { border-color: var(--accent); background: rgba(0,0,0,0.5); box-shadow: 0 0 20px rgba(245,166,35,0.1); }
        
        .btn-submit { display: block; width: 100%; padding: 18px; background: linear-gradient(to right, var(--accent), #e8862a); color: #1a0f00; border: none; border-radius: 50px; font-size: 1.1rem; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 20px; box-shadow: 0 10px 30px rgba(245,166,35,0.3); }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(245,166,35,0.45); }
        
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
      <a href="b2b.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; border: 1px solid var(--glass-border); padding: 8px 16px; border-radius: 12px; transition: 0.3s;">← Back to B2B</a>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Partner with Faseeh</h1>
            <p>Modernize your school or mosque with the world's most immersive Arabic learning OS. Tell us about your institution.</p>
        </div>
        
        <div class="content-box">
            <form onsubmit="event.preventDefault(); alert('Inquiry received! Our institutional partnership lead will contact you within 24 hours to schedule a demo.'); window.location.href='b2b.php';">
                <div class="form-group">
                    <label>Institution Name</label>
                    <input type="text" placeholder="e.g. Al-Noor Islamic School" required>
                </div>
                <div class="form-group">
                    <label>Your Role</label>
                    <select required>
                        <option value="">Select your role</option>
                        <option value="principal">Principal / Director</option>
                        <option value="teacher">Head Teacher</option>
                        <option value="board">Board Member</option>
                        <option value="it">IT / Operations</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Work Email</label>
                    <input type="email" placeholder="principal@yourschool.com" required>
                </div>
                <div class="form-group">
                    <label>Estimated Students</label>
                    <select required>
                        <option value="<50">Less than 50</option>
                        <option value="50-200">50 - 200 students</option>
                        <option value="200-500">200 - 500 students</option>
                        <option value="500+">500+ students</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Specific Requirements</label>
                    <textarea placeholder="Tell us about your current Arabic curriculum or specific needs..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Request Institutional Demo →</button>
            </form>
        </div>
    </div>
</body>
</html>
