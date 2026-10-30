<?php

use App\Enums\UserRole;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function ensureShiftRotationTestSchema(): void
{
    if (! Schema::hasColumn('shifts', 'is_wita')) {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->boolean('is_wita')->default(false);
        });
    }

    if (! Schema::hasTable('shift_rotations')) {
        Schema::create('shift_rotations', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('frequency', 16);
            $table->date('start_date');
            $table->unsignedTinyInteger('weekly_day')->default(1);
            $table->unsignedSmallInteger('interval_days')->nullable();
            $table->json('shift_ids');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('shift_rotation_members')) {
        Schema::create('shift_rotation_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('start_shift_id');
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('shift_rotation_snapshots')) {
        Schema::create('shift_rotation_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id');
            $table->unsignedBigInteger('user_id');
            $table->date('work_date');
            $table->unsignedBigInteger('shift_id');
            $table->string('shift_name');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_wita')->default(false);
            $table->timestamps();
        });
    }
}

it('renders /hr/shift-rotations with standardized header icon, stats, tabs, cards, stop modal, and form modal', function () {
    ensureShiftRotationTestSchema();

    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employeeA = User::factory()->create([
        'name' => 'Andi Pratama',
        'role' => UserRole::EMPLOYEE,
    ]);
    $employeeB = User::factory()->create([
        'name' => 'Budi Santoso',
        'role' => UserRole::EMPLOYEE,
    ]);

    $location = AttendanceLocation::factory()->create();
    $shiftPagi = Shift::factory()->create(['name' => 'Shift Pagi', 'is_active' => true]);
    $shiftMalam = Shift::factory()->create(['name' => 'Shift Malam', 'is_active' => true]);

    EmployeeShift::create([
        'user_id' => $employeeA->id,
        'shift_id' => $shiftPagi->id,
        'location_id' => $location->id,
    ]);
    EmployeeShift::create([
        'user_id' => $employeeB->id,
        'shift_id' => $shiftMalam->id,
        'location_id' => $location->id,
    ]);

    $rotationId = DB::table('shift_rotations')->insertGetId([
        'name' => 'Tim Gudang Pandaan',
        'frequency' => 'weekly',
        'start_date' => now('Asia/Jakarta')->toDateString(),
        'weekly_day' => 1,
        'interval_days' => null,
        'shift_ids' => json_encode([$shiftPagi->id, $shiftMalam->id]),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('shift_rotation_members')->insert([
        'shift_rotation_id' => $rotationId,
        'user_id' => $employeeA->id,
        'start_shift_id' => $shiftPagi->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('shift_rotation_snapshots')->insert([
        'shift_rotation_id' => $rotationId,
        'user_id' => $employeeA->id,
        'work_date' => now('Asia/Jakarta')->toDateString(),
        'shift_id' => $shiftPagi->id,
        'shift_name' => 'Shift Pagi',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
        'is_holiday' => false,
        'is_overnight' => false,
        'is_wita' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shift-rotations.index'))
        ->assertOk()
        ->assertSee('section-header-inline', false)
        ->assertSee('section-icon icon-navy', false)
        ->assertSee('Jadwal Rotasi Kerja')
        ->assertSee('Total Kelompok')
        ->assertSee('Rotasi Aktif')
        ->assertSee('Dihentikan')
        ->assertSee('Karyawan Ikut')
        ->assertSee('data-rotation-live-search', false)
        ->assertSee('Tim Gudang Pandaan')
        ->assertSee('Urutan Siklus Shift')
        ->assertSee('Andi Pratama')
        ->assertSee('Budi Santoso')
        ->assertSee('Mengikuti: Tim Gudang Pandaan')
        ->assertSee('stop-rotation-'.$rotationId, false)
        ->assertSee('sr-modal-header', false)
        ->assertSee('mlc-banner', false)
        ->assertSee('mlc-step', false)
        ->assertSee('Informasi &amp; Jadwal Pergantian', false)
        ->assertSee('Simpan Rotasi');
});

it('stores and stops a shift rotation from /hr/shift-rotations', function () {
    ensureShiftRotationTestSchema();

    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create([
        'name' => 'Citra Dewi',
        'role' => UserRole::EMPLOYEE,
    ]);

    $location = AttendanceLocation::factory()->create();
    $shiftA = Shift::factory()->create(['name' => 'Shift A', 'is_active' => true]);
    $shiftB = Shift::factory()->create(['name' => 'Shift B', 'is_active' => true]);

    EmployeeShift::create([
        'user_id' => $employee->id,
        'shift_id' => $shiftA->id,
        'location_id' => $location->id,
    ]);

    $startDate = now('Asia/Jakarta')->toDateString();

    $this->actingAs($hrd, 'web')
        ->post(route('hr.shift-rotations.store'), [
            'name' => 'Rotasi Operasional Cabang',
            'frequency' => 'monthly',
            'start_date' => $startDate,
            'shift_ids' => [$shiftA->id, $shiftB->id],
            'members' => [
                $employee->id => $shiftA->id,
            ],
        ])
        ->assertRedirect(route('hr.shift-rotations.index'));

    $rotation = DB::table('shift_rotations')->where('name', 'Rotasi Operasional Cabang')->first();
    expect($rotation)->not->toBeNull()
        ->and((bool) $rotation->is_active)->toBeTrue();

    $this->actingAs($hrd, 'web')
        ->post(route('hr.shift-rotations.stop', $rotation->id))
        ->assertRedirect(route('hr.shift-rotations.index'));

    expect((bool) DB::table('shift_rotations')->where('id', $rotation->id)->value('is_active'))->toBeFalse()
        ->and(DB::table('shift_rotation_members')->where('shift_rotation_id', $rotation->id)->count())->toBe(0);
});
