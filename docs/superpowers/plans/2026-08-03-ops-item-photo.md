# OPS Item Photo Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan foto opsional pada barang dan katalog OPS.

**Architecture:** Controller OPS memakai `ImageCompressor` dan kolom `image_path` yang sudah digunakan ATK. Form admin mengirim multipart, sedangkan katalog merender gambar public storage dengan fallback tanpa foto.

**Tech Stack:** Laravel, Blade, Pest, storage public, layanan `ImageCompressor` yang sudah terpasang.

## Global Constraints

- Tidak ada perubahan database atau dependency baru.
- Foto opsional, maksimal 2 MB, dan validasi format sama dengan ATK.
- Edit tanpa foto baru mempertahankan foto lama.
- Tampilan katalog tetap mobile-first dan hijau.

---

### Task 1: Upload dan katalog foto OPS

**Files:**
- Modify: `app/Http/Controllers/Ops/Admin/ItemController.php`
- Modify: `resources/views/ops/admin/items/create.blade.php`
- Modify: `resources/views/ops/admin/items/edit.blade.php`
- Modify: `resources/views/ops/catalog.blade.php`
- Test: `tests/Feature/OpsV2Test.php`

**Interfaces:**
- Consumes: `ImageCompressor::compressAndStore(UploadedFile, string, string, string): string`.
- Produces: nilai `AtkItem::image_path` dan kartu katalog bergambar.

- [ ] **Step 1: Write failing tests**

Tambahkan tes upload gambar dengan mock `ImageCompressor`, tes edit tanpa gambar mempertahankan `image_path`, serta assertion `enctype="multipart/form-data"`, URL `storage/{image_path}`, dan fallback `Tanpa foto`.

- [ ] **Step 2: Run tests to verify failure**

Run: `php artisan test tests\Feature\OpsV2Test.php --filter="ops item photo" --compact`

Expected: FAIL karena field, penyimpanan, dan markup foto belum tersedia.

- [ ] **Step 3: Implement minimal upload flow**

Inject `ImageCompressor`, tambahkan validasi file yang sama dengan ATK, panggil:

```php
$validated['image_path'] = $this->imageCompressor->compressAndStore(
    $request->file('image'),
    'photo',
    'ops-items',
    'ops_',
);
```

Gunakan fallback HEIC/HEIF yang sama seperti controller ATK. Tambahkan multipart dan input file pada create/edit. Render foto 4:3 di katalog, atau `Tanpa foto` bila kosong/gagal dimuat.

- [ ] **Step 4: Run regression tests**

Run: `php artisan test tests\Feature\OpsV2Test.php --compact`

Expected: PASS.

- [ ] **Step 5: Verify and commit**

Run: `git diff --check`

```bash
git add app/Http/Controllers/Ops/Admin/ItemController.php resources/views/ops/admin/items/create.blade.php resources/views/ops/admin/items/edit.blade.php resources/views/ops/catalog.blade.php tests/Feature/OpsV2Test.php
git commit -m "feat: add ops item photos"
```
