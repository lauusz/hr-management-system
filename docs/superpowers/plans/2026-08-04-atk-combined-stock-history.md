# ATK Combined Stock History Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan mutasi stok ATK dan OPS secara kronologis pada riwayat stok ATK, lengkap dengan label dan filter modul.

**Architecture:** Gunakan tabel dan model bersama yang sudah ada. Perluas query baca pada controller ATK ke dua modul, lalu tambahkan kontrol filter dan label modul pada view ATK; controller dan view OPS tidak diubah.

**Tech Stack:** Laravel, Eloquent, Blade, Pest.

## Global Constraints

- Tidak ada perubahan skema atau data database.
- Urutan mutasi harus berdasarkan waktu terbaru secara global, bukan kelompok modul.
- Riwayat OPS tetap hanya menampilkan modul `OPS`.
- Label `ATK` memakai gaya ungu dan label `OPS` memakai gaya hijau.

---

### Task 1: Gabungkan riwayat stok ATK dan OPS

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `app/Http/Controllers/Atk/Admin/StockMovementController.php`
- Modify: `resources/views/atk/admin/stock_movements/index.blade.php`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: `AtkItem::MODULE_ATK`, `AtkItem::MODULE_OPS`, relasi `AtkStockMovement::item`, dan parameter query opsional `module`.
- Produces: halaman `v2.atk.admin.stock-movements.index` dengan daftar gabungan kronologis dan filter modul.

- [ ] **Step 1: Ubah tes isolasi lama menjadi tes gabungan dan tambahkan tes filter**

```php
it('shows atk and ops movements in global chronological order', function () {
    // Buat movement ATK lebih lama dan OPS lebih baru.
    // Pastikan keduanya tampil, OPS tampil lebih dahulu, dan label modul tersedia.
});

it('filters combined stock history by module', function () {
    // module=OPS hanya menampilkan movement OPS.
});
```

- [ ] **Step 2: Jalankan tes untuk memastikan gagal**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="global chronological|filters combined"`

Expected: FAIL karena query ATK masih membuang movement OPS dan UI belum memiliki label/filter modul.

- [ ] **Step 3: Perluas query controller secara minimal**

```php
$modules = [AtkItem::MODULE_ATK, AtkItem::MODULE_OPS];
$selectedModule = in_array($request->string('module')->toString(), $modules, true)
    ? $request->string('module')->toString()
    : null;

$movements = AtkStockMovement::with(['item', 'createdBy'])
    ->whereHas('item', fn ($query) => $query->whereIn('module', $modules))
    ->when($selectedModule, fn ($query) => $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('module', $selectedModule)))
    ->latest()
    ->paginate(20);
```

Query sumber pengajuan dan daftar barang juga memakai kedua modul. Tidak tambahkan `groupBy` atau urutan berdasarkan modul.

- [ ] **Step 4: Tambahkan filter dan label modul pada Blade**

Tambahkan select `module` dengan `Semua`, `ATK`, `OPS`; tambahkan header/field `Modul`; dan tampilkan badge berdasarkan `$movement->item?->module` pada tabel desktop maupun layout kartu mobile.

- [ ] **Step 5: Jalankan tes terarah dan seluruh tes ATK**

Run: `php artisan test tests/Feature/AtkV2Test.php --filter="global chronological|filters combined"`

Expected: PASS.

Run: `php artisan test tests/Feature/AtkV2Test.php`

Expected: seluruh tes PASS.

- [ ] **Step 6: Verifikasi format dan commit**

Run: `vendor/bin/pint --test app/Http/Controllers/Atk/Admin/StockMovementController.php tests/Feature/AtkV2Test.php`

Run: `git diff --check`

```bash
git add app/Http/Controllers/Atk/Admin/StockMovementController.php resources/views/atk/admin/stock_movements/index.blade.php tests/Feature/AtkV2Test.php
git commit -m "feat: combine atk and ops stock history"
```
