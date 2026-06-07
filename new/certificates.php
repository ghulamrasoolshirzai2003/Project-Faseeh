<?php
// certificates.php — Faseeh Certificate System
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');
$user_id  = (int)($_SESSION['user_id'] ?? 0);

// Certificate definitions — tied to XP/lesson milestones
// In production, check actual DB progress
$certificates = [
  [
    'id'         => 'arabic-foundations',
    'title'      => 'Arabic Foundations',
    'title_ar'   => 'أساسيات العربية',
    'level'      => 'Beginner',
    'desc'       => 'Demonstrated mastery of Arabic alphabet, basic vocabulary, and foundational grammar.',
    'requirement'=> 'Complete all Beginner lessons + score 80%+ in Foundations Quiz',
    'xp_needed'  => 500,
    'color'      => '#3ecf8e',
    'icon'       => '🌱',
    'earned'     => false, // check against DB in production
  ],
  [
    'id'         => 'quranic-beginner',
    'title'      => 'Quranic Arabic I',
    'title_ar'   => 'العربية القرآنية - المستوى الأول',
    'level'      => 'Beginner',
    'desc'       => 'Learned the 100 most frequent words in the Holy Quran with their meanings and roots.',
    'requirement'=> 'Complete Quranic Track — Al-Fatiha, Al-Ikhlas, Al-Falaq, An-Nas',
    'xp_needed'  => 800,
    'color'      => '#d4a843',
    'icon'       => '📖',
    'earned'     => false,
  ],
  [
    'id'         => 'intermediate-arabic',
    'title'      => 'Intermediate Arabic',
    'title_ar'   => 'العربية المتوسطة',
    'level'      => 'Intermediate',
    'desc'       => 'Achieved conversational fluency with solid grammar, verb conjugation, and reading comprehension.',
    'requirement'=> 'Complete all Intermediate modules + 50 AI conversation turns',
    'xp_needed'  => 2000,
    'color'      => '#f5a623',
    'icon'       => '🚀',
    'earned'     => false,
  ],
  [
    'id'         => 'advanced-arabic',
    'title'      => 'Advanced Arabic',
    'title_ar'   => 'العربية المتقدمة',
    'level'      => 'Advanced',
    'desc'       => 'Mastery of complex grammar, academic writing, and advanced reading in Modern Standard Arabic.',
    'requirement'=> 'Complete Advanced track + AI Essay Grader score 85%+',
    'xp_needed'  => 5000,
    'color'      => '#7c5cbf',
    'icon'       => '🏛️',
    'earned'     => false,
  ],
  [
    'id'         => 'faseeh-master',
    'title'      => 'Faseeh Master',
    'title_ar'   => 'ماجستير فصيح',
    'level'      => 'Expert',
    'desc'       => 'The highest honour on Faseeh — complete mastery of Modern Standard Arabic and Quranic Arabic.',
    'requirement'=> 'Earn all previous certificates + 10,000 XP',
    'xp_needed'  => 10000,
    'color'      => '#e85d5d',
    'icon'       => '👑',
    'earned'     => false,
  ],
];

// Current user XP — replace with real DB query
$user_xp = (int)($_SESSION['xp'] ?? 0);
$issue_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Certificates — Faseeh</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=Amiri:ital,wght@0,400;0,700;1,400&family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet"/>
<style>
:root{--bg:#0e0c1e;--bg-card:#161430;--bg-card2:#1c1a38;--border:rgba(255,255,255,.07);
  --accent:#f5a623;--accent2:#7c5cbf;--accent3:#3ecf8e;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0;--radius:16px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh}
nav{position:sticky;top:0;z-index:50;background:rgba(14,12,30,.92);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;font-weight:800;font-size:1.25rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#e8862a);display:flex;align-items:center;justify-content:center;font-size:.95rem}
.nav-logo span{color:var(--accent)}
.page{max-width:1100px;margin:0 auto;padding:40px 24px 80px}
.page-header{text-align:center;margin-bottom:48px}
.section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(1.8rem,3vw,2.5rem);margin-bottom:12px}
.page-sub{color:var(--muted);font-size:1rem;max-width:500px;margin:0 auto}

/* XP BAR */
.xp-banner{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px 28px;margin-bottom:40px;display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.xp-num{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--accent);min-width:80px}
.xp-label{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:2px}
.xp-bar-wrap{flex:1;min-width:200px}
.xp-bar{height:10px;background:rgba(255,255,255,.07);border-radius:50px;overflow:hidden}
.xp-bar-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--accent3),var(--accent));transition:width 1.2s ease}
.xp-next{font-size:.8rem;color:var(--muted);margin-top:6px}

