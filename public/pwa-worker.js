const CACHE_PREFIX = 'hrd-system-';
const CACHE_NAME = `${CACHE_PREFIX}static-v2`;
const BASE_URL = new URL('./', self.registration.scope);
const assetUrl = (path) => new URL(path, BASE_URL).href;
const OFFLINE_URL = assetUrl('offline.html');
const STATIC_ASSETS = [
  OFFLINE_URL,
  assetUrl('manifest.json'),
  assetUrl('images/logo-triguna-clean.png'),
  assetUrl('images/icons/icon-72x72.png'),
  assetUrl('images/icons/icon-144x144.png'),
  assetUrl('images/icons/icon-192x192.png'),
  assetUrl('images/icons/icon-512x512.png'),
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((names) => Promise.all(
        names
          .filter((name) => name.startsWith(CACHE_PREFIX) && name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  if (!url.pathname.startsWith(BASE_URL.pathname)) return;

  const relativePath = url.pathname.slice(BASE_URL.pathname.length);
  const isSafeStaticAsset =
    relativePath.startsWith('build/assets/') ||
    relativePath.startsWith('images/icons/') ||
    relativePath === 'images/logo-triguna-clean.png' ||
    relativePath === 'manifest.json' ||
    relativePath === 'offline.html';

  if (!isSafeStaticAsset) return;

  event.respondWith(
    caches.match(request).then((cached) => cached || fetch(request).then((response) => {
      if (response.ok && response.type === 'basic') {
        const copy = response.clone();
        event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.put(request, copy)));
      }

      return response;
    }))
  );
});
