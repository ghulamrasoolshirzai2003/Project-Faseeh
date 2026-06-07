<?php
// quran.php — Faseeh Quranic Arabic Track (Live Edition)
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
// We'll use a fraction of total correct answers as "Quranic words" for now, 
// or implement a specific quran_words column if you prefer later.
$quran_words = floor(($stats['academic_correct_count'] ?? 0) / 3); 

$surahs = [
  ['id'=>1,   'arabic'=>'الفاتحة', 'name'=>'Al-Fatiha',  'meaning'=>'The Opening',       'verses'=>7,  'lessons'=>3,  'level'=>'Beginner',     'color'=>'#FFD700', 'icon'=>'🌟'],
  ['id'=>112, 'arabic'=>'الإخلاص', 'name'=>'Al-Ikhlas', 'meaning'=>'Sincerity',         'verses'=>4,  'lessons'=>2,  'level'=>'Beginner',     'color'=>'#7c5cbf', 'icon'=>'💎'],
  ['id'=>113, 'arabic'=>'الفلق',   'name'=>'Al-Falaq',  'meaning'=>'The Daybreak',      'verses'=>5,  'lessons'=>2,  'level'=>'Beginner',     'color'=>'#3ecf8e', 'icon'=>'🌅'],
  ['id'=>114, 'arabic'=>'الناس',   'name'=>'An-Nas',    'meaning'=>'Mankind',           'verses'=>6,  'lessons'=>2,  'level'=>'Beginner',     'color'=>'#e85d5d', 'icon'=>'🤲'],
  ['id'=>2,   'arabic'=>'البقرة',  'name'=>'Al-Baqarah','meaning'=>'The Cow',           'verses'=>286,'lessons'=>12, 'level'=>'Intermediate', 'color'=>'#5ca8e8', 'icon'=>'📖'],
  ['id'=>36,  'arabic'=>'يس',      'name'=>'Ya-Sin',    'meaning'=>'Ya Sin',            'verses'=>83, 'lessons'=>8,  'level'=>'Intermediate', 'color'=>'#f2994a', 'icon'=>'❤️'],
  ['id'=>67,  'arabic'=>'الملك',   'name'=>'Al-Mulk',   'meaning'=>'The Sovereignty',   'verses'=>30, 'lessons'=>5,  'level'=>'Advanced',     'color'=>'#8ee83e', 'icon'=>'👑'],
];

$top_words = [
  ['اللَّهُ',    'Allah',    'God / Allah',              2699, 'أله', '﴿بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ﴾'],
  ['رَبّ',       'Rabb',     'Lord / Sustainer',          980,  'ربب', '﴿الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ﴾'],
  ['مَا',        'Ma',       'What / That which',         2131, '—',   '﴿وَمَا أَرْسَلْنَاكَ إِلَّا رَحْمَةً﴾'],
  ['قَالَ',      'Qala',     'He said',                  1722, 'قول', '﴿قَالَ رَبِّ اشْرَحْ لِي صَدْرِي﴾'],
  ['إِنَّ',      'Inna',     'Indeed / Verily',          1448, '—',   '﴿إِنَّ اللَّهَ غَفُورٌ رَّحِيمٌ﴾'],
  ['لَا',        'La',       'No / Not',                 5098, '—',   '﴿لَا إِلَٰهَ إِلَّا اللَّهُ﴾'],
  ['يَوْم',      'Yawm',     'Day',                       405,  'يوم', '﴿مَالِكِ يَوْمِ الدِّينِ﴾'],
  ['قُلْ',       'Qul',      'Say! (command)',            332,  'قول', '﴿قُلْ هُوَ اللَّهُ أَحَدٌ﴾'],
  ['رَحْمَة',    'Rahma',    'Mercy / Compassion',        79,   'رحم', '﴿وَرَحْمَتِي وَسِعَتْ كُلَّ شَيْءٍ﴾'],
  ['نَفْس',      'Nafs',     'Soul / Self / Person',     295,  'نفس', '﴿كُلُّ نَفْسٍ ذَائِقَةُ الْمَوْتِ﴾'],
];

