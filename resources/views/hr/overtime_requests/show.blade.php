<x-app title="Detail Rekap Lembur (HR)">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Rekap Lembur</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan lembur karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
        $st = $overtimeRequest->status;
        $statusClass = 'hov-badge--gray';
        $statusLabel = $overtimeRequest->hrd_status_label;
        $statusIcon = '';

        if ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) {
            $statusClass = 'hov-badge--success';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
            $statusClass = 'hov-badge--info';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
            $statusClass = 'hov-badge--error';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            $statusClass = 'hov-badge--warning';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
            $statusClass = 'hov-badge--gray';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }
    @endphp

    <div class="hov-detail-page">

        {{-- Back Button --}}
        <a href="{{ route('hr.overtime-requests.master') }}" class="back-btn" aria-label="Kembali ke daftar">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="hov-alert hov-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="hov-alert hov-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="hov-alert hov-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="hov-detail-body">

            {{-- Card: Employee Header --}}
            <div class="hov-card">
                <div class="hov-card-header">
                    <div class="hov-avatar hov-avatar--lg">{{ substr($overtimeRequest->user->name, 0, 1) }}</div>
                    <div class="hov-card-header-info">
                        <h2 class="hov-employee-name">{{ $overtimeRequest->user->name }}</h2>
                        <div class="hov-employee-meta">
                            <span class="hov-role-chip">{{ $overtimeRequest->user->role instanceof \App\Enums\UserRole ? $overtimeRequest->user->role->label() : $overtimeRequest->user->role }}</span>
                            <span class="hov-meta-dot">•</span>
                            <span>{{ $overtimeRequest->user->position->name ?? '-' }}</span>
                            <span class="hov-meta-dot">•</span>
                            <span>{{ $overtimeRequest->user->division->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="hov-card-header-footer">
                    <span class="hov-badge {{ $statusClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                    <span class="hov-submit-time">Diajukan {{ $overtimeRequest->created_at->translatedFormat('j F Y, H:i') }}</span>
                </div>
            </div>

            {{-- Card: Informasi Lembur --}}
            <div class="hov-card">
                <div class="hov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Lembur
                </div>

                <div class="hov-detail-row">
                    <span class="hov-detail-label">Tanggal</span>
                    <div class="hov-date-display">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $overtimeRequest->overtime_date->translatedFormat('l, j F Y') }}</span>
                    </div>
                </div>

                <div class="hov-detail-row">
                    <span class="hov-detail-label">Jam</span>
                    <span class="hov-time-display">{{ $overtimeRequest->start_time->format('H:i') }} - {{ $overtimeRequest->end_time->format('H:i') }}</span>
                </div>

                <div class="hov-detail-row">
                    <span class="hov-detail-label">Durasi</span>
                    <span class="hov-duration-badge">{{ $overtimeRequest->duration_human }}</span>
                </div>

                @if($overtimeRequest->supervisorApprover)
                    <div class="hov-detail-row">
                        <span class="hov-detail-label">Disetujui Supervisor</span>
                        <div class="hov-approval-inline">
                            <div class="hov-approval-icon hov-approval-icon--green">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="hov-approval-info">
                                <span class="hov-approval-name">{{ $overtimeRequest->supervisorApprover->name }}</span>
                                <span class="hov-approval-time">{{ $overtimeRequest->approved_by_supervisor_at ? $overtimeRequest->approved_by_supervisor_at->translatedFormat('j F Y, H:i') : '' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($overtimeRequest->hrdApprover)
                    <div class="hov-detail-row">
                        <span class="hov-detail-label">Disetujui HRD</span>
                        <div class="hov-approval-inline">
                            <div class="hov-approval-icon hov-approval-icon--green">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="hov-approval-info">
                                <span class="hov-approval-name">{{ $overtimeRequest->hrdApprover->name }}</span>
                                <span class="hov-approval-time">{{ $overtimeRequest->approved_by_hrd_at ? $overtimeRequest->approved_by_hrd_at->translatedFormat('j F Y, H:i') : '' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card: Keterangan Pekerjaan --}}
            <div class="hov-card">
                <div class="hov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Keterangan Pekerjaan
                </div>
                <div class="hov-reason-box">
                    {{ $overtimeRequest->description }}
                </div>
            </div>

            {{-- Card: Data Absensi & Pencocokan --}}
            <div class="hov-card">
                <div class="hov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Data Absensi & Pencocokan
                </div>

                <div class="hov-match-grid">
                    <div class="hov-match-card">
                        <div class="hov-match-label">Status Cocok</div>
                        <div class="hov-match-value">
                            @php
                                $matchBadgeClass = match($recap['status_color'] ?? 'gray') {
                                    'green' => 'hov-badge--success',
                                    'red' => 'hov-badge--error',
                                    'yellow' => 'hov-badge--warning',
                                    default => 'hov-badge--gray',
                                };
                            @endphp
                            <span class="hov-badge {{ $matchBadgeClass }}">
                                {{ $recap['status'] ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="hov-match-card">
                        <div class="hov-match-label">Clock In</div>
                        <div class="hov-match-value">
                            @if($recap['clock_in'] ?? null)
                                <span class="hov-text-date">{{ $recap['clock_in']->format('H:i') }}</span>
                                <div class="hov-text-muted hov-text-xs">{{ $recap['clock_in']->translatedFormat('j F Y') }}</div>
                            @else
                                <span class="hov-text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="hov-match-card">
                        <div class="hov-match-label">Clock Out</div>
                        <div class="hov-match-value">
                            @if($recap['clock_out'] ?? null)
                                <span class="hov-text-date">{{ $recap['clock_out']->format('H:i') }}</span>
                                <div class="hov-text-muted hov-text-xs">{{ $recap['clock_out']->translatedFormat('j F Y') }}</div>
                            @else
                                <span class="hov-text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="hov-match-card">
                        <div class="hov-match-label">Waktu Pengajuan Selesai</div>
                        <div class="hov-match-value">
                            @if($recap['requested_end'] ?? null)
                                <span class="hov-text-date">{{ $recap['requested_end']->format('H:i') }}</span>
                                <div class="hov-text-muted hov-text-xs">{{ $recap['requested_end']->translatedFormat('j F Y') }}</div>
                            @else
                                <span class="hov-text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="hov-match-card">
                        <div class="hov-match-label">Selisih</div>
                        <div class="hov-match-value">
                            @if(!is_null($recap['variance_minutes'] ?? null))
                                @if($recap['variance_minutes'] >= 0)
                                    <span class="hov-variance hov-variance--positive">+{{ $recap['variance_minutes'] }} menit</span>
                                @else
                                    <span class="hov-variance hov-variance--negative">{{ $recap['variance_minutes'] }} menit</span>
                                @endif
                            @else
                                <span class="hov-text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Alasan Penolakan --}}
            @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtimeRequest->rejection_note)
                <div class="hov-card">
                    <div class="hov-section-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Alasan Penolakan
                    </div>
                    <div class="hov-rejection-box">
                        {{ $overtimeRequest->rejection_note }}
                    </div>
                </div>
            @endif

        </div>
    </div>



    <style>
        /* ========================================== */
        /* PAGE LAYOUT                                */
        /* ========================================== */
        .hov-detail-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .hov-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .hov-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .hov-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .hov-alert--error {
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
        /* BACK BUTTON                                */
        /* ========================================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .hov-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .hov-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .hov-section-title svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* CARD HEADER (Employee)                     */
        /* ========================================== */
        .hov-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .hov-avatar {
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
        .hov-avatar--lg {
            width: 52px;
            height: 52px;
            font-size: 1.25rem;
            border-radius: 12px;
        }
        .hov-card-header-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .hov-employee-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hov-employee-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .hov-role-chip {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .hov-meta-dot {
            color: var(--border-light, #E5E7EB);
        }

        .hov-card-header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .hov-badge {
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
        .hov-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .hov-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .hov-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .hov-badge--info    { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
        .hov-badge--gray    { background: #F8FAFC; color: var(--text-secondary, #374151); }

        .hov-submit-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* DETAIL ROWS                                */
        /* ========================================== */
        .hov-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            gap: 12px;
        }
        .hov-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .hov-detail-row:first-child { padding-top: 0; }
        .hov-detail-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        /* ========================================== */
        /* DATE & TIME                                */
        /* ========================================== */
        .hov-date-display {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .hov-date-display svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .hov-time-display {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .hov-duration-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }

        /* ========================================== */
        /* APPROVAL INLINE                            */
        /* ========================================== */
        .hov-approval-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hov-approval-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hov-approval-icon--green {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success, #22C55E);
        }
        .hov-approval-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .hov-approval-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .hov-approval-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* REASON BOX                                 */
        /* ========================================== */
        .hov-reason-box {
            background: var(--gray-50, #F5F7FA);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            padding: 14px;
            font-size: 0.875rem;
            color: var(--text-primary, #111827);
            line-height: 1.7;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* REJECTION BOX                              */
        /* ========================================== */
        .hov-rejection-box {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.18);
            border-radius: 10px;
            padding: 14px;
            font-size: 0.875rem;
            color: #b91c1c;
            line-height: 1.7;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* MATCH GRID                                 */
        /* ========================================== */
        .hov-match-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .hov-match-card {
            background: var(--gray-50, #F5F7FA);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .hov-match-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.03em;
        }
        .hov-match-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }

        /* ========================================== */
        /* VARIANCE                                   */
        /* ========================================== */
        .hov-variance {
            font-size: 14px;
            font-weight: 600;
        }
        .hov-variance--positive { color: #15803d; }
        .hov-variance--negative { color: #dc2626; }

        /* ========================================== */
        /* UTILITY                                    */
        /* ========================================== */
        .hov-text-muted { color: var(--text-muted, #6B7280); font-size: 13px; }
        .hov-text-date { font-weight: 600; color: var(--text-primary, #1f2937); font-size: 13.5px; }
        .hov-text-xs { font-size: 11px; margin-top: 2px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .hov-match-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 768px) {
            .hov-card {
                padding: 24px;
            }
            .hov-match-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .hov-match-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }
    </style>
</x-app>
