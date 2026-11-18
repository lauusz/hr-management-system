<?php

// Regresi pengaman publish/email payroll. Semua email dan antrean memakai fake; tidak ada layanan/alamat nyata.
// Catatan: log pengiriman (storage/logs/log_email_terkirim.txt) ditulis job; test memakai nama unik.

use App\Enums\UserRole;
use App\Jobs\SendPayslipEmailJob;
use App\Mail\PayslipPublishedMail;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

function payrollTarget(User $user, array $overrides = [], int $month = 11, int $year = 2026): array
{
    $row = ['user_id' => $user->id, 'period_month' => $month, 'period_year' => $year, 'sisa_utang' => ''];
    foreach (Payslip::NOMINAL_FIELDS as $field) {
        $row[$field] = '0';
    }
    $row['gaji_pokok'] = '5.000.000';

    return array_merge($row, $overrides);
}

function payrollMasterPost(User $actor, array $targets, string $action = 'publish', ?string $operationId = null, array $extra = [])
{
    return test()->actingAs($actor)->post(route('hr.payroll.import.store'), array_merge([
        'source' => 'master',
        'action' => $action,
        'month' => 11,
        'year' => 2026,
        'operation_id' => $operationId ?? (string) Illuminate\Support\Str::uuid(),
        'targets_json' => json_encode($targets),
    ], $extra));
}

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    $this->hr = User::factory()->create(['role' => UserRole::HRD]);
    $this->a = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Karyawan A']);
    $this->b = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Karyawan B']);
});

it('menolak publish tanpa target tanpa menulis data maupun menjadwalkan email', function () {
    payrollMasterPost($this->hr, [])->assertSessionHas('error');

    expect(Payslip::count())->toBe(0);
    Queue::assertNothingPushed();
});

it('menolak target dengan field nominal hilang atau tidak valid tanpa menjadikannya 0', function () {
    $missing = payrollTarget($this->a);
    unset($missing['lembur']);
    payrollMasterPost($this->hr, [$missing])->assertSessionHas('error');

    payrollMasterPost($this->hr, [payrollTarget($this->a, ['gaji_pokok' => 'abc'])])->assertSessionHas('error');

    expect(Payslip::count())->toBe(0);
    Queue::assertNothingPushed();
});

it('hanya memproses target yang dikonfirmasi pada publish dari Master', function () {
    payrollMasterPost($this->hr, [payrollTarget($this->a)])->assertSessionHas('success');

    expect(Payslip::where('user_id', $this->a->id)->value('status'))->toBe('PUBLISHED')
        ->and(Payslip::where('user_id', $this->b->id)->exists())->toBeFalse();
    Queue::assertPushed(SendPayslipEmailJob::class, 1);
});

it('tetap memproses seluruh baris hasil Preview Import', function () {
    $rows = collect([$this->a, $this->b])->map(function (User $user, int $i) {
        $row = payrollTarget($user);
        unset($row['period_month'], $row['period_year']);

        return $row;
    })->all();

    $this->actingAs($this->hr)->post(route('hr.payroll.import.store'), [
        'source' => 'preview',
        'action' => 'publish',
        'month' => 11,
        'year' => 2026,
        'expected_rows' => 2,
        'operation_id' => (string) Illuminate\Support\Str::uuid(),
        'payslips' => $rows,
    ])->assertSessionHas('success');

    expect(Payslip::where('period_month', 11)->where('period_year', 2026)->count())->toBe(2);
    Queue::assertPushed(SendPayslipEmailJob::class, 2);
});

it('tidak menyimpan sebagian bila salah satu target dalam batch tidak valid', function () {
    payrollMasterPost($this->hr, [
        payrollTarget($this->a),
        payrollTarget($this->b, ['potongan_pph21' => '12x']),
    ], 'draft')->assertSessionHas('error');

    expect(Payslip::count())->toBe(0);
    Queue::assertNothingPushed();
});

