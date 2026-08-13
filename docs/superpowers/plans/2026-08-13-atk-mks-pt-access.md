# ATK MKS PT Access Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti akses pengguna ATK MKS dari divisi menjadi PT tanpa mengubah akses OPS atau hak admin per pengguna.

**Architecture:** Tabel baru `atk_mks_access_pts` menyimpan PT terpilih. `User::canAccessAtkMks()` memeriksa `employee_profiles.pt_id`, sedangkan controller dan halaman admin menyinkronkan PT secara bulk. Tabel divisi ATK MKS lama dibiarkan tetapi tidak lagi dibaca.

**Tech Stack:** Laravel 12, PHP 8.3, Blade, Eloquent, Pest, MySQL/MariaDB SQL manual.

## Global Constraints

- Perubahan hanya berlaku untuk ATK MKS; OPS tetap berbasis divisi.
- Admin ATK dan role `ADMIN ATK MKS` tetap mendapat akses penuh tanpa bergantung pada PT.
- Jangan menjalankan migration atau SQL terhadap database pengguna.
- SQL incremental hanya membuat `atk_mks_access_pts`; tidak menghapus atau mengubah data lama.
- Pengguna tanpa employee profile atau tanpa PT tidak mendapat akses berbasis PT.

---

### Task 1: Skema tes dan otorisasi berbasis PT

**Files:**
- Modify: `tests/Support/InstallsOpsSchema.php`
- Modify: `tests/Feature/AtkMksV2Test.php`
- Create: `app/Models/AtkMksAccessPt.php`
- Modify: `app/Models/User.php`

**Interfaces:**
- Produces: model `AtkMksAccessPt` dengan fillable `pt_id`, `created_by`.
- Produces: `User::canAccessAtkMks(): bool` yang memeriksa `profile.pt_id`.

- [ ] **Step 1: Tambahkan tabel `atk_mks_access_pts` pada schema test**

```php
if (! Schema::hasTable('atk_mks_access_pts')) {
    Schema::create('atk_mks_access_pts', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('pt_id')->unique();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });
}
```

- [ ] **Step 2: Ubah tes akses ATK MKS agar memakai employee profile dan PT**

Tes harus membuat PT terpilih, employee profile pada PT tersebut, PT yang tidak dipilih, serta pengguna tanpa PT. Ekspektasi: PT terpilih `200`; PT lain dan tanpa PT `403`; Admin ATK MKS dan Admin ATK tetap `200`.

- [ ] **Step 3: Jalankan tes dan pastikan gagal karena akses masih membaca divisi**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="limits atk mks access"`

Expected: FAIL pada pengguna PT terpilih atau tabel/model akses PT belum digunakan.

- [ ] **Step 4: Buat model dan ubah otorisasi minimal**

```php
class AtkMksAccessPt extends Model
{
    protected $fillable = ['pt_id', 'created_by'];

