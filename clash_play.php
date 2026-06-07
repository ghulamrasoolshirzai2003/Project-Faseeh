<?php
// clash_play.php
session_start();
if (!isset($_SESSION['clash_room_id']) || !isset($_SESSION['clash_player_id'])) {
    header("Location: clash_join.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clash Controller | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
            --red: #eb4d4b; --blue: #3098f3; --yellow: #f0932b; --green: #6ab04c;
            --gold: #FFD700; --glass: rgba(255,255,255,0.06); --glass-border: rgba(255,255,255,0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-mid), var(--bg-end));
            color: white; min-height: 100vh; overflow: hidden;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        .screen-container { width: 100%; max-width: 500px; height: 100vh; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; }

        /* HEADER */
        .player-header { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 15px 0; border-bottom: 1px solid var(--glass-border); }
        .player-nick { font-weight: 800; font-size: 1.1rem; }
        .player-score-chip { background: var(--glass); padding: 5px 15px; border-radius: 50px; font-weight: 700; border: 1px solid var(--glass-border); }

        /* MAIN CARD VIEWS */
        .main-card {
            background: var(--glass); border: 1.5px solid var(--glass-border);
            border-radius: 30px; padding: 40px 20px; width: 100%; text-align: center;
            backdrop-filter: blur(15px); box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            margin: auto 0; display: flex; flex-direction: column; align-items: center;
        }

        .spin-loader { width: 50px; height: 50px; border: 5px solid rgba(255,255,255,0.1); border-top-color: var(--gold); border-radius: 50%; animation: spin 1s infinite linear; margin: 30px 0; }

        /* GRID CONTROLLER FOR ACTIVE QUESTIONS */
        .controller-grid { display: none; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%; height: 65vh; margin: auto 0; }
        .control-btn {
            border: none; border-radius: 20px; color: white; font-size: 3.5rem;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); transition: 0.2s;
            position: relative;
        }
        .control-btn:active { transform: scale(0.95); opacity: 0.8; }
        .control-btn.red { background: var(--red); }
        .control-btn.blue { background: var(--blue); }
        .control-btn.yellow { background: var(--yellow); }
        .control-btn.green { background: var(--green); }

        /* FULLSCREEN FLASH (Correct / Incorrect) */
        .flash-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1000; display: none; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 30px; animation: popFlash 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .flash-overlay.correct { background: var(--green); }
        .flash-overlay.incorrect { background: var(--red); }
        
        .flash-icon { font-size: 6rem; margin-bottom: 20px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2)); }
        .flash-title { font-size: 2.2rem; font-weight: 900; text-transform: uppercase; margin-bottom: 10px; }
        .flash-points { font-size: 1.5rem; font-weight: 800; background: rgba(0,0,0,0.2); padding: 8px 25px; border-radius: 50px; margin-bottom: 20px; }
        .streak-flame { font-size: 1.2rem; font-weight: 700; color: var(--gold); }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes popFlash { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

    <div class="screen-container">
        <!-- HEADER -->
        <header class="player-header">
            <div class="player-nick">👾 <?= htmlspecialchars($_SESSION['clash_nickname'] ?? 'Player') ?></div>
            <div class="player-score-chip" id="score-chip">0 Points</div>
        </header>

        <!-- 1. LOBBY / WAITING STATE -->
        <div class="main-card" id="lobby-card">
            <h2 style="font-weight: 800; font-size: 1.5rem;">Joined Lobby!</h2>
            <p style="opacity: 0.6; margin-top: 5px;">Look at the host screen for your name</p>
            <div class="spin-loader"></div>
            <h3 style="font-weight: 600; opacity: 0.8;">Waiting for battle to start...</h3>
        </div>

        <!-- 2. ANSWER SUBMITTED / WAITING OTHERS STATE -->
        <div class="main-card" id="waiting-others-card" style="display: none;">
            <h2 style="font-weight: 800; font-size: 1.5rem;">Answer Submitted!</h2>
            <div class="spin-loader" style="border-top-color: var(--blue);"></div>
            <h3 style="font-weight: 600; opacity: 0.8;" id="encouraging-quote">Great speed! Waiting for others...</h3>
        </div>

        <!-- 3. CONTROLLER ACTION BUTTONS -->
        <div class="controller-grid" id="controller-grid">
            <button class="control-btn red" onclick="submitChoice(0)">▲</button>
            <button class="control-btn blue" onclick="submitChoice(1)">◆</button>
            <button class="control-btn yellow" onclick="submitChoice(2)">●</button>
            <button class="control-btn green" onclick="submitChoice(3)">■</button>
        </div>

        <!-- FOOTER BAR -->
        <footer style="padding: 15px 0; opacity: 0.4; font-size: 0.75rem; font-weight: 600;">
            FASEEH CLASS CLASH
        </footer>
    </div>

    <!-- CORRECT FLASH OVERLAY -->
    <div class="flash-overlay correct" id="correct-flash">
        <div class="flash-icon">🎉</div>
        <div class="flash-title">MUMTAZ!</div>
        <div class="flash-points" id="correct-points-val">+0 Points</div>
        <div class="streak-flame" id="streak-flame-val">🔥 Answer Streak: 0</div>
    </div>

    <!-- INCORRECT FLASH OVERLAY -->
    <div class="flash-overlay incorrect" id="incorrect-flash">
        <div class="flash-icon">😔</div>
        <div class="flash-title">Incorrect</div>
        <div class="flash-points" style="background: rgba(0,0,0,0.15);">+0 Points</div>
        <p style="opacity: 0.8; font-weight:600;">Streak reset. Better luck next question!</p>
    </div>

    <script>
        // --- CLIENT SYNTH (For instant gameplay sound feedback!) ---
        class ClientSynth {
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
                try {
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
                } catch(e){}
            }
            correct() {
                this.playTone(523.25, 'sine', 0.15, 0.2); // C5
                setTimeout(() => this.playTone(659.25, 'sine', 0.3, 0.2), 100); // E5
            }
            wrong() {
                this.playTone(180, 'triangle', 0.4, 0.3);
            }
        }

        const audio = new ClientSynth();
        document.body.onclick = () => { audio.init(); };

        let currentQuestionIndex = -1;
        let questionActive = false;
        let questionStartTime = 0;
        let currentOptions = [];
        let hasSubmittedCurrent = false;

        const quotes = [
            "Super speed! Your Ustad is proud.",
            "Formidable reflex! Keep it up.",
            "Excellent effort! You are mastering Arabic.",
            "A fine choice! Let's see the result."
        ];

        window.onload = () => {
            // Main gameloop check state
            setInterval(checkRoomState, 1200);
        };

        async function checkRoomState() {
            try {
                const res = await fetch('api/clash_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'check_student_state'})
                });
                const data = await res.json();
                if (data.error) {
                    location.href = 'clash_join.php';
                    return;
                }

                // Update score
                document.getElementById('score-chip').innerText = `${data.player_score} Points`;

                // Handle game state transitions
                if (data.status === 'lobby') {
                    showView('lobby-card');
                    hideFlash();
                } else if (data.status === 'active') {
                    hideFlash();
                    
                    // New question!
                    if (data.current_question_index !== currentQuestionIndex) {
                        currentQuestionIndex = data.current_question_index;
                        currentOptions = data.question.options;
                        questionStartTime = performance.now();
                        hasSubmittedCurrent = false;
                        showView('controller-grid');
                    } else {
                        // Maintain submitted state if already answered
                        if (hasSubmittedCurrent || data.has_answered) {
                            showView('waiting-others-card');
                        } else {
                            showView('controller-grid');
                        }
                    }
                } else if (data.status === 'show_answer') {
                    // Reveal results overlay
                    if (data.has_answered) {
                        if (data.is_correct) {
                            document.getElementById('correct-points-val').innerText = `+${data.score_awarded} Points`;
                            document.getElementById('streak-flame-val').innerText = `🔥 Answer Streak: ${data.player_streak}`;
                            document.getElementById('correct-flash').style.display = 'flex';
                            audio.correct();
                        } else {
                            document.getElementById('incorrect-flash').style.display = 'flex';
                            audio.wrong();
                        }
                    } else {
                        // User ran out of time! Show incorrect state
                        document.getElementById('incorrect-flash').style.display = 'flex';
                        audio.wrong();
                    }
                } else if (data.status === 'finished') {
                    hideFlash();
                    document.getElementById('lobby-card').querySelector('h2').innerText = "Battle Completed! 🏆";
                    document.getElementById('lobby-card').querySelector('p').innerText = "Congratulations on finishing the battle!";
                    document.getElementById('lobby-card').querySelector('.spin-loader').style.display = 'none';
                    document.getElementById('lobby-card').querySelector('h3').innerText = `Final Score: ${data.player_score} Points`;
                    showView('lobby-card');
                }
            } catch(e){}
        }

        function showView(viewId) {
            document.getElementById('lobby-card').style.display = 'none';
            document.getElementById('waiting-others-card').style.display = 'none';
            document.getElementById('controller-grid').style.display = 'none';

            document.getElementById(viewId).style.display = viewId === 'controller-grid' ? 'grid' : 'flex';
        }

        function hideFlash() {
            document.getElementById('correct-flash').style.display = 'none';
            document.getElementById('incorrect-flash').style.display = 'none';
        }

        async function submitChoice(choiceIdx) {
            if (hasSubmittedCurrent) return;
            hasSubmittedCurrent = true;

            const timeTakenSec = (performance.now() - questionStartTime) / 1000.0;
            const chosenValue = currentOptions[choiceIdx];

            // Reroute to waiting view immediately for responsiveness
            document.getElementById('encouraging-quote').innerText = quotes[Math.floor(Math.random() * quotes.length)];
            showView('waiting-others-card');

            try {
                await fetch('api/clash_engine.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'submit_answer',
                        choice: chosenValue,
                        time_taken: timeTakenSec
                    })
                });
            } catch(e){}
        }
    </script>
</body>
</html>