$ayat_kursi = [
  ['اللَّهُ',       'Allah',        'God',                   'The proper name of the Creator'],
  ['لَا',           'La',           'No / Not',              'Negation particle'],
  ['إِلَٰهَ',       'Ilaha',        'deity / god',           'From root أله — one who is worshipped'],
  ['إِلَّا',        'Illa',         'except',                'Exclusion particle'],
  ['هُوَ',          'Huwa',         'He',                    'Third-person masculine pronoun'],
  ['الْحَيُّ',      'Al-Hayy',      'The Ever-Living',       'From root حيي — life'],
  ['الْقَيُّومُ',   'Al-Qayyum',    'The Self-Subsisting',   'From root قوم — to stand/sustain'],
];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Quranic Arabic — Faseeh Academy</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet"/>
<?php include 'pwa_install.php'; ?>
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

.page{max-width:1200px;margin:0 auto;padding:40px 24px 80px}

.quran-hero{
  background: linear-gradient(135deg, rgba(15,12,41,0.8), rgba(48,43,99,0.8));
  border: 1px solid var(--border); border-radius: 30px;
  padding: 60px 40px; margin-bottom: 50px; text-align: center; position: relative; overflow: hidden;
  backdrop-filter: blur(20px);
}
.quran-hero-bismillah{
  font-family:'Amiri',serif; font-size: 3.5rem; color: var(--gold); margin-bottom: 20px;
  text-shadow: 0 0 40px rgba(255,215,0,0.3);
}
.quran-hero h1{font-family:'Syne',sans-serif; font-weight:800; font-size: 2.5rem; margin-bottom: 15px;}
.quran-hero p{color:var(--muted); max-width: 650px; margin: 0 auto 30px; line-height: 1.7}

.progress-card{
  background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);
  padding:30px; margin-bottom:40px; display:flex; align-items:center; gap:35px; backdrop-filter:blur(10px);
}
.prog-stat{text-align:center; min-width:100px}
.prog-stat strong{display:block; font-size:2.2rem; font-weight:800; color:var(--accent)}
.prog-stat span{font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.1em}
.prog-divider{width:1px; height:50px; background:var(--border)}
.prog-bar-wrap{flex:1}
.prog-bar{height:10px; background:rgba(255,255,255,0.08); border-radius:50px; overflow:hidden; margin-top:10px}
.prog-bar-fill{height:100%; border-radius:50px; background:linear-gradient(90deg,var(--accent),var(--gold)); transition:width 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)}

.tabs{display:flex; gap:10px; margin-bottom:35px; flex-wrap:wrap}
.tab-btn{
  padding:12px 25px; border-radius:50px; border:1px solid var(--border);
  background:var(--bg-card); color:var(--muted); font-weight:600; cursor:pointer; transition:.3s;
}
.tab-btn.active{background:var(--accent); color:#000; border-color:var(--accent)}

.surah-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:25px}
.surah-card{
  background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius);
  padding:25px; transition:.3s; cursor:pointer; position:relative; overflow:hidden;
}
.surah-card:hover{transform:translateY(-5px); border-color:var(--surah-color)}
.surah-card::before{content:''; position:absolute; top:0; left:0; right:0; height:4px; background:var(--surah-color); opacity:0.8}
.surah-arabic{font-family:'Amiri',serif; font-size:2rem; color:var(--gold); margin-bottom:10px; text-align:right}
.surah-name{font-weight:800; font-size:1.2rem; margin-bottom:5px}
.surah-meaning{font-size:.85rem; color:var(--muted); margin-bottom:15px}
.surah-meta{display:flex; gap:10px; flex-wrap:wrap}
.surah-pill{font-size:.7rem; font-weight:700; padding:4px 10px; border-radius:50px; background:rgba(255,255,255,0.08); color:var(--muted)}

.wbw-container{background:var(--bg-card); border:1px solid var(--border); border-radius:30px; padding:40px; margin-bottom:40px}
.wbw-verse{
  font-family:'Amiri',serif; font-size:2.8rem; direction:rtl; text-align:center;
  line-height:2; color:#fff; margin-bottom:40px; padding-bottom:30px; border-bottom:1px solid var(--border);
}
.wbw-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:20px}
.wbw-card{
  background:var(--bg-card2); border:1px solid var(--border); border-radius:15px;
  padding:20px; text-align:center; cursor:pointer; transition:.2s;
}
.wbw-card:hover{border-color:var(--gold); background:rgba(255,215,0,0.05)}
.wbw-arabic{font-family:'Amiri',serif; font-size:2.2rem; color:var(--gold); margin-bottom:10px; display:block}
.wbw-meaning{font-weight:700; font-size:1rem; margin-bottom:5px}

