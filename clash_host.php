<?php
// clash_host.php
session_start();
require 'includes/db.php';

// Teacher security gate
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$level = $_GET['level'] ?? 'beginner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Clash Host | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@700&family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0a052e; --bg-end: #170b4f;
            --red: #eb4d4b; --blue: #3098f3; --yellow: #f0932b; --green: #6ab04c;
            --gold: #f1c40f; --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle, var(--bg-end), var(--bg-start));
            color: white; min-height: 100vh; overflow: hidden;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        .host-container {
            width: 90%; max-width: 1200px; min-height: 80vh;
            background: var(--glass); border: 1.5px solid var(--glass-border);
            border-radius: 40px; padding: 40px; backdrop-filter: blur(20px);
            display: flex; flex-direction: column; align-items: center; justify-content: space-between;
            position: relative; box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        /* Screen 1: Lobby View */
        .lobby-view { display: flex; flex-direction: column; align-items: center; width: 100%; text-align: center; }
        .lobby-title { font-size: 2.2rem; font-weight: 900; letter-spacing: 2px; color: var(--gold); margin-bottom: 20px; }
        .pin-box {
            background: rgba(255,255,255,0.1); border: 2px dashed var(--gold);
            padding: 20px 60px; border-radius: 25px; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(241,196,15,0.15);
        }
        .pin-label { font-size: 0.9rem; font-weight: 600; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px; }
        .pin-number { font-size: 5rem; font-weight: 900; color: var(--gold); letter-spacing: 5px; line-height: 1.1; }
        
        .players-joined-card { width: 100%; max-width: 800px; background: rgba(0,0,0,0.3); border-radius: 25px; padding: 30px; margin-bottom: 30px; min-height: 250px; }
        .players-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; max-height: 200px; overflow-y: auto; }
        .player-chip { background: var(--glass); border: 1px solid var(--glass-border); padding: 10px 15px; border-radius: 50px; font-weight: 700; font-size: 0.95rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5); animation: popJoin 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

        /* Screen 2: Active Question View */
        .question-view { display: none; flex-direction: column; align-items: center; width: 100%; height: 100%; }
        .question-header { display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 30px; }
        .timer-badge { width: 90px; height: 90px; border-radius: 50%; border: 5px solid var(--gold); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 900; background: rgba(0,0,0,0.4); box-shadow: 0 0 20px rgba(241,196,15,0.2); }
        .timer-badge.danger { border-color: var(--red); color: var(--red); animation: shakeTimer 0.5s infinite alternate; }
        .answers-submitted { background: rgba(255,255,255,0.1); padding: 15px 30px; border-radius: 20px; font-weight: 700; text-align: center; }
        .answers-submitted span { font-size: 2.5rem; color: var(--gold); display: block; line-height: 1.1; }

        .arabic-banner { font-family: 'Amiri', serif; font-size: 5rem; color: #fff; margin: 40px 0; text-shadow: 0 10px 30px rgba(0,0,0,0.5); }

        /* Grid of 4 multiple choices on main board */
        .choices-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px; }
        .choice-card {
            padding: 25px 40px; border-radius: 25px; border: 1.5px solid rgba(255,255,255,0.1);
            font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3); text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative; overflow: hidden;
        }
        .choice-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 12px; height: 100%;
        }
        .choice-card.red { background: var(--red); }
        .choice-card.red::before { background: #ff7675; }
        .choice-card.blue { background: var(--blue); }
        .choice-card.blue::before { background: #74b9ff; }
        .choice-card.yellow { background: var(--yellow); }
        .choice-card.yellow::before { background: #ffeaa7; }
        .choice-card.green { background: var(--green); }
        .choice-card.green::before { background: #55efc4; }

        /* Screen 3: Show Answer Results View */
        .results-view { display: none; flex-direction: column; align-items: center; width: 100%; }
        .podium-container { display: flex; align-items: flex-end; justify-content: center; gap: 20px; margin: 40px 0; width: 100%; max-width: 800px; height: 280px; }
        .podium-bar { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; flex: 1; border-radius: 20px 20px 0 0; position: relative; transition: height 1s ease-out; }
        .podium-bar.silver { background: linear-gradient(180deg, #d2dae2, #57606f); height: 0; } /* 2nd */
        .podium-bar.gold { background: linear-gradient(180deg, #ffd700, #b8860b); height: 0; } /* 1st */
        .podium-bar.bronze { background: linear-gradient(180deg, #cd7f32, #8b4513); height: 0; } /* 3rd */
        .podium-name { font-weight: 700; font-size: 1.1rem; position: absolute; top: -35px; white-space: nowrap; text-align: center; }
        .podium-score { font-size: 0.85rem; opacity: 0.8; font-weight: 600; margin-bottom: 10px; }
        .podium-number { font-size: 3rem; font-weight: 900; color: rgba(255,255,255,0.3); }

        /* Action Buttons */
        .action-btn {
            background: linear-gradient(135deg, var(--gold), #d4af37);
            color: #000; border: none; padding: 18px 45px; border-radius: 50px;
            font-size: 1.2rem; font-weight: 900; cursor: pointer; text-transform: uppercase;
            box-shadow: 0 10px 25px rgba(241,196,15,0.3); transition: 0.3s;
        }
        .action-btn:hover { transform: translateY(-4px) scale(1.05); box-shadow: 0 15px 30px rgba(241,196,15,0.4); }

        .chart-container { display: flex; gap: 15px; align-items: flex-end; justify-content: center; height: 180px; width: 100%; max-width: 600px; margin-bottom: 30px; }
        .chart-bar-wrapper { display: flex; flex-direction: column; align-items: center; width: 60px; height: 100%; justify-content: flex-end; }
        .chart-bar { width: 100%; border-radius: 8px 8px 0 0; transition: height 0.6s ease; min-height: 10px; }
        .chart-bar.red { background: var(--red); }
        .chart-bar.blue { background: var(--blue); }
        .chart-bar.yellow { background: var(--yellow); }
        .chart-bar.green { background: var(--green); }

        @keyframes popJoin { 0% { transform: scale(0.6); opacity: 0; } 80% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
        @keyframes shakeTimer { 0% { transform: scale(1) rotate(-5deg); } 100% { transform: scale(1.1) rotate(5deg); } }
    </style>
</head>
<body>

    <div class="host-container">
        <!-- 1. LOBBY VIEW -->
        <div class="lobby-view" id="lobby-view">
            <div class="lobby-title">🕌 FASEEH CLASS CLASH LOBBY</div>
            
            <div class="pin-box">
                <div class="pin-label">Join at Faseeh Academy with PIN</div>
                <div class="pin-number" id="pin-display">----</div>
            </div>

            <div class="players-joined-card">
                <h3 style="margin-bottom: 20px; font-weight: 700; opacity: 0.7;" id="players-count-label">Waiting for players to join (0 joined)...</h3>
                <div class="players-grid" id="players-joined-grid"></div>
            </div>

            <button class="action-btn" onclick="startClash()">⚔️ Start Classroom Battle</button>
        </div>

        <!-- 2. ACTIVE QUESTION VIEW -->
        <div class="question-view" id="question-view">
            <div class="question-header">
                <div class="timer-badge" id="clash-timer">20</div>
                <div style="font-weight: 900; font-size: 1.8rem; letter-spacing: 1px; color: var(--gold);" id="question-number-title">QUESTION 1/10</div>
                <div class="answers-submitted">
                    <span id="clash-answers-count">0</span>
                    Answers Submitted
                </div>
            </div>

            <div class="arabic-banner" id="clash-arabic-word">كِتَاب</div>

            <div class="choices-grid">
                <div class="choice-card red" id="choice-0"><span style="font-size:2rem; margin-right:10px;">▲</span> <span class="choice-val">Option A</span></div>
                <div class="choice-card blue" id="choice-1"><span style="font-size:2rem; margin-right:10px;">◆</span> <span class="choice-val">Option B</span></div>
                <div class="choice-card yellow" id="choice-2"><span style="font-size:2rem; margin-right:10px;">●</span> <span class="choice-val">Option C</span></div>
                <div class="choice-card green" id="choice-3"><span style="font-size:2rem; margin-right:10px;">■</span> <span class="choice-val">Option D</span></div>
            </div>
        </div>

        <!-- 3. RESULTS & PODIUM VIEW -->
        <div class="results-view" id="results-view">
            <div class="lobby-title" style="margin-bottom: 10px;" id="results-headline">QUESTION COMPLETED!</div>
            <p id="correct-answer-reveal" style="font-size: 1.4rem; color: var(--green); font-weight: 700; margin-bottom: 25px;"></p>

            <div style="display: flex; justify-content: space-around; width: 100%; flex-wrap: wrap; gap: 30px;">
                <!-- Bar Chart Results -->
                <div style="display:flex; flex-direction:column; align-items:center;">
                    <h3 style="margin-bottom: 15px; font-weight:600; opacity:0.7;">Class Answers Distribution</h3>
                    <div class="chart-container">
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar red" id="bar-red" style="height: 10%;"></div>
                            <span style="font-size:1.2rem; margin-top:5px;">▲</span>
                        </div>
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar blue" id="bar-blue" style="height: 10%;"></div>
                            <span style="font-size:1.2rem; margin-top:5px;">◆</span>
                        </div>
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar yellow" id="bar-yellow" style="height: 10%;"></div>
                            <span style="font-size:1.2rem; margin-top:5px;">●</span>
                        </div>
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar green" id="bar-green" style="height: 10%;"></div>
                            <span style="font-size:1.2rem; margin-top:5px;">■</span>
                        </div>
                    </div>
                </div>

                <!-- Live Scoreboard Top 3 Podium -->
                <div style="display:flex; flex-direction:column; align-items:center;">
                    <h3 style="margin-bottom: 15px; font-weight:600; opacity:0.7;">Top Class Competitors</h3>
                    <div class="podium-container">
                        <!-- 2nd Place -->
                        <div class="podium-bar silver" id="podium-2">
                            <div class="podium-name" id="podium-name-2">-</div>
                            <div class="podium-score" id="podium-score-2">-</div>
                            <div class="podium-number">2</div>
                        </div>
                        <!-- 1st Place -->
                        <div class="podium-bar gold" id="podium-1">
                            <div class="podium-name" id="podium-name-1">-</div>
                            <div class="podium-score" id="podium-score-1">-</div>
                            <div class="podium-number">1</div>
                        </div>
                        <!-- 3rd Place -->
                        <div class="podium-bar bronze" id="podium-3">
                            <div class="podium-name" id="podium-name-3">-</div>
                            <div class="podium-score" id="podium-score-3">-</div>
                            <div class="podium-number">3</div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="action-btn" id="next-q-btn" onclick="nextQuestion()" style="margin-top: 30px;">Next Question ➔</button>
        </div>
    </div>

    <script>
        // --- 8-BIT SYNTH AUDIO CORE GENERATOR (Pure Web Audio API) ---
        class FaseehSynth {
            constructor() {
                this.ctx = null;
            }
            init() {
                if (!this.ctx) {
                    this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                }
            }
            playTone(freq, type, duration, gainStart) {
                this.init();
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.connect(gain);
                gain.connect(this.ctx.destination);

                osc.type = type;
                osc.frequency.value = freq;
                
                gain.gain.setValueAtTime(gainStart, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + duration);

                osc.start();
                osc.stop(this.ctx.currentTime + duration);
            }
            playChime() {
                // Happy double-tone win chime
                this.playTone(523.25, 'sine', 0.2, 0.2); // C5
                setTimeout(() => this.playTone(659.25, 'sine', 0.4, 0.2), 150); // E5
            }
            playBuzzer() {
                // Sad low synth buzz
                this.playTone(150, 'sawtooth', 0.5, 0.3);
            }
            playTick() {
                // High short tick
                this.playTone(800, 'triangle', 0.04, 0.15);
            }
            playLobbyMusic() {
                this.init();
                // Synthesise an upbeat continuous loop!
                let notes = [261.63, 293.66, 329.63, 349.23, 392.00, 440.00, 493.88, 523.25];
                let beatIdx = 0;
                this.lobbyInterval = setInterval(() => {
                    // Play sweet subtle bassline
                    const note = notes[beatIdx % notes.length];
                    this.playTone(note / 2, 'triangle', 0.3, 0.1);
                    if (beatIdx % 2 === 0) {
                        this.playTone(note * 1.5, 'sine', 0.15, 0.05); // High chord
                    }
                    beatIdx++;
                }, 250);
            }
            stopLobbyMusic() {
                if (this.lobbyInterval) {
                    clearInterval(this.lobbyInterval);
                }
            }
        }

        const synth = new FaseehSynth();
        const level = '<?= $level ?>';
        
        let roomId = null;
        let pin = null;
        let lobbyPoll = null;
        let activeQuestionPoll = null;
        let timeRemaining = 20;
        let questionTimer = null;
        let totalPlayers = 0;
        let currentQuestion = null;

        // Initialize Lobby
        window.onload = async () => {
            // Trigger browser audio pre-activation
            document.body.onclick = () => { synth.init(); };

            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'host_room', level: level})
            });
            const data = await res.json();
            if (data.success) {
                roomId = data.room_id;
                pin = data.pin;
                document.getElementById('pin-display').innerText = pin;
                
                // Play live lobby background loop
                synth.playLobbyMusic();
                
                // Start polling players
                lobbyPoll = setInterval(pollPlayers, 1500);
            } else {
                alert("Error launching Class Clash: " + data.error);
            }
        };

        async function pollPlayers() {
            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_players', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                const players = data.players;
                totalPlayers = players.length;
                document.getElementById('players-count-label').innerText = `Waiting for players to join (${totalPlayers} joined)...`;
                
                document.getElementById('players-joined-grid').innerHTML = players.map(p => `
                    <div class="player-chip">👾 ${p.nickname}</div>
                `).join('');
            }
        }

        async function startClash() {
            if (totalPlayers === 0) {
                alert("Cannot start a battle with 0 players in lobby!");
                return;
            }
            synth.stopLobbyMusic();
            clearInterval(lobbyPoll);

            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'start_room', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                loadQuestion();
            }
        }

        async function loadQuestion() {
            document.getElementById('lobby-view').style.display = 'none';
            document.getElementById('results-view').style.display = 'none';
            document.getElementById('question-view').style.display = 'flex';

            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_room_state', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                currentQuestion = data.question;
                document.getElementById('question-number-title').innerText = `QUESTION ${data.current_question_index + 1}/10`;
                document.getElementById('clash-arabic-word').innerText = currentQuestion.arabic_word;
                document.getElementById('clash-answers-count').innerText = "0";

                // Populate choices
                currentQuestion.options.forEach((opt, idx) => {
                    document.getElementById(`choice-${idx}`).querySelector('.choice-val').innerText = opt;
                });

                // Start Timer
                timeRemaining = 20;
                document.getElementById('clash-timer').innerText = timeRemaining;
                document.getElementById('clash-timer').classList.remove('danger');

                // Active Question poll for answers count
                activeQuestionPoll = setInterval(pollAnswerCounts, 1000);

                questionTimer = setInterval(() => {
                    timeRemaining--;
                    document.getElementById('clash-timer').innerText = timeRemaining;
                    synth.playTick(); // Tick sound!

                    if (timeRemaining <= 5) {
                        document.getElementById('clash-timer').classList.add('danger');
                    }

                    if (timeRemaining <= 0) {
                        endQuestion();
                    }
                }, 1000);
            }
        }

        async function pollAnswerCounts() {
            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_room_state', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('clash-answers-count').innerText = data.answers_count;
                // If all active players answered, end question early!
                if (data.answers_count >= totalPlayers && totalPlayers > 0) {
                    endQuestion();
                }
            }
        }

        async function endQuestion() {
            clearInterval(questionTimer);
            clearInterval(activeQuestionPoll);
            synth.playChime(); // Play resolution chime

            // Trigger "show_answer" on backend
            await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'show_answer', room_id: roomId})
            });

            // Show results view
            document.getElementById('question-view').style.display = 'none';
            document.getElementById('results-view').style.display = 'flex';

            // Get answers distribution and scoreboard podium
            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_room_state', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                const dist = data.distribution;
                
                // Set bar charts heights
                currentQuestion.options.forEach((opt, idx) => {
                    const count = dist[opt] || 0;
                    const pct = totalPlayers > 0 ? (count / totalPlayers) * 100 : 0;
                    const barId = ['bar-red', 'bar-blue', 'bar-yellow', 'bar-green'][idx];
                    document.getElementById(barId).style.height = `${pct + 10}%`; // at least 10% showing
                });

                document.getElementById('correct-answer-reveal').innerText = `✅ Correct Answer: "${currentQuestion.correct_meaning}"`;

                // Fetch Scoreboard rankings
                fetchScoreboard();
            }
        }

        async function fetchScoreboard() {
            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get_players', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                const players = data.players;
                
                // Fill Podium
                if (players[0]) {
                    document.getElementById('podium-name-1').innerText = players[0].nickname;
                    document.getElementById('podium-score-1').innerText = `${players[0].score} pts (🔥${players[0].streak})`;
                    document.getElementById('podium-1').style.height = '180px';
                }
                if (players[1]) {
                    document.getElementById('podium-name-2').innerText = players[1].nickname;
                    document.getElementById('podium-score-2').innerText = `${players[1].score} pts`;
                    document.getElementById('podium-2').style.height = '130px';
                } else {
                    document.getElementById('podium-2').style.height = '0px';
                }
                if (players[2]) {
                    document.getElementById('podium-name-3').innerText = players[2].nickname;
                    document.getElementById('podium-score-3').innerText = `${players[2].score} pts`;
                    document.getElementById('podium-3').style.height = '90px';
                } else {
                    document.getElementById('podium-3').style.height = '0px';
                }
            }
        }

        async function nextQuestion() {
            const res = await fetch('api/clash_engine.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'next_question', room_id: roomId})
            });
            const data = await res.json();
            if (data.success) {
                if (data.finished) {
                    // Show final podium and redirect
                    document.getElementById('results-headline').innerText = "🏆 CLASS CLASH COMPLETED!";
                    document.getElementById('correct-answer-reveal').innerText = "Congratulations to the winners!";
                    document.getElementById('next-q-btn').innerText = "Finish Clash";
                    document.getElementById('next-q-btn').onclick = () => { location.href = 'dashboard.php'; };
                } else {
                    loadQuestion();
                }
            }
        }
    </script>
</body>
</html>
