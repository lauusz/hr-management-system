import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

test('menjaga perubahan yang belum disimpan saat kembali dari halaman detail akses absensi', () => {
    const template = readFileSync('resources/views/hr/attendances/access-detail.blade.php', 'utf8');
    const script = template.match(/<script data-attendance-unsaved-guard>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script);

    const makeEl = () => ({ listeners: {}, addEventListener(name, fn) { this.listeners[name] = fn; } });
    const backBtn = makeEl();
    const cancelBtn = makeEl();
    const checkbox = { checked: true, defaultChecked: false };
    const document = {
        getElementById: (id) => (id === 'ava-back-to-list' ? backBtn : id === 'ava-cancel-btn' ? cancelBtn : null),
        querySelectorAll: () => [checkbox],
    };

    vm.runInNewContext(script, { document, confirm: () => false });

    let preventedBack = false;
    let preventedCancel = false;
    backBtn.listeners.click({ preventDefault() { preventedBack = true; } });
    cancelBtn.listeners.click({ preventDefault() { preventedCancel = true; } });

    assert.equal(preventedBack, true);
    assert.equal(preventedCancel, true);
});

test('memfilter dan mencentang hasil yang tampil pada detail akses tanpa menghapus pilihan tersembunyi', () => {
    const template = readFileSync('resources/views/hr/attendances/access-detail.blade.php', 'utf8');
    const script = template.match(/<script data-attendance-selection>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script);

    const element = () => ({ listeners: {}, value: '', addEventListener(name, fn) { this.listeners[name] = fn; } });
    const first = element(); first.value = '1'; first.checked = true;
    const second = element(); second.value = '2'; second.checked = false;
    const rows = [
        { dataset: { name: 'andi', pt: '1', division: '1' }, querySelector: () => first },
        { dataset: { name: 'budi', pt: '2', division: '2' }, querySelector: () => second },
    ];
    const form = element(); const search = element(); const pt = element(); const division = element();
    const select = element(); const count = element(); const values = element();
    const elements = {
        'attendance-access-form': form,
        'ava-search': search,
        'ava-pt': pt,
        'ava-division': division,
        'ava-select-visible': select,
        'ava-selected-count': count,
        'attendance-access-values': values,
    };
    const document = { getElementById: (id) => elements[id], querySelectorAll: () => rows };

    vm.runInNewContext(script, { document });
    search.value = 'budi'; search.listeners.input();
    select.checked = true; select.listeners.change();
    form.listeners.submit();

    assert.equal(values.value, '[1,2]');
    assert.equal(count.textContent, '2 dipilih');
});

test('membuka modal tambah penerima akses dan memfilter daftar pengguna pada halaman master', () => {
    const template = readFileSync('resources/views/hr/attendances/access.blade.php', 'utf8');
    const script = template.match(/<script data-attendance-master>\s*([\s\S]*?)\s*<\/script>/)?.[1];
    assert.ok(script);

    const element = () => ({
        listeners: {},
        value: '',
        hidden: false,
        style: { display: 'none' },
        classList: { toggle() {} },
        setAttribute() {},
        focus() {},
        addEventListener(name, fn) { this.listeners[name] = fn; },
    });

    const openBtn = element();
    const modal = element(); modal.hidden = true;
    const modalSearch = element();
    const modalRole = element();
    const modalEmpty = element(); modalEmpty.hidden = true;

    const candidateA = { dataset: { search: 'andi supervisor finance', role: 'SUPERVISOR' }, hidden: false };
    const candidateB = { dataset: { search: 'siti manager operasional', role: 'MANAGER' }, hidden: false };

    const elements = {
        'ava-grant-modal': modal,
        'ava-modal-search': modalSearch,
        'ava-modal-role': modalRole,
        'ava-modal-empty': modalEmpty,
    };
    const document = {
        getElementById: (id) => elements[id] || null,
        querySelectorAll: (selector) => {
            if (selector === '[data-open-grant-modal]') return [openBtn];
            if (selector === '[data-candidate-row]') return [candidateA, candidateB];
            return [];
        },
        addEventListener() {},
    };

    vm.runInNewContext(script, { document, setTimeout: (fn) => fn() });

    openBtn.listeners.click();
    assert.equal(modal.hidden, false);

    modalSearch.value = 'siti';
    modalSearch.listeners.input();
    assert.equal(candidateA.hidden, true);
    assert.equal(candidateB.hidden, false);
});
