<?php
session_start();
require 'includes/db.php';
require 'includes/google_config.php';

if (!isset($_SESSION['lang'])) { 
    $_SESSION['lang'] = 'en'; 
}
// Clear any forced roles from other portals (Teacher/Parent)
unset($_SESSION['force_role']);
$lang = $_SESSION['lang'];

// --- TRANSLATION DICTIONARY ---
$t = ($lang == 'my') ? [
    'title' => 'Selamat Kembali',
    'subtitle' => 'Log masuk untuk meneruskan perjalanan anda.',
    'identifier_ph' => 'Emel atau Nama Pengguna',
    'pass_ph' => 'Kata Laluan',
    'btn' => 'Log Masuk',
    'no_acc' => 'Tiada akaun?',
    'register' => 'Daftar di sini',
    'success_reg' => 'Pendaftaran berjaya! Sila log masuk.',
    'error' => 'Emel/Nama Pengguna atau kata laluan salah!',
    'forgot' => 'Lupa kata laluan?',
    'or' => 'atau',
    'google_btn' => 'Log Masuk dengan Google',
    'google_error' => 'Log masuk Google gagal. Sila cuba lagi.'
] : [
    'title' => 'Welcome Back',
    'subtitle' => 'Sign in to continue your journey.',
    'identifier_ph' => 'Email or Username',
    'pass_ph' => 'Password',
    'btn' => 'Sign In',
    'no_acc' => "Don't have an account?",
    'register' => 'Register here',
    'success_reg' => 'Registration successful! Please login.',
    'error' => 'Invalid credentials!',
    'forgot' => 'Forgot password?',
    'or' => 'or',
    'google_btn' => 'Sign in with Google',
    'google_error' => 'Google sign-in failed. Please try again.'
];

$role_param = $_GET['role'] ?? 'student';
$role_title = 'Welcome Back';
$role_subtitle = 'Sign in to continue your journey.';

if ($role_param === 'teacher') {
    $role_title = 'Teacher Login';
    $role_subtitle = 'Manage your classes and track student growth.';
} elseif ($role_param === 'parent') {
    $role_title = 'Parent Portal Login';
    $role_subtitle = 'Stay connected to your child\'s learning progress.';
}

$error_msg = "";
$success_msg = isset($_GET['registered']) ? $t['success_reg'] : "";
$google_error = isset($_GET['error']) && $_GET['error'] === 'google_failed' ? $t['google_error'] : "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    // Support both Email and Username login
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header("Location: admin_panel.php");
        } elseif ($user['role'] == 'teacher') {
            header("Location: teacher_dashboard.php");
        } elseif ($user['role'] == 'parent') {
            header("Location: parent_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error_msg = $t['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - Faseeh</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23f2994a%22/><text x=%2250%22 y=%2270%22 font-size=%2255%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22serif%22 font-weight=%22bold%22>ف</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'pwa_install.php'; ?>
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

        .auth-footer { text-align: center; margin-top: 15px; font-size: 0.8rem; opacity: 0.6; }
        .auth-footer a { color: var(--accent2); text-decoration: none; font-weight: 600; }

        .forgot-link {
            display: block; text-align: right; margin-top: -5px; margin-bottom: 12px;
            font-size: 0.75rem;
        }
        .forgot-link a { color: var(--accent2); text-decoration: none; opacity: 0.7; }

        .divider {
            display: flex; align-items: center; gap: 15px;
            margin: 15px 0; font-size: 0.7rem; opacity: 0.3; text-transform: uppercase;
            letter-spacing: 2px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--glass-border); }

        .btn-google {
            width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.04); color: white; font-weight: 600; font-size: 0.85rem;
            cursor: pointer; transition: 0.3s; display: flex; align-items: center;
            justify-content: center; gap: 8px; text-decoration: none;
        }
        .btn-google:hover { background: rgba(255,255,255,0.08); transform: translateY(-1px); }
        .btn-google img { width: 16px; height: 16px; }

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
                <h2><?php echo $role_title; ?></h2>
                <p class="subtitle"><?php echo $role_subtitle; ?></p>
            </div>

            <?php if($error_msg) echo "<div class='error-banner'>$error_msg</div>"; ?>
            <?php if($google_error) echo "<div class='error-banner'>$google_error</div>"; ?>
            <?php if($success_msg) echo "<div class='success-banner'>$success_msg</div>"; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Email or Username</label>
                    <input type="text" name="identifier" placeholder="johndoe123" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="forgot-link">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div class="divider">OR</div>

            <a href="<?php echo getGoogleLoginUrl(); ?>" class="btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                Sign in with Google
            </a>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>