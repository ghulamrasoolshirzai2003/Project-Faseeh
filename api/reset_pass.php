<?php
session_start();
require 'includes/db.php';

$msg = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // In a real system, we would send an email here.
        // For now, we allow the user to reset it directly for testing.
        $_SESSION['reset_email'] = $email;
        $success = true;
    } else {
        $msg = "❌ No account found with that email address.";
    }
}

if (isset($_POST['new_password'])) {
    $email = $_SESSION['reset_email'];
    $newPass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$newPass, $email]);
    
    $msg = "✅ Password updated! You can now login.";
    $success = false;
    unset($_SESSION['reset_email']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #0f0c29; --accent: #f39c12; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { background: rgba(255,255,255,0.05); padding: 40px; border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); width: 400px; text-align: center; }
        h2 { margin-bottom: 20px; font-weight: 800; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: white; outline: none; }
        button { width: 100%; padding: 12px; border-radius: 10px; border: none; background: var(--accent); color: white; font-weight: 700; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); opacity: 0.9; }
        .msg { margin-bottom: 20px; font-size: 0.9rem; }
        a { color: var(--accent); text-decoration: none; font-size: 0.8rem; display: block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Recovery</h2>
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        
        <?php if(!$success): ?>
            <p style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 20px;">Enter your email to reset your password.</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <button type="submit">Verify Email</button>
            </form>
        <?php else: ?>
            <p style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 20px;">Verification successful! Enter your new password.</p>
            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required minlength="6">
                <button type="submit">Update Password</button>
            </form>
        <?php endif; ?>
        
        <a href="index.php">Back to Login</a>
    </div>
</body>
</html>