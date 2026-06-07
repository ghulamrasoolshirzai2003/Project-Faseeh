<?php
// quran.php — Faseeh Quranic Arabic Track
// Requires: session with $_SESSION['user_id'], $_SESSION['username']
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');
$user_id  = (int)($_SESSION['user_id'] ?? 0);

// ── Quranic Surah Data ──────────────────────────────────────────────────────
// Each surah has: id, arabic name, transliteration, english meaning,
// total verses, our lesson count, difficulty, description
$surahs = [
  [
    'id'     => 1,
    'arabic' => 'الفاتحة',
    'name'   => 'Al-Fatiha',
    'meaning'=> 'The Opening',
    'verses' => 7,
    'lessons'=> 3,
    'level'  => 'Beginner',
    'desc'   => 'The most recited surah in Islam. Master every word of the prayer you say 17 times daily.',
    'color'  => '#d4a843',
    'icon'   => '🌟',
  ],
  [
    'id'     => 112,
    'arabic' => 'الإخلاص',
    'name'   => 'Al-Ikhlas',
    'meaning'=> 'Sincerity',
    'verses' => 4,
    'lessons'=> 2,
    'level'  => 'Beginner',
    'desc'   => 'One of the shortest and most powerful surahs. Equals one-third of the Quran in reward.',
    'color'  => '#7c5cbf',
    'icon'   => '💎',
  ],
  [
    'id'     => 113,
    'arabic' => 'الفلق',
    'name'   => 'Al-Falaq',
    'meaning'=> 'The Daybreak',
    'verses' => 5,
    'lessons'=> 2,
    'level'  => 'Beginner',
    'desc'   => 'A surah of protection and seeking refuge. Learn the vocabulary of divine protection.',
    'color'  => '#3ecf8e',
    'icon'   => '🌅',
  ],
  [
    'id'     => 114,
    'arabic' => 'الناس',
    'name'   => 'An-Nas',
    'meaning'=> 'Mankind',
    'verses' => 6,
    'lessons'=> 2,
    'level'  => 'Beginner',
    'desc'   => 'The final surah — seeking refuge in Allah from the whisperings of Shaytan.',
    'color'  => '#e85d5d',
    'icon'   => '🤲',
  ],
  [
    'id'     => 2,
    'arabic' => 'البقرة',
    'name'   => 'Al-Baqarah',
    'meaning'=> 'The Cow',
    'verses' => 286,
    'lessons'=> 12,
    'level'  => 'Intermediate',
    'desc'   => 'The longest surah. Contains Ayatul Kursi, the greatest verse in the Quran.',
    'color'  => '#5ca8e8',
    'icon'   => '📖',
  ],
  [
    'id'     => 36,
    'arabic' => 'يس',
    'name'   => "Ya-Sin",
    'meaning'=> 'Ya Sin',
    'verses' => 83,
    'lessons'=> 8,
    'level'  => 'Intermediate',
    'desc'   => 'The heart of the Quran. Essential vocabulary for understanding divine power and resurrection.',
    'color'  => '#cf8e3e',
    'icon'   => '❤️',
  ],
  [
    'id'     => 55,
    'arabic' => 'الرحمن',
    'name'   => 'Ar-Rahman',
    'meaning'=> 'The Most Merciful',
    'verses' => 78,
    'lessons'=> 6,
    'level'  => 'Intermediate',
    'desc'   => 'The beauty of the Quran. Learn the refrain فَبِأَيِّ آلَاءِ رَبِّكُمَا and its surrounding vocabulary.',
    'color'  => '#e86db5',
    'icon'   => '✨',
  ],
  [
    'id'     => 67,
    'arabic' => 'الملك',
    'name'   => 'Al-Mulk',
    'meaning'=> 'The Sovereignty',
    'verses' => 30,
    'lessons'=> 5,
    'level'  => 'Advanced',
    'desc'   => 'The protector surah. Recite it every night — now understand every word you say.',
    'color'  => '#8ee83e',
    'icon'   => '👑',
  ],
];

