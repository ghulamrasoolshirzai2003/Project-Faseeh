<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$userId = $_SESSION['user_id'];
$sessionRole = $_SESSION['role'] ?? 'student';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newAvatar = $_POST['avatar'];
    $newBio = trim($_POST['bio']);
    $stmt = $pdo->prepare("UPDATE users SET avatar = ?, bio = ? WHERE id = ?");
    $stmt->execute([$newAvatar, $newBio, $userId]);
    $msg = "Profile updated successfully!";
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT u.*, p.xp, p.academic_xp, p.current_streak, p.wins, p.total_score 
                       FROM users u 
                       LEFT JOIN progress p ON u.id = p.user_id 
                       WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$role = $user['role']; // Role from database

// Teacher-specific data
$studentCount = 0;
if ($role === 'teacher') {
    $stmtS = $pdo->prepare("SELECT COUNT(*) FROM user_relationships WHERE parent_id = ? AND relationship_type = 'teacher_of'");
    $stmtS->execute([$userId]);
    $studentCount = $stmtS->fetchColumn();
}

// Fetch Unlocked Achievements
$achievements = [];
if ($role === 'student') {
    $stmtAch = $pdo->prepare("SELECT a.title, a.icon, a.description 
                              FROM user_achievements ua 
                              JOIN achievements a ON ua.achievement_id = a.id 
                              WHERE ua.user_id = ?");
    $stmtAch->execute([$userId]);
    $achievements = $stmtAch->fetchAll();
}

// Pre-defined avatar options
$avatars = ['👨‍🏫', '👩‍🏫', '👨‍🎓', '👩‍🎓', '👳‍♂️', '🧕', '🦁', '🦅', '🕌', '📚', '🌟', '🚀'];
$currentAvatar = ($user['avatar'] && $user['avatar'] !== 'default_avatar.png') ? $user['avatar'] : ($role === 'teacher' ? '👨‍🏫' : '👨‍🎓');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&family=Syne:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; }

        .container { max-width: 950px; margin: 0 auto; padding: 120px 20px 40px; display: grid; grid-template-columns: 300px 1fr; gap: 30px; }
        .card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; padding: 35px; backdrop-filter: blur(15px); }
        
        .profile-header { text-align: center; height: fit-content; }
        .avatar-display { font-size: 5.5rem; width: 140px; height: 140px; background: rgba(255,255,255,0.08); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 3px solid var(--accent); box-shadow: 0 0 30px rgba(242,153,74,0.2); }
        .profile-name { font-size: 1.6rem; font-weight: 700; margin-bottom: 5px; font-family: 'Syne', sans-serif; }
        .role-badge { display: inline-block; padding: 4px 12px; background: rgba(255,255,255,0.1); border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--accent); margin-bottom: 15px; }
        
        .profile-bio { font-style: italic; opacity: 0.8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 15px; }
        
        .edit-btn { background: var(--accent); border: none; padding: 12px 20px; border-radius: 12px; color: #1a0f00; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s; }
        .edit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(242,153,74,0.3); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-box { background: rgba(0,0,0,0.25); padding: 25px; border-radius: 20px; text-align: center; border: 1px solid var(--glass-border); }
        .stat-value { font-size: 2rem; font-weight: 800; color: var(--accent); font-family: 'Syne', sans-serif; }
        .stat-label { font-size: 0.75rem; opacity: 0.5; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 8px; font-weight: 600; }

        .section-title { font-size: 1.3rem; font-weight: 700; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 25px; font-family: 'Syne', sans-serif; display: flex; align-items: center; gap: 10px; }
        
        .achievements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 15px; }
        .badge-card { background: rgba(0,0,0,0.2); border: 1px solid rgba(242,153,74,0.1); padding: 20px 10px; border-radius: 18px; text-align: center; transition: 0.4s; }
        .badge-card:hover { transform: scale(1.05); background: rgba(242,153,74,0.08); border-color: var(--accent); }
        .badge-icon { font-size: 2.5rem; margin-bottom: 8px; }
        .badge-title { font-size: 0.8rem; font-weight: 700; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center; }
        .modal-content { background: #1a1635; border: 1px solid var(--glass-border); padding: 40px; border-radius: 28px; width: 90%; max-width: 450px; }
        .avatar-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 25px; }
        .avatar-option { font-size: 2.2rem; cursor: pointer; padding: 10px; text-align: center; border-radius: 15px; transition: 0.2s; border: 2px solid transparent; background: rgba(255,255,255,0.03); }
        .avatar-option:hover { background: rgba(255,255,255,0.08); }
        .avatar-option.selected { border-color: var(--accent); background: rgba(242,153,74,0.15); }

        .bio-input { width: 100%; padding: 15px; border-radius: 15px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; margin-bottom: 25px; font-family: inherit; font-size: 0.95rem; line-height: 1.5; resize: none; }
        .save-btn { background: #3ecf8e; color: #000; border: none; padding: 15px; width: 100%; border-radius: 15px; font-weight: 800; cursor: pointer; font-size: 1rem; transition: 0.3s; }
        .save-btn:hover { background: #32ae76; transform: translateY(-2px); }

        .logout-btn { 
            display: block; text-align: center; margin-top: 20px; 
            text-decoration: none; color: #ff4757; font-weight: 700; 
            padding: 12px; border: 1px solid rgba(255, 71, 87, 0.2); 
            border-radius: 12px; transition: 0.3s; font-size: 0.9rem;
        }
        .logout-btn:hover { background: rgba(255, 71, 87, 0.1); border-color: #ff4757; }

        @media (max-width: 850px) {
            .container { grid-template-columns: 1fr; padding-top: 100px; }
        }
    </style>
</head>
<body>

    <?php require 'includes/navbar.php'; ?>

    <div class="container">
        <!-- Sidebar -->
        <aside class="card profile-header">
            <div class="avatar-display"><?= $currentAvatar ?></div>
            <div class="role-badge"><?= $role ?></div>
            <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
            
            <div class="profile-bio">"<?= htmlspecialchars($user['bio'] ?: 'No bio yet. Click edit to add one!') ?>"</div>
            
            <p style="font-size: 0.8rem; opacity: 0.4; margin-bottom: 25px;">Educating since <?= date('M Y', strtotime($user['created_at'])) ?></p>
            
            <button class="edit-btn" onclick="document.getElementById('editModal').style.display='flex'">Edit Profile</button>
            <a href="index.php?logout=true" class="logout-btn">🚪 Logout Account</a>
        </aside>

        <!-- Main Content -->
        <main class="card">
            <?php if(isset($msg)): ?>
                <div style="background: rgba(62, 207, 142, 0.1); border: 1px solid #3ecf8e; color: #3ecf8e; padding: 15px; border-radius: 15px; margin-bottom: 30px; text-align: center; font-weight: 700;">
                    ✅ <?= $msg ?>
                </div>
            <?php endif; ?>

            <h3 class="section-title"><span>📊</span> <?= ($role === 'teacher' ? 'Classroom Performance' : 'Your Learning Stats') ?></h3>
            
            <div class="stats-grid">
                <?php if ($role === 'teacher'): ?>
                    <div class="stat-box">
                        <div class="stat-value"><?= $studentCount ?></div>
                        <div class="stat-label">Students Tracked</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" style="color: #3ecf8e;"><?= $user['class_code'] ?></div>
                        <div class="stat-label">Invite Code</div>
                    </div>
                <?php else: ?>
                    <div class="stat-box">
                        <div class="stat-value"><?= number_format(($user['xp'] ?? 0) + ($user['academic_xp'] ?? 0)) ?></div>
                        <div class="stat-label">Total XP</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= $user['current_streak'] ?? 0 ?> 🔥</div>
                        <div class="stat-label">Day Streak</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" style="color: #7c5cbf;"><?= ucfirst($user['selected_level'] ?? 'Beginner') ?></div>
                        <div class="stat-label">Learning Path</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?= count($achievements) ?></div>
                        <div class="stat-label">Badges</div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($role === 'student'): ?>
                <h3 class="section-title"><span>🏆</span> Trophy Cabinet</h3>
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
                    <p style="opacity: 0.5; font-style: italic; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 15px; text-align: center;">No achievements yet. Keep learning to unlock badges!</p>
                <?php endif; ?>
            <?php endif; ?>

            <h3 class="section-title" style="margin-top: 50px; color: #ff7675;"><span>⚠️</span> Account Security</h3>
            <div style="background: rgba(231,76,60,0.05); border: 1px solid rgba(231,76,60,0.2); border-radius: 20px; padding: 25px; text-align: center;">
                <p style="font-size: 0.9rem; margin-bottom: 20px; opacity: 0.7; line-height: 1.5;">Once you delete your account, all your progress, XP, and classroom data will be permanently erased. This cannot be undone.</p>
                <button onclick="confirmDelete()" style="background: #e74c3c; border: none; padding: 14px 28px; border-radius: 12px; color: white; font-weight: 800; cursor: pointer; transition: 0.3s;">Delete Account Forever</button>
            </div>
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 style="font-family: 'Syne', sans-serif; margin-bottom: 25px;">Update Profile</h2>
            <form method="POST">
                <input type="hidden" name="avatar" id="avatar-input" value="<?= $currentAvatar ?>">
                
                <p style="margin-bottom: 12px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6;">Choose Avatar</p>
                <div class="avatar-grid">
                    <?php foreach($avatars as $av): ?>
                        <div class="avatar-option <?= ($currentAvatar == $av) ? 'selected' : '' ?>" onclick="selectAvatar(this, '<?= $av ?>')"><?= $av ?></div>
                    <?php endforeach; ?>
                </div>

                <p style="margin-bottom: 12px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6;">Bio</p>
                <textarea name="bio" class="bio-input" rows="3" maxlength="150" required><?= htmlspecialchars($user['bio']) ?></textarea>

                <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
            </form>
            <button class="close-btn" onclick="document.getElementById('editModal').style.display='none'">Discard Changes</button>
        </div>
    </div>

    <!-- Custom Deletion Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="text-align: center; border: 1px solid rgba(231,76,60,0.3); background: #0e0c1e;">
            <div style="font-size: 4rem; margin-bottom: 20px;">🚨</div>
            <h2 style="font-family: 'Syne', sans-serif; color: #ff7675; margin-bottom: 15px;">Are you sure?</h2>
            <p style="font-size: 0.95rem; line-height: 1.6; opacity: 0.8; margin-bottom: 30px;">
                This action is <strong>permanent</strong>. All your data will be wiped from the Faseeh Academy servers.
            </p>
            
            <div id="step1-btns">
                <button onclick="nextDeleteStep()" class="edit-btn" style="background: #e74c3c; margin-bottom: 12px;">Yes, I Understand</button>
                <button onclick="closeDeleteModal()" style="background: none; border: none; color: white; opacity: 0.5; cursor: pointer;">Cancel</button>
            </div>

            <div id="step2-btns" style="display: none;">
                <p style="color: #ff7675; font-weight: 800; margin-bottom: 20px;">FINAL CONFIRMATION REQUIRED</p>
                <button onclick="executeDelete()" class="edit-btn" style="background: #c0392b;">CONFIRM PERMANENT DELETE</button>
                <button onclick="closeDeleteModal()" style="background: none; border: none; color: white; opacity: 0.5; margin-top: 10px; cursor: pointer;">Go Back</button>
            </div>
        </div>
    </div>

    <script>
        function selectAvatar(el, icon) {
            document.querySelectorAll('.avatar-option').forEach(a => a.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('avatar-input').value = icon;
        }

        function confirmDelete() {
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('step1-btns').style.display = 'block';
            document.getElementById('step2-btns').style.display = 'none';
        }

        function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }
        function nextDeleteStep() {
            document.getElementById('step1-btns').style.display = 'none';
            document.getElementById('step2-btns').style.display = 'block';
        }

        function executeDelete() {
            const btn = document.querySelector('#step2-btns button');
            btn.innerHTML = "Processing...";
            btn.disabled = true;

            fetch('api/delete_account.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) { window.location.href = 'index.php?account_deleted=true'; } 
                else { alert("Error: " + data.message); closeDeleteModal(); }
            })
            .catch(err => { alert("Connection Error."); closeDeleteModal(); });
        }
    </script>
</body>
</html>
