<x-app title="Master Gaji Karyawan">
    @php
        $deferred = (bool) ($deferred ?? false);
        $totalRows = $payrollData->count();
        $publishedCount = $payrollData->where('payslip_status', 'PUBLISHED')->count();
        $draftCount = $payrollData->where('payslip_status', 'DRAFT')->count();
        $uncreatedCount = $payrollData->where('payslip_status', 'BELUM_DIBUAT')->count();

        $startMonthFull = \Carbon\Carbon::create()->month((int) $startMonth)->locale('id')->translatedFormat('F');
        $endMonthFull = \Carbon\Carbon::create()->month((int) $endMonth)->locale('id')->translatedFormat('F');
        $isMultiMonth = (int) $startMonth !== (int) $endMonth;
        $periodHeaderDisplay = $isMultiMonth
            ? "{$startMonthFull} – {$endMonthFull} {$year}"
            : "{$startMonthFull} {$year}";

        $formatNominal = static function ($value): string {
            $num = (float) ($value ?? 0);
            if ($num == 0.0) {
                return '0';
            }
            $hasDecimals = abs($num - round($num)) > 0.001;
            return number_format($num, $hasDecimals ? 2 : 0, ',', '.');
        };

        $incomeColumns = [
            ['key' => 'gaji_pokok', 'label' => 'Gaji Pokok', 'short' => 'Gaji Pokok', 'compact' => true],
            ['key' => 'tunjangan_jabatan', 'label' => 'Tunj. Jabatan', 'short' => 'T. Jabatan', 'compact' => true],
            ['key' => 'tunjangan_makan', 'label' => 'Tunj. Makan', 'short' => 'T. Makan', 'compact' => true],
            ['key' => 'fee_marketing', 'label' => 'Fee Marketing', 'short' => 'Fee Mktg', 'compact' => false],
            ['key' => 'bonus_bulanan', 'label' => 'Bonus Bulanan', 'short' => 'Bonus Bln', 'compact' => false],
            ['key' => 'tunjangan_telekomunikasi', 'label' => 'Telekomunikasi', 'short' => 'Telekom', 'compact' => false],
            ['key' => 'tunjangan_lainnya', 'label' => 'Tunj. Lainnya', 'short' => 'T. Lainnya', 'compact' => false],
            ['key' => 'tunjangan_penempatan', 'label' => 'Penempatan', 'short' => 'Penempatan', 'compact' => false],
            ['key' => 'tunjangan_asuransi', 'label' => 'Asuransi', 'short' => 'Asuransi', 'compact' => false],
            ['key' => 'tunjangan_kelancaran', 'label' => 'Kelancaran', 'short' => 'Kelancaran', 'compact' => false],
            ['key' => 'pendapatan_lain', 'label' => 'Lain-lain', 'short' => 'Lain-lain', 'compact' => false],
            ['key' => 'tunjangan_transportasi', 'label' => 'Transport', 'short' => 'Transport', 'compact' => true],
            ['key' => 'lembur', 'label' => 'Lembur', 'short' => 'Lembur', 'compact' => true],
            ['key' => 'thr', 'label' => 'THR', 'short' => 'THR', 'compact' => true, 'thr' => true],
            ['key' => 'bonus', 'label' => 'Bonus', 'short' => 'Bonus', 'compact' => true],
        ];

        $deductionColumns = [
            ['key' => 'potongan_bpjs_tk', 'label' => 'BPJS TK', 'short' => 'BPJS TK', 'compact' => true],
            ['key' => 'potongan_pph21', 'label' => 'PPh 21', 'short' => 'PPh 21', 'compact' => true],
            ['key' => 'potongan_hutang', 'label' => 'Hutang', 'short' => 'Hutang', 'compact' => true],
            ['key' => 'potongan_bpjs_kes', 'label' => 'BPJS Kes', 'short' => 'BPJS Kes', 'compact' => true],
            ['key' => 'potongan_terlambat', 'label' => 'Keterlambatan', 'short' => 'Terlambat', 'compact' => true],
        ];

        // Disiapkan di PHP (bukan di dalam @json) karena @json memecah ekspresi pada koma.
        $payrollPayloadFieldKeys = array_merge(
            array_column($incomeColumns, 'key'),
            array_column($deductionColumns, 'key'),
            ['sisa_utang']
        );
    @endphp

    <x-slot name="header">
        <div class="pw-topbar-inner">
            <div class="pw-topbar-left">
                <div class="pw-title-group">
                    <h1 class="pw-page-title">Master Gaji Karyawan</h1>
                    <span class="pw-subtitle-pill">ACTIVE</span>
                </div>

                <div class="pw-status-pills" id="pwStatusPills" aria-label="Ringkasan status slip gaji">
                    <span class="pw-stat-chip" title="Total baris data">
                        <span class="pw-stat-dot pw-dot-slate"></span>
                        <strong>{{ $deferred ? '…' : $totalRows }}</strong> Baris
                    </span>
                    <span class="pw-stat-chip pw-stat-chip--green" title="Status Published">
                        <span class="pw-stat-dot pw-dot-green"></span>
                        <strong>{{ $deferred ? '…' : $publishedCount }}</strong> Published
                    </span>
                    <span class="pw-stat-chip pw-stat-chip--yellow" title="Status Draft">
                        <span class="pw-stat-dot pw-dot-yellow"></span>
                        <strong>{{ $deferred ? '…' : $draftCount }}</strong> Draft
                    </span>
                    <span class="pw-stat-chip pw-stat-chip--red" title="Belum dibuat">
                        <span class="pw-stat-dot pw-dot-red"></span>
                        <strong>{{ $deferred ? '…' : $uncreatedCount }}</strong> Belum
                    </span>
                </div>
            </div>

            <div class="pw-topbar-actions">
                <span class="selected-info" title="Jumlah baris yang dicentang">
                    <span id="selected-count-badge" class="selected-count-badge">0</span>
                    <span class="selected-label">dipilih</span>
                </span>

                <form id="payroll-export-form" action="{{ route('hr.payroll.export') }}" method="POST" class="pw-inline-form">
                    @csrf
                    <input type="hidden" name="start_month" value="{{ $startMonth }}">
                    <input type="hidden" name="end_month" value="{{ $endMonth }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="pt_id" value="{{ $ptId }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="division_id" value="{{ $divisionId }}">
                    <div id="selected_rows_container"></div>
                    <button type="submit" class="btn-action btn-action-outline-success" {{ $deferred ? 'disabled' : '' }}>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        Unduh Data Excel
                    </button>
                </form>

                <form action="{{ route('hr.payroll.import.preview') }}" method="POST" enctype="multipart/form-data" class="pw-inline-form">
                    @csrf
                    <input type="hidden" name="month" value="{{ $startMonth }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="pt_id" value="{{ $ptId }}">
                    <input type="hidden" name="division_id" value="{{ $divisionId }}">

                    <label for="file_upload" class="btn-action" style="cursor: pointer;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        + Import File
                    </label>
                    <input type="file" name="file" id="file_upload" style="display: none;" onchange="this.form.submit()" accept=".xlsx,.xls,.csv,.xlsm">
                </form>

                @if(auth()->user()->isHrManager())
                    <a href="{{ route('hr.payroll.settings') }}" class="btn-action" title="Pengaturan Hak Akses Payroll">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Akses
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="payroll-page-workspace" id="payrollWorkspace">
        {{-- Compact Single-Row Filter & View Mode Toolbar --}}
        <div class="pw-toolbar">
            <form action="{{ route('hr.payroll.index') }}" method="GET" class="pw-filter-bar" id="payroll-filter-form">
                <div class="pw-filter-group">
                    <label class="pw-filter-item" for="start_month">
                        <span class="pw-filter-tag">Dari</span>
                        <select name="start_month" id="start_month" class="pw-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $startMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('M') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="pw-filter-item" for="end_month">
                        <span class="pw-filter-tag">S/d</span>
                        <select name="end_month" id="end_month" class="pw-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $endMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('M') }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="pw-filter-item" for="year">
                        <span class="pw-filter-tag">Thn</span>
                        <select name="year" id="year" class="pw-select">
                            @foreach(range(date('Y') + 1, 2023) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="pw-filter-item pw-filter-item--pt" for="pt_id">
                        <span class="pw-filter-tag">PT</span>
                        <select name="pt_id" id="pt_id" class="pw-select">
                            <option value="" {{ empty($ptId) ? 'selected' : '' }}>Semua PT</option>
                            @foreach($pts as $pt)
                                <option value="{{ $pt->id }}" {{ $ptId == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="pw-filter-item pw-filter-item--div" for="division_id">
                        <span class="pw-filter-tag">Divisi</span>
                        <select name="division_id" id="division_id" class="pw-select">
                            <option value="" {{ empty($divisionId) ? 'selected' : '' }}>Semua Divisi</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ $divisionId == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="pw-search-box" id="pwSearchBox">
                        <svg class="pw-search-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama karyawan (otomatis)..."
                            autocomplete="off"
                            data-payroll-live-search
                            class="pw-search-input"
                        >
                        <span class="pw-search-spinner" id="pwSearchSpinner" hidden aria-hidden="true"></span>
                    </div>

                    <button type="submit" class="btn-action btn-action-primary pw-btn-compact">Filter Data</button>
                    <a href="{{ route('hr.payroll.index') }}" class="btn-action pw-btn-compact">Clear</a>
                </div>
            </form>

            <div class="pw-period-banner" id="pwPeriodBanner" aria-label="Periode Gaji Aktif">
                <div class="pw-period-badge">
                    <svg class="pw-period-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="pw-period-tag">PERIODE</span>
                    <strong class="pw-period-value" id="pwPeriodHeaderValue">{{ $periodHeaderDisplay }}</strong>
                </div>
            </div>

            <div class="pw-view-controls">
                <div class="pw-mode-tabs" role="tablist" aria-label="Mode tampilan kolom">
                    <button type="button" class="pw-mode-btn is-active" data-col-mode="all" title="Tampilkan seluruh 29 kolom tabel">
                        Semua Kolom
                    </button>
                    <button type="button" class="pw-mode-btn" data-col-mode="compact" title="Tampilkan kolom utama agar muat 1 layar">
                        Fit 1 Layar
                    </button>
                    <button type="button" class="pw-mode-btn" data-col-mode="income" title="Fokus 15 kolom Pendapatan (muat 1 layar)">
                        Pendapatan (15)
                    </button>
                    <button type="button" class="pw-mode-btn" data-col-mode="deduction" title="Fokus 5 kolom Pengeluaran & Sisa Utang (muat 1 layar)">
                        Potongan (6)
                    </button>
                </div>

                <label for="thr_only_toggle" class="thr-only-toggle" title="Mode khusus input & publish THR saja">
                    <input type="checkbox" id="thr_only_toggle">
                    <span>THR</span>
                </label>
            </div>
        </div>

        @if($deferred)
            <noscript>
                <div class="pw-noscript">JavaScript dinonaktifkan. <a href="{{ request()->fullUrlWithQuery(['full' => 1]) }}">Muat data payroll tanpa JavaScript</a></div>
            </noscript>
        @endif

        {{-- Full-Height Spreadsheet Form --}}
        <form id="payroll-bulk-form" action="{{ route('hr.payroll.import.store') }}" method="POST" class="pw-bulk-form">
            @csrf
            <input type="hidden" name="action" id="payroll-action-input" value="">
            <input type="hidden" name="source" value="master">
            <input type="hidden" name="operation_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            {{-- Target simpan/publish dikirim sebagai satu payload JSON (bukan ratusan input) agar tidak terpotong max_input_vars. --}}
            <input type="hidden" name="targets_json" id="payroll-targets-json" value="">
            <input type="hidden" name="month" value="{{ $startMonth }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="pt_id" value="{{ $ptId }}">
            <input type="hidden" name="start_month" value="{{ $startMonth }}">
            <input type="hidden" name="end_month" value="{{ $endMonth }}">
            <input type="hidden" name="division_id" value="{{ $divisionId }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="thr_only_mode" id="thr_only_mode_input" value="0">

            <div class="pw-workspace-body">
                <div class="pw-load-error" id="pwLoadError" role="alert" hidden>
                    <span>Data gagal dimuat.</span>
                    <button type="button" id="pwLoadRetry" class="btn-action btn-action-primary pw-btn-compact">Coba Lagi</button>
                </div>
                <div class="table-wrapper" id="payrollTableWrapper" data-payroll-results aria-live="polite" aria-busy="false">
                    <table class="custom-table" id="payrollTable">
                        <thead>
                            <tr>
                                <th rowspan="2" class="col-sticky-1 text-center" data-col-role="fixed">
                                    <input type="checkbox" id="select_all_payroll" title="Pilih semua">
                                </th>
                                <th rowspan="2" class="col-sticky-2" data-col-role="fixed">
                                    <div class="pw-th-emp">
                                        <span>Karyawan</span>
                                        <span class="pw-th-hint">Klik "Input" / F2 untuk form 1 layar</span>
                                    </div>
                                </th>
                                <th colspan="16" class="text-center th-group-income" id="th-group-pendapatan">Pendapatan</th>
                                <th colspan="6" class="text-center th-group-deduction" id="th-group-pengeluaran">Pengeluaran</th>
                                <th rowspan="2" class="col-sticky-right-2 text-right" id="th-total-penghasilan" data-col-role="summary">Total THP</th>
                                <th rowspan="2" class="col-utang" id="th-sisa-utang" data-col-group="deduction" data-col-compact="false">Ket / Sisa Utang</th>
                                <th rowspan="2" class="col-sticky-right-1 text-center" id="th-aksi" data-col-role="fixed">Aksi</th>
                            </tr>
                            <tr class="pw-subhead-row">
                                @foreach($incomeColumns as $col)
                                    <th
                                        data-col-group="income"
                                        data-col-key="{{ $col['key'] }}"
                                        data-col-compact="{{ $col['compact'] ? 'true' : 'false' }}"
                                        data-col-thr="{{ !empty($col['thr']) ? 'true' : 'false' }}"
                                        title="{{ $col['label'] }}"
                                    >
                                        {{ $col['short'] }}
                                    </th>
                                @endforeach
                                <th
                                    class="th-subtotal-income"
                                    data-col-group="income"
                                    data-col-key="total_pendapatan"
                                    data-col-compact="true"
                                    title="Total Pendapatan"
                                >
                                    Sub. Pendapatan
                                </th>

                                @foreach($deductionColumns as $col)
                                    <th
                                        data-col-group="deduction"
                                        data-col-key="{{ $col['key'] }}"
                                        data-col-compact="{{ $col['compact'] ? 'true' : 'false' }}"
                                        title="{{ $col['label'] }}"
                                    >
                                        {{ $col['short'] }}
                                    </th>
                                @endforeach
                                <th
                                    class="th-subtotal-deduction"
                                    data-col-group="deduction"
                                    data-col-key="total_potongan"
                                    data-col-compact="true"
                                    title="Total Pengeluaran"
                                >
                                    Sub. Potongan
                                </th>
                            </tr>
                        </thead>
                        <tbody id="payrollTableBody" data-payroll-loaded="{{ $deferred ? '0' : '1' }}">
                            @forelse($payrollData as $index => $data)
                                @php
                                    $payslip = $data->latest_payslip;
                                    $monthLabel = \Carbon\Carbon::create()->month((int) $data->month)->locale('id')->translatedFormat('F');
                                    $monthShort = \Carbon\Carbon::create()->month((int) $data->month)->locale('id')->translatedFormat('M');
                                    $rowKey = $data->user->id . '-' . $data->month . '-' . $data->year;
                                    $statusDotClass = $data->payslip_status === 'PUBLISHED'
                                        ? 'pw-row-dot--green'
                                        : ($data->payslip_status === 'DRAFT' ? 'pw-row-dot--yellow' : 'pw-row-dot--red');
                                    $statusLabelText = $data->payslip_status === 'PUBLISHED'
                                        ? 'PUBLISHED'
                                        : ($data->payslip_status === 'DRAFT' ? 'DRAFT' : 'BELUM DIBUAT');
                                @endphp
                                <tr
                                    class="pw-row"
                                    data-row-index="{{ $index }}"
                                    data-row-key="{{ $rowKey }}"
                                    data-employee-name="{{ $data->user->name }}"
                                    data-employee-email="{{ $data->user->email }}"
                                    data-period-label="{{ $monthLabel }} {{ $data->year }}"
                                    data-status="{{ $data->payslip_status }}"
                                >
                                    <td class="col-sticky-1 text-center" data-col-role="fixed">
                                        <input
                                            type="checkbox"
                                            class="payroll-row-checkbox"
                                            value="{{ $rowKey }}"
                                            title="Pilih baris ini"
                                        >
                                    </td>
                                    <td class="col-sticky-2" data-col-role="fixed">
                                        <div class="pw-emp-cell">
                                            <div class="pw-emp-info">
                                                <div class="pw-emp-name-row">
                                                    <span
                                                        class="pw-row-status-dot {{ $statusDotClass }}"
                                                        title="Status: {{ $statusLabelText }}"
                                                        aria-label="Status: {{ $statusLabelText }}"
                                                    ></span>
                                                    <div class="fw-bold pw-emp-name" title="{{ $data->user->name }} ({{ $statusLabelText }})">{{ $data->user->name }}</div>
                                                </div>
                                                <div class="text-muted pw-emp-email" title="{{ $data->user->email }}">
                                                    {{ $data->user->email }}
                                                    @if($isMultiMonth)
                                                        <span class="pw-inline-period-pill">{{ $monthShort }} {{ $data->year }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                class="pw-quick-edit-btn"
                                                data-open-quick-edit="{{ $index }}"
                                                title="Buka form input cepat 1 layar untuk {{ $data->user->name }} (F2)"
                                            >
                                                Input
                                            </button>
                                        </div>

                                        <input type="hidden" name="payslips[{{ $index }}][user_id]" value="{{ $data->user->id }}">
                                        <input type="hidden" name="payslips[{{ $index }}][period_month]" value="{{ $data->month }}">
                                        <input type="hidden" name="payslips[{{ $index }}][period_year]" value="{{ $data->year }}">
                                    </td>

                                    @foreach($incomeColumns as $col)
                                        @php
                                            $fieldKey = $col['key'];
                                            $rawVal = $payslip->{$fieldKey} ?? 0;
                                        @endphp
                                        <td
                                            class="pw-input-td"
                                            data-col-group="income"
                                            data-col-key="{{ $fieldKey }}"
                                            data-col-compact="{{ $col['compact'] ? 'true' : 'false' }}"
                                            data-col-thr="{{ !empty($col['thr']) ? 'true' : 'false' }}"
                                        >
                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                class="form-control-sm input-income"
                                                data-field-key="{{ $fieldKey }}"
                                                data-field-label="{{ $col['label'] }}"
                                                oninput="formatInput(this)"
                                                name="payslips[{{ $index }}][{{ $fieldKey }}]"
                                                value="{{ $formatNominal($rawVal) }}"
                                            >
                                        </td>
                                    @endforeach

                                    <td
                                        class="pw-subtotal-td pw-subtotal-income"
                                        data-col-group="income"
                                        data-col-key="total_pendapatan"
                                        data-col-compact="true"
                                    >
                                        <span class="fw-bold text-success total-income">{{ number_format($payslip->total_pendapatan ?? 0, 0, ',', '.') }}</span>
                                    </td>

                                    @foreach($deductionColumns as $col)
                                        @php
                                            $fieldKey = $col['key'];
                                            $rawVal = $payslip->{$fieldKey} ?? 0;
                                        @endphp
                                        <td
                                            class="pw-input-td"
                                            data-col-group="deduction"
                                            data-col-key="{{ $fieldKey }}"
                                            data-col-compact="{{ $col['compact'] ? 'true' : 'false' }}"
                                        >
                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                class="form-control-sm input-deduction"
                                                data-field-key="{{ $fieldKey }}"
                                                data-field-label="{{ $col['label'] }}"
                                                oninput="formatInput(this)"
                                                name="payslips[{{ $index }}][{{ $fieldKey }}]"
                                                value="{{ $formatNominal($rawVal) }}"
                                            >
                                        </td>
                                    @endforeach

                                    <td
                                        class="pw-subtotal-td pw-subtotal-deduction"
                                        data-col-group="deduction"
                                        data-col-key="total_potongan"
                                        data-col-compact="true"
                                    >
                                        <span class="fw-bold text-danger total-deduction">{{ number_format($payslip->total_potongan ?? 0, 0, ',', '.') }}</span>
                                    </td>

                                    <td class="col-sticky-right-2 text-right pw-thp-td" data-col-role="summary">
                                        <span class="fw-bold text-primary total-thp">{{ number_format($payslip->gaji_bersih ?? 0, 0, ',', '.') }}</span>
                                    </td>

                                    <td class="pw-input-td col-utang" data-col-group="deduction" data-col-key="sisa_utang" data-col-compact="false">
                                        <input
                                            type="text"
                                            class="form-control-sm input-utang"
                                            data-field-key="sisa_utang"
                                            data-field-label="Ket / Sisa Utang"
                                            name="payslips[{{ $index }}][sisa_utang]"
                                            value="{{ $payslip->sisa_utang ?? '' }}"
                                            placeholder="Catatan / sisa..."
                                        >
                                    </td>

                                    <td class="col-sticky-right-1 text-center pw-action-td" data-col-role="fixed">
                                        @if($payslip)
                                            <button
                                                type="button"
                                                class="btn-action btn-action-clear open-clear-confirm pw-clear-row-btn"
                                                data-delete-url="{{ route('hr.payroll.destroy', $payslip->id) }}"
                                                data-employee-name="{{ $data->user->name }}"
                                                data-month-label="{{ $monthLabel }} {{ $data->year }}"
                                            >
                                                Clear
                                            </button>
                                        @else
                                            <span class="pw-dash">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                @if($deferred)
                                    <tr class="pw-placeholder-row" aria-hidden="true"><td colspan="33" class="pw-placeholder-cell"></td></tr>
                                @else
                                    <tr>
                                        <td colspan="33" class="empty-state">Tidak ada data karyawan ditemukan.</td>
                                    </tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 1-Screen Slide-Over Quick Input Panel (Synced directly with table inputs) --}}
                <aside class="pw-quick-drawer" id="pwQuickDrawer" aria-hidden="true" aria-label="Form Input Cepat Karyawan" inert>
                    <div class="pw-drawer-header">
                        <div class="pw-drawer-emp">
                            <div class="pw-drawer-title-row">
                                <h3 class="pw-drawer-name" id="pwDrawerName">-</h3>
                                <span class="badge-type" id="pwDrawerStatus">-</span>
                            </div>
                            <div class="pw-drawer-meta">
                                <span id="pwDrawerEmail">-</span>
                                <span>&bull;</span>
                                <span id="pwDrawerPeriod">-</span>
                            </div>
                        </div>

                        <div class="pw-drawer-nav">
                            <button type="button" class="pw-nav-btn" id="pwDrawerPrev" title="Karyawan Sebelumnya (Alt + Panah Atas)">
                                &larr; Prev
                            </button>
                            <span class="pw-drawer-pos" id="pwDrawerCounter">1 / {{ max(1, $totalRows) }}</span>
                            <button type="button" class="pw-nav-btn" id="pwDrawerNext" title="Karyawan Berikutnya (Alt + Panah Bawah)">
                                Next &rarr;
                            </button>
                            <button type="button" class="pw-drawer-close" id="pwDrawerClose" title="Tutup panel (Esc)">&times;</button>
                        </div>
                    </div>

                    <div class="pw-drawer-summary">
                        <div class="pw-summary-box pw-summary-box--income">
                            <span class="pw-summary-label">Total Pendapatan</span>
                            <strong class="pw-summary-val text-success" id="pwDrawerTotalIncome">0</strong>
                        </div>
                        <div class="pw-summary-box pw-summary-box--deduction">
                            <span class="pw-summary-label">Total Pengeluaran</span>
                            <strong class="pw-summary-val text-danger" id="pwDrawerTotalDeduction">0</strong>
                        </div>
                        <div class="pw-summary-box pw-summary-box--thp">
                            <span class="pw-summary-label">Gaji Bersih (THP)</span>
                            <strong class="pw-summary-val text-primary" id="pwDrawerTotalThp">0</strong>
                        </div>
                    </div>

                    <div class="pw-drawer-body">
                        <div class="pw-drawer-section">
                            <div class="pw-section-head pw-section-head--income">Pendapatan (15 Komponen)</div>
                            <div class="pw-drawer-grid">
                                @foreach($incomeColumns as $col)
                                    <label class="pw-field">
                                        <span class="pw-field-label">{{ $col['label'] }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            class="pw-field-input"
                                            data-drawer-field="{{ $col['key'] }}"
                                            placeholder="0"
                                        >
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pw-drawer-section">
                            <div class="pw-section-head pw-section-head--deduction">Pengeluaran &amp; Catatan Utang</div>
                            <div class="pw-drawer-grid">
                                @foreach($deductionColumns as $col)
                                    <label class="pw-field">
                                        <span class="pw-field-label">{{ $col['label'] }}</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            class="pw-field-input pw-field-input--deduction"
                                            data-drawer-field="{{ $col['key'] }}"
                                            placeholder="0"
                                        >
                                    </label>
                                @endforeach
                                <label class="pw-field pw-field--wide">
                                    <span class="pw-field-label">Ket / Sisa Utang</span>
                                    <input
                                        type="text"
                                        class="pw-field-input pw-field-input--text"
                                        data-drawer-field="sisa_utang"
                                        placeholder="Ketik keterangan atau nominal sisa utang..."
                                    >
                                </label>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            {{-- Sticky Bottom Action Bar --}}
            <div class="bulk-footer-actions">
                <div class="pw-footer-left">
                    <span class="pw-kbd-hint">Tips: Gunakan <strong>Enter / &uarr;&darr;</strong> pindah baris, <strong>Tab</strong> pindah kolom, <strong>F2</strong> buka panel input 1 layar</span>
                </div>

                <div class="pw-footer-right">
                    <button type="button" id="btn-clear-selected" class="btn-action btn-action-clear" disabled>Hapus Data</button>
                    <button type="button" id="btn-save-draft" class="btn-action" {{ $deferred ? 'disabled' : '' }}>Simpan DRAFT</button>
                    <button type="button" id="btn-publish-email" class="btn-action btn-action-primary" {{ $deferred ? 'disabled' : '' }}>Publish &amp; Kirim Email</button>
                </div>
            </div>
        </form>
    </div>

    @include('hr._master_ajax')

    <x-modal id="modal-publish-warning" title="Perhatian" variant="warning" type="form">
        <div style="display:flex; align-items:flex-start; gap:12px;">
            <p style="margin:0; color:#374151; line-height:1.6;">Pilih minimal <strong>satu data karyawan</strong> yang akan dipublish terlebih dahulu dengan mencentang checkbox pada tabel.</p>
        </div>
        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            <button type="button" data-modal-close="true" class="modal-btn-primary">Mengerti</button>
        </div>
    </x-modal>

    <x-modal id="modal-publish-loading" title="Memproses..." type="form">
        <div style="text-align:center; padding: 20px 0;">
            <div class="payroll-loading-spinner" style="width:40px; height:40px; border-width:3px; margin:0 auto 16px;"></div>
            <p style="margin:0; color:#374151; font-weight:600;">Data sedang dipublish ke queue email...</p>
            <p style="margin:8px 0 0; color:#6b7280; font-size:0.85rem;">Email akan terkirim bertahap. Modal ini boleh ditutup, tetapi sebaiknya jangan tutup tab atau pindah halaman sebelum proses selesai.</p>
            <div style="margin-top:16px; display:flex; justify-content:center;">
                <button type="button" data-modal-close="true" class="modal-btn-light">Tutup</button>
            </div>
        </div>
    </x-modal>

    <x-modal id="modal-publish-confirm" title="Konfirmasi Publish" variant="info" type="form">
        <p style="margin:0; color:#374151;">Anda akan mempublish dan menjadwalkan email ke <strong id="publish-confirm-count">0</strong> karyawan yang dipilih. Lanjutkan?</p>
        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" data-modal-close="true" class="modal-btn-light">Batal</button>
            <button type="button" id="confirm-publish-submit" class="modal-btn-primary">Ya, Publish</button>
        </div>
    </x-modal>

    <x-modal id="modal-clear-payslip-confirm" title="Hapus Data Gaji?" variant="danger" type="form">
        <p id="clear-payslip-confirm-text" style="margin:0; color:#374151;"></p>
        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" data-modal-close="true" class="modal-btn-light">Batal</button>
            <button type="button" id="confirm-clear-payslip-submit" class="modal-btn-danger">Ya, Hapus</button>
        </div>
    </x-modal>

    <x-modal id="modal-clear-selected-confirm" title="Hapus Data Terpilih?" variant="danger" type="form">
        <p id="clear-selected-confirm-text" style="margin:0; color:#374151;"></p>
        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" data-modal-close="true" class="modal-btn-light">Batal</button>
            <button type="button" id="confirm-clear-selected-submit" class="modal-btn-danger">Ya, Hapus</button>
        </div>
    </x-modal>

    <form id="clear-payslip-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="filter_start_month" value="{{ $startMonth }}">
        <input type="hidden" name="filter_end_month" value="{{ $endMonth }}">
        <input type="hidden" name="filter_year" value="{{ $year }}">
        <input type="hidden" name="filter_pt_id" value="{{ $ptId }}">
        <input type="hidden" name="filter_division_id" value="{{ $divisionId }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
    </form>

    <form id="clear-selected-payslips-form" action="{{ route('hr.payroll.destroy-selected') }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="filter_start_month" value="{{ $startMonth }}">
        <input type="hidden" name="filter_end_month" value="{{ $endMonth }}">
        <input type="hidden" name="filter_year" value="{{ $year }}">
        <input type="hidden" name="filter_pt_id" value="{{ $ptId }}">
        <input type="hidden" name="filter_division_id" value="{{ $divisionId }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div id="clear_selected_rows_container"></div>
    </form>

    <script>
        // Apply full-bleed class immediately so there is no layout shift
        document.querySelector('.main-content')?.classList.add('pw-main-fullbleed');
        document.querySelector('.content-wrapper')?.classList.add('pw-wrapper-fullbleed');

        document.addEventListener('DOMContentLoaded', function() {
            if (sessionStorage.getItem('payroll_refresh_after_back') === '1') {
                sessionStorage.removeItem('payroll_refresh_after_back');
                window.location.reload();
                return;
            }

            const filterForm = document.getElementById('payroll-filter-form');
            const searchInput = document.getElementById('search');
            const searchSpinner = document.getElementById('pwSearchSpinner');
            const tableWrapper = document.getElementById('payrollTableWrapper');
            const tableBody = document.getElementById('payrollTableBody');

            const exportForm = document.getElementById('payroll-export-form');
            const thrOnlyToggle = document.getElementById('thr_only_toggle');
            const selectAllCheckbox = document.getElementById('select_all_payroll');
            const selectedRowsContainer = document.getElementById('selected_rows_container');
            const selectedCountBadge = document.getElementById('selected-count-badge');
            const clearPayslipForm = document.getElementById('clear-payslip-form');
            const clearSelectedPayslipsForm = document.getElementById('clear-selected-payslips-form');
            const clearPayslipText = document.getElementById('clear-payslip-confirm-text');
            const clearSelectedConfirmText = document.getElementById('clear-selected-confirm-text');
            const confirmClearPayslipSubmitButton = document.getElementById('confirm-clear-payslip-submit');
            const confirmClearSelectedSubmitButton = document.getElementById('confirm-clear-selected-submit');
            const btnClearSelected = document.getElementById('btn-clear-selected');
            const clearSelectedRowsContainer = document.getElementById('clear_selected_rows_container');
            const bulkForm = document.getElementById('payroll-bulk-form');
            const bulkActionInput = document.getElementById('payroll-action-input');
            const thrOnlyModeInput = document.getElementById('thr_only_mode_input');
            const btnSaveDraft = document.getElementById('btn-save-draft');
            const btnPublishEmail = document.getElementById('btn-publish-email');
            const confirmPublishSubmitButton = document.getElementById('confirm-publish-submit');
            const publishConfirmCount = document.getElementById('publish-confirm-count');

            const clearPayslipConfirmModalId = 'modal-clear-payslip-confirm';
            const clearSelectedConfirmModalId = 'modal-clear-selected-confirm';
            const publishConfirmModalId = 'modal-publish-confirm';
            const publishLoadingModalId = 'modal-publish-loading';
            const publishWarningModalId = 'modal-publish-warning';

            const groupPendapatanHeader = document.getElementById('th-group-pendapatan');
            const groupPengeluaranHeader = document.getElementById('th-group-pengeluaran');
            const totalPenghasilanHeader = document.getElementById('th-total-penghasilan');
            const sisaUtangHeader = document.getElementById('th-sisa-utang');
            const aksiHeader = document.getElementById('th-aksi');
            const secondHeaderRow = document.querySelector('.custom-table thead tr:nth-child(2)');
            const payrollTable = document.querySelector('.custom-table');
            const modeButtons = document.querySelectorAll('[data-col-mode]');

            const grandTotalIncomeNode = document.getElementById('grand-total-income');
            const grandTotalDeductionNode = document.getElementById('grand-total-deduction');
            const grandTotalThpNode = document.getElementById('grand-total-thp');

            // Quick Drawer Elements
            const quickDrawer = document.getElementById('pwQuickDrawer');
            const drawerName = document.getElementById('pwDrawerName');
            const drawerEmail = document.getElementById('pwDrawerEmail');
            const drawerPeriod = document.getElementById('pwDrawerPeriod');
            const drawerStatus = document.getElementById('pwDrawerStatus');
            const drawerCounter = document.getElementById('pwDrawerCounter');
            const drawerTotalIncome = document.getElementById('pwDrawerTotalIncome');
            const drawerTotalDeduction = document.getElementById('pwDrawerTotalDeduction');
            const drawerTotalThp = document.getElementById('pwDrawerTotalThp');
            const drawerPrevBtn = document.getElementById('pwDrawerPrev');
            const drawerNextBtn = document.getElementById('pwDrawerNext');
            const drawerCloseBtn = document.getElementById('pwDrawerClose');
            const drawerInputs = Array.from(document.querySelectorAll('[data-drawer-field]'));

            let pendingClearPayslipUrl = null;
            let isSubmitting = false;
            let currentColumnMode = 'all';
            let activeDrawerRowIndex = null;
            let lastInteractedRowKey = null;

            // Preserve unsaved edits & checked state across AJAX live searches
            const dirtyRowsState = new Map();

            // Kunci field yang wajib ada pada payload setiap target simpan/publish.
            const PAYLOAD_FIELD_KEYS = @json($payrollPayloadFieldKeys);

            // select() pada input yang sudah tidak fokus dapat menarik fokus kembali (Chrome memfokuskan elemen saat
            // select()). Penundaan 0 ms hanya diperlukan agar klik mouse tidak membatalkan seleksi, jadi callback
            // hanya bekerja bila input itu masih terhubung, masih fokus, dan belum digantikan fokus berikutnya.
            let focusSelectSeq = 0;
            const selectWhenStillFocused = (input) => {
                const seq = ++focusSelectSeq;
                setTimeout(() => {
                    if (seq !== focusSelectSeq || !input.isConnected || document.activeElement !== input) return;
                    try { input.select(); } catch (_) {}
                }, 0);
            };

            // Salinan lengkap seluruh field satu baris (nominal kosong = 0, sesuai perilaku input). Disimpan di
            // dirtyRowsState supaya baris yang disembunyikan pencarian AJAX tetap punya payload lengkap.
            const captureRowSnapshot = (row) => {
                const snapshot = {};
                row.querySelectorAll('[data-field-key]').forEach((input) => {
                    const key = input.dataset.fieldKey;
                    snapshot[key] = key === 'sisa_utang' ? input.value : (input.value.trim() === '' ? '0' : input.value);
                });
                return snapshot;
            };

            const recordDirtyRowField = (row, fieldKey, value) => {
                const rowKey = row?.dataset.rowKey;
                if (!rowKey || !fieldKey) return;
                lastInteractedRowKey = rowKey;
                const cb = row.querySelector('.payroll-row-checkbox');
                if (cb && !cb.checked) {
                    cb.checked = true;
                }
                const state = dirtyRowsState.get(rowKey) || { fields: {}, checked: true, edited: true };
                state.fields[fieldKey] = value;
                state.checked = cb ? cb.checked : true;
                state.edited = true;
                state.snapshot = captureRowSnapshot(row);
                dirtyRowsState.set(rowKey, state);
                syncSelectAllState();
            };

            const recordDirtyRowCheck = (rowKey, checked) => {
                if (!rowKey) return;
                const state = dirtyRowsState.get(rowKey) || { fields: {}, checked: false };
                state.checked = checked;
                const row = Array.from(document.querySelectorAll('.custom-table tbody tr.pw-row')).find((r) => r.dataset.rowKey === rowKey);
                if (row) state.snapshot = captureRowSnapshot(row);
                dirtyRowsState.set(rowKey, state);
            };

            // Payload target = baris di DOM (nilai terkini) atau snapshot tersimpan untuk baris yang sedang
            // tersembunyi. Target tanpa payload lengkap dilaporkan di `missing` dan TIDAK dikirim.
            const buildTargetPayload = (rowKeys) => {
                const rowsByKey = new Map(getDataRows().map((r) => [r.dataset.rowKey, r]));
                const targets = [];
                const missing = [];
                rowKeys.forEach((key) => {
                    const row = rowsByKey.get(key);
                    const snapshot = row ? captureRowSnapshot(row) : dirtyRowsState.get(key)?.snapshot;
                    const parts = String(key).split('-').map(Number);
                    if (!snapshot || parts.length !== 3 || !PAYLOAD_FIELD_KEYS.every((k) => Object.prototype.hasOwnProperty.call(snapshot, k))) {
                        missing.push(key);
                        return;
                    }
                    targets.push({ user_id: parts[0], period_month: parts[1], period_year: parts[2], ...snapshot });
                });
                return { targets, missing };
            };

            // Kirim hanya payload target (JSON); input tabel penuh dinonaktifkan agar tidak ikut terkirim.
            const submitBulkTargets = (action, rowKeys) => {
                const { targets, missing } = buildTargetPayload(rowKeys);
                if (missing.length > 0 || targets.length === 0) {
                    window.showToast(
                        missing.length > 0
                            ? `${missing.length} baris terpilih belum punya data lengkap (tidak sedang ditampilkan). Tampilkan baris tersebut lewat pencarian lalu coba lagi.`
                            : 'Pilih minimal satu baris payroll.',
                        'warning'
                    );
                    return false;
                }

                const payloadInput = document.getElementById('payroll-targets-json');
                if (!payloadInput) return false;
                payloadInput.value = JSON.stringify(targets);
                bulkForm.querySelectorAll('[name^="payslips["]').forEach((input) => { input.disabled = true; });
                syncSearchHiddenInputs(searchInput?.value || '');
                bulkActionInput.value = action;
                if (thrOnlyModeInput && thrOnlyToggle) {
                    thrOnlyModeInput.value = thrOnlyToggle.checked ? '1' : '0';
                }
                return true;
            };

            const restoreDirtyRowsState = () => {
                getDataRows().forEach((row) => {
                    const rowKey = row.dataset.rowKey;
                    const state = rowKey ? dirtyRowsState.get(rowKey) : null;
                    if (!state) return;

                    const cb = row.querySelector('.payroll-row-checkbox');
                    if (cb && typeof state.checked === 'boolean') {
                        cb.checked = state.checked;
                    }

                    Object.entries(state.fields || {}).forEach(([fieldKey, val]) => {
                        const input = row.querySelector(`[data-field-key="${fieldKey}"]`);
                        if (input) {
                            input.value = val;
                        }
                    });
                });
            };

            const toggleModalById = (id, show) => {
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.style.display = show ? 'flex' : 'none';
                document.body.style.overflow = show ? 'hidden' : '';
            };

            const getRowCheckboxes = () => Array.from(document.querySelectorAll('.payroll-row-checkbox'));
            const getDataRows = () => Array.from(document.querySelectorAll('.custom-table tbody tr.pw-row'));

            const parseCurrency = (value) => {
                if (!value) return 0;
                return parseFloat(String(value).replace(/\./g, '').replace(/,/g, '.')) || 0;
            };

            const formatIdNumber = (num) => {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(num);
            };

            const applyColumnViewMode = (mode) => {
                currentColumnMode = mode;

                modeButtons.forEach((btn) => {
                    btn.classList.toggle('is-active', btn.dataset.colMode === mode);
                });

                const isThr = mode === 'thr';
                if (thrOnlyToggle && thrOnlyToggle.checked !== isThr) {
                    thrOnlyToggle.checked = isThr;
                }
                if (thrOnlyModeInput) {
                    thrOnlyModeInput.value = isThr ? '1' : '0';
                }

                let visibleIncomeCols = 0;
                let visibleDeductionCols = 0;

                const subHeaderCells = document.querySelectorAll('.pw-subhead-row th');
                subHeaderCells.forEach((th) => {
                    const group = th.dataset.colGroup;
                    const isCompact = th.dataset.colCompact === 'true';
                    const isThrCol = th.dataset.colThr === 'true';
                    let show = true;

                    if (mode === 'thr') {
                        show = isThrCol;
                    } else if (mode === 'compact') {
                        show = isCompact;
                    } else if (mode === 'income') {
                        show = group === 'income';
                    } else if (mode === 'deduction') {
                        show = group === 'deduction';
                    } else {
                        show = true;
                    }

                    th.style.display = show ? '' : 'none';
                    if (show && group === 'income') visibleIncomeCols++;
                    if (show && group === 'deduction') visibleDeductionCols++;
                });

                if (groupPendapatanHeader) {
                    if (mode === 'deduction') {
                        groupPendapatanHeader.style.display = 'none';
                    } else {
                        groupPendapatanHeader.style.display = '';
                        groupPendapatanHeader.colSpan = Math.max(1, visibleIncomeCols);
                        groupPendapatanHeader.textContent = isThr ? 'THR' : 'Pendapatan';
                        if (isThr) {
                            groupPendapatanHeader.setAttribute('rowspan', '2');
                        } else {
                            groupPendapatanHeader.removeAttribute('rowspan');
                        }
                    }
                }

                if (groupPengeluaranHeader) {
                    if (mode === 'income' || mode === 'thr') {
                        groupPengeluaranHeader.style.display = 'none';
                    } else {
                        groupPengeluaranHeader.style.display = '';
                        groupPengeluaranHeader.colSpan = Math.max(1, visibleDeductionCols);
                    }
                }

                if (totalPenghasilanHeader) {
                    totalPenghasilanHeader.style.display = isThr ? 'none' : '';
                }

                if (sisaUtangHeader) {
                    const showUtang = mode === 'all' || mode === 'deduction';
                    sisaUtangHeader.style.display = showUtang ? '' : 'none';
                }

                if (aksiHeader) {
                    aksiHeader.style.display = '';
                }

                if (secondHeaderRow) {
                    secondHeaderRow.style.display = isThr ? 'none' : '';
                }

                if (payrollTable) {
                    payrollTable.dataset.viewMode = mode;
                }

                document.querySelectorAll('.custom-table tbody tr').forEach((row) => {
                    const emptyStateCell = row.querySelector('td.empty-state');
                    if (emptyStateCell) {
                        emptyStateCell.colSpan = isThr ? 6 : 33;
                        return;
                    }

                    row.querySelectorAll('td[data-col-group], td[data-col-role="summary"]').forEach((cell) => {
                        if (cell.dataset.colRole === 'summary') {
                            cell.style.display = isThr ? 'none' : '';
                            return;
                        }

                        const group = cell.dataset.colGroup;
                        const key = cell.dataset.colKey;
                        const isCompact = cell.dataset.colCompact === 'true';
                        const isThrCol = cell.dataset.colThr === 'true';

                        let show = true;
                        if (mode === 'thr') {
                            show = isThrCol;
                        } else if (mode === 'compact') {
                            show = isCompact;
                        } else if (mode === 'income') {
                            show = group === 'income';
                        } else if (mode === 'deduction') {
                            show = group === 'deduction';
                        } else {
                            show = true;
                        }

                        if (key === 'sisa_utang') {
                            show = mode === 'all' || mode === 'deduction';
                        }

                        cell.style.display = show ? '' : 'none';
                    });
                });
            };

            const toggleThrOnlyMode = (enabled) => {
                applyColumnViewMode(enabled ? 'thr' : 'all');
            };

            const appendSelectedRowsToContainer = (container, checkedRows) => {
                if (!container) return;
                container.innerHTML = '';
                checkedRows.forEach((checkbox) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_rows[]';
                    hiddenInput.value = checkbox.value;
                    container.appendChild(hiddenInput);
                });
            };

            const syncSelectAllState = () => {
                const rowCheckboxes = getRowCheckboxes();
                const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;

                if (selectedCountBadge) {
                    selectedCountBadge.textContent = String(checkedCount);
                    selectedCountBadge.style.background = checkedCount > 0 ? '#eef2ff' : '#f3f4f6';
                    selectedCountBadge.style.borderColor = checkedCount > 0 ? '#c7d2fe' : '#d1d5db';
                    selectedCountBadge.style.color = checkedCount > 0 ? '#3730a3' : '#1f2937';
                }

                rowCheckboxes.forEach((cb) => {
                    const tr = cb.closest('tr');
                    if (tr) tr.classList.toggle('is-checked', cb.checked);
                });

                if (btnClearSelected) {
                    btnClearSelected.disabled = checkedCount === 0;
                }

                if (!selectAllCheckbox) return;
                if (rowCheckboxes.length === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                    return;
                }

                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            };

            const updateGrandTotals = () => {
                let grandIncome = 0;
                let grandDeduction = 0;

                getDataRows().forEach((row) => {
                    if (row.style.display === 'none') return;
                    grandIncome += Number(row.dataset.totalIncome || 0);
                    grandDeduction += Number(row.dataset.totalDeduction || 0);
                });

                const grandThp = grandIncome - grandDeduction;
                if (grandTotalIncomeNode) grandTotalIncomeNode.textContent = formatIdNumber(grandIncome);
                if (grandTotalDeductionNode) grandTotalDeductionNode.textContent = formatIdNumber(grandDeduction);
                if (grandTotalThpNode) grandTotalThpNode.textContent = formatIdNumber(grandThp);
            };

            const syncDrawerTotalsIfOpen = (row) => {
                if (activeDrawerRowIndex === null) return;
                if (String(row.dataset.rowIndex) !== String(activeDrawerRowIndex)) return;

                if (drawerTotalIncome) drawerTotalIncome.textContent = row.querySelector('.total-income')?.textContent || '0';
                if (drawerTotalDeduction) drawerTotalDeduction.textContent = row.querySelector('.total-deduction')?.textContent || '0';
                if (drawerTotalThp) drawerTotalThp.textContent = row.querySelector('.total-thp')?.textContent || '0';
            };

            const updateRowTotals = (row) => {
                let totalIncome = 0;
                let totalDeduction = 0;

                row.querySelectorAll('.input-income').forEach((input) => {
                    const val = parseCurrency(input.value);
                    totalIncome += val;
                    input.classList.toggle('has-value', val > 0);
                });

                row.querySelectorAll('.input-deduction').forEach((input) => {
                    const val = parseCurrency(input.value);
                    totalDeduction += val;
                    input.classList.toggle('has-value', val > 0);
                });

                const net = totalIncome - totalDeduction;
                row.dataset.totalIncome = String(totalIncome);
                row.dataset.totalDeduction = String(totalDeduction);

                const totalIncomeNode = row.querySelector('.total-income');
                const totalDeductionNode = row.querySelector('.total-deduction');
                const totalThpNode = row.querySelector('.total-thp');

                if (totalIncomeNode) totalIncomeNode.textContent = formatIdNumber(totalIncome);
                if (totalDeductionNode) totalDeductionNode.textContent = formatIdNumber(totalDeduction);
                if (totalThpNode) totalThpNode.textContent = formatIdNumber(net);

                syncDrawerTotalsIfOpen(row);
                updateGrandTotals();
            };

            const formatCurrencyPreserveCaret = (input) => {
                const raw = input.value;
                const selectionStart = input.selectionStart ?? raw.length;
                const rawBeforeCursor = raw.slice(0, selectionStart);
                let digitsBeforeCursor = (rawBeforeCursor.match(/[0-9]/g) || []).length;
                const hadCommaBeforeCursor = rawBeforeCursor.includes(',');

                let value = raw.replace(/[^0-9,]/g, '');
                const parts = value.split(',');
                let integerPart = parts[0] || '';
                const decimalPart = parts.length > 1 ? ',' + parts[1].slice(0, 2) : '';

                if (integerPart.length > 1 && integerPart.startsWith('0')) {
                    const stripped = integerPart.replace(/^0+/, '') || '0';
                    const removed = integerPart.length - stripped.length;
                    integerPart = stripped;
                    digitsBeforeCursor = Math.max(0, digitsBeforeCursor - removed);
                }

                const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                const formatted = formattedInteger + decimalPart;
                input.value = formatted;

                if (document.activeElement === input) {
                    let newPos = 0;
                    let seenDigits = 0;
                    for (let i = 0; i < formatted.length; i++) {
                        if (seenDigits >= digitsBeforeCursor && (!hadCommaBeforeCursor || formatted[i - 1] === ',')) {
                            break;
                        }
                        if (/[0-9]/.test(formatted[i])) {
                            seenDigits++;
                        }
                        newPos = i + 1;
                    }
                    if (hadCommaBeforeCursor && newPos <= formattedInteger.length) {
                        newPos = formattedInteger.length + 1;
                    }
                    try {
                        input.setSelectionRange(newPos, newPos);
                    } catch (_) {}
                }
            };

            window.formatInput = function(input) {
                formatCurrencyPreserveCaret(input);

                const row = input.closest('tr');
                if (row) {
                    recordDirtyRowField(row, input.dataset.fieldKey, input.value);
                    updateRowTotals(row);
                    if (activeDrawerRowIndex !== null && String(row.dataset.rowIndex) === String(activeDrawerRowIndex)) {
                        const fieldKey = input.dataset.fieldKey;
                        const drawerInput = quickDrawer?.querySelector(`[data-drawer-field="${fieldKey}"]`);
                        if (drawerInput && document.activeElement !== drawerInput) {
                            drawerInput.value = input.value;
                        }
                    }
                }
            };

            // Quick Input Drawer Logic
            const openQuickDrawerForRow = (rowIndex) => {
                const rows = getDataRows();
                const targetRow = rows.find((r) => String(r.dataset.rowIndex) === String(rowIndex));
                if (!targetRow || !quickDrawer) return;

                activeDrawerRowIndex = Number(rowIndex);
                lastInteractedRowKey = targetRow.dataset.rowKey || null;
                rows.forEach((r) => r.classList.toggle('is-drawer-active', r === targetRow));

                if (drawerName) drawerName.textContent = targetRow.dataset.employeeName || '-';
                if (drawerEmail) drawerEmail.textContent = targetRow.dataset.employeeEmail || '-';
                if (drawerPeriod) drawerPeriod.textContent = targetRow.dataset.periodLabel || '-';

                if (drawerStatus) {
                    const st = targetRow.dataset.status || 'BELUM_DIBUAT';
                    drawerStatus.textContent = st === 'BELUM_DIBUAT' ? 'BELUM DIBUAT' : st;
                    drawerStatus.className = 'badge-type ' + (st === 'PUBLISHED' ? 'badge-green' : (st === 'DRAFT' ? 'badge-yellow' : 'badge-red'));
                }

                const visibleRows = rows.filter((r) => r.style.display !== 'none');
                const posIndex = visibleRows.indexOf(targetRow);
                if (drawerCounter) {
                    drawerCounter.textContent = `${posIndex >= 0 ? posIndex + 1 : 1} / ${visibleRows.length}`;
                }
                if (drawerPrevBtn) drawerPrevBtn.disabled = posIndex <= 0;
                if (drawerNextBtn) drawerNextBtn.disabled = posIndex < 0 || posIndex >= visibleRows.length - 1;

                drawerInputs.forEach((dInput) => {
                    const key = dInput.dataset.drawerField;
                    const rowInput = targetRow.querySelector(`[data-field-key="${key}"]`);
                    dInput.value = rowInput ? rowInput.value : '';
                    if (key !== 'sisa_utang') {
                        dInput.classList.toggle('has-value', parseCurrency(dInput.value) > 0);
                    }
                });

                syncDrawerTotalsIfOpen(targetRow);

                quickDrawer.inert = false;
                quickDrawer.classList.add('is-open');
                quickDrawer.setAttribute('aria-hidden', 'false');
                // Hanya scroll vertikal, tanpa animasi bertumpuk; posisi horizontal pengguna dipertahankan.
                const keepScrollLeft = tableWrapper ? tableWrapper.scrollLeft : 0;
                targetRow.scrollIntoView({ block: 'nearest', behavior: 'auto' });
                if (tableWrapper) tableWrapper.scrollLeft = keepScrollLeft;
            };

            const closeQuickDrawer = () => {
                if (!quickDrawer) return;
                const focusedKey = quickDrawer.contains(document.activeElement) ? document.activeElement.dataset?.drawerField : null;
                const activeRow = focusedKey ? getDataRows().find((r) => Number(r.dataset.rowIndex) === activeDrawerRowIndex) : null;
                quickDrawer.classList.remove('is-open');
                quickDrawer.setAttribute('aria-hidden', 'true');
                quickDrawer.inert = true; // drawer tertutup tidak boleh menjadi tujuan Tab
                activeDrawerRowIndex = null;
                getDataRows().forEach((r) => r.classList.remove('is-drawer-active'));
                // Fokus yang ada di drawer dikembalikan ke sel yang sama pada baris aktif (bukan hilang ke body).
                if (focusedKey && activeRow?.isConnected) {
                    activeRow.querySelector(`[data-field-key="${focusedKey}"]`)?.focus({ preventScroll: true });
                }
            };

            const navigateDrawerRow = (delta) => {
                if (activeDrawerRowIndex === null) return;
                const visibleRows = getDataRows().filter((r) => r.style.display !== 'none');
                const currentIdx = visibleRows.findIndex((r) => Number(r.dataset.rowIndex) === activeDrawerRowIndex);
                const nextRow = visibleRows[currentIdx + delta];
                if (nextRow) {
                    openQuickDrawerForRow(nextRow.dataset.rowIndex);
                }
            };

            if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeQuickDrawer);
            if (drawerPrevBtn) drawerPrevBtn.addEventListener('click', () => navigateDrawerRow(-1));
            if (drawerNextBtn) drawerNextBtn.addEventListener('click', () => navigateDrawerRow(1));

            drawerInputs.forEach((dInput, idx) => {
                const isText = dInput.dataset.drawerField === 'sisa_utang';

                dInput.addEventListener('focus', function() {
                    if (!isText) {
                        selectWhenStillFocused(this);
                    }
                });

                dInput.addEventListener('blur', function() {
                    if (!isText && (this.value.trim() === '' || this.value.trim() === ',')) {
                        this.value = '0';
                        this.dispatchEvent(new Event('input'));
                    }
                });

                dInput.addEventListener('input', function() {
                    if (activeDrawerRowIndex === null) return;
                    if (!isText) {
                        formatCurrencyPreserveCaret(this);
                        this.classList.toggle('has-value', parseCurrency(this.value) > 0);
                    }

                    const targetRow = getDataRows().find((r) => Number(r.dataset.rowIndex) === activeDrawerRowIndex);
                    if (!targetRow) return;
                    const key = this.dataset.drawerField;
                    const rowInput = targetRow.querySelector(`[data-field-key="${key}"]`);
                    if (rowInput) {
                        rowInput.value = this.value;
                        recordDirtyRowField(targetRow, key, this.value);
                        updateRowTotals(targetRow);
                    }
                });

                dInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const nextInput = drawerInputs[idx + 1];
                        if (nextInput) {
                            nextInput.focus();
                            nextInput.select?.();
                        } else if (drawerNextBtn && !drawerNextBtn.disabled) {
                            navigateDrawerRow(1);
                            const firstInput = drawerInputs[0];
                            if (firstInput) {
                                // Sinkron: isi drawer sudah diganti oleh navigateDrawerRow di atas.
                                firstInput.focus({ preventScroll: true });
                                firstInput.select?.();
                            }
                        }
                    } else if (e.altKey && e.key === 'ArrowDown') {
                        e.preventDefault();
                        navigateDrawerRow(1);
                    } else if (e.altKey && e.key === 'ArrowUp') {
                        e.preventDefault();
                        navigateDrawerRow(-1);
                    } else if (e.key === 'Escape') {
                        closeQuickDrawer();
                    }
                });
            });

            // Delegated Event Listeners on Table Body (supports dynamically loaded AJAX rows)
            if (tableBody) {
                tableBody.addEventListener('click', function(event) {
                    const quickEditBtn = event.target.closest('[data-open-quick-edit]');
                    if (quickEditBtn) {
                        openQuickDrawerForRow(quickEditBtn.dataset.openQuickEdit);
                        const firstInput = quickDrawer?.querySelector('[data-drawer-field="gaji_pokok"]');
                        if (firstInput && quickDrawer.classList.contains('is-open')) {
                            firstInput.focus({ preventScroll: true });
                            firstInput.select();
                        }
                        return;
                    }

                    const clearBtn = event.target.closest('.open-clear-confirm');
                    if (clearBtn) {
                        pendingClearPayslipUrl = clearBtn.dataset.deleteUrl;
                        const employeeName = clearBtn.dataset.employeeName || 'Karyawan';
                        const monthLabel = clearBtn.dataset.monthLabel || '-';

                        if (clearPayslipText) {
                            clearPayslipText.textContent = `Data gaji ${employeeName} untuk periode ${monthLabel} akan dihapus. Yakin ingin melanjutkan?`;
                        }

                        toggleModalById(clearPayslipConfirmModalId, true);
                    }
                });

                tableBody.addEventListener('change', function(event) {
                    const checkbox = event.target.closest('.payroll-row-checkbox');
                    if (checkbox) {
                        recordDirtyRowCheck(checkbox.value, checkbox.checked);
                        syncSelectAllState();
                    }
                });

                let focusedRowEl = null;
                tableBody.addEventListener('focusin', function(event) {
                    const input = event.target.closest('.form-control-sm');
                    if (!input) return;

                    const isText = input.classList.contains('input-utang');
                    const tr = input.closest('tr');
                    if (tr) {
                        lastInteractedRowKey = tr.dataset.rowKey || null;
                        if (tr !== focusedRowEl) {
                            focusedRowEl?.classList.remove('is-focused-row');
                            tr.classList.add('is-focused-row');
                            focusedRowEl = tr;
                        }
                    }
                    // Nilai saat fokus: Tab tanpa mengubah isi tidak boleh dianggap perubahan.
                    input.dataset.focusValue = input.value;
                    if (!isText) {
                        selectWhenStillFocused(input);
                    }
                });

                tableBody.addEventListener('focusout', function(event) {
                    const input = event.target.closest('.form-control-sm');
                    if (!input) return;

                    const isText = input.classList.contains('input-utang');
                    const row = input.closest('tr');
                    if (!isText && (input.value.trim() === '' || input.value.trim() === ',')) {
                        input.value = '0';
                        if (row) {
                            recordDirtyRowField(row, input.dataset.fieldKey, '0');
                            updateRowTotals(row);
                        }
                    } else if (isText && row && input.value !== input.dataset.focusValue) {
                        recordDirtyRowField(row, 'sisa_utang', input.value);
                    }
                });

                tableBody.addEventListener('input', function(event) {
                    const utangInput = event.target.closest('.input-utang');
                    if (utangInput) {
                        const row = utangInput.closest('tr');
                        if (row) recordDirtyRowField(row, 'sisa_utang', utangInput.value);
                    }
                });

                tableBody.addEventListener('keydown', function(event) {
                    const input = event.target.closest('.form-control-sm');
                    if (!input) return;

                    const currentRow = input.closest('tr');
                    const fieldKey = input.dataset.fieldKey;
                    if (!currentRow || !fieldKey) return;

                    if (event.key === 'F2') {
                        event.preventDefault();
                        openQuickDrawerForRow(currentRow.dataset.rowIndex);
                        const dField = quickDrawer?.querySelector(`[data-drawer-field="${fieldKey}"]`);
                        if (dField && quickDrawer.classList.contains('is-open')) {
                            dField.focus({ preventScroll: true });
                            dField.select?.();
                        }
                        return;
                    }

                    if (event.key === 'Enter' || event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        const visibleRows = getDataRows().filter((r) => r.style.display !== 'none');
                        const rowIdx = visibleRows.indexOf(currentRow);
                        const delta = event.key === 'ArrowUp' ? -1 : 1;
                        const targetRow = visibleRows[rowIdx + delta];
                        if (targetRow) {
                            const targetInput = targetRow.querySelector(`[data-field-key="${fieldKey}"]`);
                            if (targetInput) {
                                targetInput.focus();
                                targetInput.select?.();
                            }
                        }
                    }
                });
            }

            // AJAX Automatic Live Search by Employee Name
            let searchTimer = null;
            let searchAbortController = null;

            const syncSearchHiddenInputs = (queryValue) => {
                [bulkForm, exportForm, clearPayslipForm, clearSelectedPayslipsForm].forEach((frm) => {
                    const hiddenSearch = frm?.querySelector('input[name="search"]');
                    if (hiddenSearch) hiddenSearch.value = queryValue;
                });
            };

            // Satu jalur pemuatan data (awal & pencarian): hanya respons terbaru yang dipasang.
            const workspaceBody = document.querySelector('.pw-workspace-body');
            const loadError = document.getElementById('pwLoadError');
            const loadRetryBtn = document.getElementById('pwLoadRetry');
            const exportSubmitBtn = exportForm?.querySelector('button[type="submit"]') || null;
            const loadStatus = document.createElement('div');
            loadStatus.className = 'mx-sr-only';
            loadStatus.setAttribute('role', 'status');
            workspaceBody?.before(loadStatus);
            let dataReady = tableBody?.dataset.payrollLoaded === '1';
            let tableBusy = false;
            let focusLostToLoader = false;

            const setTableBusy = (on) => {
                if (!workspaceBody) return;
                tableBusy = on;
                const hadFocus = on && workspaceBody.contains(document.activeElement);
                workspaceBody.classList.add('mx-primary');
                workspaceBody.classList.toggle('mx-loading', on);
                workspaceBody.inert = on;
                tableWrapper?.setAttribute('aria-busy', String(on));
                workspaceBody.querySelector(':scope > .mx-loader')?.remove();
                loadStatus.textContent = on ? 'Memuat data…' : '';
                if (on) {
                    const loader = document.createElement('div');
                    loader.className = 'mx-loader';
                    loader.setAttribute('aria-hidden', 'true');
                    loader.innerHTML = '<span class="mx-loader__box"><span class="mx-spinner"></span><span>Memuat data…</span></span>';
                    workspaceBody.append(loader);
                    // Permintaan yang menggantikan permintaan sebelumnya tidak boleh menghapus penanda fokus yang hilang.
                    focusLostToLoader = focusLostToLoader || hadFocus;
                } else if (focusLostToLoader) {
                    // Pulihkan fokus hanya bila pengguna belum berpindah: fokus masih "hilang" (body). Jika pengguna
                    // sudah menekan Tab/klik ke elemen lain, posisi terbaru itu dihormati.
                    const current = document.activeElement;
                    if (tableWrapper && (!current || current === document.body)) {
                        tableWrapper.tabIndex = -1;
                        tableWrapper.focus({ preventScroll: true });
                    }
                    focusLostToLoader = false;
                }
                // Tindakan yang membutuhkan tabel siap dikunci selama data belum siap / sedang diperbarui.
                const actionsOn = dataReady && !on;
                [btnSaveDraft, btnPublishEmail, exportSubmitBtn].forEach((btn) => { if (btn) btn.disabled = !actionsOn; });
                if (actionsOn) {
                    syncSelectAllState();
                } else if (btnClearSelected) {
                    btnClearSelected.disabled = true;
                }
            };

            const performAjaxSearch = async (options = {}) => {
                if (!filterForm || !tableBody) return;

                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams(new FormData(filterForm));
                url.search = params.toString();

                syncSearchHiddenInputs(searchInput?.value || '');

                searchAbortController?.abort();
                const currentController = new AbortController();
                searchAbortController = currentController;

                if (loadError) loadError.hidden = true;
                setTableBusy(true);

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                        signal: currentController.signal,
                    });

                    if (response.status === 401 || response.status === 419) {
                        window.location.assign(url);
                        return;
                    }
                    if (!response.ok) throw new Error('Gagal memuat data payroll');

                    const htmlText = await response.text();
                    if (searchAbortController !== currentController) return;

                    const doc = new DOMParser().parseFromString(htmlText, 'text/html');
                    const nextTbody = doc.getElementById('payrollTableBody');
                    if (!nextTbody || nextTbody.dataset.payrollLoaded !== '1') {
                        // Bukan data payroll (mis. halaman login setelah sesi berakhir): jangan dipasang.
                        if (response.redirected) {
                            window.location.assign(url);
                            return;
                        }
                        throw new Error('Respons data payroll tidak lengkap');
                    }
                    const nextStatusPills = doc.getElementById('pwStatusPills');
                    const currentStatusPills = document.getElementById('pwStatusPills');
                    const nextPeriodVal = doc.getElementById('pwPeriodHeaderValue');
                    const currentPeriodVal = document.getElementById('pwPeriodHeaderValue');

                    closeQuickDrawer();
                    tableBody.replaceChildren(...Array.from(nextTbody.childNodes));
                    tableBody.dataset.payrollLoaded = '1';
                    dataReady = true;
                    restoreDirtyRowsState();
                    getDataRows().forEach((row) => updateRowTotals(row));
                    updateGrandTotals();
                    syncSelectAllState();
                    applyColumnViewMode(currentColumnMode);

                    if (currentStatusPills && nextStatusPills) {
                        currentStatusPills.replaceChildren(...Array.from(nextStatusPills.childNodes));
                    }

                    if (currentPeriodVal && nextPeriodVal) {
                        currentPeriodVal.textContent = nextPeriodVal.textContent;
                    }

                    if (!options.initial) window.history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name === 'AbortError' || searchAbortController !== currentController) return;
                    // Tanpa log payload; data terakhir (bila ada) beserta inputnya dipertahankan.
                    if (loadError) loadError.hidden = false;
                } finally {
                    if (searchAbortController === currentController) {
                        setTableBusy(false);
                    }
                }
            };

            loadRetryBtn?.addEventListener('click', () => performAjaxSearch({ initial: !dataReady }));

            // Back/Forward (bfcache): jangan tinggalkan spinner atau kontrol terkunci.
            window.addEventListener('pageshow', (event) => {
                if (event.persisted && (tableBusy || !dataReady)) performAjaxSearch({ initial: !dataReady });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => performAjaxSearch(), 300);
                });

                searchInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        clearTimeout(searchTimer);
                        performAjaxSearch();
                    }
                });
            }

            // Mode Switcher Buttons
            modeButtons.forEach((btn) => {
                btn.addEventListener('click', function() {
                    applyColumnViewMode(this.dataset.colMode);
                });
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    getRowCheckboxes().forEach((checkbox) => {
                        if (checkbox.closest('tr')?.style.display === 'none') return;
                        checkbox.checked = this.checked;
                        recordDirtyRowCheck(checkbox.value, this.checked);
                    });
                    syncSelectAllState();
                });
            }

            if (thrOnlyToggle) {
                thrOnlyToggle.addEventListener('change', function() {
                    toggleThrOnlyMode(this.checked);
                    if (thrOnlyModeInput) {
                        thrOnlyModeInput.value = this.checked ? '1' : '0';
                    }
                });
            }

            if (exportForm) {
                exportForm.addEventListener('submit', function(event) {
                    if (!dataReady || tableBusy) {
                        event.preventDefault();
                        return;
                    }
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);
                    if (checkedRows.length === 0) {
                        event.preventDefault();
                        window.showToast('Pilih minimal satu data karyawan yang akan diunduh.', 'warning');
                        return;
                    }

                    appendSelectedRowsToContainer(selectedRowsContainer, checkedRows);
                });
            }

            if (confirmClearPayslipSubmitButton && clearPayslipForm) {
                confirmClearPayslipSubmitButton.addEventListener('click', function() {
                    if (!pendingClearPayslipUrl) {
                        toggleModalById(clearPayslipConfirmModalId, false);
                        return;
                    }

                    clearPayslipForm.action = pendingClearPayslipUrl;
                    this.disabled = true;
                    clearPayslipForm.submit();
                });
            }

            if (btnClearSelected) {
                btnClearSelected.addEventListener('click', function() {
                    if (!dataReady || tableBusy) return;
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);
                    if (checkedRows.length === 0) {
                        return;
                    }

                    if (clearSelectedConfirmText) {
                        clearSelectedConfirmText.textContent = `${checkedRows.length} data gaji terpilih akan dihapus (clear dari payslips). Yakin ingin melanjutkan?`;
                    }

                    toggleModalById(clearSelectedConfirmModalId, true);
                });
            }

            if (confirmClearSelectedSubmitButton && clearSelectedPayslipsForm) {
                confirmClearSelectedSubmitButton.addEventListener('click', function() {
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);
                    if (checkedRows.length === 0) {
                        toggleModalById(clearSelectedConfirmModalId, false);
                        return;
                    }

                    appendSelectedRowsToContainer(clearSelectedRowsContainer, checkedRows);
                    this.disabled = true;
                    clearSelectedPayslipsForm.submit();
                });
            }

            if (btnSaveDraft && bulkForm) {
                btnSaveDraft.addEventListener('click', function() {
                    if (isSubmitting || !dataReady || tableBusy) return;

                    const checkedRowKeys = getRowCheckboxes()
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    const editedRowKeys = Array.from(dirtyRowsState.entries())
                        .filter(([, st]) => st && st.edited)
                        .map(([key]) => key);

                    let targetRowKeys = checkedRowKeys.length > 0
                        ? checkedRowKeys
                        : (editedRowKeys.length > 0 ? editedRowKeys : (lastInteractedRowKey ? [lastInteractedRowKey] : []));

                    targetRowKeys = Array.from(new Set(targetRowKeys));

                    if (targetRowKeys.length === 0) {
                        toggleModalById(publishWarningModalId, true);
                        return;
                    }

                    if (!submitBulkTargets('draft', targetRowKeys)) return;
                    isSubmitting = true;
                    bulkForm.submit();
                });
            }

            if (btnPublishEmail && bulkForm) {
                btnPublishEmail.addEventListener('click', function() {
                    if (isSubmitting || !dataReady || tableBusy) return;

                    const checkedRows = getRowCheckboxes().filter(cb => cb.checked);
                    if (checkedRows.length === 0) {
                        toggleModalById(publishWarningModalId, true);
                        return;
                    }

                    if (publishConfirmCount) {
                        publishConfirmCount.textContent = String(checkedRows.length);
                    }
                    toggleModalById(publishConfirmModalId, true);
                });
            }

            if (confirmPublishSubmitButton && bulkForm) {
                confirmPublishSubmitButton.addEventListener('click', function() {
                    if (isSubmitting || !dataReady || tableBusy) return;

                    // Target publish wajib eksplisit: baris yang dicentang. Payload lengkap divalidasi dulu,
                    // baru modal loading dibuka; bila tidak lengkap, tidak ada yang dikirim.
                    const checkedKeys = getRowCheckboxes().filter(cb => cb.checked).map(cb => cb.value);
                    if (checkedKeys.length === 0) {
                        toggleModalById(publishConfirmModalId, false);
                        toggleModalById(publishWarningModalId, true);
                        return;
                    }
                    if (!submitBulkTargets('publish', checkedKeys)) {
                        toggleModalById(publishConfirmModalId, false);
                        return;
                    }

                    isSubmitting = true;
                    this.disabled = true;

                    toggleModalById(publishConfirmModalId, false);
                    toggleModalById(publishLoadingModalId, true);

                    bulkForm.submit();
                });
            }

            document.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
                button.addEventListener('click', function() {
                    const modal = this.closest('[id^="modal-"]');
                    if (!modal) return;
                    modal.style.display = 'none';
                    document.body.style.overflow = '';

                    if (modal.id === clearPayslipConfirmModalId) {
                        pendingClearPayslipUrl = null;
                        if (confirmClearPayslipSubmitButton) {
                            confirmClearPayslipSubmitButton.disabled = false;
                        }
                    }

                    if (modal.id === publishConfirmModalId) {
                        if (confirmPublishSubmitButton) {
                            confirmPublishSubmitButton.disabled = false;
                        }
                    }

                    if (modal.id === clearSelectedConfirmModalId) {
                        if (confirmClearSelectedSubmitButton) {
                            confirmClearSelectedSubmitButton.disabled = false;
                        }
                    }
                });
            });

            getDataRows().forEach((row) => {
                updateRowTotals(row);
            });

            syncSelectAllState();
            applyColumnViewMode('all');

            // Kerangka sudah tampil: ambil data payroll (sekali) setelah semua handler terpasang.
            if (!dataReady) {
                performAjaxSearch({ initial: true });
            }
        });
    </script>

    <style>
        /* =========================================================
           1-PAGE FULL-BLEED OVERRIDES FOR /hr/payroll
           Removes outer container gaps between sidebar & table
           ========================================================= */
        .main-content.pw-main-fullbleed,
        .main-content:has(.payroll-page-workspace) {
            overflow: hidden !important;
            background: #f8fafc !important;
        }

        .content-wrapper.pw-wrapper-fullbleed,
        .content-wrapper:has(.payroll-page-workspace) {
            max-width: none !important;
            width: 100% !important;
            height: 100vh !important;
            height: 100dvh !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .content-wrapper.pw-wrapper-fullbleed > .topbar,
        .content-wrapper:has(.payroll-page-workspace) > .topbar {
            margin-bottom: 0 !important;
            padding: 8px 14px !important;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            gap: 10px;
            flex-shrink: 0;
            min-height: 48px;
        }

        /* =========================================================
           TOPBAR HEADER
           ========================================================= */
        .pw-topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex: 1;
            min-width: 0;
            flex-wrap: wrap;
        }

        .pw-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex-wrap: wrap;
        }

        .pw-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pw-page-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        .pw-subtitle-pill {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #e0f2fe;
            color: #0369a1;
            letter-spacing: 0.03em;
        }

        .pw-status-pills {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pw-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 500;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .pw-stat-chip strong {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pw-stat-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pw-dot-slate { background: #64748b; }
        .pw-dot-green { background: #16a34a; }
        .pw-dot-yellow { background: #ca8a04; }
        .pw-dot-red { background: #dc2626; }

        .pw-topbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pw-inline-form {
            display: inline-flex;
            margin: 0;
        }

        /* =========================================================
           WORKSPACE CONTAINER & COMPACT TOOLBAR
           ========================================================= */
        .payroll-page-workspace {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            overflow: hidden;
        }

        .pw-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .pw-filter-bar {
            margin: 0;
            flex: 0 1 620px;
            min-width: 0;
        }

        .pw-period-banner {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
        }

        .pw-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
            border: 1px solid #bfdbfe;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            white-space: nowrap;
        }

        .pw-period-icon {
            color: #0284c7;
            flex-shrink: 0;
        }

        .pw-period-tag {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #0369a1;
            text-transform: uppercase;
            padding-right: 6px;
            border-right: 1px solid #93c5fd;
        }

        .pw-period-value {
            font-size: 13.5px;
            font-weight: 800;
            color: #0a3d62;
            letter-spacing: -0.01em;
        }

        .pw-inline-period-pill {
            display: inline-block;
            margin-left: 4px;
            padding: 0 5px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #334155;
            font-size: 9.5px;
            font-weight: 700;
        }

        .pw-filter-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .pw-filter-item {
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            height: 30px;
            padding: 0 2px 0 7px;
            margin: 0;
            transition: border-color 0.15s ease;
        }

        .pw-filter-item:focus-within {
            border-color: #145da0;
            box-shadow: 0 0 0 2px rgba(20, 93, 160, 0.12);
        }

        .pw-filter-tag {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-right: 4px;
            white-space: nowrap;
            user-select: none;
        }

        .pw-select {
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            padding: 2px 4px;
            height: 28px;
            outline: none;
            cursor: pointer;
        }

        .pw-filter-item--pt .pw-select {
            max-width: 145px;
        }

        .pw-filter-item--div .pw-select {
            max-width: 135px;
        }

        .pw-search-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 185px;
            flex: 1;
            max-width: 260px;
        }

        .pw-search-icon {
            position: absolute;
            left: 8px;
            color: #94a3b8;
            pointer-events: none;
        }

        .pw-search-input {
            width: 100%;
            height: 30px;
            padding: 0 26px 0 27px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            font-family: inherit;
            font-size: 12px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .pw-search-input:focus {
            border-color: #145da0;
            box-shadow: 0 0 0 2px rgba(20, 93, 160, 0.12);
        }

        .pw-search-spinner {
            position: absolute;
            right: 8px;
            width: 13px;
            height: 13px;
            border: 2px solid #cbd5e1;
            border-top-color: #145da0;
            border-radius: 50%;
            animation: payroll-spin 0.7s linear infinite;
            pointer-events: none;
        }

        .pw-search-spinner[hidden] {
            display: none !important;
        }

        .pw-placeholder-cell {
            height: 160px;
        }

        /* Indikator loading di tengah viewport (bukan di tengah area tabel); hanya indikator yang tampil,
           tanpa backdrop dan tidak menangkap klik. Area data tetap dikunci lewat inert + .mx-loading. */
        .pw-workspace-body > .mx-loader {
            position: fixed;
            inset: auto;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: auto;
            max-width: calc(100vw - 32px);
            padding: 0;
            background: transparent;
            border-radius: 0;
            pointer-events: none;
            z-index: 1000;
        }

        .pw-noscript {
            padding: 10px 14px;
            background: #fffbeb;
            color: #92400e;
            font-size: 13px;
        }

        .pw-load-error {
            position: absolute;
            top: 56px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 6;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #fecaca;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .12);
            color: #b91c1c;
            font-size: 13.5px;
            font-weight: 600;
        }

        .pw-load-error[hidden] {
            display: none !important;
        }

        .pw-view-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .pw-mode-tabs {
            display: inline-flex;
            align-items: center;
            background: #e2e8f0;
            padding: 2px;
            border-radius: 7px;
            gap: 2px;
        }

        .pw-mode-btn {
            border: none;
            background: transparent;
            color: #475569;
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 9px;
            border-radius: 5px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.12s ease;
        }

        .pw-mode-btn:hover {
            color: #0f172a;
        }

        .pw-mode-btn.is-active {
            background: #ffffff;
            color: #0a3d62;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .thr-only-toggle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 28px;
            padding: 0 9px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #fff;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
        }

        .thr-only-toggle:has(input:checked) {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        .thr-only-toggle input {
            width: 13px;
            height: 13px;
            margin: 0;
            cursor: pointer;
        }

        /* =========================================================
           SPREADSHEET TABLE & SPLIT WORKSPACE
           ========================================================= */
        .pw-bulk-form {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow: hidden;
        }

        .pw-workspace-body {
            flex: 1;
            min-height: 0;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        .table-wrapper {
            flex: 1;
            min-width: 0;
            height: 100%;
            overflow: auto;
            background: #ffffff;
            scrollbar-width: thin;
        }

        /* Fokus keyboard yang di-scroll otomatis tidak boleh tertutup kolom sticky kiri (36+215px) / kanan (105+64px). */
        .table-wrapper {
            scroll-padding-left: 251px;
            scroll-padding-right: 169px;
        }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-variant-numeric: tabular-nums;
        }

        .custom-table[data-view-mode="compact"],
        .custom-table[data-view-mode="deduction"],
        .custom-table[data-view-mode="thr"] {
            min-width: 100%;
        }

        .custom-table[data-view-mode="income"] {
            min-width: 1280px;
        }

        .custom-table[data-view-mode="all"] {
            min-width: 2150px;
        }

        .custom-table th {
            background: #f1f5f9;
            padding: 6px 8px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 20;
            white-space: nowrap;
            user-select: none;
        }

        .custom-table thead tr:first-child th {
            height: 28px;
        }

        .custom-table thead tr.pw-subhead-row th {
            top: 28px;
            height: 28px;
            z-index: 19;
            text-align: right;
            background: #f8fafc;
        }

        .th-group-income {
            background: #ecfdf5 !important;
            color: #065f46 !important;
            border-bottom-color: #a7f3d0 !important;
        }

        .th-group-deduction {
            background: #fef2f2 !important;
            color: #991b1b !important;
            border-bottom-color: #fecaca !important;
        }

        .th-subtotal-income {
            background: #f0fdf4 !important;
            color: #166534 !important;
        }

        .th-subtotal-deduction {
            background: #fef2f2 !important;
            color: #991b1b !important;
        }

        .custom-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            font-size: 12px;
            color: #1e293b;
            vertical-align: middle;
            background: #ffffff;
            height: 36px;
        }

        .custom-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .custom-table tbody tr:hover td {
            background: #f1f5f9;
        }

        .custom-table tbody tr.is-checked td {
            background: #eff6ff !important;
        }

        .custom-table tbody tr.is-focused-row td,
        .custom-table tbody tr.is-drawer-active td {
            background: #f0f9ff !important;
        }

        /* Sticky Left Columns */
        .col-sticky-1 {
            position: sticky;
            left: 0;
            z-index: 30;
            width: 36px;
            min-width: 36px;
            max-width: 36px;
            padding: 0 4px !important;
        }

        .col-sticky-2 {
            position: sticky;
            left: 36px;
            z-index: 30;
            width: 215px;
            min-width: 215px;
            max-width: 215px;
            border-right: 2px solid #cbd5e1 !important;
        }

        thead th.col-sticky-1,
        thead th.col-sticky-2 {
            z-index: 50;
            background: #f1f5f9;
        }

        /* Sticky Right Columns (THP + Aksi always visible!) */
        .col-sticky-right-1 {
            position: sticky;
            right: 0;
            z-index: 30;
            width: 64px;
            min-width: 64px;
            max-width: 64px;
            border-left: 1px solid #e2e8f0;
        }

        .col-sticky-right-2 {
            position: sticky;
            right: 64px;
            z-index: 30;
            width: 105px;
            min-width: 105px;
            border-left: 2px solid #cbd5e1 !important;
            background: #f8fafc !important;
        }

        thead th.col-sticky-right-1,
        thead th.col-sticky-right-2 {
            z-index: 50;
            background: #f1f5f9 !important;
        }

        .col-meta {
            width: 82px;
            min-width: 78px;
            white-space: nowrap;
        }

        .pw-period-text {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
        }

        .pw-th-emp {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .pw-th-hint {
            font-size: 9px;
            font-weight: 500;
            color: #64748b;
            text-transform: none;
            letter-spacing: 0;
        }

        .pw-emp-cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            min-width: 0;
        }

        .pw-emp-info {
            min-width: 0;
            flex: 1;
        }

        .pw-emp-name-row {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .pw-row-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-block;
            cursor: help;
        }

        .pw-row-dot--green {
            background: #16a34a;
            box-shadow: 0 0 0 2px #dcfce7;
        }

        .pw-row-dot--yellow {
            background: #ca8a04;
            box-shadow: 0 0 0 2px #fef9c3;
        }

        .pw-row-dot--red {
            background: #dc2626;
            box-shadow: 0 0 0 2px #fee2e2;
        }

        .pw-emp-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
            min-width: 0;
        }

        .pw-emp-email {
            font-size: 10px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .pw-quick-edit-btn {
            flex-shrink: 0;
            padding: 2px 7px;
            border-radius: 5px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0a3d62;
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.12s ease;
        }

        .pw-quick-edit-btn:hover,
        tr.is-drawer-active .pw-quick-edit-btn {
            background: #0a3d62;
            border-color: #0a3d62;
            color: #ffffff;
        }

        /* Spreadsheet Cell Inputs */
        .pw-input-td {
            padding: 0 !important;
            min-width: 86px;
        }

        .col-utang {
            min-width: 130px;
        }

        .form-control-sm {
            width: 100%;
            height: 35px;
            padding: 4px 7px;
            font-family: inherit;
            font-size: 12px;
            font-variant-numeric: tabular-nums;
            border: 1.5px solid transparent;
            border-radius: 0;
            background-color: transparent;
            color: #64748b;
            text-align: right;
            transition: background-color 0.1s ease, border-color 0.1s ease, color 0.1s ease;
        }

        .form-control-sm.has-value {
            color: #0f172a;
            font-weight: 600;
        }

        .form-control-sm.input-deduction.has-value {
            color: #b91c1c;
        }

        .form-control-sm.input-utang {
            text-align: left;
            color: #0f172a;
        }

        .form-control-sm:hover {
            background-color: rgba(20, 93, 160, 0.04);
        }

        .form-control-sm:focus {
            outline: none;
            border-color: #16a34a;
            background-color: #ffffff !important;
            color: #0f172a;
            font-weight: 700;
            box-shadow: inset 0 0 0 1px #16a34a;
        }

        .custom-table td:has(.form-control-sm:focus) {
            background-color: #f0fdf4 !important;
        }

        .pw-subtotal-td {
            text-align: right;
            padding: 4px 8px !important;
            min-width: 98px;
            white-space: nowrap;
        }

        .pw-subtotal-income {
            background: #f0fdf4 !important;
        }

        .pw-subtotal-deduction {
            background: #fef2f2 !important;
        }

        .pw-thp-td {
            padding: 4px 8px !important;
            white-space: nowrap;
        }

        .text-success { color: #15803d; }
        .text-danger { color: #b91c1c; }
        .text-primary { color: #0a3d62; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .fw-bold { font-weight: 700; }
        .text-muted { color: #64748b; }

        /* =========================================================
           1-SCREEN QUICK INPUT DRAWER (SLIDE-OVER PANEL)
           ========================================================= */
        .pw-quick-drawer {
            width: 0;
            flex-shrink: 0;
            background: #ffffff;
            border-left: 1px solid transparent;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 60;
        }

        .pw-quick-drawer.is-open {
            width: 480px;
            max-width: 92vw;
            border-left-color: #cbd5e1;
            box-shadow: -8px 0 24px rgba(15, 23, 42, 0.08);
        }

        .pw-drawer-header {
            padding: 10px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-shrink: 0;
        }

        .pw-drawer-emp {
            min-width: 0;
            flex: 1;
        }

        .pw-drawer-title-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pw-drawer-name {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pw-drawer-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pw-drawer-nav {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .pw-nav-btn {
            height: 26px;
            padding: 0 8px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background: #ffffff;
            color: #334155;
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .pw-nav-btn:hover:not(:disabled) {
            background: #f1f5f9;
            color: #0f172a;
        }

        .pw-nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .pw-drawer-pos {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            padding: 0 4px;
            font-variant-numeric: tabular-nums;
        }

        .pw-drawer-close {
            width: 26px;
            height: 26px;
            border: none;
            border-radius: 5px;
            background: transparent;
            color: #64748b;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
        }

        .pw-drawer-close:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .pw-drawer-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 8px 14px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        .pw-summary-box {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .pw-summary-box--income { background: #f0fdf4; border-color: #bbf7d0; }
        .pw-summary-box--deduction { background: #fef2f2; border-color: #fecaca; }
        .pw-summary-box--thp { background: #eff6ff; border-color: #bfdbfe; }

        .pw-summary-label {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        .pw-summary-val {
            font-size: 13px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pw-drawer-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .pw-section-head {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 4px 8px;
            border-radius: 5px;
            margin-bottom: 8px;
        }

        .pw-section-head--income {
            background: #ecfdf5;
            color: #065f46;
        }

        .pw-section-head--deduction {
            background: #fef2f2;
            color: #991b1b;
        }

        .pw-drawer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 7px 8px;
        }

        .pw-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin: 0;
        }

        .pw-field--wide {
            grid-column: span 3;
        }

        .pw-field-label {
            font-size: 10.5px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pw-field-input {
            width: 100%;
            height: 30px;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-family: inherit;
            font-size: 12px;
            font-variant-numeric: tabular-nums;
            text-align: right;
            color: #475569;
            background: #f8fafc;
            outline: none;
            transition: all 0.12s ease;
        }

        .pw-field-input.has-value {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            border-color: #94a3b8;
        }

        .pw-field-input--deduction.has-value {
            color: #b91c1c;
        }

        .pw-field-input--text {
            text-align: left;
            color: #0f172a;
            background: #ffffff;
        }

        .pw-field-input:focus {
            background: #ffffff;
            border-color: #145da0;
            box-shadow: 0 0 0 2px rgba(20, 93, 160, 0.15);
            color: #0f172a;
            font-weight: 700;
        }

        /* =========================================================
           BADGES, BUTTONS & FOOTER BAR
           ========================================================= */
        .selected-info {
            display: inline-flex;
            align-items: center;
            margin-right: 2px;
        }

        .selected-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 22px;
            padding: 0 7px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
        }

        .selected-label {
            margin-left: 5px;
            font-size: 11.5px;
            color: #4b5563;
            font-weight: 600;
        }

        .badge-type {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 9.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }

        .btn-action {
            height: 30px;
            padding: 0 11px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 6px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.15s ease;
            white-space: nowrap;
            cursor: pointer;
        }

        .btn-action:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-action-primary {
            background: #4f46e5;
            color: #ffffff;
            border-color: #4f46e5;
        }

        .btn-action-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
            color: #ffffff;
        }

        .btn-action-outline-success {
            color: #15803d;
            border-color: #16a34a;
            background: #f0fdf4;
        }

        .btn-action-outline-success:hover {
            background: #dcfce7;
            border-color: #15803d;
            color: #14532d;
        }

        .btn-action-clear {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fff1f2;
        }

        .btn-action-clear:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            color: #991b1b;
        }

        .pw-clear-row-btn {
            height: 24px;
            padding: 0 8px;
            font-size: 11px;
        }

        .btn-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
        }

        .bulk-footer-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 14px;
            border-top: 1px solid #cbd5e1;
            background: #f8fafc;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .pw-footer-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #334155;
            flex-wrap: wrap;
        }

        .pw-grand-item strong {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .pw-grand-sep {
            color: #cbd5e1;
        }

        .pw-kbd-hint {
            font-size: 11px;
            color: #64748b;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .pw-footer-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            padding: 40px 20px !important;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
        }

        .payroll-loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-top-color: #1e4a8d;
            border-radius: 9999px;
            display: inline-block;
            animation: payroll-spin 0.8s linear infinite;
        }

        .modal-btn-light {
            padding: 9px 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-btn-primary {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: #1e4a8d;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-btn-danger {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: #dc2626;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        @keyframes payroll-spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 1024px) {
            .pw-kbd-hint {
                display: none;
            }

            .pw-quick-drawer.is-open {
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: min(440px, 95vw);
            }
        }

        @media (max-width: 768px) {
            .pw-drawer-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .pw-field--wide {
                grid-column: span 2;
            }

            .pw-footer-right {
                width: 100%;
            }

            .pw-footer-right .btn-action {
                flex: 1;
            }
        }
    </style>
</x-app>
