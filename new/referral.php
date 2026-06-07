<?php
// referral.php — Faseeh Referral System
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');
$user_id  = (int)($_SESSION['user_id'] ?? 0);

// Generate a deterministic referral code from user_id
// In production, store this in DB and validate on signup
$referral_code = strtoupper(substr(md5('faseeh_ref_' . $user_id . '_salt2025'), 0, 8));
$referral_link = 'https://faseeh.com/register.php?ref=' . $referral_code;

// Mock referral stats — replace with real DB queries
$stats = [
  'total_invited'  => 0,
  'total_joined'   => 0,
  'total_active'   => 0,
  'bonus_days'     => 0,
  'pending_reward' => 0,
];

// Reward tiers
$tiers = [
  ['invited'=>1,  'reward'=>'7 days Premium',        'icon'=>'🎁', 'xp'=>100,  'color'=>'#3ecf8e'],
  ['invited'=>3,  'reward'=>'1 month Premium',        'icon'=>'🌟', 'xp'=>300,  'color'=>'#f5a623'],
  ['invited'=>5,  'reward'=>'3 months Premium',       'icon'=>'🚀', 'xp'=>500,  'color'=>'#7c5cbf'],
  ['invited'=>10, 'reward'=>'1 year Premium + Badge', 'icon'=>'👑', 'xp'=>1000, 'color'=>'#d4a843'],
  ['invited'=>25, 'reward'=>'Lifetime Premium',       'icon'=>'💎', 'xp'=>2500, 'color'=>'#e85d5d'],
];

// Handle referral process for NEW registrations
// In register.php, check $_GET['ref'] and credit the referrer
// Example DB logic (to add in register.php):
/*
if (!empty($_GET['ref'])) {
  $ref_code = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $_GET['ref']), 0, 8));
  $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
  $stmt->execute([$ref_code]);
  $referrer = $stmt->fetch();
  if ($referrer) {
    // Credit referrer
    $pdo->prepare("UPDATE users SET referral_count = referral_count + 1, xp = xp + 100 WHERE id = ?")
        ->execute([$referrer['id']]);
    // Set referred_by on new user
    $_SESSION['referred_by'] = $referrer['id'];
  }
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Refer Friends — Faseeh</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
<style>
:root{
  --bg:#0e0c1e;--bg-card:#161430;--bg-card2:#1c1a38;
  --border:rgba(255,255,255,.07);--accent:#f5a623;--accent2:#7c5cbf;
  --accent3:#3ecf8e;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0;--radius:16px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh}

nav{position:sticky;top:0;z-index:50;background:rgba(14,12,30,.92);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 32px;height:64px;
  display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;font-weight:800;
  font-size:1.25rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--accent),#e8862a);
  display:flex;align-items:center;justify-content:center;font-size:.95rem}
.nav-logo span{color:var(--accent)}

.page{max-width:960px;margin:0 auto;padding:40px 24px 80px}

/* HERO */
.ref-hero{
  background:linear-gradient(135deg,#1c1a38 0%,#14122a 100%);
  border:1px solid var(--border);border-radius:24px;
  padding:48px;text-align:center;margin-bottom:40px;
  position:relative;overflow:hidden;
}
.ref-hero::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 60% 80% at 50% 0%,rgba(245,166,35,.12),transparent);
  pointer-events:none;
}
.ref-emoji{font-size:3.5rem;margin-bottom:16px;display:block;position:relative}
.ref-hero h1{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(1.8rem,3vw,2.5rem);
  margin-bottom:12px;position:relative}
.ref-hero p{color:var(--muted);font-size:1rem;max-width:520px;margin:0 auto 32px;
  position:relative;line-height:1.7}

