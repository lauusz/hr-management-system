<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

it('lists each Clock In and Clock Out as an independent event across dates', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['name' => 'USER LINTAS HARI']);

    AttendanceEvent::factory()->forUser($employee)->create(['event_type' => AttendanceEvent::CLOCK_IN, 'occurred_at' => '2026-10-10 18:00:00']);
    AttendanceEvent::factory()->forUser($employee)->clockOut()->create(['occurred_at' => '2026-10-11 02:00:00']);
    AttendanceEvent::factory()->forUser($employee)->create(['event_type' => AttendanceEvent::CLOCK_IN, 'occurred_at' => '2026-10-11 15:00:00']);
    AttendanceEvent::factory()->forUser($employee)->clockOut()->create(['occurred_at' => '2026-10-11 20:00:00']);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => '2026-10-10',
        'date_end' => '2026-10-11',
    ]));

    expect($response->viewData('items'))->toHaveCount(4)
        ->and($response->viewData('items')->pluck('event_type')->all())
        ->toContain(AttendanceEvent::CLOCK_IN, AttendanceEvent::CLOCK_OUT);

    $response->assertOk()
        ->assertSee('18:00')
        ->assertSee('02:00')
        ->assertSee('15:00')
        ->assertSee('20:00')
        ->assertSee('10/10/26')
        ->assertSee('11/10/26');
});

it('filters event history by occurred_at range and employee name without blank rows', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $match = User::factory()->create(['name' => 'MOH. AINUL YAQIN']);
    $other = User::factory()->create(['name' => 'KARYAWAN LAIN']);

    AttendanceEvent::factory()->forUser($match)->create(['occurred_at' => '2026-10-12 08:00:00']);
    AttendanceEvent::factory()->forUser($match)->clockOut()->create(['occurred_at' => '2026-10-13 17:00:00']);
    AttendanceEvent::factory()->forUser($other)->create(['occurred_at' => '2026-10-13 08:00:00']);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => '2026-10-13',
        'date_end' => '2026-10-13',
        'q' => 'mohainul',
    ]));

    expect($response->viewData('items'))->toHaveCount(1)
        ->and($response->viewData('items')->sole()->user_id)->toBe($match->id)
        ->and($response->viewData('items')->sole()->event_type)->toBe(AttendanceEvent::CLOCK_OUT);
});

it('sorts attendance events by the latest submitted input, not by attendance time', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $earliestSubmission = AttendanceEvent::factory()->forUser($employee)->clockOut()->create([
        'occurred_at' => '2026-10-15 02:00:00',
        'created_at' => '2026-10-15 09:00:00',
        'updated_at' => '2026-10-15 09:00:00',
    ]);
    $latestSubmission = AttendanceEvent::factory()->forUser($employee)->create([
        'occurred_at' => '2026-10-15 18:00:00',
        'created_at' => '2026-10-15 18:00:00',
        'updated_at' => '2026-10-15 18:00:00',
    ]);
    $sameSecondLatestSubmission = AttendanceEvent::factory()->forUser($employee)->clockOut()->create([
        'occurred_at' => '2026-10-15 15:00:00',
        'created_at' => '2026-10-15 18:00:00',
        'updated_at' => '2026-10-15 18:00:00',
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => '2026-10-15',
        'date_end' => '2026-10-15',
    ]));

    expect($response->viewData('items')->pluck('id')->all())
        ->toBe([$sameSecondLatestSubmission->id, $latestSubmission->id, $earliestSubmission->id]);
});

it('does not expose events belonging to inactive employees', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $active = User::factory()->create(['name' => 'KARYAWAN AKTIF']);
    $inactive = User::factory()->create(['name' => 'KARYAWAN NONAKTIF', 'status' => 'INACTIVE']);

    AttendanceEvent::factory()->forUser($active)->create(['occurred_at' => '2026-10-13 08:00:00']);
    AttendanceEvent::factory()->forUser($inactive)->create(['occurred_at' => '2026-10-13 08:00:00']);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => '2026-10-13',
        'date_end' => '2026-10-13',
    ]));

    $response->assertSee($active->name)->assertDontSee($inactive->name);
});

it('shows the event date work schedule and distinct event type chips', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $scheduledEmployee = User::factory()->create(['name' => 'KARYAWAN BERSHIFT']);
    $employeeWithoutShift = User::factory()->create(['name' => 'KARYAWAN TANPA SHIFT']);
    $onTimeEmployee = User::factory()->create(['name' => 'KARYAWAN TEPAT WAKTU']);
    $shift = Shift::factory()->create();

    EmployeeShift::factory()->forUser($scheduledEmployee)->forShift($shift)->create();
    EmployeeShift::factory()->forUser($onTimeEmployee)->forShift($shift)->create();
    ShiftDay::factory()->forShift($shift)->create([
        'day_of_week' => 3,
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
    ]);

    AttendanceEvent::factory()->forUser($scheduledEmployee)->create([
        'event_type' => AttendanceEvent::CLOCK_IN,
        'occurred_at' => '2026-10-14 08:45:00',
    ]);
    AttendanceEvent::factory()->forUser($employeeWithoutShift)->clockOut()->create([
        'occurred_at' => '2026-10-14 17:00:00',
    ]);
    AttendanceEvent::factory()->forUser($onTimeEmployee)->create([
        'event_type' => AttendanceEvent::CLOCK_IN,
        'occurred_at' => '2026-10-14 08:00:59',
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => '2026-10-14',
        'date_end' => '2026-10-14',
    ]));

    $items = $response->viewData('items')->keyBy('user_id');

    expect($items[$scheduledEmployee->id]->work_schedule)->toBe('08:00–16:00')
        ->and($items[$scheduledEmployee->id]->late_label)->toBe('45 min')
        ->and($items[$employeeWithoutShift->id]->work_schedule)->toBe('-')
        ->and($items[$onTimeEmployee->id]->late_label)->toBe('-');

    $response->assertOk()
        ->assertSee('08:00–16:00')
        ->assertSee('Terlambat')
        ->assertSee('45 min')
        ->assertSee('attendance-type-chip--in', false)
        ->assertSee('attendance-type-chip--out', false)
        ->assertSee('attendance-media-column', false)
        ->assertSee('right: calc(100% + 6px)', false)
        ->assertSee('closeOtherAttendanceActionMenus', false);
});
