<?php
session_start();
require 'includes/db.php';

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['full_name']);
    $dob = $_POST['dob'];

    // Check if any student has this person as a guardian
    $stmt = $pdo->prepare("SELECT id FROM users WHERE guardian_name = ? AND guardian_dob = ? AND role = 'student' LIMIT 1");
    $stmt->execute([$name, $dob]);
    $match = $stmt->fetch();

    if ($match) {
        // We don't create a 'user' account for the parent, 
        // we just store their guardian info in the session to authorize access.
        $_SESSION['parent_auth'] = true;
        $_SESSION['parent_name'] = $name;
        $_SESSION['parent_dob'] = $dob;
        $_SESSION['role'] = 'parent';
        
        header("Location: parent_dashboard.php");
        exit;
    } else {
        $msg = "❌ No student records found matching this Guardian name and Date of Birth.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal Access - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Syne:wght@800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --glass: rgba(255, 255, 255, 0.08);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .login-box {
            width: 100%; max-width: 400px; background: var(--glass);
            border: 1px solid rgba(255,255,255,0.12); padding: 40px;
            border-radius: 24px; backdrop-filter: blur(20px); text-align: center;
        }
        h2 { font-family: 'Syne', sans-serif; font-size: 1.8rem; margin-bottom: 10px; }
        p { opacity: 0.6; font-size: 0.9rem; margin-bottom: 30px; }
        input {
            width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
            padding: 14px; border-radius: 12px; color: white; margin-bottom: 15px; outline: none;
        }
        .btn {
            width: 100%; padding: 14px; border-radius: 12px; border: none;
            background: linear-gradient(to right, #f2994a, #f2c94c);
            color: #1a0f00; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(242,153,74,0.3); }
        .error { color: #ff7675; font-size: 0.85rem; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div style="font-size: 3rem; margin-bottom: 10px;">🏠</div>
        <h2>Parent Portal</h2>
        <p>Enter your details to view your child's progress.</p>

        <?php if($msg) echo "<div class='error'>$msg</div>"; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Your Full Name" required>
            <input type="date" name="dob" required title="Your Date of Birth">
            <button type="submit" class="btn">View Child Progress →</button>
        </form>
        
        <div style="margin-top: 25px;">
            <a href="index.php" style="color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.8rem;">← Back to Landing Page</a>
        </div>
    </div>
</body>
</html>
