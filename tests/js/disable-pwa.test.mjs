import assert from 'node:assert/strict';
import { existsSync } from 'node:fs';
import test from 'node:test';

const scriptPath = new URL('../../public/js/disable-pwa.js', import.meta.url);

test('menonaktifkan service worker HRD dan cache offline tanpa mengganggu aplikasi lain', async () => {
    assert.ok(existsSync(scriptPath), 'Script penonaktifan PWA belum tersedia.');

    const { disableHrdPwa } = await import(scriptPath.href);
    const unregistered = [];
    const deletedCaches = [];

    const registrations = [
        {
            active: { scriptURL: 'https://hrd.example.test/hrd/sw.js' },
            unregister: async () => unregistered.push('hrd'),
        },
        {
            active: { scriptURL: 'https://hrd.example.test/other-app/sw.js' },
            unregister: async () => unregistered.push('other-app'),
        },
    ];

    const cacheStorage = {
        keys: async () => ['hrd-system-static-v2', 'hrd-system-v1', 'other-app-v1'],
        delete: async (name) => deletedCaches.push(name),
    };

    await disableHrdPwa({
        serviceWorker: { getRegistrations: async () => registrations },
        cacheStorage,
        baseUrl: 'https://hrd.example.test/hrd/',
    });

    assert.deepEqual(unregistered, ['hrd']);
    assert.deepEqual(deletedCaches, ['hrd-system-static-v2', 'hrd-system-v1']);
});
