# ATK Admin Search and Steppers Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan pencarian nama yang dapat diklik, pencarian barang langsung, dan stepper jumlah pada form pengambilan manual serta stepper stok masuk pada Master Barang ATK.

**Architecture:** Gunakan koleksi pengguna dan barang yang sudah dirender server, lalu filter secara lokal agar pilihan form tidak hilang. Pertahankan endpoint serta payload yang ada; hanya izinkan nilai `0` pada quantity manual agar barang yang tidak dipilih dapat diabaikan oleh filter controller yang sudah tersedia.

**Tech Stack:** Laravel Blade, CSS native, JavaScript native, Pest feature tests.

## Global Constraints

- Tidak mengubah database, route, atau struktur tabel.
- Tidak menambah dependency atau endpoint pencarian baru.
- Pencarian nama dan barang tidak memuat ulang halaman.
- Form manual tetap mengirim `user_id`, `notes`, dan `quantities`.
- Form stok tetap mengirim `movement_type=IN`, `qty`, dan `unit_price`.
- Kontrol interaktif memiliki tinggi sentuh minimum 44px serta label aksesibel.

---

### Task 1: Kunci Perilaku Form Manual dengan Tes Merah

**Files:**
- Modify: `tests/Feature/AtkV2Test.php:665-735`

**Interfaces:**
- Consumes: route `v2.atk.admin.requests.manual.create` dan `v2.atk.admin.requests.manual.store`.
- Produces: kontrak HTML pencarian/stepper dan bukti quantity `0` diabaikan.

- [ ] **Step 1: Tambahkan assertion struktur interaktif**

```php
->assertSee('data-manual-user-search', false)
->assertSee('data-manual-user-option', false)
->assertSee('data-manual-item-search', false)
->assertSee('data-manual-item', false)
->assertSee('data-manual-stepper', false)
->assertSee('data-manual-stepper-decrease', false)
->assertSee('data-manual-stepper-increase', false)
->assertSee('min="0"', false)
->assertDontSee('type="submit">Cari', false);
```

- [ ] **Step 2: Tambahkan barang quantity nol ke tes penyimpanan**

```php
$ignoredItem = AtkItem::create([
    'name' => 'Barang Manual Tidak Dipilih',
    'unit_name' => 'pcs',
    'stock_qty' => 10,
    'is_active' => true,
]);

'quantities' => [
    $approvedItem->id => 2,
    $rejectedItem->id => 1,
    $ignoredItem->id => 0,
],
```

Pertahankan ekspektasi request memiliki dua item dan tambahkan:

```php
expect($atkRequest->items->pluck('atk_item_id'))->not->toContain($ignoredItem->id);
```

- [ ] **Step 3: Jalankan tes dan pastikan gagal**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="shows the manual ATK request form|creates a pending manual request"`

Expected: FAIL karena marker pencarian/stepper belum ada dan validasi quantity `0` masih memakai `min:1`.

---

### Task 2: Implementasikan Pencarian dan Stepper Form Manual

**Files:**
- Modify: `resources/views/atk/admin/requests/manual-create.blade.php`
- Modify: `app/Http/Controllers/Atk/Admin/RequestApprovalController.php:58-72`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: `$users`, `$items`, nilai `old('user_id')`, dan `old('quantities.*')`.
- Produces: hidden input `user_id`, daftar tombol pengguna, filter barang, serta input `quantities[item_id]` bernilai `0..stock_qty`.

- [ ] **Step 1: Ganti select pengguna dengan pencarian dan pilihan klik**

```blade
<input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}" required data-manual-user-id>
<input class="atk-input" type="search" placeholder="Cari nama..." autocomplete="off" data-manual-user-search>
<div class="atk-manual-user-results" data-manual-user-results>
    @foreach($users as $user)
        <button type="button" data-manual-user-option data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-pt="{{ $user->profile?->pt?->name ?? '' }}">
            <strong>{{ $user->name }}</strong>
            <small>{{ $user->profile?->pt?->name ?? 'PT belum tersedia' }}</small>
        </button>
    @endforeach
