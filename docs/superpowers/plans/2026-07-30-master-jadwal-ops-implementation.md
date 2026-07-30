# Master Jadwal OPS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan Master Jadwal OPS untuk mengelola roster operasional dan perubahan shift/lokasi secara bulk, baik langsung maupun efektif tanggal 1 bulan berikutnya.

**Architecture:** `employee_shifts` tetap menjadi sumber jadwal aktif yang dibaca absensi. `employee_shift_changes` menyimpan perubahan pending/applied/cancelled; service tunggal menerapkan perubahan yang sudah jatuh tempo sebelum jadwal dibaca. UI mengikuti pola pencarian, filter turun, card, tabel, dan modal pada `/hr/leave/master`.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Eloquent, MySQL 8.4, Pest.

## Global Constraints

- HR menentukan perubahan secara manual; tidak ada shuffle otomatis.
- Master Shift tetap menjadi sumber pola hari dan jam.
- Hanya role `HRD` dan `HR STAFF` yang dapat mengakses fitur.
- Pilihan waktu berlaku hanya `NOW` atau tanggal 1 bulan berikutnya berdasarkan `Asia/Jakarta`.
- Satu karyawan hanya boleh memiliki satu perubahan `PENDING`.
- Tidak ada UI riwayat rolling pada tahap ini.
- Perubahan bulk harus atomik.
- Jangan memakai `RefreshDatabase` atau `LazilyRefreshDatabase`; test memakai `DatabaseTransactions`.
- UI mengikuti `/hr/leave/master` tanpa dependency frontend baru.

---

### Task 1: Persistensi dan Model Perubahan Jadwal

**Files:**
- Create: `database/migrations/2026_07_30_150000_create_employee_shift_changes_table.php`
- Create: `app/Models/EmployeeShiftChange.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/OperationalScheduleTest.php`

**Interfaces:**
- Produces: `EmployeeShiftChange::STATUS_PENDING`, `STATUS_APPLIED`, `STATUS_CANCELLED`.
- Produces: `User::employeeShiftChanges()` dan `User::pendingShiftChange()`.
- Produces: `EmployeeShiftChange` relations `user`, `shift`, `location`, dan `creator`.

- [ ] **Step 1: Write the failing model and schema test**

```php
it('maps OPS membership and shift change relationships', function () {
    $creator = User::factory()->create();
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create();
    $location = AttendanceLocation::factory()->create();

    $change = EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'effective_date' => now()->addMonthNoOverflow()->startOfMonth()->toDateString(),
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $creator->id,
        'shift_name_snapshot' => $shift->name,
        'location_name_snapshot' => $location->name,
    ]);

    expect($user->fresh()->is_ops_schedule_member)->toBeTrue()
        ->and($user->pendingShiftChange->is($change))->toBeTrue()
        ->and($change->shift->is($shift))->toBeTrue()
        ->and($change->location->is($location))->toBeTrue()
        ->and($change->creator->is($creator))->toBeTrue();
});
```

- [ ] **Step 2: Run the test and verify RED**

Run:

```bash
php artisan test tests/Feature/OperationalScheduleTest.php --filter="maps OPS membership"
```

Expected: FAIL because `EmployeeShiftChange` and relationships do not exist.

- [ ] **Step 3: Add the idempotent migration**

The migration must:

```php
if (! Schema::hasColumn('users', 'is_ops_schedule_member')) {
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_ops_schedule_member')
            ->default(false)
            ->after('status')
            ->comment('1 = tampil pada Master Jadwal OPS');
    });
}

if (! Schema::hasTable('employee_shift_changes')) {
    Schema::create('employee_shift_changes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('location_id')->nullable()->constrained('attendance_locations')->nullOnDelete();
        $table->date('effective_date');
        $table->string('status', 20)->default('PENDING');
        $table->unsignedTinyInteger('pending_slot')->nullable()->default(1);
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->string('shift_name_snapshot');
        $table->string('location_name_snapshot');
        $table->dateTime('applied_at')->nullable();
        $table->dateTime('cancelled_at')->nullable();
        $table->timestamps();

        $table->unique(['user_id', 'pending_slot'], 'employee_shift_changes_unique_pending_user');
        $table->index(['user_id', 'effective_date'], 'employee_shift_changes_user_effective_index');
        $table->index(['status', 'effective_date'], 'employee_shift_changes_status_effective_index');
    });

    DB::statement(
        "ALTER TABLE employee_shift_changes
         ADD CONSTRAINT employee_shift_changes_status_check
         CHECK (
             (status = 'PENDING' AND pending_slot = 1)
             OR
             (status IN ('APPLIED', 'CANCELLED') AND pending_slot IS NULL)
         )"
    );
}
```

