<?php
// referral.php — Faseeh Referral System (Live Edition)
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');

// --- AUTO-SCHEMA REPAIR (Ensures DB is ready) ---
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS referral_code VARCHAR(10) UNIQUE");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS referred_by INT DEFAULT NULL");
    $pdo->exec("ALTER TABLE progress ADD COLUMN IF NOT EXISTS premium_until DATETIME DEFAULT NULL");
} catch (Exception $e) { /* Already exists or not supported */ }

// Ensure user has a referral code
$stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->execute([$uid]);
$row = $stmt->fetch();
$referral_code = $row['referral_code'];

if (!$referral_code) {
    $referral_code = strtoupper(substr(md5('faseeh_ref_' . $uid . time()), 0, 8));
    $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?")->execute([$referral_code, $uid]);
}

$referral_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/register.php?ref=" . $referral_code;

// --- FETCH LIVE STATS ---
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE referred_by = ?");
$stmt->execute([$uid]);
$total_joined = $stmt->fetch()['cnt'] ?? 0;

$stats = [
  'total_invited'  => $total_joined, // For now, joined = invited
  'total_joined'   => $total_joined,
  'total_active'   => $total_joined,
  'bonus_days'     => $total_joined * 7, // 7 days per friend
  'pending_reward' => 0,
];

