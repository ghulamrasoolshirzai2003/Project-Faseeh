<?php
// clash_join.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Class Clash | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px;
        }

        .join-card {
            width: 100%; max-width: 420px;
            background: var(--glass); border: 1.5px solid var(--glass-border);
            border-radius: 30px; padding: 40px 30px; backdrop-filter: blur(15px);
            text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            animation: slideUp 0.6s ease;
        }

        .brand-icon {
            width: 65px; height: 65px; background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-family: 'Amiri', serif; font-size: 28px; font-weight: bold;
            box-shadow: 0 8px 20px rgba(242,153,74,0.3);
        }

        h2 { font-weight: 800; font-size: 1.6rem; margin-bottom: 5px; }
        p { opacity: 0.6; font-size: 0.85rem; margin-bottom: 30px; }

        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-size: 0.8rem; font-weight: 600; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        
        input {
            width: 100%; padding: 15px 20px; border-radius: 12px;
            background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border);
            color: white; font-size: 1.1rem; font-weight: 700; transition: 0.3s;
            text-align: center;
        }
        input:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 15px rgba(242,153,74,0.25); }

        .join-btn {
            width: 100%; background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: white; border: none; padding: 16px; border-radius: 15px;
            font-size: 1.05rem; font-weight: 800; cursor: pointer; margin-top: 10px;
            box-shadow: 0 8px 25px rgba(242,153,74,0.2); transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .join-btn:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(242,153,74,0.3); }

        .exit-link { display: inline-block; margin-top: 20px; font-size: 0.8rem; color: white; opacity: 0.5; text-decoration: none; transition: 0.3s; }
        .exit-link:hover { opacity: 0.9; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="join-card">
        <div class="brand-icon">ف</div>
        <h2>Classroom Clash</h2>
        <p>Enter the game PIN to compete with your class!</p>

        <form id="join-form" onsubmit="handleJoin(event)">
            <div class="input-group">
                <label>Game PIN</label>
                <input type="text" id="room-pin" placeholder="0000" maxlength="4" autocomplete="off" required>
            </div>
            
            <div class="input-group" style="margin-bottom: 25px;">
                <label>Your Nickname</label>
                <input type="text" id="nickname" placeholder="e.g. Sultan" maxlength="15" autocomplete="off" required value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>">
            </div>

            <button type="submit" class="join-btn">🚀 Join Clash</button>
        </form>

        <a href="dashboard.php" class="exit-link">← Return to Dashboard</a>
    </div>

    <script>
        async function handleJoin(e) {
            e.preventDefault();
            const pin = document.getElementById('room-pin').value.trim();
            const nickname = document.getElementById('nickname').value.trim();

            if (!pin || !nickname) return;

            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'join_room', pin: pin, nickname: nickname})
            });
            const data = await res.json();
            
            if (data.success) {
                location.href = 'clash_play.php';
            } else {
                alert("Could not join lobby: " + data.error);
            }
        }
    </script>
</body>
</html>
