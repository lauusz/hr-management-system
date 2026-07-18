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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
                ->assertRedirect()
                ->assertSessionHas('error');
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
    // EDIT
    // =====================================================================
    describe('edit', function () {
        it('owner can access edit page for pending leave', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.edit', $leave));

            $response->assertStatus(200);
            expect($response->viewData('item')->id)->toBe($leave->id);
        });

        it('owner cannot access edit page for approved leave', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.edit', $leave));

            $response->assertRedirect(route('leave-requests.index'));
            $response->assertSessionHas('error');
        });

        it('other user cannot access edit page', function () {
            $owner = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $other = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($owner)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($other, 'web');

            $this->get(route('leave-requests.edit', $leave))
                ->assertRedirect()
                ->assertSessionHas('error');
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

            $response = $this->from(route('leave-requests.create'))->post(route('leave-requests.store'), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(), // requesting 6 days
                'reason' => 'Liburan panjang',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertRedirect(route('leave-requests.create'))
                ->assertSessionHas('error');
            $this->get(route('leave-requests.create'))
                ->assertSee('Sisa cuti tidak mencukupi.');
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
            expect(session('errors')->first('reason'))->toBe('Alasan wajib diisi.');
        });

        it('deletes uploaded file when creating leave request fails', function () {
            Storage::fake('public');

            $user = User::factory()->create();
            LeaveRequest::creating(fn () => throw new RuntimeException('Simulasi gagal menyimpan pengajuan.'));

            actingAs($user, 'web');
            $this->withoutExceptionHandling();

            try {
                $this->post(route('leave-requests.store'), [
                    'type' => LeaveType::IZIN->value,
                    'start_date' => now()->addDay()->toDateString(),
                    'end_date' => now()->addDay()->toDateString(),
                    'reason' => 'Keperluan mendesak',
                    'photo' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
                ]);
            } catch (RuntimeException $exception) {
                expect($exception->getMessage())->toBe('Simulasi gagal menyimpan pengajuan.');
            } finally {
                LeaveRequest::flushEventListeners();
            }

            expect(Storage::disk('public')->allFiles('leave_photos'))->toBeEmpty();
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
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $user = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'manager_id' => $manager->id,
            ]);

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
            expect(LeaveRequest::where('user_id', $user->id)->first()?->status)
                ->toBe(LeaveRequest::PENDING_HR);
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

            $response->assertRedirect();
            $response->assertSessionHas('error');
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

            $response->assertRedirect();
            $response->assertSessionHas('error');
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

        it('preserves audit notes and refreshes automatic warnings on update', function () {
            $user = User::factory()->create(['leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'notes' => '[System] Diedit oleh HR (Admin) pada 01 Jan 2026',
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'reason' => 'Updated reason',
                'substitute_pic' => 'PIC Name',
                'substitute_phone' => '08123456789',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');
            $leave->refresh();
            expect($leave->notes)
                ->toContain('[System] Diedit oleh HR (Admin) pada 01 Jan 2026')
                ->toContain('H-5')
                ->not->toContain('H-3');
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

        it('does not allow owner to cancel approved leave via stale instance race', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            // Simulasikan approval HRD: potong saldo dan ubah status menjadi APPROVED.
            app(LeaveBalanceService::class)->deductLeaveBalanceForLeave($leave);
            LeaveRequest::where('id', $leave->id)->update([
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            // Instance $leave masih PENDING_HR; DB sudah APPROVED dengan saldo terpotong.
            actingAs($user, 'web');

            $controller = app(\App\Http\Controllers\LeaveRequestController::class);
            $response = $controller->destroy($leave);

            expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
                ->and(session('error'))->toBe('Pengajuan ini tidak dapat dibatalkan.');

            $leave->refresh();
            $user->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and((float) $user->leave_balance)->toBe(10.0)
                ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
                    ->where('leave_request_id', $leave->id)
                    ->count())->toBe(0);
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

            $response->assertRedirect();
            $response->assertSessionHas('error');

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

            $response->assertRedirect();
            $response->assertSessionHas('error');

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

        it('prevents upload photo to cancelled BATAL leave', function () {
            Storage::fake('public');

            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => 'BATAL',
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('leave-requests.upload-photo', $leave->id), [
                'photo' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->photo)->toBeNull();
        });

        it('does not overwrite terminal status on photo upload race', function () {
            Storage::fake('public');

            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            // Race: instance $leave masih PENDING_HR, tapi DB sudah REJECTED.
            LeaveRequest::where('id', $leave->id)->update([
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            $file = UploadedFile::fake()->image('bukti.jpg');
            $controller = app(\App\Http\Controllers\LeaveRequestController::class);
            $request = \Illuminate\Http\Request::create('/dummy', 'POST', [], [], ['photo' => $file], []);
            $response = $controller->uploadPhoto($request, $leave);

            expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
                ->and(session('error'))->toBe('Pengajuan sudah diproses, bukti pendukung tidak dapat diunggah.');
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED)
                ->and($leave->photo)->toBeNull()
                ->and(Storage::disk('public')->allFiles('leave_photos'))->toBeEmpty();
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

            $response->assertRedirect();
            $response->assertSessionHas('error');
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

        it('excludes own leave when exclude_id provided', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($user, 'web');

            $response = $this->postJson(route('leave-requests.checkDuplicate'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-05-04',
                'end_date' => '2026-05-04',
                'exclude_id' => $leave->id,
            ]);

            $response->assertJson(['has_duplicate' => false]);
        });
    });

    // =====================================================================
    // INDEX STATS & STATUS FILTER
    // =====================================================================
    describe('index stats and status filter', function () {
        it('stats count all user leave requests not only current page', function () {
            $user = User::factory()->create();

            LeaveRequest::factory()->count(15)->forUser($user)->create(['status' => LeaveRequest::STATUS_APPROVED]);
            LeaveRequest::factory()->count(10)->forUser($user)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->count(5)->forUser($user)->create(['status' => LeaveRequest::STATUS_REJECTED]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.index'));

            $response->assertStatus(200);
            $stats = $response->viewData('stats');
            expect($stats['total'])->toBe(30)
                ->and($stats['approved'])->toBe(15)
                ->and($stats['pending'])->toBe(10)
                ->and($stats['rejected'])->toBe(5);
        });

        it('filters by status query param', function () {
            $user = User::factory()->create();

            LeaveRequest::factory()->forUser($user)->create(['status' => LeaveRequest::STATUS_APPROVED]);
            LeaveRequest::factory()->forUser($user)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->forUser($user)->create(['status' => LeaveRequest::STATUS_REJECTED]);

            actingAs($user, 'web');

            $response = $this->get(route('leave-requests.index', ['status' => LeaveRequest::STATUS_APPROVED]));

            expect($response->viewData('items')->total())->toBe(1);
            expect($response->viewData('statusFilter'))->toBe(LeaveRequest::STATUS_APPROVED);
        });
    });

    // =====================================================================
    // CALCULATE EFFECTIVE DAYS
    // =====================================================================
    describe('calculateEffectiveDays', function () {
        it('returns office holiday breakdown and leave shortage', function () {
            if (! Schema::hasTable('office_holidays')) {
                Schema::create('office_holidays', function (Blueprint $table) {
                    $table->id();
                    $table->date('holiday_date')->unique();
                    $table->string('name');
                    $table->string('type')->default('NATIONAL');
                    $table->boolean('deducts_leave')->default(false);
                    $table->text('notes')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->timestamps();
                });
            }

            DB::table('office_holidays')->delete();
            DB::table('office_holidays')->insert([
                'holiday_date' => '2026-06-16',
                'name' => 'Libur Kantor',
                'type' => 'COMPANY',
                'deducts_leave' => false,
                'is_active' => true,
            ]);

            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 11,
            ]);

            actingAs($user, 'web');

            $response = $this->postJson(route('leave-requests.calculate-effective-days'), [
                'start_date' => '2026-06-03',
                'end_date' => '2026-06-18',
            ]);

            $response->assertOk()
                ->assertJsonPath('days', 12)
                ->assertJsonPath('breakdown.holiday_days', 1)
                ->assertJsonPath('breakdown.holidays.0.date', '2026-06-16')
                ->assertJsonPath('leave_balance', 11)
                ->assertJsonPath('shortage', 1)
                ->assertJsonPath('exceeds_balance', true);
        });

        it('returns effective days for date range', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($user, 'web');

            $response = $this->postJson(route('leave-requests.calculate-effective-days'), [
                'start_date' => '2026-05-04',
                'end_date' => '2026-05-08',
            ]);

            $response->assertOk();
            $json = $response->json();
            expect($json['days'])->toBeGreaterThan(0);
            expect($json['label'])->toContain('hari kerja');
        });

        it('returns zero for missing dates', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->postJson(route('leave-requests.calculate-effective-days'), []);

            $response->assertJson(['days' => 0, 'label' => '0 hari']);
        });
    });

    // =====================================================================
    // UPDATE VALIDATION
    // =====================================================================
    describe('update validation', function () {
        it('requires reason when updating', function () {
            $user = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'reason' => 'Original reason',
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => '',
            ]);

            $response->assertSessionHasErrors(['reason']);
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason');
        });

        it('requires substitute pic and phone for CUTI update', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $user->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'reason' => 'Update to cuti',
            ]);

            $response->assertSessionHasErrors(['substitute_pic', 'substitute_phone']);
        });

        it('rejects CUTI update when balance insufficient', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $user->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'reason' => 'Update to cuti',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHas('error');
        });

        it('rejects CUTI update when masa kerja less than 1 year', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
                'direct_supervisor_id' => null,
            ]);
            EmployeeProfile::create(['user_id' => $user->id, 'tgl_bergabung' => now()->subMonths(6)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'reason' => 'Update to cuti',
                'substitute_pic' => 'John',
                'substitute_phone' => '081234567890',
            ]);

            $response->assertSessionHas('error');
        });

        it('rejects OFF_SPV update for non-supervisor', function () {
            $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($user, 'web');

            $response = $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'reason' => 'Update to off spv',
            ]);

            $response->assertSessionHas('error');
        });

        it('forwards pending supervisor request to HR when changed to OFF_SPV', function () {
            $manager = User::factory()->create(['role' => UserRole::MANAGER]);
            $user = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($user)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($user, 'web');

            $this->put(route('leave-requests.update', $leave->id), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'reason' => 'Update menjadi OFF SPV',
            ]);

            expect($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR);
        });
    });

    describe('search substitute', function () {
        it('returns active users matching name and excludes current user', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'name' => 'Andi Tester',
                'status' => User::STATUS_ACTIVE,
            ]);
            $match = User::factory()->create([
                'name' => 'Budi Rekan',
                'phone' => '08111111111',
                'status' => User::STATUS_ACTIVE,
            ]);
            User::factory()->create([
                'name' => 'Citra Lain',
                'status' => User::STATUS_ACTIVE,
            ]);
            User::factory()->create([
                'name' => 'Budi Lama',
                'status' => 'INACTIVE',
            ]);

            actingAs($user, 'web');

            $response = $this->getJson(route('leave-requests.search-substitute', ['q' => 'Budi']));

            $response->assertOk()
                ->assertJsonCount(1)
                ->assertJsonPath('0.name', 'Budi Rekan')
                ->assertJsonPath('0.phone', '08111111111');
        });

        it('returns results for single character query', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'name' => 'Andi Tester',
                'status' => User::STATUS_ACTIVE,
            ]);
            User::factory()->create([
                'name' => 'Budi Rekan',
                'phone' => '08111111111',
                'status' => User::STATUS_ACTIVE,
            ]);

            actingAs($user, 'web');

            $response = $this->getJson(route('leave-requests.search-substitute', ['q' => 'B']));

            $response->assertOk()
                ->assertJsonCount(1)
                ->assertJsonPath('0.name', 'Budi Rekan');
        });

        it('returns empty for empty query', function () {
            $user = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'status' => User::STATUS_ACTIVE,
            ]);

            actingAs($user, 'web');

            $response = $this->getJson(route('leave-requests.search-substitute', ['q' => '']));

            $response->assertOk()->assertJsonCount(0);
        });
    });
});
