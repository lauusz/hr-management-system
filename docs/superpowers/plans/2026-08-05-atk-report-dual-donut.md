# ATK Report Dual Donut Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the PT and item bar charts on `/v2/atk/admin/reports` with responsive donut charts for approved requests per PT and approved item quantity.

**Architecture:** Keep the existing filtered report query as the single data source. Prepare accessible chart segments and CSS gradients in `ReportController`, then render native HTML/CSS donuts and legends in the existing Blade view without adding a chart dependency.

**Tech Stack:** Laravel, Blade, MySQL query builder, native CSS `conic-gradient`, Pest feature tests.

## Global Constraints

- Do not change the database, migrations, routes, or Excel export.
- PT donut counts distinct approved/partial requests containing approved items.
- Item donut sums approved item quantities, exposes five leading items, and combines the remainder as `Lainnya`.
- Both charts must remain understandable without color through visible legends.
- Mobile stacks donut and legend; desktop places both chart cards side by side.

---

### Task 1: Dual Donut Report

**Files:**
- Modify: `tests/Feature/AtkV2Test.php`
- Modify: `app/Http/Controllers/Atk/Admin/ReportController.php`
- Modify: `resources/views/atk/admin/reports/index.blade.php`

**Interfaces:**
- Consumes: existing `$baseQuery`, month filter, and optional `pt_id` filter.
- Produces: `$ptRows`, `$ptChartGradient`, `$ptRequestTotal`, `$itemChartRows`, `$itemChartGradient`, and `$itemQtyTotal` for the report view.

- [ ] **Step 1: Write the failing feature test**

Update the existing report tests to require `atk-report-donut`, the two chart titles, accessible legends, and an item dataset containing six named items where only five names plus `Lainnya` appear as donut segments.

```php
actingAs($admin)
    ->get(route('v2.atk.admin.reports.index'))
    ->assertOk()
    ->assertSee('Pengajuan per PT')
    ->assertSee('Barang Keluar Terbanyak')
    ->assertSee('class="atk-report-donut"', false)
    ->assertSee('data-item-segment="Lainnya"', false)
    ->assertDontSee('data-item-segment="Barang Peringkat Enam"', false);
```

- [ ] **Step 2: Run the focused tests to verify RED**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="filters usage report|renders an informative pt report dashboard"`

Expected: FAIL because the current report explicitly renders bar charts and does not expose the second donut dataset.

- [ ] **Step 3: Prepare chart data in the controller**

Reuse the existing color palette. Keep every active PT segment, change the item aggregation to fetch all grouped items, keep the first five, sum all remaining quantities into a `Lainnya` row, calculate percentages, and build `conic-gradient` segment strings. Pass both totals and both gradients to the view.

```php
$itemQtyTotal = (int) $itemRows->sum('total_qty');
$itemChartRows = $itemRows->take(5)->values();
$otherQty = (int) $itemRows->skip(5)->sum('total_qty');

if ($otherQty > 0) {
    $itemChartRows->push((object) [
        'item_name_snapshot' => 'Lainnya',
        'total_qty' => $otherQty,
    ]);
}
```

- [ ] **Step 4: Render both responsive donuts**

Replace the PT and item bar markup with semantic donut figures and visible legends. Put the relevant total in each donut center, use the prepared gradients via CSS custom properties, and retain existing empty states and requester ranking.

```blade
<div class="atk-report-donut" style="--donut-gradient: {{ $ptChartGradient }}">
    <div class="atk-report-donut-center">
        <strong>{{ $ptRequestTotal }}</strong>
        <span>pengajuan</span>
    </div>
</div>
```

- [ ] **Step 5: Run verification**

Run:

```powershell
php artisan test tests\Feature\AtkV2Test.php --compact
vendor\bin\pint --test app\Http\Controllers\Atk\Admin\ReportController.php tests\Feature\AtkV2Test.php
git diff --check
```

Expected: all ATK tests pass, Pint passes, and `git diff --check` produces no output.

- [ ] **Step 6: Commit**

```powershell
git add app/Http/Controllers/Atk/Admin/ReportController.php resources/views/atk/admin/reports/index.blade.php tests/Feature/AtkV2Test.php
git commit -m "feat: add dual donut charts to atk report"
```
