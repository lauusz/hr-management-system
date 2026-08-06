# Employee Live Search and Three-Column Grid Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the employee search button, update results automatically from the server, and show three compact employee cards per row on wide desktop.

**Architecture:** Reuse the existing GET form and controller query. Native JavaScript fetches the same page after a 350 ms debounce and replaces only the result count, card list, and pagination; CSS extends the existing responsive grid at 1280px.

**Tech Stack:** Laravel Blade, native Fetch API, CSS Grid, Pest feature tests.

## Global Constraints

- Work directly on `main` as requested.
- Do not change database, routes, controller query, pagination size, or add dependencies.
- Keep server-side search and every existing filter.
- Mobile remains one column; 1024px remains two columns; 1280px becomes three columns.
- Keep shift selection and employee detail actions functional.

---

### Task 1: Live Search and Compact Desktop Grid

**Files:**
- Modify: `tests/Feature/HrEmployeeControllerTest.php`
- Modify: `resources/views/hr/employees/index.blade.php`

**Interfaces:**
- Consumes: existing GET form `#filterForm` and `q` query parameter.
- Produces: `[data-employee-live-search]`, `[data-employee-results]`, `[data-employee-list]`, and `[data-employee-pagination]` DOM targets.

- [ ] **Step 1: Add a failing rendered-page test**

```php
it('renders live employee search and a three column desktop grid', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd)
        ->get(route('hr.employees.index'))
        ->assertOk()
        ->assertSee('data-employee-live-search', false)
        ->assertSee('data-employee-results', false)
        ->assertSee('data-employee-list', false)
        ->assertSee('data-employee-pagination', false)
        ->assertSee('grid-template-columns: repeat(3, minmax(0, 1fr))', false)
        ->assertDontSee('emp-btn-search', false);
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php artisan test tests\Feature\HrEmployeeControllerTest.php --filter="live employee search"`

Expected: FAIL because the markers and three-column rule do not exist and the search button is still rendered.

- [ ] **Step 3: Update search markup and result targets**

Change the search input to `type="search"`, add `data-employee-live-search`, remove the Cari button, and add the result target attributes plus `aria-live="polite"`.

- [ ] **Step 4: Add native debounced server search**

```js
const form = document.getElementById('filterForm');
const input = form?.querySelector('[data-employee-live-search]');
let timer;
let request;

input?.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(loadEmployeeResults, 350);
});
```

`loadEmployeeResults()` builds a URL from `new FormData(form)`, removes `page`, aborts the prior request, fetches the rendered page, parses it with `DOMParser`, copies the three result targets, and calls `history.replaceState`. Abort errors are ignored; other failures leave current results unchanged.

- [ ] **Step 5: Add the desktop grid rule**

```css
@media (min-width: 1280px) {
    .emp-list {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .emp-card { min-width: 0; padding: 14px; gap: 12px; }
    .emp-avatar { width: 40px; height: 40px; }
}
```

- [ ] **Step 6: Run verification**

```powershell
php artisan test tests\Feature\HrEmployeeControllerTest.php --compact
php artisan test tests\Unit\SearchInputAutocompleteTest.php --compact
git diff --check
```

Expected: both test files pass and the diff has no whitespace errors.

- [ ] **Step 7: Commit**

```powershell
git add resources/views/hr/employees/index.blade.php tests/Feature/HrEmployeeControllerTest.php
git commit -m "feat: add live employee search grid"
```