// ── Top 50 Most Frequent Quran Words ───────────────────────────────────────
// word, transliteration, meaning, frequency in Quran, root, example verse
$top_words = [
  ['اللَّهُ',    'Allah',    'God / Allah',              2699, 'أله', '﴿بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ﴾'],
  ['رَبّ',       'Rabb',     'Lord / Sustainer',          980, 'ربب', '﴿الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ﴾'],
  ['مَا',        'Ma',       'What / That which',         2131, '—',  '﴿وَمَا أَرْسَلْنَاكَ إِلَّا رَحْمَةً﴾'],
  ['قَالَ',      'Qala',     'He said',                  1722, 'قول', '﴿قَالَ رَبِّ اشْرَحْ لِي صَدْرِي﴾'],
  ['إِنَّ',      'Inna',     'Indeed / Verily',          1448, '—',  '﴿إِنَّ اللَّهَ غَفُورٌ رَّحِيمٌ﴾'],
  ['لَا',        'La',       'No / Not',                 5098, '—',  '﴿لَا إِلَٰهَ إِلَّا اللَّهُ﴾'],
  ['مَنْ',       'Man',      'Who / Whoever',            1592, '—',  '﴿مَن يَعْمَلْ سُوءًا يُجْزَ بِهِ﴾'],
  ['كَانَ',      'Kana',     'Was / Used to be',         1358, 'كون', '﴿وَكَانَ اللَّهُ غَفُورًا رَّحِيمًا﴾'],
  ['الَّذِي',    "Alladhi",  'Who / The one who',        2250, '—',  '﴿الَّذِي خَلَقَ الْمَوْتَ وَالْحَيَاةَ﴾'],
  ['يَوْم',      'Yawm',     'Day',                       405, 'يوم', '﴿مَالِكِ يَوْمِ الدِّينِ﴾'],
  ['قُلْ',       'Qul',      'Say! (command)',            332, 'قول', '﴿قُلْ هُوَ اللَّهُ أَحَدٌ﴾'],
  ['عَلَى',      'Ala',      'On / Upon / Over',         1440, '—',  '﴿وَعَلَى اللَّهِ فَتَوَكَّلُوا﴾'],
  ['أَنَّ',      'Anna',     'That (conjunction)',        1240, '—',  '﴿أَلَمْ تَعْلَمْ أَنَّ اللَّهَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ﴾'],
  ['آيَة',       'Ayah',     'Sign / Verse',              382, 'أيي', '﴿تِلْكَ آيَاتُ اللَّهِ﴾'],
  ['رَحْمَة',    'Rahma',    'Mercy / Compassion',        79,  'رحم', '﴿وَرَحْمَتِي وَسِعَتْ كُلَّ شَيْءٍ﴾'],
  ['نَفْس',      'Nafs',     'Soul / Self / Person',     295, 'نفس', '﴿كُلُّ نَفْسٍ ذَائِقَةُ الْمَوْتِ﴾'],
  ['عِلْم',      'Ilm',      'Knowledge',                 105, 'علم', '﴿وَفَوْقَ كُلِّ ذِي عِلْمٍ عَلِيمٌ﴾'],
  ['أَرْض',      'Ard',      'Earth / Land',             461, 'أرض', '﴿وَلِلَّهِ مَا فِي السَّمَاوَاتِ وَالْأَرْضِ﴾'],
  ['سَمَاء',     'Sama',     'Sky / Heaven',             310, 'سمو', '﴿وَالسَّمَاءَ بَنَيْنَاهَا بِأَيْدٍ﴾'],
  ['حَقّ',       'Haqq',     'Truth / Right / Due',      227, 'حقق', '﴿ذَٰلِكَ بِأَنَّ اللَّهَ هُوَ الْحَقُّ﴾'],
];

