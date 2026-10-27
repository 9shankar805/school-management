/**
 * NepalSchoolHub — Service Worker (Module 23)
 *
 * Strategy:
 *   - App shell (layout CSS/JS)    → Cache-first with revalidation
 *   - API & dynamic HTML           → Network-first, fall back to cache
 *   - Static assets (images, fonts)→ Cache-first, long TTL
 *   - Offline fallback page        → Served from cache when network fails
 *
 * Cache versioning: bump SW_VERSION to invalidate all caches on deploy.
 */

const SW_VERSION   = 'v2.0.0';   // v2.0.0: comprehensive offline-first platform
const CACHE_SHELL  = `shell-${SW_VERSION}`;
const CACHE_PAGES  = `pages-${SW_VERSION}`;
const CACHE_ASSETS = `assets-${SW_VERSION}`;

// ── App shell — always cached on install ─────────────────────────────────
const SHELL_URLS = [
    '/',
    '/home',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/js/offline-db.js',
    '/js/offline-seed.js',
    '/js/attendance-sync.js',
    '/js/offline-forms.js',
    '/js/offline-sync.js',
    '/favicon_io/android-chrome-192x192.png',
    '/favicon_io/favicon-32x32.png',
    '/manifest.json',
];

// ── Static asset patterns — Cache-first ──────────────────────────────────
const CACHE_FIRST_PATTERNS = [
    /\/css\//,
    /\/js\//,
    /\/fonts\//,
    /\/imgs\//,
    /\/favicon_io\//,
    /\.(?:png|jpg|jpeg|gif|svg|ico|webp|woff|woff2|ttf|otf)$/i,
];

// ── Never cache these ─────────────────────────────────────────────────────
const BYPASS_PATTERNS = [
    /\/api\/(?!offline\/)/,   // bypass all /api/* EXCEPT /api/offline/*
    /\/horizon/,
    /\/webhooks\//,
    /\/_debugbar/,
    /logout/,
    /\/sanctum\//,
];

// ── Always pre-cache these HTML pages for offline use ────────────────────
const PRECACHE_PAGES = [
    // Core & Dashboards
    '/home',
    '/offline',
    '/attendances/take',
    '/attendance/qr',
    // Homework & Assignments
    '/homework',
    '/homework/create',
    '/assignments',
    '/courses/assignments/create',
    // Timetables & Routine
    '/routine/view',
    // Student & Academics
    '/students/add',
    '/students/view/list',
    '/syllabus/index',
    '/syllabus/create',
    '/marks/create',
    // Communication & Notices
    '/notices',
    '/notice/create',
    // Visitors & Front Desk
    '/visitors',
    '/visitors/create',
    // Feedback & Grievances
    '/feedback',
    '/feedback/create',
    '/feedback/my',
    // Leave
    '/leave',
    '/leave/create',
    // Finance
    '/finance/day-book',
];

// Max age for page cache entries (5 minutes)
const PAGE_CACHE_MAX_AGE_MS = 5 * 60 * 1000;

// ─────────────────────────────────────────────────────────────────────────
//  INSTALL: Pre-cache the app shell
// ─────────────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    console.log(`[SW ${SW_VERSION}] Installing…`);

    event.waitUntil(
        caches.open(CACHE_SHELL)
            .then((cache) => cache.addAll(SHELL_URLS))
            .then(() => {
                // Best-effort: pre-cache key app pages; ignore 401/redirect failures
                return caches.open(CACHE_PAGES).then((pageCache) =>
                    Promise.allSettled(
                        PRECACHE_PAGES.map((url) =>
                            fetch(url, { credentials: 'include' })
                                .then((r) => { if (r.ok) pageCache.put(url, r); })
                                .catch(() => {})
                        )
                    )
                );
            })
            .then(() => {
                console.log(`[SW ${SW_VERSION}] Shell cached`);
                return self.skipWaiting();
            })
            .catch((err) => {
                console.warn(`[SW ${SW_VERSION}] Shell cache failed (some URLs may be 404 in dev):`, err);
                return self.skipWaiting();
            })
    );
});

// ─────────────────────────────────────────────────────────────────────────
//  ACTIVATE: Delete old caches
// ─────────────────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    console.log(`[SW ${SW_VERSION}] Activating…`);

    const validCaches = [CACHE_SHELL, CACHE_PAGES, CACHE_ASSETS];

    event.waitUntil(
        caches.keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !validCaches.includes(key))
                        .map((key) => {
                            console.log(`[SW] Deleting old cache: ${key}`);
                            return caches.delete(key);
                        })
                )
            )
            .then(() => self.clients.claim())
    );
});

// ─────────────────────────────────────────────────────────────────────────
//  FETCH: Route requests to appropriate strategies
// ─────────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin GET requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Bypass API, Horizon, webhooks — always go to network
    if (BYPASS_PATTERNS.some((rx) => rx.test(url.pathname))) {
        return;
    }

    // Static assets → Cache-first
    if (CACHE_FIRST_PATTERNS.some((rx) => rx.test(url.pathname))) {
        event.respondWith(cacheFirst(request, CACHE_ASSETS));
        return;
    }

    // HTML pages → Network-first with offline fallback
    if (request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(networkFirst(request, CACHE_PAGES));
        return;
    }

    // Everything else → Network-first
    event.respondWith(networkFirst(request, CACHE_PAGES));
});

