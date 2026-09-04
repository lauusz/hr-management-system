<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\Pt;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\EmployeeProfileFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder as PhpSpreadsheetDefaultValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;

use function Pest\Laravel\actingAs;

afterEach(function () {
    Carbon::setTestNow();
});

it('menampilkan pilihan export semua data dan export cuti pada halaman master', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd, 'web');

    $response = $this->get(route('hr.leave.master'));

    $response->assertOk();

    $dom = new DOMDocument;
    $previousState = libxml_use_internal_errors(true);
    $dom->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previousState);
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//*[@data-leave-export-menu]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-leave-export-trigger]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-leave-export-all and contains(normalize-space(.), "Export semua data")]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-leave-export-cuti and contains(normalize-space(.), "Export Cuti")]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-leave-export-cuti]/@href')->item(0)?->nodeValue)
        ->toContain('/hr/leave/master/export-cuti');
});

it('membagi rekap menjadi blok per PT dengan jeda dan blok tanpa PT paling akhir', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');

    try {
        $ptAlpha = Pt::factory()->create(['name' => 'PT. Alpha']);
        $ptBeta = Pt::factory()->create(['name' => 'PT. Beta']);
        $hrd = User::factory()->create(['name' => 'Admin HR', 'role' => UserRole::HRD]);
        $alphaEmployee = User::factory()->create(['name' => 'Ayu Alpha']);
        $betaEmployee = User::factory()->create(['name' => 'Beni Beta']);
        User::factory()->create(['name' => 'Citra Tanpa PT']);

        EmployeeProfileFactory::new()->forUser($hrd)->withPt($ptAlpha)->create();
        EmployeeProfileFactory::new()->forUser($alphaEmployee)->withPt($ptAlpha)->create();
        EmployeeProfileFactory::new()->forUser($betaEmployee)->withPt($ptBeta)->create();

        actingAs($hrd, 'web');

        $response = $this->get('/hr/leave/master/export-cuti');
        $response->assertOk();

        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();

        expect($sheet->getCell('A1')->getValue())->toBe('PT. Alpha')
            ->and($sheet->getCell('A2')->getValue())->toBe('Nama')
            ->and($sheet->getCell('B2')->getValue())->toBe(2026)
            ->and($sheet->getCell('B3')->getValue())->toBe(1)
            ->and($sheet->getCell('M3')->getValue())->toBe(12)
            ->and($sheet->getCell('A4')->getValue())->toBe('Admin HR')
            ->and($sheet->getCell('A5')->getValue())->toBe('Ayu Alpha')
            ->and($sheet->getCell('A6')->getValue())->toBeNull()
            ->and($sheet->getCell('A7')->getValue())->toBe('PT. Beta')
            ->and($sheet->getCell('A10')->getValue())->toBe('Beni Beta')
            ->and($sheet->getCell('A11')->getValue())->toBeNull()
            ->and($sheet->getCell('A12')->getValue())->toBe('Tanpa PT')
            ->and($sheet->getCell('A15')->getValue())->toBe('Citra Tanpa PT')
            ->and($sheet->getMergeCells())->toHaveKeys([
                'A1:M1',
                'A2:A3',
                'B2:M2',
                'A7:M7',
                'A8:A9',
                'B8:M8',
                'A12:M12',
                'A13:A14',
                'B13:M13',
            ]);
    } finally {
        Carbon::setTestNow();
    }
});