// ── Ayat al-Kursi word-by-word breakdown ──────────────────────────────────
$ayat_kursi = [
  ['اللَّهُ',       'Allah',        'God',                   'The proper name of the Creator'],
  ['لَا',           'La',           'No / Not',              'Negation particle'],
  ['إِلَٰهَ',       'Ilaha',        'deity / god',           'From root أله — one who is worshipped'],
  ['إِلَّا',        'Illa',         'except',                'Exclusion particle'],
  ['هُوَ',          'Huwa',         'He',                    'Third-person masculine pronoun'],
  ['الْحَيُّ',      'Al-Hayy',      'The Ever-Living',       'From root حيي — life'],
  ['الْقَيُّومُ',   'Al-Qayyum',    'The Self-Subsisting',   'From root قوم — to stand/sustain'],
  ['لَا',           'La',           'No / Not',              'Negation particle (repeated)'],
  ['تَأْخُذُهُ',    "Ta'khudhuhu",  'Seizes Him / Takes Him','From root أخذ — to take/seize'],
  ['سِنَةٌ',        'Sinatun',      'drowsiness / slumber',  'Light sleep, dozing off'],
  ['وَلَا',         'Wa-la',        'nor',                   'Conjunction + negation'],
  ['نَوْمٌ',        'Nawmun',       'sleep',                 'Deep sleep — stronger than سِنَة'],
];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Quranic Arabic — Faseeh Academy</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet"/>
<style>
:root{
  --bg:#0e0c1e; --bg-card:#161430; --bg-card2:#1c1a38;
  --border:rgba(255,255,255,.07); --accent:#f5a623; --accent2:#7c5cbf;
  --accent3:#3ecf8e; --gold:#d4a843; --text:#f0eeff; --muted:#8b87b0;
  --radius:16px; --radius-lg:24px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh}

/* NAV */
nav{position:sticky;top:0;z-index:50;background:rgba(14,12,30,.92);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 32px;height:64px;
  display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;
  font-weight:800;font-size:1.25rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#e8862a);
  display:flex;align-items:center;justify-content:center;font-size:.95rem}
.nav-logo span{color:var(--accent)}
.nav-links{display:flex;gap:8px}
.nav-link{padding:8px 16px;border-radius:50px;text-decoration:none;color:var(--muted);
  font-size:.875rem;font-weight:500;transition:.2s}
.nav-link:hover{color:var(--text);background:rgba(255,255,255,.05)}
.nav-link.active{background:rgba(245,166,35,.12);color:var(--accent)}
.nav-user{display:flex;align-items:center;gap:12px;font-size:.875rem;color:var(--muted)}
.nav-user strong{color:var(--text)}

/* LAYOUT */
.page{max-width:1200px;margin:0 auto;padding:40px 24px 80px}

/* HERO BANNER */
.quran-hero{
  background:linear-gradient(135deg,#1a1535 0%,#0f1a2e 50%,#1a1535 100%);
  border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:48px;margin-bottom:48px;position:relative;overflow:hidden;
  text-align:center;
}
.quran-hero::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 60% 80% at 50% 50%,rgba(212,168,67,.12),transparent);
  pointer-events:none;
}
.quran-hero-bismillah{
  font-family:'Amiri',serif;font-size:clamp(2rem,5vw,3.5rem);
  color:var(--gold);line-height:1.6;margin-bottom:16px;
  text-shadow:0 0 60px rgba(212,168,67,.4);position:relative;
}
.quran-hero h1{font-family:'Syne',sans-serif;font-weight:800;
  font-size:clamp(1.5rem,3vw,2.2rem);margin-bottom:12px;position:relative}
.quran-hero p{color:var(--muted);max-width:560px;margin:0 auto 28px;position:relative}
.hero-badges{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative}
.hero-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;
  border-radius:50px;border:1px solid var(--border);font-size:.78rem;font-weight:600;
  background:rgba(255,255,255,.04);color:var(--muted)}
.hero-badge.gold{border-color:rgba(212,168,67,.4);background:rgba(212,168,67,.08);color:var(--gold)}

/* PROGRESS CARD */
.progress-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:24px 28px;margin-bottom:32px;
  display:flex;align-items:center;gap:24px;flex-wrap:wrap;
}
.prog-stat{text-align:center;min-width:80px}
.prog-stat strong{display:block;font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--accent)}
.prog-stat span{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.prog-divider{width:1px;height:48px;background:var(--border)}
.prog-bar-wrap{flex:1;min-width:200px}
.prog-bar-label{display:flex;justify-content:space-between;font-size:.82rem;color:var(--muted);margin-bottom:8px}
.prog-bar{height:8px;background:rgba(255,255,255,.07);border-radius:50px;overflow:hidden}
.prog-bar-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--accent),#e8862a);transition:width 1s ease}
.prog-next{font-size:.82rem;color:var(--muted);margin-top:6px}
.prog-next strong{color:var(--text)}

/* SECTION HEADERS */
.section-header{margin-bottom:24px}
.section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:6px}
.section-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.5rem}
.section-sub{color:var(--muted);font-size:.9rem;margin-top:6px}

