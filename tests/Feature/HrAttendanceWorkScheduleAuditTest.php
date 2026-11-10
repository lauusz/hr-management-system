<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\InstallsAttendanceEventSchema;
use Tests\Support\InstallsFlexibleScheduleSchema;

uses(InstallsAttendanceEventSchema::class, InstallsFlexibleScheduleSchema::class);

beforeEach(function () {
    // Fixture SQL manual hanya dipasang pada SQLite sekali pakai milik harness.
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toContain('testing-disposable-');
    $this->installAttendanceEventSchema();
    $this->installFlexibleScheduleSchema();
    if (! Schema::hasColumn('shifts', 'is_wita')) {
        Schema::table('shifts', fn (Blueprint $table) => $table->boolean('is_wita')->default(false));
    }
    if (! Schema::hasTable('employee_shift_locations')) {
        Schema::create('employee_shift_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_shift_id')->constrained('employee_shifts')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('attendance_locations')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['employee_shift_id', 'location_id']);
        });
    }
    foreach (['is_wita', 'schedule_is_holiday', 'shift_name_snapshot'] as $column) {
        if (! Schema::hasColumn('attendance_events', $column)) {
            Schema::table('attendance_events', function (Blueprint $table) use ($column) {
                $column === 'shift_name_snapshot' ? $table->string($column)->nullable() : $table->boolean($column)->nullable();
            });
        }
    }
    if (! Schema::hasTable('employee_work_schedule_groups')) {
        Schema::create('employee_work_schedule_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    if (! Schema::hasTable('employee_work_schedules')) {
        Schema::create('employee_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->restrictOnDelete();
            $table->string('shift_name');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_wita')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('work_schedule_group_id')->nullable()->constrained('employee_work_schedule_groups')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'work_date']);
        });
    }
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-11-09 18:01:00', 'Asia/Jakarta'));
    $this->hr = User::factory()->create(['role' => UserRole::HRD]);
    $this->employee = User::factory()->create(['name' => 'MAWAR AUDIT']);
    $this->location = AttendanceLocation::factory()->create(['latitude' => -6.2, 'longitude' => 106.816666, 'radius_meters' => 100]);
    $this->regular = Shift::factory()->create(['is_wita' => false]);
    $this->night = Shift::factory()->create(['name' => 'Malam Audit', 'is_wita' => false]);
    foreach (range(1, 7) as $day) {
        ShiftDay::factory()->create(['shift_id' => $this->regular->id, 'day_of_week' => $day, 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'is_holiday' => false, 'is_overnight' => false]);
        ShiftDay::factory()->create(['shift_id' => $this->night->id, 'day_of_week' => $day, 'start_time' => '18:00:00', 'end_time' => '06:00:00', 'is_holiday' => false, 'is_overnight' => true]);
    }
    EmployeeShift::factory()->create(['user_id' => $this->employee->id, 'shift_id' => $this->regular->id, 'location_id' => $this->location->id]);
});

afterEach(fn () => Carbon::setTestNow());

function auditPhotoPayload(): array
{
    return ['photo' => UploadedFile::fake()->image('audit.jpg', 100, 100), 'lat' => -6.2, 'lng' => 106.816666, 'accuracy' => 10];
}

function auditSpecialSchedule(object $test, array $overrides = []): WorkSchedule
{
    $schedule = WorkSchedule::create(array_merge([
        'user_id' => $test->employee->id, 'work_date' => '2026-11-09',
        'shift_id' => $test->night->id, 'shift_name' => 'Malam Audit',
        'start_time' => '18:00:00', 'end_time' => '06:00:00',
        'is_holiday' => false, 'is_overnight' => true, 'is_wita' => false,
    ], $overrides));
    // MySQL DATE loại bỏ phần giờ; SQLite fixture cần chuẩn hóa tương đương.
    DB::table('employee_work_schedules')->where('id', $schedule->id)->update(['work_date' => $schedule->work_date->toDateString()]);

    return $schedule;
}

function auditHrResponse(object $test, string $start = '2026-11-09', ?string $end = null)
{
    return $test->actingAs($test->hr)->get(route('hr.attendances.index', ['date_start' => $start, 'date_end' => $end ?? $start]));
}

