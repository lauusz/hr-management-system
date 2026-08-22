const CACHE_PREFIX = 'hrd-system-';

self.addEventListener('install', (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const cacheNames = await caches.keys().catch(() => []);

    await Promise.allSettled(
      cacheNames
        .filter((name) => name.startsWith(CACHE_PREFIX))
        .map((name) => caches.delete(name))
    );

    await Promise.allSettled([self.registration.unregister()]);

    const windowClients = await self.clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).catch(() => []);

    await Promise.allSettled(
      windowClients.map((client) => client.navigate(client.url))
    );
  })());
});
