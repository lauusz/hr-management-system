const HRD_CACHE_PREFIX = 'hrd-system-';

function isHrdServiceWorker(registration, baseUrl) {
    const worker = registration.active ?? registration.waiting ?? registration.installing;

    if (!worker?.scriptURL) {
        return false;
    }

    return new URL(worker.scriptURL, baseUrl).href === new URL('sw.js', baseUrl).href;
}

export async function disableHrdPwa(options = {}) {
    const serviceWorker = options.serviceWorker
        ?? (typeof navigator !== 'undefined' ? navigator.serviceWorker : null);
    const cacheStorage = options.cacheStorage
        ?? (typeof caches !== 'undefined' ? caches : null);
    const baseUrl = options.baseUrl ?? new URL('../', import.meta.url).href;

    const unregisterWorkers = async () => {
        if (!serviceWorker?.getRegistrations) {
            return;
        }

        const registrations = await serviceWorker.getRegistrations();
        const hrdRegistrations = registrations.filter((registration) => (
            isHrdServiceWorker(registration, baseUrl)
        ));

        await Promise.allSettled(
            hrdRegistrations.map((registration) => registration.unregister())
        );
    };

    const deleteOfflineCaches = async () => {
        if (!cacheStorage?.keys) {
            return;
        }

        const cacheNames = await cacheStorage.keys();
        const hrdCacheNames = cacheNames.filter((name) => name.startsWith(HRD_CACHE_PREFIX));

        await Promise.allSettled(
            hrdCacheNames.map((name) => cacheStorage.delete(name))
        );
    };

    await Promise.allSettled([
        unregisterWorkers(),
        deleteOfflineCaches(),
    ]);
}

if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
        disableHrdPwa();
    }, { once: true });
}
