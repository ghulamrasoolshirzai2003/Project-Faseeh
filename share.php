<?php
// share.php — Faseeh Social Sharing Cards Generator
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');

// FETCH LIVE STATS FOR ACCURACY
$stmt = $pdo->prepare("SELECT xp, daily_streak FROM progress WHERE user_id = ?");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
$user_xp = $stats['xp'] ?? 0;
$streak  = $stats['daily_streak'] ?? 0;

// Fetch Rank
$academic_ranks = [
    ["name" => "Novice", "min_q" => 0],
    ["name" => "Apprentice", "min_q" => 50],
    ["name" => "Scholar", "min_q" => 150],
    ["name" => "Grammarian", "min_q" => 300],
    ["name" => "Linguist", "min_q" => 600],
    ["name" => "Master", "min_q" => 1000],
    ["name" => "Legend", "min_q" => 1500]
];
$ansStmt = $pdo->prepare("SELECT academic_correct_count FROM progress WHERE user_id = ?");
$ansStmt->execute([$uid]);
$academic_q_count = $ansStmt->fetch()['academic_correct_count'] ?? 0;
$rank_name = "Novice";
foreach ($academic_ranks as $r) {
    if ($academic_q_count >= $r['min_q']) $rank_name = $r['name'];
}

// Fetch Word of the Day
$wordStmt = $pdo->query("SELECT * FROM words WHERE is_daily=1 LIMIT 1");
$dailyWord = $wordStmt->fetch();
if(!$dailyWord) $dailyWord = ['word'=>'جَمَرك', 'translation'=>'Customs'];

