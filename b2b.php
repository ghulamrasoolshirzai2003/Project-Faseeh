<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh Academy for Institutions — The Future of Arabic Education</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0e0c1e; --bg-mid: #161430; --bg-end: #1c1a38;
            --accent: #f5a623; --accent2: #7c5cbf; --accent3: #3ecf8e;
            --glass: rgba(255,255,255,0.03); --glass-border: rgba(255,255,255,0.08);
            --text: #f0eeff; --text-muted: #8b87b0;
            --radius: 24px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-start);
            background: radial-gradient(circle at top right, rgba(124,92,191,0.1), transparent), 
                        radial-gradient(circle at bottom left, rgba(245,166,35,0.05), transparent),
                        var(--bg-start);
            color: var(--text); line-height: 1.6; overflow-x: hidden;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 30px; }
        
        /* Navigation */
        nav {
            position: fixed; top: 0; left: 0; right: 0;
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 40px; background: rgba(14, 12, 30, 0.8);
            backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.05);
            z-index: 1000;
        }
        
        /* Hero Section */
        .hero { padding: 160px 0 100px; text-align: center; }
        .hero-badge {
            display: inline-block; padding: 8px 16px; background: rgba(245,166,35,0.1);
            border: 1px solid rgba(245,166,35,0.2); border-radius: 50px;
            color: var(--accent); font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 24px;
        }
        .hero h1 {
            font-family: 'Syne', sans-serif; font-size: clamp(2.5rem, 5vw, 4.2rem);
            font-weight: 800; line-height: 1.1; margin-bottom: 24px;
            background: linear-gradient(to bottom, #fff, #b8b1e0);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .hero p {
            font-size: 1.2rem; color: var(--text-muted); max-width: 800px; margin: 0 auto 40px;
        }
        
        /* Value Proposition Grid */
        .value-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 100px; }
        .value-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            padding: 40px; border-radius: var(--radius); text-align: center;
            transition: 0.3s;
        }
        .value-card:hover { transform: translateY(-10px); border-color: var(--accent); }
        .value-card i { font-size: 3rem; margin-bottom: 20px; display: block; }
        .value-card h3 { font-family: 'Syne', sans-serif; font-size: 1.4rem; margin-bottom: 12px; }
        
        /* Detailed Role Sections */
        .role-section { padding: 80px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .role-section:nth-child(even) { direction: rtl; text-align: right; }
        .role-section:nth-child(even) .role-content { direction: ltr; text-align: left; }
        
        .role-image {
            background: linear-gradient(135deg, #1e1b3a, #251f40);
            border-radius: var(--radius); padding: 40px; border: 1px solid var(--glass-border);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4); position: relative;
        }
        .role-image img { width: 100%; border-radius: 12px; }
        
        .role-label { color: var(--accent); font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }
        .role-title { font-family: 'Syne', sans-serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 20px; }
        .role-list { list-style: none; margin-top: 24px; }
        .role-list li { margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px; font-size: 1.05rem; color: var(--text-muted); }
        .role-list li::before { content: '✓'; color: var(--accent3); font-weight: 900; }

        /* Features Mosaic */
        .mosaic-section { padding: 100px 0; background: rgba(0,0,0,0.2); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); }
        .mosaic-header { text-align: center; margin-bottom: 60px; }
        .mosaic-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .mosaic-item {
            background: var(--glass); border: 1px solid var(--glass-border);
            padding: 30px; border-radius: 20px; transition: 0.3s;
        }
        .mosaic-item h4 { color: var(--accent); margin-bottom: 10px; }
        .mosaic-item p { font-size: 0.85rem; color: var(--text-muted); }

        /* CTA Section */
        .cta-box {
            background: linear-gradient(135deg, var(--accent2), #4a3a8a);
            border-radius: 40px; padding: 80px; text-align: center; margin: 100px 0;
            position: relative; overflow: hidden;
        }
        .cta-box::after {
            content: 'ف'; position: absolute; right: -30px; bottom: -30px;
            font-family: 'Amiri', serif; font-size: 15rem; color: rgba(255,255,255,0.05);
        }
        .btn-large {
            display: inline-block; padding: 20px 50px; background: #fff; color: var(--accent2);
            text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 1.2rem;
            transition: 0.3s; box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        .btn-large:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.3); }

        @keyframes spinLogo { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shineText { to { background-position: 200% center; } }
    </style>
</head>
<body>
    <nav>
      <div style="display: flex; align-items: center; gap: 12px;">
        <div class="mini-icon" style="width: 38px; height: 38px; background: linear-gradient(135deg, #f2994a, #f2c94c); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4);">
          <div style="font-family: 'Amiri', serif; font-size: 18px; color: white;">ف</div>
          <div style="content: ''; position: absolute; width: 26px; height: 26px; border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent; border-radius: 50%; animation: spinLogo 8s linear infinite;"></div>
        </div>
        <h1 style="font-size: 1.2rem; font-weight: 800; margin: 0; background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shineText 3s linear infinite; font-family: 'Syne', sans-serif;">Faseeh</h1>
      </div>
      <a href="index.php" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; border: 1px solid var(--glass-border); padding: 8px 16px; border-radius: 12px; transition: 0.3s;">← Back to Home</a>
    </nav>

    <div class="container">
        <!-- HERO -->
        <section class="hero">
            <div class="hero-badge">Institutional Partner Program</div>
            <h1>The Arabic OS for the<br/>Modern Islamic School</h1>
            <p>Moving beyond PDFs and static apps. Faseeh Academy provides a comprehensive, AI-powered ecosystem designed to transform how your institution teaches, tracks, and masters the Arabic language.</p>
            <a href="contact_sales.php" class="btn-large" style="background: var(--accent); color: #000;">Request Institutional Demo</a>
        </section>

        <!-- VALUE PROP -->
        <div class="value-grid">
            <div class="value-card">
                <i>📊</i>
                <h3>Data-Driven</h3>
                <p>Real-time analytics for every student, class, and grade level.</p>
            </div>
            <div class="value-card">
                <i>🤖</i>
                <h3>AI-Enhanced</h3>
                <p>Automated grading and personalized AI tutors for every child.</p>
            </div>
            <div class="value-card">
                <i>🎮</i>
                <h3>Gamified</h3>
                <p>Engagement-first methodology that turns homework into play.</p>
            </div>
        </div>

        <!-- TEACHERS -->
        <section class="role-section">
            <div class="role-content">
                <div class="role-label">For Your Faculty</div>
                <h2 class="role-title">Empower Your Teachers</h2>
                <p>Traditional grading takes hours. Faseeh gives that time back to your teachers so they can focus on inspiration, not administration.</p>
                <ul class="role-list">
                    <li><strong>Automated Grading:</strong> AI instantly grades Arabic essays and grammar exercises.</li>
                    <li><strong>Classroom Management:</strong> Manage multiple cohorts and track attendance/participation.</li>
                    <li><strong>Insight Reports:</strong> Identify exactly which student is struggling with specific grammar roots.</li>
                    <li><strong>Resource Library:</strong> Instant access to thousands of curated Arabic learning modules.</li>
                </ul>
            </div>
            <div class="role-image">
                <div style="font-size: 5rem; text-align: center;">🏫</div>
                <div style="background:rgba(0,0,0,0.3); padding:20px; border-radius:12px; margin-top:20px;">
                    <div style="height:10px; width:80%; background:var(--accent); border-radius:5px; margin-bottom:10px;"></div>
                    <div style="height:10px; width:60%; background:var(--glass-border); border-radius:5px; margin-bottom:10px;"></div>
                    <div style="height:10px; width:90%; background:var(--glass-border); border-radius:5px;"></div>
                </div>
            </div>
        </section>

        <!-- STUDENTS -->
        <section class="role-section">
            <div class="role-image">
                <div style="font-size: 5rem; text-align: center;">🎮</div>
                <div style="display:flex; gap:10px; margin-top:20px; justify-content:center;">
                    <div style="width:40px; height:40px; background:var(--accent2); border-radius:8px;"></div>
                    <div style="width:40px; height:40px; background:var(--accent3); border-radius:8px;"></div>
                    <div style="width:40px; height:40px; background:var(--accent); border-radius:8px;"></div>
                </div>
            </div>
            <div class="role-content">
                <div class="role-label">For Your Students</div>
                <h2 class="role-title">Learning That Feels Like Play</h2>
                <p>Arabic is often seen as "hard" by students. We change that perception through a gamified journey that rewards curiosity.</p>
                <ul class="role-list">
                    <li><strong>Game Zone:</strong> 10+ games like Root Word Finder and Calligraphy Atelier.</li>
                    <li><strong>Competitive Spirit:</strong> Class and Global leaderboards with Islamic rank titles.</li>
                    <li><strong>Personalized Path:</strong> The AI adapts difficulty based on individual performance.</li>
                    <li><strong>Quranic Track:</strong> Connect language learning directly to the words of the Quran.</li>
                </ul>
            </div>
        </section>

        <!-- PARENTS -->
        <section class="role-section">
            <div class="role-content">
                <div class="role-label">For Your Parents</div>
                <h2 class="role-title">Complete Transparency</h2>
                <p>Parents are the backbone of your school. Faseeh keeps them connected to their child's progress like never before.</p>
                <ul class="role-list">
                    <li><strong>Guardian Guard:</strong> Real-time notifications of student achievements and streaks.</li>
                    <li><strong>Mastery Maps:</strong> Visual reports showing exactly what their child has learned.</li>
                    <li><strong>At-Home Practice:</strong> Seamlessly continue the classroom journey at home.</li>
                    <li><strong>Engagement Insights:</strong> Know exactly how much time is spent on productive learning.</li>
                </ul>
            </div>
            <div class="role-image">
                <div style="font-size: 5rem; text-align: center;">🏠</div>
                <div style="text-align:center; margin-top:20px; color:var(--accent3); font-weight:800;">7 Day Streak! 🔥</div>
            </div>
        </section>

        <!-- FEATURE MOSAIC -->
        <section class="mosaic-section">
            <div class="container">
                <div class="mosaic-header">
                    <h2 class="role-title">Enterprise Features</h2>
                    <p>Designed for scale. Built for excellence.</p>
                </div>
                <div class="mosaic-grid">
                    <div class="mosaic-item">
                        <h4>AI Essay Grader</h4>
                        <p>Instant feedback on syntax, grammar, and vocabulary usage.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>Root system</h4>
                        <p>Proprietary methodology teaching the 3-letter Arabic root logic.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>Calligraphy AI</h4>
                        <p>Real-time stroke analysis for mastering Arabic handwriting.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>Quranic Corpus</h4>
                        <p>Database of the most frequent Quranic words and patterns.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>White-Labeling</h4>
                        <p>Custom branding options for larger school chains.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>API Integration</h4>
                        <p>Connect with your existing School Management Systems (SMS).</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>Offline Mode</h4>
                        <p>Support for learning in areas with limited connectivity.</p>
                    </div>
                    <div class="mosaic-item">
                        <h4>24/7 Support</h4>
                        <p>Dedicated account manager for institutional partners.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <div class="cta-box">
            <h2 style="font-family:'Syne',sans-serif; font-size:3rem; margin-bottom:20px;">Ready to Elevate Your School?</h2>
            <p style="font-size:1.2rem; margin-bottom:40px; opacity:0.9;">Join the growing network of Islamic schools modernizing their Arabic departments with Faseeh.</p>
            <a href="contact_sales.php" class="btn-large">Get Started Now</a>
            <div style="margin-top:24px; font-size:0.9rem; opacity:0.7;">Free setup & curriculum alignment for the first 10 schools.</div>
        </div>

    </div>

    <footer style="padding: 60px 0; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-muted);">
        <p>&copy; <?= date('Y') ?> Faseeh Academy. All Rights Reserved.</p>
    </footer>
</body>
</html>
