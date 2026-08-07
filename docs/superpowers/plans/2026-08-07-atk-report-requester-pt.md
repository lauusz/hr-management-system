# ATK Report Requester PT Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan nama dan PT dengan format `NAMA · PT. NAMA PT` pada ranking Sering Mengambil.

**Architecture:** Perluas query agregasi yang sudah ada dengan snapshot PT dan kelompokkan nama bersama PT. View tetap memakai komponen ranking yang sama, hanya menambahkan nilai PT pada baris nama.

**Tech Stack:** Laravel, Query Builder, Blade, Pest.

## Global Constraints

- Gunakan `atk_requests.pt_name_snapshot`, bukan relasi PT akun terbaru.
- Nilai PT kosong ditampilkan sebagai `-`.
- Jangan mengubah database, filter, chart, export, atau laporan OPS.

---

### Task 1: Tampilkan PT pada ranking pengambil

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `app/Http/Controllers/Atk/Admin/ReportController.php`
- Modify: `resources/views/atk/admin/reports/index.blade.php`

**Interfaces:**
- Consumes: kolom snapshot `atk_requests.user_name_snapshot` dan `atk_requests.pt_name_snapshot`.
- Produces: setiap objek `$requesterRows` memiliki `user_name_snapshot`, `pt_name_snapshot`, dan `request_count`.

- [ ] **Step 1: Tambahkan assertion yang gagal**

Pada tes `renders an informative pt report dashboard`, tambahkan:

```php
->assertSee('Budi Paling Sering · PT Aktif Chart')
```

- [ ] **Step 2: Jalankan tes untuk memastikan gagal**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="renders an informative pt report dashboard"`

Expected: FAIL karena PT belum ditampilkan pada ranking.

- [ ] **Step 3: Perluas query agregasi**

Tambahkan `atk_requests.pt_name_snapshot` pada `select()` dan `groupBy()` query `$requesterRows`.

- [ ] **Step 4: Tampilkan snapshot PT**

Ubah isi elemen `strong` menjadi:

```blade
{{ $row->user_name_snapshot }} · {{ $row->pt_name_snapshot ? 'PT. '.preg_replace('/^PT\.?\s*/i', '', $row->pt_name_snapshot) : '-' }}
```

- [ ] **Step 5: Jalankan tes laporan ATK**

Run: `php artisan test tests/Feature/AtkV2Test.php`

Expected: seluruh tes lulus.

- [ ] **Step 6: Periksa diff**

Run: `git diff --check`

Expected: tidak ada error whitespace dan tidak ada file di luar scope yang ikut di-stage.
