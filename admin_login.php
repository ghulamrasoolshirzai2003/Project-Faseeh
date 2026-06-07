<?php
session_start();
require 'includes/db.php';

// ==========================================================
// 1. LOGOUT LOGIC (MUST BE FIRST!)
// ==========================================================
if (isset($_GET['logout'])) {
    // Force Offline in Database (Optional but good)
    if(isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE progress SET last_active = '2000-01-01 00:00:00' WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }

    session_unset();    // Clear variables
    session_destroy();  // Kill session
    
    // Refresh page cleanly to remove '?logout=true' from URL
    header("Location: admin_login.php");
    exit;
}

// ==========================================================
// 2. CHECK IF ALREADY LOGGED IN
// (Only run this IF we are not trying to logout)
// ==========================================================
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_panel.php");
    exit;
}

$msg = "";

// ==========================================================
// 3. LOGIN FORM HANDLER
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if ($row && password_verify($pass, $row['password'])) {
        if ($row['role'] === 'admin') {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = 'admin'; 
            
            // Update Online Status
            $pdo->prepare("UPDATE progress SET last_active = NOW() WHERE user_id = ?")->execute([$row['id']]);
            
            header("Location: admin_panel.php");
            exit;
        } else {
            $msg = "❌ Access Denied: This portal is for Staff Only.";
        }
    } else {
        $msg = "❌ Invalid Admin Credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Faseeh</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
     <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 20px; overflow-x: hidden;
        }

        .auth-container {
            width: 100%; max-width: 380px; z-index: 10;
            animation: fadeIn 0.8s ease-out;
        }

        .glass-box {
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 24px;
            padding: 25px 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            text-align: center;
        }

        /* --- DASHBOARD BRANDING SYNC --- */
        .logo-area { display: flex; flex-direction: column; align-items: center; margin-bottom: 15px; }
        .mini-icon {
            width: 40px; height: 40px; background: linear-gradient(135deg, #f2994a, #f2c94c);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4); margin-bottom: 8px;
        }
        .mini-icon::after {
            content: ''; position: absolute; width: 32px; height: 32px;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent;
            border-radius: 50%; animation: spinNavbar 8s linear infinite;
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 20px; color: white; margin-top: -2px; z-index: 2; }
        .mini-text {
            font-size: 1.5rem; font-weight: 800; margin: 0;
            background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%);
            background-size: 200% auto; color: transparent;
            -webkit-background-clip: text; background-clip: text;
            animation: shineNavbar 3s linear infinite; font-family: 'Poppins', sans-serif;
        }
        @keyframes spinNavbar { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shineNavbar { to { background-position: 200% center; } }

        h2 { font-size: 1.3rem; font-weight: 700; margin-top: 5px; margin-bottom: 2px; }
        .subtitle { font-size: 0.75rem; opacity: 0.5; margin-bottom: 15px; }

        .input-group { margin-bottom: 12px; position: relative; text-align: left; }
        .input-group label {
            display: block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 5px; opacity: 0.4; margin-left: 5px;
        }
        input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 10px; padding: 10px 15px; color: white; font-family: inherit;
            font-size: 0.85rem; transition: 0.3s;
        }
        input:focus { border-color: var(--accent); outline: none; background: rgba(0,0,0,0.4); }

        .btn-submit {
            width: 100%; padding: 12px; border-radius: 10px; border: none;
            background: linear-gradient(to right, var(--accent), var(--accent2));
            color: #1a0f00; font-weight: 700; font-size: 0.9rem; cursor: pointer;
            transition: 0.3s; margin-top: 5px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(242,153,74,0.3); }

        .back-link { text-align: center; margin-top: 15px; font-size: 0.8rem; opacity: 0.6; }
        .back-link a { color: var(--accent2); text-decoration: none; font-weight: 600; }

        .error-banner { background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.2); color: #ff7675; padding: 8px; border-radius: 8px; margin-bottom: 12px; font-size: 0.75rem; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Floating decoration */
        .shape { position: absolute; border-radius: 50%; filter: blur(80px); z-index: 1; }
        .s1 { width: 250px; height: 250px; background: rgba(94, 99, 186, 0.2); top: -80px; left: -80px; }
        .s2 { width: 200px; height: 200px; background: rgba(242, 153, 74, 0.15); bottom: -40px; right: -40px; }
    </style>
</head>
<body>
    <div class="shape s1"></div>
    <div class="shape s2"></div>

    <div class="auth-container">
        <div class="glass-box">
            <div class="logo-area">
                <div class="mini-icon"><div class="mini-letter">ف</div></div>
                <h1 class="mini-text">Faseeh</h1>
                <h2>Admin Login</h2>
                <p class="subtitle">Secure access for academy staff only.</p>
            </div>

            <?php if($msg) echo "<div class='error-banner'>$msg</div>"; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" placeholder="Admin Username" required />
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                
                <button type="submit" class="btn-submit">Secure Login</button>
            </form>
            
            <div class="back-link">
                <a href="login.php">← Back to Student Portal</a>
            </div>
        </div>
    </div>
</body>
</html>