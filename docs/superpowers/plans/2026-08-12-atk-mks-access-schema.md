# ATK MKS Access Schema Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyediakan SQL deployment aman untuk akses ATK MKS berdasarkan divisi.

**Architecture:** Gunakan tabel akses terpisah `atk_mks_access_divisions`, mengikuti pola `ops_access_divisions`. Admin per orang tetap menggunakan `user_access_roles` existing sehingga tidak perlu perubahan schema tambahan.

**Tech Stack:** MySQL/MariaDB, Laravel project conventions.

## Global Constraints

- SQL hanya menambah tabel baru.
- Tidak mengubah atau menghapus data existing.
- Tidak menjalankan migration atau mengubah database secara otomatis.

---

### Task 1: File SQL akses ATK MKS

**Files:**
- Create: `database/sql/atk_mks_sql_update.sql`

**Interfaces:**
- Consumes: tabel existing `divisions(id)` dan `users(id)`.
- Produces: tabel `atk_mks_access_divisions`.

- [ ] **Step 1: Buat file SQL deployment**

```sql
CREATE TABLE atk_mks_access_divisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY atk_mks_access_divisions_division_unique (division_id),
    KEY atk_mks_access_divisions_created_by_foreign (created_by),
    CONSTRAINT atk_mks_access_divisions_division_foreign
        FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE,
    CONSTRAINT atk_mks_access_divisions_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

- [ ] **Step 2: Periksa sintaks tanpa mengubah database**

Run: baca file dan pastikan pernyataan dibatasi oleh `CREATE TABLE` beserta constraint yang diperlukan.

Expected: tidak ada `DROP`, `DELETE`, `TRUNCATE`, `UPDATE`, atau `ALTER TABLE`.

- [ ] **Step 3: Periksa diff**

Run: `git diff --check -- database/sql/atk_mks_sql_update.sql`

Expected: exit code 0.
