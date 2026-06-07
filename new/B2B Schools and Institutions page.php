<?php
// b2b.php — Faseeh For Schools & Institutions
// Public page — no login required

$page_title = "Faseeh for Schools & Institutions — Arabic Learning Platform";
$page_desc  = "Bring world-class Arabic learning to your school, mosque, or university. Faseeh offers institutional licensing, teacher dashboards, and custom learning paths.";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= $page_title ?></title>
<meta name="description" content="<?= $page_desc ?>"/>
<meta property="og:title"       content="<?= $page_title ?>"/>
<meta property="og:description" content="<?= $page_desc ?>"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500&family=Amiri:ital,wght@0,400;0,700&display=swap" rel="stylesheet"/>
<style>
:root{
  --bg:#0e0c1e; --bg-card:#161430; --bg-card2:#1c1a38;
  --border:rgba(255,255,255,.07); --accent:#f5a623; --accent2:#7c5cbf;
  --accent3:#3ecf8e; --gold:#d4a843; --text:#f0eeff; --muted:#8b87b0;
  --radius:16px; --radius-lg:24px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;overflow-x:hidden}

/* ── NAV ── */
nav{position:fixed;top:0;left:0;right:0;z-index:100;
  background:rgba(14,12,30,.9);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 40px;height:68px;
  display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px;font-family:'Syne',sans-serif;
  font-weight:800;font-size:1.25rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,var(--accent),#e8862a);
  display:flex;align-items:center;justify-content:center;font-size:1rem}
.nav-logo span{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:16px}
.nav-link{color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:500;transition:.2s}
.nav-link:hover{color:var(--text)}
.btn-nav{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;
  border:none;padding:9px 20px;border-radius:50px;font-size:.875rem;font-weight:700;
  cursor:pointer;text-decoration:none;transition:.15s}
.btn-nav:hover{opacity:.9}

/* ── LAYOUT ── */
.container{max-width:1160px;margin:0 auto;padding:0 24px;position:relative;z-index:1}
section{position:relative;z-index:1}

/* ── HERO ── */
.b2b-hero{
  min-height:100vh;display:flex;align-items:center;padding:120px 0 80px;
  background:
    radial-gradient(ellipse 70% 50% at 20% 40%,rgba(124,92,191,.15),transparent),
    radial-gradient(ellipse 50% 60% at 80% 60%,rgba(245,166,35,.08),transparent);
}
.b2b-hero-inner{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
.hero-badge{display:inline-flex;align-items:center;gap:8px;
  background:rgba(62,207,142,.1);border:1px solid rgba(62,207,142,.3);
  color:var(--accent3);border-radius:50px;padding:6px 16px;
  font-size:.78rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:20px}
.b2b-hero h1{font-family:'Syne',sans-serif;font-weight:800;
  font-size:clamp(2rem,3.5vw,3.2rem);line-height:1.15;margin-bottom:20px}
.b2b-hero h1 em{font-style:normal;color:var(--accent)}
.b2b-hero p{color:var(--muted);font-size:1.05rem;line-height:1.7;max-width:500px;margin-bottom:36px}

.cta-group{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:48px}
.btn-primary-lg{
  background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;
  border:none;padding:15px 32px;border-radius:50px;font-size:1rem;font-weight:700;
  cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;
  transition:.2s;box-shadow:0 8px 32px rgba(245,166,35,.3)}
.btn-primary-lg:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(245,166,35,.4)}
.btn-outline{background:none;border:1px solid var(--border);color:var(--text);
  padding:14px 28px;border-radius:50px;font-size:.95rem;font-weight:500;
  cursor:pointer;text-decoration:none;transition:.2s}
.btn-outline:hover{border-color:var(--accent);color:var(--accent)}

