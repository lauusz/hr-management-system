import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

function setup() {
    const template = readFileSync('resources/views/supervisor/leave_requests/master.blade.php', 'utf8');
    const script = template.match(/<script data-supervisor-leave-search>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script, 'Skrip pencarian harus tersedia');

    const elements = {};
    for (const selector of ['#filterForm', '#q', '#filterPanel', '#filterToggle', '#filterBadge', '#searchReset', '#searchError']) {
        elements[selector] = { listeners: {}, attrs: {}, props: {}, value: '', classes: new Set() };
    }
    elements['#filterForm'].action = 'https://example.test/supervisor/leave/master';
    elements['#filterForm'].dataset = { hasFilters: '0' };
    elements['#filterPanel'].props.hidden = true;
    const fragments = {
        '[data-supervisor-leave-stats]': { innerHTML: 'statistik lama' },
        '[data-supervisor-leave-results]': { innerHTML: 'hasil lama' },
    };
    const pending = [];
    let timer;
    let lastUrl;

    function $(selector) {
        const el = elements[selector];
        assert.ok(el, `Elemen tidak dikenal: ${selector}`);
        return {
            0: el,
            on(event, callback) { el.listeners[event] = callback; return this; },
            val() { return el.value; },
            prop(name, value) { if (arguments.length === 1) return el.props[name]; el.props[name] = value; return this; },
            attr(name, value) { el.attrs[name] = value; return this; },
            text(value) { el.text = value; return this; },
            toggleClass(name, value) { value ? el.classes.add(name) : el.classes.delete(name); return this; },
            serialize() { return `q=${encodeURIComponent(elements['#q'].value)}&status=APPROVED`; },
        };
    }
    $.ajax = ({ url }) => {
        const callbacks = {};
        const request = {
            url,
            aborted: false,
            done(fn) { callbacks.done = fn; return this; },
            fail(fn) { callbacks.fail = fn; return this; },
            always(fn) { callbacks.always = fn; return this; },
            abort() { this.aborted = true; callbacks.fail?.(null, 'abort'); callbacks.always?.(); },
            resolve(value) { callbacks.done?.(value); callbacks.always?.(); },
            reject() { callbacks.fail?.(null, 'error'); callbacks.always?.(); },
        };
        pending.push(request);
        return request;
    };
    const document = { querySelector: selector => fragments[selector] };
    const context = {
        jQuery: $, document, URL, URLSearchParams,
        DOMParser: class { parseFromString(html) { return { querySelector: selector => ({ innerHTML: `${selector}: ${html}` }) }; } },
        window: { location: { origin: 'https://example.test' }, history: { replaceState(_state, _title, url) { lastUrl = String(url); } } },
        setTimeout(fn) { timer = fn; return 1; },
        clearTimeout() { timer = null; },
    };
    vm.runInNewContext(script, context);
    return { elements, fragments, pending, runTimer: () => timer(), lastUrl: () => lastUrl };
}

test('pencarian memakai filter dan memperbarui hasil serta paginasi', () => {
    const ui = setup();
    ui.elements['#q'].value = 'andi';
    ui.elements['#q'].listeners.input();
    ui.runTimer();
    assert.match(ui.pending[0].url, /q=andi/);
    assert.match(ui.pending[0].url, /status=APPROVED/);
    ui.pending[0].resolve('hasil baru');
    assert.match(ui.fragments['[data-supervisor-leave-results]'].innerHTML, /hasil baru/);
    assert.match(ui.fragments['[data-supervisor-leave-stats]'].innerHTML, /hasil baru/);
    assert.match(ui.lastUrl(), /q=andi/);
});

test('pencarian lama dibatalkan saat input atau filter berubah', () => {
    const ui = setup();
    ui.elements['#q'].value = 'a';
    ui.elements['#q'].listeners.input();
    ui.runTimer();
    ui.elements['#q'].value = 'andi';
    ui.elements['#q'].listeners.input();
    assert.equal(ui.pending[0].aborted, true);
    ui.pending[0].resolve('hasil usang');
    assert.equal(ui.fragments['[data-supervisor-leave-results]'].innerHTML, 'hasil lama');
    ui.runTimer();
    ui.elements['#filterForm'].listeners.change({ target: {} });
    assert.equal(ui.pending[1].aborted, true);
});

test('filter dapat dibuka dan ditutup, error tetap mempertahankan hasil', () => {
    const ui = setup();
    ui.elements['#filterToggle'].listeners.click();
    assert.equal(ui.elements['#filterPanel'].props.hidden, false);
    assert.equal(ui.elements['#filterToggle'].attrs['aria-expanded'], 'true');
    ui.elements['#filterToggle'].listeners.click();
    assert.equal(ui.elements['#filterPanel'].props.hidden, true);
    ui.elements['#q'].listeners.input();
    ui.runTimer();
    ui.pending[0].reject();
    assert.equal(ui.elements['#searchError'].props.hidden, false);
    assert.equal(ui.fragments['[data-supervisor-leave-results]'].innerHTML, 'hasil lama');
});