it('mengisi tanggal dan comment dari pending HR cuti serta potongan bersih ledger', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');
    $hrd = User::factory()->create(['name' => 'Admin HR', 'role' => UserRole::HRD]);
    $pendingEmployee = User::factory()->create(['name' => 'Budi Pending']);
    $deductedEmployee = User::factory()->create(['name' => 'Citra Potong Cuti']);

    LeaveRequest::factory()->forUser($pendingEmployee)->create([
        'type' => LeaveType::CUTI,
        'status' => LeaveRequest::PENDING_HR,
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-03',
        'reason' => 'Keperluan keluarga',
    ]);

    $deductedLeave = LeaveRequest::factory()->forUser($deductedEmployee)->create([
        'type' => LeaveType::IZIN,
        'status' => LeaveRequest::STATUS_APPROVED,
        'start_date' => '2026-09-07',
        'end_date' => '2026-09-09',
        'reason' => 'Mengurus dokumen keluarga',
    ]);

    LeaveBalanceTransaction::create([
        'user_id' => $deductedEmployee->id,
        'leave_request_id' => $deductedLeave->id,
        'transaction_type' => LeaveBalanceTransaction::DEDUCT,
        'amount' => 2,
        'balance_before' => 12,
        'balance_after' => 10,
        'description' => 'Potong saldo cuti',
        'idempotency_key' => "TEST:DEDUCT:{$deductedLeave->id}",
        'created_by' => $hrd->id,
    ]);
    LeaveBalanceTransaction::create([
        'user_id' => $deductedEmployee->id,
        'leave_request_id' => $deductedLeave->id,
        'transaction_type' => LeaveBalanceTransaction::REFUND,
        'amount' => 1,
        'balance_before' => 10,
        'balance_after' => 11,
        'description' => 'Refund sebagian',
        'idempotency_key' => "TEST:REFUND:{$deductedLeave->id}",
        'created_by' => $hrd->id,
    ]);

    actingAs($hrd, 'web');

    $response = $this->get('/hr/leave/master/export-cuti');

    $response->assertOk();

    try {
        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();
        $rowsByName = [];

        for ($row = 3; $row <= $sheet->getHighestRow(); $row++) {
            $name = (string) $sheet->getCell("A{$row}")->getValue();
            if ($name !== '') {
                $rowsByName[$name] = $row;
            }
        }

        $pendingRow = $rowsByName['Budi Pending'] ?? null;
        $deductedRow = $rowsByName['Citra Potong Cuti'] ?? null;

        $pendingDateCell = $sheet->getCell("B{$pendingRow}");

        expect($pendingRow)->not->toBeNull()
            ->and($deductedRow)->not->toBeNull()
            ->and($pendingDateCell->getDataType())->toBe(DataType::TYPE_NUMERIC)
            ->and($pendingDateCell->getStyle()->getNumberFormat()->getFormatCode())->toBe('dd/mm/yy')
            ->and($pendingDateCell->getFormattedValue())->toBe('01/09/26')
            ->and($sheet->getCell("C{$pendingRow}")->getFormattedValue())->toBe('02/09/26')
            ->and($sheet->getCell("D{$pendingRow}")->getFormattedValue())->toBe('03/09/26')
            ->and($sheet->getComment("B{$pendingRow}")->getText()->getPlainText())
            ->toBe("Jenis: Cuti\nStatus: Menunggu HR\nKeterangan: Keperluan keluarga\nPotong cuti: 3 hari")
            ->and($sheet->getCell("B{$deductedRow}")->getFormattedValue())->toBe('07/09/26')
            ->and($sheet->getCell("C{$deductedRow}")->getValue())->toBeNull()
            ->and($sheet->getComment("B{$deductedRow}")->getText()->getPlainText())
            ->toBe("Jenis: Izin\nStatus: Disetujui\nKeterangan: Mengurus dokumen keluarga\nPotong cuti: 1 hari");
    } finally {
        Carbon::setTestNow();
    }
});

