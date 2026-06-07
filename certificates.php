<?php
// certificates.php — Faseeh Certificate System (Live Edition)
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');

// FETCH LIVE STATS
$stmt = $pdo->prepare("SELECT xp, academic_correct_count FROM progress WHERE user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
$user_xp = $stats['xp'] ?? 0;
$academic_correct = $stats['academic_correct_count'] ?? 0;

$certificates = [
  [
    'id'         => 'arabic-foundations',
    'title'      => 'Arabic Foundations',
    'title_ar'   => 'أساسيات العربية',
    'level'      => 'Beginner',
    'desc'       => 'Demonstrated mastery of Arabic alphabet, basic vocabulary, and foundational grammar.',
    'requirement'=> 'Complete Beginner modules + 500 XP',
    'xp_needed'  => 500,
    'color'      => '#3ecf8e',
    'icon'       => '🌱',
    'earned'     => $user_xp >= 500,
  ],
  [
    'id'         => 'quranic-beginner',
    'title'      => 'Quranic Arabic I',
    'title_ar'   => 'العربية القرآنية - المستوى الأول',
    'level'      => 'Beginner',
    'desc'       => 'Learned the most frequent words in the Holy Quran with their meanings and roots.',
    'requirement'=> 'Complete Quranic Track + 800 XP',
    'xp_needed'  => 800,
    'color'      => '#FFD700',
    'icon'       => '📖',
    'earned'     => $user_xp >= 800,
  ],
  [
    'id'         => 'intermediate-arabic',
    'title'      => 'Intermediate Arabic',
    'title_ar'   => 'العربية المتوسطة',
    'level'      => 'Intermediate',
    'desc'       => 'Achieved conversational fluency with solid grammar and reading comprehension.',
    'requirement'=> 'Complete Intermediate modules + 2,000 XP',
    'xp_needed'  => 2000,
    'color'      => '#f2994a',
    'icon'       => '🚀',
    'earned'     => $user_xp >= 2000,
  ],
  [
    'id'         => 'advanced-arabic',
    'title'      => 'Advanced Arabic',
    'title_ar'   => 'العربية المتقدمة',
    'level'      => 'Advanced',
    'desc'       => 'Mastery of complex grammar and advanced reading in Modern Standard Arabic.',
    'requirement'=> 'Complete Advanced track + 5,000 XP',
    'xp_needed'  => 5000,
    'color'      => '#7c5cbf',
    'icon'       => '🏛️',
    'earned'     => $user_xp >= 5000,
  ],
  [
    'id'         => 'faseeh-master',
    'title'      => 'Faseeh Master',
    'title_ar'   => 'ماجستير فصيح',
    'level'      => 'Expert',
    'desc'       => 'The highest honour on Faseeh — complete mastery of Arabic language arts.',
    'requirement'=> 'Earn 10,000 XP',
    'xp_needed'  => 10000,
    'color'      => '#e74c3c',
    'icon'       => '👑',
    'earned'     => $user_xp >= 10000,
  ],
];

$issue_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Certificates — Faseeh Academy</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&family=Cinzel:wght@600;700&display=swap" rel="stylesheet"/>
<style>
:root {
    --bg: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
    --bg-card: rgba(255,255,255,0.06); --bg-card2: rgba(255,255,255,0.1);
    --border: rgba(255,255,255,0.1); --accent: #f2994a; --accent2: #f2c94c;
    --gold: #FFD700; --text: #f0eeff; --muted: #8b87b0; --radius: 20px;
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

.page{max-width:1100px;margin:0 auto;padding:40px 24px 80px}
.page-header{text-align:center;margin-bottom:48px}
.section-label{font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
h1{font-weight:800;font-size:2.8rem;margin-bottom:12px}

.xp-banner{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:30px;margin-bottom:45px;display:flex;align-items:center;gap:30px;backdrop-filter:blur(10px)}
.xp-num{font-size:2.5rem;font-weight:800;color:var(--accent);line-height:1}
.xp-label{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;display:block;margin-top:5px}
.xp-bar-wrap{flex:1}
.xp-bar{height:12px;background:rgba(255,255,255,0.08);border-radius:50px;overflow:hidden}
.xp-bar-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--accent),var(--accent2));transition:width 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)}

.cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:25px;margin-bottom:48px}
.cert-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:24px;
  padding:30px;position:relative;overflow:hidden;transition:.3s;backdrop-filter:blur(10px);
}
.cert-card.earned{border-color:var(--cert-color);box-shadow:0 15px 40px rgba(0,0,0,0.3)}
.cert-card.locked{opacity:0.6;filter:grayscale(0.5)}
.cert-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--cert-color);opacity:0.8}
.cert-icon{font-size:3rem;margin-bottom:15px;display:block}
.cert-title{font-weight:800;font-size:1.25rem;margin-bottom:5px}
.cert-title-ar{font-family:'Amiri',serif;font-size:1.3rem;color:var(--gold);margin-bottom:15px}
.cert-level{display:inline-block;font-size:.7rem;font-weight:800;padding:4px 12px;border-radius:50px;background:rgba(255,255,255,0.08);color:var(--muted);margin-bottom:15px;text-transform:uppercase}
.cert-desc{font-size:.85rem;color:var(--muted);line-height:1.6;margin-bottom:20px}
.cert-req{font-size:.8rem;color:var(--text);background:rgba(255,255,255,0.05);padding:10px 15px;border-radius:12px;margin-bottom:20px;border-left:3px solid var(--cert-color)}

.cert-btn{
  display:block;width:100%;padding:14px;border-radius:15px;text-align:center;
  font-weight:700;font-size:.9rem;cursor:pointer;transition:.3s;border:none;text-decoration:none;
}
.cert-btn.locked-btn{background:rgba(255,255,255,0.05);color:var(--muted);cursor:not-allowed}
.cert-btn.earn-btn{background:rgba(242,153,74,0.1);border:1px solid var(--accent);color:var(--accent)}
.cert-btn.download-btn{background:linear-gradient(135deg,var(--cert-color),var(--accent2));color:#000}

.cert-preview-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:1000;
  display:none;align-items:center;justify-content:center;padding:24px;backdrop-filter:blur(10px);
}
.cert-preview-overlay.show{display:flex}
.cert-preview-wrap{position:relative;max-width:950px;width:100%}
.cert-close{
  position:absolute;top:-20px;right:-20px;width:44px;height:44px;border-radius:50%;
  background:#fff;color:#000;font-size:1.2rem;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:10;border:none;font-weight:bold;
}
canvas#cert-canvas{width:100%;border-radius:20px;box-shadow:0 30px 90px rgba(0,0,0,0.8);border:1px solid rgba(255,255,255,0.1)}
.cert-download-row{display:flex;gap:15px;margin-top:25px;justify-content:center;flex-wrap:wrap}
.cert-dl-btn{
  padding:14px 30px;border-radius:50px;font-weight:700;font-size:.9rem;cursor:pointer;transition:.3s;border:none;
}
.cert-dl-btn.primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#000}
.cert-dl-btn.secondary{background:rgba(255,255,255,0.1);color:#fff}

@media(max-width:768px){ .xp-banner{flex-direction:column;text-align:center;} }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:600">← Hub Dashboard</a>
</nav>

<div class="page">
  <div class="page-header">
    <div class="section-label">Institutional Recognition</div>
    <h1 class="page-title">🎓 Mastery Certificates</h1>
    <p class="page-sub">Earn verifiable digital credentials as you advance through the Academy. Valid worldwide.</p>
  </div>

  <div class="xp-banner">
    <div><div class="xp-num"><?= number_format($user_xp) ?></div><span class="xp-label">Total Academy XP</span></div>
    <div class="xp-bar-wrap">
      <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--muted);margin-bottom:10px">
        <span>Milestone Progress</span>
        <span><?= number_format($user_xp) ?> / 500 XP</span>
      </div>
      <div class="xp-bar"><div class="xp-bar-fill" style="width:<?= min(100, round($user_xp/500*100)) ?>%"></div></div>
    </div>
  </div>

  <div class="cert-grid">
    <?php foreach ($certificates as $cert):
      $earned = $cert['earned'];
      $canEarn = $user_xp >= $cert['xp_needed'];
    ?>
    <div class="cert-card <?= $earned?'earned':'locked' ?>" style="--cert-color:<?= $cert['color'] ?>">
      <span class="cert-icon"><?= $cert['icon'] ?></span>
      <div class="cert-title"><?= $cert['title'] ?></div>
      <div class="cert-title-ar"><?= $cert['title_ar'] ?></div>
      <span class="cert-level"><?= $cert['level'] ?></span>
      <div class="cert-desc"><?= $cert['desc'] ?></div>
      <div class="cert-req">📋 <?= $cert['requirement'] ?></div>
      
      <?php if ($earned): ?>
        <button class="cert-btn download-btn" onclick="previewCert('<?= $cert['id'] ?>','<?= addslashes($cert['title']) ?>','<?= addslashes($cert['title_ar']) ?>','<?= $cert['level'] ?>','<?= $cert['color'] ?>')">
          👑 View & Download Honors
        </button>
      <?php elseif ($canEarn): ?>
        <a href="academy.php" class="cert-btn earn-btn">▶ Claim Achievement</a>
      <?php else: ?>
        <button class="cert-btn locked-btn" disabled>🔒 Need <?= number_format($cert['xp_needed']) ?> XP</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="cert-preview-overlay" id="cert-overlay">
  <div class="cert-preview-wrap">
    <button class="cert-close" onclick="closePreview()">✕</button>
    <canvas id="cert-canvas" width="1200" height="848"></canvas>
    <div class="cert-download-row">
      <button class="cert-dl-btn primary" onclick="downloadCert()">⬇ Save as PNG</button>
      <button class="cert-dl-btn secondary" onclick="shareLinkedIn()">💼 Share on LinkedIn</button>
    </div>
  </div>
