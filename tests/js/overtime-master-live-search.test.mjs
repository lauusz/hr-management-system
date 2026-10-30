import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

function setup() {
    const template = readFileSync('resources/views/hr/overtime_requests/master.blade.php', 'utf8');
    const script = template.match(/<script data-overtime-live-search>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script, 'Skrip pencarian dinamis harus tersedia');

    const element = () => ({
        listeners: {}, hidden: false, value: '', innerHTML: '', attributes: {},
        classList: { add() {}, remove() {}, toggle() {} },
        addEventListener(name, listener) { this.listeners[name] = listener; },
        setAttribute(name, value) { this.attributes[name] = value; },
    });
    const input = element();
    const status = element();
    const date = element();
    const results = element();
    const reset = element();
    const panel = element();
    const toggle = element();
    const badge = element();
    const error = element();
    date.value = '2026-10-01 sampai 2026-10-31';
    const form = element();
    form.action = 'https://example.test/hr/overtime-requests/master';
    form.dataset = { defaultRange: date.value };
    form.elements = { status, overtime_date_range: date };
    form.querySelector = () => input;
    const elements = {
        'hom-filter-form': form, 'hom-filter-panel': panel, 'hom-filter-toggle': toggle,
        'hom-search-reset': reset, 'hom-search-error': error, 'hom-filter-badge': badge,
    };
    const document = {
        listeners: {},
        addEventListener(name, listener) { this.listeners[name] = listener; },
        getElementById(id) { return elements[id]; },
        querySelector(selector) { return selector === '[data-overtime-results]' ? results : null; },
    };
    let scheduled;
    let lastUrl;
    const pending = [];
    const context = {
        document, URL, URLSearchParams, AbortController,
        FormData: class { constructor() { return [['q', input.value], ['status', status.value], ['overtime_date_range', date.value], ['page', '3']]; } },
        DOMParser: class { parseFromString(html) { return { querySelector() { return { innerHTML: html }; } }; } },
        window: { location: { origin: 'https://example.test', href: 'https://example.test/hr/overtime-requests/master?status=PENDING_SUPERVISOR&page=3' }, history: { replaceState(_state, _title, url) { lastUrl = String(url); } } },
        setTimeout(fn) { scheduled = fn; return 1; },
        clearTimeout() { scheduled = null; },
        fetch(url, options) { return new Promise(resolve => pending.push({ url, options, resolve })); },
    };
    vm.runInNewContext(script, context);
    return { document, input, status, date, results, reset, panel, toggle, error, pending, runTimer: () => scheduled(), lastUrl: () => lastUrl };
}

test('pembaruan setelah pembatalan mempertahankan filter dan halaman aktif', async () => {
    const ui = setup();
    ui.document.listeners['overtime-refresh-results']();
    const request = ui.pending[0];
    assert.equal(request.url.searchParams.get('status'), 'PENDING_SUPERVISOR');
    assert.equal(request.url.searchParams.get('page'), '3');
    request.resolve({ ok: true, text: async () => 'tabel terbaru' });
    await finish();
    assert.equal(ui.results.innerHTML, 'tabel terbaru');
});

const finish = () => new Promise(resolve => setImmediate(resolve));

test('pencarian menggunakan filter dan memperbarui tabel beserta paginasi', async () => {
    const ui = setup();
    ui.status.value = 'REJECTED';
    ui.input.value = 'andi';
    ui.input.listeners.input();
    assert.equal(ui.reset.hidden, false);
    ui.runTimer();
    const request = ui.pending[0];
    assert.equal(request.url.searchParams.get('q'), 'andi');
    assert.equal(request.url.searchParams.get('status'), 'REJECTED');
    assert.equal(request.url.searchParams.get('overtime_date_range'), ui.date.value);
    assert.equal(request.url.searchParams.has('page'), false);
    request.resolve({ ok: true, text: async () => 'tabel dan halaman terbaru' });
    await finish();
    assert.equal(ui.results.innerHTML, 'tabel dan halaman terbaru');
    assert.match(ui.lastUrl(), /q=andi/);
    assert.equal(ui.results.attributes['aria-busy'], 'false');
});

test('hasil lama tidak menggantikan pencarian baru yang sedang diketik', async () => {
    const ui = setup();
    ui.input.value = 'a';
    ui.input.listeners.input();
    ui.runTimer();
    ui.input.value = 'andi';
    ui.input.listeners.input();
    assert.equal(ui.pending[0].options.signal.aborted, true);
    ui.pending[0].resolve({ ok: true, text: async () => 'hasil lama' });
    await finish();
    assert.notEqual(ui.results.innerHTML, 'hasil lama');
    ui.runTimer();
    ui.pending[1].resolve({ ok: true, text: async () => 'hasil baru' });
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil baru');
});

test('perubahan filter membatalkan hasil pencarian yang sudah tidak sesuai', async () => {
    const ui = setup();
    ui.input.value = 'andi';
    ui.input.listeners.input();
    ui.runTimer();
    ui.status.value = 'REJECTED';
    ui.status.listeners.change();
    assert.equal(ui.pending[0].options.signal.aborted, true);
    ui.pending[0].resolve({ ok: true, text: async () => 'hasil sebelum filter diubah' });
    await finish();
    assert.notEqual(ui.results.innerHTML, 'hasil sebelum filter diubah');
});

test('kegagalan pencarian ditampilkan tanpa menghapus hasil sebelumnya', async () => {
    const ui = setup();
    ui.results.innerHTML = 'hasil sebelumnya';
    ui.input.value = 'andi';
    ui.input.listeners.input();
    ui.runTimer();
    ui.pending[0].resolve({ ok: false });
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil sebelumnya');
    assert.equal(ui.error.hidden, false);
    assert.equal(ui.results.attributes['aria-busy'], 'false');
});

test('filter dapat dibuka dan ditutup dengan status aksesibilitas', () => {
    const ui = setup();
    ui.panel.hidden = true;
    ui.toggle.listeners.click();
    assert.equal(ui.panel.hidden, false);
    assert.equal(ui.toggle.attributes['aria-expanded'], 'true');
    ui.toggle.listeners.click();
    assert.equal(ui.panel.hidden, true);
    assert.equal(ui.toggle.attributes['aria-expanded'], 'false');
});
