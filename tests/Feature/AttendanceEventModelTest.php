<?php

use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\User;
use Tests\Support\InstallsAttendanceEventSchema;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

it('records one independent clock event with its own evidence and relations', function () {
    $user = User::factory()->create();
    $location = AttendanceLocation::factory()->create();

    $event = AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'event_type' => AttendanceEvent::CLOCK_OUT,
        'type' => 'WFO',
        'occurred_at' => '2026-08-11 02:00:00',
        'photo' => 'attendance_photos/clock-out.jpg',
        'latitude' => -7.220326,
        'longitude' => 112.729222,
        'distance_m' => 26,
        'approval_status' => 'PENDING',
    ]);

    expect($event->event_type)->toBe(AttendanceEvent::CLOCK_OUT)
        ->and($event->occurred_at->format('Y-m-d H:i:s'))->toBe('2026-08-11 02:00:00')
        ->and($event->photo)->toBe('attendance_photos/clock-out.jpg')
        ->and($event->user->id)->toBe($user->id)
        ->and($event->location->id)->toBe($location->id);
});

it('rejects a second historical event from the same legacy side', function () {
    $user = User::factory()->create();

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => '2026-08-10 18:00:00',
        'legacy_attendance_id' => 87,
        'legacy_side' => AttendanceEvent::CLOCK_IN,
        'approval_status' => 'APPROVED',
    ]);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => '2026-08-10 18:00:00',
        'legacy_attendance_id' => 87,
        'legacy_side' => AttendanceEvent::CLOCK_IN,
        'approval_status' => 'APPROVED',
    ]);
})->throws(Illuminate\Database\UniqueConstraintViolationException::class);

it('exposes a user event history without using attendance sessions', function () {
    $user = User::factory()->create();
    $event = AttendanceEvent::factory()->forUser($user)->clockOut()->create();

    expect($user->attendanceEvents->modelKeys())->toBe([$event->id]);
});
