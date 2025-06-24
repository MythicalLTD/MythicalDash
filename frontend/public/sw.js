// Simple service worker for performance caching
const CACHE_NAME = 'mccloudadmin-cache-v1';
const urlsToCache = ['/', '/assets/main.css', '/api/system/custom.css', '/api/system/custom.js'];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(urlsToCache);
        }),
    );
});

self.addEventListener('fetch', function (event) {
    event.respondWith(
        caches.match(event.request).then(function (response) {
            // Cache hit - return response
            if (response) {
                return response;
            }
            return fetch(event.request);
        }),
    );
});
