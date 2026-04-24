<?php

use App\Enums\UserRole;
use App\Models\LoanRepayment;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('LoanRequest Model', function () {
    it('can be created with valid data', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        $loan = LoanRequest::create([
            'user_id' => $user->id,
            'snapshot_name' => $user->name,
            'snapshot_nik' => 'NIK001',
            'snapshot_position' => 'Staff',
            'snapshot_division' => 'IT',
            'snapshot_company' => 'Company A',
            'submitted_at' => now()->toDateString(),
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'repayment_term' => 10,
            'payment_method' => 'POTONG_GAJI',
            'status' => 'PENDING_HRD',
        ]);

        expect($loan->id)->toBeTruthy()
            ->and($loan->status)->toBe('PENDING_HRD')
            ->and($loan->amount)->toBe('5000000.00');
    });

    it('belongs to a user', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $loan = LoanRequest::factory()->create(['user_id' => $user->id]);

        expect($loan->user->id)->toBe($user->id);
    });

    it('has many repayments', function () {
        $loan = LoanRequest::factory()->create();

        LoanRepayment::factory()->count(3)->create(['loan_request_id' => $loan->id]);

        expect($loan->repayments->count())->toBe(3);
    });

    it('can be approved and records hrd info', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'PENDING_HRD',
            'hrd_id' => null,
        ]);

        $loan->update([
            'status' => 'APPROVED',
            'hrd_id' => $hrd->id,
            'hrd_decided_at' => now(),
        ]);

        expect($loan->fresh()->status)->toBe('APPROVED')
            ->and($loan->hrd_id)->toBe($hrd->id)
            ->and($loan->hrd_decided_at)->toBeTruthy();
    });

    it('can be rejected', function () {
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        $loan->update(['status' => 'REJECTED']);

        expect($loan->fresh()->status)->toBe('REJECTED');
    });

    it('can transition from PENDING_HRD to APPROVED', function () {
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);
        $loan->update(['status' => 'APPROVED']);

        expect($loan->fresh()->status)->toBe('APPROVED');
    });

    it('can transition from APPROVED to LUNAS when fully repaid', function () {
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'amount' => 1000000,
        ]);

        expect($loan->fresh()->status)->toBe('LUNAS');
    });

    it('can be rejected from PENDING_HRD status', function () {
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);
        $loan->update(['status' => 'REJECTED']);

        expect($loan->fresh()->status)->toBe('REJECTED');
    });

    it('can be rejected from APPROVED status (HRD cancel)', function () {
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED']);
        $loan->update(['status' => 'REJECTED']);

        expect($loan->fresh()->status)->toBe('REJECTED');
    });

    it('has hrd relationship', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['hrd_id' => $hrd->id]);

        expect($loan->hrd->id)->toBe($hrd->id);
    });

    it('calculates remaining balance correctly', function () {
        $loan = LoanRequest::factory()->create([
            'amount' => 1000000,
        ]);

        LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'amount' => 400000,
        ]);

        $totalPaid = $loan->repayments()->sum('amount');
        $remaining = $loan->amount - $totalPaid;

        expect($remaining)->toBe(600000);
    });

    it('stores snapshot data at submission time', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
        ]);

        $loan = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'snapshot_name' => 'Original Name',
            'snapshot_nik' => 'NIK123',
        ]);

        expect($loan->snapshot_name)->toBe('Original Name')
            ->and($loan->snapshot_nik)->toBe('NIK123');
    });
});