.words-table{width:100%; border-collapse:collapse; margin-top:20px}
.words-table th{text-align:left; color:var(--muted); font-size:.75rem; text-transform:uppercase; padding:15px; border-bottom:1px solid var(--border)}
.words-table td{padding:20px 15px; border-bottom:1px solid rgba(255,255,255,0.05)}
.word-arabic{font-family:'Amiri',serif; font-size:2rem; color:var(--gold)}

/* =========================================
   MOBILE RESPONSIVENESS (100% PC SAFE)
   ========================================= */
@media(max-width:768px){
  .progress-card{flex-direction:column; text-align:center; padding:20px;}
  .prog-divider{display:none}
  nav { padding: 0 15px; height: 60px; }
  .nav-logo { font-size: 1.2rem; }
  .page { padding: 20px 15px 40px; }
  .quran-hero { padding: 30px 20px; border-radius: 20px; }
  .quran-hero-bismillah { font-size: 2.2rem; margin-bottom: 10px; }
  .quran-hero h1 { font-size: 1.8rem; }
  .quran-hero p { font-size: 0.9rem; }
  .surah-grid { grid-template-columns: 1fr; gap: 15px; }
  .wbw-container { padding: 25px 15px; }
  .wbw-verse { font-size: 1.8rem; line-height: 1.6; }
  .wbw-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
  .wbw-arabic { font-size: 1.6rem; }
  .tabs { justify-content: center; }
  .tab-btn { flex: 1 1 100%; text-align: center; }
}
</style>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:600">← Hub Dashboard</a>
</nav>