/* TABS */
.tabs{display:flex;gap:4px;background:var(--bg-card);border:1px solid var(--border);
  border-radius:50px;padding:4px;width:fit-content;margin-bottom:32px}
.tab-btn{padding:8px 20px;border-radius:50px;border:none;background:none;
  color:var(--muted);font-size:.875rem;font-weight:500;cursor:pointer;transition:.2s;white-space:nowrap}
.tab-btn.active{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;font-weight:700}
.tab-content{display:none}.tab-content.active{display:block}

/* SURAH GRID */
.surah-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:48px}
.surah-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:24px;cursor:pointer;transition:.25s;position:relative;overflow:hidden;
  text-decoration:none;color:inherit;display:block;
}
.surah-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:var(--surah-color,var(--accent));opacity:.6}
.surah-card:hover{transform:translateY(-3px);border-color:rgba(255,255,255,.12);
  box-shadow:0 12px 40px rgba(0,0,0,.4)}
.surah-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.surah-arabic{font-family:'Amiri',serif;font-size:1.8rem;color:var(--gold);line-height:1}
.surah-num{width:36px;height:36px;border-radius:50%;border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;font-size:.8rem;color:var(--muted)}
.surah-name{font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;margin-bottom:2px}
.surah-meaning{font-size:.78rem;color:var(--muted);margin-bottom:12px}
.surah-desc{font-size:.83rem;color:var(--muted);line-height:1.6;margin-bottom:16px}
.surah-meta{display:flex;gap:8px;flex-wrap:wrap}
.surah-pill{font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:50px;
  background:rgba(255,255,255,.06);color:var(--muted)}
.surah-pill.level-Beginner{background:rgba(62,207,142,.1);color:var(--accent3)}
.surah-pill.level-Intermediate{background:rgba(245,166,35,.1);color:var(--accent)}
.surah-pill.level-Advanced{background:rgba(232,93,93,.1);color:#e85d5d}
.surah-progress{margin-top:16px}
.surah-prog-bar{height:4px;background:rgba(255,255,255,.06);border-radius:50px;overflow:hidden;margin-top:6px}
.surah-prog-fill{height:100%;border-radius:50px;background:var(--surah-color,var(--accent));transition:width .6s}
.surah-start-btn{
  display:block;width:100%;margin-top:16px;padding:10px;border-radius:10px;
  background:rgba(255,255,255,.05);border:1px solid var(--border);
  color:var(--text);font-size:.85rem;font-weight:600;cursor:pointer;
  text-align:center;transition:.2s;text-decoration:none;
}
.surah-start-btn:hover{background:rgba(245,166,35,.1);border-color:rgba(245,166,35,.3);color:var(--accent)}

/* WORD-BY-WORD SECTION */
.wbw-container{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;margin-bottom:32px}
.wbw-verse{
  font-family:'Amiri',serif;font-size:clamp(1.4rem,3vw,2rem);direction:rtl;text-align:right;
  line-height:2.2;color:var(--text);background:rgba(255,255,255,.03);
  border-radius:12px;padding:20px 24px;margin-bottom:28px;
  border:1px solid rgba(212,168,67,.15);
}
.wbw-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}
.wbw-card{
  background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;
  padding:16px;text-align:center;transition:.2s;cursor:pointer;
}
.wbw-card:hover{border-color:var(--gold);background:rgba(212,168,67,.05)}
.wbw-arabic{font-family:'Amiri',serif;font-size:1.6rem;color:var(--gold);margin-bottom:6px;display:block}
.wbw-roman{font-size:.78rem;color:var(--muted);margin-bottom:4px}
.wbw-meaning{font-size:.88rem;font-weight:600;margin-bottom:6px}
.wbw-root{font-size:.72rem;color:var(--accent2);background:rgba(124,92,191,.1);
  padding:2px 8px;border-radius:50px;display:inline-block}
.wbw-note{font-size:.75rem;color:var(--muted);margin-top:6px;line-height:1.4}

