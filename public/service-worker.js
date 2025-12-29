const VERSION = 'hrd-pwa-v1';
const ASSET_CACHE = `assets-${VERSION}`;

const PRECACHE_URLS = [
  '/offline.html',
  '/pwa/icon-192.png',
  '/pwa/icon-512.png'
];

function isSameOrigin(url) {
  return self.location.origin === url.origin;
}

function isAssetRequest(reqUrl) {
  const path = reqUrl.pathname || '';
  return (
    path.startsWith('/build/') ||
    path.startsWith('/css/') ||
    path.startsWith('/js/') ||
    path.startsWith('/images/') ||
    path.startsWith('/pwa/') ||
    path.endsWith('.css') ||
    path.endsWith('.js') ||
    path.endsWith('.png') ||
    path.endsWith('.jpg') ||
    path.endsWith('.jpeg') ||
    path.endsWith('.webp') ||
    path.endsWith('.svg') ||
    path.endsWith('.ico') ||
    path.endsWith('.woff') ||
    path.endsWith('.woff2') ||
    path.endsWith('.ttf')
  );
}

function isSensitivePath(reqUrl) {
  const p = reqUrl.pathname || '';
  return (
    p.startsWith('/storage/') ||
    p.startsWith('/leave-requests') ||
    p.startsWith('/attendance') ||
    p.startsWith('/hr/') ||
    p.startsWith('/supervisor/') ||
    p.startsWith('/logout') ||
    p.startsWith('/login')
  );
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(ASSET_CACHE).then((cache) => cache.addAll(PRECACHE_URLS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((k) => k.startsWith('assets-') && k !== ASSET_CACHE)
          .map((k) => caches.delete(k))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  if (!isSameOrigin(url)) return;

  const accept = req.headers.get('accept') || '';
  const isNavigate = req.mode === 'navigate' || accept.includes('text/html');

  if (isNavigate) {
    event.respondWith(
      fetch(req).catch(() => caches.match('/offline.html'))
    );
    return;
  }

  if (isSensitivePath(url)) {
    event.respondWith(fetch(req));
    return;
  }

  if (isAssetRequest(url)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req).then((res) => {
          const copy = res.clone();
          caches.open(ASSET_CACHE).then((cache) => cache.put(req, copy));
          return res;
        });
      })
    );
    return;
  }

  event.respondWith(fetch(req));
});
