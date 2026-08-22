import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import vm from 'node:vm';

const workerFiles = [
    '../../public/sw.js',
    '../../public/sw-server.js',
];

for (const workerFile of workerFiles) {
    test(`${workerFile} menghentikan mode offline dan membersihkan instalasi lama`, async () => {
        const source = await readFile(new URL(workerFile, import.meta.url), 'utf8');
        const listeners = new Map();
        const deletedCaches = [];
        const navigatedClients = [];
        let skipWaitingCalled = false;
        let unregisterCalled = false;

        const context = {
            caches: {
                keys: async () => ['hrd-system-static-v2', 'hrd-system-v1', 'other-app-v1'],
                delete: async (name) => deletedCaches.push(name),
            },
            self: {
                addEventListener: (eventName, listener) => listeners.set(eventName, listener),
                skipWaiting: async () => {
                    skipWaitingCalled = true;
                },
                registration: {
                    unregister: async () => {
                        unregisterCalled = true;
                    },
                },
                clients: {
                    matchAll: async () => [
                        {
                            url: 'https://hrd.example.test/hrd/login',
                            navigate: async (url) => navigatedClients.push(url),
                        },
                    ],
                },
            },
        };

        vm.runInNewContext(source, context, { filename: workerFile });

        assert.equal(listeners.has('fetch'), false, 'Retirement worker tidak boleh mencegat request.');
        assert.equal(listeners.has('install'), true);
        assert.equal(listeners.has('activate'), true);

        let installPromise;
        listeners.get('install')({ waitUntil: (promise) => { installPromise = promise; } });
        await installPromise;

        let activatePromise;
        listeners.get('activate')({ waitUntil: (promise) => { activatePromise = promise; } });
        await activatePromise;

        assert.equal(skipWaitingCalled, true);
        assert.equal(unregisterCalled, true);
        assert.deepEqual(deletedCaches, ['hrd-system-static-v2', 'hrd-system-v1']);
        assert.deepEqual(navigatedClients, ['https://hrd.example.test/hrd/login']);
    });
}

