<?php
// landing.php — Faseeh Public Landing Page
// Place this at your site root. Visitors who aren't logged in see this.

$page_title   = "Faseeh — Master Arabic, One Word at a Time";
$page_desc    = "The world's most immersive Arabic learning platform. Games, AI tutors, Quranic Arabic, and real dialects. Join thousands already speaking Arabic with confidence.";
$canonical    = "https://faseeh.com/";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($page_desc) ?>"/>
<meta property="og:title"       content="<?= htmlspecialchars($page_title) ?>"/>
<meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>"/>
<meta property="og:image"       content="<?= $canonical ?>assets/og-image.png"/>
<meta property="og:url"         content="<?= $canonical ?>"/>
<meta name="twitter:card"       content="summary_large_image"/>
<link rel="canonical"           href="<?= $canonical ?>"/>
<link rel="manifest"            href="/manifest.json"/>
<link rel="icon"                href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet"/>

<style>
/* ============================================
   FASEEH LANDING PAGE — CSS
   ============================================ */
:root {
  --bg-deep:      #0e0c1e;
  --bg-card:      #161430;
  --bg-card2:     #1c1a38;
  --border:       rgba(255,255,255,0.07);
  --accent:       #f5a623;
  --accent2:      #7c5cbf;
  --accent3:      #3ecf8e;
  --text:         #f0eeff;
  --text-muted:   #8b87b0;
  --arabic-gold:  #d4a843;
  --radius:       16px;
  --radius-lg:    24px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  background: var(--bg-deep);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  line-height: 1.65;
  overflow-x: hidden;
}

/* ---- NOISE TEXTURE OVERLAY ---- */
body::before {
  content: '';
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
  opacity: 0.4;
}

/* ---- RADIAL GLOWS ---- */
.glow-1 {
  position: fixed; top: -200px; left: -200px; width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(124,92,191,0.18) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}
.glow-2 {
  position: fixed; bottom: -200px; right: -200px; width: 800px; height: 800px;
  background: radial-gradient(circle, rgba(245,166,35,0.10) 0%, transparent 70%);
  pointer-events: none; z-index: 0;
}

/* ---- LAYOUT HELPERS ---- */
.container { max-width: 1180px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 1; }
section    { position: relative; z-index: 1; }

/* ============================================
   NAVBAR
   ============================================ */
nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  padding: 0 32px;
  display: flex; align-items: center; justify-content: space-between;
  height: 68px;
  background: rgba(14,12,30,0.85);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
}

.nav-logo {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.35rem;
  color: var(--text); text-decoration: none;
}
.nav-logo-icon {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent) 0%, #e8862a 100%);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
}
.nav-logo span { color: var(--accent); }

.nav-links {
  display: flex; align-items: center; gap: 32px; list-style: none;
}
.nav-links a {
  color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500;
  transition: color .2s;
}
.nav-links a:hover { color: var(--text); }

.nav-cta {
  display: flex; align-items: center; gap: 12px;
}
.btn-ghost {
  background: none; border: 1px solid var(--border); color: var(--text);
  padding: 9px 20px; border-radius: 50px; font-size: 0.875rem; font-weight: 500;
  cursor: pointer; text-decoration: none; transition: border-color .2s, color .2s;
}
.btn-ghost:hover { border-color: var(--accent); color: var(--accent); }

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e8862a 100%);
  color: #1a0f00; border: none; padding: 10px 22px; border-radius: 50px;
  font-size: 0.875rem; font-weight: 700; cursor: pointer; text-decoration: none;
  transition: opacity .2s, transform .15s; display: inline-block;
}
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
.hamburger span { width: 24px; height: 2px; background: var(--text); border-radius: 2px; transition: .3s; }

/* ============================================
   HERO
   ============================================ */
.hero {
  min-height: 100vh; display: flex; align-items: center;
  padding: 120px 0 80px;
  background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(124,92,191,0.15) 0%, transparent 70%);
}

.hero-inner {
  display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center;
}

.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(245,166,35,0.1); border: 1px solid rgba(245,166,35,0.3);
  color: var(--accent); border-radius: 50px; padding: 6px 16px;
  font-size: 0.8rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase;
  margin-bottom: 24px;
  animation: fadeInUp .6s ease both;
}

.hero-arabic-title {
  font-family: 'Amiri', serif;
  font-size: clamp(2.5rem, 5vw, 4.5rem);
  color: var(--arabic-gold);
  line-height: 1.2;
  margin-bottom: 8px;
  animation: fadeInUp .6s .1s ease both;
  text-shadow: 0 0 40px rgba(212,168,67,0.3);
}

