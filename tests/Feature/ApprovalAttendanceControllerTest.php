<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\User;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

it('counts each employee once in the approval sidebar badge across both eligible queues', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $first = User::factory()->create();
    $second = User::factory()->create();
    $clockIn = AttendanceEvent::factory()->forUser($first)->create(['is_flexible' => true]);
    $clockOut = AttendanceEvent::factory()->forUser($first)->clockOut()->create(['is_flexible' => true]);
    $remote = AttendanceEvent::factory()->forUser($second)->create(['type' => 'DINAS_LUAR', 'is_flexible' => null]);
    AttendanceEvent::factory()->create(['is_flexible' => false]);
    AttendanceEvent::factory()->create(['is_flexible' => null]);
    AttendanceEvent::factory()->create(['is_flexible' => true, 'approval_status' => 'APPROVED']);

    $this->actingAs($hrd, 'web')->get(route('hr.approval_attendance.index', ['tab' => 'remote']))
        ->assertOk()->assertSee('aria-label="2 karyawan menunggu persetujuan absensi"', false);

    $clockIn->update(['approval_status' => 'APPROVED']);
    $clockOut->update(['approval_status' => 'APPROVED']);
    $remote->update(['approval_status' => 'APPROVED']);

    $this->get(route('hr.approval_attendance.index'))
        ->assertOk()->assertDontSee('data-attendance-approval-badge', false);
});

it('filters pending approval events by Clock In and Clock Out type', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    AttendanceEvent::factory()->forUser($employee)->create(['event_type' => AttendanceEvent::CLOCK_IN, 'is_flexible' => true]);
    AttendanceEvent::factory()->forUser($employee)->clockOut()->create(['is_flexible' => true]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.approval_attendance.index', ['event_type' => 'CLOCK_OUT']));

    expect($response->viewData('pendingAttendances'))->toHaveCount(1)
        ->and($response->viewData('pendingAttendances')->sole()->event_type)->toBe(AttendanceEvent::CLOCK_OUT);
});

it('shows only outside-area flexible events from the new flow while retaining legacy events', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $legacyClockOut = AttendanceEvent::factory()->clockOut()->create([
        'is_flexible' => true,
        'is_outside_reference_location' => null,
    ]);
    $outsideClockIn = AttendanceEvent::factory()->create([
        'is_flexible' => true,
        'is_outside_reference_location' => true,
    ]);
    $insideClockIn = AttendanceEvent::factory()->create([
        'is_flexible' => true,
        'is_outside_reference_location' => false,
    ]);
    $newClockOut = AttendanceEvent::factory()->clockOut()->create([
        'is_flexible' => true,
        'is_outside_reference_location' => false,
    ]);
    $outsideClockOut = AttendanceEvent::factory()->clockOut()->create([
        'is_flexible' => true,
        'is_outside_reference_location' => true,
    ]);

    $events = $this->actingAs($hrd, 'web')
        ->get(route('hr.approval_attendance.index'))
        ->viewData('pendingAttendances');

    expect($events->modelKeys())
        ->toContain($legacyClockOut->id, $outsideClockIn->id, $outsideClockOut->id)
        ->not->toContain($insideClockIn->id, $newClockOut->id);
});

it('approves one pending event without changing its time or evidence', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->create([
        'occurred_at' => '2026-10-13 08:00:00',
        'photo' => 'attendance_photos/event.jpg',
        'is_flexible' => true,
    ]);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.approval_attendance.approve', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->fresh()->approval_status)->toBe('APPROVED')
        ->and($event->fresh()->occurred_at->format('Y-m-d H:i'))->toBe('2026-10-13 08:00')
        ->and($event->fresh()->photo)->toBe('attendance_photos/event.jpg');
});

it('approves multiple pending events and leaves a decided event unchanged', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $first = AttendanceEvent::factory()->create(['is_flexible' => true]);
    $second = AttendanceEvent::factory()->clockOut()->create(['is_flexible' => true]);
    $decided = AttendanceEvent::factory()->create(['is_flexible' => true, 'approval_status' => 'REJECTED']);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.approval_attendance.bulk_approve'), ['attendance_ids' => [$first->id, $second->id, $decided->id]])
        ->assertRedirect();

    expect($first->fresh()->approval_status)->toBe('APPROVED')
        ->and($second->fresh()->approval_status)->toBe('APPROVED')
        ->and($decided->fresh()->approval_status)->toBe('REJECTED');
});

it('moves only a pending event between Clock In and Clock Out', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->clockOut()->create(['approval_status' => 'PENDING', 'is_flexible' => true]);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.approval_attendance.move_clock_side', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->fresh()->event_type)->toBe(AttendanceEvent::CLOCK_IN);
});

it('does not correct an event that has already been decided', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->create(['approval_status' => 'APPROVED']);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.approval_attendance.move_clock_side', $event))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($event->fresh()->event_type)->toBe(AttendanceEvent::CLOCK_IN);
});

