<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$type = $_GET['type'] ?? 'reading';
$lesson_id = $_GET['lesson_id'] ?? null;
$uid = $_SESSION['user_id'];

// 1. Fetch all lessons of this type for the sidebar
$stmt = $pdo->prepare("SELECT id, title, level FROM academy_lessons WHERE type = ? ORDER BY id ASC");
$stmt->execute([$type]);
$all_lessons = $stmt->fetchAll();

// 2. Fetch the specific lesson content
if ($lesson_id) {
    $stmt = $pdo->prepare("SELECT * FROM academy_lessons WHERE id = ? AND type = ?");
    $stmt->execute([$lesson_id, $type]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM academy_lessons WHERE type = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$type]);
}
$current_db = $stmt->fetch();

if (!$current_db) {
    die("Lesson not found. Please run seed_academy.php first!");
}

// Map DB data to app logic
$metadata = json_decode($current_db['metadata'] ?? '{}', true);
$current = [
    'title' => $current_db['title'],
    'arabic_title' => $current_db['arabic_title'],
    'content' => $current_db['content'],
    'translation' => $current_db['translation'] ?? '',
    'prompt' => ($type == 'writing') ? $current_db['content'] : '',
    'target_text' => ($type == 'speaking') ? $current_db['content'] : '',
    'audio_text' => ($type == 'listening') ? $current_db['content'] : '',
    'words' => $metadata['words'] ?? [],
    'quiz' => $metadata['quiz'] ?? []
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current['title'] ?> — Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-dark); color: white; min-height: 100vh; }

        .app-container { display: flex; height: 100vh; overflow: hidden; }

        /* --- SIDEBAR --- */
        .sidebar { width: 350px; background: rgba(0,0,0,0.3); border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; padding: 30px; }
        .back-btn { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--accent); font-weight: 600; margin-bottom: 40px; transition: 0.3s; }
        .back-btn:hover { transform: translateX(-5px); }
        .tutor-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 25px; margin-top: auto; }
        .tutor-head { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .tutor-avatar { width: 50px; height: 50px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .tutor-name { font-weight: 700; font-size: 0.9rem; }
        .tutor-msg { font-size: 0.8rem; opacity: 0.7; line-height: 1.5; }

        /* --- MAIN AREA --- */
        .main-content { flex: 1; padding: 60px; overflow-y: auto; background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid)); }
        .module-header { margin-bottom: 50px; }
        .module-type { text-transform: uppercase; letter-spacing: 3px; font-size: 0.8rem; color: var(--accent); font-weight: 700; margin-bottom: 10px; display: block; }
        .module-title { font-size: 2.5rem; font-weight: 800; }

        /* --- SKILL SPECIFIC: READING --- */
        .reading-panel { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px; text-align: center; }
        .arabic-text { font-family: 'Amiri', serif; font-size: 3.5rem; direction: rtl; line-height: 1.8; margin-bottom: 40px; }
        .word-clickable { cursor: pointer; transition: 0.2s; display: inline-block; padding: 0 5px; border-radius: 8px; }
        .word-clickable:hover { background: var(--accent); color: #333; }
        .translation-box { font-size: 1.2rem; opacity: 0.6; font-style: italic; max-width: 700px; margin: 0 auto; }

        /* --- TOOLTIP / INFO --- */
        #word-info {
            position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%);
            background: rgba(255,255,255,0.95); color: #333; padding: 20px 40px;
            border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            display: none; align-items: center; gap: 30px; z-index: 1000; animation: slideUp 0.3s ease;
        }
        .info-item { text-align: center; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; opacity: 0.5; font-weight: 700; }
        .info-val { font-size: 1.1rem; font-weight: 700; }
        .info-val.arabic { font-family: 'Amiri'; font-size: 1.5rem; color: var(--accent); }

        @keyframes slideUp { from { opacity: 0; transform: translate(-50%, 30px); } to { opacity: 1; transform: translate(-50%, 0); } }

        /* --- SKILL SPECIFIC: WRITING --- */
        .writing-panel { max-width: 800px; margin: 0 auto; }
        .writing-area { width: 100%; background: var(--glass); border: 2px solid var(--glass-border); border-radius: 20px; padding: 30px; color: white; font-family: 'Amiri', serif; font-size: 1.8rem; direction: rtl; min-height: 300px; outline: none; transition: 0.3s; }
        .writing-area:focus { border-color: var(--accent); background: rgba(255,255,255,0.08); }
        .btn-action { background: var(--accent); color: #333; border: none; padding: 15px 35px; border-radius: 50px; font-weight: 700; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-action:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(242,153,74,0.3); }

        /* =========================================
           MOBILE RESPONSIVENESS (100% PC SAFE)
           ========================================= */
        @media (max-width: 1000px) {
            .app-container { flex-direction: column; }
            .sidebar { width: 100%; height: auto; border-right: none; border-bottom: 1px solid var(--glass-border); padding: 20px; }
            .tutor-card { display: none; }
            .main-content { padding: 30px 15px; }
            .module-title { font-size: 1.8rem; }
            .module-title span { font-size: 1.2rem !important; display: block; margin-left: 0 !important; margin-top: 5px; }
            .reading-panel { padding: 30px 20px; }
            .arabic-text { font-size: 2.2rem !important; line-height: 1.6; }
            .writing-area { font-size: 1.4rem; padding: 20px; min-height: 200px; }
            .btn-action { width: 100%; padding: 12px 20px; font-size: 0.95rem; }
            #word-info { width: 90%; padding: 20px; gap: 15px; flex-wrap: wrap; justify-content: center; bottom: 20px; }
        }
    </style>
</head>
<body>

    <div class="app-container">
        <div class="sidebar">
            <a href="academy.php" class="back-btn">← Exit to Academy</a>
            
            <h3 style="margin-bottom: 20px;">Curriculum</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; overflow-y: auto; max-height: 400px; padding-right: 10px;" id="lesson-list">
                <?php foreach($all_lessons as $l): ?>
                    <?php 
                    $isActive = ($l['id'] == ($current_db['id'] ?? 0));
                    $bg = $isActive ? 'var(--accent)' : 'var(--glass)';
                    $color = $isActive ? '#333' : 'white';
                    ?>
                    <a href="academy_module.php?type=<?= $type ?>&lesson_id=<?= $l['id'] ?>" 
                       style="text-decoration:none; background: <?= $bg ?>; color: <?= $color ?>; padding: 12px 20px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; transition:0.3s; border: 1px solid var(--glass-border);">
                       <span style="opacity:0.5; font-size:0.6rem; display:block; text-transform:uppercase;"><?= $l['level'] ?></span>
                       <?= $l['title'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="tutor-card" style="margin-top:20px;">
                <div class="tutor-head">
                    <div class="tutor-avatar">👴</div>
                    <div class="tutor-name">Professor Faseeh</div>
                </div>
                <p class="tutor-msg" id="tutor-advice">
                    Welcome to the Studio. Select a lesson from the list above to begin your academic journey.
                </p>
            </div>
        </div>

        <div class="main-content">
            <div class="module-header">
                <span class="module-type"><?= $type ?> Studio</span>
                <h1 class="module-title"><?= $current['title'] ?> <span style="font-family:'Amiri'; opacity:0.4; font-size:1.5rem; margin-left:15px;"><?= $current['arabic_title'] ?></span></h1>
            </div>

            <?php if($type == 'reading'): ?>
                <div class="reading-panel">
                    <div class="arabic-text" id="reading-content" style="font-size: 2.8rem;">
                        <?php 
                        $words = explode(" ", $current['content']);
                        foreach($words as $w) {
                            $clean = preg_replace('/[^\x{0600}-\x{06FF}]/u', '', $w);
                            echo "<span class='word-clickable' onmouseenter='showWordInfo(\"$clean\")'>$w</span> ";
                        }
                        ?>
                    </div>
                    <div class="translation-box" id="translation-text">"<?= $current['translation'] ?>"</div>
                    <button class="btn-action" style="background:transparent; border:1px solid var(--accent); color:var(--accent); margin-top:40px;" onclick="speakText()">🔊 Listen to Verse</button>
                </div>

            <?php elseif($type == 'writing'): ?>
                <div class="writing-panel">
                    <p style="margin-bottom: 20px; opacity:0.8;"><?= $current['prompt'] ?></p>
                    <textarea class="writing-area" id="writing-input" placeholder="اكتب هنا..."></textarea>
                    <button class="btn-action" onclick="analyzeWriting()">Submit to AI Tutor</button>
                    <div id="writing-feedback" style="margin-top:30px; display:none; background:rgba(0,184,148,0.1); border:1px solid var(--success); padding:20px; border-radius:15px;">
                        <h4 style="color:var(--success); margin-bottom:10px;">✅ Professor's Feedback</h4>
                        <p id="feedback-text" style="font-size:0.9rem; line-height:1.6;"></p>
                    </div>
                </div>

            <?php elseif($type == 'speaking'): ?>
                <div class="reading-panel">
                    <p style="margin-bottom: 20px; opacity:0.6;">Listen to the phrase and repeat it clearly.</p>
                    <div class="arabic-text" style="font-size:3rem;"><?= $current['target_text'] ?></div>
                    <div style="display:flex; justify-content:center; gap:20px;">
                        <button class="btn-action" style="background:#5E63BA; color:white;" onclick="speakTarget()">🔊 Hear Model</button>
                        <button class="btn-action" id="mic-btn" onclick="startRecognition()">🎤 Start Speaking</button>
                    </div>
                    <div id="speech-result" style="margin-top:40px; font-size:1.2rem; font-weight:700; display:none;"></div>
                </div>

            <?php elseif($type == 'listening'): ?>
                <div class="reading-panel">
                    <div style="font-size:5rem; margin-bottom:20px;">📻</div>
                    <button class="btn-action" style="margin-bottom:40px;" onclick="playAudio()">▶ Play Audio Clip</button>
                    
                    <div style="text-align:left; max-width:500px; margin:0 auto; background:rgba(255,255,255,0.03); padding:30px; border-radius:20px; border:1px solid var(--glass-border);">
                        <h4 style="margin-bottom:20px; color:var(--accent);">Comprehension Quiz</h4>
                        <p style="margin-bottom:20px;"><?= $current['quiz']['question'] ?></p>
                        <?php foreach($current['quiz']['options'] as $idx => $opt): ?>
                            <label style="display:block; padding:12px; background:var(--glass); margin-bottom:10px; border-radius:10px; cursor:pointer; transition:0.3s;">
                                <input type="radio" name="quiz" value="<?= $idx ?>" style="margin-right:10px;"> <?= $opt ?>
                            </label>
                        <?php endforeach; ?>
                        <button class="btn-action" style="width:100%;" onclick="checkQuiz()">Check Answer</button>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- WORD INFO POPUP (FOR READING) -->
    <div id="word-info">
        <div class="info-item">
            <div class="info-label">Word</div>
            <div class="info-val arabic" id="info-word">-</div>
        </div>
        <div class="info-item">
            <div class="info-label">Root</div>
            <div class="info-val arabic" id="info-root">-</div>
        </div>
        <div class="info-item">
            <div class="info-label">Meaning</div>
            <div class="info-val" id="info-mean">-</div>
        </div>
        <div class="info-item">
            <div class="info-label">Grammar</div>
            <div class="info-val" style="color:var(--success)" id="info-gram">-</div>
        </div>
        <button onclick="document.getElementById('word-info').style.display='none'" style="background:none; border:none; font-size:1.2rem; cursor:pointer; margin-left:20px;">✕</button>
    </div>

    <script>
        const wordData = <?= json_encode($current['words'] ?? []) ?>;

        async function showWordInfo(word) {
            let info = wordData[word];
            
            // If not in lesson metadata, fetch from Universal Dictionary
            if(!info) {
                try {
                    const r = await fetch(`api/dictionary.php?word=${encodeURIComponent(word)}`);
                    info = await r.json();
                } catch(e) { console.error('Dictionary error'); }
            }

            if(info) {
                document.getElementById('info-word').innerText = word;
                document.getElementById('info-root').innerText = info.root || 'Analyzing...';
                document.getElementById('info-mean').innerText = info.mean || 'Academic Term';
                document.getElementById('info-gram').innerText = info.grammar || 'Noun/Verb';
                document.getElementById('word-info').style.display = 'flex';
                
                // Update Professor's advice dynamically
                document.getElementById('tutor-advice').innerText = `I see you are interested in the word "${word}". In this context, it refers to ${info.mean}. Pay close attention to its root ${info.root} to understand similar words.`;
            }
        }

        function speakText() {
            const text = "<?= addslashes($current['content'] ?? '') ?>";
            responsiveVoice.speak(text, "Arabic Male");
        }

        // --- WRITING LOGIC ---
        async function analyzeWriting() {
            const input = document.getElementById('writing-input').value;
            const feedbackDiv = document.getElementById('writing-feedback');
            const feedbackText = document.getElementById('feedback-text');
            const btn = event.target;

            if(!input.trim()) return alert('Please write something first!');
            
            btn.innerText = 'Professor is thinking...';
            btn.disabled = true;

            try {
                const r = await fetch('api/analyze_writing.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ text: input })
                });
                const res = await r.json();
                
                feedbackDiv.style.display = 'block';
                feedbackText.innerText = res.message;
                
                if(res.status === 'warning') {
                    feedbackDiv.style.background = 'rgba(231, 76, 60, 0.1)';
                    feedbackDiv.style.borderColor = 'var(--danger)';
                    feedbackText.style.color = 'var(--danger)';
                } else {
                    feedbackDiv.style.background = 'rgba(0, 184, 148, 0.1)';
                    feedbackDiv.style.borderColor = 'var(--success)';
                    feedbackText.style.color = 'var(--success)';
                }
            } catch(e) { 
                alert('Connection to Professor lost. Please try again!'); 
            } finally {
                btn.innerText = 'Submit to AI Tutor';
                btn.disabled = false;
            }
        }

        // --- SPEAKING LOGIC ---
        function speakTarget() {
            responsiveVoice.speak("<?= addslashes($current['target_text'] ?? '') ?>", "Arabic Male");
        }

        function startRecognition() {
            const btn = document.getElementById('mic-btn');
            const resultDiv = document.getElementById('speech-result');
            
            if (!('webkitSpeechRecognition' in window)) {
                return alert("Your browser doesn't support speech recognition. Please use Chrome.");
            }

            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'ar-SA';
            
            recognition.onstart = () => {
                btn.innerText = '🔴 Listening...';
                btn.style.background = '#e74c3c';
            };

            recognition.onresult = (event) => {
                const speechToText = event.results[0][0].transcript;
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = `You said: <span style="color:var(--accent)">${speechToText}</span>`;
                
                const target = "<?= $current['target_text'] ?? '' ?>";
                if(speechToText.includes(target.split(' ')[0])) {
                    resultDiv.innerHTML += "<br><span style='color:var(--success)'>✨ Great Pronunciation!</span>";
                } else {
                    resultDiv.innerHTML += "<br><span style='color:var(--accent)'>Keep practicing! Focus on the vowels.</span>";
                }
            };

            recognition.onend = () => {
                btn.innerText = '🎤 Start Speaking';
                btn.style.background = 'var(--accent)';
            };

            recognition.start();
        }

        // --- LISTENING LOGIC ---
        function playAudio() {
            responsiveVoice.speak("<?= addslashes($current['audio_text'] ?? '') ?>", "Arabic Male", {rate: 0.85});
        }

        function checkQuiz() {
            const selected = document.querySelector('input[name="quiz"]:checked');
            if(!selected) return alert('Please select an answer!');
            
            if(selected.value == "<?= $current['quiz']['correct'] ?? -1 ?>") {
                alert('🎉 Correct! You understood the report perfectly.');
            } else {
                alert('❌ Not quite. Listen again and look for keywords about the economy.');
            }
        }
    </script>

</body>
</html>
