<x-app title="Rekap Kehadiran Bulanan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
            ['heading' => 'TELAT', 'key' => 'TELAT', 'tone' => 'warn'],
            ['heading' => 'POT UM', 'key' => null, 'tone' => 'muted'],
            ['heading' => 'IZIN TELAT', 'key' => 'IZIN_TELAT', 'tone' => 'info'],
            ['heading' => 'SET HARI', 'key' => null, 'tone' => 'muted'],
            ['heading' => 'CUTI', 'key' => 'CUTI', 'tone' => 'primary'],
            ['heading' => 'SAKIT', 'key' => 'SAKIT', 'tone' => 'purple'],
            ['heading' => 'IZIN', 'key' => 'IZIN', 'tone' => 'info'],
            ['heading' => 'CUTI KHUSUS', 'key' => 'CUTI_KHUSUS', 'tone' => 'primary'],
            ['heading' => 'ALPHA', 'key' => null, 'tone' => 'muted'],
            ['heading' => 'LIBUR SPV', 'key' => 'OFF_SPV', 'tone' => 'teal'],
        ];

        $isAllMonths = $month === 'all';
        $isCurrentPeriod = ($month === (int) $now->month && $year === (int) $now->year);
        $activeCount = (int) (! $isCurrentPeriod) + (int) ($search !== '') + (int) (bool) $ptId + (int) (bool) $positionId;

        $sharedFilterParams = array_filter([
            'q' => $search !== '' ? $search : null,
            'pt_id' => $ptId ?: null,
            'position_id' => $positionId ?: null,
        ], static fn ($value) => $value !== null);

        // Navigasi bulan hanya untuk mode bulanan; pada "Semua Bulan" tahun diganti lewat filter Tahun.
        $prevMonth = $nextMonth = $prevUrl = $nextUrl = null;
        if (! $isAllMonths) {
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
        }

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

        $totalLeaveAndPermit = $totals['CUTI'] + $totals['CUTI_KHUSUS'] + $totals['SAKIT'] + $totals['IZIN'] + $totals['IZIN_TELAT'] + $totals['OFF_SPV'];
    @endphp

    {{-- Filter Card --}}
    <div class="report-filter-card">
        @if($errors->any())
            <div class="report-errors" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Parameter filter tidak valid. Periksa pilihan dan coba lagi.</span>
            </div>
        @endif

        <form method="GET" action="{{ route('hr.reports.attendance') }}" id="reportFilterForm">
            <div class="report-filter-grid">
                <label class="report-field report-field--search">
                    <span class="report-label">Cari Karyawan</span>
                    <div class="report-input-icon-wrap">
                        <svg class="report-input-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" name="q" value="{{ $search }}" placeholder="Ketik nama karyawan..." maxlength="255" autocomplete="off" data-report-live-search>
                    </div>
                </label>

                <label class="report-field">
                    <span class="report-label">Bulan</span>
                    <select name="month">
                        <option value="all" @selected($isAllMonths)>Semua Bulan</option>
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
                    <button type="submit" class="report-btn-submit">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Terapkan</span>
                    </button>
                    <span data-report-reset-wrap>
                        <a class="report-btn-reset" href="{{ route('hr.reports.attendance') }}">
                            <span>Reset</span>
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
        {{-- Summary & Period Bar --}}
        <div class="report-toolbar-card">
            <div class="report-toolbar-top">
                <div class="report-meta-left">
                    @if($isAllMonths)
                        <span class="report-stat-pill">Semua Bulan {{ $year }}</span>
                    @endif
                    <span class="report-period-pill">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><strong>Periode:</strong> {{ $start->day }} {{ $months[$start->month] }} {{ $start->year }} – {{ $end->day }} {{ $months[$end->month] }} {{ $end->year }}</span>
                    </span>

                    <span class="report-stat-pill" data-visible-counter>{{ $items->count() }} karyawan</span>

                    @if($activeCount > 0)
                        <span class="report-stat-pill report-stat-pill--active">{{ $activeCount }} Filter Aktif</span>
                    @endif
                </div>

                <div class="report-meta-right">
                    @if($items->isNotEmpty())
                        <div class="report-view-switch" role="group" aria-label="Filter baris cepat">
                            <button type="button" class="report-switch-btn is-active" data-quick-view="all">Semua ({{ $items->count() }})</button>
                            <button type="button" class="report-switch-btn" data-quick-view="records">Ada Catatan ({{ $rowsWithRecords }})</button>
                        </div>
                    @endif

                    @unless($isAllMonths)
                    <div class="report-month-nav" role="group" aria-label="Navigasi periode">
                        @if($prevUrl)
                            <a href="{{ $prevUrl }}" class="report-nav-link">&lsaquo; {{ $months[$prevMonth] }}</a>
                        @endif
                        @if($nextUrl)
                            <a href="{{ $nextUrl }}" class="report-nav-link">{{ $months[$nextMonth] }} &rsaquo;</a>
                        @endif
                    </div>
                    @endunless
                </div>
            </div>

            <div class="report-toolbar-bottom">
                <div class="report-kpi-mini">
                    <span class="report-kpi-item">
                        <span class="report-kpi-dot report-kpi-dot--warn"></span>
                        Total Telat: <strong>{{ $totals['TELAT'] }}</strong>
                    </span>
                    <span class="report-kpi-item">
                        <span class="report-kpi-dot report-kpi-dot--primary"></span>
                        Total Cuti/Izin/Sakit: <strong>{{ $totalLeaveAndPermit }}</strong>
                    </span>
                </div>
                <div class="report-legend">
                    <span>Klik angka untuk rincian tanggal</span>
                    <span class="report-meta-sep">·</span>
                    <span><strong class="report-incomplete">*</strong> Data historis belum lengkap</span>
                    <span class="report-meta-sep">·</span>
                    <span><strong>—</strong> Belum tersedia</span>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="report-table-card">
            @if($items->isEmpty())
                <div class="report-empty">
                    <div class="report-empty-icon" aria-hidden="true">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3>Belum Ada Data Karyawan</h3>
                    <p>Tidak ada karyawan yang sesuai dengan filter yang dipilih.</p>
                </div>
            @else
                <div class="report-table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th scope="col" class="col-identity">NAMA &amp; JABATAN</th>
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
                                    $ptName = $item->profile?->pt?->name;
                                    $posName = $item->position?->name;
                                    $ptAndPos = collect([$ptName, $posName])->filter(fn ($v) => filled($v))->implode(' - ') ?: '—';
                                @endphp
                                <tr data-report-row data-has-records="{{ $hasRecords ? '1' : '0' }}" class="{{ $hasRecords ? 'has-records' : '' }}">
                                    <td class="report-identity">
                                        <div class="report-identity-inner">
                                            <span class="report-emp-avatar" aria-hidden="true">{{ strtoupper(substr($item->name, 0, 1)) }}</span>
                                            <div class="report-identity-text">
                                                <div class="report-emp-name">
                                                    <span>{{ $item->name }}</span>
                                                </div>
                                                <div class="report-emp-sub">{{ $ptAndPos }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    @foreach($columns as $col)
                                        @php
                                            $category = $col['key'];
                                            $tone = $col['tone'];
                                            $count = $category ? count($empReport[$category] ?? []) : null;
                                        @endphp
                                        <td class="report-cell {{ $category === null ? 'report-cell-muted' : '' }}">
                                            @if($category === null)
                                                <span class="report-dash">—</span>
                                            @elseif($count > 0)
                                                <button
                                                    type="button"
                                                    class="report-detail-button report-detail-button--{{ $tone }}"
                                                    data-report-url="{{ route('hr.reports.attendance.detail', ['employee' => $item->id, 'month' => $month, 'year' => $year, 'category' => $category]) }}"
                                                    data-report-count="{{ $count }}"
                                                    aria-label="Rincian {{ $category }} {{ $item->name }}">{{ $count }}</button>
                                            @else
                                                <span class="report-zero">0</span>
                                            @endif
                                            @if($category === 'TELAT' && $isIncomplete)
                                                @php
                                                    $incompleteDates = collect($empReport['incomplete_dates'] ?? [])->filter()->sort()->values();
                                                    $checkUrl = $incompleteDates->isNotEmpty()
                                                        ? route('hr.attendances.index', ['q' => $item->name, 'date_start' => $incompleteDates->first(), 'date_end' => $incompleteDates->last()])
                                                        : null;
                                                @endphp
                                                @if($checkUrl)
                                                    <a href="{{ $checkUrl }}" target="_blank" rel="noopener" class="report-incomplete report-incomplete-link" title="Data historis belum lengkap (buka di tab baru)" aria-label="Periksa data absensi {{ $item->name }} yang belum lengkap di Master Absensi (buka di tab baru)">*</a>
                                                @else
                                                    <span class="report-incomplete" title="Data historis belum lengkap">*</span>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="report-tfoot-label">Total Hari Periode</td>
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
        /* ========================================== */
        /* PAGE HEADER (FIX BROKEN ICON & TITLE)      */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0a3d62);
        }
        .section-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 1px 0 0;
            font-size: 0.78rem;
            color: var(--text-muted, #64748b);
            font-weight: 500;
            line-height: 1.35;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .report-filter-card,
        .report-toolbar-card,
        .report-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .report-filter-card {
            padding: 14px 16px;
            margin-bottom: 12px;
        }
        [data-report-results] {
            transition: opacity 0.15s ease;
        }
        [data-report-results].is-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .report-errors {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 9px;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            font-size: 12.5px;
            font-weight: 600;
        }
        .report-filter-grid {
            display: grid;
            grid-template-columns: minmax(170px, 1.5fr) minmax(115px, 0.9fr) minmax(80px, 0.55fr) minmax(130px, 1fr) minmax(130px, 1fr) auto;
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
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .report-input-icon-wrap {
            position: relative;
        }
        .report-input-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }
        .report-field input,
        .report-field select {
            width: 100%;
            height: 36px;
            padding: 6px 10px;
            box-sizing: border-box;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
            font: inherit;
            font-size: 12.5px;
            font-weight: 500;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .report-input-icon-wrap input {
            padding-left: 31px;
        }
        .report-field input:focus,
        .report-field select:focus {
            outline: none;
            border-color: #145da0;
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .report-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .report-btn-submit,
        .report-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 36px;
            padding: 0 13px;
            box-sizing: border-box;
            border-radius: 9px;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .report-btn-submit {
            background: linear-gradient(135deg, #0a3d62, #145da0);
            border: 1px solid #0a3d62;
            color: #ffffff;
        }
        .report-btn-submit:hover {
            opacity: 0.94;
        }
        .report-btn-reset {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #475569;
        }
        .report-btn-reset:hover {
            border-color: #145da0;
            color: #145da0;
            background: #f8fafc;
        }
        .report-badge {
            padding: 1px 6px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.12);
            color: #145da0;
            font-size: 11px;
            font-weight: 800;
        }

        /* ========================================== */
        /* TOOLBAR & SUMMARY BAR                      */
        /* ========================================== */
        .report-toolbar-card {
            padding: 11px 16px;
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }
        .report-toolbar-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .report-meta-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .report-period-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.07);
            color: #0a3d62;
            font-size: 12.5px;
            font-weight: 500;
        }
        .report-period-pill strong {
            font-weight: 800;
        }
        .report-stat-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }
        .report-stat-pill--active {
            background: #fef3c7;
            color: #b45309;
        }
        .report-meta-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .report-view-switch {
            display: inline-flex;
            padding: 3px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }
        .report-switch-btn {
            padding: 4px 11px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .report-switch-btn.is-active {
            background: #145da0;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }
        .report-month-nav {
            display: inline-flex;
            gap: 6px;
        }
        .report-nav-link {
            padding: 5px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .report-nav-link:hover {
            border-color: #145da0;
            color: #145da0;
            background: #f8fafc;
        }

        .report-toolbar-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px 16px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
            font-size: 11.5px;
            color: #64748b;
        }
        .report-kpi-mini {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }
        .report-kpi-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            font-weight: 600;
        }
        .report-kpi-item strong {
            color: #0f172a;
            font-weight: 800;
        }
        .report-kpi-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
        }
        .report-kpi-dot--warn { background: #ea580c; }
        .report-kpi-dot--primary { background: #145da0; }
        .report-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .report-meta-sep {
            color: #cbd5e1;
        }

        /* ========================================== */
        /* COMPACT 1-PAGE TABLE (NO HORIZONTAL SCROLL)*/
        /* ========================================== */
        .report-table-card {
            overflow: hidden;
        }
        .report-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .report-table th,
        .report-table td {
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f8fafc;
            padding: 8px 4px;
            box-sizing: border-box;
            font-size: 11.5px;
            line-height: 1.3;
            vertical-align: middle;
        }
        .report-table th:last-child,
        .report-table td:last-child {
            border-right: 0;
        }
        .report-table thead th {
            background: linear-gradient(180deg, #f8fafc, #f1f5f9);
            color: #334155;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-align: center;
            border-bottom: 1.5px solid #e2e8f0;
            padding: 10px 3px;
            line-height: 1.2;
            word-break: break-word;
        }
        .report-table thead th.col-identity {
            width: 27%;
            text-align: left;
            padding-left: 14px;
            padding-right: 10px;
        }
        .report-table thead th.col-num {
            width: 7.3%;
        }
        .report-table thead th.col-muted {
            color: #94a3b8;
            font-weight: 700;
        }

        .report-table tbody tr:hover td {
            background: #f8fafc;
        }
        .report-identity {
            padding: 8px 10px 8px 14px !important;
            text-align: left;
        }
        .report-identity-inner {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .report-emp-avatar {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.09);
            color: #145da0;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .report-identity-text {
            min-width: 0;
            flex: 1;
        }
        .report-emp-name {
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }
        .report-status-tag {
            display: inline-flex;
            align-items: center;
            padding: 1px 6px;
            border-radius: 999px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .report-emp-sub {
            margin-top: 2px;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 600;
            line-height: 1.25;
            overflow-wrap: break-word;
        }

        .report-cell {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .report-cell-muted {
            background: #fafbfc;
        }
        .report-dash {
            color: #cbd5e1;
            font-weight: 500;
        }
        .report-zero {
            color: #cbd5e1;
            font-size: 11px;
        }
        .report-incomplete {
            margin-left: 1px;
            color: #d97706;
            font-weight: 800;
            cursor: help;
        }
        .report-incomplete-link {
            text-decoration: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .report-incomplete-link:hover { text-decoration: underline; }
        .report-incomplete-link:focus-visible {
            outline: 2px solid #d97706;
            outline-offset: 2px;
            background: #fef3c7;
        }

        .report-detail-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 22px;
            padding: 0 6px;
            box-sizing: border-box;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            background: #eff6ff;
            color: #145da0;
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .report-detail-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08);
        }
        .report-detail-button--warn {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }
        .report-detail-button--warn:hover {
            background: #ffedd5;
            border-color: #fdba74;
        }
        .report-detail-button--primary {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #145da0;
        }
        .report-detail-button--primary:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        .report-detail-button--purple {
            background: #faf5ff;
            border-color: #e9d5ff;
            color: #7e22ce;
        }
        .report-detail-button--purple:hover {
            background: #f3e8ff;
            border-color: #d8b4fe;
        }
        .report-detail-button--info {
            background: #f0f9ff;
            border-color: #bae6fd;
            color: #0369a1;
        }
        .report-detail-button--info:hover {
            background: #e0f2fe;
            border-color: #7dd3fc;
        }
        .report-detail-button--teal {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }
        .report-detail-button--teal:hover {
            background: #dcfce7;
            border-color: #86efac;
        }
        .report-detail-button:focus-visible {
            outline: 2px solid #145da0;
            outline-offset: 1px;
        }

        .report-table tfoot td {
            background: #f8fafc;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: 0;
            padding: 10px 4px;
            font-weight: 700;
        }
        .report-tfoot-label {
            padding-left: 14px !important;
            color: #0f172a;
            text-align: left;
            font-size: 12px;
            font-weight: 800;
        }
        .report-tfoot-cell {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .report-total-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            padding: 2px 6px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            font-size: 11.5px;
        }

        .report-empty[hidden] {
            display: none;
        }
        .report-empty {
            padding: 44px 20px;
            text-align: center;
            color: #64748b;
        }
        .report-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #f8fafc;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        .report-empty h3 {
            margin: 0 0 4px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }
        .report-empty p {
            margin: 0;
            font-size: 12.5px;
        }

        /* ========================================== */
        /* DETAIL MODAL (z-index: 9999)               */
        /* ========================================== */
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
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(2px);
        }
        .report-modal-dialog {
            position: relative;
            width: min(520px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.24);
            border: 1px solid #e2e8f0;
        }
        .report-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .report-modal-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.35;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .report-modal-cat-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.1);
            color: #145da0;
            font-size: 12px;
            font-weight: 700;
        }
        .report-modal-close {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: 0;
            background: #f1f5f9;
            color: #475569;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .report-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .report-modal-body {
            padding-top: 14px;
            font-size: 13px;
            color: #334155;
        }
        .report-modal-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0 0 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
        }
        .report-modal-meta-pills {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .report-modal-balance-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.1);
            border: 1px solid rgba(20, 93, 160, 0.22);
            color: #145da0;
            font-size: 11.5px;
            font-weight: 700;
        }
        .report-modal-total-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            background: #0a3d62;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
        }
        .report-modal-notice {
            margin: 0 0 12px;
            padding: 9px 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            color: #92400e;
            font-size: 12px;
            line-height: 1.4;
        }
        .report-modal-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .report-modal-entry {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .report-modal-entry:hover {
            border-color: #cbd5e1;
            background: #fafbfc;
        }
        .report-modal-entry-main {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .report-modal-date {
            display: block;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 800;
        }
        .report-modal-badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .report-modal-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #334155;
        }
        .report-modal-tag--approved {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .report-modal-tag--late {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #c2410c;
        }
        .report-modal-time-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12px;
            color: #475569;
        }
        .report-modal-time-box {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
        }
        .report-modal-time-box strong {
            color: #0f172a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .report-modal-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            border: 1px solid rgba(20, 93, 160, 0.22);
            color: #145da0;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .report-modal-link:hover {
            background: #145da0;
            border-color: #145da0;
            color: #ffffff;
        }

        @media (min-width: 768px) {
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }
            .section-title {
                font-size: 1.15rem;
            }
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
        @media (max-width: 820px) {
            .report-table {
                min-width: 760px;
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
            const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const LEAVE_SHOW_BASE = @json(url('/hr/leave-requests'));
            const ATTENDANCE_INDEX_BASE = @json(url('/hr/attendances'));

            const buildAttendanceUrl = (empName, startDate, endDate) => {
                const params = new URLSearchParams({
                    q: String(empName || '').trim(),
                    date_range: `${startDate} to ${endDate}`,
                    date_start: startDate,
                    date_end: endDate,
                });
                return `${ATTENDANCE_INDEX_BASE}?${params.toString()}`;
            };

            const TYPE_LABELS = {
                CUTI: 'Cuti Tahunan',
                IZIN_TELAT: 'Izin Telat',
                SAKIT: 'Sakit',
                IZIN: 'Izin',
                CUTI_KHUSUS: 'Cuti Khusus',
                OFF_SPV: 'Libur SPV',
            };

            const STATUS_LABELS = {
                APPROVED: 'Disetujui',
                PENDING: 'Menunggu',
                REJECTED: 'Ditolak',
            };

            const formatDateId = (iso) => {
                const parts = String(iso || '').split('-').map(Number);
                if (parts.length !== 3) return String(iso || '');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return `${DAY_NAMES[d.getDay()]}, ${d.getDate()} ${MONTH_NAMES[d.getMonth()]} ${d.getFullYear()}`;
            };

            const formatShortPeriodDate = (iso) => {
                const parts = String(iso || '').split('-').map(Number);
                if (parts.length !== 3) return String(iso || '');
                return `${parts[2]} ${MONTH_SHORT[parts[1] - 1]} ${parts[0]}`;
            };

            const formatTimeShort = (raw) => {
                const str = String(raw || '').trim();
                const timePart = str.includes(' ') ? str.split(' ').pop() : str;
                return timePart.slice(0, 5);
            };

            const calcLateLabel = (startRaw, clockInRaw) => {
                const s = formatTimeShort(startRaw).split(':').map(Number);
                const c = formatTimeShort(clockInRaw).split(':').map(Number);
                if (s.length < 2 || c.length < 2 || Number.isNaN(s[0]) || Number.isNaN(c[0])) return '';
                const diff = (c[0] * 60 + c[1]) - (s[0] * 60 + s[1]);
                if (diff <= 0) return '';
                if (diff < 60) return `Telat ${diff} menit`;
                const hrs = Math.floor(diff / 60);
                const mins = diff % 60;
                return mins > 0 ? `Telat ${hrs}j ${mins}m` : `Telat ${hrs} jam`;
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
                    if (searchRequest !== currentRequest) return; // hasil lama tidak boleh menimpa hasil terbaru
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

                    title.replaceChildren();
                    add(title, 'span', data.employee);
                    add(title, 'span', data.category, 'report-modal-cat-badge');

                    body.replaceChildren();
                    const metaBox = document.createElement('div');
                    metaBox.className = 'report-modal-meta';
                    add(metaBox, 'span', `Periode: ${formatShortPeriodDate(data.start)} – ${formatShortPeriodDate(data.end)}`);

                    const pillsWrap = document.createElement('div');
                    pillsWrap.className = 'report-modal-meta-pills';
                    if (data.leave_balance !== null && data.leave_balance !== undefined) {
                        const formattedBal = Number(data.leave_balance).toLocaleString('id-ID', { maximumFractionDigits: 1 });
                        add(pillsWrap, 'span', `Sisa Cuti: ${formattedBal} Hari`, 'report-modal-balance-pill');
                    }
                    add(pillsWrap, 'span', `${data.total} Hari`, 'report-modal-total-pill');
                    metaBox.append(pillsWrap);
                    body.append(metaBox);

                    if (Number(button.dataset.reportCount) !== data.total) {
                        add(body, 'p', 'Data telah berubah sejak tabel dimuat. Total di atas adalah data terbaru; muat ulang halaman untuk memperbarui tabel.', 'report-modal-notice');
                    }
                    if (data.incomplete) {
                        add(body, 'p', 'Sebagian riwayat absensi lama belum memiliki jam shift lengkap.', 'report-modal-notice');
                    }
                    if (!data.entries.length) {
                        add(body, 'p', 'Tidak ada rincian pada periode ini.');
                    }

                    const listEl = document.createElement('div');
                    listEl.className = 'report-modal-list';

                    data.entries.forEach((entry) => {
                        const row = document.createElement('div');
                        row.className = 'report-modal-entry';

                        const mainCol = document.createElement('div');
                        mainCol.className = 'report-modal-entry-main';
                        add(mainCol, 'strong', formatDateId(entry.date), 'report-modal-date');

                        let actionLinkEl = null;

                        if (entry.references && entry.references.length) {
                            const badgesWrap = document.createElement('div');
                            badgesWrap.className = 'report-modal-badges';

                            entry.references.forEach((ref) => {
                                const typeLabel = TYPE_LABELS[ref.type] || ref.type;
                                const statusLabel = STATUS_LABELS[ref.status] || ref.status;
                                add(badgesWrap, 'span', typeLabel, 'report-modal-tag');
                                add(badgesWrap, 'span', statusLabel, 'report-modal-tag report-modal-tag--approved');

                                const refId = Number(ref.id);
                                if (Number.isInteger(refId) && refId > 0 && !actionLinkEl) {
                                    actionLinkEl = document.createElement('a');
                                    actionLinkEl.className = 'report-modal-link';
                                    actionLinkEl.href = `${LEAVE_SHOW_BASE}/${encodeURIComponent(String(refId))}`;
                                    actionLinkEl.target = '_blank';
                                    actionLinkEl.rel = 'noopener noreferrer';
                                    actionLinkEl.textContent = 'Cek ↗';
                                }
                            });

                            mainCol.append(badgesWrap);
                        }

                        if (entry.start) {
                            const timeRow = document.createElement('div');
                            timeRow.className = 'report-modal-time-row';

                            const schedBox = document.createElement('span');
                            schedBox.className = 'report-modal-time-box';
                            add(schedBox, 'span', 'Jadwal:');
                            add(schedBox, 'strong', formatTimeShort(entry.start));
                            timeRow.append(schedBox);

                            const inBox = document.createElement('span');
                            inBox.className = 'report-modal-time-box';
                            add(inBox, 'span', 'Masuk:');
                            add(inBox, 'strong', formatTimeShort(entry.clock_in));
                            timeRow.append(inBox);

                            const lateText = calcLateLabel(entry.start, entry.clock_in);
                            if (lateText) {
                                add(timeRow, 'span', lateText, 'report-modal-tag report-modal-tag--late');
                            }

                            mainCol.append(timeRow);

                            actionLinkEl = document.createElement('a');
                            actionLinkEl.className = 'report-modal-link';
                            actionLinkEl.href = buildAttendanceUrl(data.employee, entry.date, entry.date);
                            actionLinkEl.target = '_blank';
                            actionLinkEl.rel = 'noopener noreferrer';
                            actionLinkEl.textContent = 'Cek ↗';
                        }

                        row.append(mainCol);
                        if (actionLinkEl) {
                            row.append(actionLinkEl);
                        }
                        listEl.append(row);
                    });

                    if (data.entries.length) {
                        body.append(listEl);
                    }
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