describe('EmployeeLoanRequestController', function () {
    // === INDEX ===
    it('index shows only loans belonging to the authenticated user', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $otherUser = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        LoanRequest::factory()->count(2)->create(['user_id' => $user->id]);
        LoanRequest::factory()->count(3)->create(['user_id' => $otherUser->id]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.index'));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(2);
    });

    it('index shows all statuses of own loans', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'PENDING_HRD']);
        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'APPROVED']);
        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'REJECTED']);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.index'));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(3);
    });

    // === SHOW ===
    it('show prevents access to other users loan', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $otherUser = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        $otherLoan = LoanRequest::factory()->create(['user_id' => $otherUser->id]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.show', $otherLoan->id));

        $response->assertStatus(403);
    });

    it('show allows access to own loan', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $loan = LoanRequest::factory()->create(['user_id' => $user->id]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.show', $loan->id));

        $response->assertStatus(200);
    });

    it('show returns 404 for non-existent loan', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.show', 99999));

        $response->assertStatus(404);
    });

    // === CREATE ===
    it('create is accessible by any authenticated user', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.create'));

        $response->assertStatus(200);
    });

    it('create is accessible by HR STAFF', function () {
        $user = User::factory()->create(['role' => UserRole::HR_STAFF]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.create'));

        $response->assertStatus(200);
    });

    it('create is accessible by HRD', function () {
        $user = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($user, 'web');

        $response = $this->get(route('employee.loan_requests.create'));

        $response->assertStatus(200);
    });

    // === STORE ===
    it('store creates a loan request for the authenticated user', function () {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'name' => 'John Doe',
        ]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
            'purpose' => 'Kebutuhan keluarga',
        ]);

        $response->assertRedirect(route('employee.loan_requests.index'));

        $loan = LoanRequest::where('user_id', $user->id)->first();

        expect($loan)->toBeTruthy()
            ->and($loan->amount)->toBe('5000000.00')
            ->and($loan->status)->toBe('PENDING_HRD')
            ->and($loan->snapshot_name)->toBe('John Doe');
    });

    it('store validates required fields', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), []);

        $response->assertSessionHasErrors(['amount', 'monthly_installment', 'payment_method']);
    });

    it('store calculates tenor correctly', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $this->post(route('employee.loan_requests.store'), [
            'amount' => 6000000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();

        // 6000000 / 500000 = 12 bulan
        expect($loan->repayment_term)->toBe(12);
    });

    it('store rejects amount below 1', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 0,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
        ]);

        $response->assertSessionHasErrors(['amount']);
    });

    it('store rejects negative amount', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => -1000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
        ]);

        $response->assertSessionHasErrors(['amount']);
    });

    it('store rejects invalid payment method', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'payment_method' => 'INVALID_METHOD',
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    });

    it('store accepts all valid payment methods', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $paymentMethods = ['TUNAI', 'CICILAN', 'POTONG_GAJI'];

        foreach ($paymentMethods as $method) {
            $response = $this->post(route('employee.loan_requests.store'), [
                'amount' => 1000000,
                'monthly_installment' => 100000,
                'payment_method' => $method,
            ]);

            $response->assertSessionDoesntHaveErrors(['payment_method']);
        }
    });

    it('store accepts optional disbursement_date', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
            'disbursement_date' => '2026-05-01',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan->disbursement_date)->toBeTruthy();
    });

    it('store accepts optional purpose', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
            'purpose' => 'Keperluan rumah tangga',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan->purpose)->toBe('Keperluan rumah tangga');
    });

    it('HR staff can also create loan request', function () {
        $user = User::factory()->create(['role' => UserRole::HR_STAFF]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 3000000,
            'monthly_installment' => 300000,
            'payment_method' => 'TUNAI',
        ]);

        $response->assertRedirect(route('employee.loan_requests.index'));

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan)->toBeTruthy();
    });

    it('Manager can create loan request', function () {
        $user = User::factory()->create(['role' => UserRole::MANAGER]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 2000000,
            'monthly_installment' => 200000,
            'payment_method' => 'CICILAN',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan)->toBeTruthy();
    });

    it('Supervisor can create loan request', function () {
        $user = User::factory()->create(['role' => UserRole::SUPERVISOR]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 2000000,
            'monthly_installment' => 200000,
            'payment_method' => 'CICILAN',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan)->toBeTruthy();
    });

    // === UNAUTHENTICATED ===
    it('unauthenticated user redirected to login for index', function () {
        $response = $this->get(route('employee.loan_requests.index'));

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for create', function () {
        $response = $this->get(route('employee.loan_requests.create'));

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for store', function () {
        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 5000000,
            'monthly_installment' => 500000,
            'payment_method' => 'POTONG_GAJI',
        ]);

        $response->assertRedirect('/login');
    });
});

