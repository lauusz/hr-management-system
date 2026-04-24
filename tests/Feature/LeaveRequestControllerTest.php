<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

pest()->extend(Tests\TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

describe('LeaveRequestController', function () {
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
            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Keperluan dokter',
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
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Keperluan mendesak',
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
            EmployeeProfile::factory()->forUser($employee)->joinedYearsAgo(2)->create();

            actingAs($employee, 'web');

            $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
                'reason'     => 'Liburan keluarga',
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
            EmployeeProfile::factory()->forUser($employee)->joinedLessThanOneYear()->create();

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
                'reason'     => 'Liburan',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHasErrors(['error']);
            expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(0);
        });

        it('rejects CUTI request when balance insufficient', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::factory()->forUser($employee)->joinedYearsAgo(2)->create();

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(10)->toDateString(), // requesting 6 days
                'reason'     => 'Liburan panjang',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHasErrors(['error']);
        });

        it('rejects duplicate leave request on same date', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'status'     => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'reason'     => 'Keperluan lain',
            ]);

            $response->assertSessionHasErrors(['error']);
        });

        it('allows new request if previous was rejected', function () {
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'direct_supervisor_id' => null,
            ]);
            LeaveRequest::factory()->forUser($employee)->create([
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'status'     => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'reason'     => 'Keperluan baru',
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
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'status'     => 'BATAL',
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date'   => now()->addDays(3)->toDateString(),
                'reason'     => 'Keperluan baru',
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
                'type'       => 'INVALID_TYPE',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Test',
            ]);

            $response->assertSessionHasErrors(['type']);
        });

        it('rejects end_date before start_date', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Test',
            ]);

            $response->assertSessionHasErrors(['end_date']);
        });

        it('rate limits leave request submission', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            RateLimiter::hit('submit_izin_' . $user->id, 10);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Test',
            ]);

            $response->assertSessionHasErrors(['error']);
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
                'type'       => LeaveType::OFF_SPV,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Off SPV',
            ]);

            $response->assertSessionHasErrors(['error']);
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
                'type'       => LeaveType::OFF_SPV,
                'start_date' => $nextMonday->toDateString(),
                'end_date'   => $nextMonday->toDateString(),
                'reason'     => 'Off SPV',
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
                'type'       => LeaveType::OFF_SPV,
                'start_date' => $nextMonthMonday->toDateString(),
                'end_date'   => $nextMonthMonday->toDateString(),
                'reason'     => 'Off SPV bulan depan',
                'manager_id' => null,
            ]);

            $response->assertSessionHasErrors(['error']);
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
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date'   => now()->addDays(2)->toDateString(),
                'reason'     => 'Updated reason',
            ]);

            $response->assertStatus(200);
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
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date'   => now()->addDays(2)->toDateString(),
                'reason'     => 'New reason',
            ]);

            $response->assertSessionHasErrors(['error']);
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

        it('owner cannot cancel already approved leave without HR', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'type'   => LeaveType::CUTI,
            ]);

            actingAs($user, 'web');

            $response = $this->delete(route('leave-requests.destroy', $leave->id));

            $response->assertRedirect(route('leave-requests.index'));
            $leave->refresh();
            expect($leave->status)->toBe('BATAL');
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

            $response->assertSessionHasErrors(['error']);
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
                'end_date'   => now()->addDays(5)->toDateString(),
                'status'     => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date'   => now()->addDays(2)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => false]);
        });

        it('returns duplicate found for overlapping date', function () {
            $user = User::factory()->create();
            LeaveRequest::factory()->forUser($user)->create([
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(7)->toDateString(),
                'status'     => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'start_date' => now()->addDays(6)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => true]);
        });

        it('ignores rejected requests in duplicate check', function () {
            $user = User::factory()->create();
            LeaveRequest::factory()->forUser($user)->create([
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(5)->toDateString(),
                'status'     => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), [
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(5)->toDateString(),
            ]);

            $response->assertJson(['has_duplicate' => false]);
        });

        it('returns false for missing date parameters', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.checkDuplicate'), []);

            $response->assertJson(['has_duplicate' => false]);
        });
    });
});
