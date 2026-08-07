# ATK Report Hide Request Number Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghapus kolom No. Request dari riwayat pengambilan pada halaman laporan ATK.

**Architecture:** Hapus nomor request dari query detail halaman dan markup tabel Blade. Export memakai alur terpisah dan tidak diubah.

**Tech Stack:** Laravel, Query Builder, Blade, Pest.

## Global Constraints

- Nomor request tetap tersimpan di database.
- Export Excel tetap menampilkan nomor request.
- Jangan mengubah filter, chart, atau tabel laporan lain.

---

### Task 1: Sederhanakan riwayat pengambilan

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `app/Http/Controllers/Atk/Admin/ReportController.php`
- Modify: `resources/views/atk/admin/reports/index.blade.php`

**Interfaces:**
- Consumes: `$detailRows` hasil query laporan ATK.
- Produces: tabel riwayat lima kolom tanpa `request_number`.

- [ ] **Step 1: Tambahkan assertion yang gagal**

Pada tes filter laporan PT, tambahkan:

```php
->assertDontSee('No. Request')
->assertDontSee('ATK-REPORT-1')
```

- [ ] **Step 2: Jalankan tes target untuk memastikan gagal**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="filters usage report by pt"`

Expected: FAIL karena header dan nomor request masih tampil.

- [ ] **Step 3: Hapus nomor request dari query halaman**

Hapus `atk_requests.request_number` dari `select()` milik `$detailRows` pada method `index()`.

- [ ] **Step 4: Hapus kolom dari tabel**

- Hapus `<th>No. Request</th>`.
- Hapus `<td data-label="No. Request">`.
- Ubah empty-state dari `colspan="6"` menjadi `colspan="5"`.

- [ ] **Step 5: Jalankan seluruh tes ATK**

Run: `php artisan test tests/Feature/AtkV2Test.php`

Expected: seluruh tes lulus, termasuk tes download Excel yang sudah ada.

- [ ] **Step 6: Periksa format dan diff**

Run: `vendor/bin/pint --test app/Http/Controllers/Atk/Admin/ReportController.php tests/Feature/AtkV2Test.php && git diff --check`

Expected: formatter dan pemeriksaan whitespace lulus.