.hero-title {
  font-family: 'Syne', sans-serif;
  font-size: clamp(2rem, 4vw, 3.5rem);
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 24px;
  animation: fadeInUp .6s .2s ease both;
}
.hero-title em { font-style: normal; color: var(--accent); }

.hero-subtitle {
  font-size: 1.1rem; color: var(--text-muted); max-width: 480px;
  margin-bottom: 40px; font-weight: 300;
  animation: fadeInUp .6s .3s ease both;
}

.hero-actions {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  animation: fadeInUp .6s .4s ease both;
}
.btn-hero {
  background: linear-gradient(135deg, var(--accent) 0%, #e8862a 100%);
  color: #1a0f00; border: none; padding: 15px 32px; border-radius: 50px;
  font-size: 1rem; font-weight: 700; cursor: pointer; text-decoration: none;
  transition: all .2s; box-shadow: 0 8px 32px rgba(245,166,35,0.3);
  display: inline-flex; align-items: center; gap: 8px;
}
.btn-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(245,166,35,0.4); }

.hero-stats {
  display: flex; gap: 32px; margin-top: 48px; flex-wrap: wrap;
  animation: fadeInUp .6s .5s ease both;
}
.hero-stat strong {
  display: block; font-family: 'Syne', sans-serif; font-size: 1.8rem; font-weight: 800;
  color: var(--text);
}
.hero-stat span { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }

/* Hero visual */
.hero-visual { animation: fadeInUp .6s .3s ease both; }
.hero-card-stack { position: relative; height: 480px; }

.hcard {
  position: absolute; border-radius: var(--radius-lg);
  background: var(--bg-card); border: 1px solid var(--border);
  padding: 20px 24px;
  box-shadow: 0 24px 80px rgba(0,0,0,0.5);
}

.hcard-main {
  width: 340px; top: 20px; left: 50%; transform: translateX(-50%);
  background: linear-gradient(135deg, #1c1a38 0%, #221f42 100%);
  animation: float1 4s ease-in-out infinite;
}
.hcard-main h3 { font-family: 'Amiri', serif; font-size: 2.8rem; color: var(--arabic-gold); text-align: center; margin-bottom: 4px; }
.hcard-main p  { text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; }
.word-meaning  { text-align: center; font-size: 1.1rem; font-weight: 600; margin-bottom: 16px; }
.word-example  {
  background: rgba(255,255,255,0.05); border-radius: 10px; padding: 10px 14px;
  font-family: 'Amiri', serif; font-size: 1.1rem; text-align: right; direction: rtl;
  color: var(--text-muted);
}

.hcard-streak {
  width: 160px; bottom: 80px; left: 0;
  animation: float2 4s 1s ease-in-out infinite;
}
.hcard-streak .streak-num { font-family: 'Syne', sans-serif; font-size: 2rem; font-weight: 800; }
.hcard-streak .streak-fire { font-size: 1.5rem; }

.hcard-xp {
  width: 160px; top: 60px; right: 0;
  animation: float3 4s .5s ease-in-out infinite;
  text-align: center;
}
.hcard-xp .xp-num { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 800; color: var(--accent3); }

.hcard-badge {
  width: 180px; bottom: 30px; right: -10px;
  animation: float1 5s 2s ease-in-out infinite;
  text-align: center;
}
.hcard-badge .badge-icon { font-size: 2rem; margin-bottom: 4px; }
.hcard-badge p { font-size: 0.75rem; color: var(--text-muted); }

@keyframes float1 { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(-10px)} }
@keyframes float2 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes float3 { 0%,100%{transform:translateY(0)} 50%{transform:translateY(8px)} }

/* ============================================
   SOCIAL PROOF BAR
   ============================================ */
.proof-bar {
  padding: 24px 0;
  border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
  overflow: hidden;
}
.proof-track {
  display: flex; gap: 64px; align-items: center;
  animation: scrollLeft 30s linear infinite;
  width: max-content;
}
.proof-item {
  display: flex; align-items: center; gap: 12px;
  color: var(--text-muted); font-size: 0.85rem; white-space: nowrap;
}
.proof-item span { font-size: 1.2rem; }
@keyframes scrollLeft { from{transform:translateX(0)} to{transform:translateX(-50%)} }

/* ============================================
   FEATURES / WHY FASEEH
   ============================================ */
