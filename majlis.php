<?php
session_start();
require 'includes/db.php';
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$username = $_SESSION['username'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Majlis — Ustad Faseeh | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php include 'pwa_install.php'; ?>
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <script>
        // TURBO AUDIO TOGGLE
        let currentlyPlayingBtn = null;

        window.playArabic = function(text, btn) {
            // 1. Instant Stop if already playing
            if (currentlyPlayingBtn === btn) {
                stopAudio();
                return;
            }

            // 2. Clear previous line instantly
            if (window.responsiveVoice) responsiveVoice.cancel();
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            if (currentlyPlayingBtn) resetBtn(currentlyPlayingBtn);

            // 3. Fire Speech IMMEDIATELY
            currentlyPlayingBtn = btn;
            btn.innerHTML = "<span>⏹️</span> Stop";
            btn.style.background = "var(--danger)";
            btn.style.color = "#fff";

            try {
                if (window.responsiveVoice && responsiveVoice.voiceSupport()) {
                    responsiveVoice.speak(text, "Arabic Male", {
                        rate: 0.95,
                        onend: () => resetBtn(btn)
                    });
                } else {
                    const msg = new SpeechSynthesisUtterance(text);
                    msg.lang = 'ar-SA';
                    msg.onend = () => resetBtn(btn);
                    window.speechSynthesis.speak(msg);
                }
            } catch (e) {
                const msg = new SpeechSynthesisUtterance(text);
                msg.lang = 'ar-SA';
                msg.onend = () => resetBtn(btn);
                window.speechSynthesis.speak(msg);
            }
        };

        function stopAudio() {
            if (window.responsiveVoice) responsiveVoice.cancel();
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            if (currentlyPlayingBtn) resetBtn(currentlyPlayingBtn);
            currentlyPlayingBtn = null;
        }

        function resetBtn(btn) {
            btn.innerHTML = "<span>🔊</span> Listen to Ustad";
            btn.style.background = "var(--glass)";
            btn.style.color = "white";
            if (currentlyPlayingBtn === btn) currentlyPlayingBtn = null;
        }
    </script>
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.1);
            --success: #00b894; --danger: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; height: 100vh; display: flex; overflow: hidden;
        }

        /* SIDEBAR BRANDING */
        .sidebar {
            width: 300px; background: rgba(0,0,0,0.2); backdrop-filter: blur(20px);
            display: flex; flex-direction: column; padding: 25px;
            border-right: 1px solid var(--glass-border); z-index: 100;
        }
        .new-chat-btn {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: white; border: none; padding: 15px; border-radius: 15px; margin-bottom: 25px;
            cursor: pointer; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 4px 15px rgba(242, 153, 74, 0.3); transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem;
        }
        .new-chat-btn:hover { transform: translateY(-2px); filter: brightness(1.1); }
        
        .history-list { flex: 1; overflow-y: auto; }
        .history-item {
            padding: 12px 15px; border-radius: 12px; cursor: pointer; transition: 0.3s;
            font-size: 0.85rem; margin-bottom: 8px; background: var(--glass);
            border: 1px solid var(--glass-border); opacity: 0.7;
        }
        .history-item:hover { opacity: 1; border-color: var(--accent); background: rgba(255,255,255,0.1); }
        .history-item.active { background: rgba(242,153,74,0.15); color: var(--accent); opacity: 1; border-color: var(--accent); }

        /* MAIN CHAT AREA */
        .chat-main { flex: 1; display: flex; flex-direction: column; position: relative; }
        
        .chat-header {
            padding: 20px 40px; border-bottom: 1px solid var(--glass-border);
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(0,0,0,0.2); backdrop-filter: blur(15px); z-index: 50;
        }
        .ustad-title { font-weight: 900; letter-spacing: 2px; color: var(--accent); font-size: 1.2rem; }
        
        .chat-container {
            flex: 1; overflow-y: auto; padding: 40px 0 160px; scroll-behavior: smooth;
        }
        .chat-inner { max-width: 850px; margin: 0 auto; width: 90%; display: flex; flex-direction: column; gap: 35px; }

        /* MESSAGE BUBBLES */
        .msg { width: 100%; display: flex; flex-direction: column; animation: slideUp 0.4s ease; }
        .msg-user { align-items: flex-end; }
        .msg-ai { align-items: flex-start; }

        .bubble {
            max-width: 85%; padding: 18px 25px; border-radius: 20px;
            font-size: 1rem; line-height: 1.6; position: relative;
            background: var(--glass); border: 1px solid var(--glass-border);
        }
        .msg-user .bubble {
            background: rgba(94, 99, 186, 0.2); border-color: #5E63BA;
            border-bottom-right-radius: 4px;
        }
        .msg-ai .bubble { width: 100%; padding: 0; background: transparent; border: none; }

        .arabic-text {
            font-family: 'Amiri', serif; font-size: 2.2rem; direction: rtl;
            color: #fff; line-height: 1.4; display: block; margin-bottom: 15px;
        }
        .hint-text {
            font-size: 0.95rem; opacity: 0.6; border-left: 3px solid var(--accent);
            padding-left: 15px; font-style: italic;
        }

        /* AUDIO ROW */
        .audio-row { display: flex; align-items: center; gap: 12px; margin-top: 20px; }
        .listen-btn {
            background: var(--glass); border: 1px solid var(--glass-border);
            color: white; padding: 8px 18px; border-radius: 30px;
            font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 8px; text-transform: uppercase;
        }
        .listen-btn:hover { background: var(--accent); color: #000; border-color: var(--accent); }

        /* INPUT BAR BRANDING */
        .input-wrapper {
            position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
            width: 85%; max-width: 800px; z-index: 100;
        }
        .input-bar {
            background: rgba(0,0,0,0.4); backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            padding: 10px 10px 10px 25px; border-radius: 20px;
            display: flex; align-items: center; gap: 15px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .input-bar:focus-within { border-color: var(--accent); }
        .input-bar input {
            flex: 1; background: transparent; border: none; color: white;
            outline: none; font-size: 1rem; font-family: inherit;
        }
        .send-arrow {
            width: 50px; height: 50px; background: var(--accent); color: #000;
            border: none; border-radius: 15px; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .send-arrow:hover { transform: scale(1.05); }

        .thinking { font-size: 0.8rem; opacity: 0.4; margin-left: 20px; margin-bottom: 20px; display: none; }

        .mic-btn.recording { animation: pulse-record 0.8s infinite alternate; background: #c0392b !important; }
        @keyframes pulse-record { 0% { transform: scale(1); box-shadow: 0 0 5px var(--danger); } 100% { transform: scale(1.1); box-shadow: 0 0 20px var(--danger); } }

        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        .stats-chip {
            background: rgba(255,215,0,0.1); border: 1px solid rgba(255,215,0,0.2);
            padding: 6px 18px; border-radius: 50px; color: var(--gold);
            font-size: 0.8rem; font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <button class="new-chat-btn" onclick="startNewChat()">
            <span>✨</span> New Session
        </button>
        <div class="history-list" id="history-list"></div>
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--glass-border);">
            <a href="dashboard.php" style="color: #fff; text-decoration: none; font-size: 0.85rem; opacity: 0.6; display: flex; align-items: center; gap: 10px;">
                <span>←</span> Hub Dashboard
            </a>
        </div>
    </div>

    <!-- MAIN CHAT -->
    <div class="chat-main">
        <header class="chat-header">
            <div class="ustad-title">USTAD FASEEH</div>
            <div id="user-stats-chip" class="stats-chip">Loading...</div>
        </header>

        <div class="chat-container" id="scroll-area">
            <div class="chat-inner" id="chat-box"></div>
            <div class="thinking" id="thinking-indicator">Ustad is reflecting...</div>
        </div>

        <div class="input-wrapper">
            <div class="input-bar">
                <button id="mic-btn" class="send-arrow" style="background:var(--danger); color:white;" onclick="toggleVoice()">🎙️</button>
                <input type="text" id="chat-input" placeholder="Consult with Ustad Faseeh..." autocomplete="off" onkeydown="if(event.key==='Enter') handleSend()">
                <button class="send-arrow" onclick="handleSend()">🏹</button>
            </div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatInput = document.getElementById('chat-input');
        const scrollArea = document.getElementById('scroll-area');
        const historyList = document.getElementById('history-list');
        const geminiKey = '<?= GEMINI_API_KEY ?>';
        const learningTrack = '<?= $_SESSION['learning_track'] ?? 'msa' ?>';
        
        let currentChatId = null;
        let userContext = { username: 'Student', xp: 0 };
        let messageHistory = [];

        // PRE-WARM VOICES FOR WARP SPEED
        if(window.speechSynthesis) window.speechSynthesis.getVoices();

        window.onload = async () => {
            await fetchProfile();
            await fetchHistory();
            if (!currentChatId) startFresh();
        };

        async function fetchProfile() {
            try {
                const res = await fetch('majlis_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'get_ai_context'})
                });
                userContext = await res.json();
                document.getElementById('user-stats-chip').innerText = `${userContext.username.toUpperCase()} • ${userContext.xp} XP`;
            } catch(e) {}
        }

        async function fetchHistory() {
            try {
                const res = await fetch('majlis_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'get_history'})
                });
                const list = await res.json();
                historyList.innerHTML = list.map(item => `
                    <div class="history-item ${item.id == currentChatId ? 'active' : ''}" onclick="loadChat(${item.id}, '${item.title}')">
                        ${item.title}
                    </div>
                `).join('');
            } catch(e) {}
        }

        function startFresh() {
            currentChatId = null;
            chatBox.innerHTML = '';
            renderAI(`أهلاً بك يا ${userContext.username}! أنا أستاذك فصيح. ما هو موضوعنا اليوم؟ [Welcome ${userContext.username}! I am your Ustad Faseeh. What is our topic today?]`);
        }

        async function loadChat(id, title) {
            currentChatId = id;
            chatBox.innerHTML = '<p style="text-align:center; opacity:0.3; margin-top:100px;">Consulting the archives...</p>';
            const res = await fetch('majlis_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'load_chat', conversation_id: id})
            });
            const msgs = await res.json();
            chatBox.innerHTML = '';
            msgs.forEach(m => renderMessage(m.content, m.role, m.hint));
            fetchHistory();
        }

        async function handleSend() {
            const text = chatInput.value.trim();
            if (!text) return;
            renderMessage(text, 'user');
            chatInput.value = '';
            document.getElementById('thinking-indicator').style.display = 'block';

            const saveRes = await fetch('majlis_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'save_message', conversation_id: currentChatId, message: text, role: 'user' })
            });
            const saveData = await saveRes.json();
            const isFirst = !currentChatId;
            currentChatId = saveData.conversation_id;

            let trackDirective = "";
            if (learningTrack === 'quranic') {
                trackDirective = "Your conversation must be strictly themed around classical Quranic Arabic. Use spiritual motifs, classical sentence structures, references to historic theological concepts, and relevant moral wisdom suited for Quranic study.";
            } else {
                trackDirective = "Your conversation must be themed around Modern Standard Arabic (MSA), using modern everyday topics, business conversations, and standard grammar rules.";
            }
            const system = `You are Ustad Faseeh, the wise mentor of Faseeh Academy. Respond in Arabic first, then English hint in brackets [like this]. Always be Ustad Faseeh. ${trackDirective}`;

            try {
                const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${geminiKey}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ contents: [{ parts: [{ text: system + "\n\nUser: " + text }] }] })
                });
                const data = await res.json();
                const response = data.candidates[0].content.parts[0].text;
                renderAI(response);
                if (isFirst) triggerSmartTitle(text);
            } catch (e) {
                setTimeout(() => {
                    renderAI(`أنا معك. لقد قلت: "${text}". كيف يمكننا المتابعة؟ [I am with you. You said: "${text}". How shall we continue?]`);
                }, 800);
            }
            document.getElementById('thinking-indicator').style.display = 'none';
        }

        function renderAI(fullText) {
            const matches = fullText.match(/(.*?)\[(.*?)\]/s);
            const arabic = matches ? matches[1].trim() : fullText;
            const hint = matches ? matches[2].trim() : "";
            renderMessage(arabic, 'ai', hint);
            if (currentChatId) {
                fetch('majlis_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'save_message', conversation_id: currentChatId, message: arabic, role: 'ai', hint: hint })
                }).then(() => { fetchHistory(); fetchProfile(); });
            }
        }

        function renderMessage(text, role, hint = "") {
            const div = document.createElement('div');
            div.className = `msg msg-${role}`;
            
            if (role === 'ai') {
                const bubble = document.createElement('div');
                bubble.className = 'bubble';
                
                const arabicSpan = document.createElement('span');
                arabicSpan.className = 'arabic-text';
                arabicSpan.innerText = text;
                bubble.appendChild(arabicSpan);
                
                if (hint) {
                    const hintSpan = document.createElement('span');
                    hintSpan.className = 'hint-text';
                    hintSpan.innerText = hint;
                    bubble.appendChild(hintSpan);
                }
                
                const audioRow = document.createElement('div');
                audioRow.className = 'audio-row';
                
                const listenBtn = document.createElement('button');
                listenBtn.className = 'listen-btn';
                listenBtn.innerHTML = '<span>🔊</span> Listen to Ustad';
                listenBtn.onclick = function() { window.playArabic(text, this); };
                
                audioRow.appendChild(listenBtn);
                bubble.appendChild(audioRow);
                div.appendChild(bubble);
                
                // AUTO-PLAY VOICE FOR IMMERSION
                setTimeout(() => { window.playArabic(text, listenBtn); }, 600);
            } else {
                div.innerHTML = `<div class="bubble">${text}</div>`;
            }
            
            chatBox.appendChild(div);
            scrollArea.scrollTop = scrollArea.scrollHeight;
        }

        async function triggerSmartTitle(msg) {
            try {
                const prompt = `Short 3-word title for: "${msg}". No quotes.`;
                const url = `https://generativelanguage.googleapis.com/v1/models/gemini-1.0-pro:generateContent?key=${geminiKey}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ contents: [{parts:[{text: prompt}]}] })
                });
                const data = await res.json();
                const title = data.candidates[0].content.parts[0].text.trim().replace(/['"]+/g, '');
                await fetch('majlis_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'update_title', conversation_id: currentChatId, title: title})
                });
                fetchHistory();
            } catch(e) {}
        }

        // --- WEB SPEECH API INTEGRATION ---
        let recognition = null;
        let isRecording = false;
        
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'ar-SA';
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            
            recognition.onstart = function() {
                isRecording = true;
                document.getElementById('mic-btn').classList.add('recording');
                document.getElementById('chat-input').placeholder = "Listening...";
                document.getElementById('chat-input').value = "";
            };
            
            recognition.onresult = function(event) {
                let interimTranscript = '';
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) finalTranscript += event.results[i][0].transcript;
                    else interimTranscript += event.results[i][0].transcript;
                }
                document.getElementById('chat-input').value = finalTranscript || interimTranscript;
            };
            
            recognition.onerror = function() { stopVoice(); };
            recognition.onend = function() { 
                if (isRecording) {
                    stopVoice(); 
                    if (document.getElementById('chat-input').value.trim() !== '') handleSend();
                }
            };
        }
        
        function toggleVoice() {
            if (!recognition) { alert("Voice recording is not supported in this browser."); return; }
            if (isRecording) stopVoice();
            else recognition.start();
        }
        
        function stopVoice() {
            isRecording = false;
            document.getElementById('mic-btn').classList.remove('recording');
            document.getElementById('chat-input').placeholder = "Consult with Ustad Faseeh...";
            try { recognition.stop(); } catch(e) {}
        }
    </script>
</body>
</html>