$card_types = [
  ['id'=>'streak',      'label'=>'Streak Card',        'icon'=>'🔥', 'desc'=>'Show off your learning streak'],
  ['id'=>'xp',          'label'=>'XP Milestone',        'icon'=>'✨', 'desc'=>'Celebrate your XP achievement'],
  ['id'=>'word',        'label'=>'Word of the Day',      'icon'=>'📖', 'desc'=>'Share today\'s Arabic word'],
  ['id'=>'rank',        'label'=>'Rank Achievement',     'icon'=>'🏆', 'desc'=>'Announce your new Arabic rank'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Share Your Progress — Faseeh</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
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
    color: var(--text); font-family: 'Poppins', sans-serif; min-height: 100vh; overflow-x: hidden;
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
.page-header{text-align:center;margin-bottom:40px}
.section-label{font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
h1{font-weight:800;font-size:2.5rem;margin-bottom:10px}
.page-sub{color:var(--muted);font-size:.95rem}

/* TABS */
.type-tabs{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:32px;justify-content:center}
.type-tab{background:var(--bg-card);border:1px solid var(--border);border-radius:50px;
  padding:12px 24px;cursor:pointer;font-size:.9rem;color:var(--muted);transition:.3s;display:flex;align-items:center;gap:10px;backdrop-filter:blur(10px)}
.type-tab:hover{border-color:var(--accent);color:var(--text)}
.type-tab.active{border-color:var(--accent);background:rgba(242,153,74,0.15);color:var(--accent);font-weight:700;box-shadow:0 0 20px rgba(242,153,74,0.1)}

/* CANVAS PREVIEW */
.canvas-section{display:grid;grid-template-columns:1fr 350px;gap:35px;align-items:start}
.canvas-wrap{
    background: var(--bg-card); border: 1px solid var(--border); border-radius: 30px; 
    padding: 30px; backdrop-filter: blur(20px); box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
canvas#share-canvas{width:100%;border-radius:15px;display:block;box-shadow: 0 10px 30px rgba(0,0,0,0.5);}
.customise-panel{background:var(--bg-card);border:1px solid var(--border);border-radius:30px;padding:30px;display:flex;flex-direction:column;gap:25px;backdrop-filter:blur(20px)}
.customise-panel h3{font-weight:700;font-size:1.1rem;margin-bottom:5px;color:var(--accent)}
.field-group{display:flex;flex-direction:column;gap:8px}
.field-label{font-size:.8rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.field-input{background:rgba(0,0,0,0.2);border:1px solid var(--border);border-radius:12px;
  padding:14px;color:var(--text);font-family:inherit;font-size:.95rem;width:100%;transition:.3s}
.field-input:focus{outline:none;border-color:var(--accent);background:rgba(0,0,0,0.4)}
.color-row{display:flex;gap:10px;flex-wrap:wrap}
.color-btn{width:32px;height:32px;border-radius:50%;border:3px solid transparent;cursor:pointer;transition:.2s}
.color-btn.selected{border-color:#fff;transform:scale(1.2);box-shadow:0 0 15px rgba(255,255,255,0.3)}

.share-btn{width:100%;padding:16px;border-radius:15px;border:none;cursor:pointer;font-weight:700;font-size:.95rem;transition:.3s;display:flex;align-items:center;justify-content:center;gap:10px}
.share-btn.download{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#000}
.share-btn.download:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(242,153,74,0.3)}
.share-btn.copy{background:rgba(255,255,255,0.06);border:1px solid var(--border);color:var(--text)}
.share-btn.copy:hover{background:rgba(255,255,255,0.12)}
.share-btn.twitter{background:#1DA1F2;color:#fff}
.share-btn.instagram{background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);color:#fff}

@media(max-width:900px){ .canvas-section{grid-template-columns:1fr} }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:600">← Hub Dashboard</a>
</nav>

<div class="page">
  <div class="page-header">
    <div class="section-label">Viralize Your Mastery</div>
    <h1>📤 Sharing Cards</h1>
    <p class="page-sub">Celebrate your Arabic milestones with beautiful, shareable achievement cards.</p>
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
      <h3>🎨 Customise Card</h3>

      <div class="field-group">
        <div class="field-label">Student Name</div>
        <input type="text" class="field-input" id="custom-name" value="<?= $username ?>" oninput="redraw()"/>
      </div>
      <div class="field-group">
        <div class="field-label">Personal Message</div>
        <input type="text" class="field-input" id="custom-msg" value="" placeholder="e.g. My Arabic journey is booming!" oninput="redraw()"/>
      </div>
      <div class="field-group">
        <div class="field-label">Theme Colour</div>
        <div class="color-row">
          <?php
          $colors = ['#f2994a','#FFD700','#3ecf8e','#7c5cbf','#e74c3c','#3498db','#e86db5'];
          foreach ($colors as $c): ?>
          <div class="color-btn <?= $c==='#f2994a'?'selected':'' ?>" style="background:<?= $c ?>"
               data-color="<?= $c ?>" onclick="selectColor(this)"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:flex; flex-direction:column; gap:12px;">
        <button class="share-btn download" onclick="downloadCard()">⬇ Save Achievement</button>
        <button class="share-btn copy" onclick="copyCard()">📋 Copy Image</button>
        <button class="share-btn twitter" onclick="shareTwitter()">𝕏 Share on Twitter</button>
        <button class="share-btn instagram" onclick="alert('Card saved! Now upload it to your Instagram story.')">◎ Share on Instagram</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentType = 'streak';
let accentColor = '#f2994a';
const userXP    = <?= $user_xp ?>;
const streak    = <?= $streak ?>;
const rankName  = <?= json_encode($rank_name) ?>;
const wordOfDay = { 
    arabic: '<?= $dailyWord['word'] ?>', 
    translation: '<?= $dailyWord['translation'] ?>' 
};

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
  const name = document.getElementById('custom-name').value || 'Learner';
  const msg  = document.getElementById('custom-msg').value;

  const canvas = document.getElementById('share-canvas');
  const ctx    = canvas.getContext('2d');
  const S = 1080;
  ctx.clearRect(0, 0, S, S);

  // Deep Background
  ctx.fillStyle = '#0f0c29';
  ctx.fillRect(0, 0, S, S);

  // Gradient Overlay
  const grad = ctx.createLinearGradient(0, 0, S, S);
  grad.addColorStop(0, '#0f0c29');
  grad.addColorStop(1, '#24243e');
  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, S, S);

  // Radial Aura
  const glow = ctx.createRadialGradient(S/2, S/2, 0, S/2, S/2, S*0.8);
  glow.addColorStop(0, accentColor + '33');
  glow.addColorStop(1, 'transparent');
  ctx.fillStyle = glow;
  ctx.fillRect(0, 0, S, S);

  // Branding Box
  ctx.strokeStyle = accentColor;
  ctx.lineWidth   = 10;
  ctx.globalAlpha = 0.3;
  ctx.strokeRect(40, 40, S-80, S-80);
  ctx.globalAlpha = 1;

  // Header Logo
  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 45px sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('FASEEH ACADEMY', S/2, 110);
  
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '24px sans-serif';
  ctx.fillText('فصيح — Mastering Arabic together', S/2, 150);

  // Content Drawing
  if (currentType === 'streak') drawStreak(ctx, S, name, msg);
  if (currentType === 'xp')     drawXP(ctx, S, name, msg);
  if (currentType === 'word')   drawWord(ctx, S, name, msg);
  if (currentType === 'rank')   drawRank(ctx, S, name, msg);

  // Footer Tag
  ctx.fillStyle = accentColor;
  ctx.font      = 'bold 30px sans-serif';
  ctx.fillText('faseeh.com', S/2, S-90);
  ctx.fillStyle = '#8b87b0';
  ctx.font      = '22px sans-serif';
  ctx.fillText('Start your Arabic journey today', S/2, S-55);
}

function drawStreak(ctx, S, name, msg) {
  ctx.font = '180px sans-serif';
  ctx.fillText('🔥', S/2, 450);
  ctx.fillStyle = accentColor;
  ctx.font = 'bold 200px sans-serif';
  ctx.fillText(streak, S/2, 650);
  ctx.fillStyle = '#fff';
  ctx.font = '700 50px sans-serif';
  ctx.fillText('DAY STREAK', S/2, 730);
  ctx.fillStyle = '#8b87b0';
  ctx.font = '35px sans-serif';
  ctx.fillText(name + ' is unstoppable!', S/2, 800);
  if(msg) { ctx.fillStyle='#fff'; ctx.font='italic 30px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 880); }
}

function drawXP(ctx, S, name, msg) {
  ctx.font = '150px sans-serif';
  ctx.fillText('✨', S/2, 430);
  ctx.fillStyle = accentColor;
  ctx.font = 'bold 180px sans-serif';
  ctx.fillText(userXP.toLocaleString(), S/2, 630);
  ctx.fillStyle = '#fff';
  ctx.font = '700 50px sans-serif';
  ctx.fillText('TOTAL XP EARNED', S/2, 710);
  ctx.fillStyle = '#8b87b0';
  ctx.font = '35px sans-serif';
  ctx.fillText(name + ' is reaching new heights!', S/2, 780);
  if(msg) { ctx.fillStyle='#fff'; ctx.font='italic 30px sans-serif'; ctx.fillText('"' + msg + '"', S/2, 860); }
}

function drawWord(ctx, S, name, msg) {
  ctx.fillStyle = accentColor;
  ctx.font = 'bold 160px "Amiri", serif';
  ctx.fillText(wordOfDay.arabic, S/2, 480);
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 80px sans-serif';
  ctx.fillText(wordOfDay.translation, S/2, 620);
  ctx.fillStyle = '#8b87b0';
  ctx.font = '35px sans-serif';
  ctx.fillText("Learned today by " + name, S/2, 700);
}

function drawRank(ctx, S, name, msg) {
  ctx.font = '150px sans-serif';
  ctx.fillText('🏆', S/2, 430);
  ctx.fillStyle = '#FFD700';
  ctx.font = 'bold 120px "Amiri", serif';
  ctx.fillText(rankName, S/2, 600);
  ctx.fillStyle = '#fff';
  ctx.font = '700 50px sans-serif';
  ctx.fillText('NEW RANK UNLOCKED', S/2, 690);
  ctx.fillStyle = '#8b87b0';
  ctx.font = '35px sans-serif';
  ctx.fillText(name + ' just leveled up!', S/2, 760);
}

function downloadCard() {
  const canvas = document.getElementById('share-canvas');
  const a = document.createElement('a');
  a.download = 'faseeh-achievement.png';
  a.href = canvas.toDataURL('image/png', 1.0);
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
  const text = encodeURIComponent("Just hit a new milestone on @FaseehAcademy! 🚀 Learn Arabic with me at faseeh.com #Arabic #EdTech");
  window.open('https://twitter.com/intent/tweet?text=' + text, '_blank');
}

window.onload = redraw;
</script>
</body>
</html>