The `down()` method drops `employee_shift_changes`, then drops `users.is_ops_schedule_member` only when present.

- [ ] **Step 4: Add the model and relationships**

`EmployeeShiftChange` must define fillable fields, date/datetime casts, status constants, and `belongsTo` relationships. Add the membership field to `User::$fillable`, cast it to boolean, and add:

```php
public function employeeShiftChanges()
{
    return $this->hasMany(EmployeeShiftChange::class);
}

public function pendingShiftChange()
{
    return $this->hasOne(EmployeeShiftChange::class)
        ->where('status', EmployeeShiftChange::STATUS_PENDING);
}
```

Do not add a reverse relationship to `EmployeeShift`; no current consumer needs it.

- [ ] **Step 5: Run model test and inspect migration discovery**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php --filter="maps OPS membership"
php artisan migrate:status
```

Expected: model test PASS and the migration appears as pending. Do not run `php artisan migrate` in this task because the development schema was injected manually by the user; the idempotent guards allow the user to mark it through the normal deployment migration later without recreating either structure.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_07_30_150000_create_employee_shift_changes_table.php app/Models/EmployeeShiftChange.php app/Models/User.php tests/Feature/OperationalScheduleTest.php
git commit -m "feat: add operational schedule persistence"
```

---

### Task 2: Service untuk Jadwal Langsung, Pending, dan Jatuh Tempo

**Files:**
- Create: `app/Services/OperationalScheduleService.php`
- Modify: `app/Models/EmployeeShiftChange.php`
- Test: `tests/Feature/OperationalScheduleTest.php`

**Interfaces:**
- Produces: `applyNow(array $userIds, Shift $shift, AttendanceLocation $location, User $actor): void`.
- Produces: `scheduleNextMonth(array $userIds, Shift $shift, AttendanceLocation $location, User $actor, CarbonInterface $now): void`.
- Produces: `applyDueForUser(User $user, CarbonInterface $date): ?EmployeeShift`.
- Produces: `applyAllDue(CarbonInterface $date): int`.
- Consumes: status constants and relationships from Task 1.

- [ ] **Step 1: Write failing service tests**

Add three focused tests:

```php
it('applies an immediate bulk schedule and records applied changes', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $users = User::factory()->count(2)->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create(['is_active' => true]);

    app(OperationalScheduleService::class)
        ->applyNow($users->modelKeys(), $shift, $location, $actor);

    foreach ($users as $user) {
        $this->assertDatabaseHas('employee_shifts', [
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'location_id' => $location->id,
        ]);
        $this->assertDatabaseHas('employee_shift_changes', [
            'user_id' => $user->id,
            'status' => 'APPLIED',
            'pending_slot' => null,
        ]);
    }
});

it('replaces one pending schedule without changing the active schedule', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $oldShift = Shift::factory()->create(['is_active' => true]);
    $newShift = Shift::factory()->create(['is_active' => true]);
    $replacementShift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create(['is_active' => true]);
    EmployeeShift::create([
        'user_id' => $user->id,
        'shift_id' => $oldShift->id,
        'location_id' => $location->id,
    ]);
    $service = app(OperationalScheduleService::class);
    $now = Carbon::parse('2026-07-30', 'Asia/Jakarta');

    $service->scheduleNextMonth([$user->id], $newShift, $location, $actor, $now);
    $service->scheduleNextMonth([$user->id], $replacementShift, $location, $actor, $now);

    expect($user->employeeShift()->value('shift_id'))->toBe($oldShift->id)
        ->and(EmployeeShiftChange::where('user_id', $user->id)
            ->where('status', EmployeeShiftChange::STATUS_CANCELLED)->count())->toBe(1)
        ->and(EmployeeShiftChange::where('user_id', $user->id)
            ->where('status', EmployeeShiftChange::STATUS_PENDING)
            ->where('shift_id', $replacementShift->id)->count())->toBe(1);
});

it('applies a due pending schedule using the application date', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create(['is_active' => true]);
    $change = EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'effective_date' => '2026-08-01',
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $actor->id,
        'shift_name_snapshot' => $shift->name,
        'location_name_snapshot' => $location->name,
    ]);

    app(OperationalScheduleService::class)
        ->applyDueForUser($user, Carbon::parse('2026-08-01', 'Asia/Jakarta'));

    expect($user->employeeShift()->value('shift_id'))->toBe($shift->id)
        ->and($change->fresh()->status)->toBe(EmployeeShiftChange::STATUS_APPLIED)
        ->and($change->fresh()->pending_slot)->toBeNull()
        ->and($change->fresh()->applied_at)->not->toBeNull();
});
```

