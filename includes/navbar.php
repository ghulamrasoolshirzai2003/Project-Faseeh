<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
/* --- CONSISTENT NAVBAR --- */
.navbar {
    width: 100%; padding: 15px 30px;
    display: flex; justify-content: space-between; align-items: center;
    position: fixed; top: 0; left: 0; z-index: 1000;
    background: rgba(0,0,0,0.3); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    box-sizing: border-box;
}
.nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.mini-icon {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #f2994a, #f2c94c);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    position: relative; box-shadow: 0 0 15px rgba(242,153,74,0.4);
}
.mini-icon::after {
    content: ''; position: absolute; width: 34px; height: 34px;
    border: 2px solid rgba(255,255,255,0.4); border-top-color: transparent;
    border-radius: 50%; animation: spinNavbar 8s linear infinite;
}
.mini-letter { font-family: 'Amiri', serif; font-size: 20px; color: white; margin-top: -3px; z-index: 2; }
.mini-text {
    font-size: 1.4rem; font-weight: 800; margin: 0;
    background: linear-gradient(to right, #fff 20%, #FFD700 50%, #fff 80%);
    background-size: 200% auto; color: transparent;
    -webkit-background-clip: text; background-clip: text;
    animation: shineNavbar 3s linear infinite;
    font-family: 'Poppins', sans-serif;
}
.nav-links { display: flex; gap: 10px; align-items: center; font-family: 'Poppins', sans-serif; }
.nav-link {
    color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.85rem;
    padding: 8px 18px; border-radius: 25px; transition: 0.3s; font-weight: 500;
    border: 1px solid transparent; 
}
.nav-link:hover { color: white; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.1); }
.nav-link.active { background: rgba(242,153,74,0.2); color: #f2994a; border-color: rgba(242,153,74,0.3); }

@keyframes spinNavbar { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes shineNavbar { to { background-position: 200% center; } }

@media (max-width: 768px) {
    .navbar { padding: 10px 15px; }
    .nav-links { gap: 5px; }
    .nav-link { font-size: 0.75rem; padding: 6px 12px; }
}
@media (max-width: 480px) {
    .nav-link span.hide-mobile { display: none; }
    .mini-text { display: block; font-size: 1.2rem; margin-left: 5px; }
    .mini-icon { width: 32px; height: 32px; }
    .mini-icon::after { width: 26px; height: 26px; }
    .mini-letter { font-size: 16px; margin-top: -2px; }
    .nav-links { gap: 2px; }
    .nav-link { font-size: 1.1rem; padding: 5px 8px; }
    .navbar { padding: 8px 10px; }
}
</style>

<nav class="navbar">
    <a href="dashboard.php" class="nav-brand">
        <div class="mini-icon"><div class="mini-letter">ف</div></div>
        <h1 class="mini-text">Faseeh</h1>
    </a>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">📊 <span class="hide-mobile">Dashboard</span></a>
        <a href="academy.php" class="nav-link <?= $currentPage == 'academy.php' ? 'active' : '' ?>">🎓 <span class="hide-mobile">Academy</span></a>
        <a href="level_select.php" class="nav-link <?= $currentPage == 'level_select.php' ? 'active' : '' ?>">🎮 <span class="hide-mobile">Play</span></a>
        <a href="leaderboard.php" class="nav-link <?= $currentPage == 'leaderboard.php' ? 'active' : '' ?>">🏆 <span class="hide-mobile">Rankings</span></a>
        <a href="profile.php" class="nav-link <?= $currentPage == 'profile.php' ? 'active' : '' ?>" style="border-color: rgba(255,255,255,0.15);">👤 <span class="hide-mobile">Profile</span></a>
    </div>
</nav>

<!-- Streak Reminder Banner (hidden by default) -->
<div id="streak-banner" style="display:none; position:fixed; top:70px; width:100%; background:rgba(242,153,74,0.2); color:#fff; text-align:center; padding:8px; z-index:999; font-weight:500;">
    🔥 Keep your <strong>5‑day streak</strong> alive! <a href="level_select.php" style="color:#fff; text-decoration:underline; margin-left:10px;">Play Now</a>
    <span id="streak-close" style="margin-left:15px; cursor:pointer; font-weight:bold;">✕</span>
</div>
<script>
    // Show banner if streak >=5 and not dismissed this session
    document.addEventListener('DOMContentLoaded', function(){
        const streak = parseInt('<?php echo $_SESSION["streak"] ?? 0; ?>',10);
        if(streak >=5 && !sessionStorage.getItem('streakDismissed')){
            const banner = document.getElementById('streak-banner');
            banner.style.display='block';
        }
        const closeBtn = document.getElementById('streak-close');
        if(closeBtn){
            closeBtn.addEventListener('click',()=>{
                document.getElementById('streak-banner').style.display='none';
                sessionStorage.setItem('streakDismissed',true);
            });
        }
    });
</script>
