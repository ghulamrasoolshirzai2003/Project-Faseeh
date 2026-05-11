// 1. Load Database & Session Master
require 'includes/db.php';

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) { 
    if(isset($_SESSION['user_id'])) {
        // FORCE OFFLINE
        $stmt = $pdo->prepare("UPDATE progress SET last_active = '2000-01-01 00:00:00' WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    session_destroy(); 
    header("Location: /index.php"); 
    exit; 
}

// --- SESSION CLEANER --- (Disabled to prevent ERR_TOO_MANY_REDIRECTS)
/*
if ($_SERVER["REQUEST_METHOD"] != "POST" && !isset($_GET['logout'])) {
    if(isset($_SESSION['user_id'])) {
        if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: /admin_panel.php");
        } else {
            header("Location: /level_select.php");
        }
        exit;
    }
}
*/

// DEBUG MODE (Add ?debug=1 to see session info)
if (isset($_GET['debug'])) {
    echo "<h3>Session Debug Info:</h3>";
    echo "ID: " . session_id() . "<br>";
    echo "Data: <pre>"; print_r($_SESSION); echo "</pre>";
    exit;
}

$msg = "";
$initialView = "login"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // --- LOGIN ---
    if ($action == 'login') {
        $identifier = trim($_POST['username']); 
        $pass = $_POST['password'];

        // Support both Email and Username login (Case-Insensitive)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch();

        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; 
            
            // ONLINE
            $pdo->prepare("UPDATE progress SET last_active = NOW() WHERE user_id = ?")->execute([$row['id']]);

            if($row['role'] == 'admin') header("Location: /admin_panel.php");
            else header("Location: /level_select.php");
            exit;
        } else {
            $msg = "❌ Incorrect Credentials";
            $initialView = "login";
        }

    // --- REGISTER ---
    } elseif ($action == 'register') {
        $name  = trim($_POST['full_name']);
        $user  = trim($_POST['username']);
        $email = trim($_POST['email']);
        $level = $_POST['level'];
        $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $check = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
        $check->execute([$user, $email]);
        if($check->rowCount() > 0) {
            $msg = "❌ Username or Email already exists!";
            $initialView = "register"; 
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, selected_level, role) VALUES (?,?,?,?,?,'student')");
                $stmt->execute([$name, $user, $email, $pass, $level]);
                $newId = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') 
                    ? $pdo->lastInsertId('users_id_seq') 
                    : $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak) VALUES (?, 0, 0, 0, 0)")->execute([$newId]);

                $_SESSION['user_id'] = $newId;
                $_SESSION['username'] = $user;
                $_SESSION['role'] = 'student';
                $_SESSION['level'] = $level;
                
                header("Location: /level_select.php");
                exit;
            } catch(PDOException $e) {
                if (strpos($e->getMessage(), '1062') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                    $msg = "❌ This email or username is already taken. Please login or use another one.";
                } else {
                    $msg = "❌ Error: " . $e->getMessage();
                }
                $initialView = "register";
            }
        }
    }
}

// Success message handling
$success_msg = "";
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success_msg = "✅ Password reset successfully! Please login with your new password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh - Portal</title>
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
            padding: 30px;    
            position: relative;
            z-index: 10;
        }

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

        .toggle-box {
            background: rgba(0,0,0,0.2);
            border-radius: 30px;
            display: flex;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .toggle-btn {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.3s;
            z-index: 2;
        }

        .toggle-btn.active { color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
        .toggle-btn:not(.active) { color: rgba(255,255,255,0.6); }

        .slider {
            position: absolute;
            top: 0; left: 0;
            width: 50%; height: 100%;
            background: linear-gradient(to right, #f2994a, #f2c94c);
            border-radius: 30px;
            transition: 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: 1;
            box-shadow: 0 2px 10px rgba(242, 153, 74, 0.4);
        }

        .form-group { display: none; animation: fadeIn 0.4s; }
        .form-group.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        input, select {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            margin-bottom: 12px;
            width: 100%;
            border-radius: 12px;
            outline: none;
            color: white;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }

        ::placeholder { color: rgba(255, 255, 255, 0.85); font-weight: 500; }
        input:focus, select:focus {
            border-color: var(--accent-orange);
            background-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 10px rgba(243, 156, 18, 0.3);
        }
        
        option { background: #1e3c72; color: white; }

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
        .error-msg { background: rgba(231, 76, 60, 0.8); border-radius: 10px; padding: 10px; color: white; font-size: 0.85rem; margin-bottom: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); }
        .success-msg { background: rgba(46, 204, 113, 0.8); border-radius: 10px; padding: 10px; color: white; font-size: 0.85rem; margin-bottom: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.2); }
        .container::-webkit-scrollbar { width: 6px; }
        .container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
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

        <?php if($msg) echo "<div class='error-msg'>$msg</div>"; ?>
        <?php if($success_msg) echo "<div class='success-msg'>$success_msg</div>"; ?>

        <div class="toggle-box">
            <div class="slider" id="slider"></div>
            <div class="toggle-btn active" id="btn-login" onclick="switchTab('login')">Login</div>
            <div class="toggle-btn" id="btn-register" onclick="switchTab('register')">Register</div>
        </div>

        <div id="form-login" class="form-group active">
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Username or Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button class="action-btn">Login</button>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="reset_pass.php" style="color: rgba(255,255,255,0.5); font-size: 0.8rem; text-decoration: none;">Forgot Password?</a>
                </div>
            </form>
        </div>

        <div id="form-register" class="form-group">
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <input type="text" name="full_name" placeholder="Full Name" required />
                <input type="text" name="username" placeholder="Choose Username" required />
                <input type="email" name="email" placeholder="Email Address" required />
                <select name="level">
                    <option value="Beginner">Level: Beginner</option>
                    <option value="Intermediate">Level: Intermediate</option>
                    <option value="Advanced">Level: Advanced</option>
                </select>
                <input type="password" name="password" placeholder="Create Password" required />
                <button class="action-btn">Create Account</button>
            </form>
        </div>
    </div>

    <script>
        let currentTab = "<?php echo $initialView; ?>";

        function switchTab(tab) {
            const slider = document.getElementById('slider');
            const loginForm = document.getElementById('form-login');
            const registerForm = document.getElementById('form-register');
            const btnLogin = document.getElementById('btn-login');
            const btnRegister = document.getElementById('btn-register');

            if(tab === 'register') {
                slider.style.transform = "translateX(100%)";
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
                btnLogin.classList.remove('active');
                btnRegister.classList.add('active');
            } else {
                slider.style.transform = "translateX(0)";
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
                btnRegister.classList.remove('active');
                btnLogin.classList.add('active');
            }
        }
        switchTab(currentTab);
    </script>

</body>
</html>