.trust-logos{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.trust-label{font-size:.78rem;color:var(--muted);margin-right:4px}
.trust-pill{background:rgba(255,255,255,.05);border:1px solid var(--border);
  border-radius:50px;padding:6px 14px;font-size:.78rem;color:var(--muted)}

/* Hero visual — stat cards */
.hero-stats-visual{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.hstat{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:22px 20px;transition:.2s;
}
.hstat:hover{border-color:rgba(255,255,255,.12)}
.hstat-num{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;
  color:var(--accent);display:block;margin-bottom:4px}
.hstat-label{font-size:.78rem;color:var(--muted);line-height:1.4}
.hstat.wide{grid-column:1/-1;background:linear-gradient(135deg,#1a1535,#0f1a2e);
  border-color:rgba(212,168,67,.2)}
.hstat.wide .hstat-num{color:var(--gold);font-size:1.5rem}

/* ── SECTION HEADERS ── */
.section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;color:var(--accent);margin-bottom:10px}
.section-title{font-family:'Syne',sans-serif;font-weight:800;
  font-size:clamp(1.7rem,2.8vw,2.4rem);line-height:1.2;margin-bottom:14px}
.section-sub{color:var(--muted);font-size:1rem;line-height:1.7;max-width:580px}
.text-center{text-align:center}
.text-center .section-sub{margin:0 auto}

/* ── WHO IS IT FOR ── */
.who-section{padding:100px 0}
.who-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:48px}
.who-card{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:32px;transition:.25s;position:relative;overflow:hidden;
}
.who-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:var(--who-color,var(--accent));opacity:.7}
.who-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.4)}
.who-icon{font-size:2.4rem;margin-bottom:16px;display:block}
.who-card h3{font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem;margin-bottom:10px}
.who-card p{font-size:.88rem;color:var(--muted);line-height:1.65}
.who-features{list-style:none;margin-top:14px}
.who-features li{font-size:.83rem;color:var(--muted);padding:5px 0;
  display:flex;align-items:center;gap:8px;border-bottom:1px solid rgba(255,255,255,.04)}
.who-features li:last-child{border:none}
.who-features li::before{content:'✓';color:var(--accent3);font-weight:700;flex-shrink:0}

/* ── FEATURES ── */
.features-section{padding:80px 0;
  background:linear-gradient(180deg,transparent,rgba(124,92,191,.05),transparent)}
.features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:48px}
.feat{
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);
  padding:28px 24px;display:flex;gap:20px;align-items:flex-start;transition:.2s;
}
.feat:hover{border-color:rgba(255,255,255,.12)}
.feat-icon{font-size:1.8rem;flex-shrink:0;width:48px;text-align:center}
.feat h3{font-family:'Syne',sans-serif;font-weight:700;font-size:.98rem;margin-bottom:6px}
.feat p{font-size:.85rem;color:var(--muted);line-height:1.6}

/* ── PRICING ── */
.pricing-section{padding:100px 0}
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;
  max-width:960px;margin:48px auto 0}
.price-card{
  background:var(--bg-card);border:1px solid var(--border);
  border-radius:var(--radius-lg);padding:32px 28px;transition:.25s;position:relative;
}
.price-card.featured{
  border-color:var(--accent);
  background:linear-gradient(135deg,#1c1a38,#221f42);
  box-shadow:0 0 0 1px rgba(245,166,35,.2),0 20px 60px rgba(0,0,0,.5);
  transform:scale(1.03);
}
.price-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);
  background:var(--accent);color:#1a0f00;font-size:.7rem;font-weight:800;
  padding:4px 14px;border-radius:50px;letter-spacing:.05em;text-transform:uppercase}
.price-plan{font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:var(--muted);margin-bottom:10px}
.price-amount{font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;margin-bottom:4px}
.price-amount sup{font-size:1rem;vertical-align:top;margin-top:6px;font-weight:400}
.price-amount small{font-size:.9rem;font-weight:400;color:var(--muted)}
.price-desc{font-size:.82rem;color:var(--muted);margin-bottom:24px}
.price-features{list-style:none;margin-bottom:24px}
.price-features li{padding:7px 0;font-size:.85rem;color:var(--muted);
  display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(255,255,255,.04)}
