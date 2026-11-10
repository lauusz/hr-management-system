<x-app title="Detail Jadwal Kerja">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <h1 class="section-title">Detail Jadwal Kerja</h1>
                <p class="section-subtitle">Rincian jadwal tersimpan untuk {{ $groupName }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $monthLabel = \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y');
        $workingRows = $rows->where('is_holiday', false)->count();
        $holidayRows = $rows->where('is_holiday', true)->count();
        $groupedRows = $rows->groupBy(fn ($row) => $row->work_date->toDateString());
    @endphp

    <div class="ws-back-bar">
        <a class="ws-back-btn" href="{{ route('hr.work-schedules.index', ['month' => $month]) }}">Kembali ke Jadwal Kerja</a>
        <span class="ws-period-badge">Periode: {{ $monthLabel }}</span>
    </div>
    <div class="lm-stats">
        <div class="lm-stat"><div class="lm-stat-value">{{ $rows->count() }}</div><div class="lm-stat-label">Total Jadwal</div></div>
        <div class="lm-stat"><div class="lm-stat-value lm-stat-value--success">{{ $workingRows }}</div><div class="lm-stat-label">Hari Kerja</div></div>
        <div class="lm-stat"><div class="lm-stat-value lm-stat-value--neutral">{{ $holidayRows }}</div><div class="lm-stat-label">Hari Libur</div></div>
    </div>
    <div class="ws-card">
        <div class="ws-card-header">
            <div>
                <h2 class="ws-card-title">Rincian Jadwal Harian — {{ $groupName }}</h2>
                <p class="ws-card-subtitle">Dikelompokkan per tanggal ({{ $groupedRows->count() }} tanggal terjadwal)</p>
            </div>
        </div>

        @if($groupedRows->isNotEmpty())
            <div class="ws-date-sections">
                @foreach($groupedRows as $dateKey => $dateRows)
                    @php
                        $workDate = $dateRows->first()->work_date;
                    @endphp
                    <section class="ws-date-section" aria-label="Jadwal tanggal {{ $workDate->format('d-m-Y') }}">
                        <div class="ws-date-section-header">
                            <div class="ws-date-heading">
                                <div class="ws-date-icon" aria-hidden="true">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ws-date-cell">
                                    <span class="ws-date-main">{{ $workDate->format('d-m-Y') }}</span>
                                    <span class="ws-date-sub">{{ $workDate->locale('id')->translatedFormat('l, d F Y') }}</span>
                                </div>
                            </div>
                            <span class="ws-date-count-badge">{{ $dateRows->count() }} karyawan</span>
                        </div>

                        <div class="lm-table-wrap">
                            <table class="lm-data-table ws-date-table">
                                <thead>
                                    <tr>
                                        <th class="ws-col-employee">Karyawan</th>
                                        <th class="ws-col-shift">Shift</th>
                                        <th class="ws-col-time">Jam</th>
                                        <th class="ws-col-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dateRows as $row)
                                        <tr>
                                            <td class="ws-col-employee">
                                                <div class="lm-employee">
                                                    <div class="lm-employee-avatar">{{ strtoupper(substr($row->user?->name ?? '—', 0, 1)) }}</div>
                                                    <div class="lm-employee-name">{{ $row->user?->name ?? '—' }}</div>
                                                </div>
                                            </td>
                                            <td class="ws-col-shift"><span class="ws-shift-pill">{{ $row->shift_name }}</span></td>
                                            <td class="ws-col-time">
                                                <div class="ws-time-cell">
                                                    <span class="ws-time-badge">{{ $row->start_time ? substr($row->start_time, 0, 5).'–'.substr($row->end_time, 0, 5) : '—' }}</span>
                                                    <span class="ws-tz-badge">{{ $row->is_wita ? 'WITA' : 'WIB' }}</span>
                                                </div>
                                            </td>
                                            <td class="ws-col-status">
                                                <span class="lm-badge {{ $row->is_holiday ? 'lm-badge--holiday' : 'lm-badge--success' }}">{{ $row->is_holiday ? 'Libur' : 'Kerja' }}{{ $row->is_overnight ? ' (lintas hari)' : '' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>
        @else
            <div class="ws-empty-state">
                Belum ada jadwal khusus untuk kelompok ini pada bulan tersebut.
            </div>
        @endif

        <div class="ws-detail-footer"><a class="ws-btn ws-btn--outline" href="{{ route('hr.work-schedules.index', ['month' => $month]) }}">Kembali</a></div>
    </div>

    <style>
        /* ========================================== */
        /* SECTION HEADER                             */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
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
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #64748b);
            font-weight: 500;
            line-height: 1.35;
        }

        /* ========================================== */
        /* BACK BAR & ALERTS                          */
        /* ========================================== */
        .ws-back-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .ws-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid var(--border, #cbd5e1);
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .ws-back-btn:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }
        .ws-period-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* STATS                                      */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: stretch;
            gap: 1px;
            background: var(--border-light, #e2e8f0);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #e2e8f0);
        }
        .lm-stat {
            flex: 1;
            background: var(--white, #fff);
            padding: 12px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 0;
        }
        .lm-stat-value {
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            line-height: 1;
        }
        .lm-stat-value--primary { color: var(--primary, #145da0); }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: #d97706; }
        .lm-stat-label {
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.2;
        }
        .lm-stat-divider { display: none; }

        /* ========================================== */
        /* TABLE CARD & DATE SECTIONS                 */
        /* ========================================== */
        .ws-card {
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .ws-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
        }
        .ws-card-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
        }
        .ws-card-subtitle {
            margin: 3px 0 0;
            font-size: 0.8rem;
            color: var(--text-muted, #64748b);
        }

        .ws-date-sections {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 16px 20px;
            background: var(--gray-50, #f8fafc);
        }
        .ws-date-section {
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .ws-date-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 16px;
            background: linear-gradient(180deg, rgba(241, 245, 249, 0.8), rgba(248, 250, 252, 0.95));
            border-bottom: 1px solid var(--border-light, #e2e8f0);
        }
        .ws-date-heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ws-date-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary, #145da0);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ws-date-count-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #cbd5e1);
            color: var(--text-secondary, #334155);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .ws-empty-state {
            padding: 32px 20px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted, #64748b);
        }

        .lm-table-wrap {
            overflow-x: auto;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .ws-date-table {
            table-layout: fixed;
        }
        .ws-col-employee { width: 36%; }
        .ws-col-shift { width: 28%; }
        .ws-col-time { width: 18%; }
        .ws-col-status { width: 18%; }

        .lm-data-table th {
            text-align: left;
            padding: 10px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--white, #fff);
            border-bottom: 1px solid var(--border-light, #e2e8f0);
        }
        .lm-data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            vertical-align: middle;
            font-size: 0.8125rem;
            color: var(--text-primary, #0f172a);
        }
        .lm-data-table tr:last-child td {
            border-bottom: none;
        }
        .lm-data-table tbody tr:hover td {
            background: var(--gray-50, #f8fafc);
        }

        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lm-employee-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
        }

        .ws-date-cell {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .ws-date-main {
            font-size: 0.875rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            line-height: 1.2;
        }
        .ws-date-sub {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted, #64748b);
            line-height: 1.2;
        }

        .ws-shift-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .ws-time-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .ws-time-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--gray-100, #f1f5f9);
            color: var(--text-primary, #0f172a);
        }
        .ws-tz-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 6px;
            background: var(--gray-100, #f1f5f9);
            color: var(--text-secondary, #475569);
        }
        .ws-status-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .lm-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .lm-badge--holiday {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }
        .ws-text-muted {
            color: var(--text-muted, #94a3b8);
        }

        /* Footer */
        .ws-detail-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border-light, #e2e8f0);
            background: var(--gray-50, #f8fafc);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ws-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
            border: none;
            text-decoration: none;
            white-space: nowrap;
        }
        .ws-btn--outline {
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            border: 1.5px solid var(--border, #cbd5e1);
        }
        .ws-btn--outline:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }
        .ws-btn--submit {
            background: linear-gradient(135deg, var(--primary-dark, #0a3d62), var(--primary, #145da0));
            color: #fff;
            padding: 11px 22px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .ws-btn--submit:hover {
            box-shadow: 0 6px 18px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }

        @media (min-width: 640px) {
            .ws-detail-footer {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        @media (min-width: 768px) {
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }
            .section-title {
                font-size: 1.2rem;
            }
            .lm-stats {
                border-radius: 14px;
            }
            .lm-stat {
                padding: 16px 14px;
            }
            .lm-stat-value {
                font-size: 1.4rem;
            }
            .lm-stat-label {
                font-size: 0.72rem;
            }
        }
    </style>

</x-app>