it('request operasi yang sama tidak menjadwalkan email ganda, tetapi operasi baru boleh kirim ulang', function () {
    $operationId = (string) Illuminate\Support\Str::uuid();

    payrollMasterPost($this->hr, [payrollTarget($this->a)], 'publish', $operationId);
    payrollMasterPost($this->hr, [payrollTarget($this->a)], 'publish', $operationId);
    Queue::assertPushed(SendPayslipEmailJob::class, 1);

    payrollMasterPost($this->hr, [payrollTarget($this->a)], 'publish', (string) Illuminate\Support\Str::uuid());
    Queue::assertPushed(SendPayslipEmailJob::class, 2);
});

it('menyimpan edit karyawan A dari payload lengkap walau baris A tidak sedang tampil', function () {
    // Payload dibangun frontend dari snapshot dirtyRowsState; server hanya menerima target + nilai lengkapnya.
    payrollMasterPost($this->hr, [payrollTarget($this->a, ['gaji_pokok' => '7.500.000,50'])], 'draft')
        ->assertSessionHas('success');

    $payslip = Payslip::where('user_id', $this->a->id)->first();
    expect((float) $payslip->gaji_pokok)->toBe(7500000.50)->and($payslip->status)->toBe('DRAFT');
    Queue::assertNothingPushed();
});

it('membuka, memfilter, atau mencoba ulang pemuatan halaman payroll tidak menulis data atau mengirim email', function () {
    $this->actingAs($this->hr)->get(route('hr.payroll.index'))->assertOk();
    $this->actingAs($this->hr)->get(route('hr.payroll.index', ['search' => 'Karyawan']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

    expect(Payslip::count())->toBe(0);
    Queue::assertNothingPushed();
    Mail::assertNothingSent();
});

it('mailable slip tidak mengantre sendiri (satu lapis antrean di job)', function () {
    expect((new ReflectionClass(PayslipPublishedMail::class))->implementsInterface(ShouldQueue::class))->toBeFalse();
});

function publishedPayslipFor(User $user): Payslip
{
    return Payslip::create(['user_id' => $user->id, 'period_month' => 11, 'period_year' => 2026, 'status' => 'PUBLISHED', 'gaji_pokok' => 5000000]);
}

it('job mengirim langsung melalui mailer untuk slip PUBLISHED yang sesuai revisi', function () {
    $slip = publishedPayslipFor($this->a);

    (new SendPayslipEmailJob($slip->id, $this->a->name, $this->a->email, null, false, 0, $slip->revisionFingerprint()))->handle();

    Mail::assertSent(PayslipPublishedMail::class, 1);
});

it('job lama tidak mengirim slip yang kembali DRAFT atau berubah revisi', function () {
    $slip = publishedPayslipFor($this->a);
    $job = new SendPayslipEmailJob($slip->id, $this->a->name, $this->a->email, null, false, 0, $slip->revisionFingerprint());

    $slip->update(['gaji_pokok' => 9000000]);
    $job->handle();
    Mail::assertNothingSent();

    $slip->update(['gaji_pokok' => 5000000, 'status' => 'DRAFT']);
    $job->handle();
    Mail::assertNothingSent();
});

it('payload job lama tanpa fingerprint tetap diproses dengan pemeriksaan status dan penerima', function () {
    $slip = publishedPayslipFor($this->a);

    (new SendPayslipEmailJob($slip->id, $this->a->name, $this->a->email))->handle();
    Mail::assertSent(PayslipPublishedMail::class, 1);

    $slip->update(['status' => 'DRAFT']);
    (new SendPayslipEmailJob($slip->id, $this->a->name, $this->a->email))->handle();
    Mail::assertSent(PayslipPublishedMail::class, 1);
});

it('kegagalan transport tidak dicatat SUCCESS dan exception tidak ditelan', function () {
    $slip = publishedPayslipFor($this->a);
    Mail::shouldReceive('to')->andReturnSelf();
    Mail::shouldReceive('send')->andThrow(new RuntimeException('smtp down'));

    $job = new SendPayslipEmailJob($slip->id, 'TEST-QUEUE-FAIL', $this->a->email, null, false, 0, $slip->revisionFingerprint());

    expect(fn () => $job->handle())->toThrow(RuntimeException::class);

    $logPath = storage_path('logs/log_email_terkirim.txt');
    $log = is_file($logPath) ? file_get_contents($logPath) : '';
    expect(preg_match('/TEST-QUEUE-FAIL[^\n]*Status: SUCCESS/', $log))->toBe(0);
});