<div class="page">

  <div class="quran-hero">
    <div class="quran-hero-bismillah">بِسْمِ ٱللَّٰهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</div>
    <h1>Quranic Mastery Track</h1>
    <p>Understand the words of Allah directly. Master the most frequent Quranic vocabulary and connect deeply with the Holy Surahs.</p>
  </div>

  <div class="progress-card">
    <div class="prog-stat"><strong><?= $quran_words ?></strong><span>Words Learned</span></div>
    <div class="prog-divider"></div>
    <div class="prog-stat"><strong><?= count($surahs) ?></strong><span>Surahs Available</span></div>
    <div class="prog-divider"></div>
    <div class="prog-bar-wrap">
      <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--muted);margin-bottom:10px">
        <span>Quranic Rank: <strong style="color:var(--gold)">مبتدئ</strong></span>
        <span><?= $quran_words ?> / 50 words</span>
      </div>
      <div class="prog-bar"><div class="prog-bar-fill" style="width:<?= min(100, ($quran_words/50)*100) ?>%"></div></div>
    </div>
  </div>

  <div class="tabs">
    <button id="btn-library" class="tab-btn active" onclick="switchTab('library')">📖 Surah Library</button>
    <button id="btn-parser" class="tab-btn" onclick="switchTab('parser')">🔍 Grammatical Parser (AI)</button>
    <button id="btn-vocab" class="tab-btn" onclick="switchTab('vocab')">📊 Quranic Vocabulary</button>
  </div>

  <!-- TAB CONTENT: SURAH LIBRARY -->
  <div id="content-library" class="tab-content">
    <div class="surah-grid">
      <?php foreach ($surahs as $s): ?>
      <div class="surah-card" style="--surah-color:<?= $s['color'] ?>">
        <div class="surah-arabic"><?= $s['arabic'] ?></div>
        <div class="surah-name"><?= $s['icon'] ?> <?= $s['name'] ?></div>
        <div class="surah-meaning"><?= $s['meaning'] ?></div>
        <div class="surah-meta">
          <span class="surah-pill"><?= $s['verses'] ?> verses</span>
          <span class="surah-pill"><?= $s['level'] ?></span>
        </div>
        <a href="game.php?mode=quran&surah=<?= $s['id'] ?>" style="display:block; margin-top:20px; padding:10px; border-radius:10px; background:rgba(255,255,255,0.05); color:var(--text); text-align:center; text-decoration:none; font-weight:700">Start Study →</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- TAB CONTENT: AI PARSER -->
  <div id="content-parser" class="tab-content" style="display:none;">
    <div class="wbw-container">
        <h2 style="margin-bottom:15px; text-align:center; color: var(--gold); font-family: 'Syne', sans-serif;">🕌 Quranic I'rab & Morphological Parser</h2>
        <p style="text-align:center; color: var(--muted); margin-bottom: 30px; font-size: 0.95rem;">Type or paste any verse from the Holy Quran, or click one of our curated verses to analyze the grammar, root words, cases, and types of every word!</p>
        
        <!-- Preset Suggestions -->
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 25px;">
            <button onclick="presetParse('بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ')" style="padding: 8px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; color: var(--gold); font-family: 'Amiri', serif; font-size: 1.1rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</button>
            <button onclick="presetParse('قُلْ هُوَ اللَّهُ أَحَدٌ')" style="padding: 8px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; color: var(--gold); font-family: 'Amiri', serif; font-size: 1.1rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">قُلْ هُوَ اللَّهُ أَحَدٌ</button>
            <button onclick="presetParse('اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ')" style="padding: 8px 16px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; color: var(--gold); font-family: 'Amiri', serif; font-size: 1.1rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">آية الكرسي (بداية)</button>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
            <input type="text" id="parse-input" placeholder="Enter verse (e.g. إِنَّ اللَّهَ مَعَ الصَّابِرِينَ)..." style="flex: 1; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--border); background: rgba(0,0,0,0.3); color: white; font-family: 'Amiri', serif; font-size: 1.6rem; text-align: right; direction: rtl; min-width: 280px;" required>
            <button id="parse-btn" onclick="parseSentence()" style="padding: 15px 30px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border: none; color: black; font-weight: 800; border-radius: 15px; cursor: pointer; transition: 0.3s; box-shadow: 0 5px 15px rgba(242,153,74,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                Parse with AI ⚡
            </button>
        </div>

        <!-- Loading State -->
        <div id="parser-loading" style="display: none; text-align: center; margin: 40px 0;">
            <div style="width: 50px; height: 50px; border: 5px solid rgba(255,255,255,0.1); border-top-color: var(--gold); border-radius: 50%; animation: spin 1s infinite linear; margin: 0 auto 15px;"></div>
            <p style="opacity: 0.7; font-size: 1.1rem;">Analyzing Classical Grammatical Structure (I'rab)...</p>
        </div>
        
        <style>
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            .tab-content { animation: fadeIn 0.4s ease; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        </style>

        <!-- Active Parser Area -->
        <div id="parser-results" style="display: none;">
            <div id="parsed-verse-text" class="wbw-verse" style="line-height: 2.2; color: var(--gold);"></div>
            
            <h3 style="margin-bottom: 20px; color: var(--gold); font-size: 1.2rem;">🔍 Word-by-Word Grammatical Analysis</h3>
            <div id="parsed-cards" class="wbw-grid" style="margin-bottom: 40px;"></div>
            
            <!-- Detailed analysis drawer -->
            <div id="analysis-drawer" style="display: none; padding: 30px; background: rgba(0,0,0,0.4); border: 1px solid var(--border); border-radius: 20px; animation: slideUp 0.4s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                    <h3 id="drawer-title" style="font-family: 'Amiri', serif; font-size: 2.5rem; color: var(--gold);"></h3>
                    <span id="drawer-type" style="background: var(--accent); color: black; font-weight: bold; font-size: 0.75rem; padding: 4px 12px; border-radius: 20px;"></span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Root (الجذر)</span>
                        <strong id="drawer-root" style="font-family: 'Amiri', serif; font-size: 1.8rem; color: white;"></strong>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Grammatical Case (الإعراب)</span>
                        <strong id="drawer-case" style="font-size: 1.1rem; color: white;"></strong>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Morphological Pattern</span>
                        <strong id="drawer-weight" style="font-family: 'Amiri', serif; font-size: 1.3rem; color: white;"></strong>
                    </div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Phonetics</span>
                        <strong id="drawer-translit" style="font-size: 1.1rem; color: white;"></strong>
                    </div>
                </div>
                <div>
                    <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Contextual Meaning</span>
                    <p id="drawer-meaning" style="font-size: 1.2rem; font-weight: 600; color: var(--gold); margin-bottom: 15px;"></p>
                    
                    <span style="display:block; font-size:0.7rem; color:var(--muted); text-transform:uppercase; margin-bottom:5px;">Syntactic Analysis (الإعراب التفصيلي)</span>
                    <p id="drawer-explanation" style="opacity: 0.9; line-height: 1.6;"></p>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- TAB CONTENT: HIGH FREQ VOCABULARY -->
  <div id="content-vocab" class="tab-content" style="display:none;">
    <div class="wbw-container">
        <h2 style="margin-bottom:15px; color: var(--gold); text-align:center; font-family: 'Syne', sans-serif;">📊 Top Quranic Vocabulary</h2>
        <p style="text-align:center; color: var(--muted); margin-bottom: 30px; font-size: 0.95rem;">These words make up over **65%** of the entire Quranic text. Mastering these grants immediate comprehension of most verses!</p>
        
        <div style="overflow-x: auto; background: rgba(0,0,0,0.15); border-radius: 20px; border: 1px solid var(--border);">
            <table class="words-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th>Phonetic</th>
                        <th>Core Meaning</th>
                        <th>Frequency</th>
                        <th>Root</th>
                        <th>Example Context</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_words as $w): ?>
                    <tr style="cursor: pointer;" onclick="playWord('<?= $w[0] ?>')">
                        <td><span class="word-arabic"><?= $w[0] ?></span></td>
                        <td style="font-weight: 600;"><?= $w[1] ?></td>
                        <td style="color: var(--gold); font-weight: bold;"><?= $w[2] ?></td>
                        <td><strong style="color: var(--accent);"><?= $w[3] ?> times</strong></td>
                        <td style="font-family: 'Amiri', serif; font-size: 1.3rem;"><?= $w[4] ?></td>
                        <td style="font-family: 'Amiri', serif; font-size: 1.3rem; direction: rtl; text-align: right; opacity: 0.8;"><?= $w[5] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>

</div>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>
<script>
let lastParsedTokens = [];

function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
    
    document.getElementById('btn-' + tabId).classList.add('active');
    document.getElementById('content-' + tabId).style.display = 'block';
}

