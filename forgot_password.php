<?php
session_start();
require 'includes/db.php';

$error_msg = "";
$success_msg = "";
$step = 1;
$identifier = "";
$question = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $identifier = trim($_POST['identifier'] ?? '');

    if ($action === 'verify_user') {
        $stmt = $pdo->prepare("SELECT id, recovery_question, recovery_answer FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user) {
            if (empty($user['recovery_question'])) {
                // Backward compatibility: If no security question is set for this old account, let them reset using DOB
                $step = 2;
                $question = "Enter your date of birth as recovery answer (Format: YYYY-MM-DD):";
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_legacy'] = true;
            } else {
                $step = 2;
                $question = $user['recovery_question'];
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_legacy'] = false;
            }
        } else {
            $error_msg = "❌ Account not found. Please check and try again.";
        }
    } elseif ($action === 'reset_password') {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $answer = trim($_POST['recovery_answer'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$userId) {
            $error_msg = "❌ Session expired. Please start over.";
            $step = 1;
        } elseif (strlen($new_password) < 6) {
            $error_msg = "❌ Password must be at least 6 characters.";
            $step = 2;
            $stmt = $pdo->prepare("SELECT recovery_question FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $question = $stmt->fetchColumn() ?: "Enter your date of birth as recovery answer (Format: YYYY-MM-DD):";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "❌ Passwords do not match!";
            $step = 2;
            $stmt = $pdo->prepare("SELECT recovery_question FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $question = $stmt->fetchColumn() ?: "Enter your date of birth as recovery answer (Format: YYYY-MM-DD):";
        } else {
            // Verify recovery answer
            $stmt = $pdo->prepare("SELECT password, recovery_answer, dob, username, role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            $verified = false;
            if ($_SESSION['reset_legacy'] ?? false) {
                // Match Date of Birth for old accounts
                $verified = ($answer === $user['dob']);
            } else {
                // Match hashed recovery answer (lowercase comparison)
                $verified = password_verify(strtolower($answer), $user['recovery_answer']);
            }

            if ($verified) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashed, $userId]);

                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_legacy']);

                $success_msg = "✅ Password reset successful! Redirecting...";
            } else {
                $error_msg = "❌ Incorrect recovery answer. Please try again.";
                $step = 2;
                $question = $user['recovery_question'] ?: "Enter your date of birth as recovery answer (Format: YYYY-MM-DD):";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Faseeh</title>
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
        input, select {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 10px; padding: 10px 15px; color: white; font-family: inherit;
            font-size: 0.85rem; transition: 0.3s;
        }
        input:focus, select:focus { border-color: var(--accent); outline: none; background: rgba(0,0,0,0.4); }

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
        .success-banner { background: rgba(46,204,113,0.1); border: 1px solid rgba(46,204,113,0.2); color: #55efc4; padding: 8px; border-radius: 8px; margin-bottom: 12px; font-size: 0.75rem; }

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
                <h2>Reset Password</h2>
                <p class="subtitle">Complete the verification to secure your account.</p>
            </div>

            <?php if($error_msg) echo "<div class='error-banner'>$error_msg</div>"; ?>
            <?php if($success_msg) echo "<div class='success-banner'>$success_msg</div>"; ?>

            <?php if(!$success_msg): ?>
                <?php if($step === 1): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="verify_user">
                    <div class="input-group">
                        <label>Email or Username</label>
                        <input type="text" name="identifier" value="<?php echo htmlspecialchars($identifier); ?>" placeholder="Email or Username" required>
                    </div>
                    <button type="submit" class="btn-submit">Verify Account ➔</button>
                </form>
                <?php elseif($step === 2): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="identifier" value="<?php echo htmlspecialchars($identifier); ?>">
                    
                    <div class="input-group" style="text-align: left; background: rgba(0,0,0,0.15); padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); margin-bottom: 18px;">
                        <span style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700; opacity: 0.4; display: block; margin-bottom: 5px;">Your Recovery Question:</span>
                        <strong style="font-size: 0.9rem; color: var(--accent2); line-height: 1.4;"><?php echo htmlspecialchars($question); ?></strong>
                    </div>

                    <div class="input-group">
                        <label>Security Answer</label>
                        <input type="text" name="recovery_answer" placeholder="Type your answer here" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="••••••••" required minlength="6">
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn-submit">Reset & Sign In</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>

            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <?php if($success_msg): ?>
    <script>
        setTimeout(function() {
            window.location.href = 'dashboard.php';
        }, 1500);
    </script>
    <?php endif; ?>
</body>
</html>
