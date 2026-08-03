# OPS Division Access Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti akses pengguna OPS per orang menjadi akses yang disinkronkan berdasarkan divisi.

**Architecture:** Tabel `ops_access_divisions` menyimpan divisi yang diizinkan. `User::canAccessOps()` membaca keanggotaan divisi secara langsung, sedangkan Admin OPS dan Admin ATK tetap memakai `user_access_roles`. Halaman akses menyinkronkan checkbox divisi dan mempertahankan pengelolaan admin per orang.

**Tech Stack:** Laravel, Eloquent, Blade, Pest, MySQL 8.

## Global Constraints

- Database production hanya diubah melalui SQL manual di `database/sql/2026-08-03-ops-mvp.sql`.
- Jangan menjalankan migration atau query tulis terhadap database pengguna.
- Role individual `OPS` tidak lagi memberikan akses.
- Admin OPS dan Admin ATK tetap dapat membuka OPS tanpa divisi terpilih.

---

### Task 1: Penyimpanan dan keputusan akses divisi

**Files:**
- Modify: `database/sql/2026-08-03-ops-mvp.sql`
- Create: `app/Models/OpsAccessDivision.php`
- Modify: `app/Models/User.php`
- Modify: `tests/Support/InstallsOpsSchema.php`
- Test: `tests/Feature/OpsV2Test.php`

**Interfaces:**
- Produces: `OpsAccessDivision` dengan relasi `division()` dan `createdBy()`.
- Produces: `User::canAccessOps(): bool` yang menerima Admin ATK, Admin OPS, atau divisi terpilih.

- [ ] **Step 1: Write the failing access tests**

Tambahkan pengujian yang membuat dua divisi, memilih satu melalui `OpsAccessDivision`, lalu memastikan anggota divisi terpilih mendapat akses dan anggota divisi lain ditolak. Pastikan role `OPS` individual tanpa divisi terpilih tidak memberi akses.

- [ ] **Step 2: Run tests to verify failure**

Run: `php artisan test tests\Feature\OpsV2Test.php --filter="division access" --compact`

Expected: FAIL karena model/tabel belum tersedia atau `canAccessOps()` masih membaca role `OPS`.

- [ ] **Step 3: Add manual SQL and model**

Tambahkan SQL:

```sql
CREATE TABLE ops_access_divisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT ops_access_divisions_division_foreign FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE,
    CONSTRAINT ops_access_divisions_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

Model mengisi `division_id` dan `created_by`. Ubah `canAccessOps()` agar memeriksa `ADMIN ATK`, `ADMIN OPS`, atau keberadaan baris berdasarkan `division_id` pengguna.

- [ ] **Step 4: Run access tests**

Run: `php artisan test tests\Feature\OpsV2Test.php --filter="division access|ops access" --compact`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/sql/2026-08-03-ops-mvp.sql app/Models/OpsAccessDivision.php app/Models/User.php tests/Support/InstallsOpsSchema.php tests/Feature/OpsV2Test.php
git commit -m "feat: base ops user access on divisions"
```

### Task 2: Sinkronisasi checkbox dan halaman admin

**Files:**
- Modify: `app/Http/Controllers/Ops/Admin/AccessController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/ops/admin/access/index.blade.php`
- Test: `tests/Feature/OpsV2Test.php`

**Interfaces:**
- Produces: `AccessController::syncDivisions(Request)` yang memvalidasi `division_ids.*` dan menyinkronkan pilihan dalam transaksi.
- Retains: `grantAdmin(User)` dan `revokeAdmin(Request, User)` untuk akses admin per orang.

- [ ] **Step 1: Write failing synchronization tests**

Uji bahwa halaman menampilkan checkbox divisi dan jumlah pengguna aktif, tidak menampilkan `Jadikan Pengguna`, serta POST sinkronisasi menambah pilihan baru dan menghapus pilihan yang dilepas.

- [ ] **Step 2: Run tests to verify failure**

Run: `php artisan test tests\Feature\OpsV2Test.php --filter="bulk division access" --compact`

Expected: FAIL karena route dan form sinkronisasi belum tersedia.

- [ ] **Step 3: Implement minimal synchronization flow**

Tambahkan route `POST /v2/ops/admin/access/divisions`. Controller memvalidasi:

```php
['division_ids' => ['array'], 'division_ids.*' => ['integer', 'distinct', 'exists:divisions,id']]
```

Dalam transaksi, hapus baris yang tidak dipilih lalu `firstOrCreate` setiap divisi terpilih dengan `created_by`. Halaman memuat divisi beserta `users_count`, menampilkan checkbox, dan menghapus seluruh tombol grant/revoke pengguna individual.

- [ ] **Step 4: Run OPS regression tests**

Run: `php artisan test tests\Feature\OpsV2Test.php --compact`

Expected: PASS.

- [ ] **Step 5: Verify diff and commit**

Run: `git diff --check`

```bash
git add app/Http/Controllers/Ops/Admin/AccessController.php routes/web.php resources/views/ops/admin/access/index.blade.php tests/Feature/OpsV2Test.php
git commit -m "feat: manage ops access by division"
```