.section-label {
  font-size: 0.75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--accent); margin-bottom: 12px;
}
.section-title {
  font-family: 'Syne', sans-serif; font-weight: 800;
  font-size: clamp(1.8rem, 3vw, 2.8rem); line-height: 1.2;
  margin-bottom: 16px;
}
.section-sub { color: var(--text-muted); font-size: 1.05rem; max-width: 560px; }

.features { padding: 100px 0; }
.features-header { text-align: center; margin-bottom: 64px; }
.features-header .section-sub { margin: 0 auto; }

.features-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
}

.feat-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg);
  padding: 32px 28px; transition: transform .25s, border-color .25s, box-shadow .25s;
  position: relative; overflow: hidden;
}
.feat-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--card-accent, var(--accent)), transparent);
  opacity: 0; transition: opacity .3s;
}
.feat-card:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.12); box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
.feat-card:hover::before { opacity: 1; }
.feat-card:nth-child(3n+1) { --card-accent: var(--accent); }
.feat-card:nth-child(3n+2) { --card-accent: var(--accent2); }
.feat-card:nth-child(3n+3) { --card-accent: var(--accent3); }

.feat-icon {
  width: 52px; height: 52px; border-radius: 14px;
  background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; margin-bottom: 20px;
}
.feat-card h3 { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; }
.feat-card p  { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; }

/* ============================================
   TRACKS SECTION
   ============================================ */
.tracks { padding: 100px 0; background: linear-gradient(180deg, transparent, rgba(124,92,191,0.05), transparent); }
.tracks-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }

.track-cards { display: flex; flex-direction: column; gap: 16px; }
.track-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 24px 28px; cursor: pointer; transition: .25s; display: flex; align-items: center; gap: 20px;
}
.track-card:hover, .track-card.active {
  border-color: var(--accent); background: var(--bg-card2);
  box-shadow: 0 0 0 1px rgba(245,166,35,0.2), 0 8px 32px rgba(0,0,0,0.3);
}
.track-icon { font-size: 2rem; width: 56px; text-align: center; flex-shrink: 0; }
.track-info h3 { font-family: 'Syne', sans-serif; font-weight: 700; margin-bottom: 4px; }
.track-info p  { font-size: 0.85rem; color: var(--text-muted); }
.track-pill {
  margin-left: auto; font-size: 0.7rem; font-weight: 700; letter-spacing: .05em;
  padding: 4px 10px; border-radius: 50px; background: rgba(245,166,35,0.15); color: var(--accent);
  white-space: nowrap; flex-shrink: 0;
}

/* ============================================
   GAMES SHOWCASE
   ============================================ */
.games { padding: 100px 0; }
.games-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 56px; }
.game-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 28px 20px; text-align: center; transition: .25s; position: relative; overflow: hidden;
}
.game-card::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, var(--game-color, rgba(124,92,191,0.1)), transparent);
  opacity: 0; transition: opacity .3s;
}
.game-card:hover { transform: translateY(-4px); }
.game-card:hover::after { opacity: 1; }
.game-emoji { font-size: 2.4rem; margin-bottom: 14px; display: block; }
.game-card h3 { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.95rem; margin-bottom: 8px; }
.game-card p  { font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; }
.game-tag {
  display: inline-block; margin-top: 14px; font-size: 0.7rem; font-weight: 700;
  padding: 3px 10px; border-radius: 50px;
  background: rgba(255,255,255,0.06); color: var(--text-muted);
}

/* ============================================
   TESTIMONIALS
   ============================================ */
.testimonials { padding: 100px 0; }
.testimonials-header { text-align: center; margin-bottom: 56px; }
.testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.testi-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 28px; position: relative;
}
.testi-quote { font-size: 2.5rem; color: var(--accent); opacity: .4; line-height: 1; margin-bottom: 12px; font-family: Georgia, serif; }
.testi-card p { font-size: 0.9rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 20px; }
.testi-author { display: flex; align-items: center; gap: 12px; }
.testi-avatar {
  width: 38px; height: 38px; border-radius: 50%; font-size: 1.1rem;
  background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;
}
.testi-name   { font-weight: 600; font-size: 0.88rem; }
.testi-meta   { font-size: 0.78rem; color: var(--text-muted); }
.stars        { color: var(--accent); font-size: 0.8rem; margin-bottom: 4px; }

/* ============================================
   PRICING
   ============================================ */