describe('HrLoanRequestController', function () {
    // === INDEX ===
    it('index is accessible by HRD', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.index'));

        $response->assertStatus(200);
    });

    it('index is accessible by HR Staff', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);

        actingAs($hrStaff, 'web');

        $response = $this->get(route('hr.loan_requests.index'));

        $response->assertStatus(200);
    });

    it('index returns all loans for HRD', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $user1 = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $user2 = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        LoanRequest::factory()->count(2)->create(['user_id' => $user1->id]);
        LoanRequest::factory()->count(3)->create(['user_id' => $user2->id]);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.index'));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(5);
    });

    it('index filters by status query param', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $user = User::factory()->create();

        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'PENDING_HRD']);
        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'APPROVED']);
        LoanRequest::factory()->create(['user_id' => $user->id, 'status' => 'REJECTED']);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.index', ['status' => 'PENDING_HRD']));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(1);
        expect($response->viewData('loans')->first()->status)->toBe('PENDING_HRD');
    });

    it('index filters by search query (snapshot_name)', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        LoanRequest::factory()->create(['snapshot_name' => 'John Doe']);
        LoanRequest::factory()->create(['snapshot_name' => 'Jane Smith']);
        LoanRequest::factory()->create(['snapshot_name' => 'Bob Wilson']);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.index', ['q' => 'John']));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(1);
        expect($response->viewData('loans')->first()->snapshot_name)->toBe('John Doe');
    });

    it('index filters by submitted_at date', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        LoanRequest::factory()->create(['submitted_at' => '2026-04-01']);
        LoanRequest::factory()->create(['submitted_at' => '2026-04-15']);
        LoanRequest::factory()->create(['submitted_at' => '2026-04-20']);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.index', ['submitted_at' => '2026-04-15']));

        $response->assertStatus(200);
        expect($response->viewData('loans')->count())->toBe(1);
    });

    // === SHOW ===
    it('show is accessible by HRD', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create();

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.show', $loan->id));

        $response->assertStatus(200);
    });

    it('show is accessible by HR Staff (read-only)', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $loan = LoanRequest::factory()->create();

        actingAs($hrStaff, 'web');

        $response = $this->get(route('hr.loan_requests.show', $loan->id));

        $response->assertStatus(200);
    });

    it('show returns 404 for non-existent loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.show', 99999));

        $response->assertStatus(404);
    });

    it('show loads repayments relationship', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create();

        LoanRepayment::factory()->count(2)->create(['loan_request_id' => $loan->id]);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.loan_requests.show', $loan->id));

        $response->assertStatus(200);
        expect($response->viewData('loan')->repayments->count())->toBe(2);
    });

    // === APPROVE ===
    it('HRD can approve PENDING_HRD loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertRedirect();
        expect($loan->fresh()->status)->toBe('APPROVED');
    });

    it('HRD approve saves hrd_id and hrd_decided_at', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD', 'hrd_id' => null]);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.approve', $loan->id));

        expect($loan->fresh()->hrd_id)->toBe($hrd->id)
            ->and($loan->fresh()->hrd_decided_at)->toBeTruthy();
    });

    it('HRD approve saves notes field', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.approve', $loan->id), [
            'notes' => 'Disetujui dengan conditions',
        ]);

        expect($loan->fresh()->notes)->toBe('Disetujui dengan conditions');
    });

    it('HRD approve saves hrd_note (internal)', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.approve', $loan->id), [
            'hrd_note' => 'Internal note for HR',
        ]);

        expect($loan->fresh()->hrd_note)->toBe('Internal note for HR');
    });

    it('HR Staff cannot approve loan', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrStaff, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertStatus(403);
    });

    it('Manager cannot approve loan', function () {
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($manager, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertStatus(403);
    });

    it('Supervisor cannot approve loan', function () {
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($supervisor, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertStatus(403);
    });

    it('Employee cannot approve loan', function () {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($employee, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertStatus(403);
    });

    it('cannot approve already approved loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertSessionHasErrors();
    });

    it('cannot approve already rejected loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'REJECTED']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertSessionHasErrors();
    });

    it('cannot approve LUNAS loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'LUNAS']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.approve', $loan->id));

        $response->assertSessionHasErrors();
    });

    it('approve non-existent loan returns 404', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.approve', 99999));

        $response->assertStatus(404);
    });

    // === REJECT ===
    it('HRD can reject PENDING_HRD loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertRedirect();
        expect($loan->fresh()->status)->toBe('REJECTED');
    });

    it('HRD can reject APPROVED loan (cancel)', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertRedirect();
        expect($loan->fresh()->status)->toBe('REJECTED');
    });

    it('HRD reject saves hrd_id and hrd_decided_at', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD', 'hrd_id' => null]);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.reject', $loan->id));

        expect($loan->fresh()->hrd_id)->toBe($hrd->id)
            ->and($loan->fresh()->hrd_decided_at)->toBeTruthy();
    });

    it('HRD reject saves notes and hrd_note', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.reject', $loan->id), [
            'notes' => 'Pengajuan ditolak',
            'hrd_note' => 'Melebihi batas maksimal',
        ]);

        expect($loan->fresh()->notes)->toBe('Pengajuan ditolak')
            ->and($loan->fresh()->hrd_note)->toBe('Melebihi batas maksimal');
    });

    it('HR Staff cannot reject loan', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($hrStaff, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertStatus(403);
    });

    it('Manager cannot reject loan', function () {
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($manager, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertStatus(403);
    });

    it('Supervisor cannot reject loan', function () {
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($supervisor, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertStatus(403);
    });

    it('Employee cannot reject loan', function () {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD']);

        actingAs($employee, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertStatus(403);
    });

    it('cannot reject already rejected loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'REJECTED']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertSessionHasErrors();
    });

    it('cannot reject LUNAS loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'LUNAS']);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.reject', $loan->id));

        $response->assertSessionHasErrors();
    });

    it('reject non-existent loan returns 404', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.reject', 99999));

        $response->assertStatus(404);
    });

    // === SAVE INTERNAL NOTE ===
    it('HRD can save internal note', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create();

        actingAs($hrd, 'web');

        $response = $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => 'Internal note from HRD',
        ]);

        $response->assertRedirect();
        expect($loan->fresh()->hrd_note)->toBe('Internal note from HRD');
    });

    it('HRD can update existing internal note', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['hrd_note' => 'Old note']);

        actingAs($hrd, 'web');

        $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => 'Updated note',
        ]);

        expect($loan->fresh()->hrd_note)->toBe('Updated note');
    });

    it('HRD can clear internal note by setting empty string', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['hrd_note' => 'Some note']);

        actingAs($hrd, 'web');

        $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => '',
        ]);

        expect($loan->fresh()->hrd_note)->toBe('');
    });

    it('HR Staff cannot save internal note', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $loan = LoanRequest::factory()->create();

        actingAs($hrStaff, 'web');

        $response = $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => 'Trying to add note',
        ]);

        $response->assertStatus(403);
    });

    it('Manager cannot save internal note', function () {
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $loan = LoanRequest::factory()->create();

        actingAs($manager, 'web');

        $response = $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => 'Manager trying to add note',
        ]);

        $response->assertStatus(403);
    });

    it('saveInternalNote validates max 1000 characters', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create();

        actingAs($hrd, 'web');

        $response = $this->put(route('hr.loan_requests.saveInternalNote', $loan->id), [
            'hrd_note' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors(['hrd_note']);
    });

    it('saveInternalNote non-existent loan returns 404', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->put(route('hr.loan_requests.saveInternalNote', 99999), [
            'hrd_note' => 'Test',
        ]);

        $response->assertStatus(404);
    });

    // === STORE REPAYMENT ===
    it('HRD can add repayment to APPROVED loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertRedirect();
        expect($loan->repayments()->count())->toBe(1);
    });

    it('HRD repayment saves user_id of recorder', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TRANSFER',
        ]);

        $repayment = $loan->repayments()->first();
        expect($repayment->user_id)->toBe($hrd->id);
    });

    it('repayment amount exceeding remaining balance shows warning instead of error', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'amount' => 400000,
        ]);

        actingAs($hrd, 'web');

        // Remaining is 600000, trying to add 800000
        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 800000,
            'method' => 'TRANSFER',
        ]);

        // Now shows warning instead of error
        $response->assertSessionHas('repayment_warning');
        $response->assertSessionDoesntHaveErrors();

        $warning = session('repayment_warning');
        expect($warning['amount'])->toBe(800000)
            ->and($warning['remaining'])->toBe(600000)
            ->and($warning['remainder'])->toBe(200000);
    });

    it('repayment with force_submit allows exceeding amount', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'amount' => 400000,
        ]);

        actingAs($hrd, 'web');

        // With force_submit, allows the excess
        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 800000,
            'method' => 'TRANSFER',
            'force_submit' => '1',
        ]);

        $response->assertRedirect();
        expect($loan->fresh()->repayments->count())->toBe(2);
    });

    it('repayment amount exactly remaining balance is allowed', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'amount' => 400000,
        ]);

        actingAs($hrd, 'web');

        // Remaining is 600000, exactly matching
        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 600000,
            'method' => 'TRANSFER',
        ]);

        $response->assertRedirect();
    });

    it('loan status becomes LUNAS when fully repaid', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        actingAs($hrd, 'web');

        $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 1000000,
            'method' => 'TRANSFER',
        ]);

        expect($loan->fresh()->status)->toBe('LUNAS');
    });

    it('loan status becomes LUNAS with multiple repayments that sum to total', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        actingAs($hrd, 'web');

        // First repayment 400000
        $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 400000,
            'method' => 'TUNAI',
        ]);

        expect($loan->fresh()->status)->toBe('APPROVED');

        // Second repayment 600000 - should become LUNAS
        $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 600000,
            'method' => 'TRANSFER',
        ]);

        expect($loan->fresh()->status)->toBe('LUNAS');
    });

    it('cannot add repayment to PENDING_HRD loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'PENDING_HRD', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        // Should fail because only APPROVED loans can be repaid
        $response->assertSessionHasErrors();
    });

    it('cannot add repayment to REJECTED loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'REJECTED', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertSessionHasErrors();
    });

    it('cannot add repayment to LUNAS loan', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'LUNAS', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertSessionHasErrors();
    });

    it('repayment method must be valid enum', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'INVALID_METHOD',
        ]);

        $response->assertSessionHasErrors(['method']);
    });

    it('repayment amount must be at least 1', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 0,
            'method' => 'TUNAI',
        ]);

        $response->assertSessionHasErrors(['amount']);
    });

    it('repayment paid_at is required', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create(['status' => 'APPROVED', 'amount' => 1000000]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertSessionHasErrors(['paid_at']);
    });

    it('HR Staff cannot add repayment', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        actingAs($hrStaff, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertStatus(403);
    });

    it('Manager cannot add repayment', function () {
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 1000000,
        ]);

        actingAs($manager, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertStatus(403);
    });

    it('storeRepayment non-existent loan returns 404', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.loan_requests.repayments.store', 99999), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertStatus(404);
    });

    // === UNAUTHENTICATED ===
    it('unauthenticated user redirected to login for HR index', function () {
        $response = $this->get(route('hr.loan_requests.index'));

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for HR approve', function () {
        $response = $this->post(route('hr.loan_requests.approve', 1));

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for HR reject', function () {
        $response = $this->post(route('hr.loan_requests.reject', 1));

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for save internal note', function () {
        $response = $this->put(route('hr.loan_requests.saveInternalNote', 1), [
            'hrd_note' => 'test',
        ]);

        $response->assertRedirect('/login');
    });

    it('unauthenticated user redirected to login for store repayment', function () {
        $response = $this->post(route('hr.loan_requests.repayments.store', 1), [
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        $response->assertRedirect('/login');
    });
});

