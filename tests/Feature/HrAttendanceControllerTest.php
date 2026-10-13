<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

it('uses the updated attendance recap columns', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $today = now()->startOfDay();

    Attendance::factory()->forUser($employee)->today()->create([
        'normal_start_time' => '06:45:00',
        'normal_end_time' => '15:45:00',
        'clock_in_at' => $today->copy()->setTime(8, 0),
        'clock_out_at' => $today->copy()->setTime(17, 0),
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index'))
        ->assertOk()
        ->assertSee('<th>Clock In</th>', false)
        ->assertSee('<th>Clock Out</th>', false)
        ->assertDontSee('<th>Check In</th>', false)
        ->assertDontSee('<th>Check Out</th>', false)
        ->assertDontSee('<th>Jam Kerja</th>', false)
        ->assertDontSee('<th>Status Kelengkapan</th>', false)
        ->assertDontSee('06:45');
});

it('shows a work-date range only for overnight attendance in the recap', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $normalEmployee = User::factory()->create();
    $overnightEmployee = User::factory()->create();
    $workDate = now()->startOfDay();
    $normalDateLabel = $workDate->copy()->locale('id')->translatedFormat('j M Y');
    $overnightDateLabel = $workDate->copy()->locale('id')->translatedFormat('j M').' - '.$workDate->copy()->addDay()->locale('id')->translatedFormat('j M Y');

    Attendance::factory()->forUser($normalEmployee)->create([
        'date' => $workDate->toDateString(),
        'normal_start_time' => '08:00:00',
        'normal_end_time' => '17:00:00',
        'clock_in_at' => $workDate->copy()->setTime(8, 30),
        'clock_out_at' => $workDate->copy()->setTime(17, 0),
    ]);
    Attendance::factory()->forUser($overnightEmployee)->create([
        'date' => $workDate->toDateString(),
        'normal_start_time' => '20:00:00',
        'normal_end_time' => '06:00:00',
        'clock_in_at' => $workDate->copy()->setTime(20, 0),
        'clock_out_at' => $workDate->copy()->addDay()->setTime(6, 0),
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'date_start' => $workDate->copy()->subDay()->toDateString(),
            'date_end' => $workDate->copy()->addDay()->toDateString(),
        ]));

    expect($response->viewData('items')->total())->toBe(2);

    $response
        ->assertOk()
        ->assertSee('<th>Tanggal</th>', false)
        ->assertSee($normalDateLabel)
        ->assertSee($overnightDateLabel)
        ->assertSee('08:30')
        ->assertSee('17:00')
        ->assertSee('20:00')
        ->assertSee('06:00');
});

it('uses Lihat for attendance location buttons', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();

    Attendance::factory()->forUser($employee)->today()->create([
        'clock_in_lat' => -6.200000,
        'clock_in_lng' => 106.816666,
        'clock_out_lat' => -6.210000,
        'clock_out_lng' => 106.826666,
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => now()->subDay()->toDateString(),
        'date_end' => now()->addDay()->toDateString(),
    ]));
    $response->assertOk()
        ->assertSee('data-attendance-location="clock-in"', false)
        ->assertSee('data-attendance-location="clock-out"', false);

    preg_match_all(
        '/data-attendance-location="[^"]+"[^>]*>\s*([^<]+?)\s*<\/button>/',
        $response->getContent(),
        $matches,
    );

    expect($matches[1])->toBe(['Lihat', 'Lihat']);
});

it('renders the attendance live search filter contract', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index'));

    $response->assertOk();

    $dom = new DOMDocument;
    $previousState = libxml_use_internal_errors(true);
    $dom->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previousState);
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//*[@data-attendance-live-search and @aria-label="Cari nama karyawan"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-attendance-results and @aria-live="polite" and @aria-busy="false"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-attendance-pagination]')->length)->toBe(1)
        ->and($xpath->query('//button[contains(concat(" ", normalize-space(@class), " "), " attendance-btn-toggle-filter ")]')->length)->toBe(1);
});