// ─────────────────────────────────────────────────────────────────────────
//  PUSH notifications — display when app is in background
// ─────────────────────────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = { title: 'SchoolHub', body: 'You have a new notification.' };

    try {
        if (event.data) {
            data = event.data.json();
        }
    } catch (_) { /* ignore parse errors */ }

    event.waitUntil(
        self.registration.showNotification(data.title || 'SchoolHub', {
            body:    data.body    || '',
            icon:    data.icon    || '/favicon_io/android-chrome-192x192.png',
            badge:   data.badge   || '/favicon_io/favicon-32x32.png',
            tag:     data.tag     || 'school-hub',
            data:    { url: data.url || '/notifications' },
            actions: data.actions || [],
            vibrate: [200, 100, 200],
        })
    );
});

// ── Notification click: open the target URL ───────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/home';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Focus an existing tab if already open
                for (const client of clientList) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.navigate(targetUrl);
                        return client.focus();
                    }
                }
                // Otherwise open a new tab
                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }
            })
    );
});

// ─────────────────────────────────────────────────────────────────────────
//  Background sync — retry failed form submissions when online
// ─────────────────────────────────────────────────────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncAttendance());
    }

    if (event.tag === 'sync-seed') {
        event.waitUntil(syncSeed());
    }
});

async function syncAttendance() {
    // Reads queued attendance records from IndexedDB and replays them.
    // Implementation delegated to the app's JS layer (attendance-sync.js).
    const channel = new BroadcastChannel('sw-sync');
    channel.postMessage({ type: 'attendance-sync-requested' });
    channel.close();
}

async function syncSeed() {
    // Asks the app layer to refresh cached reference data (students/classes).
    const channel = new BroadcastChannel('sw-sync');
    channel.postMessage({ type: 'seed-refresh-requested' });
    channel.close();
}

// ─────────────────────────────────────────────────────────────────────────
//  Strategy helpers
// ─────────────────────────────────────────────────────────────────────────

/** Cache-first: serve from cache, update in background */
async function cacheFirst(request, cacheName) {
    const cache    = await caches.open(cacheName);
    const cached   = await cache.match(request);

    if (cached) {
        // Revalidate in background (stale-while-revalidate)
        fetch(request)
            .then((response) => {
                if (response.ok) cache.put(request, response.clone());
            })
            .catch(() => { /* ignore network errors during revalidation */ });

        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) cache.put(request, response.clone());
        return response;
    } catch (_) {
        return offlineFallback(request);
    }
}

/** Network-first: try network, fall back to cache, then offline page */
async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);

    try {
        const response = await fetch(request);

        if (response.ok) {
            cache.put(request, response.clone());
        }

        return response;
    } catch (_) {
        const cached = await cache.match(request);
        if (cached) return cached;
        return offlineFallback(request);
    }
}

/** Return offline fallback page for HTML requests */
async function offlineFallback(request) {
    if (request.headers.get('Accept')?.includes('text/html')) {
        const cache   = await caches.open(CACHE_SHELL);
        const offline = await cache.match('/offline');
        if (offline) return offline;
    }

    return new Response('Offline — please check your connection.', {
        status:  503,
        headers: { 'Content-Type': 'text/plain' },
    });
}


// ═══════════════════════════════════════════════════════════════════════════
//  Firebase Cloud Messaging — Background Push Handler
//  This block is appended to the existing service worker.
//  Firebase Messaging SDK (compat v9) is imported below.
//  The SW_VERSION above handles cache busting; bump it after deploying.
// ═══════════════════════════════════════════════════════════════════════════

// Import Firebase Messaging compat SDK (works in service worker context)
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Firebase config is injected by the server at runtime via a meta-tag read
// when the page registers this SW. We receive it via postMessage below.
let _fbApp = null;
let _messaging = null;

/**
 * The main app sends firebaseConfig via postMessage after SW registration.
 * We initialise Firebase here so the background handler works.
 */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        const cfg = event.data.config;
        if (!_fbApp && cfg && cfg.apiKey) {
            try {
                _fbApp     = firebase.initializeApp(cfg);
                _messaging = firebase.messaging();

                // Background message handler — shown when page is NOT focused
                _messaging.onBackgroundMessage((payload) => {
                    console.log('[SW] FCM background message:', payload);

                    const notif  = payload.notification || {};
                    const data   = payload.data         || {};
                    const title  = notif.title  || data.title  || 'NepalSchoolHub';
                    const body   = notif.body   || data.body   || '';
                    const icon   = notif.icon   || data.icon   || '/favicon_io/android-chrome-192x192.png';
                    const badge  = '/favicon_io/favicon-32x32.png';
                    const click  = data.click_action || data.url || '/home';

                    self.registration.showNotification(title, {
                        body,
                        icon,
                        badge,
                        data: { url: click },
                        vibrate:    [200, 100, 200],
                        requireInteraction: false,
                    });
                });

                console.log('[SW] Firebase Messaging initialised.');
            } catch (err) {
                console.error('[SW] Firebase init failed:', err);
            }
        }
    }
});

// Notification click → navigate to the URL stored in notification.data.url
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/home';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Focus existing tab if already open
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    client.navigate(url);
                    return;
                }
            }
            // Otherwise open new tab
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