it('separates flexible and remote pending events into tabs without showing unknown or regular WFO', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $flexible = AttendanceEvent::factory()->create(['is_flexible' => true, 'notes' => 'Bekerja dari rumah']);
    $remote = AttendanceEvent::factory()->create(['type' => 'DINAS_LUAR', 'is_flexible' => null, 'notes' => 'Kunjungan']);
    $unknown = AttendanceEvent::factory()->create(['is_flexible' => null]);
    $regular = AttendanceEvent::factory()->create(['is_flexible' => false]);
    $decided = AttendanceEvent::factory()->create(['is_flexible' => true, 'approval_status' => 'APPROVED']);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.approval_attendance.index'));
    $response->assertOk()->assertSee('Bekerja dari rumah')->assertDontSee('Kunjungan')->assertDontSee('Mode tidak diketahui');
    expect($response->viewData('pendingAttendances')->modelKeys())
        ->toContain($flexible->id)
        ->not->toContain($remote->id, $unknown->id, $regular->id, $decided->id);

    $response = $this->get(route('hr.approval_attendance.index', ['tab' => 'remote']));
    $response->assertOk()->assertSee('Kunjungan')->assertDontSee('Bekerja dari rumah')->assertDontSee('Mode tidak diketahui');
    expect($response->viewData('pendingAttendances')->modelKeys())
        ->toContain($remote->id)
        ->not->toContain($flexible->id, $unknown->id, $regular->id, $decided->id);
});

it('keeps remote filters and live search within the remote tab', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['name' => 'Karyawan Contoh']);
    $remote = AttendanceEvent::factory()->forUser($employee)->clockOut()->create([
        'type' => 'DINAS_LUAR', 'occurred_at' => '2026-10-19 17:00:00',
    ]);
    $flexible = AttendanceEvent::factory()->forUser($employee)->clockOut()->create([
        'is_flexible' => true, 'occurred_at' => '2026-10-19 17:00:00',
    ]);
    AttendanceEvent::factory()->forUser($employee)->create([
        'type' => 'DINAS_LUAR', 'occurred_at' => '2026-10-19 08:00:00',
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.approval_attendance.index', [
        'tab' => 'remote', 'name' => 'Contoh', 'event_type' => 'CLOCK_OUT', 'submitted_range' => '2026-10-19',
    ]), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk();
    expect($response->viewData('pendingAttendances')->modelKeys())->toBe([$remote->id])
        ->and($response->viewData('tab'))->toBe('remote')
        ->and($flexible->fresh()->approval_status)->toBe('PENDING');
});

it('only processes eligible pending events when approval IDs are mixed', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $flexible = AttendanceEvent::factory()->create(['is_flexible' => true]);
    $remote = AttendanceEvent::factory()->create(['type' => 'DINAS_LUAR']);
    $unknown = AttendanceEvent::factory()->create(['is_flexible' => null]);
    $regular = AttendanceEvent::factory()->create(['is_flexible' => false]);

    $this->actingAs($hrd, 'web')->post(route('hr.approval_attendance.bulk_approve'), [
        'attendance_ids' => [$flexible->id, $remote->id, $unknown->id, $regular->id],
    ])->assertRedirect();

    expect($flexible->fresh()->approval_status)->toBe('APPROVED')
        ->and($remote->fresh()->approval_status)->toBe('APPROVED')
        ->and($unknown->fresh()->approval_status)->toBe('PENDING')
        ->and($regular->fresh()->approval_status)->toBe('PENDING');

    $this->post(route('hr.approval_attendance.approve', $regular))->assertSessionHas('error');
    $this->post(route('hr.approval_attendance.move_clock_side', $regular))->assertSessionHas('error');
    $this->post(route('hr.approval_attendance.reject', $regular), ['rejection_note' => 'Tidak sesuai'])->assertSessionHas('error');
    $this->post(route('hr.approval_attendance.approve', $unknown))->assertSessionHas('error');
    $this->post(route('hr.approval_attendance.move_clock_side', $unknown))->assertSessionHas('error');
    expect($regular->fresh()->approval_status)->toBe('PENDING')
        ->and($regular->fresh()->event_type)->toBe(AttendanceEvent::CLOCK_IN);
});

it('preserves flexible event evidence and an empty reason when correcting the type', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->clockOut()->create([
        'is_flexible' => true,
        'notes' => null,
        'occurred_at' => '2026-10-19 17:00:00',
        'photo' => 'attendance_photos/event.jpg',
    ]);

    $this->actingAs($hrd, 'web')->post(route('hr.approval_attendance.move_clock_side', $event))
        ->assertRedirect()->assertSessionHas('success');

    expect($event->fresh()->event_type)->toBe(AttendanceEvent::CLOCK_IN)
        ->and($event->fresh()->is_flexible)->toBeTrue()
        ->and($event->fresh()->notes)->toBeNull()
        ->and($event->fresh()->photo)->toBe('attendance_photos/event.jpg')
        ->and($event->fresh()->occurred_at->format('Y-m-d H:i'))->toBe('2026-10-19 17:00');
});

it('denies approval access to employees', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $event = AttendanceEvent::factory()->create(['is_flexible' => true]);

    $this->actingAs($employee, 'web')->get(route('hr.approval_attendance.index'))->assertForbidden();
    $this->post(route('hr.approval_attendance.approve', $event))->assertForbidden();
    expect($event->fresh()->approval_status)->toBe('PENDING');
});
