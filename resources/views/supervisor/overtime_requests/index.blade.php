<x-app title="Inbox Approval Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Inbox Approval Lembur</h1>
                <p class="section-subtitle">Daftar pengajuan lembur yang membutuhkan persetujuan Anda</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="sov-alert sov-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="sov-alert sov-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- SUMMARY STATS --}}
    {{-- ============================================== --}}
    <div class="sov-stats">
        <div class="sov-stat">
            <div class="sov-stat-value">{{ $overtimes->total() }}</div>
            <div class="sov-stat-label">Total</div>
        </div>
        <div class="sov-stat-divider"></div>
        <div class="sov-stat">
            <div class="sov-stat-value sov-stat-value--warning">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)->count() }}
            </div>
            <div class="sov-stat-label">Menunggu</div>
        </div>
        <div class="sov-stat-divider"></div>
        <div class="sov-stat">
            <div class="sov-stat-value sov-stat-value--success">
                {{ $overtimes->whereIn('status', [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])->count() }}
            </div>
            <div class="sov-stat-label">Disetujui</div>
        </div>
        <div class="sov-stat-divider"></div>
        <div class="sov-stat">
            <div class="sov-stat-value sov-stat-value--error">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_REJECTED)->count() }}
            </div>
            <div class="sov-stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- REQUEST LIST --}}
    {{-- ============================================== --}}
    <div class="sov-list">
        @forelse($overtimes as $overtime)
            @php
                $st = $overtime->status;
                $badgeClass = 'sov-badge--gray';
                $statusLabel = $overtime->status_label;
                $statusIcon = '';

                if (in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
                    $badgeClass = 'sov-badge--success';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                    $badgeClass = 'sov-badge--error';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                    $badgeClass = 'sov-badge--warning';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                    $badgeClass = 'sov-badge--gray';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                }

                $isPending = $st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR;
            @endphp

            <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="sov-card">
                <div class="sov-card-top">
                    <span class="sov-type">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Lembur
                    </span>
                    <span class="sov-badge {{ $badgeClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="sov-card-employee">
                    <div class="sov-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                    <div class="sov-employee-info">
                        <span class="sov-employee-name">{{ $overtime->user->name }}</span>
                        <span class="sov-employee-detail">{{ $overtime->user->position->name ?? '-' }} - {{ $overtime->user->division->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="sov-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $overtime->overtime_date->translatedFormat('l, j F Y') }}</span>
                </div>

                <div class="sov-card-time">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</span>
                    <span class="sov-duration">{{ $overtime->duration_human }}</span>
                </div>

                @if($overtime->description)
                    <div class="sov-card-note">
                        {{ Str::limit($overtime->description, 100) }}
                    </div>
                @endif

                <div class="sov-card-footer">
                    <div class="sov-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $overtime->created_at->translatedFormat('l, j F Y') }} · {{ $overtime->created_at->format('H:i') }}</span>
                    </div>
                    <div class="sov-card-action">
                        <span>{{ $isPending ? 'Proses' : 'Detail' }}</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="sov-empty">
                <div class="sov-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="sov-empty-title">Tidak Ada Pengajuan</h3>
                <p class="sov-empty-desc">Tidak ada pengajuan lembur yang perlu diproses saat ini.</p>
            </div>
        @endforelse
    </div>

    @if($overtimes->hasPages())
    <div class="sov-pagination">
        <x-pagination :items="$overtimes" />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .sov-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .sov-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .sov-alert--error {
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
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .sov-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light, #E5E7EB);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .sov-stat {
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
        .sov-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1;
        }
        .sov-stat-value--success { color: var(--success, #22C55E); }
        .sov-stat-value--warning { color: var(--warning, #F59E0B); }
        .sov-stat-value--error   { color: var(--error, #EF4444); }
        .sov-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .sov-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* REQUEST LIST & CARDS                       */
        /* ========================================== */
        .sov-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .sov-card {
            display: block;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .sov-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .sov-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* Type badge (rounded, not pill) */
        .sov-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }

        /* Status badge (pill) */
        .sov-badge {
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
            flex-shrink: 0;
        }
        .sov-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .sov-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .sov-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .sov-badge--gray    { background: #F8FAFC; color: var(--text-secondary, #374151); }

        /* Employee info */
        .sov-card-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .sov-avatar {
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
        .sov-employee-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .sov-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sov-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Date */
        .sov-card-date {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 8px;
        }
        .sov-card-date svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }

        /* Time */
        .sov-card-time {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.8125rem;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .sov-card-time svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .sov-duration {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            margin-left: 4px;
        }

        /* Note */
        .sov-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* Footer */
        .sov-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
        }
        .sov-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .sov-card-meta svg {
            flex-shrink: 0;
        }
        .sov-card-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .sov-card-action svg {
            transition: transform 0.2s ease;
        }
        .sov-card:hover .sov-card-action svg {
            transform: translateX(3px);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .sov-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .sov-empty-icon {
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
        .sov-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .sov-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .sov-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .sov-stats {
                display: flex;
                align-items: center;
                background: var(--white, #FFFFFF);
                padding: 14px 20px;
            }
            .sov-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .sov-stat-value {
                font-size: 1.375rem;
            }
            .sov-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .sov-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border, #D1D5DB);
                flex-shrink: 0;
            }
            .sov-card {
                padding: 18px 20px;
            }
        }

        @media (min-width: 768px) {
            .sov-card {
                padding: 20px;
            }
        }

        @media (min-width: 1024px) {
            .sov-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .sov-empty {
                grid-column: 1 / -1;
            }
        }
    </style>
</x-app>
