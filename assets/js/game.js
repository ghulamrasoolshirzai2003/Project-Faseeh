const wordDisplay = document.getElementById("word-display");
const keyboardDiv = document.getElementById("keyboard");
const hangmanImg = document.getElementById("hangman-img");
const timerText = document.getElementById("timer");
const modal = document.getElementById("result-modal");
const hintDisplay = document.getElementById("hint-display");

const arabicLetters = ["ا", "ب", "ت", "ث", "ج", "ح", "خ", "د", "ذ", "ر", "ز", "س", "ش", "ص", "ض", "ط", "ظ", "ع", "غ", "ف", "ق", "ك", "ل", "م", "ن", "ه", "ة", "و", "ي", "ء", "ئ", "ؤ", "ى", "أ", "إ", "آ"];

// Global Variables
let currentWord, currentAudio, currentRoot, currentWordID, timer;
let timeLeft = 30;
let correctLetters = [];
let wrongCount = 0;
let maxGuesses = 6;

// 1. INIT GAME
const initGame = async () => {
    // RESET LOGIC
    clearInterval(timer);
    timeLeft = 30;
    wrongCount = 0;
    correctLetters = [];
    
    // --- FIX: IMMEDIATELY WIPE ALL MODAL TEXT TO PREVENT GLIMPSE ---
    modal.classList.remove("show");
    document.getElementById("final-word").innerText = ""; 
    document.getElementById("final-root").innerText = "";
    document.getElementById("modal-title").innerText = ""; 

    // Reset Visuals
    hangmanImg.src = `assets/images/hangman-0.svg`;
    timerText.innerText = timeLeft;
    const wrongCountEl = document.getElementById("wrong-count");
    if(wrongCountEl) wrongCountEl.innerText = 0;
    
    wordDisplay.innerHTML = ""; 
    keyboardDiv.innerHTML = ""; 

    try {
        const res = await fetch('api/get_word.php');
        const data = await res.json();

        // LEVEL COMPLETE CHECK
        if(data.completed) {
            hintDisplay.innerText = "🎉 LEVEL COMPLETE! 🎉";
            hintDisplay.style.color = "#2ecc71";
            wordDisplay.innerHTML = `<a href="level_select.php" class="btn-gold" style="text-decoration:none; padding:10px 20px; background:#FFD700; border-radius:8px; color:#333; font-weight:bold;">Choose Next Level</a>`;
            return;
        }

        if(data.error) { hintDisplay.innerText = data.error; return; }

        // STORE DATA
        currentWord = data.arabic_word;
        currentAudio = data.audio_file;
        currentRoot = data.root;
        currentWordID = data.id; 
        
        hintDisplay.innerText = `Translate: "${data.meaning}"`;
        hintDisplay.style.color = ""; // Reset color if it was green from level complete
        
        const audioEl = document.getElementById("snd-word");
        if(audioEl) audioEl.src = `assets/audio/${currentAudio}`;

        // Create Slots
        wordDisplay.innerHTML = currentWord.split("").map(char => {
            if (char === " ") return `<li class="letter-slot filled" style="border:none;"> </li>`;
            return `<li class="letter-slot"></li>`;
        }).join("");

        // Create Keyboard
        arabicLetters.forEach(char => {
            const btn = document.createElement("button");
            btn.innerText = char;
            btn.className = "key-btn";
            btn.onclick = () => handleGuess(btn, char);
            keyboardDiv.appendChild(btn);
        });

        startTimer();

    } catch (e) { console.error("Game Load Error:", e); }
};

// 2. HANDLE GUESS
const handleGuess = (btn, char) => {
    btn.disabled = true;
    const clickSnd = document.getElementById("snd-click");
    if(clickSnd) clickSnd.play();

    if (currentWord.includes(char)) {
        // Correct Guess
        btn.classList.add("correct-key"); // Optional: add a class to style the button
        
        [...currentWord].forEach((val, index) => {
            if (val === char) {
                // Only add to correctLetters if not already there to prevent double-counting
                if(!correctLetters.includes(val)) {
                    correctLetters.push(val);
                }
                const slot = wordDisplay.querySelectorAll("li")[index];
                if(slot) {
                    slot.innerText = val;
                    slot.classList.add("filled");
                }
            }
        });
        
        // Win Condition Check
        const uniqueCharsInWord = [...new Set(currentWord.replace(/ /g, ''))];
        if (uniqueCharsInWord.length === correctLetters.length) {
            setTimeout(() => endGame(true), 300); // Tiny delay so user sees the last letter
        }
    } else {
        // Wrong Guess
        wrongCount++;
        btn.classList.add("wrong-key"); // Optional: style button red
        
        if(wrongCount <= maxGuesses) hangmanImg.src = `assets/images/hangman-${wrongCount}.svg`;
        
        const wrongCountEl = document.getElementById("wrong-count");
        if(wrongCountEl) wrongCountEl.innerText = wrongCount;
        
        if (wrongCount >= maxGuesses) {
            setTimeout(() => endGame(false), 300);
        }
    }
};

// 3. TIMER
const startTimer = () => {
    timer = setInterval(() => {
        timeLeft--;
        timerText.innerText = timeLeft;
        if(timeLeft <= 0) endGame(false);
    }, 1000);
};

// 4. GAME OVER
const endGame = (win) => {
    clearInterval(timer);
    
    // --- PUSH DATA TO MODAL ---
    document.getElementById("final-word").innerText = currentWord;
    document.getElementById("final-root").innerText = currentRoot || "";
    
    const snd = win ? document.getElementById("snd-win") : document.getElementById("snd-lose");
    if(snd) snd.play();
    
    const modalTitle = document.getElementById("modal-title");
    modalTitle.innerText = win ? "MUMTAZ! 🎉" : "Game Over";
    modalTitle.style.color = win ? "#2ecc71" : "#e74c3c";
    
    // Show Modal
    modal.classList.add("show");

    // Update Stats
    fetch('api/update_progress.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
            result: win ? 'win' : 'lose',
            word_id: win ? currentWordID : 0 
        })
    }).then(r => r.json()).then(d => {
        // Update dashboard score if elements exist in the UI
        const scoreVal = document.getElementById("score-val");
        const streakVal = document.getElementById("streak-val");
        if(scoreVal) scoreVal.innerText = d.total_score;
        if(streakVal) streakVal.innerText = d.current_streak;
    }).catch(err => console.error("Update Progress Error:", err));
};

const playAudio = () => {
    const aud = document.getElementById("snd-word");
    if(aud) aud.play();
}

// START
initGame();