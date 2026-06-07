<?php
// share.php — Faseeh Social Sharing Cards Generator
// Generates shareable achievement images (streak, XP, word learned, leaderboard)
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$username  = htmlspecialchars($_SESSION['username'] ?? 'Learner');
$user_xp   = (int)($_SESSION['xp']     ?? 0);
$streak    = (int)($_SESSION['streak'] ?? 0);
$rank_name = $_SESSION['rank'] ?? 'مبتدئ';

// Share card types
$card_types = [
  ['id'=>'streak',      'label'=>'Streak Card',        'icon'=>'🔥', 'desc'=>'Show off your learning streak'],
  ['id'=>'xp',          'label'=>'XP Milestone',        'icon'=>'✨', 'desc'=>'Celebrate your XP achievement'],
  ['id'=>'word',        'label'=>'Word of the Day',      'icon'=>'📖', 'desc'=>'Share today\'s Arabic word'],
  ['id'=>'rank',        'label'=>'Rank Achievement',     'icon'=>'🏆', 'desc'=>'Announce your new Arabic rank'],
  ['id'=>'certificate', 'label'=>'Certificate Earned',   'icon'=>'🎓', 'desc'=>'Share your certificate'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Share Your Progress — Faseeh</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
<style>
:root{--bg:#0e0c1e;--bg-card:#161430;--bg-card2:#1c1a38;--border:rgba(255,255,255,.07);
  --accent:#f5a623;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0;--radius:16px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh}
nav{position:sticky;top:0;background:rgba(14,12,30,.92);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;font-weight:800;font-size:1.25rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#e8862a);display:flex;align-items:center;justify-content:center}
.nav-logo span{color:var(--accent)}
.page{max-width:960px;margin:0 auto;padding:40px 24px 80px}
.page-header{text-align:center;margin-bottom:40px}
.section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
h1{font-family:'Syne',sans-serif;font-weight:800;font-size:2rem;margin-bottom:10px}
.page-sub{color:var(--muted);font-size:.95rem}

/* TABS */
.type-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:32px;justify-content:center}
.type-tab{background:var(--bg-card);border:1px solid var(--border);border-radius:50px;
  padding:8px 18px;cursor:pointer;font-size:.85rem;color:var(--muted);transition:.2s;display:flex;align-items:center;gap:6px}
.type-tab:hover{border-color:rgba(255,255,255,.15);color:var(--text)}
.type-tab.active{border-color:var(--accent);background:rgba(245,166,35,.08);color:var(--accent);font-weight:600}

/* CANVAS PREVIEW */
.canvas-section{display:grid;grid-template-columns:1fr 300px;gap:28px;align-items:start}
.canvas-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:24px}
canvas#share-canvas{width:100%;border-radius:12px;display:block}
.customise-panel{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:24px;display:flex;flex-direction:column;gap:20px}
.customise-panel h3{font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;margin-bottom:0}
.field-group{display:flex;flex-direction:column;gap:6px}
.field-label{font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.field-input{background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;
  padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;width:100%}
.field-input:focus{outline:none;border-color:rgba(245,166,35,.4)}
.color-row{display:flex;gap:8px;flex-wrap:wrap}
.color-btn{width:28px;height:28px;border-radius:50%;border:2px solid transparent;cursor:pointer;transition:.15s}
.color-btn.selected{border-color:#fff;transform:scale(1.15)}
.share-btn{width:100%;padding:12px;border-radius:12px;border:none;cursor:pointer;font-weight:700;font-size:.9rem;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px}
.share-btn.download{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00}
.share-btn.download:hover{opacity:.9}
.share-btn.copy{background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text)}
.share-btn.copy:hover{border-color:rgba(255,255,255,.15)}
.share-btn.twitter{background:#1da1f2;color:#fff}
.share-btn.instagram{background:linear-gradient(135deg,#405de6,#833ab4,#c13584,#e1306c,#fd1d1d);color:#fff}
.platform-row{display:flex;flex-direction:column;gap:8px}

@media(max-width:768px){
  .canvas-section{grid-template-columns:1fr}
}
</style>
</head>
<body>
<nav>
  <a href="/" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem">← Dashboard</a>
</nav>
<div class="page">
  <div class="page-header">
    <div class="section-label">Share Your Journey</div>
    <h1>📤 Sharing Cards</h1>
    <p class="page-sub">Create beautiful cards to share your Arabic learning milestones on any platform.</p>
  </div>

  <div class="type-tabs">
    <?php foreach ($card_types as $ct): ?>
    <div class="type-tab <?= $ct['id']==='streak'?'active':'' ?>" onclick="selectType('<?= $ct['id'] ?>',this)">
      <?= $ct['icon'] ?> <?= $ct['label'] ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="canvas-section">
    <div class="canvas-wrap">
      <canvas id="share-canvas" width="1080" height="1080"></canvas>
    </div>
    <div class="customise-panel">
      <h3>🎨 Customise</h3>

      <div class="field-group">
        <div class="field-label">Your Name</div>
        <input type="text" class="field-input" id="custom-name" value="<?= $username ?>" oninput="redraw()"/>
      </div>
      <div class="field-group">
        <div class="field-label">Custom Message</div>
        <input type="text" class="field-input" id="custom-msg" value="" placeholder="Add a personal message..." oninput="redraw()"/>
      </div>
      <div class="field-group">
        <div class="field-label">Accent Colour</div>
        <div class="color-row">
          <?php
          $colors = ['#f5a623','#d4a843','#3ecf8e','#7c5cbf','#e85d5d','#5ca8e8','#e86db5'];
          foreach ($colors as $c): ?>
          <div class="color-btn <?= $c==='#f5a623'?'selected':'' ?>" style="background:<?= $c ?>"
               data-color="<?= $c ?>" onclick="selectColor(this)"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="platform-row">
        <button class="share-btn download" onclick="downloadCard()">⬇ Download PNG</button>
        <button class="share-btn copy" onclick="copyCard()">📋 Copy to Clipboard</button>
        <button class="share-btn twitter" onclick="shareTwitter()">𝕏 Share on X/Twitter</button>
        <button class="share-btn instagram" onclick="alert('Save the image and upload to Instagram!')">◎ Save for Instagram</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentType = 'streak';
let accentColor = '#f5a623';
const userXP    = <?= $user_xp ?>;
const streak    = <?= $streak ?>;
const rankName  = <?= json_encode($rank_name) ?>;

const wordOfDay = { arabic:'جَمَرك', roman:'Jamarik', meaning:'Customs / Border Control' };

function selectType(type, el) {
  currentType = type;
  document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  redraw();
}

function selectColor(el) {
  accentColor = el.dataset.color;
  document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('selected'));
  el.classList.add('selected');
  redraw();
}

function redraw() {
  const name = document.getElementById('custom-name').value || '<?= $username ?>';
  const msg  = document.getElementById('custom-msg').value;

  const canvas = document.getElementById('share-canvas');
  const ctx    = canvas.getContext('2d');
  const S = 1080;
  ctx.clearRect(0, 0, S, S);

  // Background
  const grad = ctx.createLinearGradient(0, 0, S, S);
  grad.addColorStop(0, '#0e0c1e');
  grad.addColorStop(1, '#161430');
  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, S, S);

  // Radial glow
  const glow = ctx.createRadialGradient(S/2, S/2, 0, S/2, S/2, S*0.6);
  glow.addColorStop(0, accentColor + '22');
  glow.addColorStop(1, 'transparent');
  ctx.fillStyle = glow;
  ctx.fillRect(0, 0, S, S);

  // Border
  ctx.strokeStyle = accentColor;
  ctx.lineWidth   = 4;
  ctx.globalAlpha = 0.4;
  ctx.strokeRect(24, 24, S-48, S-48);
  ctx.globalAlpha = 1;

  // Logo
  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 32px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('FASEEH', S/2, 90);
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '20px sans-serif';
  ctx.fillText('فصيح — Arabic Learning', S/2, 122);

  // Card-type specific content
  if (currentType === 'streak') drawStreak(ctx, S, name, msg);
  if (currentType === 'xp')     drawXP(ctx, S, name, msg);
  if (currentType === 'word')   drawWord(ctx, S, name, msg);
  if (currentType === 'rank')   drawRank(ctx, S, name, msg);

  // Bottom tag
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '20px sans-serif';
  ctx.fillText('faseeh.com — Start learning Arabic for free', S/2, S-50);
}

function drawStreak(ctx, S, name, msg) {
  ctx.fillStyle = '#f59e0b';
  ctx.font      = '120px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('🔥', S/2, 420);

  ctx.fillStyle = accentColor;
  ctx.font      = `bold 160px "Syne", sans-serif`;
  ctx.fillText(streak + ' Days', S/2, 610);

  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 42px sans-serif';
  ctx.fillText(name + ' is on a streak!', S/2, 690);

  ctx.fillStyle = '#8b87b0';
  ctx.font      = '30px sans-serif';
  ctx.fillText('Consistent Arabic practice pays off 🌟', S/2, 750);

  if (msg) { ctx.fillStyle = '#f0eeff'; ctx.font = '28px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 820); }
}

function drawXP(ctx, S, name, msg) {
  ctx.fillStyle = '#a78bfa';
  ctx.font      = '90px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('✨', S/2, 380);

  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 130px sans-serif';
  ctx.fillText(userXP.toLocaleString() + ' XP', S/2, 560);

  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 40px sans-serif';
  ctx.fillText(name + ' is mastering Arabic!', S/2, 640);

  ctx.fillStyle = '#8b87b0';
  ctx.font      = '28px sans-serif';
  ctx.fillText('Every XP is a step toward fluency', S/2, 700);

  if (msg) { ctx.fillStyle = '#f0eeff'; ctx.font = '26px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 780); }
}

function drawWord(ctx, S, name, msg) {
  ctx.fillStyle = '#d4a843';
  ctx.font      = '130px serif';
  ctx.textAlign = 'center';
  ctx.fillText(wordOfDay.arabic, S/2, 440);

  ctx.fillStyle = '#8b87b0';
  ctx.font      = '28px sans-serif';
  ctx.fillText(wordOfDay.roman, S/2, 500);

  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 52px sans-serif';
  ctx.fillText(wordOfDay.meaning, S/2, 580);

  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 34px sans-serif';
  ctx.fillText("Today's Arabic word — learned by " + name, S/2, 660);

  if (msg) { ctx.fillStyle = '#8b87b0'; ctx.font = '26px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 740); }
}

function drawRank(ctx, S, name, msg) {
  ctx.fillStyle = accentColor;
  ctx.font      = '100px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('🏆', S/2, 400);

  ctx.fillStyle = '#d4a843';
  ctx.font      = 'bold 90px serif';
  ctx.fillText(rankName, S/2, 540);

  ctx.fillStyle = '#f0eeff';
  ctx.font      = 'bold 40px sans-serif';
  ctx.fillText(name + ' just levelled up!', S/2, 620);

  ctx.fillStyle = '#8b87b0';
  ctx.font      = '28px sans-serif';
  ctx.fillText('New Arabic rank unlocked on Faseeh', S/2, 680);

  if (msg) { ctx.fillStyle = '#f0eeff'; ctx.font = '26px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 760); }
}

function downloadCard() {
  const canvas = document.getElementById('share-canvas');
  const a = document.createElement('a');
  a.download = 'faseeh-share-' + currentType + '.png';
  a.href     = canvas.toDataURL('image/png', 1.0);
  a.click();
}

async function copyCard() {
  const canvas = document.getElementById('share-canvas');
  canvas.toBlob(async blob => {
    try {
      await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
      alert('Image copied to clipboard!');
    } catch { downloadCard(); }
  });
}

function shareTwitter() {
  const texts = {
    streak: `🔥 I'm on a ${streak}-day Arabic learning streak on Faseeh! Join me at faseeh.com #Arabic #Learning`,
    xp:     `✨ I just hit ${userXP} XP on Faseeh — mastering Arabic one word at a time! faseeh.com #Arabic`,
    word:   `📖 Today's Arabic word: ${wordOfDay.arabic} (${wordOfDay.meaning}) — learning with Faseeh! faseeh.com`,
    rank:   `🏆 I just earned the Arabic rank ${rankName} on Faseeh! faseeh.com #Arabic #Learning`,
  };
  const text = encodeURIComponent(texts[currentType] || 'Learning Arabic with Faseeh! faseeh.com');
  window.open('https://twitter.com/intent/tweet?text=' + text, '_blank');
}

// Init
window.addEventListener('load', redraw);
</script>
</body>
</html>