.pricing { padding: 100px 0; }
.pricing-header { text-align: center; margin-bottom: 56px; }
.pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 900px; margin: 0 auto; }
.price-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg);
  padding: 36px 28px; position: relative; transition: .25s;
}
.price-card.featured {
  border-color: var(--accent); background: linear-gradient(135deg, #1e1b3a, #251f40);
  box-shadow: 0 0 0 1px rgba(245,166,35,0.3), 0 24px 64px rgba(0,0,0,0.5);
  transform: scale(1.04);
}
.price-badge {
  position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
  background: var(--accent); color: #1a0f00; font-size: 0.72rem; font-weight: 800;
  padding: 4px 14px; border-radius: 50px; letter-spacing: .05em; text-transform: uppercase;
  white-space: nowrap;
}
.price-plan  { font-size: 0.78rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px; }
.price-amount {
  font-family: 'Syne', sans-serif; font-size: 3rem; font-weight: 800; line-height: 1; margin-bottom: 4px;
}
.price-amount sup { font-size: 1.2rem; vertical-align: top; margin-top: 8px; font-weight: 400; }
.price-amount span { font-size: 1rem; font-weight: 400; color: var(--text-muted); }
.price-period { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 28px; }
.price-features { list-style: none; margin-bottom: 28px; }
.price-features li {
  padding: 8px 0; font-size: 0.88rem; color: var(--text-muted);
  display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border);
}
.price-features li:last-child { border-bottom: none; }
.price-features li::before { content: '✓'; color: var(--accent3); font-weight: 700; flex-shrink: 0; }
.price-features li.no::before { content: '✗'; color: rgba(255,255,255,0.2); }
.price-features li.no { opacity: .5; }

/* ============================================
   CTA SECTION
   ============================================ */
