<?php
require 'includes/db.php';
// Hide raw SQL errors from the user's screen
error_reporting(0); 
ini_set('display_errors', 0);

if (!isset($_SESSION['lang'])) { 
    $_SESSION['lang'] = 'en'; 
}
$lang = $_SESSION['lang'];

// --- TRANSLATION DICTIONARY ---
$t = ($lang == 'my') ? [
    'title' => 'Sertai Faseeh',
    'subtitle' => 'Mulakan perjalanan pembelajaran bahasa Arab anda hari ini.',
    'name_ph' => 'Nama Penuh',
    'user_ph' => 'Nama Pengguna',
    'email_ph' => 'Emel',
    'pass_ph' => 'Kata Laluan',
    'btn' => 'Daftar Sekarang',
    'have_acc' => 'Sudah ada akaun?',
    'login' => 'Log Masuk',
    'error' => 'Error! This email or username is already taken. Please try another one or login.'
] : [
    'title' => 'Join Faseeh',
    'subtitle' => 'Start your Arabic learning journey today.',
    'name_ph' => 'Full Name',
    'user_ph' => 'Username',
    'email_ph' => 'Email Address',
    'pass_ph' => 'Password',
    'btn' => 'Create Account',
    'have_acc' => 'Already have an account?',
    'login' => 'Login',
    'error' => 'Error! This email or username is already taken. Please try another one or login.'
];

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // PRE-CHECK: See if email or username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        
        if ($check->rowCount() > 0) {
            $error_msg = $t['error'];
        } else {
            $pdo->beginTransaction();
            
            // 1. Create User
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'student')");
            if (!$stmt->execute([$full_name, $username, $email, $password])) {
                throw new Exception("Insert failed");
            }
            
            // Fix for Postgres lastInsertId requirement
            $user_id = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') 
                ? $pdo->lastInsertId('users_id_seq') 
                : $pdo->lastInsertId();

            // 2. Initialize Progress Record
            $stmt_p = $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak) VALUES (?, 0, 0, 0, 0)");
            $stmt_p->execute([$user_id]);

            $pdo->commit();
            header("Location: /login.php?registered=true");
            exit;
        }
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Final fallback: If it's a duplicate entry, show our nice message
        if (strpos($e->getMessage(), '1062') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
            $error_msg = $t['error'];
        } else {
            // Log other errors but show a generic message to the user
            $error_msg = $t['error']; 
        }
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
            color: white; height: 100vh; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        .auth-container {
            width: 100%; max-width: 450px; padding: 20px; z-index: 10;
            animation: fadeIn 0.8s ease-out;
        }

        .glass-box {
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 30px;
            padding: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        .logo-area { text-align: center; margin-bottom: 30px; }
        .mini-icon {
            width: 65px; height: 65px; background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 15px; box-shadow: 0 10px 20px rgba(242,153,74,0.3);
        }
        .mini-letter { font-family: 'Amiri', serif; font-size: 32px; color: white; margin-top: -5px; }
        
        h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 8px; text-align: center; }
        .subtitle { font-size: 0.85rem; opacity: 0.6; text-align: center; margin-bottom: 30px; line-height: 1.5; }

        .input-group { margin-bottom: 18px; position: relative; }
        .input-group label {
            display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 8px; opacity: 0.5; margin-left: 5px;
        }
        input {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 15px; padding: 14px 20px; color: white; font-family: inherit;
            font-size: 0.95rem; transition: 0.3s;
        }
        input:focus { border-color: var(--accent); outline: none; background: rgba(0,0,0,0.4); }

        .btn-submit {
            width: 100%; padding: 16px; border-radius: 15px; border: none;
            background: linear-gradient(to right, var(--accent), var(--accent2));
            color: white; font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: 0.3s; margin-top: 10px; box-shadow: 0 10px 20px rgba(242,153,74,0.2);
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(242,153,74,0.4); }

        .auth-footer { text-align: center; margin-top: 25px; font-size: 0.9rem; opacity: 0.8; }
        .auth-footer a { color: var(--accent2); text-decoration: none; font-weight: 700; }
        .auth-footer a:hover { text-decoration: underline; }

        .error-banner {
            background: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.3);
            color: #ff7675; padding: 12px; border-radius: 12px; margin-bottom: 20px;
            font-size: 0.85rem; text-align: center;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Floating decoration */
        .shape { position: absolute; border-radius: 50%; filter: blur(80px); z-index: 1; }
        .s1 { width: 300px; height: 300px; background: rgba(94, 99, 186, 0.2); top: -100px; left: -100px; }
        .s2 { width: 250px; height: 250px; background: rgba(242, 153, 74, 0.15); bottom: -50px; right: -50px; }
    </style>
</head>
<body>
    <div class="shape s1"></div>
    <div class="shape s2"></div>

    <div class="auth-container">
        <div class="glass-box">
            <div class="logo-area">
                <div class="mini-icon"><div class="mini-letter">ف</div></div>
                <h2><?php echo $t['title']; ?></h2>
                <p class="subtitle"><?php echo $t['subtitle']; ?></p>
            </div>

            <?php if($error_msg) echo "<div class='error-banner'>$error_msg</div>"; ?>

            <form method="POST">
                <div class="input-group">
                    <label><?php echo $t['name_ph']; ?></label>
                    <input type="text" name="full_name" placeholder="John Doe" required>
                </div>
                <div class="input-group">
                    <label><?php echo $t['user_ph']; ?></label>
                    <input type="text" name="username" placeholder="johndoe123" required>
                </div>
                <div class="input-group">
                    <label><?php echo $t['email_ph']; ?></label>
                    <input type="email" name="email" placeholder="john@example.com" required>
                </div>
                <div class="input-group">
                    <label><?php echo $t['pass_ph']; ?></label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-submit"><?php echo $t['btn']; ?></button>
            </form>

            <div class="auth-footer">
                <?php echo $t['have_acc']; ?> <a href="login.php"><?php echo $t['login']; ?></a>
            </div>
        </div>
    </div>
</body>
</html>