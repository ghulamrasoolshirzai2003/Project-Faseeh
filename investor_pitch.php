<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Faseeh — Investor Pitch Deck</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
<style>
:root{
  --bg:#0e0c1e;--bg2:#161430;--bg3:#1c1a38;
  --border:rgba(255,255,255,.07);--accent:#f5a623;--accent2:#7c5cbf;
  --accent3:#3ecf8e;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;scroll-snap-type:y mandatory}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;overflow-x:hidden}

/* ── SLIDE BASE ── */
.slide{
  min-height:100vh;scroll-snap-align:start;
  display:flex;flex-direction:column;justify-content:center;
  padding:80px 10vw;position:relative;overflow:hidden;
}
.slide::before{content:'';position:absolute;inset:0;pointer-events:none;
  background:var(--slide-bg,none)}
.slide-num{position:absolute;bottom:32px;right:40px;font-size:.75rem;
  color:rgba(255,255,255,.15);font-weight:700;letter-spacing:.1em}

/* ── NAV BAR ── */
.deck-nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  background:rgba(14,12,30,.9);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);padding:0 32px;height:56px;
  display:flex;align-items:center;justify-content:space-between;
}
.deck-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;color:var(--text)}
.deck-logo span{color:var(--accent)}
.deck-progress{display:flex;gap:6px;align-items:center}
.deck-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.2);
  cursor:pointer;transition:.2s}
.deck-dot.active{background:var(--accent);width:20px;border-radius:3px}
.deck-controls{display:flex;gap:8px}
.deck-btn{background:rgba(255,255,255,.08);border:1px solid var(--border);color:var(--text);
  padding:6px 14px;border-radius:50px;font-size:.78rem;cursor:pointer;transition:.2s}
.deck-btn:hover{border-color:var(--accent);color:var(--accent)}

/* ── TYPE HELPERS ── */
.label{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:var(--accent);margin-bottom:10px;display:block}
h1{font-family:'Syne',sans-serif;font-weight:800;line-height:1.1}
h2{font-family:'Syne',sans-serif;font-weight:800;line-height:1.2}
.arabic{font-family:'Amiri',serif;color:var(--gold)}
em{font-style:normal;color:var(--accent)}

/* ── SLIDE 1 — COVER ── */
#s1{
  --slide-bg:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(124,92,191,.2),transparent);
  align-items:center;text-align:center;
}
#s1 .arabic-title{font-family:'Amiri',serif;font-size:clamp(3rem,7vw,6rem);
  color:var(--gold);text-shadow:0 0 80px rgba(212,168,67,.4);line-height:1.2;margin-bottom:8px}
#s1 h1{font-size:clamp(2rem,5vw,4rem);margin-bottom:16px}
#s1 .tagline{font-size:clamp(1rem,2vw,1.3rem);color:var(--muted);max-width:600px;line-height:1.7;margin-bottom:48px}
.cover-badges{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:48px}
.cover-badge{padding:8px 18px;border-radius:50px;border:1px solid var(--border);
  font-size:.82rem;color:var(--muted);background:rgba(255,255,255,.03)}
.cover-badge.gold{border-color:rgba(212,168,67,.4);color:var(--gold);background:rgba(212,168,67,.06)}
.scroll-hint{font-size:.8rem;color:rgba(255,255,255,.2);animation:bounce 2s infinite}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(6px)}}

/* ── GRID HELPERS ── */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.three-col{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.four-col{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}

/* ── CARD ── */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:24px}
.card.accent{border-color:rgba(245,166,35,.3);background:rgba(245,166,35,.05)}
.card.green{border-color:rgba(62,207,142,.3);background:rgba(62,207,142,.05)}
.card.purple{border-color:rgba(124,92,191,.3);background:rgba(124,92,191,.05)}

/* ── STAT ── */
.stat-num{font-family:'Syne',sans-serif;font-size:clamp(2.5rem,5vw,4rem);
  font-weight:800;color:var(--accent);line-height:1;display:block;margin-bottom:4px}
