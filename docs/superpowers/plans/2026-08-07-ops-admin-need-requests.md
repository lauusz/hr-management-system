# OPS Admin Need Requests Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Admin OPS dapat membuat dan memantau request barang OPS, sedangkan hanya Admin ATK yang dapat memprosesnya.

**Architecture:** Gunakan model dan tabel `atk_need_requests` existing dengan discriminator `module`. Controller Admin OPS hanya menyediakan index/create/store; controller Admin ATK existing tetap menjadi satu-satunya pemroses dan menampilkan antrean gabungan berlabel modul.

**Tech Stack:** Laravel 12, PHP 8, Blade, Pest, MySQL/MariaDB production, SQLite in-memory tests.

## Global Constraints

- Tidak membuat tabel baru dan tidak menghapus data existing.
- Database server diperbarui melalui SQL manual, bukan menjalankan migration.
- Nilai internal tetap `ATK`, `OPS`, `PENDING`, `DONE`, dan `REJECTED`.
- Tampilan OPS mobile-first, hijau, dan memakai kata sederhana.
- Admin OPS tidak memiliki endpoint pemrosesan request barang.

---

### Task 1: Discriminator modul pada request kebutuhan

**Files:**
- Modify: `database/migrations/2026_06_29_000001_create_atk_tables.php`
- Modify: `tests/Support/InstallsOpsSchema.php`
- Modify: `app/Models/AtkNeedRequest.php`
- Create: `database/sql/ops_need_requests_module_update.sql`
- Test: `tests/Feature/OpsV2Test.php`

**Interfaces:**
- Produces: `AtkNeedRequest::MODULE_ATK`, `AtkNeedRequest::MODULE_OPS`, `scopeForModule(Builder $query, string $module)`.

- [ ] **Step 1: Write the failing model/schema test**

```php
it('separates need requests by module', function () {
    $atk = AtkNeedRequest::create(needRequestData(['module' => 'ATK']));
    $ops = AtkNeedRequest::create(needRequestData(['module' => 'OPS']));

    expect(AtkNeedRequest::forModule('OPS')->pluck('id')->all())->toBe([$ops->id])
        ->and(AtkNeedRequest::forModule('ATK')->pluck('id')->all())->toBe([$atk->id]);
});
```

- [ ] **Step 2: Run the test and confirm RED**

Run: `php artisan test tests/Feature/OpsV2Test.php --filter="separates need requests by module"`

Expected: FAIL because `module` and `forModule` do not exist.

- [ ] **Step 3: Add the minimum schema/model implementation**

Add `module` with default `ATK`, include it in `$fillable`, define module constants, and add the query scope. Add this idempotency-free deployment SQL:

```sql
ALTER TABLE atk_need_requests
    ADD COLUMN module VARCHAR(10) NOT NULL DEFAULT 'ATK' AFTER id,
    ADD INDEX atk_need_requests_module_status_created_index (module, status, created_at);
```

- [ ] **Step 4: Run the focused test and confirm GREEN**

Run: `php artisan test tests/Feature/OpsV2Test.php --filter="separates need requests by module"`

Expected: PASS.

### Task 2: Route dan controller Admin OPS

**Files:**
- Create: `app/Http/Controllers/Ops/Admin/NeedRequestController.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/Atk/NeedRequestController.php`
- Modify: `app/Http/Controllers/Atk/Admin/NeedRequestController.php`
- Test: `tests/Feature/OpsV2Test.php`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Produces routes `v2.ops.admin.need-requests.index`, `.create`, dan `.store`.
- Consumes `AtkNeedRequest::forModule()` dari Task 1.

- [ ] **Step 1: Write failing feature tests**

Tests must prove: Admin OPS can open/create/store; regular OPS receives 403; stored row is `OPS`; OPS has no process route; Admin ATK sees and processes the row; ATK user history excludes it.

- [ ] **Step 2: Run focused tests and confirm RED**

Run: `php artisan test tests/Feature/OpsV2Test.php --filter="need request" tests/Feature/AtkV2Test.php --filter="need request"`

Expected: FAIL because OPS routes/controller do not exist and ATK queries are not module-scoped.

- [ ] **Step 3: Implement minimum controller and route behavior**

The OPS controller uses `AtkItem::MODULE_OPS` in item lookup and validation, writes `module => AtkNeedRequest::MODULE_OPS`, snapshots user/PT, and redirects to the OPS index. ATK general queries and writes only `MODULE_ATK`. Admin ATK index remains combined and chronological.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run the same command and expect PASS.

### Task 3: Mobile-first OPS views and Admin ATK label

**Files:**
- Create: `resources/views/ops/admin/need_requests/create.blade.php`
- Create: `resources/views/ops/admin/need_requests/index.blade.php`
- Modify: `resources/views/components/ops-app.blade.php`
- Modify: `resources/views/atk/admin/need_requests/index.blade.php`
- Test: `tests/Feature/OpsV2Test.php`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes route names from Task 2 and paginator variable `$needRequests`.

- [ ] **Step 1: Write failing rendered-response tests**

Assert the OPS sidebar contains **Request Barang**, the form has green OPS classes and a quantity stepper, the OPS index has no process buttons, and the ATK admin list shows an `OPS` label.

- [ ] **Step 2: Run focused tests and confirm RED**

Run: `php artisan test tests/Feature/OpsV2Test.php --filter="need request" tests/Feature/AtkV2Test.php --filter="need request"`

- [ ] **Step 3: Implement the minimum views**

Reuse the existing field set: item/name, quantity, unit, and reason. Keep status labels Indonesian (`Menunggu`, `Selesai`, `Ditolak`). Do not add email, attachment, or approval actions in OPS.

- [ ] **Step 4: Run focused tests and confirm GREEN**

Run the same command and expect PASS.

### Task 4: Regression and deployment verification

**Files:**
- Test: `tests/Feature/OpsV2Test.php`
- Test: `tests/Feature/AtkV2Test.php`

- [ ] **Step 1: Run complete ATK/OPS regression tests**

Run: `php artisan test --compact tests/Feature/AtkV2Test.php tests/Feature/OpsV2Test.php`

Expected: all tests pass.

- [ ] **Step 2: Verify routes, syntax, and diff**

Run:

```powershell
php artisan route:list --path=v2/ops/admin/need-requests
php -l app/Http/Controllers/Ops/Admin/NeedRequestController.php
git diff --check
```

Expected: three OPS admin routes, no syntax errors, and clean diff.

- [ ] **Step 3: Confirm SQL safety**

Verify the SQL contains only `ALTER TABLE ... ADD COLUMN` and `ADD INDEX`; it must not contain `DROP`, `DELETE`, `TRUNCATE`, or `UPDATE`.