$tiers = [
  ['invited'=>1,  'reward'=>'7 days Premium',        'icon'=>'🎁', 'xp'=>100,  'color'=>'#3ecf8e'],
  ['invited'=>3,  'reward'=>'1 month Premium',        'icon'=>'🌟', 'xp'=>300,  'color'=>'#f2994a'],
  ['invited'=>5,  'reward'=>'3 months Premium',       'icon'=>'🚀', 'xp'=>500,  'color'=>'#7c5cbf'],
  ['invited'=>10, 'reward'=>'1 year Premium + Badge', 'icon'=>'👑', 'xp'=>1000, 'color'=>'#FFD700'],
  ['invited'=>25, 'reward'=>'Lifetime Premium',       'icon'=>'💎', 'xp'=>2500, 'color'=>'#e74c3c'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Refer Friends — Faseeh</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
<style>
:root {
    --bg: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
    --bg-card: rgba(255,255,255,0.06); --bg-card2: rgba(255,255,255,0.1);
    --border: rgba(255,255,255,0.1); --accent: #f2994a; --accent2: #f2c94c;
    --gold: #FFD700; --text: #f0eeff; --muted: #8b87b0; --radius: 24px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    background: linear-gradient(135deg, var(--bg), var(--bg-mid), var(--bg-end));
    color: var(--text); font-family: 'Poppins', sans-serif; min-height: 100vh;
}
nav {
    position: sticky; top: 0; background: rgba(14,12,30,0.8); backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border); padding: 0 32px; height: 70px;
    display: flex; align-items: center; justify-content: space-between; z-index: 100;
}
.nav-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.4rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Amiri', serif;}
.nav-logo span{color:var(--accent)}

.page{max-width:1000px;margin:0 auto;padding:40px 24px 80px}

.ref-hero{
    background: var(--bg-card); border: 1px solid var(--border); border-radius: 30px;
    padding: 60px 40px; text-align: center; margin-bottom: 40px; position: relative; overflow: hidden;
    backdrop-filter: blur(20px); box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.ref-emoji{font-size:4rem;margin-bottom:20px;display:block;}
.ref-hero h1{font-weight:800;font-size:2.8rem;margin-bottom:15px;color: #fff;}
.ref-hero p{color:var(--muted);font-size:1.1rem;max-width:600px;margin:0 auto 35px;line-height:1.7}

.link-box{
  background: rgba(0,0,0,0.3); border: 1px solid rgba(242,153,74,0.3);
  border-radius: 20px; padding: 25px; max-width: 650px; margin: 0 auto 30px;
}
.link-label{font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--accent);margin-bottom:12px}
.link-row{display:flex;gap:12px;align-items:center}
.link-url{
  flex:1;background:rgba(255,255,255,.05); border:1px solid var(--border);
  border-radius:12px; padding:15px 20px; color:var(--text); font-size:.9rem;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.copy-btn{
  flex-shrink:0; background:linear-gradient(135deg,var(--accent),var(--accent2));
  color:#000; border:none; padding:15px 25px; border-radius:12px;
  font-weight:700; font-size:.9rem; cursor:pointer; transition:.3s;
}
.copy-btn:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(242,153,74,0.2)}

.ref-code{
  font-size:2.2rem; font-weight:800; letter-spacing:.2em; color:var(--gold);
  background:rgba(255,215,0,0.1); border:1px solid rgba(255,215,0,0.2);
  border-radius:15px; padding:15px 40px; display:inline-block; margin-bottom:10px;
}

.share-row{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:30px}
.share-btn{
  display:flex;align-items:center;gap:10px;padding:14px 28px;border-radius:50px;
  font-size:.9rem;font-weight:700;cursor:pointer;transition:.3s;text-decoration:none;
}
.share-whatsapp{background:#25D366;color:#fff}
.share-telegram{background:#0088cc;color:#fff}
.share-twitter{background:#1DA1F2;color:#fff}
.share-btn:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.2)}

.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:50px}
.stat-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:25px;text-align:center;backdrop-filter:blur(10px);
}
.stat-num{font-size:2.2rem;font-weight:800;color:var(--accent);display:block}
.stat-label{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:5px}

.tiers-list{display:flex;flex-direction:column;gap:15px;margin-bottom:60px}
.tier-row{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:25px;display:flex;align-items:center;gap:25px;transition:.3s;
}
.tier-row.achieved{border-color:var(--tier-color);background:rgba(62,207,142,0.05)}
.tier-icon{font-size:2.2rem;width:60px;text-align:center}
.tier-info{flex:1}
.tier-invited{font-weight:700;font-size:1.1rem;margin-bottom:4px}
.tier-reward{font-size:.95rem;color:var(--tier-color)}
.tier-progress-bar{height:6px;background:rgba(255,255,255,0.1);border-radius:10px;overflow:hidden;margin-top:10px}
.tier-progress-fill{height:100%;border-radius:10px;background:var(--tier-color);transition:width 1s ease-out}

.friends-section{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:30px;backdrop-filter:blur(10px)}
#toast{
  position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(100px);
  background:var(--accent);color:#000;padding:15px 30px;border-radius:50px;
  font-weight:800;z-index:1000;transition:0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
#toast.show{transform:translateX(-50%) translateY(0)}

@media(max-width:768px){
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .ref-hero{padding:40px 20px}
}
</style>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:600">← Dashboard</a>
</nav>

<div class="page">

  <div class="ref-hero">
    <span class="ref-emoji">🎁</span>
    <h1>Refer Friends, Earn Premium</h1>
    <p>Invite your circle to Faseeh. Every friend who joins using your link unlocks free Premium time for both of you!</p>

    <div class="link-box">
      <div class="link-label">Your Unique Referral Link</div>
      <div class="link-row">
        <div class="link-url" id="ref-url"><?= $referral_link ?></div>
        <button class="copy-btn" onclick="copyLink()">Copy Link</button>
      </div>
    </div>

    <div class="code-box">
      <div class="ref-code"><?= $referral_code ?></div>
      <div style="font-size:.8rem;color:var(--muted);margin-top:10px">Your Private Invite Code</div>
    </div>

    <div class="share-row">
      <a href="https://wa.me/?text=<?= urlencode('Master Arabic with me on Faseeh! Use my link to get 7 days of Premium free: ' . $referral_link) ?>"
         target="_blank" class="share-btn share-whatsapp">WhatsApp</a>
      <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Learning Arabic the modern way on @FaseehAcademy! Join me: ' . $referral_link) ?>"
         target="_blank" class="share-btn share-twitter">𝕏 Twitter</a>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><span class="stat-num"><?= $stats['total_joined'] ?></span><div class="stat-label">Friends Joined</div></div>
    <div class="stat-card"><span class="stat-num"><?= $stats['bonus_days'] ?></span><div class="stat-label">Days Earned</div></div>
    <div class="stat-card"><span class="stat-num"><?= $total_joined * 100 ?></span><div class="stat-label">Bonus XP</div></div>
    <div class="stat-card"><span class="stat-num">∞</span><div class="stat-label">Potential</div></div>
  </div>

  <h2 style="margin-bottom:25px;font-weight:800">🏆 Reward Tiers</h2>
  <div class="tiers-list">
    <?php foreach ($tiers as $t):
      $achieved = $total_joined >= $t['invited'];
      $pct = min(100, round(($total_joined / $t['invited']) * 100));
    ?>
    <div class="tier-row <?= $achieved?'achieved':'' ?>" style="--tier-color:<?= $t['color'] ?>">
      <div class="tier-icon"><?= $t['icon'] ?></div>
      <div class="tier-info">
        <div class="tier-invited">Invite <?= $t['invited'] ?> friends</div>
        <div class="tier-reward"><?= $t['reward'] ?></div>
        <div class="tier-progress-bar"><div class="tier-progress-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
      <div style="font-weight:700;color:<?= $achieved?'#3ecf8e':'var(--muted)' ?>">
        <?= $achieved ? '✓ Done' : ($total_joined.'/'.$t['invited']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="friends-section">
    <h3 style="margin-bottom:15px">👥 Recent Referrals</h3>
    <?php if ($total_joined === 0): ?>
      <p style="color:var(--muted);text-align:center;padding:20px">No friends joined yet. Start sharing!</p>
    <?php else: ?>
      <p style="color:var(--accent);font-weight:600">Great work! You have referred <?= $total_joined ?> people.</p>
    <?php endif; ?>
  </div>

</div>

<div id="toast">Link copied!</div>

<script>
function copyLink() {
  const url = document.getElementById('ref-url').innerText;
  navigator.clipboard.writeText(url).then(() => {
    const t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2000);
  });
}
</script>
</body>
</html>
