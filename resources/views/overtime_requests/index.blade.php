<x-app title="Riwayat Lembur Saya">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Riwayat Lembur</h1>
                <p class="section-subtitle">Pantau status pengajuan lembur Anda</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="ot-alert ot-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="ot-alert ot-alert--error">
            {{ session('error') }}
        </div>
    @endif

    <div class="ot-cta-bar">
        <a href="{{ route('overtime-requests.create') }}" class="ot-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Lembur
        </a>
    </div>

    <div class="ot-stats">
        <div class="ot-stat">
            <div class="ot-stat-value">{{ $overtimes->total() }}</div>
            <div class="ot-stat-label">Total</div>
        </div>
        <div class="ot-stat-divider"></div>
        <div class="ot-stat">
            <div class="ot-stat-value ot-stat-value--success">
                {{ $overtimes->whereIn('status', [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])->count() }}
            </div>
            <div class="ot-stat-label">Disetujui</div>
        </div>
        <div class="ot-stat-divider"></div>
        <div class="ot-stat">
            <div class="ot-stat-value ot-stat-value--warning">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)->count() }}
            </div>
            <div class="ot-stat-label">Menunggu</div>
        </div>
        <div class="ot-stat-divider"></div>
        <div class="ot-stat">
            <div class="ot-stat-value ot-stat-value--error">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_REJECTED)->count() }}
            </div>
            <div class="ot-stat-label">Ditolak</div>
        </div>
    </div>

    <div class="ot-list">
        @forelse($overtimes as $overtime)
            @php
                $st = $overtime->status;
                $badgeClass = 'ot-badge--gray';
                $statusLabel = $overtime->status_label;
                $statusIcon = '';

                if (in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
                    $badgeClass = 'ot-badge--success';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                    $badgeClass = 'ot-badge--error';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                    $badgeClass = 'ot-badge--warning';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                    $badgeClass = 'ot-badge--gray';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                }
            @endphp

            <div class="ot-card">
                <a href="{{ route('overtime-requests.show', $overtime) }}" class="ot-card-main" aria-label="Lihat detail pengajuan lembur {{ $overtime->overtime_date->format('d-m-Y') }}">
                <div class="ot-card-top">
                    <div class="ot-card-meta-left">
                        <span class="ot-type">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Lembur
                        </span>
                        <span class="ot-badge {{ $badgeClass }}">
                            {!! $statusIcon !!}
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <div class="ot-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $overtime->overtime_date->translatedFormat('l, j F Y') }}</span>
                </div>

                <div class="ot-card-time">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</span>
                </div>

                @if($overtime->description)
                    <div class="ot-card-note">
                        {{ Str::limit($overtime->description, 100) }}
                    </div>
                @endif

                @include('overtime_requests._approval_status', ['overtime' => $overtime, 'compact' => true, 'showHistory' => false, 'showTitle' => false, 'class' => 'ot-approval-summary'])
                </a>

                <div class="ot-card-footer">
                    <div class="ot-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Diajukan {{ $overtime->created_at->diffForHumans() }}</span>
                    </div>
                    <a href="{{ route('overtime-requests.show', $overtime) }}" class="ot-detail-link">Lihat detail</a>
                </div>
            </div>
        @empty
            <div class="ot-empty">
                <div class="ot-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="ot-empty-title">Belum Ada Pengajuan Lembur</h3>
                <p class="ot-empty-desc">Ajukan lembur baru dengan menekan tombol "Ajukan Lembur" di atas.</p>
            </div>
        @endforelse
    </div>

    @if($overtimes->hasPages())
    <div class="ot-pagination">
        <x-pagination :items="$overtimes" />
    </div>
    @endif

    <style>
        .ot-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .ot-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .ot-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #b91c1c;
        }

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
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark); }

        .ot-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }
        .ot-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            flex-shrink: 0;
        }
        .ot-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .ot-btn-primary svg {
            flex-shrink: 0;
        }

        .ot-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
        }
        .ot-stat {
            flex: 1;
            background: var(--white);
            padding: 10px 4px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 0;
        }
        .ot-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }
        .ot-stat-value--success { color: var(--success); }
        .ot-stat-value--warning { color: var(--warning); }
        .ot-stat-value--error   { color: var(--error); }
        .ot-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .ot-stat-divider {
            display: none;
        }

        .ot-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ot-card {
            display: block;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .ot-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .ot-card-main {
            display: block;
            padding: 16px;
            color: inherit;
            text-decoration: none;
        }
        .ot-card-main:focus-visible {
            outline: 3px solid rgba(20, 93, 160, 0.35);
            outline-offset: 2px;
            border-radius: 16px;
        }
        .ot-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .ot-card-meta-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ot-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        .ot-badge {
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
        .ot-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .ot-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .ot-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .ot-badge--gray    { background: #F8FAFC; color: var(--text-secondary); }

        .ot-card-date {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .ot-card-date svg {
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .ot-card-time {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        .ot-card-time svg {
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .ot-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .ot-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px 16px;
            border-top: 1px solid var(--border-light);
            gap: 8px;
        }
        .ot-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-light);
        }
        .ot-card-meta svg {
            flex-shrink: 0;
        }
        .ot-detail-link {
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .ot-detail-link:hover { text-decoration: underline; }
        .ot-approval-summary {
            padding-top: 10px;
            border-top: 1px solid var(--border-light);
        }

        .ot-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
        }
        .ot-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .ot-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .ot-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        .ot-pagination {
            margin-top: 24px;
        }

        @media (min-width: 480px) {
            .ot-cta-bar {
                justify-content: flex-end;
            }
            .ot-btn-primary {
                justify-content: center;
            }
            .ot-stats {
                display: flex;
                align-items: center;
                background: var(--white);
                padding: 14px 20px;
            }
            .ot-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .ot-stat-value {
                font-size: 1.375rem;
            }
            .ot-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .ot-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border);
                flex-shrink: 0;
            }
        }

        @media (max-width: 479px) {
            .ot-cta-bar {
                margin-bottom: 12px;
            }
            .ot-btn-primary {
                width: 100%;
                min-height: 48px;
                font-size: 0.875rem;
            }
            .ot-stats {
                margin-bottom: 12px;
                border-radius: 14px;
            }
            .ot-stat {
                padding: 11px 2px;
            }
            .ot-stat-value {
                font-size: 1rem;
            }
            .ot-stat-label {
                font-size: 0.5625rem;
            }
            .ot-list {
                gap: 12px;
            }
            .ot-card {
                border-radius: 14px;
            }
            .ot-card-main {
                padding: 14px;
            }
            .ot-card-top {
                margin-bottom: 10px;
            }
            .ot-card-meta-left {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
            .ot-type {
                padding: 4px 8px;
            }
            .ot-badge {
                max-width: 100%;
                padding: 5px 8px;
                font-size: 0.625rem;
                letter-spacing: 0.02em;
                line-height: 1.25;
                white-space: normal;
            }
            .ot-card-date {
                margin-bottom: 6px;
                font-size: 0.8125rem;
            }
            .ot-card-time {
                margin-bottom: 10px;
            }
            .ot-card-note {
                display: -webkit-box;
                margin-bottom: 10px;
                overflow: hidden;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }
            .ot-approval-summary {
                display: grid;
                gap: 2px;
                margin-top: 10px !important;
                font-size: 0.75rem !important;
                line-height: 1.45 !important;
            }
            .ot-card-footer {
                padding: 11px 14px 14px;
            }
            .ot-card-meta {
                flex: 1;
                min-width: 0;
            }
            .ot-card-meta span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .ot-detail-link {
                font-size: 0.8125rem;
            }
        }

        @media (min-width: 768px) {
            .ot-card {
                padding: 0;
            }
            .ot-card-main {
                padding: 18px 20px;
            }
            .ot-card-footer {
                padding: 12px 20px 18px;
            }
        }

        @media (min-width: 1024px) {
            .ot-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .ot-empty {
                grid-column: 1 / -1;
            }
        }
    </style>
</x-app>
