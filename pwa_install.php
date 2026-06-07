<?php
// pwa_install.php — Include this file in your <head> on EVERY page
// It handles: service worker registration, install banner, offline detection
?>
<!-- PWA: Manifest -->
<link rel="manifest" href="/manifest.json"/>
<meta name="theme-color" content="#f5a623"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="Faseeh"/>
<link rel="apple-touch-icon" href="/assets/icons/icon-192.png"/>

<style>
/* ── PWA Install Banner ───────────────────────────────────────── */
#pwa-install-banner {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(120px);
  z-index: 9999; width: calc(100% - 48px); max-width: 480px;
  background: #1c1a38; border: 1px solid rgba(245,166,35,.4);
  border-radius: 20px; padding: 16px 20px;
  box-shadow: 0 16px 64px rgba(0,0,0,.6);
  display: flex; align-items: center; gap: 16px;
  transition: transform .4s cubic-bezier(.34,1.56,.64,1);
}
#pwa-install-banner.show { transform: translateX(-50%) translateY(0); }
.pwa-banner-icon {
  width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
  background: linear-gradient(135deg,#f5a623,#e8862a);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem;
}
.pwa-banner-text { flex: 1; }
.pwa-banner-text strong { display: block; font-size: .95rem; margin-bottom: 2px; }
.pwa-banner-text span   { font-size: .78rem; color: #8b87b0; }
.pwa-banner-actions { display: flex; flex-direction: column; gap: 6px; }
.pwa-btn-install {
  background: linear-gradient(135deg,#f5a623,#e8862a);
  color: #1a0f00; border: none; padding: 8px 18px; border-radius: 50px;
  font-size: .82rem; font-weight: 700; cursor: pointer; white-space: nowrap;
}
.pwa-btn-dismiss {
  background: none; border: none; color: #8b87b0; font-size: .75rem;
  cursor: pointer; text-align: center; padding: 2px;
}
.pwa-btn-dismiss:hover { color: #f0eeff; }

/* ── Offline Toast ────────────────────────────────────────────── */
#offline-toast {
  position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
  z-index: 9998; background: #e85d5d; color: #fff; border-radius: 50px;
  padding: 12px 28px; font-size: .9rem; font-weight: 600;
  box-shadow: 0 10px 40px rgba(0,0,0,0.4);
  transition: transform .4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: none; align-items: center; gap: 10px;
  font-family: 'Poppins', sans-serif;
}
#offline-toast.show { display: flex; transform: translateX(-50%) translateY(0); }
.offline-close { cursor: pointer; font-weight: 800; margin-left: 8px; opacity: 0.7; }
.offline-close:hover { opacity: 1; }

/* ── Update Banner ────────────────────────────────────────────── */
#update-banner {
  position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
  background: linear-gradient(90deg, #7c5cbf, #5a3f9e); color: #fff; padding: 10px 24px;
  display: none; align-items: center; justify-content: center; gap: 16px;
  font-size: .88rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
#update-banner button {
  background: #fff; border: none;
  color: #7c5cbf; padding: 6px 16px; border-radius: 50px; cursor: pointer;
  font-size: .82rem; font-weight: 700; transition: .2s;
}
#update-banner button:hover { transform: scale(1.05); background: #f0eeff; }

/* ── Mobile Overrides for PWA ── */
@media (max-width: 480px) {
  #pwa-install-banner { bottom: 10px; padding: 10px; border-radius: 12px; gap: 10px; width: calc(100% - 20px); }
  .pwa-banner-icon { width: 40px; height: 40px; font-size: 1.2rem; border-radius: 10px; }
  .pwa-banner-text strong { font-size: 0.85rem; }
  .pwa-banner-text span { font-size: 0.7rem; }
  .pwa-btn-install { padding: 6px 12px; font-size: 0.75rem; }
}
</style>

<!-- PWA Banners HTML -->
<div id="pwa-install-banner">
  <div class="pwa-banner-icon">ف</div>
  <div class="pwa-banner-text">
    <strong>Install Faseeh Academy</strong>
    <span>Access your lessons faster & study offline</span>
  </div>
  <div class="pwa-banner-actions">
    <button class="pwa-btn-install" id="pwa-install-btn">Install App</button>
    <button class="pwa-btn-dismiss" id="pwa-dismiss-btn">Maybe later</button>
  </div>
</div>

<div id="offline-toast">
  <span>📶</span> You're currently offline — using cached lessons
  <span class="offline-close" onclick="document.getElementById('offline-toast').classList.remove('show')">✕</span>
</div>

<div id="update-banner">
  ✨ A faster version of Faseeh is ready! 
  <button id="update-btn">Update Now</button>
</div>

<script>
(function () {
  'use strict';

  // --- 1. Register Service Worker ---
  let swRegistration = null;
  let newWorker      = null;

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { scope: '/' })
      .then(reg => {
        swRegistration = reg;
        reg.addEventListener('updatefound', () => {
          newWorker = reg.installing;
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              document.getElementById('update-banner').style.display = 'flex';
            }
          });
        });
      })
      .catch(err => console.warn('[PWA] SW registration failed:', err));

    navigator.serviceWorker.addEventListener('controllerchange', () => location.reload());
  }

  // --- 2. Update banner ---
  document.getElementById('update-btn')?.addEventListener('click', () => {
    if (newWorker) newWorker.postMessage({ action: 'skipWaiting' });
  });

  // --- 3. Install prompt ---
  let deferredPrompt = null;
  const banner       = document.getElementById('pwa-install-banner');
  const installBtn   = document.getElementById('pwa-install-btn');
  const dismissBtn   = document.getElementById('pwa-dismiss-btn');

  const dismissed = localStorage.getItem('pwa-dismissed');
  const isInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    if (!dismissed && !isInstalled) {
      setTimeout(() => banner?.classList.add('show'), 5000); // 5s delay
    }
  });

  installBtn?.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') {
      banner?.classList.remove('show');
    }
    deferredPrompt = null;
  });

  dismissBtn?.addEventListener('click', () => {
    banner?.classList.remove('show');
    localStorage.setItem('pwa-dismissed', Date.now());
  });

  // Auto-hide after 30s
  setTimeout(() => banner?.classList.remove('show'), 30000);

  // --- 4. Offline / Online detection (Strict Logic) ---
  const toast = document.getElementById('offline-toast');

  function updateOnlineStatus() {
    if (navigator.onLine === false) {
      toast?.classList.add('show');
    } else {
      toast?.classList.remove('show');
    }
  }

  window.addEventListener('offline', updateOnlineStatus);
  window.addEventListener('online',  updateOnlineStatus);
  
  // Verify after 15s (EXTREME DELAY) to avoid false positives
  setTimeout(() => {
    // Only show if the user is verified offline and the banner isn't already dismissed
    if (navigator.onLine === false) updateOnlineStatus();
  }, 15000);

  // ── 5. Save progress offline via IndexedDB + Background Sync ──
  window.faseehOfflineSave = async function (data) {
    if (navigator.onLine) return; // handled normally if online

    try {
      const db = await openDB();
      await saveToIDB(db, data);

      if ('SyncManager' in window && swRegistration) {
        await swRegistration.sync.register('sync-progress');
        console.log('[PWA] Progress queued for background sync');
      }
    } catch (err) {
      console.warn('[PWA] Could not save offline progress:', err);
    }
  };

  function openDB() {
    return new Promise((res, rej) => {
      const req = indexedDB.open('faseeh-offline', 1);
      req.onupgradeneeded = e => e.target.result.createObjectStore('pending', { keyPath: 'id', autoIncrement: true });
      req.onsuccess = e => res(e.target.result);
      req.onerror   = e => rej(e.target.error);
    });
  }

  function saveToIDB(db, data) {
    return new Promise((res, rej) => {
      const tx  = db.transaction('pending', 'readwrite');
      const req = tx.objectStore('pending').add({ data, ts: Date.now() });
      req.onsuccess = () => res();
      req.onerror   = e => rej(e.target.error);
    });
  }

})();
</script>