it('audit: saves a named schedule group then records and displays its overnight event snapshot', function () {
    $this->actingAs($this->hr)->post(route('hr.work-schedules.store'), [
        'month' => '2026-11', 'group_name' => 'Tim Mawar', 'users' => [$this->employee->id],
        'ranges' => [['start' => '2026-11-09', 'end' => '2026-11-09', 'shift' => (string) $this->night->id]],
    ])->assertSessionHasNoErrors()->assertRedirect();
    $planned = WorkSchedule::where('user_id', $this->employee->id)->sole();
    expect($planned->group->name)->toBe('Tim Mawar');
    DB::table('employee_work_schedules')->where('id', $planned->id)->update(['work_date' => $planned->work_date->toDateString()]);
    $this->actingAs($this->employee)->postJson(route('attendance.clockIn'), auditPhotoPayload())->assertOk();
    $in = AttendanceEvent::where('user_id', $this->employee->id)->sole();
    expect($in->shift_start_time)->toBe('18:00:00')->and($in->work_date->toDateString())->toBe('2026-11-09');
    // Master dan rencana berubah setelah Clock In; event lama wajib tetap utuh.
    $this->night->days()->update(['start_time' => '19:00:00', 'end_time' => '07:00:00']);
    $planned->update(['start_time' => '20:00:00', 'end_time' => '08:00:00']);
    Carbon::setTestNow(Carbon::parse('2026-11-10 06:05:00', 'Asia/Jakarta'));
    $this->actingAs($this->employee)->postJson(route('attendance.clockOut'), auditPhotoPayload())->assertOk();
    $out = AttendanceEvent::where('user_id', $this->employee->id)->where('event_type', 'CLOCK_OUT')->sole();
    expect($out->work_date->toDateString())->toBe('2026-11-09')
        ->and($out->shift_start_time)->toBe('18:00:00')->and($out->shift_end_time)->toBe('06:00:00');
    $response = auditHrResponse($this)->assertOk();
    expect($response->viewData('items'))->toHaveCount(1)
        ->and($response->viewData('items')->sole()->work_schedule)->toBe('18:00–06:00 (lintas hari)');
});

it('audit: a future special schedule leaves todays regular schedule unchanged', function () {
    auditSpecialSchedule($this);
    Carbon::setTestNow(Carbon::parse('2026-11-06 08:30:00', 'Asia/Jakarta'));
    $this->actingAs($this->employee)->postJson(route('attendance.clockIn'), auditPhotoPayload())->assertOk();
    $event = AttendanceEvent::where('user_id', $this->employee->id)->sole();
    expect($event->shift_start_time)->toBe('08:00:00')->and($event->attendance_shift_id)->toBe($this->regular->id);
});

it('audit: regular attendance does not require a group', function () {
    $this->actingAs($this->employee)->postJson(route('attendance.clockIn'), auditPhotoPayload())->assertOk();
    expect(AttendanceEvent::where('user_id', $this->employee->id)->sole()->shift_start_time)->toBe('08:00:00');
});

it('audit: explicit holiday persists as holiday and is displayed without regular hours', function () {
    auditSpecialSchedule($this, ['shift_id' => null, 'shift_name' => 'Libur', 'start_time' => null, 'end_time' => null, 'is_holiday' => true, 'is_overnight' => false]);
    $this->actingAs($this->employee)->postJson(route('attendance.clockIn'), auditPhotoPayload())->assertOk();
    $event = AttendanceEvent::where('user_id', $this->employee->id)->sole();
    expect($event->schedule_is_holiday)->toBeTrue()->and($event->shift_start_time)->toBeNull();
    expect(auditHrResponse($this)->assertOk()->viewData('items')->sole()->work_schedule)->toBe('Libur');
});

it('audit: WITA overnight clock out stays on the clock in work date', function () {
    auditSpecialSchedule($this, ['is_wita' => true]);
    $this->actingAs($this->employee)->postJson(route('attendance.clockIn'), auditPhotoPayload())->assertOk();
    Carbon::setTestNow(Carbon::parse('2026-11-10 05:05:00', 'Asia/Jakarta'));
    $this->actingAs($this->employee)->postJson(route('attendance.clockOut'), auditPhotoPayload())->assertOk();
    $events = AttendanceEvent::where('user_id', $this->employee->id)->get();
    expect($events->every(fn ($event) => $event->is_wita && $event->work_date->toDateString() === '2026-11-09'))->toBeTrue();
    expect(auditHrResponse($this)->assertOk()->viewData('items'))->toHaveCount(1);
});

it('audit: HR filter supports reversed ranges and normalized employee search', function () {
    AttendanceEvent::factory()->forUser($this->employee)->create(['occurred_at' => '2026-11-09 08:30:00', 'work_date' => '2026-11-09', 'is_wita' => false, 'is_overnight' => false, 'shift_start_time' => '08:00:00', 'shift_end_time' => '16:00:00']);
    $this->actingAs($this->hr)->get(route('hr.attendances.index', ['date_start' => '2026-11-10', 'date_end' => '2026-11-08', 'q' => 'mawaraudit']))->assertOk()->assertSee('MAWAR AUDIT');
});

it('audit: pending event correction changes type only while approved event stays immutable', function () {
    $event = AttendanceEvent::factory()->forUser($this->employee)->create(['shift_start_time' => '08:00:00', 'shift_end_time' => '16:00:00']);
    $this->actingAs($this->hr)->postJson(route('hr.attendances.move_event_type', $event))->assertOk();
    expect($event->fresh()->event_type)->toBe('CLOCK_OUT')->and($event->fresh()->shift_start_time)->toBe('08:00:00');
    $event->update(['approval_status' => 'APPROVED']);
    $this->postJson(route('hr.attendances.move_event_type', $event))->assertStatus(422);
    expect($event->fresh()->event_type)->toBe('CLOCK_OUT');
});

it('audit: denies HR attendance access to regular employees and guests', function () {
    $this->get(route('hr.attendances.index'))->assertRedirect(route('login'));
    $this->actingAs($this->employee)->get(route('hr.attendances.index'))->assertForbidden();
});