/* CERT GRID */
.cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-bottom:48px}
.cert-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:20px;
  padding:28px;position:relative;overflow:hidden;transition:.25s;
}
.cert-card.locked{opacity:.55}
.cert-card.earned{border-color:var(--cert-color, var(--accent));box-shadow:0 0 0 1px rgba(212,168,67,.2),0 12px 40px rgba(0,0,0,.4)}
.cert-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--cert-color,var(--accent));opacity:.7}
.cert-icon{font-size:2.5rem;margin-bottom:14px;display:block}
.cert-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;margin-bottom:4px}
.cert-title-ar{font-family:'Amiri',serif;font-size:1.1rem;color:var(--gold);margin-bottom:10px}
.cert-level{display:inline-block;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:50px;margin-bottom:12px;
  background:rgba(255,255,255,.07);color:var(--muted)}
.cert-desc{font-size:.85rem;color:var(--muted);line-height:1.6;margin-bottom:14px}
.cert-req{font-size:.78rem;color:var(--muted);background:rgba(255,255,255,.03);border-radius:8px;padding:8px 12px;margin-bottom:16px;border-left:2px solid var(--cert-color,var(--accent))}
.cert-xp-need{font-size:.8rem;color:var(--muted);margin-bottom:16px}
.cert-xp-need strong{color:var(--accent)}
.cert-btn{
  display:block;width:100%;padding:11px;border-radius:12px;text-align:center;
  font-weight:700;font-size:.88rem;cursor:pointer;transition:.2s;border:none;
}
.cert-btn.locked-btn{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted);cursor:not-allowed}
.cert-btn.earn-btn{background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3);color:var(--accent)}
.cert-btn.earn-btn:hover{background:rgba(245,166,35,.2)}
.cert-btn.download-btn{background:linear-gradient(135deg,var(--cert-color,var(--accent)),rgba(245,166,35,.6));color:#1a0f00}
.cert-btn.download-btn:hover{opacity:.9}
.earned-badge{position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;
  background:var(--cert-color,var(--accent));display:flex;align-items:center;justify-content:center;font-size:.9rem}

/* CERTIFICATE PREVIEW (print/canvas) */
.cert-preview-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1000;
  display:none;align-items:center;justify-content:center;padding:24px;
}
.cert-preview-overlay.show{display:flex}
.cert-preview-wrap{position:relative;max-width:900px;width:100%}
.cert-close{
  position:absolute;top:-16px;right:-16px;width:36px;height:36px;border-radius:50%;
  background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);
  color:#fff;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;
  z-index:10;
}
canvas#cert-canvas{width:100%;border-radius:12px;box-shadow:0 32px 80px rgba(0,0,0,.6)}
.cert-download-row{display:flex;gap:12px;margin-top:16px;justify-content:center;flex-wrap:wrap}
.cert-dl-btn{
  padding:11px 24px;border-radius:50px;font-weight:700;font-size:.88rem;cursor:pointer;transition:.2s;
}
.cert-dl-btn.primary{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;border:none}
.cert-dl-btn.secondary{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:var(--text)}
</style>
</head>
<body>
<nav>
  <a href="/" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem">← Dashboard</a>
</nav>