/* TOP WORDS TABLE */
.words-table-wrap{overflow-x:auto}
.words-table{width:100%;border-collapse:collapse}
.words-table th{
  text-align:left;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  color:var(--muted);padding:12px 16px;border-bottom:1px solid var(--border);white-space:nowrap
}
.words-table td{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.04);font-size:.88rem;vertical-align:middle}
.words-table tr:hover td{background:rgba(255,255,255,.02)}
.word-arabic-cell{font-family:'Amiri',serif;font-size:1.5rem;color:var(--gold);direction:rtl}
.word-freq{display:inline-flex;align-items:center;gap:6px}
.freq-bar{height:6px;border-radius:3px;background:linear-gradient(90deg,var(--accent3),var(--accent));min-width:4px}
.word-root-badge{font-size:.72rem;background:rgba(124,92,191,.15);color:var(--accent2);
  padding:3px 8px;border-radius:50px;font-family:'Amiri',serif;font-size:.85rem}
.word-example-ar{font-family:'Amiri',serif;font-size:.95rem;direction:rtl;color:var(--muted)}
.learn-btn{
  padding:5px 14px;border-radius:50px;background:rgba(245,166,35,.1);
  border:1px solid rgba(245,166,35,.3);color:var(--accent);font-size:.75rem;font-weight:600;
  cursor:pointer;transition:.2s;white-space:nowrap
}
.learn-btn:hover{background:rgba(245,166,35,.2)}