it('audit: incomplete legacy snapshot must not fabricate historical hours from the current master', function () {
    AttendanceEvent::factory()->forUser($this->employee)->create(['occurred_at' => '2026-11-09 08:30:00', 'work_date' => '2026-11-09', 'is_wita' => false, 'is_overnight' => false]);
    $response = auditHrResponse($this)->assertOk();
    // Acuan yang hilang harus tetap tidak diketahui, bukan jam master saat ini.
    expect($response->viewData('items')->sole()->work_schedule)->not->toBe('08:00–16:00');
});

it('audit: snapshot work date must remain authoritative for a non overnight event', function () {
    AttendanceEvent::factory()->forUser($this->employee)->create(['occurred_at' => '2026-11-10 00:05:00', 'work_date' => '2026-11-09', 'is_wita' => false, 'is_overnight' => false, 'shift_start_time' => '08:00:00', 'shift_end_time' => '16:00:00']);
    expect(auditHrResponse($this)->assertOk()->viewData('items'))->toHaveCount(1);
});

it('audit: a complete historical snapshot survives subsequent changes to regular master hours', function () {
    AttendanceEvent::factory()->forUser($this->employee)->create(['occurred_at' => '2026-11-09 08:30:00', 'work_date' => '2026-11-09', 'is_wita' => false, 'is_overnight' => false, 'shift_start_time' => '08:00:00', 'shift_end_time' => '16:00:00', 'attendance_location_scope' => 'Lokasi historis']);
    $this->regular->days()->update(['start_time' => '10:00:00', 'end_time' => '19:00:00']);
    $session = auditHrResponse($this)->assertOk()->viewData('items')->sole();
    expect($session->work_schedule)->toBe('08:00–16:00')->and($session->attendance_location_scope)->toBe('Lokasi historis');
});

it('audit: rejects invalid date filters with validation instead of server error', function () {
    $this->actingAs($this->hr)->getJson(route('hr.attendances.index', ['date_start' => 'not-a-date', 'date_end' => '2026-11-09']))->assertStatus(422);
});

it('audit: team attendance includes only explicitly assigned employees', function () {
    if (! Schema::hasTable('overtime_approver_assignments')) {
        Schema::create('overtime_approver_assignments', function (Blueprint $table) {
            $table->foreignId('employee_id')->primary()->constrained('users');
            $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
    if (! Schema::hasTable('attendance_view_access')) {
        Schema::create('attendance_view_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users');
            $table->foreignId('employee_id')->constrained('users');
        });
    }
    $viewer = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    DB::table('attendance_view_access')->insert(['viewer_id' => $viewer->id, 'employee_id' => $this->employee->id]);
    $other = User::factory()->create(['name' => 'TIDAK BOLEH TERLIHAT']);
    foreach ([$this->employee, $other] as $user) {
        AttendanceEvent::factory()->forUser($user)->create(['occurred_at' => '2026-11-09 08:30:00', 'is_wita' => false, 'is_overnight' => false, 'shift_start_time' => '08:00:00', 'shift_end_time' => '16:00:00']);
    }
    $response = $this->actingAs($viewer)->get(route('supervisor.attendances.index', ['date_start' => '2026-11-09', 'date_end' => '2026-11-09']))->assertOk();
    expect($response->viewData('items')->pluck('user.id')->all())->toBe([$this->employee->id]);
});

it('audit: monthly lateness uses the event snapshot within the 26 to 25 cutoff and exempts approved late leave', function () {
    $event = AttendanceEvent::factory()->forUser($this->employee)->create([
        'occurred_at' => '2026-11-09 09:01:00', 'work_date' => '2026-11-09',
        'is_wita' => false, 'is_overnight' => false, 'shift_start_time' => '09:00:00', 'shift_end_time' => '17:00:00', 'is_flexible' => false,
    ]);
    DB::table('attendance_events')->where('id', $event->id)->update(['work_date' => '2026-11-09']);
    $this->regular->days()->update(['start_time' => '10:00:00']);
    $service = new \App\Services\Attendance\MonthlyAttendanceReportService;
    $start = Carbon::parse('2026-10-26');
    $end = Carbon::parse('2026-11-25');
    $result = $service->calculate([$this->employee->id], $start, $end);
    expect($result[$this->employee->id]['TELAT']['2026-11-09']['start'])->toBe('09:00:00');
    $leave = \App\Models\LeaveRequest::factory()->create(['user_id' => $this->employee->id, 'type' => 'IZIN_TELAT', 'status' => 'APPROVED', 'start_date' => '2026-11-09', 'end_date' => '2026-11-09']);
    DB::table('leave_request_days')->insert(['leave_request_id' => $leave->id, 'leave_date' => '2026-11-09']);
    $result = $service->calculate([$this->employee->id], $start, $end);
    expect($result[$this->employee->id]['TELAT'] ?? [])->toBeEmpty()
        ->and($result[$this->employee->id]['IZIN_TELAT'])->toHaveKey('2026-11-09');
});
