<?php
// conversation.php — Faseeh AI Conversation Lab
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$username = htmlspecialchars($_SESSION['username'] ?? 'Learner');

$scenarios = [
  ['id'=>'market',   'icon'=>'🛒', 'title'=>'At the Market',       'ar'=>'في السوق',       'desc'=>'Buy fruits, negotiate prices, and ask for directions.', 'level'=>'Beginner'],
  ['id'=>'cafe',     'icon'=>'☕', 'title'=>'At a Café',            'ar'=>'في المقهى',      'desc'=>'Order food and drinks, make small talk with the barista.', 'level'=>'Beginner'],
  ['id'=>'intro',    'icon'=>'👋', 'title'=>'Introductions',        'ar'=>'التعارف',        'desc'=>'Introduce yourself, ask about family and background.', 'level'=>'Beginner'],
  ['id'=>'work',     'icon'=>'💼', 'title'=>'Job Interview',         'ar'=>'مقابلة عمل',    'desc'=>'Practice professional Arabic for interviews.', 'level'=>'Intermediate'],
  ['id'=>'quran',    'icon'=>'📖', 'title'=>'Quranic Study',        'ar'=>'دراسة قرآنية',  'desc'=>'Discuss meanings and roots of Quranic words.', 'level'=>'Intermediate'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Conversation Lab — Faseeh</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet"/>
<?php include 'pwa_install.php'; ?>
<style>
:root {
    --bg: #0f0c29; --bg-mid: #302b63; --bg-end: #24243e;
    --bg-card: rgba(255,255,255,0.06); --bg-card2: rgba(255,255,255,0.1);
    --border: rgba(255,255,255,0.1); --accent: #f2994a; --accent2: #f2c94c;
    --gold: #FFD700; --text: #f0eeff; --muted: #8b87b0; --radius: 20px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
    background: linear-gradient(135deg, var(--bg), var(--bg-mid), var(--bg-end));
    color: var(--text); font-family: 'Poppins', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden;
}
nav {
    flex-shrink: 0; background: rgba(14,12,30,0.85); backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border); padding: 0 32px; height: 65px;
    display: flex; align-items: center; justify-content: space-between; z-index: 100;
}
.nav-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.2rem;color:var(--text);text-decoration:none}
.nav-logo-icon{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Amiri', serif;}

.layout{flex:1; display:flex; overflow:hidden}

.sidebar{
  width:320px; background:rgba(0,0,0,0.2); border-right:1px solid var(--border);
  display:flex; flex-direction:column; overflow:hidden;
}
.sidebar-head{padding:25px; border-bottom:1px solid var(--border)}
.sidebar-head h2{font-size:1rem; font-weight:800; color:var(--accent); text-transform:uppercase; letter-spacing:1px}
.scenarios{flex:1; overflow-y:auto; padding:15px}
.scenario-btn{
  width:100%; text-align:left; background:var(--bg-card); border:1px solid var(--border);
  border-radius:15px; padding:15px; margin-bottom:12px; cursor:pointer; color:var(--text); transition:.3s;
}
.scenario-btn:hover{background:rgba(255,255,255,0.1); border-color:var(--accent)}
.scenario-btn.active{border-color:var(--accent); background:rgba(242,153,74,0.1)}
.scenario-btn .icon{font-size:1.5rem; margin-bottom:8px; display:block}
.scenario-btn .ar{font-family:'Amiri',serif; color:var(--gold); display:block; margin-top:2px}

.chat-area{flex:1; display:flex; flex-direction:column; background:rgba(0,0,0,0.1)}
.messages{flex:1; overflow-y:auto; padding:30px; display:flex; flex-direction:column; gap:20px}
.msg{display:flex; gap:12px; max-width:85%}
.msg.user{align-self:flex-end; flex-direction:row-reverse}
.msg.ai{align-self:flex-start}
.bubble{
  padding:15px 20px; border-radius:20px; font-size:.95rem; line-height:1.6;
  background:var(--bg-card); border:1px solid var(--border); position:relative;
}
.user .bubble{background:rgba(242,153,74,0.15); border-color:var(--accent)}
.msg-arabic{font-family:'Amiri',serif; font-size:1.6rem; color:var(--gold); direction:rtl; margin-bottom:10px; display:block}
.listen-btn{
  background:var(--bg-card2); border:1px solid var(--border); color:#fff;
  padding:5px 12px; border-radius:50px; font-size:.75rem; cursor:pointer; transition:.2s;
  display:inline-flex; align-items:center; gap:6px; margin-top:10px;
}
.listen-btn:hover{background:var(--accent); color:#000}

.input-area{
  padding:25px; background:rgba(0,0,0,0.3); border-top:1px solid var(--border);
}
.input-wrap{
  max-width:900px; margin:0 auto; display:flex; gap:15px; align-items:center;
}
.chat-input{
  flex:1; background:var(--bg-card); border:1px solid var(--border); border-radius:15px;
  padding:15px 20px; color:#fff; font-family:inherit; font-size:1rem; outline:none; transition:.3s;
}
.chat-input:focus{border-color:var(--accent)}
.send-btn{
  width:50px; height:50px; border-radius:50%; background:var(--accent); border:none;
  color:#000; font-size:1.2rem; cursor:pointer; transition:.3s; flex-shrink:0;
}
.send-btn:hover{transform:scale(1.1)}

.typing{display:flex; gap:5px; padding:10px}
.dot{width:8px; height:8px; background:var(--muted); border-radius:50%; animation:bounce 1.4s infinite ease-in-out}
.dot:nth-child(1){animation-delay:-0.32s}
.dot:nth-child(2){animation-delay:-0.16s}
@keyframes bounce{0%,80%,100%{transform:scale(0)}40%{transform:scale(1)}}
</style>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo"><div class="nav-logo-icon">ف</div>Fase<span>eh</span></a>
  <a href="dashboard.php" style="color:var(--muted);text-decoration:none;font-size:.875rem;font-weight:600">← Hub Dashboard</a>
</nav>

<div class="layout">
  <div class="sidebar">
    <div class="sidebar-head"><h2>Conversation Scenarios</h2></div>
    <div class="scenarios">
      <?php foreach ($scenarios as $s): ?>
      <button class="scenario-btn <?= $s['id']==='market'?'active':'' ?>" onclick="selectScenario('<?= $s['id'] ?>', this)">
        <span class="icon"><?= $s['icon'] ?></span>
        <strong><?= $s['title'] ?></strong>
        <span class="ar"><?= $s['ar'] ?></span>
        <div style="font-size:.7rem; color:var(--muted); margin-top:5px"><?= $s['desc'] ?></div>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="chat-area">
    <div class="messages" id="messages">
      <div class="msg ai">
        <div class="bubble">
          <span class="msg-arabic">أهلاً بك في فصيح! اختر موضوعاً لنبدأ المحادثة.</span>
          Welcome! Pick a scenario on the left and let's start practicing.
        </div>
      </div>
    </div>
    <div class="input-area">
      <div class="input-wrap">
        <input type="text" id="chat-input" class="chat-input" placeholder="Type your message in Arabic or English..." onkeypress="if(event.key==='Enter') sendMessage()">
        <button class="send-btn" onclick="sendMessage()">➤</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>
<script>
let currentScenario = 'market';
let chatHistory = [];

function selectScenario(id, btn) {
  document.querySelectorAll('.scenario-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentScenario = id;
  document.getElementById('messages').innerHTML = '<div class="msg ai"><div class="bubble">Scenario changed to: <strong>' + id + '</strong>. How can I help you?</div></div>';
  chatHistory = [];
}

async function sendMessage() {
  const input = document.getElementById('chat-input');
  const text = input.value.trim();
  if(!text) return;

  input.value = '';
  addMsg('user', text);
  chatHistory.push({role: 'user', content: text});

  const typing = document.createElement('div');
  typing.className = 'typing';
  typing.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
  document.getElementById('messages').appendChild(typing);
  document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;

  try {
    const res = await fetch('api/ai_chat.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        scenario: currentScenario,
        history: chatHistory,
        system: "You are Ustad Faseeh. Conduct a conversation in " + currentScenario + " scenario. Correct the student gently. Format: ARABIC: [text] | TRANSLATION: [text]"
      })
    });
    const data = await res.json();
    typing.remove();

    if(data.reply) {
      const parts = data.reply.split('|');
      const arabic = parts[0].replace('ARABIC:', '').trim();
      const english = parts[1] ? parts[1].replace('TRANSLATION:', '').trim() : '';
      addMsg('ai', arabic, english);
      chatHistory.push({role: 'assistant', content: data.reply});
    }
  } catch(e) {
    typing.remove();
    addMsg('ai', 'Something went wrong. Please try again.');
  }
}

function addMsg(role, arabic, english = '') {
  const div = document.createElement('div');
  div.className = 'msg ' + role;
  let html = '<div class="bubble">';
  if(arabic) html += '<span class="msg-arabic">' + arabic + '</span>';
  if(english) html += '<div class="msg-english">' + english + '</div>';
  if(role === 'ai' && arabic) html += '<button class="listen-btn" onclick="play(\'' + arabic.replace(/'/g, "\\'") + '\')">🔊 Listen</button>';
  html += '</div>';
  div.innerHTML = html;
  document.getElementById('messages').appendChild(div);
  document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
}

function play(t) {
  if(window.responsiveVoice) responsiveVoice.speak(t, "Arabic Male", {rate: 0.9});
}
</script>
</body>
</html>