.price-features li:last-child{border:none}
.price-features li::before{content:'✓';color:var(--accent3);font-weight:700;flex-shrink:0;margin-top:2px}
.price-cta{display:block;width:100%;padding:13px;border-radius:12px;
  text-align:center;font-weight:700;font-size:.9rem;cursor:pointer;transition:.2s;text-decoration:none;border:none}
.price-cta.primary{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00}
.price-cta.secondary{background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text)}
.price-cta:hover{opacity:.9}
.price-note{text-align:center;font-size:.8rem;color:var(--muted);margin-top:20px}

/* ── TESTIMONIALS ── */
.testi-section{padding:80px 0}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:48px}
.testi{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:28px}
.testi-stars{color:var(--accent);font-size:.9rem;margin-bottom:12px}
.testi-quote{font-size:.9rem;color:var(--muted);line-height:1.7;margin-bottom:20px;font-style:italic}
.testi-author{display:flex;align-items:center;gap:12px}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.08);
  display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.testi-name{font-weight:600;font-size:.88rem}
.testi-meta{font-size:.75rem;color:var(--muted)}

/* ── CONTACT FORM ── */
.contact-section{padding:100px 0}
.contact-inner{
  display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;
  background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);
  padding:56px 48px;
}
.contact-info h2{font-family:'Syne',sans-serif;font-weight:800;
  font-size:clamp(1.5rem,2.5vw,2rem);margin-bottom:16px}
.contact-info p{color:var(--muted);font-size:.95rem;line-height:1.7;margin-bottom:28px}
.contact-detail{display:flex;align-items:center;gap:12px;margin-bottom:14px;
  font-size:.88rem;color:var(--muted)}
.contact-detail strong{color:var(--text)}
.arabic-closing{font-family:'Amiri',serif;font-size:1.6rem;color:var(--gold);margin-top:24px}

