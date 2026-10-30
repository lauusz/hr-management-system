    @php
        $st = $overtimeRequest->status;
        $statusClass = 'hov-badge--gray';
        $statusLabel = $overtimeRequest->hrd_status_label;
        $statusIcon = '';

        if ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_HRD || $st === \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
            $statusClass = 'hov-badge--success';
            $statusIcon = '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
            $statusClass = 'hov-badge--error';
            $statusIcon = '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            $statusClass = 'hov-badge--warning';
            $statusIcon = '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
            $statusClass = 'hov-badge--gray';
            $statusIcon = '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }

        $mapping = $overtimeRequest->user?->overtimeApproverAssignment;
        $currentActions = $overtimeRequest->currentApprovalActions();
        $supervisorAction = $currentActions->firstWhere('decision_slot', 'SUPERVISOR');
        $supervisorName = $supervisorAction?->actor_name ?? $mapping?->supervisor?->name ?? 'Belum ditentukan';
        $managerName = $mapping?->manager?->name ?? 'Belum ditentukan';
        $isPendingSpv = $st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR;
        $isApproved = in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD], true);
        $isRejected = $st === \App\Models\OvertimeRequest::STATUS_REJECTED;
    @endphp

    <div class="hov-detail-page">

        @unless($modal ?? false)
            {{-- Back Button --}}
            <a href="{{ route('hr.overtime-requests.master') }}" class="back-btn" aria-label="Kembali ke daftar">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="back-btn-text">Kembali</span>
            </a>
        @endunless

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

            {{-- 1. Hero Card: Profil Karyawan & Status --}}
            <section class="hov-card hov-hero-card">
                <div class="hov-hero-top">
                    <div class="hov-employee-profile">
                        <div class="hov-avatar hov-avatar--lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($overtimeRequest->user->name, 0, 1)) }}</div>
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
                    <div class="hov-hero-status">
                        <span class="hov-badge {{ $statusClass }}">
                            {!! $statusIcon !!}
                            {{ $statusLabel }}
                        </span>
                        <span class="hov-submit-time">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Diajukan {{ $overtimeRequest->created_at->translatedFormat('j M Y, H:i') }}
                        </span>
                    </div>
                </div>

                {{-- 2. Metrics Strip: Tanggal, Jam, Durasi --}}
                <div class="hov-metrics-grid">
                    <div class="hov-metric-tile">
                        <div class="hov-metric-icon hov-metric-icon--blue" aria-hidden="true">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="hov-metric-content">
                            <span class="hov-metric-label">Tanggal Lembur</span>
                            <strong class="hov-metric-value">{{ $overtimeRequest->overtime_date->translatedFormat('l, j F Y') }}</strong>
                        </div>
                    </div>

                    <div class="hov-metric-tile">
                        <div class="hov-metric-icon hov-metric-icon--indigo" aria-hidden="true">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="hov-metric-content">
                            <span class="hov-metric-label">Jam Lembur</span>
                            <strong class="hov-metric-value">{{ $overtimeRequest->start_time->format('H:i') }} – {{ $overtimeRequest->end_time->format('H:i') }}</strong>
                            @if((int) $overtimeRequest->break_minutes > 0)
                                <span class="hov-metric-sub">Istirahat: {{ $overtimeRequest->break_human }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="hov-metric-tile hov-metric-tile--highlight">
                        <div class="hov-metric-icon hov-metric-icon--navy" aria-hidden="true">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="hov-metric-content">
                            <span class="hov-metric-label">Total Durasi</span>
                            <strong class="hov-metric-value hov-metric-value--accent">{{ $overtimeRequest->duration_human }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 3. Card: Keterangan Pekerjaan & Bukti Pendukung --}}
            <section class="hov-card">
                <div class="hov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Keterangan Pekerjaan
                </div>

                <div class="hov-reason-box">{{ $overtimeRequest->description }}</div>

                <div class="hov-evidence-block">
                    <div class="hov-evidence-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span>Bukti Pendukung</span>
                        <span class="hov-evidence-count">{{ $overtimeRequest->attachments->count() }} file</span>
                    </div>

                    @if ($overtimeRequest->attachments->isNotEmpty())
                        <div class="hov-attachment-grid">
                            @foreach ($overtimeRequest->attachments as $attachment)
                                @php
                                    $attachmentUrl = route('overtime-requests.attachments.show', [$overtimeRequest, $attachment]);
                                    $isImage = str_starts_with(strtolower((string) $attachment->mime_type), 'image/');
                                @endphp
                                <div class="hov-attachment-card">
                                    @if ($isImage)
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="hov-attachment-thumb" title="Klik untuk melihat ukuran penuh">
                                            <img src="{{ $attachmentUrl }}" alt="{{ $attachment->original_name }}" loading="lazy">
                                            <span class="hov-attachment-zoom">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Lihat Bukti
                                            </span>
                                        </a>
                                    @else
                                        <div class="hov-attachment-file-icon" aria-hidden="true">
                                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="hov-attachment-meta">
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="hov-attachment-name">{{ $attachment->original_name }}</a>
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="hov-attachment-open">
                                            Buka di tab baru
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="hov-evidence-empty">Tidak ada file bukti pendukung yang dilampirkan.</div>
                    @endif
                </div>

                @if($overtimeRequest->user_id !== auth()->id() && $overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                    @include('overtime_requests._edit_form', ['overtime' => $overtimeRequest])
                @endif
            </section>

            {{-- 4. Card: Persetujuan Lembur --}}
            <section class="hov-card">
                <div class="hov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Pihak Persetujuan Lembur
                </div>

                <div class="hov-approvers-grid">
                    <div class="hov-approver-box">
                        <div class="hov-approver-icon {{ $isApproved ? 'hov-approver-icon--green' : ($isRejected ? 'hov-approver-icon--red' : 'hov-approver-icon--amber') }}" aria-hidden="true">
                            @if($isApproved)
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/></svg>
                            @elseif($isRejected)
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 18L18 6M6 6l12 12"/></svg>
                            @else
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="hov-approver-body">
                            <span class="hov-approver-role">Disetujui Oleh (Supervisor)</span>
                            <strong class="hov-approver-name">{{ $supervisorName }}</strong>
                            <span class="hov-approver-state {{ $isApproved ? 'is-approved' : ($isRejected ? 'is-rejected' : 'is-pending') }}">
                                @if($isApproved)
                                    Telah menyetujui
                                @elseif($isRejected)
                                    Menolak pengajuan
                                @elseif($isPendingSpv)
                                    Menunggu persetujuan
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="hov-approver-box">
                        <div class="hov-approver-icon hov-approver-icon--blue" aria-hidden="true">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="hov-approver-body">
                            <span class="hov-approver-role">Mengetahui (Manager)</span>
                            <strong class="hov-approver-name">{{ $managerName }}</strong>
                            <span class="hov-approver-state">Pihak mengetahui</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 5. Card: Alasan Penolakan (bila ditolak) --}}
            @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtimeRequest->rejection_note)
                <section class="hov-card hov-card--danger">
                    <div class="hov-section-title hov-section-title--danger">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Alasan Penolakan
                    </div>
                    <div class="hov-rejection-box">
                        {{ $overtimeRequest->rejection_note }}
                    </div>
                </section>
            @endif

            @include('overtime_requests._system_note', ['overtime' => $overtimeRequest])

            @if(($isPendingSpv || $isApproved) && ((int) $overtimeRequest->user_id !== (int) auth()->id() || $isPendingSpv))
                <form method="POST" action="{{ route('overtime-requests.destroy', $overtimeRequest) }}" @if($modal ?? false) target="_top" data-overtime-cancel @endif onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="hov-cancel-btn">Batalkan Lembur</button>
                </form>
            @endif

        </div>
    </div>

    <style>
        .hov-cancel-btn {
            padding: 10px 16px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fff;
            color: #dc2626;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .hov-cancel-btn:hover { background: #fef2f2; }
        /* ========================================== */
        /* PAGE LAYOUT                                */
        /* ========================================== */
        .hov-detail-page {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .hov-detail-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
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
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* BACK BUTTON                                */
        /* ========================================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            align-self: flex-start;
        }
        .back-btn:hover {
            border-color: #145da0;
            color: #145da0;
            background: #f8fafc;
        }
        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }
        .back-btn-text { font-size: 0.75rem; font-weight: 600; line-height: 1; }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .hov-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .hov-card--danger {
            border-color: rgba(239, 68, 68, 0.25);
        }

        .hov-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f172a;
            margin-bottom: 14px;
            padding-bottom: 11px;
            border-bottom: 1px solid #f1f5f9;
        }
        .hov-section-title svg {
            color: #145da0;
            flex-shrink: 0;
        }
        .hov-section-title--danger {
            color: #b91c1c;
        }
        .hov-section-title--danger svg {
            color: #dc2626;
        }

        /* ========================================== */
        /* HERO CARD (Employee + Metrics)             */
        /* ========================================== */
        .hov-hero-card {
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        }
        .hov-hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .hov-employee-profile {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
            flex: 1;
        }
        .hov-avatar {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: linear-gradient(135deg, #0a3d62 0%, #145da0 100%);
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(20, 93, 160, 0.18);
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
            font-size: 1.025rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .hov-employee-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }
        .hov-role-chip {
            background: rgba(20, 93, 160, 0.08);
            color: #145da0;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .hov-meta-dot {
            color: #cbd5e1;
        }

        .hov-hero-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            flex-shrink: 0;
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .hov-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .hov-badge--success { background: #ecfdf5; color: #15803d; border: 1px solid #bbf7d0; }
        .hov-badge--error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .hov-badge--warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .hov-badge--info    { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .hov-badge--gray    { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

        .hov-submit-time {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 500;
            color: #64748b;
        }

        /* ========================================== */
        /* METRICS STRIP (Tanggal, Jam, Durasi)       */
        /* ========================================== */
        .hov-metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }
        .hov-metric-tile {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 13px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            min-width: 0;
        }
        .hov-metric-tile--highlight {
            background: linear-gradient(135deg, rgba(10, 61, 98, 0.04), rgba(20, 93, 160, 0.08));
            border-color: rgba(20, 93, 160, 0.22);
        }
        .hov-metric-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hov-metric-icon--blue {
            background: rgba(20, 93, 160, 0.1);
            color: #145da0;
        }
        .hov-metric-icon--indigo {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }
        .hov-metric-icon--navy {
            background: #0a3d62;
            color: #ffffff;
        }
        .hov-metric-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .hov-metric-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .hov-metric-value {
            font-size: 0.84rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .hov-metric-value--accent {
            color: #0a3d62;
            font-size: 0.9rem;
            font-weight: 800;
        }
        .hov-metric-sub {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
        }

        /* ========================================== */
        /* REASON & ATTACHMENTS                       */
        /* ========================================== */
        .hov-reason-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #145da0;
            border-radius: 10px;
            padding: 13px 15px;
            font-size: 0.875rem;
            color: #1e293b;
            line-height: 1.65;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .hov-evidence-block {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #e2e8f0;
        }
        .hov-evidence-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 10px;
        }
        .hov-evidence-header svg {
            color: #64748b;
            flex-shrink: 0;
        }
        .hov-evidence-count {
            margin-left: auto;
            padding: 2px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 700;
        }
        .hov-attachment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
        }
        .hov-attachment-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 11px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: border-color 0.15s ease, background-color 0.15s ease;
            min-width: 0;
        }
        .hov-attachment-card:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }
        .hov-attachment-thumb {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            display: block;
        }
        .hov-attachment-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .hov-attachment-zoom {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            color: #ffffff;
            font-size: 0.6rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            opacity: 0;
            transition: opacity 0.15s ease;
            text-align: center;
        }
        .hov-attachment-thumb:hover .hov-attachment-zoom {
            opacity: 1;
        }
        .hov-attachment-file-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: #145da0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hov-attachment-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            flex: 1;
        }
        .hov-attachment-name {
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hov-attachment-name:hover {
            color: #145da0;
            text-decoration: underline;
        }
        .hov-attachment-open {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #145da0;
            text-decoration: none;
        }
        .hov-attachment-open:hover {
            text-decoration: underline;
        }
        .hov-evidence-empty {
            font-size: 0.78rem;
            color: #94a3b8;
            font-style: italic;
        }

        /* ========================================== */
        /* APPROVERS GRID                             */
        /* ========================================== */
        .hov-approvers-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .hov-approver-box {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            min-width: 0;
        }
        .hov-approver-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hov-approver-icon--green {
            background: #dcfce7;
            color: #15803d;
        }
        .hov-approver-icon--amber {
            background: #fef3c7;
            color: #b45309;
        }
        .hov-approver-icon--red {
            background: #fee2e2;
            color: #b91c1c;
        }
        .hov-approver-icon--blue {
            background: #e0f2fe;
            color: #0369a1;
        }
        .hov-approver-body {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .hov-approver-role {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .hov-approver-name {
            font-size: 0.85rem;
            font-weight: 800;
            color: #0f172a;
            overflow-wrap: anywhere;
        }
        .hov-approver-state {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
        }
        .hov-approver-state.is-approved { color: #15803d; }
        .hov-approver-state.is-pending  { color: #b45309; }
        .hov-approver-state.is-rejected { color: #b91c1c; }

        /* ========================================== */
        /* EDIT FORM & REJECTION BOX                  */
        /* ========================================== */
        .hov-detail-page .overtime-edit > summary {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }
        .hov-detail-page .overtime-edit > summary:focus-visible {
            outline: 2px solid #145da0;
            outline-offset: 2px;
        }
        .hov-detail-page .overtime-edit-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .hov-detail-page .overtime-edit-form > label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            color: #0f172a;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .hov-detail-page .overtime-edit-form > label:first-of-type,
        .hov-detail-page .overtime-edit-form > label:last-of-type,
        .hov-detail-page .overtime-edit-form > p {
            grid-column: 1 / -1;
        }
        .hov-detail-page .overtime-edit-form input,
        .hov-detail-page .overtime-edit-form textarea {
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            font: inherit;
            font-size: 0.875rem;
            font-weight: 400;
        }
        .hov-detail-page .overtime-edit-form textarea {
            min-height: 96px;
            resize: vertical;
        }
        .hov-detail-page .overtime-edit-form input:focus,
        .hov-detail-page .overtime-edit-form textarea:focus {
            border-color: #145da0;
            outline: 2px solid rgba(20, 93, 160, 0.2);
        }
        .hov-detail-page .overtime-edit-form > p {
            margin: 4px 0;
            color: #92400e;
            font-size: 0.8125rem;
            line-height: 1.5;
        }
        .hov-detail-page .overtime-edit-form > button {
            justify-self: start;
            min-height: 40px;
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            background: #145da0;
            color: #fff;
            font: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }
        .hov-detail-page .overtime-edit-form > button:hover { background: #0e477d; }

        .hov-rejection-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 13px 15px;
            font-size: 0.875rem;
            color: #b91c1c;
            line-height: 1.65;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 600px) {
            .hov-hero-status {
                align-items: flex-start;
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                padding-top: 10px;
                border-top: 1px dashed #e2e8f0;
            }
            .hov-metrics-grid {
                grid-template-columns: 1fr;
            }
            .hov-approvers-grid {
                grid-template-columns: 1fr;
            }
            .hov-detail-page .overtime-edit-form {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