/* FLASHCARD QUIZ */
.quiz-section{margin-bottom:48px}
.flashcard-wrap{perspective:1000px;width:100%;max-width:480px;margin:0 auto;height:240px}
.flashcard{
  width:100%;height:100%;position:relative;transform-style:preserve-3d;
  transition:transform .5s;cursor:pointer;
}
.flashcard.flipped{transform:rotateY(180deg)}
.card-face{
  position:absolute;inset:0;border-radius:var(--radius-lg);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  backface-visibility:hidden;
  background:linear-gradient(135deg,var(--bg-card2),var(--bg-card));
  border:1px solid var(--border);padding:32px;
}
.card-back{transform:rotateY(180deg);background:linear-gradient(135deg,#1a1535,#0f1a2e);border-color:rgba(212,168,67,.3)}
.card-face-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:12px}
.card-face-arabic{font-family:'Amiri',serif;font-size:3rem;color:var(--gold);margin-bottom:8px}
.card-face-hint{font-size:.82rem;color:var(--muted)}
.card-back-meaning{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;margin-bottom:8px;color:var(--accent)}
.card-back-roman{font-size:.9rem;color:var(--muted);margin-bottom:4px}
.card-back-example{font-family:'Amiri',serif;font-size:1rem;direction:rtl;color:var(--text-muted);margin-top:8px;text-align:center}
.quiz-controls{display:flex;align-items:center;justify-content:center;gap:16px;margin-top:24px;flex-wrap:wrap}
.quiz-btn{
  padding:10px 24px;border-radius:50px;border:1px solid var(--border);
  background:rgba(255,255,255,.05);color:var(--text);font-size:.88rem;font-weight:600;
  cursor:pointer;transition:.2s;
}
.quiz-btn:hover{border-color:var(--accent);color:var(--accent)}
.quiz-btn.correct{background:rgba(62,207,142,.15);border-color:var(--accent3);color:var(--accent3)}
.quiz-btn.wrong{background:rgba(232,93,93,.1);border-color:#e85d5d;color:#e85d5d}
.quiz-counter{font-size:.82rem;color:var(--muted)}

/* RESPONSIVE */
@media(max-width:768px){
  .page{padding:24px 16px 60px}
  .quran-hero{padding:28px 20px}
  .progress-card{gap:16px}
  .prog-divider{display:none}
  .wbw-grid{grid-template-columns:repeat(2,1fr)}
  nav{padding:0 16px}
  .nav-links{display:none}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-icon">ف</div>
    Fase<span>eh</span>
  </a>
  <div class="nav-links">
    <a href="dashboard.php" class="nav-link">Dashboard</a>
    <a href="academy.php"   class="nav-link">Academy</a>
    <a href="quran.php"     class="nav-link active">Quran Track</a>
    <a href="play.php"      class="nav-link">Play</a>
    <a href="rankings.php"  class="nav-link">Rankings</a>
  </div>
  <div class="nav-user">
    Welcome, <strong><?= $username ?></strong>
  </div>
</nav>

<div class="page">

  <!-- HERO BANNER -->
  <div class="quran-hero">
    <div class="quran-hero-bismillah">بِسْمِ ٱللَّٰهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</div>
    <h1>Quranic Arabic Track</h1>
    <p>Understand the words of Allah directly. Learn word-by-word breakdowns, master the 500 most frequent Quranic words, and deepen your connection with the Quran.</p>
    <div class="hero-badges">
      <div class="hero-badge gold">📖 Word-by-Word Breakdown</div>
      <div class="hero-badge gold">🔊 Audio for every word</div>
      <div class="hero-badge">✨ Spaced repetition built-in</div>
      <div class="hero-badge">🏆 Earn Quranic rank titles</div>
      <div class="hero-badge">🤲 8 Surahs available now</div>
    </div>
  </div>

  <!-- PROGRESS CARD -->
  <div class="progress-card">
    <div class="prog-stat">
      <strong id="words-learned">0</strong>
      <span>Words Learned</span>
    </div>
    <div class="prog-divider"></div>
    <div class="prog-stat">
      <strong>0</strong>
      <span>Surahs Started</span>
    </div>
    <div class="prog-divider"></div>
    <div class="prog-stat">
      <strong>0</strong>
      <span>Lessons Done</span>
    </div>
    <div class="prog-divider"></div>
    <div class="prog-bar-wrap">
      <div class="prog-bar-label">
        <span>Quranic Rank: <strong style="color:var(--text)">مبتدئ</strong></span>
        <span>0 / 50 words</span>
      </div>
      <div class="prog-bar"><div class="prog-bar-fill" style="width:0%" id="rank-bar"></div></div>
      <div class="prog-next">Next rank: <strong>قارئ</strong> (Reciter) — learn 50 Quranic words</div>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('surahs',this)">📖 Surahs</button>
    <button class="tab-btn" onclick="switchTab('wordbyword',this)">🔍 Word-by-Word</button>
    <button class="tab-btn" onclick="switchTab('topwords',this)">📊 Top 500 Words</button>
    <button class="tab-btn" onclick="switchTab('flashcards',this)">🃏 Flashcards</button>
  </div>

  <!-- TAB: SURAHS -->
  <div class="tab-content active" id="tab-surahs">
    <div class="section-header">
      <div class="section-label">Surah Library</div>
      <h2 class="section-title">Choose a Surah to study</h2>
      <p class="section-sub">Each surah is broken down word-by-word with grammar notes, audio, and quizzes.</p>
    </div>
    <div class="surah-grid">
      <?php foreach ($surahs as $s):
        $color = $s['color'];
      ?>
      <div class="surah-card" style="--surah-color:<?= $color ?>">
        <div class="surah-header">
          <div class="surah-arabic"><?= $s['arabic'] ?></div>
          <div class="surah-num"><?= $s['id'] ?></div>
        </div>
        <div class="surah-name"><?= $s['icon'] ?> <?= $s['name'] ?></div>
        <div class="surah-meaning"><?= $s['meaning'] ?></div>
        <div class="surah-desc"><?= $s['desc'] ?></div>
        <div class="surah-meta">
          <span class="surah-pill"><?= $s['verses'] ?> verses</span>
          <span class="surah-pill"><?= $s['lessons'] ?> lessons</span>
          <span class="surah-pill level-<?= $s['level'] ?>"><?= $s['level'] ?></span>
        </div>
        <div class="surah-progress">
          <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--muted);margin-bottom:4px">
            <span>Progress</span><span>0 / <?= $s['lessons'] ?> lessons</span>
          </div>
          <div class="surah-prog-bar"><div class="surah-prog-fill" style="width:0%"></div></div>
        </div>
        <a href="quran_lesson.php?surah=<?= $s['id'] ?>" class="surah-start-btn">
          Start Surah →
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- TAB: WORD BY WORD -->
  <div class="tab-content" id="tab-wordbyword">
    <div class="section-header">
      <div class="section-label">Deep Study</div>
      <h2 class="section-title">Ayat al-Kursi — Word by Word</h2>
      <p class="section-sub">The greatest verse in the Quran (2:255). Learn every single word in depth.</p>
    </div>
    <div class="wbw-container">
      <div class="wbw-verse" title="Ayat al-Kursi — Al-Baqarah 2:255">
        ٱللَّهُ لَآ إِلَٰهَ إِلَّا هُوَ ٱلْحَيُّ ٱلْقَيُّومُ ۚ لَا تَأْخُذُهُۥ سِنَةٌ وَلَا نَوْمٌ
      </div>
      <div class="section-label" style="margin-bottom:16px">Click any word card to expand its grammar note</div>
      <div class="wbw-grid">
        <?php foreach ($ayat_kursi as $w): ?>
        <div class="wbw-card" onclick="toggleNote(this)">
          <span class="wbw-arabic"><?= $w[0] ?></span>
          <div class="wbw-roman"><?= $w[1] ?></div>
          <div class="wbw-meaning"><?= $w[2] ?></div>
          <?php if ($w[3]): ?>
          <div class="wbw-note" style="display:none"><?= $w[3] ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="background:rgba(212,168,67,.07);border:1px solid rgba(212,168,67,.2);border-radius:var(--radius);padding:20px 24px;display:flex;gap:16px;align-items:flex-start">
      <span style="font-size:1.5rem">💡</span>
      <div>
        <strong style="color:var(--gold)">Grammar insight:</strong>
        <p style="font-size:.88rem;color:var(--muted);margin-top:4px;line-height:1.7">
          Notice how لَا appears twice — the first instance negates existence (لَا إِلَٰهَ = no deity), while the second negates the action of seizing (لَا تَأْخُذُهُ). This distinction between negating nouns and verbs is a foundational pattern throughout the Quran.
        </p>
      </div>
    </div>
  </div>

  <!-- TAB: TOP WORDS -->
  <div class="tab-content" id="tab-topwords">
    <div class="section-header">
      <div class="section-label">Vocabulary Power-Up</div>
      <h2 class="section-title">Top 500 Quranic Words</h2>
      <p class="section-sub">Learning these words gives you direct understanding of over 70% of the Quran. Start here.</p>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:24px">
      <div style="display:flex;gap:32px;flex-wrap:wrap">
        <div><strong style="font-family:'Syne',sans-serif;font-size:1.8rem;color:var(--accent)">500</strong><br/><span style="font-size:.78rem;color:var(--muted);text-transform:uppercase">Total words</span></div>
        <div><strong style="font-family:'Syne',sans-serif;font-size:1.8rem;color:var(--accent3)">70%+</strong><br/><span style="font-size:.78rem;color:var(--muted);text-transform:uppercase">Quran coverage</span></div>
        <div><strong style="font-family:'Syne',sans-serif;font-size:1.8rem;color:var(--gold)">77,430</strong><br/><span style="font-size:.78rem;color:var(--muted);text-transform:uppercase">Total Quran words</span></div>
        <div><strong style="font-family:'Syne',sans-serif;font-size:1.8rem;color:var(--accent2)">6,236</strong><br/><span style="font-size:.78rem;color:var(--muted);text-transform:uppercase">Verses</span></div>
      </div>
    </div>
    <div class="words-table-wrap">
      <table class="words-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Arabic Word</th>
            <th>Transliteration</th>
            <th>Meaning</th>
            <th>Frequency</th>
            <th>Root</th>
            <th>Example</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($top_words as $i => $w): ?>
          <tr>
            <td style="color:var(--muted);font-size:.8rem"><?= $i+1 ?></td>
            <td class="word-arabic-cell"><?= $w[0] ?></td>
            <td style="font-style:italic;color:var(--muted)"><?= $w[1] ?></td>
            <td style="font-weight:500"><?= $w[2] ?></td>
            <td>
              <div class="word-freq">
                <div class="freq-bar" style="width:<?= min(80, round($w[3]/2699*80)) ?>px"></div>
                <span style="font-size:.8rem;color:var(--muted)"><?= number_format($w[3]) ?>×</span>
              </div>
            </td>
            <td>
              <?php if ($w[4] !== '—'): ?>
              <span class="word-root-badge"><?= $w[4] ?></span>
              <?php else: echo '<span style="color:var(--muted)">—</span>'; endif; ?>
            </td>
            <td class="word-example-ar"><?= $w[5] ?></td>
            <td><button class="learn-btn" onclick="openFlashcard('<?= htmlspecialchars($w[0]) ?>','<?= htmlspecialchars($w[2]) ?>')">Practice</button></td>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:20px;color:var(--muted);font-size:.85rem">
              Showing top 20 of 500 words · <a href="quran_words.php" style="color:var(--accent)">View all 500 words →</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TAB: FLASHCARDS -->
  <div class="tab-content" id="tab-flashcards">
    <div class="section-header">
      <div class="section-label">Memory Training</div>
      <h2 class="section-title">Quranic Vocabulary Flashcards</h2>
      <p class="section-sub">Tap the card to reveal meaning. Mark correct or wrong to track your mastery.</p>
    </div>
    <div class="quiz-section">
      <div style="text-align:center;margin-bottom:24px">
        <span class="quiz-counter" id="card-counter">Card 1 of <?= count($top_words) ?></span>
      </div>
      <div class="flashcard-wrap">
        <div class="flashcard" id="flashcard" onclick="flipCard()">
          <div class="card-face">
            <div class="card-face-label">Arabic Word — tap to reveal</div>
            <div class="card-face-arabic" id="card-arabic"><?= $top_words[0][0] ?></div>
            <div class="card-face-hint" id="card-roman"><?= $top_words[0][1] ?></div>
          </div>
          <div class="card-face card-back">
            <div class="card-face-label">Meaning</div>
            <div class="card-back-meaning" id="card-meaning"><?= $top_words[0][2] ?></div>
            <div class="card-back-roman">Frequency in Quran: <strong style="color:var(--accent)"><?= number_format($top_words[0][3]) ?>×</strong></div>
            <div class="card-back-example" id="card-example"><?= $top_words[0][5] ?></div>
          </div>
        </div>
      </div>
      <div class="quiz-controls">
        <button class="quiz-btn wrong" onclick="markCard(false)">✗ Didn't Know</button>
        <button class="quiz-btn" onclick="flipCard()">↻ Flip</button>
        <button class="quiz-btn correct" onclick="markCard(true)">✓ Got It</button>
      </div>
      <div style="text-align:center;margin-top:16px">
        <div style="display:flex;gap:24px;justify-content:center;font-size:.82rem;color:var(--muted)">
          <span id="fc-correct" style="color:var(--accent3)">✓ 0 correct</span>
          <span id="fc-wrong"   style="color:#e85d5d">✗ 0 wrong</span>
        </div>
      </div>
    </div>
  </div>

</div><!-- /page -->

<script>
// ── Tab switching ────────────────────────────────────────────────
function switchTab(id, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
}

// ── Word note toggle ─────────────────────────────────────────────
function toggleNote(card) {
  const note = card.querySelector('.wbw-note');
  if (!note) return;
  note.style.display = note.style.display === 'none' ? 'block' : 'none';
}

// ── Flashcard logic ──────────────────────────────────────────────
const words = <?= json_encode(array_values($top_words)) ?>;
let cardIndex = 0, correct = 0, wrong = 0;

function flipCard() {
  document.getElementById('flashcard').classList.toggle('flipped');
}

function loadCard(i) {
  const w = words[i];
  document.getElementById('flashcard').classList.remove('flipped');
  document.getElementById('card-arabic').textContent  = w[0];
  document.getElementById('card-roman').textContent   = w[1];
  document.getElementById('card-meaning').textContent = w[2];
  document.getElementById('card-example').textContent = w[5];
  document.getElementById('card-counter').textContent = 'Card ' + (i+1) + ' of ' + words.length;
}

function markCard(isCorrect) {
  if (isCorrect) correct++; else wrong++;
  document.getElementById('fc-correct').textContent = '✓ ' + correct + ' correct';
  document.getElementById('fc-wrong').textContent   = '✗ ' + wrong + ' wrong';
  cardIndex = (cardIndex + 1) % words.length;
  setTimeout(() => loadCard(cardIndex), 300);
}

function openFlashcard(arabic, meaning) {
  switchTab('flashcards', document.querySelectorAll('.tab-btn')[3]);
  // find the word and jump to it
  const idx = words.findIndex(w => w[0] === arabic);
  if (idx !== -1) { cardIndex = idx; loadCard(idx); }
}

// ── Animate progress bar on load ────────────────────────────────
window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.prog-bar-fill').forEach(b => {
      b.style.width = b.getAttribute('data-pct') || b.style.width;
    });
  }, 400);
});
</script>
</body>
</html>
