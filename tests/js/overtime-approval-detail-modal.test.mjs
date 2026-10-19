import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

function setup(rejectionError = false) {
    const template = readFileSync('resources/views/overtime_approvals/show.blade.php', 'utf8');
    let script = template.match(/@push\('scripts'\)\s*<script>([\s\S]*?)<\/script>/)?.[1];
    assert.ok(script);
    script = script.replace(/@if\(\$errors->has\('rejection_note'\)\)([\s\S]*?)@endif/, rejectionError ? '$1' : '');

    const listeners = {};
    const document = {
        addEventListener(name, callback) { (listeners[name] ??= []).push(callback); },
        getElementById(id) { return modals[id]; },
        querySelector(selector) { return triggers[selector.match(/"([^"]+)"/)?.[1]]; },
    };
    const element = () => ({
        listeners: {},
        addEventListener(name, callback) { (this.listeners[name] ??= []).push(callback); },
        focus() { document.focused = this; },
        click() { for (const callback of this.listeners.click ?? []) callback({ target: this }); },
    });
    const modals = {};
    const triggers = {};
    for (const id of ['overtime-approve', 'overtime-reject']) {
        const trigger = element();
        const close = element();
        const input = element();
        const modal = element();
        modal.style = { display: 'none' };
        modal.querySelector = selector => selector === '#rejection_note' ? input : close;
        trigger.addEventListener('click', () => { modal.style.display = 'flex'; });
        close.addEventListener('click', () => {
            modal.style.display = 'none';
            for (const callback of modal.listeners.click ?? []) callback({ target: { closest: () => true } });
        });
        modals[id] = modal;
        triggers[id] = trigger;
        modal.input = input;
        modal.closeButton = close;
    }

    vm.runInNewContext(script, { document });
    for (const callback of listeners.DOMContentLoaded ?? []) callback();
    return { document, listeners, modals, triggers };
}

test('opening rejection focuses the reason and Escape returns focus to the trigger', () => {
    const ui = setup();
    const modal = ui.modals['overtime-reject'];
    const trigger = ui.triggers['overtime-reject'];
    trigger.click();
    assert.equal(modal.style.display, 'flex');
    assert.equal(ui.document.focused, modal.input);
    for (const callback of ui.listeners.keydown) callback({ key: 'Escape' });
    assert.equal(modal.style.display, 'none');
    assert.equal(ui.document.focused, trigger);
});

test('reopens and focuses rejection reason after a server validation error', () => {
    const ui = setup(true);
    assert.equal(ui.modals['overtime-reject'].style.display, 'flex');
    assert.equal(ui.document.focused, ui.modals['overtime-reject'].input);
});