.contact-form{display:flex;flex-direction:column;gap:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-label{font-size:.78rem;font-weight:600;color:var(--muted);
  letter-spacing:.05em;text-transform:uppercase}
.form-input,.form-select,.form-textarea{
  background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;
  padding:12px 16px;color:var(--text);font-family:'DM Sans',sans-serif;
  font-size:.92rem;transition:border-color .2s;width:100%;
}
.form-input:focus,.form-select:focus,.form-textarea:focus{
  outline:none;border-color:rgba(245,166,35,.5)}
.form-textarea{resize:vertical;min-height:100px;line-height:1.5}
.form-select option{background:#1c1a38}
.form-submit{
  background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;
  border:none;padding:14px 32px;border-radius:50px;font-size:.95rem;font-weight:700;
  cursor:pointer;transition:.2s;box-shadow:0 8px 28px rgba(245,166,35,.3)}
.form-submit:hover{transform:translateY(-1px);box-shadow:0 12px 36px rgba(245,166,35,.4)}
.form-note{font-size:.78rem;color:var(--muted);margin-top:4px}

/* ── SUCCESS MSG ── */
#form-success{
  display:none;text-align:center;padding:32px;
  background:rgba(62,207,142,.08);border:1px solid rgba(62,207,142,.2);
  border-radius:var(--radius);
}

/* ── FAQ ── */
.faq-section{padding:80px 0}
.faq-list{max-width:720px;margin:40px auto 0;display:flex;flex-direction:column;gap:12px}
.faq-item{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.faq-q{width:100%;background:none;border:none;color:var(--text);text-align:left;
  padding:20px 24px;font-size:.95rem;font-weight:600;cursor:pointer;
  display:flex;justify-content:space-between;align-items:center;gap:16px;transition:.2s}
.faq-q:hover{background:rgba(255,255,255,.02)}
.faq-chevron{transition:transform .3s;flex-shrink:0;color:var(--muted)}
.faq-item.open .faq-chevron{transform:rotate(180deg)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .3s ease;font-size:.88rem;
  color:var(--muted);line-height:1.7}
.faq-a-inner{padding:0 24px 20px}
.faq-item.open .faq-a{max-height:300px}

/* ── FOOTER ── */
footer{border-top:1px solid var(--border);padding:40px 0;text-align:center}
footer p{color:var(--muted);font-size:.82rem}
footer a{color:var(--accent);text-decoration:none}

/* ── REVEAL ── */
.reveal{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .who-grid{grid-template-columns:1fr 1fr}
  .features-grid{grid-template-columns:1fr}
  .pricing-grid{grid-template-columns:1fr}
  .price-card.featured{transform:scale(1)}
  .testi-grid{grid-template-columns:1fr}
}
@media(max-width:768px){
  nav{padding:0 16px}
  .b2b-hero-inner{grid-template-columns:1fr}
  .hero-stats-visual{display:none}
  .who-grid{grid-template-columns:1fr}
  .contact-inner{grid-template-columns:1fr;padding:32px 24px;gap:36px}
  .form-row{grid-template-columns:1fr}
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
  <div class="nav-right">
    <a href="landing.php" class="nav-link">For Learners</a>
    <a href="#pricing"    class="nav-link">Pricing</a>
    <a href="#contact"    class="nav-link">Contact</a>
    <a href="register.php" class="btn-nav">Get Started</a>
  </div>
</nav>

<!-- ============ HERO ============ -->
<section class="b2b-hero">
  <div class="container">
    <div class="b2b-hero-inner">
      <div>
        <div class="hero-badge">🏫 For Schools & Institutions</div>
        <h1>Bring world-class Arabic learning to your <em>institution</em></h1>
        <p>
          Faseeh for Schools gives teachers, administrators, and students a complete Arabic
          learning ecosystem — with real-time progress tracking, AI-powered tools, Quranic
          Arabic, and engaging games — all under your control.
        </p>
        <div class="cta-group">
          <a href="#contact" class="btn-primary-lg">Request a Demo →</a>
          <a href="#pricing" class="btn-outline">View Pricing</a>
        </div>
        <div class="trust-logos">
          <span class="trust-label">Trusted by:</span>
          <span class="trust-pill">🏫 Islamic Schools</span>
          <span class="trust-pill">🕌 Mosques & Madrasas</span>
          <span class="trust-pill">🎓 Universities</span>
          <span class="trust-pill">🌍 Language Centres</span>
        </div>
      </div>
      <div class="hero-stats-visual">
        <div class="hstat">
          <span class="hstat-num">10+</span>
          <span class="hstat-label">Interactive learning modules per student</span>
        </div>
        <div class="hstat">
          <span class="hstat-num">181+</span>
          <span class="hstat-label">Vocabulary words across 3 difficulty levels</span>
        </div>
        <div class="hstat">
          <span class="hstat-num">98%</span>
          <span class="hstat-label">Student engagement rate vs traditional textbooks</span>
        </div>
        <div class="hstat">
          <span class="hstat-num">2×</span>
          <span class="hstat-label">Faster vocabulary retention vs rote learning</span>
        </div>
        <div class="hstat wide">
          <span class="hstat-num">بِسْمِ ٱللَّٰهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</span>
          <span class="hstat-label">Dedicated Quranic Arabic track — understand the words of Allah directly</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ WHO IS IT FOR ============ -->
<section class="who-section">
  <div class="container">
    <div class="text-center reveal">
      <div class="section-label">Who We Serve</div>
      <h2 class="section-title">Built for every Arabic learning institution</h2>
      <p class="section-sub">Whether you have 10 students or 10,000, Faseeh scales to fit your institution perfectly.</p>
    </div>
    <div class="who-grid">
      <?php
      $who = [
        [
          'icon'=>'🕌','color'=>'#d4a843','title'=>'Mosques & Islamic Centres',
          'desc'=>'Give your congregation and students the tools to connect deeply with the Quran and Arabic language.',
          'features'=>['Dedicated Quranic Arabic track','Word-by-word Quran breakdown','Adult-friendly learning pace','Group leaderboards','Imam dashboard & reports'],
        ],
        [
          'icon'=>'🏫','color'=>'#f5a623','title'=>'Islamic Schools (K-12)',
          'desc'=>'Complement your Arabic curriculum with an engaging platform that students love — and teachers can actually measure.',
          'features'=>['Teacher dashboard & class management','Assign lessons & homework','Student progress reports','Parent visibility portal','Custom school leaderboard'],
        ],
        [
          'icon'=>'🎓','color'=>'#7c5cbf','title'=>'Universities & Language Centres',
          'desc'=>'Supplement formal Arabic courses with AI-powered practice, advanced grammar tools, and CEFR-aligned certificates.',
          'features'=>['CEFR A1–C2 alignment','AI Essay Grader for assignments','Verifiable digital certificates','Academic performance analytics','White-label option available'],
        ],
        [
          'icon'=>'💼','color'=>'#3ecf8e','title'=>'Corporations & Embassies',
          'desc'=>'Prepare your staff for Arabic-speaking markets and diplomatic environments with professional MSA training.',
          'features'=>['Modern Standard Arabic focus','Business vocabulary modules','AI conversation partner','Progress reports for HR','Bulk licence management'],
        ],
        [
          'icon'=>'🌍','color'=>'#5ca8e8','title'=>'NGOs & Aid Organisations',
          'desc'=>'Equip your field workers and volunteers with practical Arabic communication skills quickly and affordably.',
          'features'=>['Dialect options (Egyptian, Gulf)','Fast onboarding','Offline capability (PWA)','Affordable NGO pricing','Mobile-first experience'],
        ],
        [
          'icon'=>'📺','color'=>'#e86db5','title'=>'Media & Content Creators',
          'desc'=>'Licence Faseeh content or integrate our API into your Arabic learning product or media platform.',
          'features'=>['White-label licensing','API access','Custom content packs','Co-branding options','Revenue share models'],
        ],
      ];
      foreach ($who as $w): ?>
      <div class="who-card reveal" style="--who-color:<?= $w['color'] ?>">
        <span class="who-icon"><?= $w['icon'] ?></span>
        <h3><?= $w['title'] ?></h3>
        <p><?= $w['desc'] ?></p>
        <ul class="who-features">
          <?php foreach ($w['features'] as $f): ?>
          <li><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PLATFORM FEATURES ============ -->
<section class="features-section">
  <div class="container">
    <div class="text-center reveal">
      <div class="section-label">Platform Features</div>
      <h2 class="section-title">Everything your institution needs</h2>
      <p class="section-sub">Faseeh for Schools goes far beyond a learning app — it's a complete Arabic education management system.</p>
    </div>
    <?php
    $features = [
      ['🎛️','Admin Dashboard','Manage all students, classes, and teachers from one central dashboard. Assign content, track progress, and generate reports in seconds.'],
      ['📊','Real-Time Analytics','See exactly how each student is performing — accuracy rates, time spent, words mastered, and areas needing attention.'],
      ['📝','Homework Assignment','Assign specific modules, games, or reading passages as homework. Students complete them in the app; results appear automatically.'],
      ['🤖','AI Essay Grader','Students write Arabic essays; our AI gives instant feedback on grammar, vocabulary, and style — saving teachers hours of marking.'],
      ['📖','Quranic Arabic Track','A dedicated track for Islamic schools and mosques — word-by-word Quran breakdown, frequent word lists, and audio recitation.'],
      ['🎓','Certificates & Transcripts','Issue verified, shareable digital certificates when students pass level assessments. PDF transcripts available for records.'],
      ['🔔','Automated Reminders','Streak reminders, assignment deadlines, and progress nudges keep students engaged without teacher intervention.'],
      ['👨‍👩‍👧','Parent Portal','Parents can see their child\'s daily progress, streak, accuracy, and upcoming assignments from a simple, clean interface.'],
      ['🌐','Multi-Language Interface','The platform UI supports English, Arabic, Malay, Urdu, and French — perfect for international schools.'],
      ['📱','Works on Any Device','Fully responsive web app + PWA install. Students can learn on phones, tablets, or computers with no app download required.'],
      ['🔒','GDPR & Privacy Safe','Student data is stored securely. GDPR-compliant. No advertising. No third-party data sharing. Child-safe by design.'],
      ['🎨','Custom Branding','Add your school\'s logo, colours, and name to the platform. Students see your institution\'s brand, powered by Faseeh.'],
    ];
    ?>
    <div class="features-grid">
      <?php foreach ($features as $f): ?>
      <div class="feat reveal">
        <div class="feat-icon"><?= $f[0] ?></div>
        <div>
          <h3><?= $f[1] ?></h3>
          <p><?= $f[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
<section class="pricing-section" id="pricing">
  <div class="container">
    <div class="text-center reveal">
      <div class="section-label">Institutional Pricing</div>
      <h2 class="section-title">Transparent, flexible pricing</h2>
      <p class="section-sub">All plans include onboarding support, teacher training, and dedicated account management.</p>
    </div>
    <div class="pricing-grid">
      <?php
      $plans = [
        [
          'name'=>'Starter','price'=>'49','period'=>'/ month','featured'=>false,
          'desc'=>'Perfect for small classes, community groups, or mosques getting started.',
          'features'=>[
            'Up to 30 students','1 teacher account','All learning modules',
            'Basic progress reports','Email support','Quranic Arabic track',
          ],
          'cta'=>'Start Free Trial','link'=>'#contact',
        ],
        [
          'name'=>'School','price'=>'149','period'=>'/ month','featured'=>true,
          'desc'=>'The complete solution for Islamic schools and language centres.',
          'features'=>[
            'Up to 200 students','10 teacher accounts','Admin dashboard',
            'Parent portal','Homework assignment','AI Essay Grader',
            'Custom school branding','Advanced analytics','Priority support',
            'Certificates & transcripts',
          ],
          'cta'=>'Request Demo','link'=>'#contact',
        ],
        [
          'name'=>'Enterprise','price'=>'Custom','period'=>'','featured'=>false,
          'desc'=>'For universities, large school networks, and organisations with 200+ users.',
          'features'=>[
            'Unlimited students','Unlimited teachers','White-label option',
            'API access','SSO integration','Custom content packs',
            'SLA guarantee','Dedicated account manager','Custom dialects',
            'On-site training available',
          ],
          'cta'=>'Contact Sales','link'=>'#contact',
        ],
      ];
      foreach ($plans as $pl): ?>
      <div class="price-card <?= $pl['featured']?'featured':'' ?> reveal">
        <?php if ($pl['featured']): ?><div class="price-badge">Most Popular</div><?php endif; ?>
        <div class="price-plan"><?= $pl['name'] ?></div>
        <div class="price-amount">
          <?php if (is_numeric($pl['price'])): ?>
          <sup>$</sup><?= $pl['price'] ?><small><?= $pl['period'] ?></small>
          <?php else: ?>
          <?= $pl['price'] ?>
          <?php endif; ?>
        </div>
        <div class="price-desc"><?= $pl['desc'] ?></div>
        <ul class="price-features">
          <?php foreach ($pl['features'] as $f): ?>
          <li><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= $pl['link'] ?>" class="price-cta <?= $pl['featured']?'primary':'secondary' ?>">
          <?= $pl['cta'] ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="price-note">All prices in USD. Annual billing saves 20%. NGO and mosque discounts available — ask us.</p>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="testi-section">
  <div class="container">
    <div class="text-center reveal">
      <div class="section-label">What Institutions Say</div>
      <h2 class="section-title">Trusted by educators worldwide</h2>
    </div>
    <div class="testi-grid">
      <?php
      $testis = [
        ['⭐⭐⭐⭐⭐',
         '"We deployed Faseeh across our 3 campuses with 180 students. The teacher dashboard saves hours of manual tracking each week, and students\' vocabulary retention has noticeably improved."',
         '🏫','Ustaz Tariq Noor','Head of Arabic Dept — Al-Noor Academy, KL'],
        ['⭐⭐⭐⭐⭐',
         '"The Quranic Arabic track is exactly what our community needed. Our congregants can now understand their daily prayers at a level they never thought possible. The word-by-word breakdown is exceptional."',
         '🕌','Imam Yusuf Al-Rashid','Masjid Al-Rahman, London'],
        ['⭐⭐⭐⭐⭐',
         '"We white-labelled Faseeh for our Arabic language programme. The API integration was smooth, the team was responsive, and our students love it. Highly recommend for any institution."',
         '🎓','Dr. Layla Hassan','Director of Arabic Studies — IIUM, Malaysia'],
      ];
      foreach ($testis as $t): ?>
      <div class="testi reveal">
        <div class="testi-stars"><?= $t[0] ?></div>
        <p class="testi-quote"><?= $t[1] ?></p>
        <div class="testi-author">
          <div class="testi-avatar"><?= $t[2] ?></div>
          <div>
            <div class="testi-name"><?= $t[3] ?></div>
            <div class="testi-meta"><?= $t[4] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CONTACT FORM ============ -->
<section class="contact-section" id="contact">
  <div class="container">
    <div class="contact-inner">
      <div class="contact-info reveal">
        <div class="section-label">Get In Touch</div>
        <h2>Let's bring Arabic learning to your institution</h2>
        <p>
          Fill in the form and our team will get back to you within 24 hours.
          We offer free demos, pilot programmes, and custom onboarding for all institutions.
        </p>
        <div class="contact-detail">
          <span>✉️</span>
          <span>Email: <strong><a href="mailto:schools@faseeh.com" style="color:var(--accent)">schools@faseeh.com</a></strong></span>
        </div>
        <div class="contact-detail">
          <span>📱</span>
          <span>WhatsApp: <strong>+60 11-XXXX XXXX</strong></span>
        </div>
        <div class="contact-detail">
          <span>🕐</span>
          <span>Response time: <strong>Within 24 hours</strong></span>
        </div>
        <div class="contact-detail">
          <span>🌍</span>
          <span>Available in: <strong>EN, AR, MY, UR</strong></span>
        </div>
        <div class="arabic-closing">نسعد بخدمتكم</div>
        <div style="font-size:.78rem;color:var(--muted);margin-top:4px">We are delighted to serve you</div>
      </div>

      <div class="reveal">
        <div id="form-success">
          <div style="font-size:3rem;margin-bottom:12px">🎉</div>
          <h3 style="font-family:'Syne',sans-serif;font-weight:800;margin-bottom:8px">Thank you!</h3>
          <p style="color:var(--muted);font-size:.9rem">We've received your enquiry and will be in touch within 24 hours.</p>
        </div>
        <form class="contact-form" id="contact-form" onsubmit="submitForm(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" class="form-input" placeholder="Ahmad" required/>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" class="form-input" placeholder="Abdullah" required/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" class="form-input" placeholder="principal@school.edu" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Institution Name *</label>
            <input type="text" class="form-input" placeholder="Al-Noor Islamic School" required/>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Institution Type *</label>
              <select class="form-select" required>
                <option value="">Select type...</option>
                <option>Islamic School (K-12)</option>
                <option>Mosque / Islamic Centre</option>
                <option>University / College</option>
                <option>Language Centre</option>
                <option>Corporation / Embassy</option>
                <option>NGO / Non-profit</option>
                <option>Media / Content Platform</option>
                <option>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Number of Students</label>
              <select class="form-select">
                <option value="">Select range...</option>
                <option>1–30</option>
                <option>31–100</option>
                <option>101–300</option>
                <option>301–1,000</option>
                <option>1,000+</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Country / Region</label>
            <input type="text" class="form-input" placeholder="Malaysia, UAE, UK..."/>
          </div>
          <div class="form-group">
            <label class="form-label">Tell us about your needs</label>
            <textarea class="form-textarea" placeholder="Tell us what you're looking for — curriculum goals, student ages, language level, specific features..."></textarea>
          </div>
          <button type="submit" class="form-submit">Send Enquiry →</button>
          <div class="form-note">We respond within 24 hours. No spam, ever.</div>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="faq-section">
  <div class="container">
    <div class="text-center reveal">
      <div class="section-label">FAQ</div>
      <h2 class="section-title">Common questions</h2>
    </div>
    <div class="faq-list">
      <?php
      $faqs = [
        ['Can we try Faseeh before committing?',
         'Yes — we offer a 30-day free pilot for any institution with up to 30 students. No credit card required. Our onboarding team will walk you through setup and help you configure your curriculum.'],
        ['How long does setup take?',
         'Most institutions are up and running within 48 hours. We handle all account creation, and our team provides a dedicated onboarding call to get your teachers and admins comfortable with the platform.'],
        ['Is the content in English or Arabic?',
         'Both. The learning content is Arabic, naturally, but the platform interface is available in English, Arabic, Malay, Urdu, and French. You choose the interface language per institution.'],
        ['Do you have Quranic Arabic for younger children?',
         'Yes. Our Quranic Arabic track is designed to be accessible from age 8 upwards. The word-by-word breakdown is visual and audio-supported, making it suitable for primary-level Islamic schools.'],
        ['Can we add our own content?',
         'On the Enterprise plan, yes. We can integrate custom vocabulary lists, reading passages, and lesson content curated by your teachers. Contact us to discuss.'],
        ['Is student data safe and GDPR compliant?',
         'Absolutely. Faseeh never shows advertisements, never sells data, and stores all data on secure servers. For institutional accounts we sign a data processing agreement (DPA). GDPR, PDPA, and COPPA compliant.'],
        ['What about offline access?',
         'Faseeh is a Progressive Web App (PWA) — students can install it on their phone and recently-visited content is available offline. This is especially useful for students in areas with unreliable internet.'],
      ];
      foreach ($faqs as $i => $faq): ?>
      <div class="faq-item" id="faq-<?= $i ?>">
        <button class="faq-q" onclick="toggleFaq(<?= $i ?>)">
          <?= $faq[0] ?>
          <span class="faq-chevron">▾</span>
        </button>
        <div class="faq-a"><div class="faq-a-inner"><?= $faq[1] ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <p>&copy; <?= date('Y') ?> Faseeh — <a href="landing.php">For Learners</a> · <a href="mailto:schools@faseeh.com">schools@faseeh.com</a> · <a href="privacy.php">Privacy</a></p>
  </div>
</footer>

<script>
// Scroll reveal
const observer = new IntersectionObserver(
  entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }),
  { threshold:0.08, rootMargin:'0px 0px -40px 0px' }
);
document.querySelectorAll('.reveal').forEach(r => observer.observe(r));

// FAQ
function toggleFaq(i) {
  const item = document.getElementById('faq-' + i);
  item.classList.toggle('open');
}

// Form submit
function submitForm(e) {
  e.preventDefault();
  // In production: send to contact_submit.php via fetch()
  document.getElementById('contact-form').style.display    = 'none';
  document.getElementById('form-success').style.display = 'block';
}
</script>
</body>
</html>
