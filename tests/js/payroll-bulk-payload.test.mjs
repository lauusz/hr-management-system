import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

// Pemeriksaan sumber: payload simpan/publish Master Payroll lengkap & berbasis pilihan eksplisit.
const view = readFileSync('resources/views/hr/payroll/index.blade.php', 'utf8');

test('state edit menyimpan snapshot lengkap per karyawan+bulan+tahun', () => {
    assert.match(view, /state\.snapshot = captureRowSnapshot\(row\)/);
    assert.match(view, /const rowKey = row\?\.dataset\.rowKey/);
});

test('target tanpa payload lengkap tidak dikirim', () => {
    assert.match(view, /PAYLOAD_FIELD_KEYS\.every/);
    assert.match(view, /missing\.push\(key\)/);
    assert.match(view, /missing\.length > 0 \|\| targets\.length === 0/);
});

test('payload dikirim sebagai JSON, bukan input seluruh tabel', () => {
    assert.match(view, /payroll-targets-json/);
    assert.match(view, /JSON\.stringify\(targets\)/);
    assert.match(view, /\[name\^="payslips\["\]/);
});

// Regresi fokus keyboard (pemeriksaan sumber; tidak membutuhkan aplikasi).
test('select() tertunda hanya bekerja bila input masih terhubung dan fokus', () => {
    assert.match(view, /seq !== focusSelectSeq \|\| !input\.isConnected \|\| document\.activeElement !== input/);
    assert.doesNotMatch(view, /setTimeout\(\(\) => this\.select\(\), 0\)/);
});

test('drawer tertutup inert dan tidak masuk urutan Tab', () => {
    assert.match(view, /id="pwQuickDrawer"[^>]*\binert\b/);
    assert.match(view, /quickDrawer\.inert = true/);
    assert.match(view, /quickDrawer\.inert = false/);
});

test('Tab tanpa mengubah isi tidak mencatat perubahan sisa utang', () => {
    assert.match(view, /input\.value !== input\.dataset\.focusValue/);
});

test('pemulihan fokus loading menghormati posisi terbaru pengguna', () => {
    assert.match(view, /current === document\.body/);
    assert.match(view, /focusLostToLoader = focusLostToLoader \|\| hadFocus/);
});

test('tidak ada handler Tab global dan tidak ada smooth-scroll bertumpuk', () => {
    assert.doesNotMatch(view, /key === 'Tab'/);
    assert.doesNotMatch(view, /behavior: 'smooth'/);
});
