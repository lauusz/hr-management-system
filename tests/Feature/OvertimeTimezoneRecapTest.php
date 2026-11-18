<?php

use App\Http\Controllers\HrOvertimeController;
use App\Models\AttendanceEvent;
use App\Models\OvertimeRequest;
use App\Services\OvertimeTimezone;
use Carbon\Carbon;

// PRD-024 v0.4: jam pengajuan adalah jam lokal zona lembur; absensi dibandingkan sebagai instant absolut.

function overtimeRecap(string $date, string $start, string $end, ?object $attendance): array
{
    $overtime = new OvertimeRequest;
    $overtime->setRawAttributes(['overtime_date' => $date, 'start_time' => $start.':00', 'end_time' => $end.':00']);

    $method = new ReflectionMethod(HrOvertimeController::class, 'computeRecapData');
    $method->setAccessible(true);

    return $method->invoke(new HrOvertimeController, $overtime, $attendance);
}

function overtimeAttendance(string $clockOutWib, ?bool $isWita): object
{
    $out = Carbon::parse($clockOutWib, 'Asia/Jakarta')->timezone($isWita ? 'Asia/Makassar' : 'Asia/Jakarta');

    return (object) ['clock_in_at' => null, 'clock_out_at' => $out, 'event_is_wita' => $isWita];
}

it('matches a WITA request when the local clock out passes the requested end', function () {
    // Clock Out 18:13:45 WIB = 19:13:45 WITA, pengajuan 17:00-19:00 WITA.
    $recap = overtimeRecap('2026-11-10', '17:00', '19:00', overtimeAttendance('2026-11-10 18:13:45', true));

    expect($recap['status_slug'])->toBe('match')->and($recap['zone_label'])->toBe('WITA');
});

it('matches 16:30 WITA with clock out 16:31:50 WITA and keeps 18:55:24 WITA short of 19:00', function () {
    expect(overtimeRecap('2026-11-07', '14:00', '16:30', overtimeAttendance('2026-11-07 15:31:50', true))['status_slug'])->toBe('match');
    expect(overtimeRecap('2026-11-05', '17:00', '19:00', overtimeAttendance('2026-11-05 17:55:24', true))['status_slug'])->toBe('short');
});

it('does not shift WIB requests', function () {
    expect(overtimeRecap('2026-11-10', '17:00', '19:00', overtimeAttendance('2026-11-10 19:13:45', false))['status_slug'])->toBe('match');
    expect(overtimeRecap('2026-11-10', '17:00', '19:00', overtimeAttendance('2026-11-10 18:55:00', false))['status_slug'])->toBe('short');
});

it('treats an end time earlier than the start time as the next day', function () {
    $recap = overtimeRecap('2026-11-10', '20:00', '02:00', overtimeAttendance('2026-11-11 01:30:00', true));

    expect($recap['status_slug'])->toBe('short')->and($recap['requested_end']->format('Y-m-d H:i'))->toBe('2026-11-11 02:00');
});

it('does not guess the zone when there is no evidence', function () {
    $recap = overtimeRecap('2026-11-10', '17:00', '19:00', overtimeAttendance('2026-11-10 19:13:45', null));

    expect($recap['status_slug'])->toBe('unknown_zone')->and($recap['zone_label'])->toBeNull();
});

it('reads the zone of legacy requests only from a single consistent attendance evidence', function () {
    $wita = new AttendanceEvent(['is_wita' => true]);
    $wib = new AttendanceEvent(['is_wita' => false]);

    expect(OvertimeTimezone::singleEvidence([$wita, $wita]))->toBeTrue()
        ->and(OvertimeTimezone::singleEvidence([$wib]))->toBeFalse()
        ->and(OvertimeTimezone::singleEvidence([$wita, $wib]))->toBeNull()
        ->and(OvertimeTimezone::singleEvidence([new AttendanceEvent]))->toBeNull();
});