<div class="page">
  <div class="page-header">
    <div class="section-label">Your Achievements</div>
    <h1 class="page-title">🎓 Certificates</h1>
    <p class="page-sub">Earn verifiable certificates as you master Arabic. Share them on LinkedIn and beyond.</p>
  </div>

  <div class="xp-banner">
    <div>
      <div class="xp-num"><?= number_format($user_xp) ?></div>
      <span class="xp-label">Total XP</span>
    </div>
    <div class="xp-bar-wrap">
      <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--muted);margin-bottom:8px">
        <span>Progress to next certificate</span>
        <span><?= number_format($user_xp) ?> / 500 XP</span>
      </div>
      <div class="xp-bar">
        <div class="xp-bar-fill" style="width:<?= min(100, round($user_xp/500*100)) ?>%"></div>
      </div>
      <div class="xp-next">Earn <strong style="color:var(--text)"><?= max(0, 500-$user_xp) ?> more XP</strong> to unlock Arabic Foundations certificate</div>
    </div>
  </div>

  <div class="cert-grid">
    <?php foreach ($certificates as $cert):
      $earned = $cert['earned'];
      $canEarn = $user_xp >= $cert['xp_needed'];
    ?>
    <div class="cert-card <?= $earned?'earned':'locked' ?>" style="--cert-color:<?= $cert['color'] ?>">
      <?php if ($earned): ?><div class="earned-badge">✓</div><?php endif; ?>
      <span class="cert-icon"><?= $cert['icon'] ?></span>
      <div class="cert-title"><?= $cert['title'] ?></div>
      <div class="cert-title-ar"><?= $cert['title_ar'] ?></div>
      <span class="cert-level"><?= $cert['level'] ?></span>
      <div class="cert-desc"><?= $cert['desc'] ?></div>
      <div class="cert-req">📋 <?= $cert['requirement'] ?></div>
      <div class="cert-xp-need">Requires <strong><?= number_format($cert['xp_needed']) ?> XP</strong>
        <?= $canEarn ? ' <span style="color:var(--accent3)">✓ You qualify!</span>' : '' ?>
      </div>
      <?php if ($earned): ?>
        <button class="cert-btn download-btn" onclick="previewCert('<?= $cert['id'] ?>','<?= addslashes($cert['title']) ?>','<?= addslashes($cert['title_ar']) ?>','<?= $cert['level'] ?>','<?= $cert['color'] ?>')">
          🎓 View & Download Certificate
        </button>
      <?php elseif ($canEarn): ?>
        <a href="academy.php" class="cert-btn earn-btn">▶ Start earning this certificate</a>
      <?php else: ?>
        <button class="cert-btn locked-btn" disabled>🔒 Locked — need <?= number_format($cert['xp_needed']) ?> XP</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CERTIFICATE PREVIEW OVERLAY -->
<div class="cert-preview-overlay" id="cert-overlay">
  <div class="cert-preview-wrap">
    <button class="cert-close" onclick="closePreview()">✕</button>
    <canvas id="cert-canvas" width="1200" height="848"></canvas>
    <div class="cert-download-row">
      <button class="cert-dl-btn primary" onclick="downloadCert('png')">⬇ Download PNG</button>
      <button class="cert-dl-btn secondary" onclick="shareCert()">🔗 Copy Share Link</button>
      <button class="cert-dl-btn secondary" onclick="shareLinkedIn()">💼 Share on LinkedIn</button>
    </div>
  </div>
</div>

<script>
const username  = <?= json_encode($username) ?>;
const issueDate = <?= json_encode($issue_date) ?>;
let currentCertTitle = '';

function previewCert(id, title, titleAr, level, color) {
  currentCertTitle = title;
  const overlay = document.getElementById('cert-overlay');
  overlay.classList.add('show');
  drawCertificate(title, titleAr, level, color);
}

function closePreview() {
  document.getElementById('cert-overlay').classList.remove('show');
}