</div>
```

Tambahkan panel `Nama terpilih`, tombol `Ganti`, dan pesan `Nama tidak ditemukan.`. JavaScript mengisi hidden input saat tombol hasil diklik dan memulihkan pilihan dari `old('user_id')`.

- [ ] **Step 2: Tambahkan pencarian barang dan marker item**

```blade
<input class="atk-input" type="search" placeholder="Cari barang ATK..." autocomplete="off" data-manual-item-search>
<label class="atk-manual-item" data-manual-item>
```

JavaScript membandingkan input dengan `textContent` item, mengubah properti `hidden`, dan menampilkan `Barang tidak ditemukan.` ketika tidak ada hasil.

- [ ] **Step 3: Ganti input quantity dengan stepper**

```blade
<div class="atk-stepper" data-manual-stepper>
    <button type="button" data-manual-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}">&minus;</button>
    <input type="number" min="0" max="{{ $item->stock_qty }}" name="quantities[{{ $item->id }}]" value="{{ old('quantities.'.$item->id, 0) }}" data-manual-stepper-input>
    <button type="button" data-manual-stepper-increase aria-label="Tambah jumlah {{ $item->name }}">+</button>
</div>
```

JavaScript memakai `stepDown()`/`stepUp()`, menormalisasi nilai ke `0..max`, serta menyinkronkan `disabled` dan `aria-disabled` kedua tombol.

- [ ] **Step 4: Izinkan quantity nol di controller**

```php
'quantities.*' => ['nullable', 'integer', 'min:0'],
```

Pertahankan filter controller yang hanya mengambil quantity lebih besar dari nol.

- [ ] **Step 5: Jalankan tes terarah sampai hijau**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="shows the manual ATK request form|creates a pending manual request"`

Expected: PASS.

---

### Task 3: Kunci dan Implementasikan Stepper Master Barang

**Files:**
- Modify: `tests/Feature/AtkV2Test.php:1070-1115`
- Modify: `resources/views/atk/admin/items/index.blade.php`

**Interfaces:**
- Consumes: form stok `v2.atk.admin.items.stock.store` dan validasi `qty >= 1` yang sudah ada.
- Produces: stepper stok masuk yang tetap mengirim `name="qty"` bersama `unit_price`.

- [ ] **Step 1: Tulis assertion stepper stok masuk**

Tambahkan pada tes `records unit price when admin adds incoming stock`:

```php
->assertSee('data-stock-stepper', false)
->assertSee('data-stock-stepper-decrease', false)
->assertSee('data-stock-stepper-increase', false)
->assertSee('name="qty" value="1"', false)
->assertSee('name="unit_price"', false);
```

- [ ] **Step 2: Jalankan tes dan pastikan gagal**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="records unit price when admin adds incoming stock"`

Expected: FAIL karena marker stepper stok belum ada.

- [ ] **Step 3: Ganti input qty stok dengan stepper**

```blade
<div class="atk-stock-stepper" data-stock-stepper>
    <button type="button" data-stock-stepper-decrease aria-label="Kurangi stok masuk {{ $item->name }}" disabled>&minus;</button>
    <input type="number" min="1" name="qty" value="1" aria-label="Jumlah stok masuk {{ $item->name }}" data-stock-stepper-input>
    <button type="button" data-stock-stepper-increase aria-label="Tambah stok masuk {{ $item->name }}">+</button>
</div>
```

Tambahkan CSS stepper katalog yang diperlukan dan JavaScript event delegation pada tabel. Minus nonaktif di nilai `1`; plus selalu aktif; perubahan manual di bawah `1` dikembalikan menjadi `1`.

- [ ] **Step 4: Jalankan tes sampai hijau**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="records unit price when admin adds incoming stock"`

Expected: PASS.

---

### Task 4: Verifikasi dan Commit

**Files:**
- Verify: `resources/views/atk/admin/requests/manual-create.blade.php`
- Verify: `resources/views/atk/admin/items/index.blade.php`
- Verify: `app/Http/Controllers/Atk/Admin/RequestApprovalController.php`
- Verify: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: hasil Task 2 dan Task 3.
- Produces: perubahan terverifikasi di branch `main`.

- [ ] **Step 1: Periksa format**

Run: `vendor\bin\pint --test app\Http\Controllers\Atk\Admin\RequestApprovalController.php tests\Feature\AtkV2Test.php`

Expected: PASS.

- [ ] **Step 2: Jalankan seluruh feature test ATK**

Run: `php artisan test tests\Feature\AtkV2Test.php --compact`

Expected: seluruh tes ATK lulus.

- [ ] **Step 3: Periksa diff**

Run: `git diff --check`

Expected: exit code 0 tanpa output.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Atk/Admin/RequestApprovalController.php resources/views/atk/admin/requests/manual-create.blade.php resources/views/atk/admin/items/index.blade.php tests/Feature/AtkV2Test.php
git commit -m "feat: add atk admin search and steppers"
```