.cta-section {
  padding: 100px 0;
  background: radial-gradient(ellipse 70% 60% at 50% 50%, rgba(124,92,191,0.2) 0%, transparent 70%);
  text-align: center;
}
.cta-arabic {
  font-family: 'Amiri', serif; font-size: 3rem; color: var(--arabic-gold);
  margin-bottom: 8px; display: block;
  text-shadow: 0 0 60px rgba(212,168,67,0.4);
}
.cta-section h2 { font-family: 'Syne', sans-serif; font-size: clamp(1.8rem,3.5vw,3rem); font-weight: 800; margin-bottom: 16px; }
.cta-section p  { color: var(--text-muted); font-size: 1.05rem; margin-bottom: 40px; }
.btn-cta-large {
  background: linear-gradient(135deg, var(--accent) 0%, #e8862a 100%);
  color: #1a0f00; border: none; padding: 18px 48px; border-radius: 50px;
  font-size: 1.1rem; font-weight: 700; cursor: pointer; text-decoration: none;
  transition: all .2s; box-shadow: 0 8px 40px rgba(245,166,35,0.35);
  display: inline-flex; align-items: center; gap: 10px;
}
.btn-cta-large:hover { transform: translateY(-2px); box-shadow: 0 16px 56px rgba(245,166,35,0.45); }

.cta-note { margin-top: 16px; font-size: 0.82rem; color: var(--text-muted); }

/* ============================================
   FOOTER
   ============================================ */
footer {
  border-top: 1px solid var(--border); padding: 56px 0 32px;
}
.footer-inner { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 48px; }
.footer-brand h3 { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.3rem; margin-bottom: 12px; }
.footer-brand h3 span { color: var(--accent); }
.footer-brand p { font-size: 0.88rem; color: var(--text-muted); line-height: 1.7; max-width: 260px; }
.footer-arabic { font-family: 'Amiri', serif; font-size: 1.3rem; color: var(--arabic-gold); margin-top: 8px; }
footer h4 { font-size: 0.78rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 16px; }
footer ul { list-style: none; }
footer ul li { margin-bottom: 10px; }
footer ul a { color: var(--text-muted); text-decoration: none; font-size: 0.88rem; transition: color .2s; }
footer ul a:hover { color: var(--text); }
.footer-bottom {
  border-top: 1px solid var(--border); padding-top: 24px;
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
}
.footer-bottom p { font-size: 0.8rem; color: var(--text-muted); }
.footer-socials { display: flex; gap: 12px; }
.footer-socials a {
  width: 36px; height: 36px; border-radius: 50%;
  border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
  color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: .2s;
}
.footer-socials a:hover { border-color: var(--accent); color: var(--accent); }

/* ============================================
   SCROLL ANIMATIONS
   ============================================ */
.reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1024px) {
  .features-grid { grid-template-columns: repeat(2, 1fr); }
  .games-grid    { grid-template-columns: repeat(2, 1fr); }
  .footer-inner  { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 768px) {
  .nav-links, .nav-cta { display: none; }
  .hamburger { display: flex; }
  .hero-inner { grid-template-columns: 1fr; }
  .hero-visual { display: none; }
  .tracks-inner { grid-template-columns: 1fr; }
  .features-grid { grid-template-columns: 1fr; }
  .games-grid    { grid-template-columns: repeat(2, 1fr); }
  .testi-grid    { grid-template-columns: 1fr; }
  .pricing-grid  { grid-template-columns: 1fr; }
  .price-card.featured { transform: scale(1); }
  .footer-inner  { grid-template-columns: 1fr; }
  .footer-bottom { flex-direction: column; text-align: center; }
}
</style>
</head>
<body>

<div class="glow-1"></div>
<div class="glow-2"></div>

<!-- ============ NAVBAR ============ -->
<nav>
  <a href="/" class="nav-logo">
    <div class="nav-logo-icon">ف</div>
    Fase<span>eh</span>
  </a>

  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#tracks">Tracks</a></li>
    <li><a href="#games">Games</a></li>
    <li><a href="#pricing">Pricing</a></li>
    <li><a href="b2b.php">For Schools</a></li>
  </ul>

  <div class="nav-cta">
    <a href="login.php"    class="btn-ghost">Sign In</a>
    <a href="register.php" class="btn-primary">Start Free →</a>
  </div>

  <div class="hamburger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="container">
    <div class="hero-inner">

      <div class="hero-content">
        <div class="hero-badge">🌙 Now includes Quranic Arabic</div>
        <div class="hero-arabic-title">تعلَّم العربية</div>
        <h1 class="hero-title">
          Master Arabic the<br/>
          <em>modern way</em>
        </h1>
        <p class="hero-subtitle">
          Games, AI tutors, dialect tracks, and Quranic Arabic — all in one platform built by Arabic enthusiasts, for Arabic learners worldwide.
        </p>
        <div class="hero-actions">
          <a href="register.php" class="btn-hero">
            Start Learning Free <span>→</span>
          </a>
          <a href="#features" class="btn-ghost" style="padding:13px 24px;">See How It Works</a>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <strong>10+</strong>
            <span>Learning Modules</span>
          </div>
          <div class="hero-stat">
            <strong>181+</strong>
            <span>Words & Counting</span>
          </div>
          <div class="hero-stat">
            <strong>2</strong>
            <span>Arabic Tracks</span>
          </div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="hero-card-stack">

          <div class="hcard hcard-main">
            <h3>جَمَرك</h3>
            <p>Word of the Day</p>
            <div class="word-meaning">Customs / Border Control</div>
            <div class="word-example">ذهبتُ إلى مكتب الجمارك</div>
          </div>

          <div class="hcard hcard-streak">
            <div class="streak-fire">🔥</div>
            <div class="streak-num">7</div>
            <div style="font-size:.75rem;color:var(--text-muted)">Day Streak</div>
          </div>

          <div class="hcard hcard-xp">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px">Total XP</div>
            <div class="xp-num">1,660</div>
            <div style="font-size:.7rem;color:var(--text-muted)">✨ Level 8</div>
          </div>

          <div class="hcard hcard-badge">
            <div class="badge-icon">🏆</div>
            <div style="font-weight:700;font-size:.85rem">First Victory</div>
            <p>Badge earned!</p>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ SOCIAL PROOF BAR ============ -->
<div class="proof-bar">
  <div class="container" style="overflow:hidden">
    <div class="proof-track">
      <?php
      $proofs = [
        ["🎓","Used in Islamic schools worldwide"],
        ["🌍","Learners from 40+ countries"],
        ["🤖","AI-powered pronunciation feedback"],
        ["📖","Quranic Arabic included"],
        ["🎮","10+ interactive game modes"],
        ["🏆","Leaderboards & certificates"],
        ["🎓","Used in Islamic schools worldwide"],
        ["🌍","Learners from 40+ countries"],
        ["🤖","AI-powered pronunciation feedback"],
        ["📖","Quranic Arabic included"],
        ["🎮","10+ interactive game modes"],
        ["🏆","Leaderboards & certificates"],
      ];
      foreach ($proofs as $p): ?>
        <div class="proof-item"><span><?= $p[0] ?></span><?= $p[1] ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ============ FEATURES ============ -->
<section class="features" id="features">
  <div class="container">
    <div class="features-header reveal">
      <div class="section-label">Why Faseeh</div>
      <h2 class="section-title">Everything you need to master Arabic</h2>
      <p class="section-sub">No other platform combines games, AI, calligraphy, and Quranic Arabic in one beautiful experience.</p>
    </div>

    <?php
    $features = [
      ["🎮","Game-Based Learning","Learn through Hangman, Root Word Finder, Sentence Builder and more — real learning disguised as fun."],
      ["🤖","AI Essay Grader","Write in Arabic and get instant, detailed feedback from our AI tutor on grammar, style and vocabulary."],
      ["✍️","Calligraphy Atelier","Trace and master Arabic letters with real-time AI stroke analysis and ghost-mode guidance."],
      ["📖","Quranic Arabic","A dedicated track for understanding the Quran — learn the most frequent words and grammatical patterns."],
      ["🔊","Speaking Studio","Perfect your accent and fluency with advanced voice recognition and native speaker comparisons."],
      ["🌐","Multiple Dialects","Modern Standard Arabic, Egyptian, Gulf, Levantine — learn the Arabic that matters to you."],
      ["📊","Smart Progress Tracking","Detailed performance reports across every module. Know exactly what to review and what you've mastered."],
      ["🏆","Leaderboards & Ranks","Compete globally with Arabic rank titles — from مبتدئ (Novice) to فصيح (Eloquent)."],
      ["🎓","Verified Certificates","Earn shareable, LinkedIn-ready certificates at every level to showcase your Arabic journey."],
    ];
    ?>
    <div class="features-grid">
      <?php foreach ($features as $f): ?>
      <div class="feat-card reveal">
        <div class="feat-icon"><?= $f[0] ?></div>
        <h3><?= $f[1] ?></h3>
        <p><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ TRACKS ============ -->
<section class="tracks" id="tracks">
  <div class="container">
    <div class="tracks-inner">

      <div class="reveal">
        <div class="section-label">Learning Tracks</div>
        <h2 class="section-title">Choose your Arabic journey</h2>
        <p class="section-sub" style="margin-bottom:32px">
          Whether you want to understand the Quran, watch Arabic films, or travel the Arab world — we have a track built for you.
        </p>

        <div class="track-cards">
          <?php
          $tracks = [
            ["📖","Quranic Arabic","Understand the words of Allah directly. Learn the 500 most frequent Quran words.","Most Popular","active"],
            ["🎓","Modern Standard Arabic","Formal Arabic for media, business, academia and professional communication.","CEFR A1–C2",""],
            ["🌙","Egyptian Dialect","The most widely understood dialect — perfect for media, music and entertainment.","Coming Soon",""],
            ["🏜️","Gulf Arabic","Ideal for those living or working in GCC countries.","Coming Soon",""],
          ];
          foreach ($tracks as $t): ?>
          <div class="track-card <?= $t[4] ?>">
            <div class="track-icon"><?= $t[0] ?></div>
            <div class="track-info">
              <h3><?= $t[1] ?></h3>
              <p><?= $t[2] ?></p>
            </div>
            <div class="track-pill"><?= $t[3] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="reveal" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;text-align:center">
        <div style="font-family:'Amiri',serif;font-size:3.5rem;color:var(--arabic-gold);margin-bottom:8px">بِسْمِ ٱللَّٰهِ</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:24px">In the name of God</div>
        <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:20px;margin-bottom:16px">
          <div style="font-family:'Amiri',serif;font-size:1.6rem;direction:rtl;color:var(--text);line-height:2">
            ٱقْرَأْ بِٱسْمِ رَبِّكَ ٱلَّذِى خَلَقَ
          </div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-top:8px">"Read in the name of your Lord who created" — Quran 96:1</div>
        </div>
        <div style="font-size:.85rem;color:var(--text-muted)">Start your Quranic journey today with<br/>word-by-word breakdown and audio.</div>
        <a href="register.php" class="btn-primary" style="display:inline-block;margin-top:20px;padding:12px 28px">Start Quranic Track →</a>
      </div>

    </div>
  </div>
</section>

<!-- ============ GAMES SHOWCASE ============ -->
<section class="games" id="games">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:0">
      <div class="section-label">Game Zone</div>
      <h2 class="section-title">Learning through play</h2>
      <p class="section-sub" style="margin:0 auto 0">Every game teaches real Arabic. Every point you earn is real progress.</p>
    </div>

    <?php
    $games = [
      ["🎯","Hangman","Guess Arabic words letter by letter across 181+ vocabulary words","Beginner → Advanced"],
      ["🌳","Root Word Finder","Extract the 3-letter Arabic root from complex derived words","Unique Feature"],
      ["🧩","Sentence Builder","Drag and drop scrambled words into correct grammatical order","Grammar"],
      ["✍️","Calligraphy Atelier","Trace Arabic letters with AI stroke analysis and real-time feedback","AI-Powered"],
      ["🧠","Review Flashcards","Spaced repetition system to lock vocabulary into long-term memory","Memory"],
      ["🔍","Error Correction","Find and fix deliberate grammatical mistakes in sentences","Advanced"],
      ["🔊","Audio Dictation","Listen to a sentence and type exactly what you hear in Arabic","Listening"],
      ["🔗","Vocab Match-Up","Connect Arabic academic terms to their English definitions","Academic"],
      ["📝","Fill-in-the-Blanks","Test conjugations and grammar rules in context","Grammar"],
      ["📖","Reading Comprehension","Read Arabic paragraphs and answer deep comprehension questions","Reading"],
      ["🤖","AI Essay Grader","Write Arabic essays and get instant AI feedback on grammar and style","AI Writing"],
      ["🌀","Verb Conjugator","Master Arabic verb conjugation for all pronouns and tenses","Morphology"],
    ];
    ?>
    <div class="games-grid">
      <?php foreach ($games as $g): ?>
      <div class="game-card reveal">
        <span class="game-emoji"><?= $g[0] ?></span>
        <h3><?= $g[1] ?></h3>
        <p><?= $g[2] ?></p>
        <span class="game-tag"><?= $g[3] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="testimonials" id="testimonials">
  <div class="container">
    <div class="testimonials-header reveal">
      <div class="section-label">Learners Love Faseeh</div>
      <h2 class="section-title">From our community</h2>
    </div>

    <?php
    $testimonials = [
      ["Finally, an Arabic app that teaches the ROOT system! As a linguist, this is the correct way to learn Arabic vocabulary.", "Dr. Aisha M.", "Arabic Linguistics PhD, Cairo University", "⭐⭐⭐⭐⭐"],
      ["The Quranic Arabic track is incredible. I can now read and understand more of the Quran than I ever could after years of trying other apps.", "Yusuf A.", "Student, Malaysia", "⭐⭐⭐⭐⭐"],
      ["Our Islamic school uses Faseeh for our Grade 5-8 students. The gamification keeps them engaged and the progress reports help teachers enormously.", "Ustaz Tariq N.", "Principal, Al-Noor Academy", "⭐⭐⭐⭐⭐"],
      ["The Calligraphy Atelier is unlike anything I've seen. The AI stroke analysis actually corrects how I write Arabic letters in real time.", "Priya K.", "Designer & Arabic learner, India", "⭐⭐⭐⭐⭐"],
      ["I've tried Duolingo, Rosetta Stone, and several others. Faseeh is the only one that actually explains Arabic grammar in a way that makes sense.", "Marcus T.", "Software engineer, Germany", "⭐⭐⭐⭐⭐"],
      ["The leaderboard motivates me every single day. My 14-day streak is the longest I've ever maintained in any language app!", "Fatimah R.", "Medical student, Nigeria", "⭐⭐⭐⭐⭐"],
    ];
    ?>
    <div class="testi-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testi-card reveal">
        <div class="testi-quote">"</div>
        <div class="stars"><?= $t[3] ?></div>
        <p><?= $t[0] ?></p>
        <div class="testi-author">
          <div class="testi-avatar"><?= mb_substr($t[1],0,1) ?></div>
          <div>
            <div class="testi-name"><?= $t[1] ?></div>
            <div class="testi-meta"><?= $t[2] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
<section class="pricing" id="pricing">
  <div class="container">
    <div class="pricing-header reveal">
      <div class="section-label">Simple Pricing</div>
      <h2 class="section-title">Start free, grow at your pace</h2>
      <p class="section-sub" style="margin:0 auto">No hidden fees. Cancel anytime. Unlock your full Arabic potential.</p>
    </div>

    <div class="pricing-grid">
      <?php
      $plans = [
        [
          "Free", "0", "", "Forever free",
          ["Hangman (Beginner level)", "5 Academy modules", "Basic leaderboard", "Word of the day", "Community feed"],
          ["Advanced games", "AI features", "Certificates", "All tracks"],
          false
        ],
        [
          "Premium", "4", ".99", "per month",
          ["Everything in Free", "All 12 game modes", "Quranic Arabic track", "AI Essay Grader", "AI Conversation Partner", "Certificates (all levels)", "Priority support"],
          [],
          true
        ],
        [
          "Lifetime", "49", "", "one-time payment",
          ["Everything in Premium", "All future features", "Lifetime updates", "Early access to new tracks", "Exclusive lifetime badge"],
          [],
          false
        ],
      ];
      foreach ($plans as $pl):
        $featured = $pl[6];
      ?>
      <div class="price-card <?= $featured ? 'featured' : '' ?> reveal">
        <?php if ($featured): ?><div class="price-badge">Most Popular</div><?php endif; ?>
        <div class="price-plan"><?= $pl[0] ?></div>
        <div class="price-amount">
          <sup>$</sup><?= $pl[1] ?><?php if($pl[2]): ?><sup style="font-size:.9rem;margin-top:14px"><?=$pl[2]?></sup><?php endif; ?>
          <span>/<?= $pl[3] ?></span>
        </div>
        <div class="price-period">
          <?= $featured ? 'Billed monthly. Cancel anytime.' : ($pl[0]==='Lifetime' ? 'Pay once, learn forever.' : 'No credit card required.') ?>
        </div>
        <ul class="price-features">
          <?php foreach ($pl[4] as $f): ?>
            <li><?= $f ?></li>
          <?php endforeach; ?>
          <?php foreach ($pl[5] as $f): ?>
            <li class="no"><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="register.php?plan=<?= strtolower($pl[0]) ?>" class="<?= $featured ? 'btn-hero' : 'btn-ghost' ?>" style="width:100%;text-align:center;display:block;padding:13px">
          <?= $pl[0]==='Free' ? 'Get Started Free' : ($pl[0]==='Lifetime' ? 'Get Lifetime Access' : 'Start Premium') ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ CTA ============ -->
<section class="cta-section">
  <div class="container">
    <span class="cta-arabic reveal">ابدأ رحلتك</span>
    <h2 class="reveal">Your Arabic journey starts now</h2>
    <p class="reveal">Join thousands of learners already mastering Arabic with Faseeh.</p>
    <a href="register.php" class="btn-cta-large reveal">
      Start Learning Free — No Credit Card →
    </a>
    <p class="cta-note reveal">Free forever. Upgrade anytime. Cancel anytime.</p>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="container">
    <div class="footer-inner">
      <div class="footer-brand">
        <h3>Fase<span>eh</span></h3>
        <p>The world's most immersive Arabic learning platform — combining games, AI, calligraphy, and cultural depth.</p>
        <div class="footer-arabic">فصيح — تعلَّم العربية</div>
      </div>
      <div>
        <h4>Learn</h4>
        <ul>
          <li><a href="academy.php">Academy</a></li>
          <li><a href="play.php">Game Zone</a></li>
          <li><a href="quran.php">Quranic Arabic</a></li>
          <li><a href="dashboard.php">Dashboard</a></li>
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="about.php">About Us</a></li>
          <li><a href="b2b.php">For Schools</a></li>
          <li><a href="blog.php">Blog</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4>Support</h4>
        <ul>
          <li><a href="faq.php">FAQ</a></li>
          <li><a href="privacy.php">Privacy Policy</a></li>
          <li><a href="terms.php">Terms of Service</a></li>
          <li><a href="mailto:hello@faseeh.com">hello@faseeh.com</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Faseeh. Built with ❤️ for Arabic learners worldwide.</p>
      <div class="footer-socials">
        <a href="#" title="Twitter">𝕏</a>
        <a href="#" title="Instagram">◎</a>
        <a href="#" title="YouTube">▶</a>
        <a href="#" title="TikTok">♪</a>
      </div>
    </div>
  </div>
</footer>

<script>
// ---- Scroll reveal ----
const reveals = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
reveals.forEach(r => observer.observe(r));

// ---- Mobile menu ----
function toggleMenu() {
  const links = document.querySelector('.nav-links');
  const cta   = document.querySelector('.nav-cta');
  if (!links) return;
  const open  = links.style.display === 'flex';
  links.style.cssText = open ? '' : 'display:flex;flex-direction:column;position:fixed;top:68px;left:0;right:0;background:rgba(14,12,30,.97);padding:24px 32px;gap:20px;border-bottom:1px solid rgba(255,255,255,.07);z-index:99';
  if (cta) cta.style.cssText = open ? '' : 'display:flex;flex-direction:column;position:fixed;top:68px;left:0;right:0;padding:0 32px 24px;background:rgba(14,12,30,.97);z-index:98;margin-top:200px';
}

// ---- Track tabs ----
document.querySelectorAll('.track-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.track-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
  });
});
</script>
</body>
</html>