    public function pt() { return $this->belongsTo(Pt::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
```

```php
public function canAccessAtkMks(): bool
{
    return $this->canManageAtkMks()
        || ($this->profile?->pt_id !== null
            && AtkMksAccessPt::where('pt_id', $this->profile->pt_id)->exists());
}
```

- [ ] **Step 5: Jalankan tes otorisasi sampai lulus**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="limits atk mks access"`

Expected: PASS.

### Task 2: Sinkronisasi akses PT pada admin

**Files:**
- Modify: `tests/Feature/AtkMksV2Test.php`
- Modify: `app/Http/Controllers/AtkMks/Admin/AccessController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `AtkMksAccessPt`.
- Produces: `AccessController::syncPts(Request $request)`.
- Produces: route `v2.atk-mks.admin.access.pts.sync` untuk `POST /v2/atk-mks/admin/access/pts`.

- [ ] **Step 1: Ubah tes sinkronisasi dari divisi menjadi PT**

Tes mengirim `pt_ids[]`, memastikan PT baru tersimpan, PT lama dicabut, pengguna pada PT baru dapat akses, pengguna PT lama kehilangan akses, dan pengelolaan Admin ATK MKS per pengguna tetap bekerja.

- [ ] **Step 2: Jalankan tes dan pastikan gagal pada endpoint PT yang belum ada**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="syncs pt"`

Expected: FAIL 404 atau route tidak ditemukan.

- [ ] **Step 3: Ubah controller dan route**

Controller `index()` mengambil `Pt::withCount(['profiles as active_users_count' => fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->active())])`, lalu mengirim `$pts` dan `$selectedPtIds`.

Validasi sinkronisasi:

```php
$validated = $request->validate([
    'pt_ids' => ['sometimes', 'array'],
    'pt_ids.*' => ['integer', 'distinct', 'exists:pts,id'],
]);
```

Sinkronisasi menghapus pilihan yang tidak dikirim dan memakai `firstOrCreate()` untuk pilihan baru dalam `DB::transaction()`.

- [ ] **Step 4: Jalankan tes sinkronisasi sampai lulus**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="syncs pt"`

Expected: PASS.

### Task 3: UI akses PT

**Files:**
- Modify: `tests/Feature/AtkMksV2Test.php`
- Modify: `resources/views/atk-mks/admin/access/index.blade.php`

**Interfaces:**
- Consumes: `$pts`, `$selectedPtIds`, `$users` dari controller.

- [ ] **Step 1: Tambahkan tes markup halaman PT**

Tes memastikan halaman menampilkan nama PT, jumlah pengguna aktif, `name="pt_ids[]"`, endpoint `/access/pts`, copy `Pilih PT pengguna`, dan tidak menampilkan `division_ids[]`.

- [ ] **Step 2: Jalankan tes dan pastikan gagal pada markup divisi lama**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="shows pt access"`

Expected: FAIL karena halaman masih menampilkan divisi.

- [ ] **Step 3: Ubah Blade menjadi checkbox PT**

Gunakan daftar `$pts`, field `pt_ids[]`, copy `Centang PT yang boleh membuka Stok ATK MKS.`, dan empty state `PT belum tersedia.`. Bagian Admin ATK MKS per pengguna tidak diubah.

- [ ] **Step 4: Jalankan tes UI sampai lulus**

Run: `php artisan test tests/Feature/AtkMksV2Test.php --filter="shows pt access"`

Expected: PASS.

### Task 4: SQL incremental dan verifikasi regresi

**Files:**
- Create: `database/sql/atk_mks_pt_access_update.sql`
- Test: `tests/Feature/AtkMksV2Test.php`
- Test: `tests/Feature/OpsV2Test.php`

**Interfaces:**
- Produces: tabel server `atk_mks_access_pts` sesuai model aplikasi.

- [ ] **Step 1: Buat SQL incremental**

```sql
CREATE TABLE atk_mks_access_pts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pt_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY atk_mks_access_pts_pt_unique (pt_id),
    KEY atk_mks_access_pts_created_by_foreign (created_by),
    CONSTRAINT atk_mks_access_pts_pt_foreign
        FOREIGN KEY (pt_id) REFERENCES pts(id) ON DELETE CASCADE,
    CONSTRAINT atk_mks_access_pts_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

- [ ] **Step 2: Audit SQL**

Pastikan file hanya memiliki satu `CREATE TABLE` dan tidak memiliki `DROP`, `DELETE`, `UPDATE`, `TRUNCATE`, atau `ALTER`.

- [ ] **Step 3: Jalankan regression test ATK MKS dan OPS**

Run: `php artisan test tests/Feature/AtkMksV2Test.php tests/Feature/OpsV2Test.php`

Expected: seluruh tes PASS; OPS tetap menggunakan `ops_access_divisions`.

- [ ] **Step 4: Verifikasi sintaks, Blade, route, dan diff**

Run:

```powershell
php -l app/Models/AtkMksAccessPt.php
php -l app/Models/User.php
php -l app/Http/Controllers/AtkMks/Admin/AccessController.php
php artisan route:list --path=v2/atk-mks/admin/access
php artisan view:cache
git diff --check
```

Expected: seluruh command exit 0 dan route POST `/v2/atk-mks/admin/access/pts` terdaftar.