describe('Loan Repayment Model', function () {
    it('belongs to a loan request', function () {
        $loan = LoanRequest::factory()->create();
        $repayment = LoanRepayment::factory()->create(['loan_request_id' => $loan->id]);

        expect($repayment->loanRequest->id)->toBe($loan->id);
    });

    it('belongs to a user who recorded it', function () {
        $user = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create();

        $repayment = LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'user_id' => $user->id,
        ]);

        expect($repayment->user->id)->toBe($user->id);
    });

    it('can be created with valid data', function () {
        $loan = LoanRequest::factory()->create(['amount' => 1000000]);
        $user = User::factory()->create();

        $repayment = LoanRepayment::create([
            'loan_request_id' => $loan->id,
            'user_id' => $user->id,
            'paid_at' => now()->toDateString(),
            'amount' => 500000,
            'method' => 'TUNAI',
        ]);

        expect($repayment->id)->toBeTruthy()
            ->and($repayment->amount)->toBe('500000.00');
    });

    it('amount is cast to decimal', function () {
        $loan = LoanRequest::factory()->create();
        $user = User::factory()->create();

        $repayment = LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'user_id' => $user->id,
            'amount' => 1234567,
        ]);

        expect($repayment->amount)->toBe('1234567.00');
    });

    it('paid_at is cast to date', function () {
        $loan = LoanRequest::factory()->create();
        $user = User::factory()->create();

        $repayment = LoanRepayment::factory()->create([
            'loan_request_id' => $loan->id,
            'user_id' => $user->id,
            'paid_at' => '2026-04-15',
        ]);

        expect($repayment->paid_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('Edge Cases & Boundary Conditions', function () {
    it('loan with zero amount cannot be created via store', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 0,
            'monthly_installment' => 1,
            'payment_method' => 'TUNAI',
        ]);

        $response->assertSessionHasErrors(['amount']);
    });

    it('loan with monthly_installment greater than amount', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $response = $this->post(route('employee.loan_requests.store'), [
            'amount' => 100000,
            'monthly_installment' => 500000,
            'payment_method' => 'TUNAI',
        ]);

        // Should succeed but tenor will be ceil(100000/500000) = 1
        $response->assertSessionDoesntHaveErrors(['amount', 'monthly_installment']);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan->repayment_term)->toBe(1);
    });

    it('store creates loan with PENDING_HRD status by default', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        actingAs($user, 'web');

        $this->post(route('employee.loan_requests.store'), [
            'amount' => 1000000,
            'monthly_installment' => 100000,
            'payment_method' => 'POTONG_GAJI',
        ]);

        $loan = LoanRequest::where('user_id', $user->id)->first();
        expect($loan->status)->toBe('PENDING_HRD');
    });

    it('cannot have duplicate repayments with same amount that exceeds remaining', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $loan = LoanRequest::factory()->create([
            'status' => 'APPROVED',
            'amount' => 500000,
        ]);

        actingAs($hrd, 'web');

        // First repayment 300000 - OK (remaining 200000)
        $response1 = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 300000,
            'method' => 'TUNAI',
        ]);
        $response1->assertRedirect();

        // Second repayment 300000 - FAIL (remaining only 200000)
        $response2 = $this->post(route('hr.loan_requests.repayments.store', $loan->id), [
            'paid_at' => now()->toDateString(),
            'amount' => 300000,
            'method' => 'TUNAI',
        ]);
        $response2->assertSessionHasErrors();
    });
});