/* LINK BOX */
.link-box{
  background:var(--bg-card2);border:1px solid rgba(245,166,35,.3);
  border-radius:14px;padding:20px 24px;max-width:600px;margin:0 auto 24px;
  position:relative;
}
.link-label{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:var(--accent);margin-bottom:10px}
.link-row{display:flex;gap:10px;align-items:center}
.link-url{
  flex:1;background:rgba(255,255,255,.04);border:1px solid var(--border);
  border-radius:10px;padding:11px 16px;color:var(--text);font-size:.88rem;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.copy-btn{
  flex-shrink:0;background:linear-gradient(135deg,var(--accent),#e8862a);
  color:#1a0f00;border:none;padding:11px 20px;border-radius:10px;
  font-weight:700;font-size:.85rem;cursor:pointer;transition:.15s;white-space:nowrap;
}
.copy-btn:hover{opacity:.9}
.copy-btn.copied{background:var(--accent3);color:#0a2010}

.code-box{text-align:center;margin-bottom:24px;position:relative}
.ref-code{
  font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;
  letter-spacing:.25em;color:var(--gold);
  background:rgba(212,168,67,.08);border:1px solid rgba(212,168,67,.25);
  border-radius:12px;padding:14px 32px;display:inline-block;
}
.code-label{font-size:.75rem;color:var(--muted);margin-top:8px}

/* SHARE BUTTONS */
.share-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;position:relative}
.share-btn{
  display:flex;align-items:center;gap:8px;padding:11px 20px;border-radius:50px;
  font-size:.85rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none;border:none;
}
.share-whatsapp{background:#25d366;color:#fff}
.share-telegram{background:#2ca5e0;color:#fff}
.share-twitter{background:#1da1f2;color:#fff}
.share-email{background:rgba(255,255,255,.08);border:1px solid var(--border);color:var(--text)}
.share-btn:hover{opacity:.85;transform:translateY(-1px)}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:40px}
.stat-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:20px;text-align:center;
}
.stat-num{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--accent);display:block}
.stat-label{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:4px}

/* TIERS */
.section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
.section-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.5rem;margin-bottom:24px}

.tiers-list{display:flex;flex-direction:column;gap:12px;margin-bottom:48px}
.tier-row{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:20px 24px;display:flex;align-items:center;gap:20px;transition:.2s;
}
.tier-row:hover{border-color:rgba(255,255,255,.12);background:var(--bg-card2)}
.tier-row.achieved{border-color:var(--tier-color,var(--accent));background:rgba(62,207,142,.04)}
.tier-icon{font-size:1.8rem;width:48px;text-align:center;flex-shrink:0}
.tier-info{flex:1}
.tier-invited{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:2px}
.tier-reward{font-size:.88rem;color:var(--tier-color,var(--accent))}
.tier-xp{font-size:.78rem;color:var(--muted)}
.tier-badge{
  font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:50px;
  background:rgba(255,255,255,.06);color:var(--muted);white-space:nowrap;flex-shrink:0;
}
.tier-badge.done{background:rgba(62,207,142,.15);color:var(--accent3)}
.tier-progress-bar{height:4px;background:rgba(255,255,255,.07);border-radius:50px;overflow:hidden;margin-top:8px}
.tier-progress-fill{height:100%;border-radius:50px;background:var(--tier-color,var(--accent));transition:width .6s}

/* HOW IT WORKS */
.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:48px}
.how-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:28px 24px;text-align:center;position:relative;
}
.how-step{
  width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#e8862a);
  color:#1a0f00;font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;
}
.how-icon{font-size:2rem;margin-bottom:12px;display:block}
.how-card h3{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:8px}
.how-card p{font-size:.83rem;color:var(--muted);line-height:1.6}

/* FRIENDS TABLE */
.friends-section{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px}
.friends-section h3{font-family:'Syne',sans-serif;font-weight:700;margin-bottom:16px}
.empty-state{text-align:center;padding:32px;color:var(--muted);font-size:.9rem}
.empty-state .empty-icon{font-size:2.5rem;display:block;margin-bottom:12px}

/* TOAST */
#toast{
  position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(80px);
  background:var(--accent3);color:#0a2010;padding:12px 24px;border-radius:50px;
  font-weight:700;font-size:.88rem;z-index:9999;transition:transform .3s;pointer-events:none;
}
#toast.show{transform:translateX(-50%) translateY(0)}

@media(max-width:768px){
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .how-grid{grid-template-columns:1fr}
  .ref-hero{padding:28px 20px}
  nav{padding:0 16px}
}
</style>
</head>
<body>
<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-icon">ف</div>Fase<span>eh</span>
  </a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem">← Dashboard</a>
</nav>