Use literal dates and real database rows; do not mock Eloquent.

- [ ] **Step 2: Run service tests and verify RED**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php --filter="schedule"
```

Expected: FAIL because `OperationalScheduleService` does not exist.

- [ ] **Step 3: Implement transaction-safe service**

All mutation methods use `DB::transaction()`. For each user ID:

1. Lock the active `User` row and verify `is_ops_schedule_member = 1`.
2. For immediate changes, `EmployeeShift::updateOrCreate(['user_id' => $id], [...])`.
3. Create an `APPLIED` change with `pending_slot = null`, snapshots, and `applied_at = now()`.
4. For next month, lock and cancel any current pending row by setting `status = CANCELLED`, `pending_slot = null`, and `cancelled_at`.
5. Insert the new pending row with `effective_date = $now->copy()->addMonthNoOverflow()->startOfMonth()`.
6. For due changes, lock the pending row where `effective_date <= $date`, update/create `employee_shifts`, then mark it applied.

`applyDueForUser()` returns the refreshed `EmployeeShift` with `shift` and `location`, or the existing assignment when no change is due.

- [ ] **Step 4: Run service tests**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php --filter="schedule"
```

Expected: all service tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/OperationalScheduleService.php app/Models/EmployeeShiftChange.php tests/Feature/OperationalScheduleTest.php
git commit -m "feat: manage operational schedule changes"
```

---

### Task 3: Endpoint, Roster, Bulk Form, dan UI Master Jadwal OPS

**Files:**
- Create: `app/Http/Controllers/HR/OperationalScheduleController.php`
- Create: `resources/views/hr/operational_schedules/index.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/OperationalScheduleTest.php`

**Interfaces:**
- Consumes: `OperationalScheduleService` from Task 2.
- Produces routes:
  - `GET /hr/operational-schedules` → `hr.operational-schedules.index`
  - `POST /hr/operational-schedules/members` → `hr.operational-schedules.members.store`
  - `DELETE /hr/operational-schedules/members/{user}` → `hr.operational-schedules.members.destroy`
  - `POST /hr/operational-schedules/bulk` → `hr.operational-schedules.bulk`

- [ ] **Step 1: Write failing controller and authorization tests**

```php
it('shows only active OPS members with current and pending schedules', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $member = User::factory()->create(['status' => 'ACTIVE', 'is_ops_schedule_member' => true]);
    User::factory()->create(['status' => 'ACTIVE', 'is_ops_schedule_member' => false]);

    actingAs($hrd, 'web');

    $response = $this->get('/hr/operational-schedules');

    $response->assertOk()
        ->assertSee($member->name)
        ->assertViewHas('items', fn ($items) => $items->pluck('id')->all() === [$member->id]);
});

it('adds and removes OPS members without deleting their active schedule', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['status' => 'ACTIVE']);
    $shift = Shift::factory()->create();
    $location = AttendanceLocation::factory()->create();
    EmployeeShift::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ]);
    actingAs($hrd, 'web');

    $this->post('/hr/operational-schedules/members', ['user_ids' => [$user->id]])
        ->assertRedirect();
    expect($user->fresh()->is_ops_schedule_member)->toBeTrue();

    $this->delete("/hr/operational-schedules/members/{$user->id}")
        ->assertRedirect();
    expect($user->fresh()->is_ops_schedule_member)->toBeFalse();
    $this->assertDatabaseHas('employee_shifts', ['user_id' => $user->id]);
});

it('bulk updates selected OPS members for now or next month', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $users = User::factory()->count(2)->create([
        'status' => 'ACTIVE',
        'is_ops_schedule_member' => true,
    ]);
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create(['is_active' => true]);
    actingAs($hrd, 'web');

    $this->post('/hr/operational-schedules/bulk', [
        'user_ids' => $users->modelKeys(),
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'apply_mode' => 'NOW',
    ])->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('employee_shifts', [
        'user_id' => $users->first()->id,
        'shift_id' => $shift->id,
    ]);

    $this->post('/hr/operational-schedules/bulk', [
        'user_ids' => $users->modelKeys(),
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'apply_mode' => 'NEXT_MONTH',
    ])->assertRedirect()->assertSessionHas('success');
    $this->assertDatabaseHas('employee_shift_changes', [
        'user_id' => $users->first()->id,
        'status' => EmployeeShiftChange::STATUS_PENDING,
    ]);
});