</div>

<script>
const username = <?= json_encode($username) ?>;
const issueDate = <?= json_encode($issue_date) ?>;
let currentCertTitle = '';

function previewCert(id, title, titleAr, level, color) {
  currentCertTitle = title;
  document.getElementById('cert-overlay').classList.add('show');
  drawCertificate(title, titleAr, level, color);
}

function closePreview() { document.getElementById('cert-overlay').classList.remove('show'); }

function drawCertificate(title, titleAr, level, accentColor) {
  const canvas = document.getElementById('cert-canvas');
  const ctx = canvas.getContext('2d');
  const W = 1200, H = 848;
  ctx.clearRect(0,0,W,H);

  // Elegant Background
  ctx.fillStyle = '#0f0c29';
  ctx.fillRect(0,0,W,H);
  const grad = ctx.createRadialGradient(W/2, H/2, 0, W/2, H/2, W*0.7);
  grad.addColorStop(0, '#151230'); grad.addColorStop(1, '#0f0c29');
  ctx.fillStyle = grad;
  ctx.fillRect(0,0,W,H);

  // Borders
  ctx.strokeStyle = accentColor; ctx.lineWidth = 15; ctx.globalAlpha = 0.4;
  ctx.strokeRect(30,30,W-60,H-60);
  ctx.lineWidth = 2; ctx.globalAlpha = 0.8;
  ctx.strokeRect(50,50,W-100,H-100);

  // Watermark
  ctx.fillStyle = accentColor; ctx.globalAlpha = 0.05; ctx.font = 'bold 300px "Amiri", serif'; ctx.textAlign = 'center';
  ctx.fillText('فصيح', W/2, H/2 + 100);
  ctx.globalAlpha = 1;

  // Header
  ctx.fillStyle = accentColor; ctx.font = 'bold 35px "Poppins", sans-serif'; ctx.fillText('FASEEH ACADEMY', W/2, 120);
  ctx.fillStyle = '#8b87b0'; ctx.font = '22px "Poppins", sans-serif'; ctx.fillText('OFFICIAL CERTIFICATE OF MASTERY', W/2, 160);

  // Main Content
  ctx.fillStyle = '#fff'; ctx.font = 'bold 70px "Poppins", sans-serif'; ctx.fillText(title, W/2, 280);
  ctx.fillStyle = '#FFD700'; ctx.font = 'bold 45px "Amiri", serif'; ctx.fillText(titleAr, W/2, 350);

  ctx.fillStyle = '#8b87b0'; ctx.font = '24px "Poppins", sans-serif'; ctx.fillText('This certificate is proudly awarded to', W/2, 430);
  ctx.fillStyle = '#fff'; ctx.font = 'bold 65px "Cinzel", serif'; ctx.fillText(username, W/2, 520);
  
  ctx.fillStyle = '#8b87b0'; ctx.font = '22px "Poppins", sans-serif'; ctx.fillText('for demonstrating excellence in the study of', W/2, 590);
  ctx.fillStyle = accentColor; ctx.font = 'bold 30px "Poppins", sans-serif'; ctx.fillText(level + ' Arabic Studies', W/2, 630);

  // Footer
  ctx.fillStyle = '#8b87b0'; ctx.font = '18px "Poppins", sans-serif'; ctx.fillText('Issued on ' + issueDate, W/2, 730);
  ctx.font = 'italic 16px "Poppins", sans-serif'; ctx.fillText('Verify at faseeh.com/verify — ID: F-' + Date.now().toString(36).toUpperCase(), W/2, 770);
}

function downloadCert() {
  const canvas = document.getElementById('cert-canvas');
  const a = document.createElement('a');
  a.download = 'Faseeh-Certificate-' + currentCertTitle.replace(/\s+/g,'-') + '.png';
  a.href = canvas.toDataURL('image/png', 1.0);
  a.click();
}

function shareLinkedIn() {
  const text = encodeURIComponent('I just earned my Arabic Mastery Certificate on Faseeh Academy! 🎓 Check it out: faseeh.com #Arabic #Education');
  window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent('https://faseeh.com') + '&summary=' + text, '_blank');
}
</script>
</body>
</html>
