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
    <title>Speaking Practice - Faseeh</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="https://code.responsivevoice.org/responsivevoice.js"></script>
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --accent: #f2994a; --accent2: #f2c94c; --gold: #FFD700;
            --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.12);
            --danger: #e74c3c; --success: #2ecc71;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end)); color: white; min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
        .nav { padding: 15px 30px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); }
        .nav a { color: white; text-decoration: none; font-weight: 600; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 20px; flex: 1; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        
        .card { background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); border-radius: 30px; padding: 50px 40px; width: 100%; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.4); position: relative; overflow: hidden; }
        
        .target-box { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; margin-bottom: 40px; }
        .sentence-ar { font-family: 'Amiri', serif; font-size: 2.8rem; line-height: 1.6; direction: rtl; color: var(--accent); margin-bottom: 15px; }
        .sentence-en { font-size: 1.1rem; opacity: 0.7; }
        
        /* Microphone Button */
        .mic-container { position: relative; width: 120px; height: 120px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
        .mic-btn { width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent2)); border: none; font-size: 2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: transform 0.2s; box-shadow: 0 10px 30px rgba(242,153,74,0.4); }
        .mic-btn:hover { transform: scale(1.05); }
        .mic-btn.recording { background: var(--danger); box-shadow: 0 10px 30px rgba(231,76,60,0.5); animation: pulse-danger 1.5s infinite; }
        
        /* Pulse Animation */
        .pulse-ring { position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 2px solid var(--accent); animation: ripple 2s infinite; opacity: 0; display: none; }
        .mic-btn.recording ~ .pulse-ring { display: block; border-color: var(--danger); }
        
        @keyframes ripple { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }
        @keyframes pulse-danger { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }

        .status-text { margin-top: 20px; font-size: 1.1rem; font-weight: 600; opacity: 0.8; height: 30px; }
        .transcript { margin-top: 20px; font-family: 'Amiri', serif; font-size: 2rem; direction: rtl; min-height: 50px; color: white; opacity: 0.9; }
        
        .results-box { margin-top: 30px; padding: 25px; border-radius: 20px; display: none; background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); animation: slideUp 0.5s ease; }
        .score { font-size: 4rem; font-weight: 800; line-height: 1; margin-bottom: 10px; }
        .feedback { font-size: 1.1rem; margin-bottom: 20px; line-height: 1.5; }
        
        .next-btn { padding: 12px 30px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 50px; color: white; cursor: pointer; font-size: 1rem; font-weight: 600; transition: 0.3s; margin-top: 20px; }
        .next-btn:hover { background: rgba(255,255,255,0.1); }
        
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .word { display: inline-block; margin: 0 5px; transition: 0.3s; }
        .word.wrong { color: var(--danger); font-weight: bold; text-decoration: underline; }
        
        .btn-listen { position: absolute; top: 15px; left: 15px; background: var(--glass); border: none; color: white; padding: 8px 15px; border-radius: 20px; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-listen:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>
    <div class="nav">
        <a href="academic_hub.php">← Back to Hub</a>
        <div style="font-weight: 700; color: var(--accent);">🎤 PRONUNCIATION STUDIO</div>
    </div>
    
    <div class="container">
        <div class="card">
            <div id="loading" style="font-size: 1.2rem; opacity: 0.6;">Loading your sentence...</div>
            
            <div id="game-area" style="display: none;">
                <div class="target-box">
                    <button class="btn-listen" onclick="listenToTarget()">🔊 Listen</button>
                    <div id="target-ar" class="sentence-ar"></div>
                    <div id="target-en" class="sentence-en"></div>
                </div>
                
                <div class="mic-container" style="position: relative; width: 100%; height: 160px; display: flex; align-items: center; justify-content: center; overflow: visible; margin-bottom: 20px;">
                    <canvas id="voice-wave" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; opacity: 0; transition: opacity 0.5s;"></canvas>
                    <button id="mic-btn" class="mic-btn" style="position: relative; z-index: 10; margin: 0 auto;">🎙️</button>
                    <div class="pulse-ring" style="z-index: 2;"></div>
                </div>
                
                <div id="status-text" class="status-text">Click the microphone to start reading.</div>
                <div id="transcript" class="transcript"></div>
                
                <div id="results" class="results-box">
                    <div id="score" class="score"></div>
                    <div id="feedback" class="feedback"></div>
                    <button class="next-btn" onclick="loadSentence()">Next Sentence ➔</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTarget = "";
        let recognition = null;
        let isRecording = false;

        // Visualizer variables
        let audioContext = null;
        let analyser = null;
        let dataArray = null;
        let source = null;
        let stream = null;
        let animationFrameId = null;
        
        // Initialize Speech API
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.lang = 'ar-SA';
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            
            recognition.onstart = function() {
                isRecording = true;
                document.getElementById('mic-btn').classList.add('recording');
                document.getElementById('status-text').innerText = "Listening... speak clearly.";
                document.getElementById('transcript').innerText = "";
                document.getElementById('results').style.display = 'none';
                startVisualizer();
            };
            
            recognition.onresult = function(event) {
                let interimTranscript = '';
                let finalTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    } else {
                        interimTranscript += event.results[i][0].transcript;
                    }
                }
                
                document.getElementById('transcript').innerText = finalTranscript || interimTranscript;
            };
            
            recognition.onerror = function(event) {
                console.error("Speech error", event);
                document.getElementById('status-text').innerText = "Microphone error. Try again.";
                stopRecording();
            };
            
            recognition.onend = function() {
                if (isRecording) stopRecording(true); // Automatically stop and grade if silence detected
            };
            
        } else {
            alert("Speech Recognition API is not supported in this browser. Please use Google Chrome.");
        }
        
        document.getElementById('mic-btn').addEventListener('click', () => {
            if (!recognition) return;
            if (isRecording) {
                stopRecording(true);
            } else {
                try { recognition.start(); } catch(e) {}
            }
        });

        // --- AUDIO SPECTRUM VISUALIZER ---
        async function startVisualizer() {
            const canvas = document.getElementById('voice-wave');
            const canvasCtx = canvas.getContext('2d');
            try {
                if (!stream) {
                    stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                }
                
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                analyser = audioContext.createAnalyser();
                analyser.fftSize = 128;
                
                source = audioContext.createMediaStreamSource(stream);
                source.connect(analyser);
                
                const bufferLength = analyser.frequencyBinCount;
                dataArray = new Uint8Array(bufferLength);
                
                canvas.style.opacity = '1';
                
                // Set canvas size matching elements
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                
                function draw() {
                    if (!isRecording) return;
                    animationFrameId = requestAnimationFrame(draw);
                    
                    analyser.getByteFrequencyData(dataArray);
                    
                    canvasCtx.fillStyle = 'rgba(0, 0, 0, 0)';
                    canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    const barWidth = (canvas.width / dataArray.length) * 2.5;
                    let barHeight;
                    let x = 0;
                    const centerY = canvas.height / 2;
                    
                    canvasCtx.shadowBlur = 15;
                    canvasCtx.shadowColor = '#f2994a';
                    
                    const gradient = canvasCtx.createLinearGradient(0, 0, canvas.width, 0);
                    gradient.addColorStop(0, '#f2994a');
                    gradient.addColorStop(0.5, '#f2c94c');
                    gradient.addColorStop(1, '#f2994a');
                    
                    canvasCtx.strokeStyle = gradient;
                    canvasCtx.lineWidth = 3;
                    canvasCtx.lineCap = 'round';
                    
                    canvasCtx.beginPath();
                    
                    for (let i = 0; i < dataArray.length; i++) {
                        barHeight = (dataArray[i] / 255.0) * (canvas.height / 2.2);
                        if (barHeight < 4) barHeight = 2 + Math.random() * 4; // organic idle shake
                        
                        const y = i % 2 === 0 ? centerY - barHeight : centerY + barHeight;
                        
                        if (i === 0) {
                            canvasCtx.moveTo(x, y);
                        } else {
                            canvasCtx.lineTo(x, y);
                        }
                        
                        x += barWidth + 1;
                    }
                    
                    canvasCtx.stroke();
                    canvasCtx.shadowBlur = 0;
                }
                
                draw();
            } catch (err) {
                console.error("Error accessing microphone for visualizer", err);
            }
        }

        function stopVisualizer() {
            const canvas = document.getElementById('voice-wave');
            canvas.style.opacity = '0';
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
            }
            if (audioContext) {
                try { audioContext.close(); } catch(e){}
                audioContext = null;
            }
        }
        
        function stopRecording(submit = false) {
            isRecording = false;
            document.getElementById('mic-btn').classList.remove('recording');
            stopVisualizer();
            try { recognition.stop(); } catch(e) {}
            
            const transcript = document.getElementById('transcript').innerText.trim();
            if (submit && transcript.length > 0) {
                document.getElementById('status-text').innerText = "Grading pronunciation...";
                gradePronunciation(transcript);
            } else {
                document.getElementById('status-text').innerText = "Click the microphone to start reading.";
            }
        }
        
        async function loadSentence() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('results').style.display = 'none';
            document.getElementById('transcript').innerText = '';
            document.getElementById('status-text').innerText = "Click the microphone to start reading.";
            
            try {
                const response = await fetch('api/get_dictation.php');
                const data = await response.json();
                
                if (data.error || data.completed) {
                    document.getElementById('loading').innerText = "You have completed all sentences!";
                    return;
                }
                
                currentTarget = data.sentence_ar;
                document.getElementById('target-ar').innerHTML = renderWords(data.sentence_ar);
                document.getElementById('target-en').innerText = data.translation_en;
                
                document.getElementById('loading').style.display = 'none';
                document.getElementById('game-area').style.display = 'block';
            } catch (e) {
                console.error(e);
            }
        }
        
        function renderWords(sentence) {
            return sentence.split(' ').map((word, idx) => `<span class="word" id="word-${idx}">${word}</span>`).join(' ');
        }
        
        function listenToTarget() {
            if (window.responsiveVoice) {
                responsiveVoice.speak(currentTarget, "Arabic Male");
            }
        }
        
        async function gradePronunciation(transcript) {
            const btn = document.getElementById('mic-btn');
            btn.disabled = true;
            
            try {
                const response = await fetch('api/grade_pronunciation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ target: currentTarget, transcript: transcript })
                });
                const result = await response.json();
                
                if (result.error) {
                    alert(result.error);
                    btn.disabled = false;
                    document.getElementById('status-text').innerText = "Error grading. Try again.";
                    return;
                }
                
                document.getElementById('status-text').innerText = "Analysis Complete!";
                document.getElementById('results').style.display = 'block';
                
                const scoreBox = document.getElementById('score');
                scoreBox.innerText = result.score + '%';
                scoreBox.style.color = result.score >= 80 ? 'var(--success)' : (result.score >= 50 ? 'var(--accent)' : 'var(--danger)');
                
                let feedbackText = result.feedback;
                if (result.xp_earned > 0) feedbackText += `<br><br><span style="color:var(--accent); font-weight:bold;">+${result.xp_earned} XP Earned!</span>`;
                document.getElementById('feedback').innerHTML = feedbackText;
                
                // Highlight wrong words
                const words = currentTarget.split(' ');
                result.mispronounced.forEach(wrongWord => {
                    words.forEach((w, idx) => {
                        // Strip punctuation for matching
                        const cleanW = w.replace(/[.,!?،؟]/g, '');
                        const cleanWrong = wrongWord.replace(/[.,!?،؟]/g, '');
                        if (cleanW === cleanWrong || cleanW.includes(cleanWrong)) {
                            document.getElementById(`word-${idx}`).classList.add('wrong');
                        }
                    });
                });
                
            } catch(e) {
                console.error(e);
                document.getElementById('status-text').innerText = "Network error.";
            }
            btn.disabled = false;
        }
        
        window.onload = loadSentence;
    </script>
</body>
</html>
