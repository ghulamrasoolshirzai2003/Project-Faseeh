<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO words (arabic_word, hint_malay, hint_english, difficulty) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['arabic'], $_POST['hint_my'], $_POST['hint_en'], $_POST['diff']]);
    $msg = "Word Added!";
}

$userCount = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
$wordCount = $pdo->query("SELECT count(*) FROM words")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="flex-direction:column;">
    <div class="container" style="width:800px; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Admin Dashboard</h2>
            <a href="../includes/logout.php" style="color:red;">Logout</a>
        </div>

        <div style="display:flex; gap:20px; margin:20px 0;">
            <div style="flex:1; background:#5E63BA; color:white; padding:20px; border-radius:10px; text-align:center;">
                <h1><?php echo $userCount; ?></h1>
                <p>Users</p>
            </div>
            <div style="flex:1; background:#4A4E94; color:white; padding:20px; border-radius:10px; text-align:center;">
                <h1><?php echo $wordCount; ?></h1>
                <p>Words</p>
            </div>
        </div>

        <h3>Add New Word</h3>
        <?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
        <form method="POST" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
            <input type="text" name="arabic" placeholder="Arabic (e.g. كتاب)" required style="direction:rtl;">
            <select name="diff">
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
            <input type="text" name="hint_my" placeholder="Malay Hint" required>
            <input type="text" name="hint_en" placeholder="English Hint" required>
            <button type="submit" class="btn-primary" style="grid-column: span 2;">Save Word</button>
        </form>
    </div>
</body>
</html>