it('rejects non OPS users and inactive shift or location in bulk updates', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create([
        'status' => 'ACTIVE',
        'is_ops_schedule_member' => false,
    ]);
    $shift = Shift::factory()->create(['is_active' => false]);
    $location = AttendanceLocation::factory()->create(['is_active' => false]);
    actingAs($hrd, 'web');

    $this->post('/hr/operational-schedules/bulk', [
        'user_ids' => [$user->id],
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'apply_mode' => 'NOW',
    ])->assertSessionHasErrors(['user_ids.0', 'shift_id', 'location_id']);
    $this->assertDatabaseMissing('employee_shifts', ['user_id' => $user->id]);
});
```

Also assert unauthenticated users redirect to login and non-HR roles receive the existing role middleware denial behavior.

- [ ] **Step 2: Run controller tests and verify RED**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php --filter="OPS members|bulk updates|non OPS"
```

Expected: FAIL with 404 because routes/controller do not exist.

- [ ] **Step 3: Implement controller validation and queries**

`index()` calls `applyAllDue(now())`, then loads active OPS users with:

- `profile.pt`;
- `position`;
- `employeeShift.shift`;
- `employeeShift.location`;
- `pendingShiftChange`.

Apply `q`, `pt_id`, `position_id`, `shift_id`, and pending-status filters before paginating 20 rows. Load active shifts/locations and active non-member users for the add-member modal.

`storeMembers()` validates distinct active user IDs and sets `is_ops_schedule_member = true` in one transaction.

`destroyMember()` sets membership false and cancels its pending change without deleting `employee_shifts`.

`bulkUpdate()` validates:

```php
[
    'user_ids' => ['required', 'array', 'min:1'],
    'user_ids.*' => [
        'integer',
        'distinct',
        Rule::exists('users', 'id')
            ->where('status', User::STATUS_ACTIVE)
            ->where('is_ops_schedule_member', true),
    ],
    'shift_id' => [
        'required',
        Rule::exists('shifts', 'id')->where('is_active', true),
    ],
    'location_id' => [
        'required',
        Rule::exists('attendance_locations', 'id')->where('is_active', true),
    ],
    'apply_mode' => ['required', Rule::in(['NOW', 'NEXT_MONTH'])],
]
```

Compute next-month date server-side; do not accept an arbitrary effective date from the browser.

- [ ] **Step 4: Build the Blade UI using the leave-master pattern**

Reuse the visual language of `/hr/leave/master`:

- white filter card;
- always-visible employee search;
- Search, Reset, and funnel Filter buttons;
- collapsible advanced filter panel;
- rounded table card;
- responsive horizontal scrolling;
- compact status badges;
- existing `<x-modal>` for confirmations.

The table includes a sticky checkbox/name area, current shift/location, pending schedule/date, and remove action. JavaScript must:

- toggle the advanced filter panel;
- select/deselect visible rows;
- update selected count;
- disable bulk submit when nothing is selected;
- show a confirmation message containing count, shift, location, and effective mode.

Use native checkbox, radio, select, and existing modal/CSS patterns; add no package.

- [ ] **Step 5: Add routes and sidebar entry**

Register routes inside the existing `role:HRD,HR STAFF` group. Add `hr.operational-schedules.*` to `$hrPresensiOpen` and add submenu label `Jadwal Operasional` directly after `Jadwal Karyawan`.

- [ ] **Step 6: Run controller/view tests and compile Blade**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php
php artisan view:cache
php artisan route:list --name=hr.operational-schedules
```

Expected: tests PASS, Blade compiles, and four routes appear.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/HR/OperationalScheduleController.php resources/views/hr/operational_schedules/index.blade.php resources/views/layouts/app.blade.php routes/web.php tests/Feature/OperationalScheduleTest.php
git commit -m "feat: add master jadwal operasional"
```

---

### Task 4: Integrasi Jadwal Efektif dengan Absensi dan Master Jadwal Karyawan

**Files:**
- Modify: `app/Http/Controllers/AttendanceController.php`
- Modify: `app/Http/Controllers/HR/ScheduleController.php`
- Modify: `app/Services/OperationalScheduleService.php`
- Test: `tests/Feature/AttendanceControllerTest.php`
- Test: `tests/Feature/OperationalScheduleTest.php`

**Interfaces:**
- Consumes: `OperationalScheduleService::applyDueForUser()` and `applyAllDue()`.
- Preserves: attendance snapshots `shift_id`, `employee_shift_id`, normal times, and `location_id`.

- [ ] **Step 1: Write failing attendance boundary tests**

