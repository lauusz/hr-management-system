/* SERVICE WORKER - HRD TRIGUNA (PWA)
  Adjusted for IIS Subfolder Deployment (/hrd)
*/

const VERSION = 'hrd-pwa-v2'; // Ubah versi ini jika kamu update file assets kedepannya
const BASE_PATH = '/hrd';     // PENTING: Sesuai nama folder aplikasi di IIS kamu
const ASSET_CACHE = `assets-${VERSION}`;

// Daftar file yang wajib didownload agar aplikasi bisa buka saat offline
const PRECACHE_URLS = [
  `${BASE_PATH}/offline.html`,
  `${BASE_PATH}/pwa/icon-192.png`,
  `${BASE_PATH}/pwa/icon-512.png`
  // Tambahkan CSS/JS utama jika ingin halaman offline lebih cantik
  // Contoh: `${BASE_PATH}/css/app.css`,
];

function isSameOrigin(url) {
  return self.location.origin === url.origin;
}

// Fungsi cek apakah request adalah asset (gambar, js, css)
function isAssetRequest(reqUrl) {
  const path = reqUrl.pathname || '';
  
  // Cek apakah path mengandung folder asset kita (disesuaikan dengan BASE_PATH)
  // Kita gunakan .includes() agar aman walaupun ada di subfolder
  return (
    path.includes('/build/') ||
    path.includes('/css/') ||
    path.includes('/js/') ||
    path.includes('/images/') ||
    path.includes('/pwa/') ||
    // Cek ekstensi file umum
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

// Fungsi cek path sensitif yang TIDAK BOLEH di-cache (selalu ambil dari server)
function isSensitivePath(reqUrl) {
  const p = reqUrl.pathname || '';
  
  // Pastikan request ke API/Database/Logout tidak tersimpan di cache
  return (
    p.includes('/storage/') ||
    p.includes('/leave-requests') ||
    p.includes('/attendance') ||
    p.includes('/hr/') ||
    p.includes('/supervisor/') ||
    p.includes('/logout') ||
    p.includes('/login')
  );
}

// 1. INSTALL: Download file penting (Precache)
self.addEventListener('install', (event) => {
  console.log('[SW] Installing...');
  event.waitUntil(
    caches.open(ASSET_CACHE)
      .then((cache) => {
        console.log('[SW] Caching offline page & icons');
        return cache.addAll(PRECACHE_URLS);
      })
      .catch(err => console.error('[SW] Precache failed:', err))
  );
  self.skipWaiting();
});

// 2. ACTIVATE: Hapus cache versi lama jika ada update VERSION
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating...');
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

// 3. FETCH: Mengatur lalu lintas data (Network vs Cache)
self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Hanya proses method GET
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Abaikan request ke domain luar
  if (!isSameOrigin(url)) return;

  const accept = req.headers.get('accept') || '';
  // Cek apakah user sedang membuka halaman website (Navigasi)
  const isNavigate = req.mode === 'navigate' || accept.includes('text/html');

  // A. STRATEGI NAVIGASI (Halaman HTML)
  if (isNavigate) {
    event.respondWith(
      fetch(req).catch(() => {
        // JIKA OFFLINE/ERROR: Tampilkan halaman offline.html dari cache
        console.log('[SW] Network fail, serving offline page');
        return caches.match(`${BASE_PATH}/offline.html`);
      })
    );
    return;
  }

  // B. STRATEGI PATH SENSITIF (Jangan Cache)
  if (isSensitivePath(url)) {
    event.respondWith(fetch(req));
    return;
  }

  // C. STRATEGI ASET (Stale-While-Revalidate / Cache First)
  if (isAssetRequest(url)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        // Jika ada di cache, pakai itu dulu
        if (cached) return cached;
        
        // Jika tidak, ambil dari network dan simpan ke cache
        return fetch(req).then((res) => {
          // Cek validitas respon
          if (!res || res.status !== 200 || res.type !== 'basic') {
            return res;
          }
          
          const copy = res.clone();
          caches.open(ASSET_CACHE).then((cache) => cache.put(req, copy));
          return res;
        });
      })
    );
    return;
  }

  // D. DEFAULT: Ambil dari network biasa
  event.respondWith(fetch(req));
});