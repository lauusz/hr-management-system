<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\User;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

it('filters pending approval events by Clock In and Clock Out type', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    AttendanceEvent::factory()->forUser($employee)->create(['event_type' => AttendanceEvent::CLOCK_IN]);
    AttendanceEvent::factory()->forUser($employee)->clockOut()->create();

    $response = $this->actingAs($hrd, 'web')->get(route('hr.approval_attendance.index', ['event_type' => 'CLOCK_OUT']));

    expect($response->viewData('pendingAttendances'))->toHaveCount(1)
        ->and($response->viewData('pendingAttendances')->sole()->event_type)->toBe(AttendanceEvent::CLOCK_OUT);
});

it('approves one pending event without changing its time or evidence', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->create([
        'occurred_at' => '2026-10-13 08:00:00',
        'photo' => 'attendance_photos/event.jpg',
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
    $first = AttendanceEvent::factory()->create();
    $second = AttendanceEvent::factory()->clockOut()->create();
    $decided = AttendanceEvent::factory()->create(['approval_status' => 'REJECTED']);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.approval_attendance.bulk_approve'), ['attendance_ids' => [$first->id, $second->id, $decided->id]])
        ->assertRedirect();

    expect($first->fresh()->approval_status)->toBe('APPROVED')
        ->and($second->fresh()->approval_status)->toBe('APPROVED')
        ->and($decided->fresh()->approval_status)->toBe('REJECTED');
});

it('moves only a pending event between Clock In and Clock Out', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $event = AttendanceEvent::factory()->clockOut()->create(['approval_status' => 'PENDING']);

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
