# ATK PT Report Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah rekap PT ATK menjadi dashboard native yang menampilkan pengajuan per PT, barang teratas, dan pengambil teraktif.

**Architecture:** Perluas query agregasi di `ReportController` dengan dataset pengambil serta metadata presentasi chart yang sederhana. Render seluruh visual sebagai HTML/CSS di Blade agar mobile-first, dapat diakses, dan tidak membutuhkan dependency JavaScript.

**Tech Stack:** Laravel, Eloquent query builder, Blade, CSS `conic-gradient`, Pest.

## Global Constraints

- Tidak menambahkan library chart atau dependency baru.
- Tidak mengubah database, migrasi, data lama, atau Export Excel.
- Hanya pengajuan ATK `APPROVED`/`PARTIAL` dengan item `APPROVED` yang dihitung.
- PT tanpa aktivitas tidak ditampilkan.
- Barang dan pengambil dibatasi maksimal 10 baris.
- Tampilan harus tetap terbaca tanpa mengandalkan warna atau tooltip.

---

### Task 1: Dataset Analisis Rekap PT

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `app/Http/Controllers/Atk/Admin/ReportController.php`

**Interfaces:**
- Consumes: closure `$baseQuery`, snapshot PT/barang/pengambil dari request yang disetujui.
- Produces: `$ptRows` dengan `percentage` dan `color`, `$itemRows` maksimal 10, serta `$requesterRows` maksimal 10.

- [ ] **Step 1: Tulis tes gagal untuk perhitungan dashboard**

Tes membuat dua item dalam satu request agar jumlah pengajuan dan ranking pengambil tetap dihitung satu kali, menambahkan PT tanpa aktivitas, dan membuat lebih dari sepuluh barang. Assert halaman menampilkan label dashboard, nama pengambil, PT aktif, serta tidak menampilkan PT tanpa aktivitas atau barang peringkat ke-11.

- [ ] **Step 2: Jalankan tes terarah dan pastikan gagal**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="renders an informative pt report dashboard"`

Expected: FAIL karena dataset pengambil dan label dashboard belum tersedia.

- [ ] **Step 3: Tambahkan agregasi minimal**

```php
$requesterRows = $baseQuery()
    ->select('atk_requests.user_name_snapshot', DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'))
    ->groupBy('atk_requests.user_name_snapshot')
    ->orderByDesc('request_count')
    ->limit(10)
    ->get();

$itemRows = $baseQuery()
    // select dan group yang sudah ada
    ->orderByDesc('total_qty')
    ->limit(10)
    ->get();
```

Tambahkan persentase dan warna pada `$ptRows` berdasarkan total `request_count`; pembagian hanya dilakukan ketika total lebih dari nol.

- [ ] **Step 4: Jalankan tes terarah**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="renders an informative pt report dashboard"`

Expected: tes masih FAIL pada struktur visual, tetapi data baru tidak error.

### Task 2: Visual Native dan Responsif

**Files:**
- Modify: `resources/views/atk/admin/reports/index.blade.php`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: `$ptRows`, `$itemRows`, `$requesterRows`, dan `$summary` dari controller.
- Produces: donut PT, bar horizontal barang, ranking pengambil, empty state, dan detail transaksi yang tetap tersedia.

- [ ] **Step 1: Ganti dua tabel ringkasan dengan visual HTML/CSS**

Render donut dengan `conic-gradient`, legenda teks PT, bar memakai `width` relatif terhadap nilai tertinggi, dan daftar ranking nama. Gunakan `aria-labelledby`, angka aktual, serta empty state per komponen.

- [ ] **Step 2: Tambahkan layout mobile-first**

Semua visual satu kolom di bawah 768px. Mulai 768px, donut dan ranking nama menjadi dua kolom sementara bar barang memakai satu baris penuh.

- [ ] **Step 3: Jalankan tes terarah dan seluruh tes ATK**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="renders an informative pt report dashboard|filters usage report by pt"`

Expected: PASS.

Run: `php artisan test tests/Feature/AtkV2Test.php`

Expected: seluruh tes ATK PASS.

- [ ] **Step 4: Verifikasi format dan commit**

Run: `vendor/bin/pint --test app/Http/Controllers/Atk/Admin/ReportController.php tests/Feature/AtkV2Test.php`

Run: `git diff --check`

```bash
git add app/Http/Controllers/Atk/Admin/ReportController.php resources/views/atk/admin/reports/index.blade.php tests/Feature/AtkV2Test.php
git commit -m "feat: visualize atk pt report"
```
