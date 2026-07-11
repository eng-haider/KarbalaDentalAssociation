/* نقابة أطباء الأسنان – كربلاء المقدسة | Service Worker
   Cache-first for static assets, network-first for navigations (so content
   stays fresh) with an offline fallback. Intentionally minimal & dependency-free. */

const CACHE = 'kda-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = ['/', OFFLINE_URL, '/manifest.webmanifest', '/favicon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    // Network-first for page navigations.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(request, copy));
                    return res;
                })
                .catch(() => caches.match(request).then((r) => r || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Cache-first for everything else (assets, fonts, etc).
    event.respondWith(
        caches.match(request).then((cached) => {
            return (
                cached ||
                fetch(request).then((res) => {
                    if (res && res.status === 200 && res.type === 'basic') {
                        const copy = res.clone();
                        caches.open(CACHE).then((c) => c.put(request, copy));
                    }
                    return res;
                }).catch(() => cached)
            );
        })
    );
});
