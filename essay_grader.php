<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Essay Grader - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light)); color: white; min-height: 100vh; display: flex; flex-direction: column; }
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; flex: 1; width: 100%; }

        .prompt-card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; margin-bottom: 25px; text-align: center; }
        .prompt-title { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.6; margin-bottom: 15px; }
        .prompt-text-ar { font-family: 'Amiri', serif; font-size: 2rem; line-height: 1.8; direction: rtl; color: var(--accent); margin-bottom: 10px; }
        .prompt-text-en { font-size: 1rem; opacity: 0.7; }
        .prompt-shuffle { background: none; border: 1px solid var(--glass-border); color: white; padding: 8px 20px; border-radius: 20px; margin-top: 15px; cursor: pointer; font-size: 0.85rem; transition: 0.3s; }
        .prompt-shuffle:hover { background: rgba(255,255,255,0.1); border-color: var(--accent); }

        .essay-area { width: 100%; min-height: 250px; padding: 25px; font-family: 'Amiri', serif; font-size: 1.6rem; line-height: 2; border-radius: 20px; border: 2px solid var(--glass-border); background: rgba(0,0,0,0.3); color: white; direction: rtl; text-align: right; outline: none; resize: vertical; transition: 0.3s; }
        .essay-area:focus { border-color: var(--accent); background: rgba(0,0,0,0.4); }
        .essay-area::placeholder { color: rgba(255,255,255,0.3); }

        .word-counter { text-align: right; font-size: 0.85rem; opacity: 0.5; margin-top: 8px; margin-bottom: 20px; }

        .submit-btn { width: 100%; padding: 18px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border: none; border-radius: 15px; color: white; font-size: 1.2rem; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 30px rgba(242,153,74,0.3); }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 40px rgba(242,153,74,0.4); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* Results */
        #results { display: none; margin-top: 30px; }
        
        .grade-card { background: rgba(0,0,0,0.4); border: 2px solid var(--glass-border); border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 25px; position: relative; overflow: hidden; }
        .grade-card::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(242,153,74,0.1) 0%, transparent 70%); animation: pulse 3s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.1); opacity: 1; } }
        
        .grade-letter { font-size: 6rem; font-weight: 900; position: relative; z-index: 1; }
        .grade-label { font-size: 1.2rem; opacity: 0.7; position: relative; z-index: 1; }
        .xp-earned { margin-top: 15px; font-size: 1rem; color: var(--accent); font-weight: 700; position: relative; z-index: 1; }
        
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; padding: 20px; text-align: center; }
        .stat-val { font-size: 2rem; font-weight: 800; color: var(--accent); }
        .stat-lbl { font-size: 0.75rem; opacity: 0.5; margin-top: 5px; }

        .score-bars { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; margin-bottom: 25px; }
        .score-bars h3 { margin-bottom: 20px; font-size: 1.1rem; }
        .bar-row { margin-bottom: 18px; }
        .bar-label { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; }
        .bar-track { width: 100%; height: 10px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 10px; transition: width 1.5s ease-out; }

        .tips-section { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; margin-bottom: 25px; }
        .tips-section h3 { margin-bottom: 15px; }
        .tip { padding: 12px 18px; border-radius: 12px; margin-bottom: 10px; font-size: 0.9rem; display: flex; align-items: flex-start; gap: 10px; }
        .tip.success { background: rgba(46,204,113,0.15); border: 1px solid rgba(46,204,113,0.3); }
        .tip.warning { background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); }
        .tip.info { background: rgba(52,152,219,0.15); border: 1px solid rgba(52,152,219,0.3); }

        .words-found { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; margin-bottom: 25px; }
        .words-found h3 { margin-bottom: 15px; }
        .word-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .word-tag { padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .word-tag.connector { background: rgba(52,152,219,0.2); border: 1px solid rgba(52,152,219,0.3); color: var(--info); }
        .word-tag.academic { background: rgba(155,89,182,0.2); border: 1px solid rgba(155,89,182,0.3); color: #9b59b6; }

        .try-again-btn { width: 100%; padding: 15px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; color: white; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .try-again-btn:hover { background: rgba(255,255,255,0.1); border-color: var(--accent); }

        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .grade-letter { font-size: 4rem; }
        }
    </style>
</head>
<body>
    <div class="nav"><a href="academic_hub.php">← Back to Hub</a><div style="font-weight: 700; color: var(--accent);">🤖 AI ESSAY GRADER</div></div>
    
    <div class="container">
        <!-- WRITING SECTION -->
        <div id="writing-section">
            <div class="prompt-card">
                <div class="prompt-title">📝 Your Essay Prompt</div>
                <div id="prompt-ar" class="prompt-text-ar"></div>
                <div id="prompt-en" class="prompt-text-en"></div>
                <button class="prompt-shuffle" onclick="shufflePrompt()">🔄 Different Prompt</button>
            </div>
            
            <textarea id="essay-input" class="essay-area" placeholder="ابدأ الكتابة هنا... (Start writing here...)"></textarea>
            <div class="word-counter"><span id="wc">0</span> words</div>
            
            <button id="grade-btn" class="submit-btn" onclick="submitEssay()">🤖 Grade My Essay</button>
        </div>

        <!-- RESULTS SECTION -->
        <div id="results">
            <div class="grade-card" id="grade-card">
                <div class="grade-letter" id="grade-letter"></div>
                <div class="grade-label">Overall Score: <strong id="overall-score"></strong>/100</div>
                <div class="xp-earned" id="xp-display"></div>
            </div>

            <div class="stats-row">
                <div class="stat-card"><div class="stat-val" id="r-words">0</div><div class="stat-lbl">Words</div></div>
                <div class="stat-card"><div class="stat-val" id="r-sentences">0</div><div class="stat-lbl">Sentences</div></div>
                <div class="stat-card"><div class="stat-val" id="r-unique">0</div><div class="stat-lbl">Unique Words</div></div>
                <div class="stat-card"><div class="stat-val" id="r-avg">0</div><div class="stat-lbl">Avg Sentence Length</div></div>
            </div>

            <div class="score-bars">
                <h3>📊 Detailed Breakdown</h3>
                <div class="bar-row"><div class="bar-label"><span>📚 Vocabulary Diversity</span><span id="s-vocab">0</span>%</div><div class="bar-track"><div class="bar-fill" id="b-vocab" style="width:0%; background: var(--info);"></div></div></div>
                <div class="bar-row"><div class="bar-label"><span>🔗 Connector Usage</span><span id="s-conn">0</span>%</div><div class="bar-track"><div class="bar-fill" id="b-conn" style="width:0%; background: var(--success);"></div></div></div>
                <div class="bar-row"><div class="bar-label"><span>🎓 Academic Vocabulary</span><span id="s-acad">0</span>%</div><div class="bar-track"><div class="bar-fill" id="b-acad" style="width:0%; background: #9b59b6;"></div></div></div>
                <div class="bar-row"><div class="bar-label"><span>🏗️ Structure & Organization</span><span id="s-struct">0</span>%</div><div class="bar-track"><div class="bar-fill" id="b-struct" style="width:0%; background: var(--accent);"></div></div></div>
                <div class="bar-row"><div class="bar-label"><span>📏 Length & Depth</span><span id="s-len">0</span>%</div><div class="bar-track"><div class="bar-fill" id="b-len" style="width:0%; background: var(--accent2);"></div></div></div>
            </div>

            <div class="tips-section">
                <h3>💡 AI Tutor Feedback</h3>
                <div id="tips-container"></div>
            </div>

            <div class="words-found" id="words-found-section">
                <h3>🏷️ Detected Keywords</h3>
                <div id="words-tags"></div>
            </div>

            <button class="try-again-btn" onclick="tryAgain()">✍️ Write Another Essay</button>
        </div>
    </div>

    <script>
        const prompts = [
            { ar: "اكتب مقالاً عن أهمية التعليم في بناء المجتمعات الحديثة.", en: "Write an essay about the importance of education in building modern societies." },
            { ar: "ناقش تأثير التكنولوجيا الحديثة على حياتنا اليومية.", en: "Discuss the impact of modern technology on our daily lives." },
            { ar: "ما هو دور الشباب في تحقيق التنمية المستدامة؟", en: "What is the role of youth in achieving sustainable development?" },
            { ar: "اكتب عن التحديات التي تواجه الاقتصاد العالمي في القرن الحادي والعشرين.", en: "Write about the challenges facing the global economy in the 21st century." },
            { ar: "ناقش أهمية التعاون الدولي في حل المشكلات البيئية.", en: "Discuss the importance of international cooperation in solving environmental problems." },
            { ar: "كيف يمكن للثقافة والفنون أن تعزز التفاهم بين الشعوب؟", en: "How can culture and arts promote understanding between peoples?" },
            { ar: "اكتب عن مستقبل الطاقة المتجددة وتأثيرها على البيئة.", en: "Write about the future of renewable energy and its impact on the environment." },
            { ar: "ما هي أهمية الحرية والعدالة في بناء دولة قوية؟", en: "What is the importance of freedom and justice in building a strong nation?" },
            { ar: "ناقش دور الإعلام في تشكيل الرأي العام.", en: "Discuss the role of media in shaping public opinion." },
            { ar: "اكتب عن أهمية البحث العلمي في تطوير المجتمعات.", en: "Write about the importance of scientific research in developing societies." }
        ];

        let currentPromptIdx = 0;

        function shufflePrompt() {
            currentPromptIdx = (currentPromptIdx + 1) % prompts.length;
            document.getElementById('prompt-ar').textContent = prompts[currentPromptIdx].ar;
            document.getElementById('prompt-en').textContent = prompts[currentPromptIdx].en;
        }

        // Word counter
        document.getElementById('essay-input').addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
            document.getElementById('wc').textContent = words.length;
        });

        async function submitEssay() {
            const essay = document.getElementById('essay-input').value.trim();
            if (essay.length < 10) { alert('Please write at least a few sentences!'); return; }
            
            const btn = document.getElementById('grade-btn');
            btn.disabled = true; btn.textContent = '🤖 Analyzing your essay...';

            try {
                const res = await fetch('api/grade_essay.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ essay: essay, prompt_id: currentPromptIdx })
                });
                const data = await res.json();
                
                if (data.error) { alert(data.error); btn.disabled = false; btn.textContent = '🤖 Grade My Essay'; return; }
                
                showResults(data);
            } catch (e) {
                console.error(e);
                alert('Error grading essay. Please try again.');
                btn.disabled = false; btn.textContent = '🤖 Grade My Essay';
            }
        }

        function showResults(data) {
            document.getElementById('writing-section').style.display = 'none';
            document.getElementById('results').style.display = 'block';

            // Grade
            const gc = document.getElementById('grade-card');
            const score = data.overall_score;
            let gradeColor = score >= 80 ? 'var(--success)' : (score >= 60 ? 'var(--accent)' : (score >= 40 ? 'var(--accent2)' : 'var(--danger)'));
            gc.style.borderColor = gradeColor;
            document.getElementById('grade-letter').textContent = data.grade;
            document.getElementById('grade-letter').style.color = gradeColor;
            document.getElementById('overall-score').textContent = score;
            document.getElementById('xp-display').textContent = data.xp_earned > 0 ? `+${data.xp_earned} XP Earned!` : 'Keep practicing to earn XP!';

            // Stats
            document.getElementById('r-words').textContent = data.word_count;
            document.getElementById('r-sentences').textContent = data.sentence_count;
            document.getElementById('r-unique').textContent = data.unique_words;
            document.getElementById('r-avg').textContent = data.avg_sentence_length;

            // Bars (animate)
            setTimeout(() => {
                const s = data.scores;
                setBar('vocab', s.vocabulary); setBar('conn', s.connectors);
                setBar('acad', s.academic); setBar('struct', s.structure); setBar('len', s.length);
            }, 300);

            // Tips
            const tc = document.getElementById('tips-container'); tc.innerHTML = '';
            data.tips.forEach(tip => {
                const icon = tip.type === 'success' ? '✅' : (tip.type === 'warning' ? '⚠️' : 'ℹ️');
                tc.innerHTML += `<div class="tip ${tip.type}">${icon} ${tip.text}</div>`;
            });

            // Word tags
            const wt = document.getElementById('words-tags'); wt.innerHTML = '';
            data.found_connectors.forEach(c => {
                wt.innerHTML += `<span class="word-tag connector">${c.ar} (${c.en})</span>`;
            });
            data.found_academic.forEach(a => {
                wt.innerHTML += `<span class="word-tag academic">${a}</span>`;
            });
            if (data.found_connectors.length === 0 && data.found_academic.length === 0) {
                document.getElementById('words-found-section').style.display = 'none';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function setBar(id, val) {
            document.getElementById('s-' + id).textContent = val;
            document.getElementById('b-' + id).style.width = val + '%';
        }

        function tryAgain() {
            document.getElementById('writing-section').style.display = 'block';
            document.getElementById('results').style.display = 'none';
            document.getElementById('essay-input').value = '';
            document.getElementById('wc').textContent = '0';
            document.getElementById('grade-btn').disabled = false;
            document.getElementById('grade-btn').textContent = '🤖 Grade My Essay';
            shufflePrompt();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Init
        shufflePrompt();
    </script>
</body>
</html>
