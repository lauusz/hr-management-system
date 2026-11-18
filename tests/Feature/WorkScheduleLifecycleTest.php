<?php

use App\Enums\UserRole;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Fixture hanya dipasang pada SQLite sekali pakai milik harness; mencerminkan SQL manual 2026_11_17.
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toContain('testing-disposable-');
    Schema::dropIfExists('employee_work_schedule_changes');
    Schema::dropIfExists('employee_work_schedules');
    Schema::dropIfExists('employee_work_schedule_groups');
    Schema::create('employee_work_schedule_groups', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('created_by')->nullable();
        $table->foreignId('updated_by')->nullable();
        $table->timestamps();
    });
    Schema::create('employee_work_schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('work_date');
        $table->foreignId('shift_id')->nullable();
        $table->string('shift_name');
        $table->time('start_time')->nullable();
        $table->time('end_time')->nullable();
        $table->boolean('is_holiday')->default(false);
        $table->boolean('is_overnight')->default(false);
        $table->boolean('is_wita')->default(false);
        $table->tinyInteger('is_active')->nullable()->default(1);
        $table->timestamp('deactivated_at')->nullable();
        $table->foreignId('deactivated_by')->nullable();
        $table->foreignId('created_by')->nullable();
        $table->foreignId('updated_by')->nullable();
        $table->foreignId('work_schedule_group_id')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'work_date', 'is_active']);
    });
    Schema::create('employee_work_schedule_changes', function (Blueprint $table) {
        $table->id();
        $table->char('batch_id', 36);
        $table->unsignedBigInteger('work_schedule_id')->nullable();
        $table->unsignedBigInteger('work_schedule_group_id')->nullable();
        $table->string('action', 20);
        $table->longText('before_data')->nullable();
        $table->longText('after_data')->nullable();
        $table->unsignedBigInteger('changed_by')->nullable();
        $table->timestamp('created_at')->nullable();
    });
    foreach (['is_wita'] as $column) {
        if (! Schema::hasColumn('shifts', $column)) {
            Schema::table('shifts', fn (Blueprint $table) => $table->boolean($column)->default(false));
        }
    }
    $this->hr = User::factory()->create(['role' => UserRole::HRD]);
    $this->employee = User::factory()->create(['name' => 'WIDI TEST']);
    $this->morning = Shift::factory()->create(['name' => 'Pagi Test', 'is_wita' => false, 'is_active' => true]);
    $this->night = Shift::factory()->create(['name' => 'Malam Test', 'is_wita' => false, 'is_active' => true]);
    foreach (range(1, 7) as $day) {
        ShiftDay::factory()->create(['shift_id' => $this->morning->id, 'day_of_week' => $day, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'is_overnight' => false]);
        ShiftDay::factory()->create(['shift_id' => $this->night->id, 'day_of_week' => $day, 'start_time' => '19:00:00', 'end_time' => '03:00:00', 'is_holiday' => false, 'is_overnight' => true]);
    }
});

function lifecycleStore(object $test, Shift $shift, string $group = 'Kelompok Uji'): void
{
    $test->actingAs($test->hr)->post(route('hr.work-schedules.store'), [
        'month' => '2026-11', 'group_name' => $group, 'users' => [$test->employee->id],
        'ranges' => [['start' => '2026-11-16', 'end' => '2026-11-17', 'shift' => (string) $shift->id]],
    ])->assertSessionHasNoErrors()->assertRedirect();
}

function lifecycleFingerprint(object $test, array $query): string
{
    return $test->actingAs($test->hr)->get(route('hr.work-schedules.edit', $query))->assertOk()->viewData('fingerprint');
}

