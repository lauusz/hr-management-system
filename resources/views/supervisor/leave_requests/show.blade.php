<x-app title="Detail Pengajuan">

    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;

        $backUrl = url()->previous();
        if ($backUrl === url()->current()) {
            $backUrl = route('approval.index');
        }

        $showActionButtons = isset($canApprove) && $canApprove;
        $isDirectSuper = isset($isApprover) && $isApprover;

        $applicantRole = strtoupper((string) ($item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role));
        $isHrdApplicant = in_array($applicantRole, ['HRD', 'HR MANAGER'], true);
        $ackButtonLabel = $isHrdApplicant ? 'Setujui' : 'Mengetahui & Teruskan ke HRD';
        $ackModalTitle = $isHrdApplicant ? 'Setujui Pengajuan Ini?' : 'Mengetahui & Teruskan ke HRD?';
        $ackConfirmLabel = $isHrdApplicant ? 'Ya, Setujui' : 'Ya, Teruskan';
        $ackModalBody = $isHrdApplicant
            ? 'Pengajuan ini akan langsung disetujui sebagai final approval.'
            : 'Mengetahui pengajuan ini dan meneruskannya ke HRD untuk final approval?';

        // Status badge styling
        $statusClass = 'apv-badge--gray';
        $statusLabel = $item->status;
        $statusIcon = '';

        if ($item->status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $statusClass = 'apv-badge--success';
            $statusLabel = $isHrdApplicant ? 'Disetujui oleh Manager' : 'Disetujui Final (HR)';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($item->status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $statusClass = 'apv-badge--error';
            $statusLabel = 'Ditolak';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        } elseif ($item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $statusClass = 'apv-badge--warning';
            $statusLabel = $showActionButtons ? 'Perlu Diketahui' : 'Menunggu Atasan';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } elseif ($item->status === \App\Models\LeaveRequest::PENDING_HR) {
            $statusClass = 'apv-badge--teal';
            $statusLabel = 'Atasan Mengetahui';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        }

        // Type badge styling
        $typeClass = 'apv-type--default';
        if (in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
            $typeClass = 'apv-type--cuti';
        } elseif ($typeValue === \App\Enums\LeaveType::SAKIT->value) {
            $typeClass = 'apv-type--sakit';
        } elseif (in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
            $typeClass = 'apv-type--izin';
        } elseif ($typeValue === \App\Enums\LeaveType::DINAS_LUAR->value) {
            $typeClass = 'apv-type--dinas';
        }
    @endphp

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Pengajuan</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan izin dan cuti</p>
            </div>
        </div>
    </x-slot>

    <div class="apv-detail-page">

        {{-- Back Button --}}
        <a href="{{ $backUrl }}" class="back-btn" aria-label="Kembali ke daftar pengajuan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="apv-alert apv-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="apv-alert apv-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="apv-detail-body">

            {{-- Card: Employee Header --}}
            <div class="apv-card">
                <div class="apv-card-header">
                    <div class="apv-avatar apv-avatar--lg">{{ substr($item->user->name, 0, 1) }}</div>
                    <div class="apv-card-header-info">
                        <h2 class="apv-employee-name">{{ $item->user->name }}</h2>
                        <div class="apv-employee-meta">
                            <span class="apv-role-chip">{{ $item->user->role }}</span>
                            <span class="apv-meta-dot">•</span>
                            <span>{{ $item->user->position->name ?? '-' }}</span>
                            <span class="apv-meta-dot">•</span>
                            <span>{{ $item->user->division->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="apv-card-header-footer">
                    <span class="apv-badge {{ $statusClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                    <span class="apv-submit-time">Diajukan {{ $item->created_at->translatedFormat('j F Y, H:i') }}</span>
                </div>
            </div>

            {{-- Card: Detail Pengajuan --}}
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Detail Pengajuan
                </div>

                <div class="apv-detail-row">
                    <span class="apv-detail-label">Jenis Pengajuan</span>
                    <span class="apv-type {{ $typeClass }}">
                        @if($typeValue === \App\Enums\LeaveType::CUTI->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($typeValue === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($typeValue === \App\Enums\LeaveType::SAKIT->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @else
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                        {{ $item->type_label ?? $item->type }}
                    </span>
                </div>

                @if($typeValue === 'CUTI_KHUSUS' && $item->special_leave_category)
                    @php
                        $catMap = [
                            'NIKAH_KARYAWAN'   => 'Menikah (4 Hari)',
                            'ISTRI_MELAHIRKAN' => 'Istri Melahirkan (2 Hari)',
                            'ISTRI_KEGUGURAN'  => 'Istri Keguguran (2 Hari)',
                            'KHITANAN_ANAK'    => 'Khitanan Anak (2 Hari)',
                            'PEMBAPTISAN_ANAK' => 'Pembaptisan Anak (2 Hari)',
                            'NIKAH_ANAK'       => 'Pernikahan Anak (2 Hari)',
                            'DEATH_EXTENDED'   => 'Kematian Adik/Kakak/Ipar (2 Hari)',
                            'DEATH_CORE'       => 'Kematian Inti (2 Hari)',
                            'DEATH_HOUSE'      => 'Kematian Anggota Rumah (1 Hari)',
                            'HAJI'             => 'Ibadah Haji (40 Hari)',
                            'UMROH'            => 'Ibadah Umroh (14 Hari)',
                            'CUTI_MELAHIRKAN'  => 'Cuti Melahirkan (90 Hari)',
                        ];
                        $catLabel = $catMap[$item->special_leave_category] ?? $item->special_leave_category;
                    @endphp
                    <div class="apv-detail-row">
                        <span class="apv-detail-label">Detail Cuti Khusus</span>
                        <span class="apv-cuti-khusus-badge">{{ $catLabel }}</span>
                    </div>
                @endif

                <div class="apv-detail-row">
                    <span class="apv-detail-label">Periode Izin</span>
                    <div class="apv-date-display">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $item->start_date->translatedFormat('j F Y') }}</span>
                        @if($item->end_date && $item->end_date->ne($item->start_date))
                            <span class="apv-date-sep">—</span>
                            <span>{{ $item->end_date->translatedFormat('j F Y') }}</span>
                        @endif
                    </div>
                </div>

                @php
                    $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                    $endTimeLabel   = $item->end_time ? $item->end_time->format('H:i') : null;
                @endphp

                @if($startTimeLabel)
                <div class="apv-detail-row">
                    <span class="apv-detail-label">
                        @if($endTimeLabel)
                            Jam Izin
                        @elseif($typeValue === 'IZIN_TELAT')
                            Estimasi Jam Tiba
                        @elseif($typeValue === 'IZIN_PULANG_AWAL')
                            Jam Pulang Awal
                        @else
                            Jam Mulai
                        @endif
                    </span>
                    <span class="apv-time-display">{{ $startTimeLabel }}{{ $endTimeLabel ? ' — ' . $endTimeLabel : '' }}</span>
                </div>
                @endif

                @php
                    $showShortNoticeWarning = false;
                    $shortNoticeDaysDiff = 0;
                    if ($typeValue === 'CUTI' && $item->start_date && $item->created_at) {
                        $start = $item->start_date->copy()->startOfDay();
                        $submitted = $item->created_at->copy()->startOfDay();
                        $shortNoticeDaysDiff = $submitted->diffInDays($start, false);
                        $showShortNoticeWarning = ($shortNoticeDaysDiff < 7 && $shortNoticeDaysDiff >= 0);
                    }
                @endphp

                @if($showShortNoticeWarning)
                <div class="apv-warning-banner">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <span class="apv-warning-title">Pengajuan Mendadak</span>
                        <span class="apv-warning-text">H-{{ $shortNoticeDaysDiff }} (kurang dari H-7)</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Card: Alasan --}}
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Alasan & Keterangan
                </div>

                <div class="apv-reason-box">
                    {{ $item->reason }}
                </div>

                @if($item->substitute_pic)
                <div class="apv-detail-row apv-detail-row--full" style="margin-top: 12px;">
                    <span class="apv-detail-label">PIC Pengganti</span>
                    <div class="apv-pic-box">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>{{ $item->substitute_pic }}</span>
                        @if($item->substitute_phone)
                            <span class="apv-pic-phone">{{ $item->substitute_phone }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Card: Foto & Lokasi --}}
            @php
                $url = $item->photo
                    ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                    : null;
            @endphp

            @if($url || ($item->latitude && $item->longitude))
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Bukti & Lokasi
                </div>

                @if($url)
                <div class="apv-detail-row apv-detail-row--full">
                    <span class="apv-detail-label">Bukti Pendukung</span>
                    <div class="apv-photo-preview js-view-photo" data-url="{{ $url }}">
                        <img src="{{ $url }}" alt="Bukti Izin">
                        <div class="apv-photo-overlay">
                            <svg width="20" height="20" fill="none" stroke="#fff" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Lihat Foto</span>
                        </div>
                    </div>
                </div>
                @endif

                @if($item->latitude && $item->longitude)
                <div class="apv-detail-row apv-detail-row--full">
                    <span class="apv-detail-label">Lokasi Pengajuan <span class="apv-accuracy">(±{{ (int)$item->accuracy_m }}m)</span></span>
                    <div class="apv-map-container">
                        <iframe src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed" loading="lazy" allowfullscreen></iframe>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="apv-link-map">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- Card: Status & Approval --}}
            @if($item->approved_by || $item->notes)
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat Approval
                </div>

                @if($item->approved_by)
                <div class="apv-detail-row apv-detail-row--full">
                    <span class="apv-detail-label">Diputuskan Oleh</span>
                    <div class="apv-approver-box">
                        <div class="apv-avatar">{{ substr($item->approver?->name ?? '—', 0, 1) }}</div>
                        <div class="apv-approver-info">
                            <span class="apv-approver-name">{{ $item->approver?->name ?? '-' }}</span>
                            @if($item->approved_at)
                                <span class="apv-approver-time">{{ $item->approved_at->translatedFormat('j F Y, H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($item->notes)
                <div class="apv-detail-row apv-detail-row--full">
                    <span class="apv-detail-label">Catatan Sistem / Revisi</span>
                    <div class="apv-notes-box">{!! nl2br(e($item->notes)) !!}</div>
                </div>
                @endif
            </div>
            @endif


        </div>
    </div>

    {{-- Fixed Action Bar --}}
    <div class="apv-action-bar">
        <div class="apv-action-dock">
            <div class="apv-action-main">

            @if($showActionButtons)
                {{-- Primary action: Acknowledge --}}
                <button type="button" data-modal-target="modal-ack" class="apv-action-btn apv-action-btn--primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $ackButtonLabel }}
                </button>

                {{-- Secondary actions row --}}
                <div class="apv-action-secondary">
                    @if($isDirectSuper)
                        <a href="{{ route('approval.edit', $item->id) }}" class="apv-action-btn apv-action-btn--secondary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>

                        @if(!in_array($item->status, ['BATAL', 'REJECTED']))
                            <button type="button" data-modal-target="modal-delete" class="apv-action-btn apv-action-btn--danger">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Batal
                            </button>
                        @endif
                    @endif

                    <button type="button" data-modal-target="modal-reject" class="apv-action-btn apv-action-btn--outline-danger">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak
                    </button>
                </div>

            @elseif(auth()->id() === ($item->user->manager_id ?? null) && !empty($item->user->direct_supervisor_id))
                <div class="apv-status-notice">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pengajuan ini menunggu Supervisor langsung untuk mengetahui.
                </div>

            @elseif($isDirectSuper && !in_array($item->status, ['BATAL', 'REJECTED']))
                {{-- Direct supervisor without ack rights (status already moved) --}}
                <div class="apv-action-secondary">
                    <a href="{{ route('approval.edit', $item->id) }}" class="apv-action-btn apv-action-btn--secondary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <button type="button" data-modal-target="modal-delete" class="apv-action-btn apv-action-btn--danger">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Batal
                    </button>
                </div>
            @endif
        </div>
        </div>
    </div>

    {{-- Photo Viewer Overlay --}}
    <div id="simple-viewer" class="apv-viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="apv-viewer-close">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    {{-- Modals --}}
    <x-modal id="modal-delete" title="Ajukan Pembatalan?" type="confirm" variant="danger" confirmLabel="Ya, Ajukan" cancelLabel="Batal" :confirmFormAction="route('approval.destroy', $item->id)" confirmFormMethod="DELETE">
        <p style="margin:0; color:#374151;">Anda akan mengajukan permintaan pembatalan untuk pengajuan ini ke HRD.</p>
    </x-modal>

    @if($showActionButtons)
    <x-modal id="modal-reject" title="Tolak Pengajuan Ini?" type="confirm" variant="danger" confirmLabel="Ya, Tolak" cancelLabel="Batal" :confirmFormAction="route('approval.reject', $item->id)" confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">Yakin menolak pengajuan <strong>{{ $item->user->name }}</strong>?</p>
    </x-modal>

    <x-modal id="modal-ack" :title="$ackModalTitle" type="confirm" variant="success" :confirmLabel="$ackConfirmLabel" cancelLabel="Batal" :confirmFormAction="route('approval.ack', $item->id)" confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">{{ $ackModalBody }}</p>
    </x-modal>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');

            document.querySelectorAll('.js-view-photo').forEach(el => {
                el.addEventListener('click', () => {
                    const url = el.getAttribute('data-url');
                    if (url && viewer && viewerImg) {
                        viewerImg.src = url;
                        viewer.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            function closeViewer() {
                if (viewer) viewer.style.display = 'none';
                if (viewerImg) viewerImg.src = '';
                document.body.style.overflow = '';
            }

            if (closeBtn) closeBtn.addEventListener('click', closeViewer);
            if (viewer) viewer.addEventListener('click', (e) => { if (e.target === viewer) closeViewer(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer.style.display === 'flex') closeViewer(); });
        });
    </script>

    <style>
        /* ========================================== */
        /* PAGE LAYOUT                                */
        /* ========================================== */
        .apv-detail-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 160px;
        }
        @media (min-width: 640px) {
            .apv-detail-page {
                padding-bottom: 120px;
            }
        }
        @media (min-width: 768px) {
            .apv-detail-page {
                padding-bottom: 100px;
            }
        }

        .apv-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* BACK BUTTON (KIMI.md pattern)              */
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
        /* CARDS                                      */
        /* ========================================== */
        .apv-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .apv-section-title {
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
        .apv-section-title svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* CARD HEADER (Employee)                     */
        /* ========================================== */
        .apv-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .apv-avatar {
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
        .apv-avatar--lg {
            width: 52px;
            height: 52px;
            font-size: 1.25rem;
            border-radius: 12px;
        }
        .apv-card-header-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .apv-employee-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .apv-employee-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .apv-role-chip {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .apv-meta-dot {
            color: var(--border-light, #E5E7EB);
        }

        .apv-card-header-footer {
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
        .apv-badge {
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
        .apv-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .apv-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .apv-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .apv-badge--teal    { background: rgba(20, 184, 166, 0.1); color: #0f766e; border: 1px solid rgba(20, 184, 166, 0.2); }
        .apv-badge--gray    { background: #F8FAFC; color: var(--text-secondary, #374151); }

        .apv-submit-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* Type badge */
        .apv-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .apv-type--default { background: #F8FAFC; color: var(--text-secondary, #374151); }
        .apv-type--cuti    { background: rgba(59, 130, 246, 0.1); color: var(--info, #3B82F6); }
        .apv-type--sakit   { background: rgba(245, 158, 11, 0.1); color: #b45309; }
        .apv-type--izin    { background: rgba(20, 93, 160, 0.08); color: var(--primary, #145DA0); }
        .apv-type--dinas   { background: rgba(147, 51, 234, 0.1); color: var(--purple, #9333EA); }

        .apv-cuti-khusus-badge {
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.1);
            color: var(--info, #3B82F6);
            padding: 5px 10px;
            border-radius: 8px;
        }

        /* ========================================== */
        /* DETAIL ROWS                                */
        /* ========================================== */
        .apv-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            gap: 12px;
        }
        .apv-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .apv-detail-row:first-child { padding-top: 0; }
        .apv-detail-row--full {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .apv-detail-label {
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
        .apv-date-display {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .apv-date-display svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .apv-date-sep {
            color: var(--text-muted, #6B7280);
            font-weight: 400;
        }
        .apv-time-display {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }

        /* ========================================== */
        /* WARNING BANNER                             */
        /* ========================================== */
        .apv-warning-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 10px;
            padding: 12px 14px;
            margin-top: 8px;
        }
        .apv-warning-banner svg {
            color: #d97706;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .apv-warning-title {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 2px;
        }
        .apv-warning-text {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #92400e;
        }

        /* ========================================== */
        /* REASON BOX                                 */
        /* ========================================== */
        .apv-reason-box {
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
        /* PIC BOX                                    */
        /* ========================================== */
        .apv-pic-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary, #111827);
            flex-wrap: wrap;
        }
        .apv-pic-box svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .apv-pic-phone {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* APPROVER BOX                               */
        /* ========================================== */
        .apv-approver-box {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        .apv-approver-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .apv-approver-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .apv-approver-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* NOTES BOX                                  */
        /* ========================================== */
        .apv-notes-box {
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 10px;
            padding: 14px;
            font-size: 0.8125rem;
            color: #92400e;
            line-height: 1.6;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* PHOTO                                      */
        /* ========================================== */
        .apv-photo-preview {
            position: relative;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-light, #E5E7EB);
            cursor: pointer;
        }
        .apv-photo-preview img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 300px;
            object-fit: cover;
        }
        .apv-photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .apv-photo-preview:hover .apv-photo-overlay { opacity: 1; }
        .apv-photo-overlay span { color: #fff; font-size: 0.75rem; font-weight: 600; }

        /* ========================================== */
        /* MAP                                        */
        /* ========================================== */
        .apv-map-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid var(--border-light, #E5E7EB);
            width: 100%;
            box-sizing: border-box;
        }
        .apv-map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .apv-link-map {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary, #145DA0);
            text-decoration: none;
            margin-top: 8px;
        }
        .apv-link-map:hover { color: var(--primary-dark, #0A3D62); }
        .apv-link-map svg { flex-shrink: 0; }
        .apv-accuracy {
            font-weight: 400;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* FIXED ACTION BAR                           */
        /* ========================================== */
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            pointer-events: none;
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        right 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        bottom 0.35s ease;
        }
        .apv-action-dock {
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
        }
        .apv-action-main {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
            min-height: 44px;
        }
        .apv-action-btn svg { flex-shrink: 0; }

        /* Primary: full width, gradient */
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            width: 100%;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            font-size: 0.875rem;
            padding: 14px 16px;
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        /* Secondary row */
        .apv-action-secondary {
            display: flex;
            gap: 8px;
            width: 100%;
        }
        .apv-action-secondary .apv-action-btn {
            flex: 1;
            padding: 10px 8px;
            font-size: 0.75rem;
        }

        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover {
            background: var(--white, #FFFFFF);
            border-color: var(--gray-300, #D1D5DB);
        }

        .apv-action-btn--outline-danger {
            background: var(--white, #FFFFFF);
            color: var(--error, #EF4444);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
        }
        .apv-action-btn--outline-danger:hover {
            background: rgba(239, 68, 68, 0.06);
            border-color: rgba(239, 68, 68, 0.5);
        }

        .apv-action-btn--danger {
            background: rgba(239, 68, 68, 0.08);
            color: var(--error, #EF4444);
            border: 1.5px solid rgba(239, 68, 68, 0.2);
        }
        .apv-action-btn--danger:hover {
            background: rgba(239, 68, 68, 0.12);
        }

        .apv-status-notice {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            background: rgba(245, 158, 11, 0.08);
            color: #a16207;
            border: 1px solid rgba(245, 158, 11, 0.2);
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }
        .apv-status-notice svg { flex-shrink: 0; }

        /* ========================================== */
        /* PHOTO VIEWER                               */
        /* ========================================== */
        .apv-viewer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .apv-viewer-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 100000;
        }
        .apv-viewer-close:hover { background: rgba(255,255,255,0.25); }
        #simple-viewer-img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .apv-action-secondary .apv-action-btn {
                font-size: 0.8125rem;
                padding: 12px 16px;
            }
        }

        @media (min-width: 640px) {
            .apv-action-main {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
            }
            .apv-action-btn--primary {
                width: auto;
                flex: 1;
                order: 2;
            }
            .apv-action-secondary {
                width: auto;
                flex: 1;
                order: 1;
            }
            .apv-action-secondary .apv-action-btn {
                flex: none;
            }
        }

        @media (min-width: 768px) {
            .apv-action-main {
                flex-wrap: nowrap;
            }
            .apv-action-btn--primary {
                flex: none;
                padding: 12px 28px;
            }
            .apv-action-secondary {
                flex: none;
            }
        }

        @media (min-width: 1025px) {
            .apv-action-bar {
                left: calc(var(--sidebar-width) + 12px);
                right: 0;
                bottom: 16px;
                padding: 0 32px;
            }
            .app.sidebar-collapsed .apv-action-bar {
                left: 0;
            }
            .apv-action-dock {
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 18px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
                padding: 12px 20px calc(12px + env(safe-area-inset-bottom));
            }
        }
    </style>

</x-app>
