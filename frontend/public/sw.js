// Simple service worker for performance caching
const CACHE_NAME = 'mythicaldash-cache-v1';
const urlsToCache = [
    '/',
    '/assets/main.css', 
    '/api/system/custom.css', 
    '/api/system/custom.js'
];

// Resources that should be cached (static assets only)
const cacheableResources = [
    '/assets/',
    '/images/',
    '/fonts/',
    '/api/system/custom.css',
    '/api/system/custom.js'
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            // Only cache resources that are likely to exist and be static
            return cache.addAll(urlsToCache.filter(url => 
                url === '/' || 
                url.startsWith('/assets/') || 
                url.startsWith('/api/system/')
            ));
        }).catch(function (error) {
            console.warn('Service worker cache installation failed:', error);
            // Continue installation even if caching fails
        })
    );
});

self.addEventListener('fetch', function (event) {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip non-cacheable requests
    if (!shouldCacheRequest(event.request)) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(function (response) {
            // Cache hit - return response
            if (response) {
                return response;
            }

            // Not in cache, fetch from network
            return fetch(event.request).then(function (response) {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Clone the response before caching
                const responseToCache = response.clone();

                // Cache the response for future use
                caches.open(CACHE_NAME).then(function (cache) {
                    cache.put(event.request, responseToCache);
                }).catch(function (error) {
                    console.warn('Failed to cache response:', error);
                });

                return response;
            }).catch(function (error) {
                console.warn('Fetch failed for:', event.request.url, error);
                // Return a fallback response or let the browser handle the error
                return new Response('Network error', {
                    status: 503,
                    statusText: 'Service Unavailable'
                });
            });
        })
    );
});

/**
 * Determine if a request should be cached
 * @param {Request} request - The fetch request
 * @returns {boolean} - Whether the request should be cached
 */
function shouldCacheRequest(request) {
    const url = new URL(request.url);
    
    // Don't cache API calls (except system custom resources)
    if (url.pathname.startsWith('/api/') && !url.pathname.startsWith('/api/system/')) {
        return false;
    }
    
    // Don't cache authentication-related requests
    if (url.pathname.includes('/auth/') || url.pathname.includes('/login') || url.pathname.includes('/logout')) {
        return false;
    }
    
    // Don't cache dynamic content
    if (url.pathname.includes('/dashboard') || url.pathname.includes('/mc-admin')) {
        return false;
    }
    
    // Don't cache POST requests or other methods
    if (request.method !== 'GET') {
        return false;
    }
    
    // Only cache same-origin requests
    if (url.origin !== location.origin) {
        return false;
    }
    
    // Check if it's a cacheable resource type
    return cacheableResources.some(resource => url.pathname.startsWith(resource));
}

// Clean up old caches on activation
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