it('lifecycle: deactivate keeps data, creates no false conflict, and replacement can be stored for the same employee and date', function () {
    lifecycleStore($this, $this->night);
    $groupId = (string) WorkSchedule::first()->work_schedule_group_id;
    $query = ['month' => '2026-11', 'group_id' => $groupId];

    $this->post(route('hr.work-schedules.deactivate'), $query + ['fingerprint' => lifecycleFingerprint($this, $query)])->assertSessionHasNoErrors();
    expect(WorkSchedule::count())->toBe(2)->and(WorkSchedule::active()->count())->toBe(0)
        ->and(DB::table('employee_work_schedule_changes')->where('action', 'DEACTIVATE')->count())->toBe(2);

    // Pengganti pada karyawan/tanggal yang sama tidak meminta konfirmasi penggantian.
    lifecycleStore($this, $this->morning, 'Pengganti');
    expect(session('work_schedule_conflicts'))->toBeNull()
        ->and(WorkSchedule::count())->toBe(4)->and(WorkSchedule::active()->count())->toBe(2)
        ->and(WorkSchedule::active()->first()->shift_name)->toBe('Pagi Test');

    // Mengganti jadwal aktif menonaktifkan yang lama alih-alih menimpanya.
    $this->actingAs($this->hr)->post(route('hr.work-schedules.store'), [
        'month' => '2026-11', 'group_name' => 'Ganti Lagi', 'users' => [$this->employee->id],
        'ranges' => [['start' => '2026-11-16', 'end' => '2026-11-16', 'shift' => (string) $this->night->id]],
    ]);
    $signature = session('work_schedule_conflicts.signature');
    $this->post(route('hr.work-schedules.store'), [
        'month' => '2026-11', 'group_name' => 'Ganti Lagi', 'users' => [$this->employee->id],
        'ranges' => [['start' => '2026-11-16', 'end' => '2026-11-16', 'shift' => (string) $this->night->id]],
        'replace' => 1, 'conflict_confirmation' => $signature,
    ])->assertSessionHasNoErrors();
    expect(WorkSchedule::count())->toBe(5)->and(WorkSchedule::active()->count())->toBe(2)
        ->and(WorkSchedule::active()->whereDate('work_date', '2026-11-16')->first()->shift_name)->toBe('Malam Test');
});

it('lifecycle: direct edit keeps group identity, records before/after, and rejects stale forms', function () {
    lifecycleStore($this, $this->night);
    $first = WorkSchedule::orderBy('work_date')->first();
    $groupId = (string) $first->work_schedule_group_id;
    $query = ['month' => '2026-11', 'group_id' => $groupId];
    $fingerprint = lifecycleFingerprint($this, $query);
    $rows = WorkSchedule::orderBy('work_date')->get()->mapWithKeys(fn ($r) => [$r->id => ['shift' => $r->id === $first->id ? (string) $this->morning->id : (string) $r->shift_id, 'include' => 1]])->all();
    $payload = $query + ['fingerprint' => $fingerprint, 'group_name' => 'Kelompok Uji Baru', 'rows' => $rows];

    $this->post(route('hr.work-schedules.update'), $payload)->assertSessionHasNoErrors();
    $first->refresh();
    expect($first->shift_name)->toBe('Pagi Test')->and($first->start_time)->toBe('09:00:00')->and($first->is_overnight)->toBeFalse()
        ->and((string) $first->work_schedule_group_id)->toBe($groupId)->and(WorkSchedule::active()->count())->toBe(2);
    $history = DB::table('employee_work_schedule_changes')->where('action', 'UPDATE')->first();
    expect($history->work_schedule_id)->toBe($first->id)->and(json_decode($history->before_data)->shift_name)->toBe('Malam Test')
        ->and(json_decode($history->after_data)->shift_name)->toBe('Pagi Test')
        ->and(DB::table('employee_work_schedule_groups')->value('name'))->toBe('Kelompok Uji Baru');

    // Form lama (fingerprint basi) ditolak tanpa perubahan.
    $this->post(route('hr.work-schedules.update'), $payload)->assertSessionHasErrors('schedule');
    expect(DB::table('employee_work_schedule_changes')->where('action', 'UPDATE')->count())->toBe(1);
});

it('lifecycle: non-HR cannot edit or deactivate', function () {
    lifecycleStore($this, $this->night);
    $this->actingAs($this->employee)->get(route('hr.work-schedules.edit', ['month' => '2026-11', 'group_id' => '1']))->assertForbidden();
    $this->post(route('hr.work-schedules.deactivate'), [])->assertForbidden();
    expect(WorkSchedule::active()->count())->toBe(2);
});
