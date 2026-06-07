<?php
// offline.php — shown when user is offline and page isn't cached
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Offline — Faseeh</title>
<style>
  :root{--bg:#0e0c1e;--accent:#f5a623;--gold:#d4a843;--text:#f0eeff;--muted:#8b87b0}
  body{background:var(--bg);color:var(--text);font-family:system-ui,sans-serif;
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    text-align:center;padding:24px}
  .offline-box{max-width:400px}
  .offline-icon{font-size:4rem;margin-bottom:24px;display:block}
  .arabic{font-size:2rem;color:var(--gold);margin-bottom:8px}
  h1{font-size:1.6rem;margin-bottom:12px}
  p{color:var(--muted);line-height:1.7;margin-bottom:28px}
  .btn{display:inline-block;background:linear-gradient(135deg,var(--accent),#e8862a);
    color:#1a0f00;padding:12px 28px;border-radius:50px;font-weight:700;
    text-decoration:none;cursor:pointer;border:none;font-size:1rem}
  .offline-tip{margin-top:24px;padding:16px;background:rgba(255,255,255,.04);
    border-radius:12px;font-size:.85rem;color:var(--muted)}
</style>
</head>
<body>
<div class="offline-box">
  <span class="offline-icon">📶</span>
  <div class="arabic">أنت غير متصل</div>
  <h1>You're offline</h1>
  <p>No internet connection found. But don't stop learning! Some Faseeh features work offline — try your cached lessons.</p>
  <button class="btn" onclick="location.reload()">Try Again</button>
  <div class="offline-tip">
    💡 <strong>Tip:</strong> Install Faseeh as an app and your recently visited lessons will be available offline automatically.
  </div>
</div>
</body>
</html>
