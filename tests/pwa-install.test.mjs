import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import test from 'node:test';
import {
  INSTALL_DELAY_MS,
  isInstallDeferred,
  isStandalone,
  supportedPlatform,
} from '../public/pwa-install.js';

test('standalone detection supports display mode and iOS navigator flag', () => {
  assert.equal(isStandalone({
    navigator: { standalone: true },
    matchMedia: () => ({ matches: false }),
  }), true);
  assert.equal(isStandalone({
    navigator: { standalone: false },
    matchMedia: () => ({ matches: true }),
  }), true);
});

test('only supported mobile install paths are selected', () => {
  assert.equal(supportedPlatform({
    userAgent: 'Mozilla/5.0 (Linux; Android 15) Chrome/140 Mobile Safari/537.36',
    platform: 'Linux armv8l',
    maxTouchPoints: 5,
  }), 'android');
  assert.equal(supportedPlatform({
    userAgent: 'Mozilla/5.0 (iPhone) Version/18.0 Mobile/15E148 Safari/604.1',
    platform: 'iPhone',
    maxTouchPoints: 5,
  }), 'ios');
  assert.equal(supportedPlatform({
    userAgent: 'Mozilla/5.0 (iPhone) CriOS/140 Mobile/15E148 Safari/604.1',
    platform: 'iPhone',
    maxTouchPoints: 5,
  }), null);
  assert.equal(supportedPlatform({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0) Chrome/140 Safari/537.36',
    platform: 'Win32',
    maxTouchPoints: 0,
  }), null);
});

test('Maybe Later lasts seven days', () => {
  const now = 1_000;
  const storage = {
    getItem: () => String(now + INSTALL_DELAY_MS),
  };
  assert.equal(isInstallDeferred(storage, now), true);
  assert.equal(isInstallDeferred(storage, now + INSTALL_DELAY_MS), false);
});

test('manifests are valid and path-independent', () => {
  for (const filename of ['manifest.json', 'manifest-server.json']) {
    const manifest = JSON.parse(readFileSync(new URL(`../public/${filename}`, import.meta.url)));
    assert.equal(manifest.start_url, './dashboard');
    assert.equal(manifest.scope, './');
    assert.ok(manifest.icons.some((icon) => icon.sizes === '192x192'));
    assert.ok(manifest.icons.some((icon) => icon.sizes === '512x512'));

    for (const icon of manifest.icons) {
      assert.match(icon.src, /^\.\//);
      assert.equal(
        existsSync(new URL(`../public/${icon.src.slice(2)}`, import.meta.url)),
        true,
        `${icon.src} must exist`
      );
    }
  }
});

test('service worker caches only owned static resources', () => {
  const source = readFileSync(new URL('../public/pwa-worker.js', import.meta.url), 'utf8');
  assert.equal(
    readFileSync(new URL('../public/sw.js', import.meta.url), 'utf8'),
    "importScripts('./pwa-worker.js');\n"
  );
  assert.equal(
    readFileSync(new URL('../public/sw-server.js', import.meta.url), 'utf8'),
    "importScripts('./pwa-worker.js');\n"
  );
  assert.match(source, /request\.method !== 'GET'/);
  assert.match(source, /request\.mode === 'navigate'/);
  assert.match(source, /fetch\(request\)\.catch\(\(\) => caches\.match\(OFFLINE_URL\)\)/);
  assert.match(source, /name\.startsWith\(CACHE_PREFIX\)/);
  assert.doesNotMatch(source, /dashboard|attendance|payroll|addEventListener\('push'/);
});
