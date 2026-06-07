<?php
session_start();
require 'includes/db.php';
require 'includes/google_config.php';

if (!isset($_SESSION['lang'])) { 
    $_SESSION['lang'] = 'en'; 
}
// Clear any forced roles from other portals
unset($_SESSION['force_role']);
$lang = $_SESSION['lang'];

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = trim($_POST['full_name']);
    $user  = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role  = $_POST['role'] ?? 'student';
    $dob   = $_POST['dob'] ?? null;
    $level = $_POST['level'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Recovery Question
    $rec_q = trim($_POST['recovery_question'] ?? '');
    $rec_a = trim($_POST['recovery_answer'] ?? '');
    $rec_a_hashed = !empty($rec_a) ? password_hash(strtolower($rec_a), PASSWORD_DEFAULT) : null;
    
    // Guardian info (only for students)
    $g_name = ($role === 'student') ? trim($_POST['guardian_name'] ?? '') : null;
    $g_dob  = ($role === 'student') ? ($_POST['guardian_dob'] ?? null) : null;

    $check = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->execute([$user, $email]);
    if($check->rowCount() > 0) {
        $msg = "❌ Username or Email already exists!";
    } else {
        try {
            // Referral Detection
            $referred_by = null;
            if (isset($_GET['ref'])) {
                $ref_code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['ref']));
                $refStmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
                $refStmt->execute([$ref_code]);
                $referrer = $refStmt->fetch();
                if ($referrer) $referred_by = $referrer['id'];
            }

            // Generate unique class code for teachers
            $class_code = ($role === 'teacher') ? strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)) : null;

            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, dob, selected_level, role, guardian_name, guardian_dob, referred_by, class_code, recovery_question, recovery_answer) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $user, $email, $pass, $dob, $level, $role, $g_name, $g_dob, $referred_by, $class_code, $rec_q, $rec_a_hashed]);
            $newId = $pdo->lastInsertId();
            
            if ($referred_by) {
                $pdo->prepare("UPDATE progress SET xp = xp + 100 WHERE user_id = ?")->execute([$referred_by]);
            }

            $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak) VALUES (?, 0, 0, 0, 0)")->execute([$newId]);

            $_SESSION['user_id'] = $newId;
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $role;
            
            // Redirect based on role with welcome flag
            if ($role === 'teacher') header("Location: teacher_dashboard.php?welcome=true");
            else header("Location: dashboard.php?welcome=true");
            exit;
        } catch(PDOException $e) {
            $msg = "❌ Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@400;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #161430; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 20px; overflow-x: hidden;
        }

        .auth-container { width: 100%; max-width: 420px; z-index: 10; animation: fadeIn 0.8s ease-out; }
        .glass-box {
            background: var(--glass); backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border); border-radius: 32px;
            padding: 40px; box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            text-align: center;
        }

        .logo-area { display: flex; flex-direction: column; align-items: center; margin-bottom: 25px; }
        .mini-icon {
            width: 50px; height: 50px; background: linear-gradient(135deg, #f2994a, #f2c94c);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            position: relative; box-shadow: 0 0 25px rgba(242,153,74,0.4); margin-bottom: 12px;
        }
        .mini-icon::after {
            content: ''; position: absolute; width: 42px; height: 42px;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent;
            border-radius: 50%; animation: spin 8s linear infinite;
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 26px; color: white; margin-top: -2px; z-index: 2; }
        .mini-text {
            font-size: 1.8rem; font-weight: 800; margin: 0;
            background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%);
            background-size: 200% auto; color: transparent;
            -webkit-background-clip: text; background-clip: text;
            animation: shine 3s linear infinite; font-family: 'Syne', sans-serif;
        }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shine { to { background-position: 200% center; } }

        .subtitle { font-size: 0.85rem; opacity: 0.6; margin-bottom: 30px; line-height: 1.5; }

        .input-group { margin-bottom: 18px; text-align: left; }
        .input-group label {
            display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.5px; margin-bottom: 6px; opacity: 0.4; margin-left: 5px;
        }
        input, select {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border);
            border-radius: 12px; padding: 14px 18px; color: white; font-family: inherit;
            font-size: 0.95rem; transition: 0.3s; outline: none;
        }
        input:focus, select:focus { border-color: var(--accent); background: rgba(0,0,0,0.5); box-shadow: 0 0 15px rgba(242,153,74,0.1); }

        .btn-submit {
            width: 100%; padding: 15px; border-radius: 14px; border: none;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #1a0f00; font-weight: 800; font-size: 1rem; cursor: pointer;
            transition: 0.3s; margin-top: 10px; box-shadow: 0 10px 20px rgba(242,153,74,0.2);
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(242,153,74,0.4); }

        .btn-google {
            width: 100%; padding: 14px; border-radius: 14px; border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.03); color: white; font-weight: 700; font-size: 0.95rem;
            cursor: pointer; transition: 0.3s; display: flex; align-items: center;
            justify-content: center; gap: 10px; text-decoration: none; margin-top: 20px;
        }
        .btn-google:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.3); }
        .btn-google img { width: 18px; height: 18px; }

        .auth-footer { text-align: center; margin-top: 25px; font-size: 0.9rem; opacity: 0.7; }
        .auth-footer a { color: var(--accent); text-decoration: none; font-weight: 700; border-bottom: 1px solid transparent; transition: 0.3s; }
        .auth-footer a:hover { border-color: var(--accent); }

        .error-banner { background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.2); color: #ff7675; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.85rem; font-weight: 600; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .shape { position: absolute; border-radius: 50%; filter: blur(100px); z-index: 1; }
        .s1 { width: 400px; height: 400px; background: rgba(124, 92, 191, 0.2); top: -150px; left: -150px; }
        .s2 { width: 350px; height: 350px; background: rgba(242, 153, 74, 0.15); bottom: -100px; right: -100px; }
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
                <p class="subtitle">Join the elite academy of Arabic mastery.</p>
            </div>

            <?php if($msg) echo "<div class='error-banner'>$msg</div>"; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Account Type</label>
                    <select name="role" id="role-select" required onchange="toggleFields()">
                        <option value="student" selected>I am a Student</option>
                        <option value="teacher">I am a Teacher</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Faseeh Scholar" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="faseeh123" required>
                    </div>
                    <div class="input-group">
                        <label>Target Level</label>
                        <select name="level">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@faseeh.com" required>
                </div>

                <div class="input-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>
                
                <div id="guardian-fields">
                    <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.5px; margin: 15px 0 10px 5px; opacity: 0.4; text-align: left;">Guardian Verification</div>
                    <div class="input-group">
                        <input type="text" name="guardian_name" placeholder="Guardian Full Name">
                    </div>
                    <div class="input-group">
                        <input type="date" name="guardian_dob">
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                
                <div class="input-group">
                    <label>Password Recovery Question</label>
                    <select name="recovery_question" required>
                        <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                        <option value="In what city were you born?">In what city were you born?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Recovery Answer</label>
                    <input type="text" name="recovery_answer" placeholder="Secret Answer" required>
                </div>
                
                <button type="submit" class="btn-submit">Create My Account</button>
            </form>

            <a href="<?php echo getGoogleLoginUrl(); ?>" class="btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                Continue with Google
            </a>

            <div class="auth-footer">
                Already part of the academy? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>
    <script>
        function toggleFields() {
            const role = document.getElementById('role-select').value;
            const fields = document.getElementById('guardian-fields');
            if (fields) fields.style.display = (role === 'student') ? 'block' : 'none';
            
            const inputs = fields ? fields.querySelectorAll('input') : [];
            inputs.forEach(input => { input.required = (role === 'student'); });
            
            document.cookie = "pending_role=" + role + "; path=/; max-age=3600";
        }
        toggleFields();
    </script>
</body>
</html>