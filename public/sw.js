const STATIC_CACHE = 'safetrack-static-v1';
const CORE_ASSETS = [
    '/manifest.webmanifest',
    '/icons/safetrack-192.svg',
    '/icons/safetrack-512.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => Promise.allSettled(CORE_ASSETS.map((asset) => cache.add(asset))))
            .then(() => self.skipWaiting())
            .catch(() => undefined),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== STATIC_CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
            .catch(() => undefined),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || request.mode === 'navigate') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || !isStaticAsset(url.pathname)) return;

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            if (response.ok) {
                caches.open(STATIC_CACHE).then((cache) => cache.put(request, response.clone())).catch(() => undefined);
            }
            return response;
        })).catch(() => caches.match(request)),
    );
});

function isStaticAsset(pathname) {
    return pathname === '/manifest.webmanifest'
        || pathname.startsWith('/icons/')
        || pathname.startsWith('/build/assets/');
}
