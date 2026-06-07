<?php
// ai_chat.php — Faseeh AI Arabic Conversation Partner
// Requires session. Calls your backend API endpoint ai_chat_api.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');

// Conversation scenarios
$scenarios = [
  ['id'=>'market',   'icon'=>'🛒', 'title'=>'At the Market',       'ar'=>'في السوق',       'desc'=>'Buy fruits, negotiate prices, ask for directions in Arabic.',   'level'=>'Beginner'],
  ['id'=>'cafe',     'icon'=>'☕', 'title'=>'At a Café',            'ar'=>'في المقهى',      'desc'=>'Order food and drinks, make small talk with the barista.',       'level'=>'Beginner'],
  ['id'=>'intro',    'icon'=>'👋', 'title'=>'Introductions',        'ar'=>'التعارف',        'desc'=>'Introduce yourself, ask about someone\'s background and family.', 'level'=>'Beginner'],
  ['id'=>'doctor',   'icon'=>'🏥', 'title'=>'At the Doctor',        'ar'=>'عند الطبيب',     'desc'=>'Describe symptoms, understand medical advice in Arabic.',         'level'=>'Intermediate'],
  ['id'=>'work',     'icon'=>'💼', 'title'=>'Job Interview',         'ar'=>'مقابلة عمل',    'desc'=>'Practice professional Arabic for interviews and business.',       'level'=>'Intermediate'],
  ['id'=>'travel',   'icon'=>'✈️', 'title'=>'Travel & Directions',  'ar'=>'السفر والاتجاهات','desc'=>'Navigate airports, hotels, and cities in Arabic.',              'level'=>'Intermediate'],
  ['id'=>'news',     'icon'=>'📰', 'title'=>'Discuss the News',     'ar'=>'الأخبار',        'desc'=>'Talk about current events and form opinions in formal Arabic.',   'level'=>'Advanced'],
  ['id'=>'philosophy','icon'=>'💭','title'=>'Philosophy & Ideas',   'ar'=>'الفلسفة والأفكار','desc'=>'Deep conversations about life, knowledge, and meaning.',         'level'=>'Advanced'],
  ['id'=>'quran',    'icon'=>'📖', 'title'=>'Quranic Vocabulary',   'ar'=>'مفردات قرآنية',  'desc'=>'Discuss Quranic words and their meanings in depth.',             'level'=>'Intermediate'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>AI Conversation Partner — Faseeh</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&family=Amiri:wght@400;700&display=swap" rel="stylesheet"/>
<style>
:root{
  --bg:#0e0c1e;--bg-card:#161430;--bg-card2:#1c1a38;
  --border:rgba(255,255,255,.07);--accent:#f5a623;--accent2:#7c5cbf;
  --accent3:#3ecf8e;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0;
  --radius:16px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;height:100vh;display:flex;flex-direction:column;overflow:hidden}

/* NAV */
nav{flex-shrink:0;background:rgba(14,12,30,.95);backdrop-filter:blur(12px);
  border-bottom:1px solid var(--border);padding:0 24px;height:60px;
  display:flex;align-items:center;justify-content:space-between;z-index:10}
.nav-logo{display:flex;align-items:center;gap:8px;font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;text-decoration:none;color:var(--text)}
.nav-logo-icon{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#e8862a);display:flex;align-items:center;justify-content:center;font-size:.8rem}
.nav-logo span{color:var(--accent)}
.nav-back{display:flex;align-items:center;gap:6px;color:var(--muted);text-decoration:none;font-size:.85rem;transition:.2s}
.nav-back:hover{color:var(--text)}
.nav-title{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;color:var(--text)}

/* LAYOUT */
.main{flex:1;display:flex;overflow:hidden}

/* SIDEBAR */
.sidebar{
  width:300px;flex-shrink:0;background:var(--bg-card);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;overflow:hidden;
}
.sidebar-header{padding:20px;border-bottom:1px solid var(--border)}
.sidebar-header h3{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:4px}
.sidebar-header p{font-size:.78rem;color:var(--muted)}
.scenarios-list{flex:1;overflow-y:auto;padding:12px}
.scenario-btn{
  width:100%;text-align:left;background:none;border:1px solid var(--border);
  border-radius:12px;padding:14px;margin-bottom:8px;cursor:pointer;
  color:var(--text);transition:.2s;position:relative;
}
.scenario-btn:hover{border-color:rgba(255,255,255,.15);background:rgba(255,255,255,.03)}
.scenario-btn.active{border-color:var(--accent);background:rgba(245,166,35,.06)}
.scenario-icon{font-size:1.3rem;margin-bottom:6px;display:block}
.scenario-title{font-size:.88rem;font-weight:600;margin-bottom:2px}
.scenario-arabic{font-family:'Amiri',serif;font-size:.95rem;color:var(--gold);margin-bottom:4px}
.scenario-desc{font-size:.75rem;color:var(--muted);line-height:1.4}
.scenario-level{
  position:absolute;top:10px;right:10px;font-size:.65rem;font-weight:700;
  padding:2px 8px;border-radius:50px;
}
.level-Beginner{background:rgba(62,207,142,.1);color:var(--accent3)}
.level-Intermediate{background:rgba(245,166,35,.1);color:var(--accent)}
.level-Advanced{background:rgba(232,93,93,.1);color:#e85d5d}

/* CHAT AREA */
.chat-area{flex:1;display:flex;flex-direction:column;overflow:hidden}
.chat-header{
  padding:16px 24px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
  background:var(--bg-card);
}
.chat-scenario-info{display:flex;align-items:center;gap:12px}
.chat-scenario-icon{font-size:1.6rem}
.chat-scenario-name{font-family:'Syne',sans-serif;font-weight:700}
.chat-scenario-ar{font-family:'Amiri',serif;font-size:.95rem;color:var(--gold)}
.chat-controls{display:flex;gap:8px}
.ctrl-btn{
  padding:6px 14px;border-radius:50px;border:1px solid var(--border);
  background:none;color:var(--muted);font-size:.78rem;cursor:pointer;transition:.2s;
}
.ctrl-btn:hover{border-color:var(--accent);color:var(--accent)}
.ctrl-btn.active-ctrl{background:rgba(245,166,35,.1);border-color:rgba(245,166,35,.3);color:var(--accent)}

/* MESSAGES */
.messages{flex:1;overflow-y:auto;padding:24px;display:flex;flex-direction:column;gap:16px}
.messages::-webkit-scrollbar{width:6px}
.messages::-webkit-scrollbar-track{background:transparent}
.messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}

.msg{display:flex;gap:12px;max-width:85%;animation:msgIn .3s ease}
.msg.user{flex-direction:row-reverse;align-self:flex-end}
.msg.ai{align-self:flex-start}
@keyframes msgIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

.msg-avatar{
  width:36px;height:36px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:1rem;
}
.msg.ai   .msg-avatar{background:linear-gradient(135deg,var(--accent2),#5a3f9e)}
.msg.user .msg-avatar{background:linear-gradient(135deg,var(--accent),#e8862a);color:#1a0f00;font-weight:700;font-size:.8rem}

.msg-bubble{
  padding:12px 16px;border-radius:16px;font-size:.92rem;line-height:1.65;
  max-width:100%;
}
.msg.ai   .msg-bubble{background:var(--bg-card2);border:1px solid var(--border);border-bottom-left-radius:4px}
.msg.user .msg-bubble{background:linear-gradient(135deg,var(--accent2),#5a3f9e);border-bottom-right-radius:4px}

.msg-arabic{
  font-family:'Amiri',serif;font-size:1.2rem;direction:rtl;
  color:var(--gold);margin-bottom:8px;line-height:1.8;
}
.msg-translation{font-size:.82rem;color:var(--muted);margin-bottom:8px;font-style:italic}

/* Correction bubble */
.correction-chip{
  display:inline-flex;align-items:center;gap:6px;margin-top:8px;
  background:rgba(62,207,142,.08);border:1px solid rgba(62,207,142,.2);
  border-radius:50px;padding:4px 12px;font-size:.75rem;color:var(--accent3);cursor:pointer;
}
.correction-chip:hover{background:rgba(62,207,142,.15)}
.correction-detail{
  display:none;margin-top:8px;padding:10px 14px;
  background:rgba(62,207,142,.05);border-radius:10px;
  border:1px solid rgba(62,207,142,.15);font-size:.82rem;
  color:var(--text);line-height:1.6;
}
.correction-detail.show{display:block}

/* Vocab pills */
.vocab-pills{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.vocab-pill{
  background:rgba(124,92,191,.1);border:1px solid rgba(124,92,191,.2);
  color:var(--accent2);padding:3px 10px;border-radius:50px;font-size:.75rem;cursor:pointer;
  display:inline-flex;align-items:center;gap:4px;
}
.vocab-pill:hover{background:rgba(124,92,191,.2)}
.vocab-pill .ar{font-family:'Amiri',serif;font-size:.9rem;color:var(--gold)}

/* Typing indicator */
.typing-indicator{display:flex;align-items:center;gap:4px;padding:14px 16px}
.typing-dot{width:7px;height:7px;border-radius:50%;background:var(--muted);animation:typingBounce 1.2s infinite}
.typing-dot:nth-child(2){animation-delay:.2s}
.typing-dot:nth-child(3){animation-delay:.4s}
@keyframes typingBounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-8px)}}

/* INPUT */
.chat-input-area{
  border-top:1px solid var(--border);padding:16px 24px;
  background:var(--bg-card);flex-shrink:0;
}
.input-row{display:flex;gap:10px;align-items:flex-end;margin-bottom:10px}
.chat-input{
  flex:1;background:var(--bg-card2);border:1px solid var(--border);
  border-radius:14px;padding:12px 16px;color:var(--text);font-family:'DM Sans',sans-serif;
  font-size:.95rem;resize:none;min-height:48px;max-height:120px;
  line-height:1.5;transition:border-color .2s;
}
.chat-input:focus{outline:none;border-color:rgba(245,166,35,.4)}
.chat-input::placeholder{color:var(--muted)}
.send-btn{
  width:44px;height:44px;border-radius:50%;border:none;flex-shrink:0;
  background:linear-gradient(135deg,var(--accent),#e8862a);
  color:#1a0f00;font-size:1.1rem;cursor:pointer;transition:.15s;
  display:flex;align-items:center;justify-content:center;
}
.send-btn:hover{transform:scale(1.05)}
.send-btn:disabled{opacity:.4;transform:none;cursor:not-allowed}
.input-hints{display:flex;gap:8px;flex-wrap:wrap}
.hint-chip{
  background:rgba(255,255,255,.05);border:1px solid var(--border);
  color:var(--muted);padding:5px 12px;border-radius:50px;font-size:.75rem;
  cursor:pointer;transition:.2s;
}
.hint-chip:hover{border-color:rgba(255,255,255,.15);color:var(--text)}
.input-meta{display:flex;justify-content:space-between;align-items:center;margin-top:6px}
.input-meta-left{display:flex;gap:12px;font-size:.75rem;color:var(--muted)}
.arabic-mode-btn{
  background:none;border:1px solid var(--border);color:var(--muted);
  padding:4px 12px;border-radius:50px;font-size:.75rem;cursor:pointer;
  font-family:'Amiri',serif;font-size:.9rem;transition:.2s;
}
.arabic-mode-btn.on{border-color:var(--gold);color:var(--gold);background:rgba(212,168,67,.08)}

/* WELCOME STATE */
.welcome-state{
  flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:32px;text-align:center;gap:16px;
}
.welcome-state .big-icon{font-size:3.5rem}
.welcome-state h2{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem}
.welcome-state p{color:var(--muted);max-width:360px;font-size:.9rem;line-height:1.6}

/* RESPONSIVE */
@media(max-width:768px){
  .sidebar{display:none}
  .sidebar.open{display:flex;position:fixed;inset:60px 0 0 0;width:100%;z-index:20}
  .nav-title{display:none}
}
</style>
</head>
<body>

<nav>
  <a href="/" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <div class="nav-title">🤖 AI Conversation Partner</div>
  <a href="dashboard.php" class="nav-back">← Dashboard</a>
</nav>

<div class="main">

  <!-- SIDEBAR: scenario picker -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h3>Choose a Scenario</h3>
      <p>Practice real Arabic in context</p>
    </div>
    <div class="scenarios-list">
      <?php foreach ($scenarios as $sc): ?>
      <button class="scenario-btn <?= $sc['id']==='market'?'active':'' ?>"
              data-id="<?= $sc['id'] ?>"
              data-title="<?= htmlspecialchars($sc['title']) ?>"
              data-ar="<?= htmlspecialchars($sc['ar']) ?>"
              data-icon="<?= $sc['icon'] ?>"
              data-desc="<?= htmlspecialchars($sc['desc']) ?>"
              data-level="<?= $sc['level'] ?>"
              onclick="selectScenario(this)">
        <span class="scenario-icon"><?= $sc['icon'] ?></span>
        <div class="scenario-title"><?= $sc['title'] ?></div>
        <div class="scenario-arabic"><?= $sc['ar'] ?></div>
        <div class="scenario-desc"><?= $sc['desc'] ?></div>
        <span class="scenario-level level-<?= $sc['level'] ?>"><?= $sc['level'] ?></span>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CHAT -->
  <div class="chat-area">
    <div class="chat-header">
      <div class="chat-scenario-info">
        <div class="chat-scenario-icon" id="hdr-icon">🛒</div>
        <div>
          <div class="chat-scenario-name" id="hdr-title">At the Market</div>
          <div class="chat-scenario-ar" id="hdr-ar">في السوق</div>
        </div>
      </div>
      <div class="chat-controls">
        <button class="ctrl-btn active-ctrl" id="corrections-toggle" onclick="toggleCorrections()">✓ Show Corrections</button>
        <button class="ctrl-btn" id="translation-toggle" onclick="toggleTranslations()">↔ Show Translation</button>
        <button class="ctrl-btn" onclick="clearChat()">🗑 Clear</button>
      </div>
    </div>

    <div class="messages" id="messages">
      <!-- Welcome message injected by JS -->
    </div>

    <div class="chat-input-area">
      <div class="input-row">
        <textarea
          class="chat-input" id="user-input"
          placeholder="Type in English or Arabic... (e.g. 'كم الثمن؟' or 'How much does this cost?')"
          rows="1"
          onkeydown="handleKey(event)"
          oninput="autoResize(this)"></textarea>
        <button class="send-btn" id="send-btn" onclick="sendMessage()">➤</button>
      </div>
      <div class="input-hints" id="hints-bar">
        <!-- Hints injected per scenario -->
      </div>
      <div class="input-meta">
        <div class="input-meta-left">
          <span id="session-turns">0 exchanges</span>
          <span id="words-used">0 Arabic words used</span>
        </div>
        <button class="arabic-mode-btn" id="ar-mode-btn" onclick="toggleArabicMode()">اكتب بالعربية</button>
      </div>
    </div>
  </div>

</div>

<script>
const username    = <?= json_encode($username) ?>;
let currentScenario = 'market';
let showCorrections = true;
let showTranslation = true;
let arabicMode      = false;
let turnCount       = 0;
let arabicWordCount = 0;

// Scenario hints
const scenarioHints = {
  market:  ['كم الثمن؟','أريد كيلو من التفاح','هل عندك...؟','غالي! خفض السعر','شكراً جزيلاً'],
  cafe:    ['قهوة من فضلك','ماذا تنصح؟','الحساب من فضلك','هل يوجد واي فاي؟','ممتاز!'],
  intro:   ['اسمي...','من أين أنت؟','ما عملك؟','تشرفت بمعرفتك','كم عمرك؟'],
  doctor:  ['أشعر بألم في...','منذ متى؟','هل عندك دواء لـ...؟','الجرعة كم؟','شكراً دكتور'],
  work:    ['أتحدث العربية قليلاً','خبرتي في مجال...','راتبي المتوقع...','أحب العمل في فريق','متى أبدأ؟'],
  travel:  ['أين المحطة؟','كم المسافة؟','هل هذا الطريق لـ...؟','أريد تذكرة إلى...','متى يغادر الحافلة؟'],
  news:    ['رأيي أن...','ما تفكيرك في...؟','الوضع معقد','أتفق معك تماماً','لكن من الجهة الأخرى'],
  philosophy:['ما معنى الحياة؟','أعتقد أن...','الحرية والمسؤولية','ما رأيك في...؟','فكرة عميقة'],
  quran:   ['معنى هذه الكلمة','الجذر اللغوي','هل يمكنك الشرح؟','مثال من القرآن','الفرق بين...'],
};

// System prompt per scenario
const systemPrompts = {
  market: `You are a friendly Arabic market seller in Cairo. The user is practicing Arabic with you. 
Conduct a natural conversation about buying goods. Respond primarily in Arabic (with diacritics/tashkeel), 
then provide an English translation below. 
Gently correct any Arabic mistakes the user makes, explaining what was wrong and the correct form.
Format your response as: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or "None"]
Keep responses natural, warm, and encouraging.`,

  cafe: `You are a friendly barista in a Lebanese café. Speak primarily Arabic, help the user order.
Format: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or "None"]`,

  intro: `You are meeting someone for the first time at an Arabic community event. Exchange introductions naturally.
Format: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or "None"]`,

  quran: `You are a knowledgeable Islamic scholar helping someone understand Quranic Arabic.
Explain word meanings, roots, and grammar from the Quran patiently.
Format: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or "None"]`,
};

const defaultSystem = `You are a helpful Arabic language tutor in a conversation scenario. 
Respond in Arabic with translations and gentle corrections.
Format: ARABIC: [arabic text] | TRANSLATION: [english] | CORRECTION: [correction or "None"]`;

let chatHistory = [];

// ── Init ──────────────────────────────────────────────────────
window.addEventListener('load', () => {
  renderWelcome();
  loadHints('market');
});

function renderWelcome() {
  const msgs = document.getElementById('messages');
  msgs.innerHTML = '';
  const greetings = {
    market:  {ar:'أهلاً وسهلاً! تفضل, ماذا تريد اليوم؟', en:'Welcome! Please, what would you like today?'},
    cafe:    {ar:'أهلاً! ماذا تحب أن تطلب؟', en:'Welcome! What would you like to order?'},
    intro:   {ar:'السلام عليكم! اسمي محمد. وأنت؟', en:'Peace be upon you! My name is Mohammed. And you?'},
    doctor:  {ar:'تفضل. ما شكواك اليوم؟', en:'Please come in. What brings you in today?'},
    work:    {ar:'أهلاً. شكراً لحضورك. أخبرني عن نفسك.', en:'Welcome. Thank you for coming. Tell me about yourself.'},
    travel:  {ar:'مرحباً! كيف أساعدك؟', en:'Hello! How can I help you?'},
    news:    {ar:'مرحباً! قرأت الأخبار اليوم؟ ما رأيك؟', en:'Hello! Have you read the news today? What do you think?'},
    philosophy:{ar:'أهلاً بك. أي موضوع يشغل تفكيرك؟', en:'Welcome. What topic is on your mind?'},
    quran:   {ar:'بسم الله. ما الذي تريد أن تتعلمه من القرآن الكريم؟', en:'In the name of God. What would you like to learn from the Holy Quran?'},
  };
  const g = greetings[currentScenario] || greetings.market;
  addMessage('ai', g.ar, g.en, null);
  chatHistory = [{ role:'assistant', content: g.ar + ' (' + g.en + ')' }];
}

function addMessage(role, arabic, translation, correction) {
  const msgs    = document.getElementById('messages');
  const isUser  = role === 'user';
  const div     = document.createElement('div');
  div.className = 'msg ' + role;

  const avatar  = isUser
    ? `<div class="msg-avatar">${username.charAt(0).toUpperCase()}</div>`
    : `<div class="msg-avatar">🤖</div>`;

  let inner = '';
  if (arabic) inner += `<div class="msg-arabic">${arabic}</div>`;
  if (translation && showTranslation) inner += `<div class="msg-translation">${translation}</div>`;
  if (correction && correction !== 'None' && showCorrections && !isUser) {
    inner += `<div class="correction-chip" onclick="toggleCorrection(this)">✏️ Grammar correction (tap to expand)</div>
              <div class="correction-detail">${correction}</div>`;
  }

  div.innerHTML = isUser
    ? `${avatar}<div class="msg-bubble">${inner}</div>`
    : `${avatar}<div class="msg-bubble">${inner}</div>`;

  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;

  if (isUser) {
    turnCount++;
    const arabicWords = (arabic.match(/[\u0600-\u06FF]+/g) || []).length;
    arabicWordCount += arabicWords;
    document.getElementById('session-turns').textContent = turnCount + ' exchange' + (turnCount!==1?'s':'');
    document.getElementById('words-used').textContent    = arabicWordCount + ' Arabic words used';
  }
}

function addTyping() {
  const msgs = document.getElementById('messages');
  const div  = document.createElement('div');
  div.className  = 'msg ai';
  div.id         = 'typing';
  div.innerHTML  = `<div class="msg-avatar">🤖</div>
    <div class="msg-bubble"><div class="typing-indicator">
      <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
    </div></div>`;
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
}

function removeTyping() {
  document.getElementById('typing')?.remove();
}

async function sendMessage() {
  const input = document.getElementById('user-input');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  autoResize(input);
  document.getElementById('send-btn').disabled = true;

  addMessage('user', text, null, null);
  chatHistory.push({ role:'user', content: text });

  addTyping();

  try {
    const sysPrompt = systemPrompts[currentScenario] || defaultSystem;
    const res = await fetch('ai_chat_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        scenario: currentScenario,
        history:  chatHistory,
        system:   sysPrompt,
      }),
    });
    const data = await res.json();
    removeTyping();

    if (data.error) {
      addMessage('ai', '❌ ' + (data.error || 'Error'), null, null);
    } else {
      const reply = data.reply || '';
      chatHistory.push({ role:'assistant', content: reply });

      // Parse the structured response
      const arabicMatch      = reply.match(/ARABIC:\s*(.*?)(?:\s*\||\s*$)/s);
      const translationMatch = reply.match(/TRANSLATION:\s*(.*?)(?:\s*\||\s*$)/s);
      const correctionMatch  = reply.match(/CORRECTION:\s*([\s\S]*)/);

      const ar   = arabicMatch      ? arabicMatch[1].trim()      : reply;
      const tr   = translationMatch ? translationMatch[1].trim() : '';
      const corr = correctionMatch  ? correctionMatch[1].trim()  : null;

      addMessage('ai', ar, tr, corr);
    }
  } catch (err) {
    removeTyping();
    addMessage('ai', '❌ Connection error. Please try again.', null, null);
  }

  document.getElementById('send-btn').disabled = false;
  input.focus();
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function loadHints(scenarioId) {
  const hints   = scenarioHints[scenarioId] || [];
  const bar     = document.getElementById('hints-bar');
  bar.innerHTML = hints.map(h =>
    `<div class="hint-chip" onclick="useHint('${h}')">${h}</div>`
  ).join('');
}

function useHint(text) {
  document.getElementById('user-input').value = text;
  document.getElementById('user-input').focus();
}

function selectScenario(btn) {
  document.querySelectorAll('.scenario-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentScenario = btn.dataset.id;
  document.getElementById('hdr-icon').textContent  = btn.dataset.icon;
  document.getElementById('hdr-title').textContent = btn.dataset.title;
  document.getElementById('hdr-ar').textContent    = btn.dataset.ar;
  loadHints(currentScenario);
  turnCount = 0; arabicWordCount = 0;
  document.getElementById('session-turns').textContent = '0 exchanges';
  document.getElementById('words-used').textContent    = '0 Arabic words used';
  renderWelcome();
}

function toggleCorrection(chip) {
  const detail = chip.nextElementSibling;
  detail.classList.toggle('show');
}

function toggleCorrections() {
  showCorrections = !showCorrections;
  const btn = document.getElementById('corrections-toggle');
  btn.classList.toggle('active-ctrl', showCorrections);
  btn.textContent = showCorrections ? '✓ Show Corrections' : '○ Corrections Off';
}

function toggleTranslations() {
  showTranslation = !showTranslation;
  const btn = document.getElementById('translation-toggle');
  btn.classList.toggle('active-ctrl', showTranslation);
  btn.textContent = showTranslation ? '↔ Show Translation' : '↔ Translation Off';
}

function toggleArabicMode() {
  arabicMode = !arabicMode;
  const btn   = document.getElementById('ar-mode-btn');
  const input = document.getElementById('user-input');
  btn.classList.toggle('on', arabicMode);
  input.dir           = arabicMode ? 'rtl' : 'ltr';
  input.style.fontFamily = arabicMode ? "'Amiri', serif" : "'DM Sans', sans-serif";
  input.style.fontSize   = arabicMode ? '1.1rem' : '.95rem';
  input.placeholder      = arabicMode ? 'اكتب بالعربية هنا...' : "Type in English or Arabic...";
}

function clearChat() {
  turnCount = 0; arabicWordCount = 0; chatHistory = [];
  document.getElementById('session-turns').textContent = '0 exchanges';
  document.getElementById('words-used').textContent    = '0 Arabic words used';
  renderWelcome();
}
</script>
</body>
</html>
