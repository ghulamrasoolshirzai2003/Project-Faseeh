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
        /* --- PREMIUM THEME (Matches Student Portal) --- */
        :root { 
            --bg-blue-start: #1e3c72;
            --bg-blue-end: #2a5298;
            --accent-orange: #f39c12; 
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-blue-start), var(--bg-blue-end));
            display: flex; justify-content: center; align-items: center; height: 100vh; color: white; overflow: hidden;
        }

        /* --- THE GLASS CARD --- */
        .container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 420px; max-width: 90%; padding: 40px; position: relative; z-index: 10; text-align: center;
        }

        /* --- GLOWING LOGO ANIMATIONS --- */
        .brand-logo {
            display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 30px;
            /* Slight 3D tilt */
            transform: perspective(500px) rotateX(5deg);
        }

        .logo-icon {
            width: 60px; height: 60px; background: linear-gradient(135deg, #f2994a, #f2c94c);
            border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative;
            animation: iconPulse 2s infinite ease-in-out; 
            box-shadow: 0 0 15px rgba(242, 153, 74, 0.6);
        }
        
        @keyframes iconPulse {
            0% { box-shadow: 0 0 10px rgba(242, 153, 74, 0.6); }
            50% { box-shadow: 0 0 25px rgba(242, 153, 74, 1), 0 0 40px rgba(255, 215, 0, 0.4); scale: 1.05; }
            100% { box-shadow: 0 0 10px rgba(242, 153, 74, 0.6); }
        }

        .logo-icon::after {
            content: ''; position: absolute; width: 48px; height: 48px;
            border: 3px solid rgba(255,255,255,0.5); border-top-color: transparent; border-radius: 50%;
            animation: spinSlow 8s linear infinite;
        }
        @keyframes spinSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .icon-letter { font-family: 'Amiri', serif; font-size: 32px; color: white; margin-top: -5px; z-index: 2; }

        .logo-text h1 {
            font-family: 'Poppins', sans-serif; font-size: 2.2rem; font-weight: 900; margin: 0; line-height: 1;
            background: linear-gradient(to right, #fff 20%, #ffd700 50%, #fff 80%); background-size: 200% auto;
            color: transparent; -webkit-background-clip: text; background-clip: text;
            animation: shine 3s linear infinite, textGlowPulse 2s ease-in-out infinite;
        }
        
        @keyframes shine { to { background-position: 200% center; } }
        @keyframes textGlowPulse {
            0%, 100% { text-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
            50% { text-shadow: 0 0 25px rgba(255, 215, 0, 0.6); }
        }

        .logo-text span {
            font-size: 0.75rem; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,0.8); display: block; margin-top: 5px; font-weight: 600;
        }

        /* --- FORM STYLING --- */
        input {
            background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px; margin-bottom: 12px; width: 100%; border-radius: 12px;
            outline: none; color: white; transition: 0.3s; font-family: 'Poppins', sans-serif;
        }
        ::placeholder { color: rgba(255, 255, 255, 0.85); font-weight: 500; }
        input:focus { border-color: var(--accent-orange); background-color: rgba(255, 255, 255, 0.25); box-shadow: 0 0 10px rgba(243, 156, 18, 0.3); }

        button.action-btn {
            width: 100%; border-radius: 12px; border: none;
            background: linear-gradient(to right, #f2994a, #f2c94c); color: white;
            font-size: 1rem; font-weight: 700; padding: 15px; text-transform: uppercase;
            cursor: pointer; margin-top: 15px; transition: 0.3s; box-shadow: 0 5px 15px rgba(242, 153, 74, 0.3);
        }
        button.action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(242, 153, 74, 0.5); }
        
        .error-msg { background: rgba(231, 76, 60, 0.8); border-radius: 10px; padding: 10px; color: white; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.2); }
        
        .back-link { display: block; margin-top: 25px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem; transition: 0.3s; }
        .back-link:hover { color: white; text-shadow: 0 0 5px rgba(255,255,255,0.5); }
    </style>
</head>
<body>

    <div class="container">
        <div class="brand-logo">
            <div class="logo-icon">
                <div class="icon-letter">ف</div>
            </div>
            <div class="logo-text">
                <h1>Faseeh</h1>
                <span>Admin Access</span>
            </div>
        </div>

        <?php if($msg) echo "<div class='error-msg'>$msg</div>"; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Admin Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <button class="action-btn">Secure Login</button>
        </form>
        
        <a href="index.php" class="back-link">← Back to Student Portal</a>
    </div>

</body>
</html>