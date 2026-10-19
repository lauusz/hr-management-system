import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

function setup() {
    const template = readFileSync('resources/views/hr/overtime_requests/master.blade.php', 'utf8');
    const script = template.match(/<script data-overtime-detail-modal>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script);
    const element = () => ({
        listeners: {},
        addEventListener(name, callback) { this.listeners[name] = callback; },
    });
    const dialog = element();
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.listeners.close(); };
    const frame = element();
    frame.getAttribute = () => frame.src;
    const closeButton = element();
    const document = element();
    document.getElementById = id => ({
        'hom-detail-modal': dialog,
        'hom-detail-frame': frame,
        'hom-detail-modal-close': closeButton,
    })[id];
    let reloaded = false;
    const window = { location: { origin: 'https://example.test', reload() { reloaded = true; } } };
    vm.runInNewContext(script, { document, window, URL });
    return { document, dialog, frame, closeButton, reloaded: () => reloaded };
}

function clickLink(ui, options = {}) {
    const link = { href: 'https://example.test/hr/overtime-requests/2', focus() { this.focused = true; } };
    let prevented = false;
    ui.document.listeners.click({
        target: { closest: () => link }, button: 0,
        preventDefault() { prevented = true; }, ...options,
    });
    return { link, prevented: () => prevented };
}

test('detail dari tabel yang diperbarui terbuka dalam modal', () => {
    const ui = setup();
    const click = clickLink(ui);
    assert.equal(click.prevented(), true);
    assert.equal(ui.dialog.open, true);
    assert.equal(ui.frame.src, 'https://example.test/hr/overtime-requests/2?modal=1');
    ui.closeButton.listeners.click();
    assert.equal(ui.frame.src, 'about:blank');
    assert.equal(click.link.focused, true);
    assert.equal(ui.reloaded(), false);
});

test('tautan biasa tetap tersedia saat membuka tab baru', () => {
    const ui = setup();
    const click = clickLink(ui, { ctrlKey: true });
    assert.equal(click.prevented(), false);
    assert.equal(ui.dialog.open, undefined);
});

test('rekap diperbarui setelah perubahan berhasil dan modal ditutup', () => {
    const ui = setup();
    clickLink(ui);
    const frameDocument = elementDocument(true);
    ui.frame.contentDocument = frameDocument;
    ui.frame.listeners.load();
    frameDocument.listeners.keydown({ key: 'Escape' });
    assert.equal(ui.dialog.open, false);
    assert.equal(ui.reloaded(), true);
});

test('kesalahan penyimpanan tidak memuat ulang rekap', () => {
    const ui = setup();
    clickLink(ui);
    ui.frame.contentDocument = elementDocument(false);
    ui.frame.listeners.load();
    ui.closeButton.listeners.click();
    assert.equal(ui.reloaded(), false);
});

function elementDocument(success) {
    return {
        listeners: {},
        querySelector: () => success ? {} : null,
        addEventListener(name, callback) { this.listeners[name] = callback; },
    };
}
