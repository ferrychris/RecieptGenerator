// Minimal service worker: exists mainly to satisfy PWA installability
// criteria (a registered SW with a fetch handler). Deliberately does NOT
// cache Inertia page responses or API calls — this app's data changes
// constantly and CSRF tokens are per-session, so caching navigation/POST
// requests would risk serving stale pages or broken form submissions.
// It only opportunistically caches Vite's content-hashed build assets,
// which are safe to cache forever since a new hash means a new URL.
const CACHE = 'receiptgen-static-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))),
        ),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Never touch non-GET requests (form posts, PDF downloads, etc.) or
    // cross-origin requests.
    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    const isBuildAsset = request.url.includes('/build/assets/');
    if (!isBuildAsset) {
        return; // let the browser handle everything else normally
    }

    event.respondWith(
        caches.open(CACHE).then(async (cache) => {
            const cached = await cache.match(request);
            if (cached) return cached;

            const response = await fetch(request);
            if (response.ok) cache.put(request, response.clone());
            return response;
        }),
    );
});