it('memformat rekap sebagai tabel yang mudah dibaca', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd, 'web');

    $response = $this->get('/hr/leave/master/export-cuti');

    $response->assertOk();

    try {
        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();

        expect($sheet->getFreezePane())->toBe('B4')
            ->and($sheet->getPageSetup()->getOrientation())->toBe('landscape')
            ->and($sheet->getStyle('A1')->getFont()->getBold())->toBeTrue()
            ->and($sheet->getStyle('A1')->getFill()->getStartColor()->getARGB())->toBe('FF1F4E78')
            ->and($sheet->getColumnDimension('A')->getWidth())->toBe(32.0)
            ->and($sheet->getColumnDimension('B')->getWidth())->toBe(14.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('hanya menampilkan dan menghitung porsi cuti yang berada dalam tahun sheet', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');
    $hrd = User::factory()->create(['name' => 'Admin HR', 'role' => UserRole::HRD]);
    $employee = User::factory()->create(['name' => 'Dewi Lintas Tahun']);

    LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'status' => LeaveRequest::PENDING_HR,
        'start_date' => '2025-12-31',
        'end_date' => '2026-01-02',
        'reason' => 'Libur akhir tahun',
    ]);

    actingAs($hrd, 'web');

    $response = $this->get('/hr/leave/master/export-cuti');

    $response->assertOk();

    try {
        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();
        $employeeRow = null;

        for ($row = 3; $row <= $sheet->getHighestRow(); $row++) {
            if ($sheet->getCell("A{$row}")->getValue() === 'Dewi Lintas Tahun') {
                $employeeRow = $row;
                break;
            }
        }

        expect($employeeRow)->not->toBeNull()
            ->and($sheet->getCell("B{$employeeRow}")->getFormattedValue())->toBe('01/01/26')
            ->and($sheet->getCell("C{$employeeRow}")->getFormattedValue())->toBe('02/01/26')
            ->and($sheet->getCell("D{$employeeRow}")->getValue())->toBeNull()
            ->and($sheet->getComment("B{$employeeRow}")->getText()->getPlainText())
            ->toBe("Jenis: Cuti\nStatus: Menunggu HR\nKeterangan: Libur akhir tahun\nPotong cuti: 2 hari");
    } finally {
        Carbon::setTestNow();
    }
});

it('menulis nama karyawan sebagai teks literal agar tidak menjadi formula Excel', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');

    try {
        $hrd = User::factory()->create(['name' => 'Admin HR', 'role' => UserRole::HRD]);
        User::factory()->create(['name' => '=2+2']);

        actingAs($hrd, 'web');

        $response = $this->get('/hr/leave/master/export-cuti');
        $response->assertOk();

        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();
        $formulaLikeNameCell = null;

        for ($row = 3; $row <= $sheet->getHighestRow(); $row++) {
            if ($sheet->getCell("A{$row}")->getValue() === '=2+2') {
                $formulaLikeNameCell = $sheet->getCell("A{$row}");
                break;
            }
        }

        expect($formulaLikeNameCell)->not->toBeNull()
            ->and($formulaLikeNameCell->getDataType())->toBe(DataType::TYPE_STRING)
            ->and($formulaLikeNameCell->getValue())->toBe('=2+2');
    } finally {
        Carbon::setTestNow();
    }
});

it('mengunduh workbook rekap cuti dengan sheet dan dua tingkat header tahun berjalan', function () {
    Carbon::setTestNow('2026-09-03 10:00:00');
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd, 'web');

    $response = $this->get('/hr/leave/master/export-cuti');

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload('rekap_cuti_2026.xlsx');

    try {
        SpreadsheetCell::setValueBinder(new PhpSpreadsheetDefaultValueBinder);
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();

        expect($sheet->getTitle())->toBe('2026')
            ->and($sheet->getCell('A1')->getValue())->toBe('Tanpa PT')
            ->and($sheet->getCell('A2')->getValue())->toBe('Nama')
            ->and($sheet->getCell('B2')->getValue())->toBe(2026)
            ->and($sheet->getCell('B3')->getValue())->toBe(1)
            ->and($sheet->getCell('M3')->getValue())->toBe(12)
            ->and($sheet->getMergeCells())->toHaveKeys(['A1:M1', 'A2:A3', 'B2:M2']);
    } finally {
        Carbon::setTestNow();
    }
});
