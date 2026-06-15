<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
// âš ï¸ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase / RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data.

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('LeaveRequestController', function () {
    describe('supporting file', function () {
        it('streams an HEIC attachment through Laravel for the owner', function () {
            Storage::fake('public');

            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'photo' => 'evidence.heic',
            ]);

            Storage::disk('public')->put('leave_photos/evidence.heic', 'heic-content');

            actingAs($employee, 'web');

            $response = $this->get(route('leave-requests.supporting-file', $leave));

            $response->assertOk()
                ->assertHeader('content-type', 'image/heic')
                ->assertStreamedContent('heic-content');
        });

        it('forbids unrelated users from opening an attachment', function () {
            Storage::fake('public');

            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $otherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'photo' => 'evidence.heic',
            ]);

            Storage::disk('public')->put('leave_photos/evidence.heic', 'heic-content');

            actingAs($otherEmployee, 'web');

            $this->get(route('leave-requests.supporting-file', $leave))
                ->assertForbidden();
        });

        it('allows manager as applicant manager_id to open attachment', function () {
            Storage::fake('public');

            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'photo' => 'evidence.heic',
            ]);

            Storage::disk('public')->put('leave_photos/evidence.heic', 'heic-content');

            actingAs($manager, 'web');

            $response = $this->get(route('leave-requests.supporting-file', $leave));

            $response->assertOk()
                ->assertHeader('content-type', 'image/heic')
                ->assertStreamedContent('heic-content');
        });
    });

    // =====================================================================
    // INDEX
    // =====================================================================
    describe('index', function () {
        it('shows only own leave requests', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $other = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            LeaveRequest::factory()->count(2)->forUser($user)->create();
            LeaveRequest::factory()->count(3)->forUser($other)->create();

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.index'));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(2);
        });

        it('index is accessible by any authenticated user', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.index'));

            $response->assertStatus(200);
        });

        it('unauthenticated redirected to login', function () {
            $response = $this->get(route('leave-requests.index'));

            $response->assertRedirect('/login');
        });

        it('index filters by type query param', function () {
            $user = User::factory()->create();

            LeaveRequest::factory()->forUser($user)->create(['type' => LeaveType::CUTI]);
            LeaveRequest::factory()->forUser($user)->create(['type' => LeaveType::SAKIT]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.index', ['type' => 'CUTI']));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });
    });

    // =====================================================================
    // CREATE
    // =====================================================================
    describe('create', function () {
        it('create page is accessible', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.create'));

            $response->assertStatus(200);
        });

        it('create page shows OFF_SPV info for supervisor', function () {
            $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.create'));

            $response->assertStatus(200);
            expect($response->viewData('canOffSpv'))->toBeTrue();
        });

        it('create page does not show OFF_SPV for employee', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.create'));

            $response->assertStatus(200);
            expect($response->viewData('canOffSpv'))->toBeFalse();
        });
    });

    // =====================================================================
    // STORE
    // =====================================================================
    describe('store', function () {
        it('creates leave request with PENDING_SUPERVISOR for employee with supervisor', function () {
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Keperluan dokter',
                'manager_id' => $supervisor->id,
            ]);

            $response->assertRedirect(route('leave-requests.index'));

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave)->toBeTruthy()
                ->and($leave->status)->toBe(LeaveRequest::PENDING_SUPERVISOR);
        });

        it('creates leave request with PENDING_HR when no approver', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);

            actingAs($employee, 'web');

            $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Keperluan mendesak',
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave)->toBeTruthy()
                ->and($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        it('creates CUTI request and deducts balance when approved', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);

            actingAs($employee, 'web');

            $this->post(route('leave-requests.store'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'reason' => 'Liburan keluarga',
                'substitute_pic' => 'John Doe',
                'substitute_phone' => '081234567890',
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave)->toBeTruthy()
                ->and($leave->type)->toBe(LeaveType::CUTI);
        });

        it('rejects CUTI request when masa kerja less than 1 year', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subMonths(6)->toDateString(), 'kategori' => 'KONTRAK']);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'reason' => 'Liburan',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHas('error');
            expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(0);
        });

        it('rejects CUTI request when balance insufficient', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(), // requesting 6 days
                'reason' => 'Liburan panjang',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHas('error');
        });

        it('rejects duplicate leave request on same date', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Keperluan lain',
            ]);

            $response->assertSessionHas('error');
        });

        it('allows new request if previous was rejected', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Keperluan baru',
            ]);

            $response->assertSessionDoesntHaveErrors();
            expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(2);
        });

        it('allows new request if previous was BATAL', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'status' => 'BATAL',
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'reason' => 'Keperluan baru',
            ]);

            $response->assertSessionDoesntHaveErrors();
        });

        it('validates required fields', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), []);

            $response->assertSessionHasErrors(['type', 'start_date', 'end_date', 'reason']);
        });

        it('rejects invalid leave type', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => 'INVALID_TYPE',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Test',
            ]);

            $response->assertSessionHasErrors(['type']);
        });

        it('rejects end_date before start_date', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Test',
            ]);

            $response->assertSessionHasErrors(['end_date']);
        });

        it('rate limits leave request submission', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            RateLimiter::hit('submit_izin_'.$user->id, 10);
            RateLimiter::hit('submit_izin_'.$user->id, 10);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Test',
            ]);

            $response->assertSessionHas('error');
        });

        it('blocks overlap even when type is different', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-04',
                'end_date' => '2026-05-04',
                'reason' => 'Keperluan lain',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('error');
            expect(session('error'))->toContain('Izin');
            expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(1);
        });

        it('allows non-overlapping date', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-06',
                'end_date' => '2026-05-06',
                'reason' => 'Keperluan lain',
            ]);

            $response->assertRedirect(route('leave-requests.index'));
            $response->assertSessionHas('success');
            expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(2);
        });

        it('ignores rejected and BATAL when checking overlap', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::SAKIT->value,
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-10',
                'status' => 'BATAL',
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-03',
                'end_date' => '2026-05-03',
                'reason' => 'Keperluan lain',
            ]);

            $response->assertRedirect(route('leave-requests.index'));
            $response->assertSessionHas('success');
        });
    });

    // =====================================================================
    // STORE - OFF_SPV
    // =====================================================================
    describe('store OFF_SPV', function () {
        it('rejects OFF_SPV for non-supervisor', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Off SPV',
            ]);

            $response->assertSessionHas('error');
        });

        it('accepts OFF_SPV for supervisor within same month', function () {
            $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);

            actingAs($user, 'web');

            // Find next Monday in current month
            $nextMonday = Carbon::now()->startOfMonth();
            if ($nextMonday->dayOfWeek != Carbon::MONDAY) {
                $nextMonday = $nextMonday->next(Carbon::MONDAY);
            }
            if ($nextMonday->month != Carbon::now()->month) {
                $nextMonday = $nextMonday->subWeek();
            }

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => $nextMonday->toDateString(),
                'end_date' => $nextMonday->toDateString(),
                'reason' => 'Off SPV',
                'manager_id' => $manager->id,
            ]);

            $response->assertSessionDoesntHaveErrors(['error']);
        });

        it('rejects OFF_SPV for next month', function () {
            $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

            actingAs($user, 'web');

            $nextMonthMonday = Carbon::now()->addMonth()->startOfMonth();
            if ($nextMonthMonday->dayOfWeek != Carbon::MONDAY) {
                $nextMonthMonday = $nextMonthMonday->next(Carbon::MONDAY);
            }

            $response = $this->post(route('leave-requests.store'), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => $nextMonthMonday->toDateString(),
                'end_date' => $nextMonthMonday->toDateString(),
                'reason' => 'Off SPV bulan depan',
                'manager_id' => null,
            ]);

            $response->assertSessionHas('error');
        });
    });

    // =====================================================================
    // SHOW
    // =====================================================================
    describe('show', function () {
        it('shows own leave request', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create();

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.show', $leave->id));

            $response->assertStatus(200);
        });

        it('prevents access to other users leave', function () {
            $user = User::factory()->create();
            $other = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($other)->create();

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.show', $leave->id));

            $response->assertStatus(403);
        });
    });

    // =====================================================================
    // UPDATE
    // =====================================================================
    describe('update', function () {
        it('can update own pending leave request', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated reason',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');
            $leave->refresh();
            expect($leave->reason)->toBe('Updated reason');
        });

        it('cannot update already processed leave request', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'New reason',
            ]);

            $response->assertSessionHas('error');
        });

        it('HR cannot update APPROVED leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated by HR',
            ]);

            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason');
        });

        it('owner cannot update REJECTED leave request', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_REJECTED,
                'reason' => 'Original reason',
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'New reason',
            ]);

            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason');
        });

        it('blocks overlap with another leave on update', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::PENDING_HR,
            ]);
            $leaveB = LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-10',
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->put(route('leave-requests.update', $leaveB->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-04',
                'end_date' => '2026-05-04',
                'reason' => 'Updated reason',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('error');
            $leaveB->refresh();
            expect($leaveB->start_date->format('Y-m-d'))->toBe('2026-05-10');
        });

        it('detects status race condition during update', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'reason' => 'Original reason',
            ]);

            actingAs($user, 'web');

            // Ubah status di DB tanpa merefresh instance, memaksa race check di transaction.
            LeaveRequest::where('id', $leave->id)->update(['status' => LeaveRequest::STATUS_APPROVED]);

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Race update',
            ]);

            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason')
                ->and($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('prevents manager from updating another users pending leave request', function () {
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'reason' => 'Original reason',
            ]);

            actingAs($manager, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated by manager',
            ]);

            $response->assertStatus(403);
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason');
        });

        it('allows manager to update own pending leave request', function () {
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($manager, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated own reason',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');
            $leave->refresh();
            expect($leave->reason)->toBe('Updated own reason');
        });
    });

    // =====================================================================
    // DESTROY (CANCEL)
    // =====================================================================
    describe('destroy', function () {
        it('owner can cancel pending leave', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('leave-requests.index'));
            $leave->refresh();
            expect($leave->status)->toBe('BATAL');
        });

        it('owner cannot cancel already approved leave', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'type' => LeaveType::CUTI->value,
            ]);

            actingAs($user, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('leave-requests.index'));
            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('HR can cancel APPROVED CUTI and refunds DEDUCT ledger exact', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            app(LeaveBalanceService::class)->deductLeaveBalanceForLeave($leave);
            $employee->refresh();
            expect((float) $employee->leave_balance)->toBe(10.0);

            actingAs($hrd, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('hr.leave.index'));
            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe('BATAL')
                ->and((float) $employee->leave_balance)->toBe(12.0);

            $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
                ->where('leave_request_id', $leave->id)
                ->first();
            expect($refund)->not->toBeNull()
                ->and((float) $refund->amount)->toBe(2.0);
        });

        it('HR can cancel own APPROVED CUTI and refunds DEDUCT ledger exact', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD, 'leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($hrd)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            app(LeaveBalanceService::class)->deductLeaveBalanceForLeave($leave);
            $hrd->refresh();
            expect((float) $hrd->leave_balance)->toBe(10.0);

            actingAs($hrd, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('hr.leave.index'));
            $leave->refresh();
            $hrd->refresh();
            expect($leave->status)->toBe('BATAL')
                ->and((float) $hrd->leave_balance)->toBe(12.0);

            $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
                ->where('leave_request_id', $leave->id)
                ->first();
            expect($refund)->not->toBeNull()
                ->and((float) $refund->amount)->toBe(2.0);
        });

        it('HR can cancel APPROVED SAKIT with explicit DEDUCT refund exact', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'type' => LeaveType::SAKIT->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            app(LeaveBalanceService::class)->deductLeaveBalanceForLeave($leave, 3.0);
            $employee->refresh();
            expect((float) $employee->leave_balance)->toBe(9.0);

            actingAs($hrd, 'web');

            $this->delete(route('leave-requests.destroy', $leave->id));

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe('BATAL')
                ->and((float) $employee->leave_balance)->toBe(12.0);

            $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
                ->where('leave_request_id', $leave->id)
                ->first();
            expect($refund)->not->toBeNull()
                ->and((float) $refund->amount)->toBe(3.0);
        });

        it('cannot cancel rejected leave', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($user, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertStatus(302);
        });

        it('prevents employee from canceling another users leave request', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $other = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            $leave = LeaveRequest::factory()->forUser($other)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertStatus(403);

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        it('prevents manager from canceling another users pending leave request', function () {
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($manager, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertStatus(403);

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        it('allows manager to cancel own pending leave request', function () {
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($manager, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('leave-requests.index'));
            $leave->refresh();
            expect($leave->status)->toBe('BATAL');
        });
    });

    // =====================================================================
    // UPLOAD PHOTO
    // =====================================================================
    describe('uploadPhoto', function () {
        it('owner can upload photo to pending leave', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);
            $file = UploadedFile::fake()->image('bukti.jpg', 800, 600);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.upload-photo', $leave->id), [
                'photo' => $file,
            ]);

            $response->assertSessionHas('success');
        });

        it('prevents upload photo to processed leave', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.upload-photo', $leave->id), [
                'photo' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

            $response->assertSessionHas('error');
        });

        it('prevents manager from uploading photo to another users leave', function () {
            Storage::fake('public');

            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);
            $file = UploadedFile::fake()->image('bukti.jpg', 800, 600);

            actingAs($manager, 'web');

            $response = $this->post(route('leave-requests.upload-photo', $leave->id), [
                'photo' => $file,
            ]);

            $response->assertStatus(403);
            $leave->refresh();
            expect($leave->photo)->toBeNull();
        });

        it('allows manager to upload photo to own pending leave', function () {
            Storage::fake('public');

            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);
            $file = UploadedFile::fake()->image('bukti.jpg', 800, 600);

            actingAs($manager, 'web');

            $response = $this->post(route('leave-requests.upload-photo', $leave->id), [
                'photo' => $file,
            ]);

            $response->assertSessionHas('success');
            $leave->refresh();
            expect($leave->photo)->not->toBeNull();
        });
    });

    // =====================================================================
    // CHECK DUPLICATE
    // =====================================================================
    describe('checkDuplicate', function () {
        it('returns no duplicate for available date', function () {
            $user = User::factory()->create();
            LeaveRequest::factory()->forUser($user)->create([
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => false]);
        });

        it('returns duplicate found for overlapping date', function () {
            $user = User::factory()->create();
            LeaveRequest::factory()->forUser($user)->create([
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(6)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => true]);
        });

        it('ignores rejected requests in duplicate check', function () {
            $user = User::factory()->create();
            LeaveRequest::factory()->forUser($user)->create([
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => false]);
        });

        it('returns false for missing date parameters', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), []);

            $response->assertJson(['has_duplicate' => false]);
        });

        it('returns specific duplicate data with type and date range', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->postJson(route('leave-requests.checkDuplicate'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-05-04',
                'end_date' => '2026-05-04',
            ]);

            $response->assertOk();
            $json = $response->json();
            expect($json['has_duplicate'])->toBeTrue();
            expect($json['message'])->toContain('Cuti');
            expect($json['duplicates'][0]['start_date'])->toBe('1 Mei 2026');
            expect($json['duplicates'][0]['end_date'])->toBe('5 Mei 2026');
        });
    });
});