it('attendance search ignores dots and spaces in employee names', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $matchingEmployee = User::factory()->create(['name' => 'MOH. AINUL YAQIN']);
    $otherEmployee = User::factory()->create(['name' => 'MOHAMMAD FAJAR']);

    Attendance::factory()->forUser($matchingEmployee)->today()->create();
    Attendance::factory()->forUser($otherEmployee)->today()->create();

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'q' => 'mohainul',
            'date_start' => now()->subDay()->toDateString(),
            'date_end' => now()->addDay()->toDateString(),
        ]));

    $response->assertOk();
    expect($response->viewData('items')->total())->toBe(1)
        ->and($response->viewData('items')->first()->user_id)->toBe($matchingEmployee->id);
});

it('filters attendance recap by overnight schedule snapshot', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $overnightEmployee = User::factory()->create();
    $regularEmployee = User::factory()->create();
    $workDate = now()->startOfDay();

    Attendance::factory()->forUser($overnightEmployee)->create([
        'date' => $workDate->toDateString(),
        'clock_in_at' => $workDate->copy()->setTime(22, 0),
        'normal_start_time' => '22:00:00',
        'normal_end_time' => '06:00:00',
    ]);
    Attendance::factory()->forUser($regularEmployee)->create([
        'date' => $workDate->toDateString(),
        'clock_in_at' => $workDate->copy()->setTime(8, 0),
        'normal_start_time' => '08:00:00',
        'normal_end_time' => '17:00:00',
    ]);

    $overnightResponse = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'date_start' => $workDate->copy()->subDay()->toDateString(),
            'date_end' => $workDate->copy()->addDay()->toDateString(),
            'shift_type' => 'overnight',
        ]));

    $overnightResponse->assertOk()
        ->assertSee('Jenis Shift')
        ->assertSee('Bukan Lintas Hari');
    expect($overnightResponse->viewData('items')->total())->toBe(1)
        ->and($overnightResponse->viewData('items')->first()->user_id)->toBe($overnightEmployee->id);

    $regularResponse = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'date_start' => $workDate->copy()->subDay()->toDateString(),
            'date_end' => $workDate->copy()->addDay()->toDateString(),
            'shift_type' => 'regular',
        ]));

    expect($regularResponse->viewData('items')->total())->toBe(1)
        ->and($regularResponse->viewData('items')->first()->user_id)->toBe($regularEmployee->id);
});

it('opens attendance coordinates in the location modal instead of a new maps tab', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();

    Attendance::factory()->forUser($employee)->today()->create([
        'clock_in_lat' => -6.200000,
        'clock_in_lng' => 106.816666,
        'clock_out_lat' => -6.210000,
        'clock_out_lng' => 106.826666,
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => now()->subDay()->toDateString(),
        'date_end' => now()->addDay()->toDateString(),
    ]));

    expect($response->viewData('items')->total())->toBe(1);

    $response->assertOk()
        ->assertSee($employee->name)
        ->assertSee('data-attendance-location="clock-in"', false)
        ->assertSee('data-attendance-location="clock-out"', false)
        ->assertSee('id="attendance-location-map"', false)
        ->assertDontSee('https://www.google.com/maps/search/?api=1', false);
});

it('reinitializes location buttons after the attendance search replaces the results', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index'))
        ->assertOk()
        ->assertSee('function initializeAttendanceLocationButtons()', false)
        ->assertSee('initializeAttendanceLocationButtons();', false);
});

it('filters direct clock out records as missed clock in', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $otherEmployee = User::factory()->create();

    $missingClockIn = Attendance::factory()->forUser($employee)->today()->create([
        'clock_in_at' => null,
        'clock_out_at' => now(),
        'status' => 'TIDAK CLOCK IN',
        'completion_status' => 'MISSED_CLOCK_IN',
    ]);
    Attendance::factory()->forUser($otherEmployee)->today()->create([
        'completion_status' => Attendance::COMPLETION_CLOSED,
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'completion_status' => 'MISSED_CLOCK_IN',
        'date_start' => now()->subDay()->toDateString(),
        'date_end' => now()->addDay()->toDateString(),
    ]));

    $response->assertOk()
        ->assertSee('Belum Clock In');
    expect($response->viewData('items')->total())->toBe(1)
        ->and($response->viewData('items')->first()->id)->toBe($missingClockIn->id);
});
