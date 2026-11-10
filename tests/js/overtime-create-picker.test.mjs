import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync('resources/views/overtime_requests/create.blade.php', 'utf8');

function setup() {
    const script = source.match(/<script>\s*(document\.addEventListener\('DOMContentLoaded'[\s\S]*?)<\/script>/)?.[1];
    assert.ok(script);
    const listeners = {};
    const element = () => ({
        value: '', textContent: '', disabled: false, style: {}, listeners: {},
        addEventListener(name, callback) { this.listeners[name] = callback; },
        click() { this.listeners.click?.(); },
        focus() { document.focused = this; },
    });
    const ids = {};
    for (const id of ['start_time', 'end_time', 'otc-total-value', 'otc-time-picker', 'otc-picker-hour', 'otc-picker-minute', 'otc-picker-cancel', 'otc-picker-save', 'btn-submit', 'btn-text', 'btn-spinner']) ids[id] = element();
    ids['otc-time-picker'].showModal = function () { this.open = true; };
    ids['otc-time-picker'].close = function () { this.open = false; };
    const buttons = ['start_time', 'end_time'].map(field => Object.assign(element(), { dataset: { timeField: field } }));
    const form = element();
    const document = {
        addEventListener(name, callback) { listeners[name] = callback; },
        getElementById(id) { return ids[id]; },
        querySelector() { return form; },
        querySelectorAll() { return buttons; },
    };
    vm.runInNewContext(script, { document, alert() {} });
    listeners.DOMContentLoaded();
    return { ids, buttons, form };
}

test('picker loads values, saves only the target, and cancel preserves values', () => {
    const { ids, buttons } = setup();
    ids.start_time.value = '08:30';
    ids.end_time.value = '17:00';
    buttons[0].click();
    assert.equal(ids['otc-picker-hour'].value, '08');
    assert.equal(ids['otc-picker-minute'].value, '30');
    ids['otc-picker-hour'].value = '09';
    ids['otc-picker-cancel'].click();
    assert.equal(ids.start_time.value, '08:30');
    buttons[1].click();
    assert.equal(ids['otc-picker-hour'].value, '17');
    assert.equal(ids['otc-picker-minute'].value, '00');
    ids['otc-picker-hour'].value = '18';
    ids['otc-picker-minute'].value = '30';
    ids['otc-picker-save'].click();
    assert.equal(ids.end_time.value, '18:30');
    assert.equal(ids.start_time.value, '08:30');
});

test('submit requires both times', () => {
    const { ids, form } = setup();
    let prevented = false;
    const submit = () => form.listeners.submit({ preventDefault() { prevented = true; } });
    submit();
    assert.equal(prevented, true);
    ids.start_time.value = '08:00';
    prevented = false;
    submit();
    assert.equal(prevented, true);
    ids.end_time.value = '17:00';
    prevented = false;
    submit();
    assert.equal(prevented, false);
});

test('picker minute choices are 00 and 30 only', () => {
    const select = source.match(/<select id="otc-picker-minute"[\s\S]*?<\/select>/)?.[0];
    assert.ok(select);
    assert.deepEqual([...select.matchAll(/<option value="([^"]+)">/g)].map(match => match[1]), ['00', '30']);
});

test('break dropdown defines the requested options and preserves old selection', () => {
    const block = source.match(/<select name="break_duration_minutes"[\s\S]*?<\/select>/)?.[0];
    assert.ok(block);
    const options = block.match(/@foreach \(\[(.*?)\] as/s)?.[1];
    assert.ok(options);
    assert.deepEqual([...options.matchAll(/(\d+) => '([^']+)'/g)].map(match => [Number(match[1]), match[2]]), [
        [0, 'Tanpa istirahat'], [30, '30 menit'], [60, '1 jam'], [90, '1 jam 30 menit'], [120, '2 jam'],
        [150, '2 jam 30 menit'], [180, '3 jam'], [210, '3 jam 30 menit'], [240, '4 jam'],
        [270, '4 jam 30 menit'], [300, '5 jam'],
    ]);
    assert.match(block, /old\('break_duration_minutes', '0'\)/);
    assert.doesNotMatch(source, /<input[^>]+name="break_(?:hours|minutes|duration_minutes)"/);
});

test('infinite wheel scrollers sync with hour and minute values and wrap seamlessly', () => {
    const script = source.match(/<script>\s*(document\.addEventListener\('DOMContentLoaded'[\s\S]*?)<\/script>/)?.[1];
    const listeners = {};
    const makeNode = () => ({
        value: '', textContent: '', innerHTML: '', scrollTop: 0, disabled: false, style: {}, dataset: {}, children: [], listeners: {},
        classList: {
            classes: new Set(),
            toggle(c, force) { force ? this.classes.add(c) : this.classes.delete(c); },
            contains(c) { return this.classes.has(c); },
        },
        setAttribute() {},
        appendChild(child) { this.children.push(child); },
        addEventListener(name, cb) { this.listeners[name] = cb; },
        click() { this.listeners.click?.(); },
        focus() {},
        scrollTo({ top }) { this.scrollTop = top; this.listeners.scroll?.(); },
    });
    const ids = {};
    for (const id of ['overtime_date', 'start_time', 'end_time', 'otc-total-value', 'otc-time-picker', 'otc-time-picker-title', 'otc-picker-hour', 'otc-picker-minute', 'otc-wheel-hour', 'otc-wheel-minute', 'otc-picker-cancel', 'otc-picker-save', 'otc-break-picker', 'break_duration_minutes', 'break_duration_minutes_button', 'otc-wheel-break', 'otc-break-picker-save', 'btn-submit', 'btn-text', 'btn-spinner']) ids[id] = makeNode();
    ids['otc-time-picker'].showModal = function () { this.open = true; };
    ids['otc-time-picker'].close = function () { this.open = false; };
    ids['otc-break-picker'].showModal = function () { this.open = true; };
    ids['otc-break-picker'].close = function () { this.open = false; };
    const buttons = [
        Object.assign(makeNode(), { dataset: { timeField: 'start_time', pickerTitle: 'Pilih jam mulai lembur' } }),
        Object.assign(makeNode(), { dataset: { timeField: 'end_time', pickerTitle: 'Pilih jam selesai lembur' } }),
    ];
    const form = makeNode();
    const document = {
        addEventListener(name, cb) { listeners[name] = cb; },
        getElementById(id) { return ids[id]; },
        createElement() { return makeNode(); },
        querySelector() { return form; },
        querySelectorAll() { return buttons; },
    };
    class MockDate {
        getHours() { return 9; }
    }
    let flatpickrArgs = null;
    function flatpickr(el, opts) { flatpickrArgs = { el, opts }; }
    vm.runInNewContext(script, { document, Date: MockDate, flatpickr, alert() {}, setTimeout() {}, clearTimeout() {} });
    listeners.DOMContentLoaded();

    assert.equal(flatpickrArgs?.el, ids.overtime_date);
    assert.equal(flatpickrArgs?.opts?.mode, 'single');
    assert.equal(flatpickrArgs?.opts?.dateFormat, 'Y-m-d');

    assert.equal(ids['otc-wheel-hour'].children.length, 24 * 7);
    assert.equal(ids['otc-wheel-minute'].children.length, 2);
    assert.deepEqual(ids['otc-wheel-minute'].children.map(c => c.textContent), ['00', '30']);

    // Default when empty uses current hour (09:57 -> 09:00)
    buttons[0].click();
    assert.equal(ids['otc-picker-hour'].value, '09');
    assert.equal(ids['otc-picker-minute'].value, '00');

    ids.start_time.value = '23:30';
    buttons[0].click();
    assert.equal(ids['otc-time-picker-title'].textContent, 'Pilih jam mulai lembur');
    assert.equal(ids['otc-picker-hour'].value, '23');
    assert.equal(ids['otc-picker-minute'].value, '30');

    buttons[1].click();
    assert.equal(ids['otc-time-picker-title'].textContent, 'Pilih jam selesai lembur');

    // Scroll near top boundary (cycle 0, index 5 -> hour '05') and verify infinite wrap recenters to middle cycle
    ids['otc-wheel-hour'].scrollTop = 5 * 44;
    ids['otc-wheel-hour'].listeners.scroll();
    assert.equal(ids['otc-picker-hour'].value, '05');
    assert.equal(ids['otc-wheel-hour'].scrollTop, (3 * 24 + 5) * 44);

    // Break duration modal wheel selects and saves value + label
    assert.equal(ids['otc-wheel-break'].children.length, 11);
    ids.break_duration_minutes.value = '0';
    ids.break_duration_minutes_button.click();
    assert.equal(ids['otc-break-picker'].open, true);
    ids['otc-wheel-break'].scrollTop = 3 * 44; // index 3 -> 90 ('1 jam 30 menit')
    ids['otc-wheel-break'].listeners.scroll();
    ids['otc-break-picker-save'].click();
    assert.equal(ids.break_duration_minutes.value, '90');
    assert.equal(ids.break_duration_minutes_button.textContent, '1 jam 30 menit');
    assert.equal(ids['otc-break-picker'].open, false);
});

function setupAsyncWheel(initial = {}) {
    const script = source.match(/<script>\s*(document\.addEventListener\('DOMContentLoaded'[\s\S]*?)<\/script>/)?.[1];
    const listeners = {};
    const node = () => ({
        value: '', textContent: '', scrollTop: 0, dataset: {}, children: [], listeners: {}, style: {},
        classList: { toggle() {}, add() {}, remove() {} },
        setAttribute() {}, appendChild(child) { this.children.push(child); },
        addEventListener(name, callback) { this.listeners[name] = callback; },
        click(event = {}) { this.listeners.click?.(event); }, focus() {},
        scrollTo({ top, behavior }) {
            if (behavior === 'instant') { this.pendingTarget = null; this.scrollTop = top; }
            else this.pendingTarget = top;
        },
        intermediate(top) { this.scrollTop = top; this.listeners.scroll?.(); },
        showModal() { this.open = true; }, close() { this.open = false; },
    });
    const ids = {};
    for (const id of ['start_time', 'end_time', 'otc-total-value', 'otc-time-picker', 'otc-time-picker-title', 'otc-picker-hour', 'otc-picker-minute', 'otc-wheel-hour', 'otc-wheel-minute', 'otc-picker-save', 'otc-break-picker', 'break_duration_minutes', 'break_duration_minutes_button', 'otc-wheel-break', 'otc-break-picker-save', 'btn-submit', 'btn-text', 'btn-spinner']) ids[id] = node();
    for (const [id, value] of Object.entries(initial)) ids[id].value = value;
    const buttons = ['start_time', 'end_time'].map(field => Object.assign(node(), { dataset: { timeField: field } }));
    const form = node();
    const document = {
        addEventListener(name, cb) { listeners[name] = cb; },
        getElementById(id) { return ids[id]; }, createElement: node,
        querySelector() { return form; }, querySelectorAll() { return buttons; },
    };
    vm.runInNewContext(script, { document, alert() {}, setTimeout() {}, clearTimeout() {} });
    listeners.DOMContentLoaded();
    return { ids, buttons };
}

test('async wheel click then immediate save commits start and end target', () => {
    const { ids, buttons } = setupAsyncWheel();
    ids.start_time.value = '08:00';
    ids.end_time.value = '17:00';
    buttons[0].click();
    ids['otc-wheel-hour'].children[3 * 24 + 12].click();
    ids['otc-picker-save'].click();
    assert.equal(ids.start_time.value, '12:00');
    buttons[1].click();
    ids['otc-wheel-hour'].children[3 * 24 + 20].click();
    ids['otc-picker-save'].click();
    assert.equal(ids.end_time.value, '20:00');
    assert.equal(ids.start_time.value, '12:00');
});

test('async wheel click saves minute target without waiting for scrolling', () => {
    const { ids, buttons } = setupAsyncWheel();
    ids.start_time.value = '08:00';
    buttons[0].click();
    ids['otc-wheel-minute'].children[1].click();
    ids['otc-picker-save'].click();
    assert.equal(ids.start_time.value, '08:30');
});

test('async wheel click saves break target without waiting for scrolling', () => {
    const { ids } = setupAsyncWheel();
    ids.break_duration_minutes.value = '0';
    ids.break_duration_minutes_button.click();
    ids['otc-wheel-break'].children[3].click();
    ids['otc-break-picker-save'].click();
    assert.equal(ids.break_duration_minutes.value, '90');
});

test('a click during scrolling cancels animation and queued scroll cannot restore an intermediate value', () => {
    const { ids, buttons } = setupAsyncWheel();
    ids.start_time.value = '08:00';
    buttons[0].click();
    const wheel = ids['otc-wheel-hour'];
    wheel.scrollTo({ top: (3 * 24 + 16) * 44, behavior: 'smooth' });
    wheel.intermediate((3 * 24 + 10) * 44);
    wheel.children[3 * 24 + 12].click();
    assert.equal(wheel.pendingTarget, null);
    wheel.listeners.scroll();
    ids['otc-picker-save'].click();
    assert.equal(ids.start_time.value, '12:00');
});

test('cancel after wheel selection preserves previously committed values', () => {
    const { ids, buttons } = setupAsyncWheel();
    ids.start_time.value = '08:00';
    buttons[0].click();
    ids['otc-wheel-hour'].children[3 * 24 + 12].click();
    ids['otc-time-picker'].click({ target: ids['otc-time-picker'] });
    assert.equal(ids.start_time.value, '08:00');
    ids.break_duration_minutes.value = '0';
    ids.break_duration_minutes_button.click();
    ids['otc-wheel-break'].children[3].click();
    ids['otc-break-picker'].click({ target: ids['otc-break-picker'] });
    assert.equal(ids.break_duration_minutes.value, '0');
});

test('total preview initializes from old input and validates times and breaks', () => {
    assert.match(source, /Durasi Lembur/);
    assert.doesNotMatch(source, /Setelah dikurangi istirahat/);
    const cases = [
        ['16:00', '18:00', '30', '1 jam 30 menit'],
        ['16:00', '18:00', '0', '2 jam'],
        ['23:30', '00:30', '30', '30 menit'],
        ['18:00', '02:00', '90', '6 jam 30 menit'],
        ['16:00', '', '0', '0 jam'],
        ['16:00', '16:00', '0', 'Jam selesai lembur tidak boleh sama dengan jam mulai.'],
        ['16:00', '18:00', '120', 'Istirahat harus lebih singkat dari durasi lembur.'],
        ['16:00', '18:00', '150', 'Istirahat harus lebih singkat dari durasi lembur.'],
        ['24:00', '18:00', '0', 'Jam lembur tidak valid.'],
        ['16:00', '18:00', 'abc', 'Durasi istirahat tidak valid.'],
    ];
    for (const [start, end, breakMinutes, expected] of cases) {
        const { ids } = setupAsyncWheel({ start_time: start, end_time: end, break_duration_minutes: breakMinutes });
        assert.equal(ids['otc-total-value'].textContent, expected);
    }
});

test('total preview updates only on confirmation and is unchanged when cancelled', () => {
    const { ids, buttons } = setupAsyncWheel({ start_time: '16:00', end_time: '18:00', break_duration_minutes: '30' });
    const total = ids['otc-total-value'];
    assert.equal(total.textContent, '1 jam 30 menit');
    buttons[0].click();
    ids['otc-wheel-hour'].children[3 * 24 + 17].click();
    assert.equal(total.textContent, '1 jam 30 menit');
    ids['otc-time-picker'].click({ target: ids['otc-time-picker'] });
    assert.equal(total.textContent, '1 jam 30 menit');
    buttons[0].click();
    ids['otc-wheel-hour'].children[3 * 24 + 17].click();
    ids['otc-picker-save'].click();
    assert.equal(total.textContent, '30 menit');
    buttons[1].click();
    ids['otc-wheel-hour'].children[3 * 24 + 19].click();
    ids['otc-picker-save'].click();
    assert.equal(total.textContent, '1 jam 30 menit');
    ids.break_duration_minutes_button.click();
    ids['otc-wheel-break'].children[0].click();
    assert.equal(total.textContent, '1 jam 30 menit');
    ids['otc-break-picker'].click({ target: ids['otc-break-picker'] });
    assert.equal(total.textContent, '1 jam 30 menit');
    ids.break_duration_minutes_button.click();
    ids['otc-wheel-break'].children[0].click();
    ids['otc-break-picker-save'].click();
    assert.equal(total.textContent, '2 jam');
});
