import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

// Memuat pemuat hasil bersama (hr/_master_ajax) yang dipakai Master Absensi, Izin/Cuti, dan Lembur.
function setup() {
    const template = readFileSync('resources/views/hr/_master_ajax.blade.php', 'utf8');
    const script = template.match(/<script>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script, 'Skrip pemuat bersama harus tersedia');

    const created = [];
    const element = () => ({
        listeners: {}, hidden: false, value: '', innerHTML: '', textContent: '', attributes: {}, inert: false,
        classes: new Set(),
        classList: {
            add(name) { this.owner.classes.add(name); },
            toggle(name, on) { on ? this.owner.classes.add(name) : this.owner.classes.delete(name); },
        },
        addEventListener(name, listener) { this.listeners[name] = listener; },
        setAttribute(name, value) { this.attributes[name] = value; },
        querySelector() { return null; },
        contains() { return false; },
        before() {}, append() {}, focus() {},
    });
    const make = () => { const el = element(); el.classList.owner = el; return el; };

    const input = make();
    const results = make();
    results.innerHTML = 'hasil awal';
    const form = make();
    form.action = 'https://example.test/hr/master';
    const elements = { '#form': form, '#q': input, '[data-results]': results };
    let lastUrl;
    let assigned;
    let scheduled;
    const pending = [];
    const context = {
        console: { error() {} },
        URL, URLSearchParams, AbortController, Array, String,
        FormData: class { constructor() { return [['q', input.value], ['page', '3']]; } },
        DOMParser: class { parseFromString(html) { return { querySelector: () => (html === 'tidak lengkap' ? null : { innerHTML: html }) }; } },
        document: {
            activeElement: null,
            listeners: {},
            addEventListener(name, listener) { this.listeners[name] = listener; },
            querySelector: (selector) => elements[selector] ?? null,
            createElement: () => { const el = make(); created.push(el); return el; },
        },
        window: {
            location: { origin: 'https://example.test', href: 'https://example.test/hr/master?page=3', assign(url) { assigned = String(url); } },
            history: { replaceState(_s, _t, url) { lastUrl = String(url); } },
        },
        setTimeout(fn) { scheduled = fn; return 1; },
        clearTimeout() { scheduled = null; },
        fetch(url, options) { return new Promise((resolve) => pending.push({ url, options, resolve })); },
    };
    vm.runInNewContext(script, context);
    const ajax = context.window.MasterAjax.init({ form: '#form', search: '#q', regions: ['[data-results]'] });
    return { ajax, form, input, results, pending, created, lastUrl: () => lastUrl, assigned: () => assigned, runTimer: () => scheduled() };
}

const finish = () => new Promise((resolve) => setImmediate(resolve));
const ok = (text) => ({ ok: true, status: 200, redirected: false, text: async () => text });

test('submit filter memuat hasil tanpa navigasi, kembali ke halaman pertama, dan mengakhiri loading', async () => {
    const ui = setup();
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    ui.form.listeners.submit(event);
    assert.equal(event.prevented, true);
    assert.equal(ui.results.attributes['aria-busy'], 'true');
    assert.equal(ui.results.inert, true);
    assert.equal(ui.pending[0].url.searchParams.has('page'), false);
    ui.pending[0].resolve(ok('hasil baru'));
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil baru');
    assert.equal(ui.results.attributes['aria-busy'], 'false');
    assert.equal(ui.results.inert, false);
    assert.match(ui.lastUrl(), /^https:\/\/example\.test\/hr\/master/);
});

test('respons request lama tidak menimpa hasil terbaru', async () => {
    const ui = setup();
    ui.input.value = 'a';
    ui.input.listeners.input();
    ui.runTimer();
    ui.input.value = 'andi';
    ui.input.listeners.input();
    ui.runTimer();
    assert.equal(ui.pending[0].options.signal.aborted, true);
    ui.pending[0].resolve(ok('hasil lama'));
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil awal');
    assert.equal(ui.results.attributes['aria-busy'], 'true');
    ui.pending[1].resolve(ok('hasil baru'));
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil baru');
    assert.equal(ui.results.attributes['aria-busy'], 'false');
});

test('kegagalan mempertahankan hasil lama, menampilkan error, dan tidak mengubah URL', async () => {
    const ui = setup();
    ui.form.listeners.submit({ preventDefault() {} });
    ui.pending[0].resolve({ ok: false, status: 500, text: async () => '' });
    await finish();
    const errorBox = ui.created[1];
    assert.equal(ui.results.innerHTML, 'hasil awal');
    assert.equal(errorBox.hidden, false);
    assert.equal(errorBox.textContent, 'Data gagal dimuat. Silakan coba lagi.');
    assert.equal(ui.results.attributes['aria-busy'], 'false');
    assert.equal(ui.lastUrl(), undefined);
});

test('respons tidak lengkap tidak mengganti hasil', async () => {
    const ui = setup();
    ui.form.listeners.submit({ preventDefault() {} });
    ui.pending[0].resolve(ok('tidak lengkap'));
    await finish();
    assert.equal(ui.results.innerHTML, 'hasil awal');
    assert.equal(ui.created[1].hidden, false);
    assert.equal(ui.lastUrl(), undefined);
});

test('sesi berakhir mengarahkan sekali ke aplikasi, bukan menampilkan error', async () => {
    const ui = setup();
    ui.form.listeners.submit({ preventDefault() {} });
    ui.pending[0].resolve({ ok: false, status: 419, text: async () => '' });
    await finish();
    assert.match(ui.assigned(), /hr\/master/);
    assert.equal(ui.results.innerHTML, 'hasil awal');
});
