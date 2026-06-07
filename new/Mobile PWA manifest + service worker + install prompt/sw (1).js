// sw.js — Faseeh Service Worker
// Place this file at your site ROOT: /sw.js
// Version bump this string to force cache refresh on deploy

const CACHE_VERSION  = 'faseeh-v1.0.0';
const STATIC_CACHE   = CACHE_VERSION + '-static';
const DYNAMIC_CACHE  = CACHE_VERSION + '-dynamic';

// ── Files to pre-cache on install ──────────────────────────────────────────
const PRECACHE_URLS = [
  '/',
  '/landing.php',
  '/dashboard.php',
  '/play.php',
  '/academy.php',
  '/quran.php',
  '/rankings.php',
  '/profile.php',
  '/offline.php',
  '/manifest.json',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  // Google Fonts fallback (cached on first visit)
  'https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&family=Amiri:ital,wght@0,400;0,700;1,400&display=swap',
];

// ── Install: pre-cache static assets ───────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      console.log('[SW] Pre-caching static assets');
      return cache.addAll(PRECACHE_URLS);
    }).then(() => self.skipWaiting())
  );
});

// ── Activate: purge old caches ──────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(k => k !== STATIC_CACHE && k !== DYNAMIC_CACHE)
          .map(k  => { console.log('[SW] Deleting old cache:', k); return caches.delete(k); })
      )
    ).then(() => self.clients.claim())
  );
});

// ── Fetch: network-first for API/PHP, cache-first for assets ───────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET and browser-extension requests
  if (request.method !== 'GET') return;
  if (!url.protocol.startsWith('http')) return;

  // API calls — network only (never cache)
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(fetch(request));
    return;
  }

  // PHP pages — network first, fall back to cache, then offline page
  if (url.pathname.endsWith('.php') || url.pathname === '/') {
    event.respondWith(
      fetch(request)
        .then(response => {
          const clone = response.clone();
          caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
          return response;
        })
        .catch(() =>
          caches.match(request).then(cached => cached || caches.match('/offline.php'))
        )
    );
    return;
  }

  // Static assets (CSS, JS, images, fonts) — cache first, then network
  event.respondWith(
    caches.match(request).then(cached => {
      if (cached) return cached;
      return fetch(request).then(response => {
        if (response.ok) {
          const clone = response.clone();
          caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
        }
        return response;
      });
    })
  );
});

// ── Background Sync: save game progress while offline ──────────────────────
self.addEventListener('sync', event => {
  if (event.tag === 'sync-progress') {
    event.waitUntil(syncOfflineProgress());
  }
});

async function syncOfflineProgress() {
  try {
    const db     = await openIDB();
    const pending = await getAllPending(db);
    for (const item of pending) {
      const res = await fetch('/api/save_progress.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item.data),
      });
      if (res.ok) await deletePending(db, item.id);
    }
  } catch (err) {
    console.warn('[SW] Sync failed, will retry:', err);
  }
}

// ── Push Notifications: streak reminders ───────────────────────────────────
self.addEventListener('push', event => {
  const data = event.data?.json() ?? {};
  const title   = data.title   || 'Faseeh Reminder 🔥';
  const options = {
    body:    data.body    || "Don't break your streak! Learn Arabic today.",
    icon:    '/assets/icons/icon-192.png',
    badge:   '/assets/icons/icon-96.png',
    vibrate: [200, 100, 200],
    data:    { url: data.url || '/dashboard.php' },
    actions: [
      { action: 'play',    title: '▶ Play Now' },
      { action: 'dismiss', title: '✕ Later'   },
    ],
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.action === 'dismiss') return;
  const url = event.notification.data?.url || '/dashboard.php';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
      const existing = list.find(c => c.url.includes(url));
      if (existing) return existing.focus();
      return clients.openWindow(url);
    })
  );
});

// ── Minimal IndexedDB helpers for offline queue ────────────────────────────
function openIDB() {
  return new Promise((res, rej) => {
    const req = indexedDB.open('faseeh-offline', 1);
    req.onupgradeneeded = e => e.target.result.createObjectStore('pending', { keyPath: 'id', autoIncrement: true });
    req.onsuccess = e => res(e.target.result);
    req.onerror   = e => rej(e.target.error);
  });
}
function getAllPending(db) {
  return new Promise((res, rej) => {
    const tx  = db.transaction('pending', 'readonly');
    const req = tx.objectStore('pending').getAll();
    req.onsuccess = e => res(e.target.result);
    req.onerror   = e => rej(e.target.error);
  });
}
function deletePending(db, id) {
  return new Promise((res, rej) => {
    const tx  = db.transaction('pending', 'readwrite');
    const req = tx.objectStore('pending').delete(id);
    req.onsuccess = () => res();
    req.onerror   = e => rej(e.target.error);
  });
}
