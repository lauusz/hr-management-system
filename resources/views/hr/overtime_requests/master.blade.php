<x-app :title="$pageTitle ?? 'Rekap Lembur'">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Data Lembur</h1>
                <p class="section-subtitle">Riwayat pengajuan lembur seluruh karyawan</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="hom-alert hom-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="hom-alert hom-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- STATS ROW                                    --}}
    {{-- ============================================== --}}
    <div class="hom-stats">
        <div class="hom-stat">
            <div class="hom-stat-value">{{ $overtimes->total() }}</div>
            <div class="hom-stat-label">Total</div>
        </div>
        <div class="hom-stat-divider"></div>
        <div class="hom-stat">
            <div class="hom-stat-value hom-stat-value--warning">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)->count() }}
            </div>
            <div class="hom-stat-label">Menunggu</div>
        </div>
        <div class="hom-stat-divider"></div>
        <div class="hom-stat">
            <div class="hom-stat-value hom-stat-value--success">
                {{ $overtimes->whereIn('status', [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])->count() }}
            </div>
            <div class="hom-stat-label">Disetujui</div>
        </div>
        <div class="hom-stat-divider"></div>
        <div class="hom-stat">
            <div class="hom-stat-value hom-stat-value--error">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_REJECTED)->count() }}
            </div>
            <div class="hom-stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER CARD                                  --}}
    {{-- ============================================== --}}
    <div class="hom-card">
        <div class="hom-card-header">
            <div class="hom-card-header-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </div>
            <div>
                <h4 class="hom-card-title">Filter Data</h4>
                <p class="hom-card-subtitle">Menampilkan data lembur berdasarkan periode yang dipilih</p>
            </div>
        </div>

        <form method="GET" action="{{ route('hr.overtime-requests.master') }}" class="hom-filter-form">
            <div class="hom-filter-fields">
                <div class="hom-filter-field">
                    <label for="overtime_date_range">Tanggal Lembur</label>
                    <input type="text"
                        id="overtime_date_range"
                        name="overtime_date_range"
                        value="{{ $overtimeDateRange ?? '' }}"
                        placeholder="Rentang tanggal..."
                        class="hom-input"
                        autocomplete="off">
                </div>

                @php
                    $statusLabels = [
                        \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'Menunggu Supervisor',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'Disetujui',
                        \App\Models\OvertimeRequest::STATUS_REJECTED => 'Ditolak',
                        \App\Models\OvertimeRequest::STATUS_CANCELLED => 'Dibatalkan',
                    ];
                @endphp
                <div class="hom-filter-field">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="hom-input">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}" @selected($status === $opt)>
                                {{ $statusLabels[$opt] ?? $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hom-filter-field hom-filter-field--wide">
                    <label for="q">Karyawan</label>
                    <input type="text"
                        id="q"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari nama karyawan..."
                        class="hom-input">
                </div>
            </div>

            <div class="hom-filter-actions">
                <button type="submit" class="hom-btn hom-btn--primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filter
                </button>
                @if(($q ?? null) || ($status ?? null) || ($overtimeDateRange ?? null))
                    <a href="{{ route('hr.overtime-requests.master') }}" class="hom-btn hom-btn--secondary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ============================================== --}}
    {{-- TABLE CARD                                   --}}
    {{-- ============================================== --}}
    <div class="hom-card hom-card--table">
        <div class="hom-table-wrapper">
            <table class="hom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Karyawan</th>
                        <th>Tanggal Lembur</th>
                        <th>Waktu & Durasi</th>
                        <th>Status / Supervisor</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Cocok Absensi</th>
                        <th>Selisih</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimes as $i => $overtime)
                        @php
                            $recap = $recapData[$overtime->id] ?? null;

                            $st = $overtime->status;
                            $badgeClass = 'hom-badge--gray';
                            $statusLabel = $overtime->hrd_status_label;
                            $statusIcon = '';

                            if ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) {
                                $badgeClass = 'hom-badge--success';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
                                $badgeClass = 'hom-badge--info';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                                $badgeClass = 'hom-badge--error';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                                $badgeClass = 'hom-badge--warning';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                                $badgeClass = 'hom-badge--gray';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                            }

                            $matchColor = $recap['status_color'] ?? 'gray';
                            $matchBadgeClass = match($matchColor) {
                                'green' => 'hom-badge--success',
                                'red' => 'hom-badge--error',
                                'yellow' => 'hom-badge--warning',
                                default => 'hom-badge--gray',
                            };
                        @endphp
                        <tr>
                            <td class="hom-text-muted hom-text-center">
                                {{ $overtimes->firstItem() + $i }}
                            </td>
                            <td>
                                <div class="hom-employee">
                                    <div class="hom-employee-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                                    <div class="hom-employee-info">
                                        <span class="hom-employee-name">{{ $overtime->user->name }}</span>
                                        <span class="hom-employee-role">{{ $overtime->user->division->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="hom-text-date">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                            </td>
                            <td>
                                <span class="hom-text-date">{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</span>
                                <div class="hom-text-muted hom-text-xs" style="font-weight: 600;">{{ $overtime->duration_human }}</div>
                            </td>
                            <td>
                                <span class="hom-badge {{ $badgeClass }}">
                                    {!! $statusIcon !!}
                                    {{ $statusLabel }}
                                </span>
                                @if($overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                                    <div class="hom-approver-info">
                                        Menunggu: <strong>{{ $overtime->user->directSupervisor->name ?? '-' }}</strong>
                                    </div>
                                @elseif($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR)
                                    <div class="hom-approver-info">
                                        Disetujui: <strong>{{ $overtime->supervisorApprover->name ?? '-' }}</strong>
                                    </div>
                                @elseif($overtime->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtime->rejection_note)
                                    <div class="hom-rejection-mini">
                                        Note: {{ $overtime->rejection_note }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($recap && $recap['clock_in'])
                                    <span class="hom-text-date">{{ $recap['clock_in']->format('H:i') }}</span>
                                @else
                                    <span class="hom-text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($recap && $recap['clock_out'])
                                    <span class="hom-text-date">{{ $recap['clock_out']->format('H:i') }}</span>
                                @else
                                    <span class="hom-text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="hom-badge {{ $matchBadgeClass }}">
                                    {{ $recap['status'] ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if(!is_null($recap['variance_minutes'] ?? null))
                                    @if($recap['variance_minutes'] >= 0)
                                        <span class="hom-variance hom-variance--positive">+{{ $recap['variance_minutes'] }} m</span>
                                    @else
                                        <span class="hom-variance hom-variance--negative">{{ $recap['variance_minutes'] }} m</span>
                                    @endif
                                @else
                                    <span class="hom-text-muted">-</span>
                                @endif
                            </td>
                            <td class="hom-text-right">
                                <a href="{{ route('hr.overtime-requests.show', $overtime->id) }}" class="hom-action-link">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="hom-empty-cell">
                                <div class="hom-empty">
                                    <div class="hom-empty-icon">
                                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="hom-empty-title">Belum Ada Data</h3>
                                    <p class="hom-empty-desc">Belum ada data lembur yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($overtimes->hasPages())
        <div class="hom-pagination">
            <x-pagination :items="$overtimes" />
        </div>
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#overtime_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "2020-01-01",
                locale: { rangeSeparator: " sampai " }
            });
        });
    </script>

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .hom-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .hom-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .hom-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
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
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .hom-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light, #E5E7EB);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .hom-stat {
            flex: 1;
            background: var(--white, #FFFFFF);
            padding: 10px 4px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 0;
        }
        .hom-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1;
        }
        .hom-stat-value--success { color: var(--success, #22C55E); }
        .hom-stat-value--warning { color: var(--warning, #F59E0B); }
        .hom-stat-value--error   { color: var(--error, #EF4444); }
        .hom-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .hom-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .hom-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .hom-card--table {
            padding: 0;
        }
        .hom-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px 0;
        }
        .hom-card-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hom-card-title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .hom-card-subtitle {
            margin: 2px 0 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* FILTER FORM                                */
        /* ========================================== */
        .hom-filter-form {
            padding: 16px 20px 20px;
        }
        .hom-filter-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }
        .hom-filter-field {
            flex: 1;
            min-width: 160px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .hom-filter-field--wide {
            flex: 1.5;
            min-width: 200px;
        }
        .hom-filter-field label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .hom-input {
            padding: 9px 12px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 13.5px;
            color: var(--text-primary, #374151);
            background: var(--white, #FFFFFF);
            width: 100%;
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
            -webkit-appearance: none;
            appearance: none;
        }
        .hom-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        select.hom-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }
        .hom-filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .hom-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
        }
        .hom-btn svg { flex-shrink: 0; }
        .hom-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.18);
        }
        .hom-btn--primary:hover {
            box-shadow: 0 4px 14px rgba(10, 61, 98, 0.28);
            transform: translateY(-1px);
        }
        .hom-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .hom-btn--secondary:hover {
            background: var(--white, #FFFFFF);
            border-color: var(--gray-300, #D1D5DB);
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .hom-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .hom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }
        .hom-table thead th {
            background: var(--gray-50, #F5F7FA);
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border, #E5E7EB);
            white-space: nowrap;
        }
        .hom-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            font-size: 13.5px;
            color: var(--text-primary, #1f2937);
            vertical-align: top;
        }
        .hom-table tbody tr:last-child td { border-bottom: none; }
        .hom-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
        .hom-table tbody tr { transition: background 0.15s ease; }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .hom-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }
        .hom-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .hom-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .hom-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .hom-badge--info    { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
        .hom-badge--gray    { background: var(--gray-50, #F8FAFC); color: var(--text-secondary, #374151); }

        /* ========================================== */
        /* EMPLOYEE CELL                              */
        /* ========================================== */
        .hom-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hom-employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hom-employee-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .hom-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hom-employee-role {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* APPROVER & REJECTION INFO                  */
        /* ========================================== */
        .hom-approver-info {
            font-size: 11px;
            color: var(--text-muted, #6B7280);
            margin-top: 4px;
            line-height: 1.3;
        }
        .hom-rejection-mini {
            font-size: 10px;
            color: var(--error, #EF4444);
            margin-top: 4px;
            max-width: 150px;
            line-height: 1.2;
        }

        /* ========================================== */
        /* VARIANCE                                   */
        /* ========================================== */
        .hom-variance {
            font-size: 12px;
            font-weight: 600;
        }
        .hom-variance--positive { color: #15803d; }
        .hom-variance--negative { color: #dc2626; }

        /* ========================================== */
        /* ACTION LINK                                */
        /* ========================================== */
        .hom-action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            background: var(--white, #FFFFFF);
            color: var(--primary, #145DA0);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .hom-action-link:hover {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .hom-empty-cell {
            padding: 0 !important;
        }
        .hom-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .hom-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50, #F5F7FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light, #9CA3AF);
        }
        .hom-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .hom-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .hom-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* UTILITY                                    */
        /* ========================================== */
        .hom-text-muted { color: var(--text-muted, #6B7280); font-size: 13px; }
        .hom-text-center { text-align: center; }
        .hom-text-right { text-align: right; }
        .hom-text-date { font-weight: 600; color: var(--text-primary, #1f2937); font-size: 13.5px; }
        .hom-text-xs { font-size: 11px; margin-top: 2px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .hom-stats {
                display: flex;
                align-items: center;
                background: var(--white, #FFFFFF);
                padding: 14px 20px;
            }
            .hom-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .hom-stat-value {
                font-size: 1.375rem;
            }
            .hom-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .hom-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border, #D1D5DB);
                flex-shrink: 0;
            }
        }

        @media (min-width: 768px) {
            .hom-filter-form {
                padding: 20px 24px 24px;
            }
            .hom-table tbody td {
                padding: 12px 14px;
            }
            .hom-table thead th {
                padding: 12px 14px;
            }
        }

        @media (min-width: 1025px) {
            /* Compact table for desktop */
            .hom-table thead th {
                padding: 8px 10px;
                font-size: 10px;
            }
            .hom-table tbody td {
                padding: 8px 10px;
                font-size: 12px;
            }
            .hom-table {
                min-width: 950px;
            }

            /* Compact employee */
            .hom-employee-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
                border-radius: 6px;
            }
            .hom-employee-name {
                font-size: 13px;
            }
            .hom-employee-role {
                font-size: 11px;
            }

            /* Compact badges */
            .hom-badge {
                padding: 3px 8px;
                font-size: 0.625rem;
            }

            /* Compact text */
            .hom-text-date {
                font-size: 12px;
            }
            .hom-text-muted {
                font-size: 12px;
            }
            .hom-text-xs {
                font-size: 10px;
            }

            /* Compact approver & rejection */
            .hom-approver-info {
                font-size: 10px;
            }
            .hom-rejection-mini {
                font-size: 9px;
                max-width: 120px;
            }

            /* Compact variance */
            .hom-variance {
                font-size: 11px;
            }

            /* Compact action */
            .hom-action-link {
                padding: 4px 10px;
                font-size: 11px;
                border-radius: 6px;
            }
        }

        @media (max-width: 640px) {
            .hom-filter-fields {
                flex-direction: column;
            }
            .hom-filter-field,
            .hom-filter-field--wide {
                min-width: 0;
                flex: none;
                width: 100%;
            }
            .hom-filter-actions {
                flex-direction: column;
            }
            .hom-btn {
                width: 100%;
            }
        }

        @media (max-width: 1024px) {
            .hom-table-wrapper { background: transparent; }

            .hom-table,
            .hom-table tbody,
            .hom-table tr,
            .hom-table td {
                display: block;
                width: 100%;
                min-width: 0;
            }

            .hom-table thead { display: none; }

            .hom-table tbody tr {
                background: var(--white, #FFFFFF);
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                margin-bottom: 12px;
                border: 1px solid var(--border-light, #E5E7EB);
                padding: 16px;
                position: relative;
            }

            .hom-table td {
                padding: 4px 0;
                border: none;
                text-align: left;
            }

            /* Hide Index */
            .hom-table td:nth-child(1) { display: none; }

            /* Employee Name */
            .hom-table td:nth-child(2) {
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border-light, #E5E7EB);
            }
            .hom-table td:nth-child(2) .hom-employee-name { font-size: 15px; }

            /* Status / Supervisor */
            .hom-table td:nth-child(5) {
                display: block;
                margin-bottom: 12px;
            }

            /* Tanggal, Waktu, Clock In, Clock Out, Cocok, Selisih */
            .hom-table td:nth-child(3),
            .hom-table td:nth-child(4),
            .hom-table td:nth-child(6),
            .hom-table td:nth-child(7),
            .hom-table td:nth-child(8),
            .hom-table td:nth-child(9) {
                display: block;
                background: var(--gray-50, #F5F7FA);
                padding: 6px 10px;
                border-radius: 8px;
                color: var(--text-secondary, #4b5563);
                font-size: 13px;
                margin-bottom: 4px;
            }

            .hom-table td:nth-child(3)::before { content: 'Tgl Lembur: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(4)::before { content: 'Waktu: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(6)::before { content: 'Clock In: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(7)::before { content: 'Clock Out: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(8)::before { content: 'Cocok: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(9)::before { content: 'Selisih: '; font-weight: 600; color: var(--text-primary, #374151); }

            /* Action */
            .hom-table td:last-child {
                border-top: 1px solid var(--border-light, #E5E7EB);
                margin-top: 12px;
                padding-top: 12px;
                text-align: center;
            }
            .hom-action-link {
                display: block;
                width: 100%;
                background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
                color: #fff;
                border: none;
                padding: 10px;
                border-radius: 10px;
            }
            .hom-action-link:hover {
                background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
                opacity: 0.95;
            }

            .hom-table tr:has(.hom-empty-cell) {
                text-align: center;
                padding: 40px 20px;
            }
        }
    </style>
</x-app>
