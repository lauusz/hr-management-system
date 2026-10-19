<?php

use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\InstallsAttendanceEventSchema;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)->in('Feature');

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

function createAttendanceEventShift(User $user, AttendanceLocation $location): EmployeeShift
{
    $shift = Shift::factory()->create(['is_active' => true]);

    foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
        ShiftDay::factory()->create([
            'shift_id' => $shift->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_holiday' => false,
        ]);
    }

    return EmployeeShift::factory()->create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ]);
}

function eventRequestData(string $photo = 'event.jpg'): array
{
    return [
        'photo' => UploadedFile::fake()->image($photo, 800, 600),
        'lat' => -6.200000,
        'lng' => 106.816666,
    ];
}

describe('attendance events', function () {
    beforeEach(function () {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::parse('2031-04-21 17:15:00', 'Asia/Jakarta'));

        $this->user = User::factory()->create();
        $this->location = AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);
        createAttendanceEventShift($this->user, $this->location);
        actingAs($this->user, 'web');
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    it('records Clock Out without requiring a Clock In', function () {
        $this->post(route('attendance.clockOut'), eventRequestData('clock-out.jpg'))
            ->assertOk()
            ->assertJson(['message' => 'Presensi keluar berhasil.']);

        $event = AttendanceEvent::query()->where('user_id', $this->user->id)->sole();

        expect($event->event_type)->toBe(AttendanceEvent::CLOCK_OUT)
            ->and($event->type)->toBe('WFO')
            ->and($event->location_id)->toBe($this->location->id)
            ->and($event->occurred_at->format('Y-m-d H:i'))->toBe('2031-04-21 17:15');
    });

    it('allows Clock In after Clock Out on the same date', function () {
        $this->post(route('attendance.clockOut'), eventRequestData('clock-out.jpg'))->assertOk();

        Carbon::setTestNow(Carbon::parse('2031-04-21 17:30:00', 'Asia/Jakarta'));

        $this->post(route('attendance.clockIn'), eventRequestData('clock-in.jpg'))->assertOk();

        expect(AttendanceEvent::query()->where('user_id', $this->user->id)->pluck('event_type')->all())
            ->toContain(AttendanceEvent::CLOCK_IN, AttendanceEvent::CLOCK_OUT);
    });

    it('rejects any new attendance event within fifteen minutes of the last event', function () {
        $this->post(route('attendance.clockOut'), eventRequestData('clock-out.jpg'))->assertOk();

        Carbon::setTestNow(Carbon::parse('2031-04-21 17:29:00', 'Asia/Jakarta'));

        $this->post(route('attendance.clockIn'), eventRequestData('clock-in.jpg'))
            ->assertStatus(429)
            ->assertJson([
                'code' => 'attendance_cooldown',
                'message' => 'Anda sudah Clock Out pada pukul 17:15.',
            ]);

        expect(AttendanceEvent::query()->where('user_id', $this->user->id)->count())->toBe(1);
    });

    it('allows a new attendance event exactly fifteen minutes after the last event', function () {
        $this->post(route('attendance.clockOut'), eventRequestData('clock-out.jpg'))->assertOk();

        Carbon::setTestNow(Carbon::parse('2031-04-21 17:30:00', 'Asia/Jakarta'));

        $this->post(route('attendance.clockIn'), eventRequestData('clock-in.jpg'))->assertOk();

        expect(AttendanceEvent::query()->where('user_id', $this->user->id)->count())->toBe(2);
    });

    it('shows the last attendance status in a modal while the cooldown is active', function () {
        AttendanceEvent::factory()->forUser($this->user)->create([
            'event_type' => AttendanceEvent::CLOCK_IN,
            'occurred_at' => now()->subMinute(),
        ]);

        $this->get(route('attendance.dashboard'))
            ->assertOk()
            ->assertSee('attendance-cooldown-modal', false)
            ->assertSee('Anda sudah Clock In pada pukul 17:14.', false);
    });

    it('does not mutate a previous-day event when recording a new event', function () {
        $previous = AttendanceEvent::factory()->forUser($this->user)->clockOut()->create([
            'occurred_at' => '2031-04-20 02:00:00',
        ]);

        $this->post(route('attendance.clockIn'), eventRequestData('clock-in.jpg'))->assertOk();

        expect($previous->fresh()->occurred_at->format('Y-m-d H:i'))->toBe('2031-04-20 02:00')
            ->and(AttendanceEvent::query()->where('user_id', $this->user->id)->count())->toBe(2);
    });

    it('preserves WFO radius validation and does not store an invalid event', function () {
        $this->post(route('attendance.clockOut'), [
            ...eventRequestData('outside.jpg'),
            'lat' => -6.205000,
        ])->assertStatus(400)
            ->assertJson(['message' => 'Anda harus berada di kantor untuk melakukan presensi keluar (556 m).']);

        expect(AttendanceEvent::query()->count())->toBe(0);
    });

    it('requires a remote purpose for remote Clock In and stores no event when it is absent', function () {
        $this->postJson(route('remote-attendance.clockIn'), eventRequestData('remote.jpg'))
            ->assertStatus(422);

        expect(AttendanceEvent::query()->count())->toBe(0);
    });

    it('records remote Clock Out without a remote Clock In', function () {
        $this->post(route('remote-attendance.clockOut'), [
            ...eventRequestData('remote-out.jpg'),
            'lat' => -7.500000,
            'lng' => 110.000000,
        ])->assertOk();

        expect(AttendanceEvent::query()->where('user_id', $this->user->id)->sole()->type)->toBe('DINAS_LUAR');
    });

    it('keeps both attendance dashboard actions available after an event', function () {
        AttendanceEvent::factory()->forUser($this->user)->create(['occurred_at' => now()]);

        $this->get(route('attendance.dashboard'))
            ->assertOk()
            ->assertSee(route('attendance.clockIn.form'), false)
            ->assertSee(route('attendance.clockOut.form'), false);
    });

    it('shows fixed calm green and red attendance actions on the dashboard', function () {
        $this->get(route('attendance.dashboard'))
            ->assertOk()
            ->assertSee('attendance-action-dock--sticky', false)
            ->assertSee('attendance-action--clock-in', false)
            ->assertSee('attendance-action--clock-out', false);
    });
});
