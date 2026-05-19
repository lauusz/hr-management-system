<x-app title="Rekap Data Lembur Bawahan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Data Lembur</h1>
                <p class="section-subtitle">Riwayat pengajuan lembur staf di bawah supervisi Anda</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="som-alert som-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="som-alert som-alert--error">
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
    <div class="som-stats">
        <div class="som-stat">
            <div class="som-stat-value">{{ $overtimes->total() }}</div>
            <div class="som-stat-label">Total</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--warning">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)->count() }}
            </div>
            <div class="som-stat-label">Menunggu</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--success">
                {{ $overtimes->whereIn('status', [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])->count() }}
            </div>
            <div class="som-stat-label">Disetujui</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--error">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_REJECTED)->count() }}
            </div>
            <div class="som-stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER CARD                                  --}}
    {{-- ============================================== --}}
    <div class="som-card">
        <div class="som-card-header">
            <div class="som-card-header-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </div>
            <div>
                <h4 class="som-card-title">Filter Data</h4>
                <p class="som-card-subtitle">Cari riwayat pengajuan lembur bawahan Anda</p>
            </div>
        </div>

        <form method="GET" action="{{ route('supervisor.overtime-requests.master') }}" class="som-filter-form">
            <div class="som-filter-fields">
                <div class="som-filter-field">
                    <label for="date_range">Tanggal Lembur</label>
                    <input type="text"
                        id="date_range"
                        name="date_range"
                        value="{{ request('date_range') }}"
                        placeholder="Rentang tanggal..."
                        class="som-input"
                        autocomplete="off">
                </div>

                @php
                    $statusLabels = [
                        \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'Menunggu Approval',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'Disetujui',
                        \App\Models\OvertimeRequest::STATUS_REJECTED => 'Ditolak',
                        \App\Models\OvertimeRequest::STATUS_CANCELLED => 'Dibatalkan',
                    ];
                @endphp
                <div class="som-filter-field">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="som-input">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}" @selected(request('status') === $opt)>
                                {{ $statusLabels[$opt] ?? $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="som-filter-field som-filter-field--wide">
                    <label for="q">Karyawan</label>
                    <input type="text"
                        id="q"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama bawahan..."
                        class="som-input">
                </div>
            </div>

            <div class="som-filter-actions">
                <button type="submit" class="som-btn som-btn--primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filter
                </button>
                @if(request('q') || request('status') || request('date_range'))
                    <a href="{{ route('supervisor.overtime-requests.master') }}" class="som-btn som-btn--secondary">
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
    <div class="som-card som-card--table">
        <div class="som-table-wrapper">
            <table class="som-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Karyawan</th>
                        <th>Tgl Pengajuan</th>
                        <th>Tgl Lembur</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimes as $i => $overtime)
                        @php
                            $st = $overtime->status;
                            $badgeClass = 'som-badge--gray';
                            $statusLabel = $overtime->status_label;
                            $statusIcon = '';

                            if (in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
                                $badgeClass = 'som-badge--success';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                                $badgeClass = 'som-badge--error';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                                $badgeClass = 'som-badge--warning';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                                $badgeClass = 'som-badge--gray';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                            }
                        @endphp
                        <tr>
                            <td class="som-text-muted som-text-center">
                                {{ $overtimes->firstItem() + $i }}
                            </td>
                            <td>
                                <div class="som-employee">
                                    <div class="som-employee-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                                    <div class="som-employee-info">
                                        <span class="som-employee-name">{{ $overtime->user->name }}</span>
                                        <span class="som-employee-role">{{ $overtime->user->position->name ?? 'Staff' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="som-text-muted">
                                {{ $overtime->created_at->translatedFormat('j F Y') }}
                                <div class="som-text-muted som-text-xs">{{ $overtime->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <span class="som-text-date">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                                <div class="som-text-muted som-text-xs">{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</div>
                            </td>
                            <td>
                                <span class="som-duration">{{ $overtime->duration_human }}</span>
                            </td>
                            <td>
                                <span class="som-badge {{ $badgeClass }}">
                                    {!! $statusIcon !!}
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="som-text-right">
                                <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="som-action-link">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="som-empty-cell">
                                <div class="som-empty">
                                    <div class="som-empty-icon">
                                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="som-empty-title">Belum Ada Data</h3>
                                    <p class="som-empty-desc">Belum ada riwayat lembur dari bawahan Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($overtimes->hasPages())
        <div class="som-pagination">
            <x-pagination :items="$overtimes" />
        </div>
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                locale: { rangeSeparator: " to " }
            });
        });
    </script>

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .som-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .som-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .som-alert--error {
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
        .som-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light, #E5E7EB);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .som-stat {
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
        .som-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1;
        }
        .som-stat-value--success { color: var(--success, #22C55E); }
        .som-stat-value--warning { color: var(--warning, #F59E0B); }
        .som-stat-value--error   { color: var(--error, #EF4444); }
        .som-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .som-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .som-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .som-card--table {
            padding: 0;
        }

        /* Card header inside filter card */
        .som-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px 0;
        }
        .som-card-header-icon {
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
        .som-card-title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .som-card-subtitle {
            margin: 2px 0 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* FILTER FORM                                */
        /* ========================================== */
        .som-filter-form {
            padding: 16px 20px 20px;
        }
        .som-filter-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }
        .som-filter-field {
            flex: 1;
            min-width: 160px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .som-filter-field--wide {
            flex: 1.5;
            min-width: 200px;
        }
        .som-filter-field label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .som-input {
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
        }
        .som-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .som-filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .som-btn {
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
        .som-btn svg { flex-shrink: 0; }
        .som-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.18);
        }
        .som-btn--primary:hover {
            box-shadow: 0 4px 14px rgba(10, 61, 98, 0.28);
            transform: translateY(-1px);
        }
        .som-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .som-btn--secondary:hover {
            background: var(--white, #FFFFFF);
            border-color: var(--gray-300, #D1D5DB);
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .som-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .som-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        .som-table thead th {
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
        .som-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            font-size: 13.5px;
            color: var(--text-primary, #1f2937);
            vertical-align: middle;
        }
        .som-table tbody tr:last-child td { border-bottom: none; }
        .som-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
        .som-table tbody tr { transition: background 0.15s ease; }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .som-badge {
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
        .som-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .som-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .som-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .som-badge--gray    { background: var(--gray-50, #F8FAFC); color: var(--text-secondary, #374151); }

        .som-duration {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1px solid var(--border-light, #E5E7EB);
        }

        /* ========================================== */
        /* EMPLOYEE CELL                              */
        /* ========================================== */
        .som-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .som-employee-avatar {
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
        .som-employee-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .som-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .som-employee-role {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* ACTION LINK                                */
        /* ========================================== */
        .som-action-link {
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
        .som-action-link:hover {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .som-empty-cell {
            padding: 0 !important;
        }
        .som-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .som-empty-icon {
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
        .som-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .som-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .som-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* UTILITY                                    */
        /* ========================================== */
        .som-text-muted { color: var(--text-muted, #6B7280); font-size: 13px; }
        .som-text-center { text-align: center; }
        .som-text-right { text-align: right; }
        .som-text-date { font-weight: 600; color: var(--text-primary, #1f2937); font-size: 13.5px; }
        .som-text-xs { font-size: 11px; margin-top: 2px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .som-stats {
                display: flex;
                align-items: center;
                background: var(--white, #FFFFFF);
                padding: 14px 20px;
            }
            .som-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .som-stat-value {
                font-size: 1.375rem;
            }
            .som-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .som-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border, #D1D5DB);
                flex-shrink: 0;
            }
        }

        @media (min-width: 768px) {
            .som-filter-form {
                padding: 20px 24px 24px;
            }
            .som-table tbody td {
                padding: 16px;
            }
            .som-table thead th {
                padding: 16px;
            }
        }

        @media (max-width: 640px) {
            .som-filter-fields {
                flex-direction: column;
            }
            .som-filter-field,
            .som-filter-field--wide {
                min-width: 0;
                flex: none;
                width: 100%;
            }
            .som-filter-actions {
                flex-direction: column;
            }
            .som-btn {
                width: 100%;
            }
        }
    </style>
</x-app>