<div class="page">

  <!-- HERO -->
  <div class="ref-hero">
    <span class="ref-emoji">🎁</span>
    <h1>Invite Friends, Earn Premium</h1>
    <p>
      Share Faseeh with friends and family — every person who joins earns you free Premium time,
      bonus XP, and exclusive badges. The more you share, the more you get.
    </p>

    <div class="link-box">
      <div class="link-label">Your Personal Referral Link</div>
      <div class="link-row">
        <div class="link-url" id="ref-link-display"><?= htmlspecialchars($referral_link) ?></div>
        <button class="copy-btn" id="copy-link-btn" onclick="copyLink()">Copy Link</button>
      </div>
    </div>

    <div class="code-box">
      <div class="ref-code"><?= $referral_code ?></div>
      <div class="code-label">Or share your invite code — friends enter this at signup</div>
    </div>

    <div class="share-row">
      <a href="https://wa.me/?text=<?= urlencode('I\'ve been learning Arabic with Faseeh — it\'s amazing! Join me using my link and we both get rewards: ' . $referral_link) ?>"
         target="_blank" class="share-btn share-whatsapp">📱 WhatsApp</a>
      <a href="https://t.me/share/url?url=<?= urlencode($referral_link) ?>&text=<?= urlencode('Learning Arabic with Faseeh! Join using my link:') ?>"
         target="_blank" class="share-btn share-telegram">✈️ Telegram</a>
      <a href="https://twitter.com/intent/tweet?text=<?= urlencode('I\'ve been learning Arabic with Faseeh 🌙 Join me and we both get free Premium! ' . $referral_link . ' #Arabic #Learning') ?>"
         target="_blank" class="share-btn share-twitter">𝕏 X / Twitter</a>
      <a href="mailto:?subject=<?= urlencode('Learn Arabic with me on Faseeh!') ?>&body=<?= urlencode("Hi!\n\nI've been using Faseeh to learn Arabic and it's genuinely impressive — games, AI tutors, Quranic Arabic, and more.\n\nJoin using my link and we both get free Premium:\n" . $referral_link . "\n\nOr enter my code at signup: " . $referral_code) ?>"
         class="share-btn share-email">✉️ Email</a>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <span class="stat-num"><?= $stats['total_invited'] ?></span>
      <div class="stat-label">Friends Invited</div>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= $stats['total_joined'] ?></span>
      <div class="stat-label">Friends Joined</div>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= $stats['total_active'] ?></span>
      <div class="stat-label">Still Active</div>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= $stats['bonus_days'] ?></span>
      <div class="stat-label">Premium Days Earned</div>
    </div>
  </div>

  <!-- HOW IT WORKS -->
  <div class="section-label">How It Works</div>
  <h2 class="section-title">Simple as 1, 2, 3</h2>
  <div class="how-grid">
    <div class="how-card">
      <div class="how-step">1</div>
      <span class="how-icon">🔗</span>
      <h3>Share Your Link</h3>
      <p>Copy your unique referral link or code and share it with friends, family, or your community.</p>
    </div>
    <div class="how-card">
      <div class="how-step">2</div>
      <span class="how-icon">👤</span>
      <h3>Friend Joins Faseeh</h3>
      <p>When they sign up using your link, they automatically get 7 days of Premium — no credit card needed.</p>
    </div>
    <div class="how-card">
      <div class="how-step">3</div>
      <span class="how-icon">🎁</span>
      <h3>Both Get Rewarded</h3>
      <p>You get 7 days of free Premium + 100 bonus XP. Keep referring to unlock bigger rewards.</p>
    </div>
  </div>

  <!-- REWARD TIERS -->
  <div class="section-label">Reward Tiers</div>
  <h2 class="section-title">The more you share, the more you earn</h2>
  <div class="tiers-list">
    <?php foreach ($tiers as $t):
      $achieved = $stats['total_joined'] >= $t['invited'];
      $pct = $t['invited'] > 0 ? min(100, round($stats['total_joined'] / $t['invited'] * 100)) : 0;
    ?>
    <div class="tier-row <?= $achieved?'achieved':'' ?>" style="--tier-color:<?= $t['color'] ?>">
      <div class="tier-icon"><?= $t['icon'] ?></div>
      <div class="tier-info">
        <div class="tier-invited">Invite <?= $t['invited'] ?> friend<?= $t['invited']>1?'s':'' ?></div>
        <div class="tier-reward"><?= $t['reward'] ?></div>
        <div class="tier-xp">+<?= number_format($t['xp']) ?> bonus XP</div>
        <div class="tier-progress-bar">
          <div class="tier-progress-fill" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <div class="tier-badge <?= $achieved?'done':'' ?>">
        <?= $achieved ? '✓ Achieved' : ($stats['total_joined'] . ' / ' . $t['invited']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- FRIENDS LIST -->
  <div class="friends-section">
    <h3>👥 Friends You've Invited</h3>
    <?php if ($stats['total_joined'] === 0): ?>
    <div class="empty-state">
      <span class="empty-icon">🌙</span>
      <p>You haven't referred anyone yet.<br/>Share your link above to get started!</p>
    </div>
    <?php else: ?>
      <!-- In production, loop through DB results here -->
      <p style="color:var(--muted);font-size:.88rem">Your referred friends will appear here once they join.</p>
    <?php endif; ?>
  </div>

</div>

<div id="toast">✓ Copied to clipboard!</div>

<script>
const refLink = <?= json_encode($referral_link) ?>;
const refCode = <?= json_encode($referral_code) ?>;

function copyLink() {
  navigator.clipboard.writeText(refLink).then(() => showToast('✓ Referral link copied!')).catch(() => {
    // Fallback
    const el = document.createElement('textarea');
    el.value = refLink;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    showToast('✓ Referral link copied!');
  });
  const btn = document.getElementById('copy-link-btn');
  btn.textContent = 'Copied!';
  btn.classList.add('copied');
  setTimeout(() => { btn.textContent = 'Copy Link'; btn.classList.remove('copied'); }, 2000);
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}
</script>
</body>
</html>
