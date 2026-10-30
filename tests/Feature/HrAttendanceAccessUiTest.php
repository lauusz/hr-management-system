<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Pt;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    if (! Schema::hasTable('attendance_view_access')) {
        Schema::create('attendance_view_access', function (Blueprint $table): void {
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['viewer_id', 'employee_id']);
            $table->index('employee_id', 'idx_attendance_view_access_employee');
        });
    }

    if (! Schema::hasTable('overtime_approver_assignments')) {
        Schema::create('overtime_approver_assignments', function (Blueprint $table): void {
            $table->foreignId('employee_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
});

it('limits team attendance to employees selected by HR and denies HR actions', function () {
    $this->installAttendanceEventSchema();
    $viewer = User::factory()->create(['role' => UserRole::MANAGER]);
    $selected = User::factory()->create(['name' => 'Karyawan Terpilih']);
    $other = User::factory()->create(['name' => 'Karyawan Tidak Terpilih']);
    $viewer->attendanceVisibleEmployees()->sync([$selected->id]);
    $event = AttendanceEvent::factory()->forUser($selected)->create(['is_flexible' => true]);
    AttendanceEvent::factory()->forUser($other)->create();

    $this->actingAs($viewer)->get(route('supervisor.attendances.index'))
        ->assertOk()
        ->assertViewIs('supervisor.attendances.index')
        ->assertSee('Karyawan Terpilih')
        ->assertSee('sa-mobile-summary', false)
        ->assertSee('attendance-session-row has-flexible-action', false)
        ->assertSee('@media (max-width: 767px)', false)
        ->assertDontSee('Karyawan Tidak Terpilih')
        ->assertDontSee('Atur Akses Absensi')
        ->assertDontSee('Pindahkan ke Clock');

    $this->actingAs($viewer)->get(route('hr.attendances.index'))->assertForbidden();
    $this->actingAs($viewer)->post(route('hr.attendances.move_event_type', $event))->assertForbidden();
    $viewer->attendanceVisibleEmployees()->detach();
    $this->actingAs($viewer)->get(route('supervisor.attendances.index'))->assertForbidden();
});

it('renders standardized master list UI on /hr/attendance-access separated from detail form', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $division = Division::create(['name' => 'Operasional']);
    $position = Position::create(['name' => 'Kepala Operasional', 'division_id' => $division->id, 'is_active' => true]);
    $pt = Pt::create(['name' => 'PT Maju Bersama']);

    $recipient = User::factory()->create([
        'name' => 'Budi Santoso',
        'role' => UserRole::MANAGER,
        'division_id' => $division->id,
        'position_id' => $position->id,
    ]);
    EmployeeProfile::create([
        'user_id' => $recipient->id,
        'pt_id' => $pt->id,
    ]);

    $candidateWithoutAccess = User::factory()->create([
        'name' => 'Siti Rahma',
        'role' => UserRole::SUPERVISOR,
    ]);

    $empA = User::factory()->create(['name' => 'Karyawan Satu']);
    $empB = User::factory()->create(['name' => 'Karyawan Dua']);
    $recipient->attendanceVisibleEmployees()->sync([$empA->id, $empB->id]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendance-access.index'));

    $response->assertOk()
        ->assertViewIs('hr.attendances.access')
        ->assertSee('section-header-inline', false)
        ->assertSee('Atur Akses Absensi')
        ->assertSee('Tambah Penerima Akses')
        ->assertSee('ava-grant-modal', false)
        ->assertSee('Budi Santoso')
        ->assertSee('2 Karyawan')
        ->assertSee(route('hr.attendance-access.index', ['viewer' => $recipient->id]), false)
        ->assertSee('Siti Rahma')
        ->assertDontSee('id="attendance-access-form"', false);
});

it('renders separated detail UI when visiting /hr/attendance-access?viewer={id}', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $division = Division::create(['name' => 'Keuangan']);
    $position = Position::create(['name' => 'Supervisor Pajak', 'division_id' => $division->id, 'is_active' => true]);
    $pt = Pt::create(['name' => 'PT Nusantara']);

    $viewer = User::factory()->create([
        'name' => 'Andi Pratama',
        'role' => UserRole::SUPERVISOR,
        'division_id' => $division->id,
        'position_id' => $position->id,
    ]);
    EmployeeProfile::create([
        'user_id' => $viewer->id,
        'pt_id' => $pt->id,
    ]);

    $emp = User::factory()->create([
        'name' => 'Dewi Lestari',
        'division_id' => $division->id,
    ]);
    $viewer->attendanceVisibleEmployees()->sync([$emp->id]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendance-access.index', ['viewer' => $viewer->id]));

    $response->assertOk()
        ->assertViewIs('hr.attendances.access-detail')
        ->assertSee('Detail Akses Absensi')
        ->assertSee('Penerima Akses Absensi')
        ->assertSee('Andi Pratama')
        ->assertSee('Supervisor Pajak')
        ->assertSee('PT Nusantara')
        ->assertSee('Memiliki Akses · 1 Karyawan')
        ->assertSee('id="attendance-access-form"', false)
        ->assertSee('apv-action-bar', false)
        ->assertSee('Dewi Lestari')
        ->assertDontSee('id="ava-grant-modal"', false)
        ->assertDontSee('data-access-results', false);
});

it('returns 404 when viewer query references an invalid, inactive, or HR user', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $inactiveUser = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => 'INACTIVE',
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.attendance-access.index', ['viewer' => 999999]))
        ->assertNotFound();

    $this->actingAs($hrd, 'web')
        ->get(route('hr.attendance-access.index', ['viewer' => $hrd->id]))
        ->assertNotFound();

    $this->actingAs($hrd, 'web')
        ->get(route('hr.attendance-access.index', ['viewer' => $inactiveUser->id]))
        ->assertNotFound();
});

it('updates employee viewing access and redirects back to separated detail view', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $viewer = User::factory()->create(['role' => UserRole::MANAGER]);
    $emp1 = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $emp2 = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $response = $this->actingAs($hrd, 'web')
        ->post(route('hr.attendance-access.update', $viewer), [
            'employee_ids_json' => json_encode([$emp1->id, $emp2->id]),
        ]);

    $response->assertRedirect(route('hr.attendance-access.index', ['viewer' => $viewer->id]))
        ->assertSessionHas('success', 'Akses lihat absensi berhasil disimpan.');

    expect($viewer->attendanceVisibleEmployees()->pluck('users.id')->sort()->values()->all())
        ->toBe([$emp1->id, $emp2->id]);
});
