<?php
session_start();
// --- THE MASTER SHIELD ---
error_reporting(0); 
ini_set('display_errors', 0);

require 'includes/db.php';

$msg = "";
$type = "info";

// Only run this if the user actually clicked the button
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $newPass = $_POST['new_password'] ?? '';
    
    if (empty($email) || empty($newPass)) {
        $msg = "❌ Please fill in both fields.";
        $type = "error";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // Instant Reset
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->execute([$hashed, $email]);
            
            $msg = "✅ Password Updated! Redirecting to login...";
            $type = "success";
            header("refresh:2;url=index.php");
        } else {
            $msg = "❌ No account found with that email.";
            $type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh - Reset Password</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --bg-blue-start: #0f0c29;
            --bg-blue-mid: #302b63;
            --bg-blue-end: #24243e;
            --accent-orange: #f39c12; 
            --accent-gold: #FFD700;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-blue-start), var(--bg-blue-mid), var(--bg-blue-end));
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
            overflow: hidden;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 450px;
            max-width: 90%;
            max-height: 90vh; 
            overflow-y: auto; 
            margin: 20px;     
            padding: 40px;    
            position: relative;
            z-index: 10;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* --- THE ULTRA-GLOW VIDEO LOGO CSS --- */
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
            transform: perspective(500px) rotateX(5deg);
        }

        .logo-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #f2994a, #f2c94c);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            animation: iconPulse 2s infinite ease-in-out;
        }
        
        @keyframes iconPulse {
            0% { box-shadow: 0 0 15px rgba(242, 153, 74, 0.6); }
            50% { box-shadow: 0 0 35px rgba(242, 153, 74, 1), 0 0 60px rgba(255, 215, 0, 0.6); scale: 1.05; }
            100% { box-shadow: 0 0 15px rgba(242, 153, 74, 0.6); }
        }

        .logo-icon::after {
            content: ''; position: absolute;
            width: 48px; height: 48px;
            border: 3px solid rgba(255,255,255,0.5); 
            border-top-color: transparent; 
            border-radius: 50%;
            animation: spinSlow 8s linear infinite;
        }
        
        @keyframes spinSlow {
            from { transform: rotate(0deg); } to { transform: rotate(360deg); }
        }

        .icon-letter {
            font-family: 'Amiri', serif; font-size: 32px; color: white;
            margin-top: -6px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
            z-index: 2;
        }

        .logo-text h1 {
            font-family: 'Poppins', sans-serif; font-size: 2.2rem; font-weight: 900;
            margin: 0; line-height: 1;
            background: linear-gradient(to right, #fff 20%, #ffd700 50%, #fff 80%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text; background-clip: text;
            animation: shine 3s linear infinite, textGlowPulse 2s ease-in-out infinite;
        }

        @keyframes shine { to { background-position: 200% center; } }
        @keyframes textGlowPulse {
            0%, 100% { text-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
            50% { text-shadow: 0 0 25px rgba(255, 215, 0, 0.8), 0 0 40px rgba(242, 153, 74, 0.5); }
        }

        .logo-text span {
            font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase;
            color: rgba(255,255,255,0.8); display: block; margin-top: 5px; font-weight: 500;
        }

        h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 10px; text-align: center; }
        .subtitle { font-size: 0.85rem; opacity: 0.6; text-align: center; margin-bottom: 30px; line-height: 1.5; }

        input {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            margin-bottom: 15px;
            width: 100%;
            border-radius: 12px;
            outline: none;
            color: white;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }

        ::placeholder { color: rgba(255, 255, 255, 0.85); font-weight: 500; }
        input:focus {
            border-color: var(--accent-orange);
            background-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 10px rgba(243, 156, 18, 0.3);
        }

        button.action-btn {
            width: 100%;
            border-radius: 12px;
            border: none;
            background: linear-gradient(to right, #f2994a, #f2c94c);
            color: white;
            font-size: 1rem;
            font-weight: 700;
            padding: 15px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(242, 153, 74, 0.3);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        button.action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(242, 153, 74, 0.5); }

        .msg { 
            padding: 12px; border-radius: 12px; font-size: 0.85rem; margin-bottom: 20px; text-align: center;
            border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2);
        }
        .success { color: #55efc4; border-color: rgba(46, 204, 113, 0.3); }
        .error { color: #ff7675; border-color: rgba(231, 76, 60, 0.3); }
        
        .footer-link { text-align: center; margin-top: 25px; }
        .footer-link a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; transition: 0.3s; }
        .footer-link a:hover { color: var(--accent-gold); }
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
                <span>Mastery Portal</span>
            </div>
        </div>

        <h2>Password Reset</h2>
        <p class="subtitle">Enter your details to instantly update your password.</p>

        <?php if($msg): ?>
            <div class="msg <?php echo $type; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required />
            <input type="password" name="new_password" placeholder="New Password" required minlength="6" />
            <button class="action-btn">Update & Login</button>
        </form>
        
        <div class="footer-link">
            <a href="index.php">Return to Academy Portal</a>
        </div>
    </div>

</body>
</html>