function drawCertificate(title, titleAr, level, accentColor) {
  const canvas = document.getElementById('cert-canvas');
  const ctx    = canvas.getContext('2d');
  const W = 1200, H = 848;
  ctx.clearRect(0, 0, W, H);

  // Background
  const bg = ctx.createLinearGradient(0, 0, W, H);
  bg.addColorStop(0,   '#0e0c1e');
  bg.addColorStop(0.5, '#151230');
  bg.addColorStop(1,   '#0e0c1e');
  ctx.fillStyle = bg;
  ctx.fillRect(0, 0, W, H);

  // Decorative border
  ctx.strokeStyle = accentColor;
  ctx.lineWidth   = 3;
  ctx.globalAlpha = 0.5;
  ctx.strokeRect(30, 30, W-60, H-60);
  ctx.globalAlpha = 0.2;
  ctx.strokeRect(40, 40, W-80, H-80);
  ctx.globalAlpha = 1;

  // Corner ornaments
  const corners = [[50,50],[W-50,50],[50,H-50],[W-50,H-50]];
  corners.forEach(([cx,cy]) => {
    ctx.fillStyle = accentColor;
    ctx.globalAlpha = 0.6;
    ctx.beginPath();
    ctx.arc(cx, cy, 6, 0, Math.PI*2);
    ctx.fill();
    ctx.globalAlpha = 1;
  });

  // Faseeh header
  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 28px "Syne", sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('FASEEH — فصيح', W/2, 110);

  // Divider line
  ctx.strokeStyle = accentColor;
  ctx.globalAlpha = 0.3;
  ctx.lineWidth   = 1;
  ctx.beginPath();
  ctx.moveTo(150, 130); ctx.lineTo(W-150, 130);
  ctx.stroke();
  ctx.globalAlpha = 1;

  // Certificate of title
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '22px "DM Sans", sans-serif';
  ctx.fillText('CERTIFICATE OF COMPLETION', W/2, 185);

  // Main title
  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 56px "Syne", sans-serif';
  ctx.fillText(title, W/2, 280);

  // Arabic title
  ctx.fillStyle = '#d4a843';
  ctx.font      = 'bold 38px "Amiri", serif';
  ctx.fillText(titleAr, W/2, 340);

  // Awarded to
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '20px "DM Sans", sans-serif';
  ctx.fillText('This certificate is proudly awarded to', W/2, 420);

  // Name
  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 52px "Cinzel", serif';
  ctx.fillText(username, W/2, 500);

  // Description
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '20px "DM Sans", sans-serif';
  ctx.fillText('in recognition of demonstrated mastery of', W/2, 565);

  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 26px "DM Sans", sans-serif';
  ctx.fillText(level + ' Arabic — Faseeh Academy', W/2, 605);

  // Second divider
  ctx.strokeStyle = accentColor;
  ctx.globalAlpha = 0.3;
  ctx.lineWidth   = 1;
  ctx.beginPath();
  ctx.moveTo(150, 640); ctx.lineTo(W-150, 640);
  ctx.stroke();
  ctx.globalAlpha = 1;

  // Issue date
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '18px "DM Sans", sans-serif';
  ctx.fillText('Issued on ' + issueDate, W/2, 690);

  // Verification
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '15px "DM Sans", sans-serif';
  ctx.fillText('Verify at faseeh.com/verify — Certificate ID: FASEEH-' + Date.now().toString(36).toUpperCase(), W/2, 730);

  // Bottom watermark
  ctx.fillStyle = accentColor;
  ctx.globalAlpha = 0.08;
  ctx.font = 'bold 180px "Amiri", serif';
  ctx.fillText('فصيح', W/2, 560);
  ctx.globalAlpha = 1;
}

function downloadCert(format) {
  const canvas = document.getElementById('cert-canvas');
  const link   = document.createElement('a');
  link.download = 'Faseeh-Certificate-' + currentCertTitle.replace(/\s+/g,'-') + '.png';
  link.href     = canvas.toDataURL('image/png', 1.0);
  link.click();
}

function shareCert() {
  const url = 'https://faseeh.com/certificate/' + Date.now().toString(36);
  navigator.clipboard.writeText(url).then(() => {
    alert('Certificate link copied to clipboard!');
  });
}

function shareLinkedIn() {
  const url = encodeURIComponent('https://faseeh.com');
  const text = encodeURIComponent('I just earned the ' + currentCertTitle + ' certificate on Faseeh — the Arabic learning platform! 🎓 #Arabic #Learning #Faseeh');
  window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + url + '&summary=' + text, '_blank');
}

document.getElementById('cert-overlay').addEventListener('click', function(e) {
  if (e.target === this) closePreview();
});
</script>
</body>
</html>
