# Remove ATK Admin Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the unused ATK admin dashboard and make Master Barang the first admin destination and sidebar menu.

**Architecture:** Keep the protected admin root route as a named redirect so old URLs remain valid. Delete the dashboard controller/view and simplify the existing ATK sidebar without adding new code layers.

**Tech Stack:** Laravel routes, Blade, Pest feature tests.

## Global Constraints

- Work directly on `main` as explicitly requested.
- Do not change database, migrations, data, permissions, Master Barang, or Rekap PT.
- Preserve route name `v2.atk.admin.dashboard` as a protected redirect to `v2.atk.admin.items.index`.
- Delete the dashboard controller and Blade view.

---

### Task 1: Remove Dashboard and Promote Master Barang

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `routes/web.php`
- Modify: `resources/views/components/atk-app.blade.php`
- Delete: `app/Http/Controllers/Atk/Admin/DashboardController.php`
- Delete: `resources/views/atk/admin/dashboard.blade.php`

**Interfaces:**
- Consumes: existing `atk.admin` middleware and route `v2.atk.admin.items.index`.
- Produces: route `v2.atk.admin.dashboard` returning a redirect for authorized admins.

- [ ] **Step 1: Replace dashboard tests with redirect/navigation expectations**

```php
it('protects the admin root and redirects atk admins to master items', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    actingAs($user)->get(route('v2.atk.admin.dashboard'))->assertForbidden();
    actingAs($admin)->get(route('v2.atk.admin.dashboard'))
        ->assertRedirect(route('v2.atk.admin.items.index'));
});
```

Add rendered sidebar assertions that `Dashboard Admin` is absent and `Master Barang` appears before `Request Masuk`.

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="admin root"`

Expected: FAIL because the current route returns the dashboard with status 200.

- [ ] **Step 3: Replace the controller route with a redirect**

```php
Route::get('/', fn () => redirect()->route('v2.atk.admin.items.index'))->name('dashboard');
```

Remove the `AtkAdminDashboardController` import.

- [ ] **Step 4: Simplify the sidebar**

Remove the dashboard SVG symbol and link. Move the existing `Master Barang` link immediately below the `Admin ATK` heading, ahead of `Request Masuk`.

- [ ] **Step 5: Delete the dashboard implementation files**

Delete exactly:

```text
app/Http/Controllers/Atk/Admin/DashboardController.php
resources/views/atk/admin/dashboard.blade.php
```

- [ ] **Step 6: Run verification**

```powershell
php artisan test tests\Feature\AtkV2Test.php --compact
vendor\bin\pint --test routes\web.php tests\Feature\AtkV2Test.php
git diff --check
```

Expected: all ATK tests and formatting checks pass.

- [ ] **Step 7: Commit**

```powershell
git add routes/web.php resources/views/components/atk-app.blade.php tests/Feature/AtkV2Test.php app/Http/Controllers/Atk/Admin/DashboardController.php resources/views/atk/admin/dashboard.blade.php
git commit -m "refactor: remove atk admin dashboard"
```