function presetParse(sentence) {
    document.getElementById('parse-input').value = sentence;
    parseSentence();
}

async function parseSentence() {
    const inputVal = document.getElementById('parse-input').value.trim();
    if (!inputVal) {
        alert("Please enter or select an Arabic sentence to parse!");
        return;
    }
    
    const btn = document.getElementById('parse-btn');
    const loading = document.getElementById('parser-loading');
    const results = document.getElementById('parser-results');
    const drawer = document.getElementById('analysis-drawer');
    
    btn.disabled = true;
    loading.style.display = 'block';
    results.style.display = 'none';
    drawer.style.display = 'none';
    
    try {
        const response = await fetch('api/parse_quranic.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ sentence: inputVal })
        });
        const data = await response.json();
        
        if (data.error) {
            alert(data.error);
            loading.style.display = 'none';
            btn.disabled = false;
            return;
        }
        
        lastParsedTokens = data.tokens;
        
        // Render verse text
        document.getElementById('parsed-verse-text').innerHTML = lastParsedTokens.map((t, idx) => 
            `<span style="cursor: pointer; margin: 0 8px; transition: 0.3s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--gold)'" onclick="selectWord(${idx})">${t.word}</span>`
        ).join('');
        
        // Render simple cards
        document.getElementById('parsed-cards').innerHTML = lastParsedTokens.map((t, idx) => `
            <div class="wbw-card" onclick="selectWord(${idx})">
                <span class="wbw-arabic">${t.word}</span>
                <div class="wbw-meaning">${t.meaning}</div>
                <div style="font-size:0.75rem; color:var(--muted); font-weight:600;">${t.type}</div>
            </div>
        `).join('');
        
        loading.style.display = 'none';
        results.style.display = 'block';
        
        // Auto-select first word
        selectWord(0);
        
    } catch(err) {
        console.error(err);
        alert("Grammar engine offline. Try again.");
        loading.style.display = 'none';
    }
    btn.disabled = false;
}

function selectWord(idx) {
    const t = lastParsedTokens[idx];
    if (!t) return;
    
    // Play audio pronunciation
    playWord(t.word);
    
    const drawer = document.getElementById('analysis-drawer');
    drawer.style.display = 'block';
    
    document.getElementById('drawer-title').innerText = t.word;
    document.getElementById('drawer-type').innerText = t.type;
    document.getElementById('drawer-root').innerText = t.root;
    document.getElementById('drawer-case').innerText = t.state;
    document.getElementById('drawer-weight').innerText = t.weight;
    document.getElementById('drawer-translit').innerText = t.transliteration;
    document.getElementById('drawer-meaning').innerText = t.meaning;
    document.getElementById('drawer-explanation').innerText = t.explanation;
}

function playWord(text) {
  if (window.responsiveVoice) {
    responsiveVoice.speak(text, "Arabic Male", {rate: 0.7});
  }
}
</script>
</body>
</html>