.stat-label{font-size:.82rem;color:var(--muted);line-height:1.4}

/* ── PROGRESS BAR ── */
.pbar{height:8px;background:rgba(255,255,255,.07);border-radius:50px;overflow:hidden;margin-top:10px}
.pbar-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--accent3),var(--accent))}

/* ── BULLET LIST ── */
.bullets{list-style:none;display:flex;flex-direction:column;gap:12px}
.bullets li{display:flex;align-items:flex-start;gap:12px;font-size:.92rem;color:var(--muted);line-height:1.6}
.bullets li::before{content:'→';color:var(--accent);font-weight:700;flex-shrink:0;margin-top:2px}
.bullets li strong{color:var(--text)}

/* ── TABLE ── */
.comp-table{width:100%;border-collapse:collapse;font-size:.85rem}
.comp-table th{text-align:left;padding:10px 16px;font-size:.72rem;font-weight:700;
  letter-spacing:.08em;text-transform:uppercase;color:var(--muted);
  border-bottom:1px solid var(--border)}
.comp-table td{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.04);color:var(--muted)}
.comp-table tr.us td{color:var(--text);font-weight:600}
.comp-table tr.us td:first-child{color:var(--accent)}
.check{color:var(--accent3)}
.cross{color:rgba(255,255,255,.2)}
.partial{color:var(--accent)}

/* ── TIMELINE ── */
.timeline{display:flex;flex-direction:column;gap:0}
.tl-item{display:flex;gap:20px;padding-bottom:28px;position:relative}
.tl-item:not(:last-child)::before{content:'';position:absolute;left:15px;top:32px;
  bottom:0;width:2px;background:rgba(255,255,255,.08)}
.tl-dot{width:32px;height:32px;border-radius:50%;background:var(--accent2);
  border:2px solid var(--accent);flex-shrink:0;display:flex;align-items:center;
  justify-content:center;font-size:.75rem;font-weight:800}
.tl-content h4{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:4px}
.tl-content p{font-size:.83rem;color:var(--muted);line-height:1.5}
.tl-quarter{font-size:.7rem;color:var(--accent);font-weight:700;letter-spacing:.05em;
  text-transform:uppercase;margin-bottom:4px}

/* ── TEAM ── */
.team-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px}
.team-card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:24px;text-align:center}
.team-avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--accent2),var(--accent));
  display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 14px}
.team-name{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:4px}
.team-role{font-size:.78rem;color:var(--accent);margin-bottom:6px}
.team-bio{font-size:.78rem;color:var(--muted);line-height:1.5}

/* ── ASK SLIDE ── */
#s12{
  --slide-bg:radial-gradient(ellipse 80% 60% at 50% 50%,rgba(212,168,67,.12),transparent);
  align-items:center;text-align:center;
}
.ask-amount{font-family:'Syne',sans-serif;font-size:clamp(3rem,8vw,6rem);font-weight:800;
  color:var(--accent);margin-bottom:8px}
.use-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;
  max-width:700px;margin:32px auto 0;text-align:left}
.use-item{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:20px}
.use-pct{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--accent);margin-bottom:4px}
.use-label{font-size:.85rem;color:var(--muted)}

