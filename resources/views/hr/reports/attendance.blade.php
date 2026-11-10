<x-app title="Rekap Kehadiran Bulanan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Kehadiran Bulanan</h1>
                <p class="section-subtitle">Rekap keterlambatan, izin, dan cuti karyawan per periode cut-off (26–25)</p>
            </div>
        </div>
    </x-slot>

    @php
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $columns = [
            ['heading' => 'TELAT', 'key' => 'TELAT'],
            ['heading' => 'POT UM', 'key' => null],
            ['heading' => 'IZIN TELAT', 'key' => 'IZIN_TELAT'],
            ['heading' => 'SET HARI', 'key' => null],
            ['heading' => 'CUTI', 'key' => 'CUTI'],
            ['heading' => 'SAKIT', 'key' => 'SAKIT'],
            ['heading' => 'IZIN', 'key' => 'IZIN'],
            ['heading' => 'CUTI KHUSUS', 'key' => 'CUTI_KHUSUS'],
            ['heading' => 'ALPHA', 'key' => null],
            ['heading' => 'LIBUR SPV', 'key' => 'OFF_SPV'],
        ];

        $isCurrentPeriod = ($month === (int) $now->month && $year === (int) $now->year);
        $activeCount = (int) (! $isCurrentPeriod) + (int) ($search !== '') + (int) (bool) $ptId + (int) (bool) $positionId;

        $sharedFilterParams = array_filter([
            'q' => $search !== '' ? $search : null,
            'pt_id' => $ptId ?: null,
            'position_id' => $positionId ?: null,
        ], static fn ($value) => $value !== null);

        $canGoPrev = ! ($year <= 2 && $month === 1);
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear = $month === 1 ? $year - 1 : $year;
        $prevUrl = $canGoPrev
            ? route('hr.reports.attendance', array_merge($sharedFilterParams, ['month' => $prevMonth, 'year' => $prevYear]))
            : null;

        $canGoNext = ! ($year >= 9999 && $month === 12);
        $nextMonth = $month === 12 ? 1 : $month + 1;
        $nextYear = $month === 12 ? $year + 1 : $year;
        $nextUrl = $canGoNext
            ? route('hr.reports.attendance', array_merge($sharedFilterParams, ['month' => $nextMonth, 'year' => $nextYear]))
            : null;

        $totals = [
            'TELAT' => 0,
            'IZIN_TELAT' => 0,
            'CUTI' => 0,
            'SAKIT' => 0,
            'IZIN' => 0,
            'CUTI_KHUSUS' => 0,
            'OFF_SPV' => 0,
        ];
        $rowsWithRecords = 0;

        foreach ($items as $emp) {
            $empReport = $report[$emp->id] ?? [];
            $rowSum = 0;
            foreach ($totals as $catKey => $val) {
                $c = count($empReport[$catKey] ?? []);
                $totals[$catKey] += $c;
                $rowSum += $c;
            }
            if ($rowSum > 0 || ($empReport['incomplete'] ?? false)) {
                $rowsWithRecords++;
            }
        }
    @endphp

    <div class="report-filter-card">
        @if($errors->any())
            <div class="report-errors" role="alert">Parameter filter tidak valid. Periksa pilihan dan coba lagi.</div>
        @endif

        <form method="GET" action="{{ route('hr.reports.attendance') }}" id="reportFilterForm">
            <div class="report-filter-grid">
                <label class="report-field report-field--search">
                    <span class="report-label">Cari Karyawan</span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama karyawan..." maxlength="255" autocomplete="off" data-report-live-search>
                </label>

                <label class="report-field">
                    <span class="report-label">Bulan</span>
                    <select name="month">
                        @foreach($months as $number => $label)
                            <option value="{{ $number }}" @selected($month === $number)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="report-field report-field--year">
                    <span class="report-label">Tahun</span>
                    <input type="number" name="year" min="2" max="9999" value="{{ $year }}">
                </label>

                <label class="report-field">
                    <span class="report-label">PT</span>
                    <select name="pt_id">
                        <option value="">Semua PT</option>
                        @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" @selected((string) $ptId === (string) $pt->id)>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="report-field">
                    <span class="report-label">Jabatan</span>
                    <select name="position_id">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected((string) $positionId === (string) $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="report-actions">
                    <button type="submit" class="report-btn-submit">Terapkan</button>
                    <span data-report-reset-wrap>
                        <a class="report-btn-reset" href="{{ route('hr.reports.attendance') }}">
                            Reset
                            @if($activeCount > 0)
                                <span class="report-badge" title="{{ $activeCount }} Filter Aktif">{{ $activeCount }}</span>
                            @endif
                        </a>
                    </span>
                </div>
            </div>
        </form>
    </div>

    <div data-report-results aria-live="polite" aria-busy="false">
    <div class="report-meta-bar">
        <div class="report-meta-left">
            <p class="report-period">
                <strong>Periode:</strong> {{ $start->day }} {{ $months[$start->month] }} {{ $start->year }} – {{ $end->day }} {{ $months[$end->month] }} {{ $end->year }}
            </p>
            <span class="report-meta-sep" aria-hidden="true">·</span>
            <span class="report-count-text" data-visible-counter>{{ $items->count() }} karyawan</span>
            @if($activeCount > 0)
                <span class="report-meta-sep" aria-hidden="true">·</span>
                <span class="report-active-note">{{ $activeCount }} Filter Aktif</span>
            @endif
        </div>

        <div class="report-meta-right">
            @if($items->isNotEmpty())
                <div class="report-view-switch" role="group" aria-label="Filter baris cepat">
                    <button type="button" class="report-switch-btn is-active" data-quick-view="all">Semua ({{ $items->count() }})</button>
                    <button type="button" class="report-switch-btn" data-quick-view="records">Ada Catatan ({{ $rowsWithRecords }})</button>
                </div>
            @endif

            <div class="report-month-nav" role="group" aria-label="Navigasi periode">
                @if($prevUrl)
                    <a href="{{ $prevUrl }}" class="report-nav-link">&lsaquo; {{ $months[$prevMonth] }}</a>
                @endif
                @if($nextUrl)
                    <a href="{{ $nextUrl }}" class="report-nav-link">{{ $months[$nextMonth] }} &rsaquo;</a>
                @endif
            </div>
        </div>
    </div>

    <p class="report-note">
        Klik angka pada tabel untuk melihat rincian tanggal. Kolom <strong>POT UM</strong>, <strong>SET HARI</strong>, dan <strong>ALPHA</strong> (—) belum tersedia. Tanda <strong>*</strong> pada TELAT menunjukkan data historis belum lengkap.
    </p>

    <div class="report-table-card">
        @if($items->isEmpty())
            <div class="report-empty">
                <h3>Belum Ada Data Karyawan</h3>
                <p>Tidak ada karyawan yang sesuai dengan filter yang dipilih.</p>
            </div>
        @else
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-name">NAMA</th>
                            <th scope="col" class="col-position">JABATAN</th>
                            @foreach($columns as $col)
                                <th scope="col" class="col-num {{ $col['key'] === null ? 'col-muted' : '' }}">{{ $col['heading'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $empReport = $report[$item->id] ?? [];
                                $isIncomplete = (bool) ($empReport['incomplete'] ?? false);
                                $rowTotal = 0;
                                foreach ($totals as $catKey => $unused) {
                                    $rowTotal += count($empReport[$catKey] ?? []);
                                }
                                $hasRecords = $rowTotal > 0 || $isIncomplete;
                                $isInactive = $item->status !== null && ! $item->isActive();
                            @endphp
                            <tr data-report-row data-has-records="{{ $hasRecords ? '1' : '0' }}">
                                <td class="report-identity">
                                    <div class="report-emp-name">
                                        {{ $item->name }}
                                        @if($isInactive)
                                            <span class="report-status-tag">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="report-emp-pt">{{ $item->profile?->pt?->name ?? '—' }}</div>
                                </td>
                                <td class="report-position">{{ $item->position?->name ?? '—' }}</td>

                                @foreach($columns as $col)
                                    @php
                                        $category = $col['key'];
                                        $count = $category ? count($empReport[$category] ?? []) : null;
                                    @endphp
                                    <td class="report-cell {{ $category === null ? 'report-cell-muted' : '' }}">
                                        @if($category === null)
                                            <span class="report-dash">—</span>
                                        @elseif($count > 0)
                                            <button
                                                type="button"
                                                class="report-detail-button"
                                                data-report-url="{{ route('hr.reports.attendance.detail', ['employee' => $item->id, 'month' => $month, 'year' => $year, 'category' => $category]) }}"
                                                data-report-count="{{ $count }}"
                                                aria-label="Rincian {{ $category }} {{ $item->name }}">{{ $count }}</button>
                                        @else
                                            <span class="report-zero">0</span>
                                        @endif
                                        @if($category === 'TELAT' && $isIncomplete)
                                            <span class="report-incomplete" title="Data historis belum lengkap">*</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="report-tfoot-label" colspan="2">Total Hari Periode</td>
                            @foreach($columns as $col)
                                @php
                                    $catKey = $col['key'];
                                    $colSum = $catKey ? ($totals[$catKey] ?? 0) : null;
                                @endphp
                                <td class="report-tfoot-cell {{ $catKey === null ? 'report-cell-muted' : '' }}">
                                    @if($catKey === null)
                                        <span class="report-dash">—</span>
                                    @else
                                        <span class="{{ $colSum > 0 ? 'report-total-num' : 'report-zero' }}">{{ $colSum }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="report-empty" id="report-quick-empty" hidden>
                <h3>Tidak Ada Catatan</h3>
                <p>Tidak ada karyawan yang memiliki catatan kehadiran pada periode ini.</p>
            </div>
        @endif
    </div>
    </div>

    <div class="report-modal" id="report-detail-modal" hidden>
        <div class="report-modal-backdrop" data-report-close></div>
        <section class="report-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="report-modal-header">
                <h2 id="report-modal-title">Rincian Kehadiran</h2>
                <button type="button" class="report-modal-close" data-report-close aria-label="Tutup rincian">&times;</button>
            </div>
            <div class="report-modal-body" id="report-modal-body" aria-live="polite"></div>
        </section>
    </div>

    <style>
        .report-filter-card,
        .report-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-sizing: border-box;
        }
        .report-filter-card {
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        [data-report-results] {
            transition: opacity 0.15s ease;
        }
        [data-report-results].is-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .report-errors {
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 8px;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            font-size: 13px;
        }
        .report-filter-grid {
            display: grid;
            grid-template-columns: minmax(180px, 1.5fr) minmax(120px, 0.9fr) minmax(88px, 0.6fr) minmax(140px, 1fr) minmax(140px, 1fr) auto;
            gap: 10px;
            align-items: end;
        }
        .report-field {
            min-width: 0;
            display: block;
        }
        .report-label {
            display: block;
            margin-bottom: 5px;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .report-field input,
        .report-field select {
            width: 100%;
            height: 36px;
            padding: 6px 10px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
            font-size: 13px;
            color: #1e293b;
        }
        .report-field input:focus,
        .report-field select:focus {
            outline: none;
            border-color: #145da0;
        }
        .report-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .report-btn-submit,
        .report-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 36px;
            padding: 0 13px;
            box-sizing: border-box;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
        }
        .report-btn-submit {
            background: #145da0;
            border: 1px solid #145da0;
            color: #ffffff;
        }
        .report-btn-submit:hover {
            background: #0a3d62;
        }
        .report-btn-reset {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
        }
        .report-btn-reset:hover {
            background: #f8fafc;
        }
        .report-badge {
            padding: 1px 6px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #334155;
            font-size: 11px;
        }

        /* Meta Bar */
        .report-meta-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 6px;
        }
        .report-meta-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            color: #334155;
        }
        .report-period {
            margin: 0;
        }
        .report-meta-sep {
            color: #cbd5e1;
        }
        .report-count-text,
        .report-active-note {
            color: #64748b;
            font-size: 12.5px;
        }
        .report-meta-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .report-view-switch {
            display: inline-flex;
            padding: 2px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .report-switch-btn {
            padding: 4px 10px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .report-switch-btn.is-active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .report-month-nav {
            display: inline-flex;
            gap: 6px;
        }
        .report-nav-link {
            padding: 5px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }
        .report-nav-link:hover {
            background: #f8fafc;
        }
        .report-note {
            margin: 0 0 12px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        /* Simple, Full-Width Proportional Table */
        .report-table-wrap {
            max-width: 100%;
            overflow-x: auto;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .report-table th,
        .report-table td {
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            padding: 8px 6px;
            box-sizing: border-box;
            font-size: 12px;
            line-height: 1.35;
            vertical-align: middle;
        }
        .report-table th:last-child,
        .report-table td:last-child {
            border-right: 0;
        }
        .report-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-align: center;
            border-bottom: 1px solid #cbd5e1;
        }
        .report-table thead th.col-name,
        .report-table thead th.col-position {
            text-align: left;
            padding-left: 12px;
            padding-right: 10px;
        }
        .report-table thead th.col-name {
            width: 20%;
        }
        .report-table thead th.col-position {
            width: 15%;
        }
        .report-table thead th.col-num {
            width: 6.5%;
        }
        .report-table thead th.col-muted {
            color: #94a3b8;
            font-weight: 600;
        }

        .report-table tbody tr:hover td {
            background: #f8fafc;
        }
        .report-identity {
            padding: 8px 10px 8px 12px !important;
            text-align: left;
            overflow-wrap: break-word;
        }
        .report-emp-name {
            color: #0f172a;
            font-weight: 600;
        }
        .report-status-tag {
            display: inline-block;
            margin-left: 4px;
            padding: 1px 5px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 10px;
            font-weight: 600;
        }
        .report-emp-pt {
            margin-top: 2px;
            color: #64748b;
            font-size: 11px;
        }
        .report-position {
            padding: 8px 10px !important;
            text-align: left;
            color: #334155;
            overflow-wrap: break-word;
        }

        .report-cell {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .report-cell-muted {
            background: #fcfcfd;
        }
        .report-dash,
        .report-zero {
            color: #cbd5e1;
        }
        .report-incomplete {
            margin-left: 2px;
            color: #b45309;
            font-weight: 700;
            cursor: help;
        }

        .report-detail-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 22px;
            padding: 0 7px;
            box-sizing: border-box;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #145da0;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .report-detail-button:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        .report-detail-button:focus-visible {
            outline: 2px solid #145da0;
            outline-offset: 1px;
        }

        .report-table tfoot td {
            background: #f8fafc;
            border-top: 1px solid #cbd5e1;
            border-bottom: 0;
            font-weight: 600;
        }
        .report-tfoot-label {
            padding-left: 12px !important;
            color: #334155;
            text-align: left;
        }
        .report-tfoot-cell {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .report-total-num {
            color: #0f172a;
            font-weight: 700;
        }

        .report-empty[hidden] {
            display: none;
        }
        .report-empty {
            padding: 40px 20px;
            text-align: center;
            color: #64748b;
        }
        .report-empty h3 {
            margin: 0 0 4px;
            color: #334155;
            font-size: 14px;
        }
        .report-empty p {
            margin: 0;
            font-size: 12.5px;
        }

        /* Simple Detail Modal (z-index: 9999 above sidebar) */
        .report-modal[hidden] {
            display: none;
        }
        .report-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        .report-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
        }
        .report-modal-dialog {
            position: relative;
            width: min(560px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 20px;
            box-sizing: border-box;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);
        }
        .report-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .report-modal-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
        }
        .report-modal-close {
            border: 0;
            background: none;
            color: #64748b;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }
        .report-modal-body {
            padding-top: 12px;
            font-size: 13px;
            color: #334155;
        }
        .report-modal-meta {
            margin: 0 0 10px;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            font-size: 12.5px;
        }
        .report-modal-notice {
            margin: 0 0 10px;
            padding: 8px 10px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            color: #92400e;
            font-size: 12px;
        }
        .report-modal-entry {
            padding: 10px 0;
            border-top: 1px solid #f1f5f9;
        }
        .report-modal-entry:first-of-type {
            border-top: 0;
        }
        .report-modal-entry strong {
            display: block;
            margin-bottom: 3px;
            color: #0f172a;
            font-size: 13px;
        }
        .report-modal-detail-line {
            color: #475569;
            font-size: 12.5px;
        }
        .report-modal-link {
            color: #145da0;
            font-weight: 600;
            text-decoration: none;
        }
        .report-modal-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 1100px) {
            .report-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .report-actions {
                grid-column: span 3;
                justify-content: flex-end;
            }
        }
        @media (max-width: 900px) {
            .report-table {
                min-width: 860px;
            }
        }
        @media (max-width: 640px) {
            .report-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .report-field--search,
            .report-actions {
                grid-column: span 2;
            }
        }
    </style>

    <script>
        (() => {
            const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const LEAVE_SHOW_BASE = @json(url('/hr/leave-requests'));

            const formatDateId = (iso) => {
                const parts = String(iso || '').split('-').map(Number);
                if (parts.length !== 3) return String(iso || '');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return `${DAY_NAMES[d.getDay()]}, ${d.getDate()} ${MONTH_NAMES[d.getMonth()]} ${d.getFullYear()}`;
            };

            const add = (parent, tag, value, className = '') => {
                const el = document.createElement(tag);
                el.textContent = value;
                if (className) el.className = className;
                parent.append(el);
                return el;
            };

            /* AJAX Live Search (Auto Search per Input) */
            const filterForm = document.getElementById('reportFilterForm');
            const searchInput = filterForm?.querySelector('[data-report-live-search]');
            const resultsContainer = document.querySelector('[data-report-results]');
            let searchTimer;
            let searchRequest;

            const loadReportResults = async () => {
                if (!filterForm || !resultsContainer) return;
                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams(new FormData(filterForm));
                url.search = params.toString();

                searchRequest?.abort();
                const currentRequest = new AbortController();
                searchRequest = currentRequest;
                resultsContainer.classList.add('is-loading');
                resultsContainer.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian karyawan gagal');

                    const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                    ['[data-report-results]', '[data-report-reset-wrap]'].forEach((selector) => {
                        const currentEl = document.querySelector(selector);
                        const nextEl = doc.querySelector(selector);
                        if (currentEl && nextEl) {
                            currentEl.replaceChildren(...Array.from(nextEl.childNodes));
                        }
                    });

                    window.history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name !== 'AbortError') console.error(error);
                } finally {
                    if (searchRequest === currentRequest) {
                        resultsContainer.classList.remove('is-loading');
                        resultsContainer.setAttribute('aria-busy', 'false');
                    }
                }
            };

            searchInput?.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadReportResults, 350);
            });

            /* Detail Modal & Quick Row Switch */
            const modal = document.getElementById('report-detail-modal');
            const title = document.getElementById('report-modal-title');
            const body = document.getElementById('report-modal-body');
            let request, trigger, sequence = 0;

            const close = () => {
                sequence++;
                request?.abort();
                modal.hidden = true;
                trigger?.focus();
            };

            document.addEventListener('click', async (event) => {
                const switchBtn = event.target.closest('[data-quick-view]');
                if (switchBtn) {
                    const mode = switchBtn.dataset.quickView;
                    const rows = Array.from(document.querySelectorAll('[data-report-row]'));
                    const switchBtns = Array.from(document.querySelectorAll('[data-quick-view]'));
                    const quickEmpty = document.getElementById('report-quick-empty');
                    const counter = document.querySelector('[data-visible-counter]');
                    let visible = 0;

                    rows.forEach((row) => {
                        const show = mode === 'all' || row.dataset.hasRecords === '1';
                        row.hidden = !show;
                        if (show) visible++;
                    });
                    switchBtns.forEach((b) => b.classList.toggle('is-active', b === switchBtn));
                    if (counter) {
                        counter.textContent = mode === 'all' ? `${rows.length} karyawan` : `${visible} dari ${rows.length} karyawan`;
                    }
                    if (quickEmpty) {
                        quickEmpty.hidden = visible > 0 || rows.length === 0;
                    }
                    return;
                }

                if (event.target.closest('[data-report-close]')) {
                    close();
                    return;
                }
                const button = event.target.closest('[data-report-url]');
                if (!button) return;

                request?.abort();
                request = new AbortController();
                const current = ++sequence;
                trigger = button;

                title.textContent = 'Rincian Kehadiran';
                body.replaceChildren();
                add(body, 'p', 'Memuat rincian...');
                modal.hidden = false;
                modal.querySelector('button[data-report-close]')?.focus();

                try {
                    const response = await fetch(button.dataset.reportUrl, {
                        headers: { Accept: 'application/json' },
                        signal: request.signal,
                    });
                    if (!response.ok) throw new Error('Gagal memuat rincian');
                    const data = await response.json();
                    if (current !== sequence) return;

                    title.textContent = `${data.employee} — ${data.category}`;
                    body.replaceChildren();
                    add(body, 'p', `Periode: ${data.start} – ${data.end} · Total: ${data.total} hari`, 'report-modal-meta');

                    if (Number(button.dataset.reportCount) !== data.total) {
                        add(body, 'p', 'Data telah berubah sejak tabel dimuat. Total di atas adalah data terbaru; muat ulang halaman untuk memperbarui tabel.', 'report-modal-notice');
                    }
                    if (data.incomplete) {
                        add(body, 'p', 'Data historis belum lengkap; total TELAT dapat belum mencakup seluruh kejadian.', 'report-modal-notice');
                    }
                    if (!data.entries.length) {
                        add(body, 'p', 'Tidak ada rincian pada periode ini.');
                    }

                    data.entries.forEach((entry) => {
                        const row = document.createElement('div');
                        row.className = 'report-modal-entry';
                        add(row, 'strong', formatDateId(entry.date));

                        if (entry.references) {
                            entry.references.forEach((ref) => {
                                const line = add(row, 'div', `${ref.type} · ${ref.status} · `, 'report-modal-detail-line');
                                const refId = Number(ref.id);
                                if (Number.isInteger(refId) && refId > 0) {
                                    const link = add(line, 'a', `Pengajuan #${refId}`, 'report-modal-link');
                                    link.href = `${LEAVE_SHOW_BASE}/${encodeURIComponent(String(refId))}`;
                                    link.target = '_blank';
                                    link.rel = 'noopener noreferrer';
                                }
                            });
                        }
                        if (entry.start) {
                            add(row, 'div', `Jam mulai ${entry.start} · Clock In ${entry.clock_in}`, 'report-modal-detail-line');
                        }
                        body.append(row);
                    });
                } catch (error) {
                    if (error.name !== 'AbortError' && current === sequence) {
                        body.replaceChildren();
                        add(body, 'p', 'Rincian belum dapat dimuat. Silakan coba lagi.');
                    }
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) close();
            });
        })();
    </script>
</x-app>
