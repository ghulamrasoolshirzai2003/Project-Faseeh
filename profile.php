<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newAvatar = $_POST['avatar'];
    $newBio = trim($_POST['bio']);
    $stmt = $pdo->prepare("UPDATE users SET avatar = ?, bio = ? WHERE id = ?");
    $stmt->execute([$newAvatar, $newBio, $userId]);
    $msg = "Profile updated successfully!";
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT u.username, u.full_name, u.avatar, u.bio, u.selected_level, u.created_at, p.xp, p.academic_xp, p.current_streak, p.wins, p.total_score 
                       FROM users u 
                       JOIN progress p ON u.id = p.user_id 
                       WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch Unlocked Achievements
$stmtAch = $pdo->prepare("SELECT a.title, a.icon, a.description 
                          FROM user_achievements ua 
                          JOIN achievements a ON ua.achievement_id = a.id 
                          WHERE ua.user_id = ?");
$stmtAch->execute([$userId]);
$achievements = $stmtAch->fetchAll();

// Pre-defined avatar options (Emojis for ease of use)
$avatars = ['👨‍🎓', '👩‍🎓', '👳‍♂️', '🧕', '🦁', '🦅', '🕌', '📚', '🌟', '🚀'];
$currentAvatar = $user['avatar'] !== 'default_avatar.png' ? $user['avatar'] : '👨‍🎓';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; }

        .nav { padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px); border-bottom: 1px solid var(--glass-border); }
        .nav a { color: white; text-decoration: none; font-weight: 600; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        .nav a:hover { background: var(--glass); }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }

        .card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; backdrop-filter: blur(10px); }
        
        /* Left Column */
        .profile-header { text-align: center; }
        .avatar-display { font-size: 5rem; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; border: 3px solid var(--accent); }
        .profile-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 5px; }
        .profile-username { opacity: 0.6; font-size: 0.9rem; margin-bottom: 15px; }
        .profile-bio { font-style: italic; opacity: 0.8; font-size: 0.9rem; line-height: 1.5; margin-bottom: 25px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 10px; }
        
        .edit-btn { background: var(--accent); border: none; padding: 10px 20px; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; }
        .edit-btn:hover { background: #e67e22; }

        /* Right Column */
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .stat-box { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--accent); }
        .stat-label { font-size: 0.8rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px; }

        .section-title { font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px; }
        
        .achievements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px; }
        .badge-card { background: rgba(0,0,0,0.2); border: 1px solid rgba(242,153,74,0.3); padding: 15px 10px; border-radius: 15px; text-align: center; transition: 0.3s; }
        .badge-card:hover { transform: translateY(-5px); background: rgba(242,153,74,0.1); }
        .badge-icon { font-size: 2rem; margin-bottom: 5px; }
        .badge-title { font-size: 0.75rem; font-weight: 600; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: var(--bg-mid); border: 1px solid var(--glass-border); padding: 30px; border-radius: 20px; width: 90%; max-width: 400px; }
        .modal h2 { margin-bottom: 20px; }
        .avatar-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 20px; }
        .avatar-option { font-size: 2rem; cursor: pointer; padding: 5px; text-align: center; border-radius: 10px; transition: 0.2s; border: 2px solid transparent; }
        .avatar-option:hover { background: rgba(255,255,255,0.1); }
        .avatar-option.selected { border-color: var(--accent); background: rgba(242,153,74,0.2); }
        .bio-input { width: 100%; padding: 10px; border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; margin-bottom: 20px; font-family: inherit; resize: none; }
        .save-btn { background: var(--success); color: white; border: none; padding: 12px; width: 100%; border-radius: 10px; font-weight: 700; cursor: pointer; }
        .close-btn { background: rgba(255,255,255,0.1); color: white; border: none; padding: 10px; width: 100%; border-radius: 10px; font-weight: 600; cursor: pointer; margin-top: 10px; }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="nav">
        <a href="dashboard.php">← Dashboard</a>
        <div style="font-weight: 700; color: var(--accent);">MY PROFILE</div>
        <a href="index.php?logout=true" style="color: var(--danger); background: rgba(231,76,60,0.1);">Logout</a>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <div class="card profile-header">
            <div class="avatar-display"><?= $currentAvatar ?></div>
            <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
            
            <div class="profile-bio">"<?= htmlspecialchars($user['bio']) ?>"</div>
            
            <p style="font-size: 0.8rem; opacity: 0.5; margin-bottom: 20px;">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
            
            <button class="edit-btn" onclick="document.getElementById('editModal').style.display='flex'">Edit Profile</button>
        </div>

        <!-- Main Content -->
        <div class="card">
            <?php if(isset($msg)): ?>
                <div style="background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color: #2ecc71; padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value"><?= number_format($user['xp'] + $user['academic_xp']) ?></div>
                    <div class="stat-label">Total XP</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $user['current_streak'] ?> 🔥</div>
                    <div class="stat-label">Day Streak</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= ucfirst($user['selected_level']) ?></div>
                    <div class="stat-label">Focus Level</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count($achievements) ?></div>
                    <div class="stat-label">Badges Earned</div>
                </div>
            </div>

            <h3 class="section-title">🏆 Trophy Cabinet</h3>
            <?php if(count($achievements) > 0): ?>
                <div class="achievements-grid">
                    <?php foreach($achievements as $ach): ?>
                        <div class="badge-card" title="<?= htmlspecialchars($ach['description']) ?>">
                            <div class="badge-icon"><?= $ach['icon'] ?></div>
                            <div class="badge-title"><?= htmlspecialchars($ach['title']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="opacity: 0.5; font-style: italic;">No achievements yet. Keep learning to unlock badges!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Profile</h2>
            <form method="POST">
                <input type="hidden" name="avatar" id="avatar-input" value="<?= $currentAvatar ?>">
                
                <p style="margin-bottom: 10px; font-size: 0.9rem;">Choose Avatar:</p>
                <div class="avatar-grid">
                    <?php foreach($avatars as $av): ?>
                        <div class="avatar-option <?= ($currentAvatar == $av) ? 'selected' : '' ?>" onclick="selectAvatar(this, '<?= $av ?>')"><?= $av ?></div>
                    <?php endforeach; ?>
                </div>

                <p style="margin-bottom: 10px; font-size: 0.9rem;">Bio:</p>
                <textarea name="bio" class="bio-input" rows="3" maxlength="150" required><?= htmlspecialchars($user['bio']) ?></textarea>

                <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
            </form>
            <button class="close-btn" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
        </div>
    </div>

    <script>
        function selectAvatar(el, icon) {
            document.querySelectorAll('.avatar-option').forEach(a => a.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('avatar-input').value = icon;
        }
    </script>
</body>
</html>