/* ── RESPONSIVE ── */
@media(max-width:768px){
  .slide{padding:80px 6vw 60px}
  .two-col,.three-col,.four-col{grid-template-columns:1fr}
  .comp-table{font-size:.75rem}
  .use-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- FIXED NAV -->
<nav class="deck-nav">
  <div style="display: flex; align-items: center; gap: 15px;">
    <a href="index.php" style="text-decoration: none; color: var(--muted); font-size: 0.8rem; font-weight: 700; border: 1px solid var(--border); padding: 5px 12px; border-radius: 8px; transition: 0.3s; margin-right: 10px;">← Home</a>
    <div style="display: flex; align-items: center; gap: 10px;">
        <div class="mini-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, #f2994a, #f2c94c); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4);">
            <div style="font-family: 'Amiri', serif; font-size: 18px; color: white; margin-top: -1px; z-index: 2;">ف</div>
            <div style="content: ''; position: absolute; width: 26px; height: 26px; border: 1px solid rgba(255,255,255,0.4); border-top-color: transparent; border-radius: 50%; animation: spinLogo 8s linear infinite;"></div>
        </div>
        <h1 style="font-size: 1.2rem; font-weight: 800; margin: 0; background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shineText 3s linear infinite; font-family: 'Syne', sans-serif;">Faseeh</h1>
    </div>
  </div>
  
  <div class="deck-progress" id="progress">
    <?php for ($i=1;$i<=12;$i++): ?>
    <div class="deck-dot <?= $i===1?'active':'' ?>" onclick="goTo(<?= $i ?>)" id="dot-<?= $i ?>"></div>
    <?php endfor; ?>
  </div>
  <div class="deck-controls">
    <button class="deck-btn" onclick="prev()">← Prev</button>
    <button class="deck-btn" onclick="next()">Next →</button>
  </div>
</nav>

<style>
    @keyframes spinLogo { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes shineText { to { background-position: 200% center; } }
</style>


<!-- ══════════════════════════════════════════
     SLIDE 1 — COVER
══════════════════════════════════════════ -->
<section class="slide" id="s1">

  <h1>The Arabic Learning Platform<br/>the World Has Been Waiting For</h1>
  <p class="tagline">Games · AI Tutors · Quranic Arabic · Real Dialects · Community<br/>All in one beautiful, data-driven platform.</p>
  <div class="cover-badges">
    <div class="cover-badge gold">📖 Quranic Arabic</div>
    <div class="cover-badge gold">🤖 AI-Powered</div>
    <div class="cover-badge">🎮 10+ Games</div>
    <div class="cover-badge">🏆 Leaderboards</div>
    <div class="cover-badge">🏫 B2B Ready</div>
    <div class="cover-badge">🌍 Global Market</div>
  </div>
  <div class="scroll-hint">↓ Scroll or use arrow keys</div>
  <div class="slide-num">01 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 2 — THE PROBLEM
══════════════════════════════════════════ -->
<section class="slide" id="s2">
  <span class="label">The Problem</span>
  <div class="two-col">
    <div>
      <h2 style="font-size:clamp(1.8rem,3vw,2.8rem);margin-bottom:24px">
        Arabic is the <em>world's 5th most spoken language</em> — yet no one has built a truly great app for it.
      </h2>
      <ul class="bullets">
        <li><strong>1.8 billion Muslims</strong> want to understand Quranic Arabic — and are deeply underserved</li>
        <li><strong>400 million native speakers</strong> across 22 countries need dialect-aware tools</li>
        <li><strong>Duolingo's Arabic</strong> teaches only basic MSA with poor cultural depth and no Quranic track</li>
        <li><strong>Rosetta Stone</strong> is expensive, outdated, and ignores the root-word system entirely</li>
        <li>Existing apps have <strong>no calligraphy training, no dialect options, no community</strong></li>
      </ul>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="card accent">
        <span class="stat-num">$2.5B</span>
        <span class="stat-label">Global language learning app market (2024)</span>
      </div>
      <div class="card green">
        <span class="stat-num">1.8B</span>
        <span class="stat-label">Muslims globally seeking Quranic Arabic understanding</span>
      </div>
      <div class="card purple">
        <span class="stat-num">420M</span>
        <span class="stat-label">Native Arabic speakers — still no dominant app</span>
      </div>
    </div>
  </div>
  <div class="slide-num">02 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 3 — THE SOLUTION
══════════════════════════════════════════ -->
<section class="slide" id="s3">
  <span class="label">The Solution</span>
  <h2 style="font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:8px">Faseeh — فصيح</h2>
  <p style="color:var(--muted);margin-bottom:36px;font-size:1rem">The first platform to combine games, AI, calligraphy, dialects, and Quranic Arabic in one experience.</p>
  <div class="four-col">
    <?php
    $solutions = [
      ['🎮','Game Engine','Hangman, Root Finder, Sentence Builder, Audio Dictation, Calligraphy Atelier — 12 modes'],
      ['🤖','AI Suite','Conversation Partner, Essay Grader, Pronunciation Coach — real AI feedback, not just MCQs'],
      ['📖','Quranic Track','Word-by-word breakdown of Surahs, Top 500 Quran words, spaced repetition for du\'a vocabulary'],
      ['🏫','B2B Platform','Teacher dashboards, parent portals, certificates, custom branding — for schools and mosques'],
    ];
    foreach ($solutions as $s): ?>
    <div class="card" style="text-align:center">
      <div style="font-size:2.2rem;margin-bottom:12px"><?= $s[0] ?></div>
      <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:8px"><?= $s[1] ?></h3>
      <p style="font-size:.8rem;color:var(--muted);line-height:1.6"><?= $s[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="slide-num">03 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 4 — PRODUCT SCREENSHOTS
══════════════════════════════════════════ -->
<section class="slide" id="s4">
  <span class="label">The Product</span>
  <h2 style="margin-bottom:32px;font-size:clamp(1.6rem,2.5vw,2.2rem)">Already built. Already working. Already loved.</h2>
  <div class="three-col">
    <?php
    $screens = [
      ['🎮','Game Zone','12 game modes from Hangman to Calligraphy Atelier. Beginner to Advanced levels. 181+ vocabulary words.'],
      ['📊','Dashboard','XP, streaks, daily challenges, academic performance reports, community feed — all in one view.'],
      ['🏆','Leaderboard','Global rankings filtered by level. Arabic rank titles. Weekly competitions drive daily return.'],
      ['🎓','Academy','4 Learning Studios: Reading Sanctuary, Writing Studio, Speaking Studio, Listening Lounge.'],
      ['👤','User Profile','Trophy Cabinet, badges, streak history, XP level, custom Arabic avatar — personalised identity.'],
      ['📖','Quranic Track','Word-by-word breakdown, Top 500 words, Surah library, flashcard system — the missing Arabic app.'],
    ];
    foreach ($screens as $s): ?>
    <div class="card" style="display:flex;gap:14px;align-items:flex-start">
      <div style="font-size:1.6rem;flex-shrink:0"><?= $s[0] ?></div>
      <div>
        <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;margin-bottom:6px"><?= $s[1] ?></h3>
        <p style="font-size:.8rem;color:var(--muted);line-height:1.55"><?= $s[2] ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="slide-num">04 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 5 — MARKET SIZE
══════════════════════════════════════════ -->
<section class="slide" id="s5">
  <span class="label">Market Opportunity</span>
  <div class="two-col">
    <div>
      <h2 style="font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:20px">A <em>massive, underserved</em> global market</h2>
      <ul class="bullets">
        <li><strong>TAM $12B+</strong> — Global language learning market</li>
        <li><strong>SAM $3.5B</strong> — Arabic & Islamic education apps</li>
        <li><strong>SOM $120M</strong> — Addressable in 3 years at current trajectory</li>
        <li>Arabic is <strong>official language in 22 countries</strong></li>
        <li><strong>Muslim population growing</strong> at 1.8% annually — fastest of any religion</li>
        <li>No single dominant app for Arabic — <strong>the category is wide open</strong></li>
      </ul>
    </div>
    <div style="display:flex;flex-direction:column;gap:20px">
      <?php
      $markets = [
        ['TAM — Language Learning Apps','$12B+','100'],
        ['SAM — Arabic & Islamic EdTech','$3.5B','29'],
        ['SOM — Faseeh Target (3yr)','$120M','1'],
      ];
      foreach ($markets as $m): ?>
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <span style="font-size:.82rem;color:var(--muted)"><?= $m[0] ?></span>
          <span style="font-family:'Syne',sans-serif;font-weight:800;color:var(--accent)"><?= $m[1] ?></span>
        </div>
        <div class="pbar"><div class="pbar-fill" style="width:<?= $m[2] ?>%"></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="slide-num">05 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 6 — COMPETITION
══════════════════════════════════════════ -->
<section class="slide" id="s6">
  <span class="label">Competitive Landscape</span>
  <h2 style="margin-bottom:28px;font-size:clamp(1.5rem,2.5vw,2rem)">We do what no competitor does</h2>
  <div style="overflow-x:auto">
    <table class="comp-table">
      <thead>
        <tr>
          <th>Platform</th>
          <th>Quranic Arabic</th>
          <th>Dialects</th>
          <th>Root System</th>
          <th>Calligraphy</th>
          <th>AI Tutor</th>
          <th>Games</th>
          <th>B2B</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <tr class="us">
          <td>⭐ Faseeh</td>
          <td><span class="check">✓ Full track</span></td>
          <td><span class="check">✓ MSA+4</span></td>
          <td><span class="check">✓ Deep</span></td>
          <td><span class="check">✓ AI-powered</span></td>
          <td><span class="check">✓ Chat+Essay</span></td>
          <td><span class="check">✓ 12 modes</span></td>
          <td><span class="check">✓ Schools+Mosques</span></td>
          <td>Free / $4.99</td>
        </tr>
        <tr>
          <td>Duolingo</td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗ MSA only</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="partial">~ Basic</span></td>
          <td><span class="partial">~ Limited</span></td>
          <td><span class="partial">~ Schools only</span></td>
          <td>Free / $6.99</td>
        </tr>
        <tr>
          <td>Rosetta Stone</td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗ MSA only</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="partial">~ Basic</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="check">✓</span></td>
          <td>$11.99/mo</td>
        </tr>
        <tr>
          <td>ArabicPod101</td>
          <td><span class="partial">~ Partial</span></td>
          <td><span class="partial">~ Some</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td>$4–$47/mo</td>
        </tr>
        <tr>
          <td>Bayyinah TV</td>
          <td><span class="check">✓ Excellent</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="check">✓</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗</span></td>
          <td><span class="cross">✗ Video only</span></td>
          <td><span class="cross">✗</span></td>
          <td>$30/mo</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p style="font-size:.8rem;color:var(--muted);margin-top:16px">
    ✓ = Full feature · ~ = Partial · ✗ = Not available
  </p>
  <div class="slide-num">06 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 7 — BUSINESS MODEL
══════════════════════════════════════════ -->
<section class="slide" id="s7">
  <span class="label">Business Model</span>
  <h2 style="margin-bottom:32px;font-size:clamp(1.6rem,2.5vw,2.2rem)">Multiple revenue streams. High retention by design.</h2>
  <div class="three-col">
    <?php
    $models = [
      ['💸','Freemium B2C','Free tier drives acquisition. Premium at $4.99/mo unlocks all games, AI features, and certificates. Lifetime plan at $49 for high-intent users.','Target: 5% conversion of MAU'],
      ['🏫','B2B Institutional','$49–$149/month for schools, mosques, and universities. Custom Enterprise pricing for large networks. High LTV, low churn.','Target: 200 institutions year 2'],
      ['🎓','Certificates','Premium paid certificates at $9.99 each — shareable on LinkedIn. Drives organic acquisition via social proof.','Target: 15% of Premium users'],
      ['🌐','White-Label API','Licence Faseeh content and engine to media companies, EdTech platforms, and governments. Revenue share or flat licence.','Target: 3 enterprise deals year 2'],
      ['📦','Content Packs','Downloadable curriculum packs for offline use, custom vocabulary lists, and specialised Quranic word sets.','Target: $8–$24 one-time'],
      ['📣','Referral Growth','Every referral = 7 days Premium for both parties. Viral coefficient target of K > 1.2 — growth without paid ads.','Channels: WhatsApp, Islamic networks'],
    ];
    foreach ($models as $m): ?>
    <div class="card">
      <div style="font-size:1.8rem;margin-bottom:10px"><?= $m[0] ?></div>
      <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:.92rem;margin-bottom:6px"><?= $m[1] ?></h3>
      <p style="font-size:.8rem;color:var(--muted);line-height:1.55;margin-bottom:10px"><?= $m[2] ?></p>
      <span style="font-size:.73rem;color:var(--accent);font-weight:700"><?= $m[3] ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="slide-num">07 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 8 — TRACTION
══════════════════════════════════════════ -->
<section class="slide" id="s8">
  <span class="label">Traction</span>
  <div class="two-col">
    <div>
      <h2 style="font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:24px">
        Built from scratch. <em>Real users. Real progress.</em>
      </h2>
      <ul class="bullets">
        <li><strong>Fully functional platform</strong> — Dashboard, Academy, Play, Rankings, Profile all live</li>
        <li><strong>12 game modes</strong> built and playable including AI Essay Grader</li>
        <li><strong>Quranic Arabic track</strong> launched with 8 Surahs, word-by-word breakdown</li>
        <li><strong>B2B landing page</strong> and institutional pricing ready</li>
        <li><strong>PWA installable</strong> — works on mobile without app store</li>
        <li><strong>Referral system</strong> built — viral loop ready to activate</li>
        <li><strong>Certificates system</strong> — downloadable, shareable, LinkedIn-ready</li>
      </ul>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="card accent">
        <span class="stat-num">9</span>
        <span class="stat-label">Complete feature pages built and deployed</span>
      </div>
      <div class="card green">
        <span class="stat-num">12</span>
        <span class="stat-label">Interactive game modes available to users now</span>
      </div>
      <div class="card purple">
        <span class="stat-num">181+</span>
        <span class="stat-label">Vocabulary words across Beginner, Intermediate, Advanced</span>
      </div>
      <div class="card">
        <span class="stat-num">0</span>
        <span class="stat-label">Paid acquisition spend — built organically</span>
      </div>
    </div>
  </div>
  <div class="slide-num">08 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 9 — GROWTH STRATEGY
══════════════════════════════════════════ -->
<section class="slide" id="s9">
  <span class="label">Growth Strategy</span>
  <h2 style="margin-bottom:32px;font-size:clamp(1.5rem,2.5vw,2rem)">Organic-first. Community-driven. Global by design.</h2>
  <div class="two-col">
    <div style="display:flex;flex-direction:column;gap:14px">
      <?php
      $channels = [
        ['🕌','Islamic Community Networks','1.8B Muslims = the world\'s largest underserved language learning community. Mosque partnerships, madrasas, Islamic schools.','High intent, high LTV, free word-of-mouth'],
        ['📱','TikTok & Instagram Reels','"Learn one Arabic word a day" — extremely viral format. Short-form content has infinite reach in Muslim-majority markets.','Zero CAC organic acquisition'],
        ['🎓','School & Masjid Outreach','Direct B2B sales to institutions — one school = 100-300 users acquired at once with high retention.','High LTV: $149/mo recurring'],
        ['🌐','SEO Content Engine','"How to learn Arabic", "best Arabic app", "learn Quranic Arabic" — rank for high-intent search terms.','Compounding organic traffic'],
      ];
      foreach ($channels as $c): ?>
      <div class="card" style="display:flex;gap:16px;align-items:flex-start">
        <div style="font-size:1.6rem;flex-shrink:0"><?= $c[0] ?></div>
        <div>
          <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;margin-bottom:4px"><?= $c[1] ?></h3>
          <p style="font-size:.78rem;color:var(--muted);line-height:1.5;margin-bottom:4px"><?= $c[2] ?></p>
          <span style="font-size:.72rem;color:var(--accent3);font-weight:700"><?= $c[3] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div>
      <h3 style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:20px;color:var(--muted);font-size:.85rem;text-transform:uppercase;letter-spacing:.08em">Target Markets</h3>
      <?php
      $targets = [
        ['Malaysia & SE Asia','100M Muslims, tech-savvy, high mobile penetration — ideal launch market','#f5a623'],
        ['Middle East & GCC','High disposable income, strong demand for quality education tools','#7c5cbf'],
        ['UK, US, Europe (diaspora)','Large, underserved Muslim diaspora communities seeking Quranic Arabic','#3ecf8e'],
        ['Sub-Saharan Africa','Fastest-growing Muslim population, mobile-first, huge demand','#d4a843'],
      ];
      foreach ($targets as $t): ?>
      <div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;
        padding:16px;margin-bottom:10px;border-left:3px solid <?= $t[2] ?>">
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;margin-bottom:4px;color:var(--text)"><?= $t[0] ?></div>
        <div style="font-size:.78rem;color:var(--muted)"><?= $t[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="slide-num">09 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 10 — ROADMAP
══════════════════════════════════════════ -->
<section class="slide" id="s10">
  <span class="label">Roadmap</span>
  <div class="two-col">
    <div>
      <h2 style="font-size:clamp(1.6rem,2.5vw,2.2rem);margin-bottom:28px">3-phase plan to <em>category leadership</em></h2>
      <div class="timeline">
        <?php
        $phases = [
          ['Q2–Q3 2025','Phase 1 — Foundation','Deploy production domain (faseeh.com). Launch referral system. B2B outreach to 50 Islamic schools. Grow to 1,000 MAU.'],
          ['Q4 2025','Phase 2 — Growth','Native mobile app (React Native). Egyptian + Gulf dialect tracks. AI Pronunciation Coach. 5,000 MAU. First 20 institutional clients.'],
          ['Q1–Q2 2026','Phase 3 — Scale','White-label licensing. Government partnerships. Series A raise. 50,000 MAU. 200 institutional accounts.'],
          ['2027+','Phase 4 — Dominance','Category leader for Arabic learning. 500K+ MAU. Regional language expansion. Acquisition conversations.'],
        ];
        foreach ($phases as $i => $p): ?>
        <div class="tl-item">
          <div class="tl-dot"><?= $i+1 ?></div>
          <div class="tl-content">
            <div class="tl-quarter"><?= $p[0] ?></div>
            <h4><?= $p[1] ?></h4>
            <p><?= $p[2] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px">
      <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)">3-Year Projections</h3>
      <?php
      $projections = [
        ['Year 1','1,000 MAU','$60K ARR','20 institutions'],
        ['Year 2','15,000 MAU','$900K ARR','150 institutions'],
        ['Year 3','80,000 MAU','$5M ARR','500 institutions'],
      ];
      foreach ($projections as $pr): ?>
      <div class="card">
        <div style="font-family:'Syne',sans-serif;font-weight:800;color:var(--accent);margin-bottom:10px"><?= $pr[0] ?></div>
        <div style="display:flex;gap:16px;flex-wrap:wrap">
          <div><div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem"><?= $pr[1] ?></div><div style="font-size:.72rem;color:var(--muted)">MAU</div></div>
          <div><div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem;color:var(--accent3)"><?= $pr[2] ?></div><div style="font-size:.72rem;color:var(--muted)">ARR</div></div>
          <div><div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem;color:var(--accent2)"><?= $pr[3] ?></div><div style="font-size:.72rem;color:var(--muted)">Institutions</div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="slide-num">10 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 11 — TEAM
══════════════════════════════════════════ -->
<section class="slide" id="s11">
  <span class="label">The Team</span>
  <h2 style="margin-bottom:32px;font-size:clamp(1.6rem,2.5vw,2.2rem)">Built by people who <em>care deeply</em> about Arabic</h2>
  <div class="team-grid">
    <?php
    $team = [
      ['👨‍💻','Founder & CEO','Full-stack developer & Arabic learner. Built entire Faseeh platform from scratch. Visionary behind the product.','PHP, JS, AI/ML integration'],
      ['🎨','Head of Design','UI/UX designer with deep understanding of Arabic typography, RTL design, and cultural aesthetics.','Figma, Brand, User Research'],
      ['📖','Arabic Content Lead','Native Arabic speaker & Islamic scholar. Oversees all Quranic content, vocabulary accuracy, and cultural depth.','MSA, Quranic Arabic, Dialects'],
      ['💼','Head of B2B','EdTech sales background. Manages institutional partnerships, school onboarding, and corporate accounts.','Islamic Schools, Universities'],
    ];
    foreach ($team as $t): ?>
    <div class="team-card">
      <div class="team-avatar"><?= $t[0] ?></div>
      <div class="team-name"><?= $t[1] ?></div>
      <div class="team-role"><?= $t[3] ?></div>
      <div class="team-bio"><?= $t[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--border);border-radius:14px;
    padding:20px 28px;margin-top:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="font-size:1.5rem">🤝</div>
    <div>
      <div style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:4px">We Are Actively Hiring</div>
      <div style="font-size:.85rem;color:var(--muted)">Looking for: Arabic content writers, React Native developer, Growth marketer with Islamic community experience, Customer success for B2B</div>
    </div>
  </div>
  <div class="slide-num">11 / 12</div>
</section>

<!-- ══════════════════════════════════════════
     SLIDE 12 — THE ASK
══════════════════════════════════════════ -->
<section class="slide" id="s12">
  <span class="label">The Ask</span>
  <div class="ask-amount">$250,000</div>
  <p style="color:var(--muted);font-size:1rem;margin-bottom:8px">Pre-Seed Round — Raising to reach Series A milestones</p>
  <div style="font-family:'Amiri',serif;font-size:1.8rem;color:var(--gold);margin-bottom:32px">
    نحن نبني مستقبل تعليم اللغة العربية
  </div>
  <div class="use-grid">
    <div class="use-item">
      <div class="use-pct">40%</div>
      <div class="use-label">🛠️ Product — Native app, dialect tracks, AI pronunciation coach</div>
    </div>
    <div class="use-item">
      <div class="use-pct">25%</div>
      <div class="use-label">📣 Growth — Content marketing, influencer partnerships, B2B sales</div>
    </div>
    <div class="use-item">
      <div class="use-pct">20%</div>
      <div class="use-label">📖 Content — Arabic scholars, dialect experts, Quranic advisors</div>
    </div>
    <div class="use-item">
      <div class="use-pct">15%</div>
      <div class="use-label">⚙️ Operations — Hosting, legal, admin, working capital</div>
    </div>
  </div>
  <div style="margin-top:40px;text-align:center">
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:16px">Ready to talk? We'd love to show you the product live.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="mailto:invest@faseeh.com" style="background:linear-gradient(135deg,var(--accent),#e8862a);
        color:#1a0f00;padding:14px 32px;border-radius:50px;font-weight:700;text-decoration:none;font-size:.95rem">
        ✉️ invest@faseeh.com
      </a>
      <a href="https://faseeh.com" style="background:rgba(255,255,255,.08);border:1px solid var(--border);
        color:var(--text);padding:14px 32px;border-radius:50px;font-weight:500;text-decoration:none;font-size:.95rem">
        🌐 faseeh.com
      </a>
    </div>
  </div>
  <div class="slide-num">12 / 12</div>
</section>

<script>
let current = 1;
const total = 12;

function goTo(n) {
  if (n < 1 || n > total) return;
  current = n;
  document.getElementById('s' + n).scrollIntoView({ behavior: 'smooth' });
  document.querySelectorAll('.deck-dot').forEach((d, i) => {
    d.classList.toggle('active', i + 1 === n);
  });
}

function next() { goTo(current + 1); }
function prev() { goTo(current - 1); }

// Keyboard navigation
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === ' ') { e.preventDefault(); next(); }
  if (e.key === 'ArrowUp'   || e.key === 'ArrowLeft')                   { e.preventDefault(); prev(); }
});

// Update dot on scroll
const slides = Array.from({ length: total }, (_, i) => document.getElementById('s' + (i + 1)));
const scrollObserver = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const n = parseInt(e.target.id.replace('s', ''));
      current = n;
      document.querySelectorAll('.deck-dot').forEach((d, i) => d.classList.toggle('active', i + 1 === n));
    }
  });
}, { threshold: 0.6 });
slides.forEach(s => scrollObserver.observe(s));
</script>
</body>
</html>
