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
    <title>Fast Reset - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #0f0c29; 
            --accent: #f39c12; 
            --glass: rgba(255, 255, 255, 0.08);
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: radial-gradient(circle at center, #1e3c72 0%, #0f0c29 100%);
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            overflow: hidden;
        }
        .box { 
            background: var(--glass); 
            padding: 40px; 
            border-radius: 30px; 
            backdrop-filter: blur(25px); 
            border: 1px solid rgba(255,255,255,0.1); 
            width: 400px; 
            text-align: center; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        h2 { margin-bottom: 10px; font-weight: 900; font-size: 2rem; letter-spacing: -1px; }
        p { font-size: 0.85rem; opacity: 0.6; margin-bottom: 30px; }
        
        input { 
            width: 100%; padding: 15px; margin-bottom: 15px; 
            border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); 
            background: rgba(0,0,0,0.3); color: white; outline: none; 
            font-family: inherit; transition: 0.3s;
        }
        input:focus { border-color: var(--accent); background: rgba(0,0,0,0.5); }
        
        button { 
            width: 100%; padding: 16px; border-radius: 15px; border: none; 
            background: linear-gradient(to right, #f2994a, #f2c94c); 
            color: white; font-weight: 800; font-size: 1rem; cursor: pointer; 
            transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
            box-shadow: 0 10px 20px rgba(242,153,74,0.3);
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(242,153,74,0.5); }
        
        .msg { 
            padding: 12px; border-radius: 12px; font-size: 0.9rem; margin-bottom: 20px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        }
        .success { color: #2ecc71; border-color: rgba(46, 204, 113, 0.3); }
        .error { color: #e74c3c; border-color: rgba(231, 76, 60, 0.3); }
        
        a { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; display: block; margin-top: 25px; transition: 0.3s; }
        a:hover { color: var(--accent); }
    </style>
</head>
<body>
    <div class="box">
        <h2>Fast Reset</h2>
        <p>Enter your details to instantly update your password.</p>
        
        <?php if($msg) echo "<div class='msg $type'>$msg</div>"; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Confirmed Email Address" required>
            <input type="password" name="new_password" placeholder="New Password" required minlength="6">
            <button type="submit">Update & Login</button>
        </form>
        
        <a href="index.php">Return to Academy Portal</a>
    </div>
</body>
</html>