```php
it('uses a due OPS schedule when clocking in on its effective date', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-01 07:55', 'Asia/Jakarta'));
    Storage::fake('public');

    $fixture = attendanceFixtureWithPendingOpsSchedule(
        effectiveDate: '2026-08-01',
        clockInAtNewLocation: true,
    );

    actingAs($fixture->user, 'web');
    $this->post('/attendance/clock-in', $fixture->clockInPayload)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('attendances', [
        'user_id' => $fixture->user->id,
        'shift_id' => $fixture->newShift->id,
        'location_id' => $fixture->newLocation->id,
    ]);
    expect($fixture->change->fresh()->status)
        ->toBe(EmployeeShiftChange::STATUS_APPLIED);
});

it('keeps using the active schedule before the pending effective date', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-31 07:55', 'Asia/Jakarta'));

    $fixture = attendanceFixtureWithPendingOpsSchedule(
        effectiveDate: '2026-08-01',
        clockInAtNewLocation: false,
    );

    actingAs($fixture->user, 'web');
    $this->post('/attendance/clock-in', $fixture->clockInPayload)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('attendances', [
        'user_id' => $fixture->user->id,
        'shift_id' => $fixture->oldShift->id,
        'location_id' => $fixture->oldLocation->id,
    ]);
    expect($fixture->change->fresh()->status)
        ->toBe(EmployeeShiftChange::STATUS_PENDING);
});

it('uses the attendance location snapshot when clocking out after an immediate schedule change', function () {
    $fixture = checkedInAttendanceFixture();
    EmployeeShift::where('user_id', $fixture->user->id)->update([
        'location_id' => $fixture->newLocation->id,
    ]);
    actingAs($fixture->user, 'web');

    $this->post('/attendance/clock-out', $fixture->clockOutAtOriginalLocationPayload)
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($fixture->attendance->fresh()->clock_out)->not->toBeNull();
});
```

Implement `attendanceFixtureWithPendingOpsSchedule()` and `checkedInAttendanceFixture()` beside the existing attendance test helpers by reusing the same base64 photo payload, shift-day creation, and coordinate setup already present in `AttendanceControllerTest.php`. Reset Carbon in `afterEach`.

- [ ] **Step 2: Run boundary tests and verify RED**

```bash
php artisan test tests/Feature/AttendanceControllerTest.php --filter="OPS schedule|location snapshot"
```

Expected: FAIL because attendance still reads the old/current assignment directly.

- [ ] **Step 3: Integrate the shared resolver**

Inject `OperationalScheduleService` alongside `ImageCompressor` in `AttendanceController`.

Before clock-in resolves `EmployeeShift`, call:

```php
$employeeShift = $this->operationalSchedules
    ->applyDueForUser($user, $now);
```

For WFO clock-out, validate radius against `$attendance->location` rather than re-reading the user's current `EmployeeShift`. This keeps an active attendance session bound to its clock-in snapshot.

In `ScheduleController::index()`, call `applyAllDue(now())` before loading current assignments so Master Jadwal Karyawan also reflects due changes.

- [ ] **Step 4: Run attendance and operational tests**

```bash
php artisan test tests/Feature/AttendanceControllerTest.php
php artisan test tests/Feature/OperationalScheduleTest.php
```

Expected: both suites PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/AttendanceController.php app/Http/Controllers/HR/ScheduleController.php app/Services/OperationalScheduleService.php tests/Feature/AttendanceControllerTest.php tests/Feature/OperationalScheduleTest.php
git commit -m "feat: apply effective operational schedules to attendance"
```

---

### Task 5: Final Verification

**Files:**
- Verify all files changed in Tasks 1–4.

**Interfaces:**
- No new interface; this task verifies the complete feature.

- [ ] **Step 1: Run focused suites**

```bash
php artisan test tests/Feature/OperationalScheduleTest.php
php artisan test tests/Feature/AttendanceControllerTest.php
```

Expected: zero failures.

- [ ] **Step 2: Run syntax, route, Blade, and diff checks**

```bash
php -l app/Models/EmployeeShiftChange.php
php -l app/Services/OperationalScheduleService.php
php -l app/Http/Controllers/HR/OperationalScheduleController.php
php artisan route:list --name=hr.operational-schedules
php artisan view:cache
git diff --check
```

Expected: no syntax errors, four OPS routes, successful Blade compilation, clean diff check.

- [ ] **Step 3: Run full application suite and separate unrelated failures**

```bash
php artisan test
```

Expected: all OPS and attendance tests pass. If the existing `LoanRequestTest` failures remain, report them separately and do not modify the loan module.

- [ ] **Step 4: Inspect final scope**

```bash
git status --short
git diff --stat
```

Confirm no PWA, login, loan, or unrelated user-owned files were included in feature commits.
