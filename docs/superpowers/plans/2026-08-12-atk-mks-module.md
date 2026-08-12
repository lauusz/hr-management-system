# ATK MKS Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membuat modul `/v2/atk-mks` yang mengikuti seluruh alur OPS dengan data terpisah dan tampilan kuning keemasan.

**Architecture:** Gunakan tabel ATK existing dengan discriminator `ATK_MKS`, session cart khusus, middleware akses khusus, dan tabel `atk_mks_access_divisions`. Controller dan view mengikuti OPS, sedangkan Admin ATK tetap menjadi master yang dapat mengelola modul dan memproses Request Barang.

**Tech Stack:** Laravel 12, PHP 8.3, Blade, Pest, MySQL/MariaDB.

## Global Constraints

- Tidak menambah dependency.
- Tidak membuat tabel stok, barang, request, atau riwayat baru.
- Data ATK, OPS, dan ATK MKS harus terpisah pada setiap query dan session.
- SQL server dijalankan manual oleh pengguna.
- UI mobile-first menggunakan palet `#A16207`, `#78350F`, `#FEF3C7`, dan `#F5D77C`.

---

### Task 1: Identitas dan akses ATK MKS

**Files:**
- Create: `app/Models/AtkMksAccessDivision.php`
- Create: `app/Http/Middleware/EnsureAtkMksAccess.php`
- Create: `app/Http/Middleware/EnsureAtkMksAdmin.php`
- Modify: `app/Enums/UserRole.php`
- Modify: `app/Models/User.php`
- Modify: `bootstrap/app.php`
- Modify: `tests/Support/InstallsOpsSchema.php`
- Create: `tests/Feature/AtkMksV2Test.php`

**Interfaces:**
- Produces: `UserRole::ADMIN_ATK_MKS`, `User::canAccessAtkMks()`, `User::canManageAtkMks()`, middleware aliases `atk-mks.access` dan `atk-mks.admin`.

- [ ] Tulis tes gagal untuk akses divisi, Admin ATK MKS, dan master Admin ATK.
- [ ] Jalankan `php artisan test tests/Feature/AtkMksV2Test.php` dan pastikan gagal karena API belum tersedia.
- [ ] Implementasikan model, role, helper user, middleware, registrasi alias, dan schema test minimum.
- [ ] Jalankan tes terfokus hingga hijau.

### Task 2: Data dan alur pengguna

**Files:**
- Modify: `app/Models/AtkItem.php`
- Modify: `app/Models/AtkRequest.php`
- Modify: `app/Models/AtkNeedRequest.php`
- Create: `app/Http/Controllers/AtkMks/CatalogController.php`
- Create: `app/Http/Controllers/AtkMks/CartController.php`
- Create: `app/Http/Controllers/AtkMks/RequestController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Produces: konstanta `MODULE_ATK_MKS = 'ATK_MKS'`, route katalog/keranjang/pengajuan `v2.atk-mks.*`, dan session key `atk_mks_cart`.

- [ ] Tambahkan tes gagal untuk pemisahan katalog, cart, dan request.
- [ ] Tambahkan konstanta module dan izinkan `AtkRequest::createPending()` menerima `ATK_MKS`.
- [ ] Implementasikan controller pengguna dengan seluruh lookup terkunci ke `MODULE_ATK_MKS`.
- [ ] Daftarkan route pengguna di bawah middleware `atk-mks.access`.
- [ ] Jalankan tes terfokus hingga hijau.

### Task 3: Alur admin

**Files:**
- Create: `app/Http/Controllers/AtkMks/Admin/AccessController.php`
- Create: `app/Http/Controllers/AtkMks/Admin/ItemController.php`
- Create: `app/Http/Controllers/AtkMks/Admin/NeedRequestController.php`
- Create: `app/Http/Controllers/AtkMks/Admin/RequestApprovalController.php`
- Create: `app/Http/Controllers/AtkMks/Admin/StockController.php`
- Create: `app/Http/Controllers/AtkMks/Admin/StockMovementController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Produces: route admin `v2.atk-mks.admin.*`; tidak menghasilkan route pemrosesan Need Request.

- [ ] Tambahkan tes gagal untuk CRUD barang, stok, soft delete, approval, riwayat, akses divisi, dan Request Barang.
- [ ] Implementasikan controller dengan pola transaksi/row-lock OPS serta module `ATK_MKS`.
- [ ] Daftarkan route admin di bawah middleware `atk-mks.admin`.
- [ ] Pastikan Admin ATK dapat mengelola seluruh route dan Admin ATK MKS tidak dapat memproses Need Request melalui route ATK.
- [ ] Jalankan tes terfokus hingga hijau.

### Task 4: UI kuning keemasan dan portal akses

**Files:**
- Create: `resources/views/components/atk-mks-app.blade.php`
- Create: `resources/views/atk-mks/**`
- Modify: `resources/views/v2/access.blade.php`
- Modify: `resources/views/atk/admin/need_requests/index.blade.php`
- Modify: `app/Http/Controllers/Atk/Admin/StockMovementController.php`

**Interfaces:**
- Consumes: seluruh route `v2.atk-mks.*`.
- Produces: card portal bersyarat, shell amber, halaman user/admin, label `ATK_MKS` pada antrean master, dan filter riwayat master.

- [ ] Clone struktur Blade OPS dengan route, nama, class shell, dan teks ATK MKS.
- [ ] Terapkan palet amber serta pertahankan layout mobile-first.
- [ ] Tambahkan card bersyarat pada `/v2/access`.
- [ ] Sertakan `ATK_MKS` pada riwayat stok gabungan Admin ATK dan label Need Request.
- [ ] Jalankan tes render hingga hijau.

### Task 5: Verifikasi

**Files:**
- Test: `tests/Feature/AtkMksV2Test.php`
- Test: `tests/Feature/AtkV2Test.php`
- Test: `tests/Feature/OpsV2Test.php`

- [ ] Jalankan `php artisan test tests/Feature/AtkMksV2Test.php tests/Feature/AtkV2Test.php tests/Feature/OpsV2Test.php`.
- [ ] Jalankan `php artisan route:list --path=v2/atk-mks`.
- [ ] Jalankan PHP lint pada file controller/model/middleware baru.
- [ ] Jalankan `git diff --check` untuk seluruh file terkait.
