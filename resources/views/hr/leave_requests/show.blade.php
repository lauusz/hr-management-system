<x-app title="Detail Pengajuan (HR)">

    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;
        $isTypeCuti = ($typeValue === 'CUTI');
        $isTypeSakit = ($typeValue === 'SAKIT');
        $isTypeIzin = ($typeValue === 'IZIN');
        $isTypeCutiKhusus = ($typeValue === 'CUTI_KHUSUS');
        $isTypeDinasLuar = ($typeValue === 'DINAS_LUAR');
        $showDeductOptions = ($isTypeCuti || $isTypeCutiKhusus || $isTypeDinasLuar);

        // Status
        $status = $item->status;
        $statusClass = 'apv-badge--gray';
        $statusLabel = $item->status_label ?? $status;
        $statusIcon = '';

        if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $statusClass = 'apv-badge--success';
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
            $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $statusClass = 'apv-badge--error';
            $statusLabel = 'Ditolak';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $statusClass = 'apv-badge--warning';
            $statusLabel = 'Menunggu Atasan';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
            $statusClass = 'apv-badge--teal';
            $statusLabel = 'Verifikasi HRD';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
        } elseif ($status === 'BATAL') {
            $statusClass = 'apv-badge--gray';
            $statusLabel = 'Dibatalkan';
        }

        // Type badge
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

        // Leave balance
        $balance = $item->user->leave_balance ?? 0;

        // Duration
        $start = $item->start_date;
        $end = $item->end_date ?? $start;
        $days = $start->diffInDays($end) + 1;
        $durationLabel = $days == 1 ? '1 hari' : $days . ' hari';

        // Time labels
        $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
        $endTimeLabel = $item->end_time ? $item->end_time->format('H:i') : null;

        // Photo URL
        $photoUrl = $item->photo
            ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
            : null;

        // H-7 Warning
        $showShortNoticeWarning = false;
        $shortNoticeDaysDiff = 0;
        if ($isTypeCuti && $item->start_date && $item->created_at) {
            $s = $item->start_date->copy()->startOfDay();
            $submitted = $item->created_at->copy()->startOfDay();
            $shortNoticeDaysDiff = $submitted->diffInDays($s, false);
            $showShortNoticeWarning = ($shortNoticeDaysDiff < 7 && $shortNoticeDaysDiff >= 0);
        }

        // Action logic
        $user = auth()->user();
        $isHrStaff = $user->isHR();
        $canHrEdit = $isHrStaff && $item->status !== 'BATAL';
        $isRejectedOrBatal = in_array($item->status, [\App\Models\LeaveRequest::STATUS_REJECTED, 'BATAL'], true);

        $applicantRoleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
        $applicantRole = strtoupper((string) $applicantRoleVal);
        $actorRoleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
        $actorRole = strtoupper((string) $actorRoleVal);
        $needsHrdOnly = in_array($applicantRole, ['MANAGER', 'HRD', 'HR STAFF'], true);
        $isHrdMaster = $actorRole === 'HRD';
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
                <p class="section-subtitle">Verifikasi dan proses pengajuan izin & cuti</p>
            </div>
        </div>
    </x-slot>

    <div class="apv-detail-page">

        {{-- Back Button --}}
        <button type="button" class="back-btn" onclick="sessionStorage.setItem('hrLeaveForceRefreshOnBack', '1'); history.back();">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </button>

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

            {{-- Employee Header Card --}}
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

            {{-- Balance Chip (for Cuti types) --}}
            @if($isTypeCuti || $typeValue === 'CUTI_KHUSUS')
            <div class="hr-balance-chip {{ $balance > 0 ? 'hr-balance--ok' : 'hr-balance--low' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Sisa Cuti: <strong>{{ $balance }} hari</strong></span>
            </div>
            @endif

            {{-- Detail Pengajuan Card --}}
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
                        @if($typeValue === 'CUTI')
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($typeValue === 'CUTI_KHUSUS')
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($typeValue === 'SAKIT')
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
                        <span>{{ $start->translatedFormat('l, j F Y') }}</span>
                        @if($end->ne($start))
                            <span class="apv-date-sep">—</span>
                            <span>{{ $end->translatedFormat('l, j F Y') }}</span>
                        @endif
                    </div>
                </div>

                @if($startTimeLabel)
                <div class="apv-detail-row">
                    <span class="apv-detail-label">
                        @if($endTimeLabel) Jam Izin
                        @elseif($typeValue === 'IZIN_TELAT') Estimasi Jam Tiba
                        @elseif($typeValue === 'IZIN_PULANG_AWAL') Jam Pulang Awal
                        @else Jam Mulai @endif
                    </span>
                    <span class="apv-time-display">{{ $startTimeLabel }}{{ $endTimeLabel ? ' — ' . $endTimeLabel : '' }}</span>
                </div>
                @endif

                <div class="apv-detail-row">
                    <span class="apv-detail-label">Durasi</span>
                    <span class="apv-duration-badge">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $durationLabel }}
                    </span>
                </div>

                @if($showShortNoticeWarning)
                <div class="apv-warning-banner">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <span class="apv-warning-title">Pengajuan Mendadak</span>
                        <span class="apv-warning-text">H-{{ $shortNoticeDaysDiff }} (kurang dari H-7) — Termasuk Potong Uang Makan</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Card: Alasan & Keterangan --}}
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

            {{-- Card: Bukti & Lokasi --}}
            @if($photoUrl || ($item->latitude && $item->longitude))
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Bukti & Lokasi
                </div>

                @if($photoUrl)
                <div class="apv-detail-row apv-detail-row--full">
                    <span class="apv-detail-label">Bukti Pendukung</span>
                    <div class="apv-photo-preview js-view-photo" data-url="{{ $photoUrl }}">
                        <img src="{{ $photoUrl }}" alt="Bukti Izin">
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

            {{-- Card: Riwayat Approval --}}
            @if($item->approved_by || $item->notes_hrd || $item->notes || $item->deduct_um)
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat Approval & Catatan
                </div>

                @if($item->approved_by && $item->approver)
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

                @if($item->deduct_um)
                <div class="hr-note-box hr-note--warning">
                    <div class="hr-note-header">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/>
                        </svg>
                        Potong Uang Makan (UM)
                    </div>
                    <div class="hr-note-content">
                        {!! nl2br(e($item->notes_hrd ?? 'Potong UM')) !!}
                    </div>
                </div>
                @endif

                @if($item->notes_hrd && !$item->deduct_um)
                    @php
                        if ($item->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                            $noteVariant = 'error';
                            $noteLabel = 'Alasan Penolakan';
                        } else {
                            $noteVariant = 'info';
                            $noteLabel = 'Catatan HRD';
                        }
                    @endphp
                <div class="hr-note-box hr-note--{{ $noteVariant }}">
                    <div class="hr-note-header">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        {{ $noteLabel }}
                    </div>
                    <div class="hr-note-content">{{ $item->notes_hrd }}</div>
                </div>
                @endif

                @if($item->notes)
                <div class="hr-note-box hr-note--system">
                    <div class="hr-note-header">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Catatan Sistem
                    </div>
                    <div class="hr-note-content">{!! nl2br(e($item->notes)) !!}</div>
                </div>
                @endif
            </div>
            @endif

            {{-- Bottom Spacer --}}
            <div class="apv-bottom-spacer"></div>
        </div>
    </div>

    {{-- FIXED ACTION BAR --}}
    <div class="apv-action-bar">
        <div class="apv-action-main">
            @if($item->status === 'BATAL')
                <div class="apv-status-notice apv-status-notice--gray">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Pengajuan Sudah Dibatalkan
                </div>
            @elseif($canHrEdit)
                {{-- Edit & Batal --}}
                <div class="apv-action-secondary">
                    <button type="button" data-modal-target="modal-edit-hr" class="apv-action-btn apv-action-btn--secondary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                    @if(!$isRejectedOrBatal)
                        <button type="button" data-modal-target="modal-delete" class="apv-action-btn apv-action-btn--danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Batalkan
                        </button>
                    @endif
                </div>

                {{-- Primary actions --}}
                @if($canApprove && $item->status === \App\Models\LeaveRequest::PENDING_HR)
                    <div class="apv-action-primary-row">
                        <button type="button" data-modal-target="modal-reject" class="apv-action-btn apv-action-btn--outline-danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" data-modal-target="modal-approve" class="apv-action-btn apv-action-btn--primary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terima
                        </button>
                    </div>
                @elseif($canApproveAsSupervisor && $item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    <div class="apv-action-primary-row">
                        <button type="button" data-modal-target="modal-supervisor-reject" class="apv-action-btn apv-action-btn--outline-danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" data-modal-target="modal-supervisor-approve" class="apv-action-btn apv-action-btn--primary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui
                        </button>
                    </div>
                @elseif($item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    <div class="apv-status-notice apv-status-notice--gray">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu atasan mengetahui pengajuan ini.
                    </div>
                @elseif($isHrStaff && $item->status === \App\Models\LeaveRequest::PENDING_HR && $needsHrdOnly && !$isHrdMaster)
                    <div class="apv-status-notice apv-status-notice--info">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Pengajuan dari {{ $item->user->role }} hanya dapat disetujui oleh HRD.
                    </div>
                @endif
            @elseif(!$isRejectedOrBatal)
                {{-- Non-HR edit & batal --}}
                <div class="apv-action-secondary">
                    <button type="button" data-modal-target="modal-edit-hr" class="apv-action-btn apv-action-btn--secondary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                    <button type="button" data-modal-target="modal-delete" class="apv-action-btn apv-action-btn--danger">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Batalkan
                    </button>
                </div>

                @if($canApprove && $item->status === \App\Models\LeaveRequest::PENDING_HR)
                    <div class="apv-action-primary-row">
                        <button type="button" data-modal-target="modal-reject" class="apv-action-btn apv-action-btn--outline-danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" data-modal-target="modal-approve" class="apv-action-btn apv-action-btn--primary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terima
                        </button>
                    </div>
                @elseif($canApproveAsSupervisor && $item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    <div class="apv-action-primary-row">
                        <button type="button" data-modal-target="modal-supervisor-reject" class="apv-action-btn apv-action-btn--outline-danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" data-modal-target="modal-supervisor-approve" class="apv-action-btn apv-action-btn--primary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui
                        </button>
                    </div>
                @elseif($item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    <div class="apv-status-notice apv-status-notice--gray">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu atasan mengetahui pengajuan ini.
                    </div>
                @elseif($isHrStaff && $item->status === \App\Models\LeaveRequest::PENDING_HR && $needsHrdOnly && !$isHrdMaster)
                    <div class="apv-status-notice apv-status-notice--info">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Pengajuan dari {{ $item->user->role }} hanya dapat disetujui oleh HRD.
                    </div>
                @endif
            @else
                <div class="apv-status-notice apv-status-notice--gray">
                    {{ $statusLabel }}
                </div>
            @endif
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


    {{-- MODAL: Edit HR --}}
    <x-modal id="modal-edit-hr" title="Edit Data Pengajuan" type="form" style="max-width: 720px;">
        <form action="{{ route('hr.leave.update', $item->id) }}" method="POST" id="form-edit-hr" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="edit-modal-content">
                @if($isHrStaff)
                <div class="edit-section edit-section--status">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Status Pengajuan</span>
                    </div>
                    @php
                        $statusConfig = [
                            \App\Models\LeaveRequest::PENDING_SUPERVISOR => ['label' => 'Menunggu Atasan', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
                            \App\Models\LeaveRequest::PENDING_HR => ['label' => 'Menunggu HRD', 'color' => '#0891b2', 'bg' => '#cffafe'],
                            \App\Models\LeaveRequest::STATUS_APPROVED => ['label' => 'Disetujui', 'color' => '#16a34a', 'bg' => '#dcfce7'],
                            \App\Models\LeaveRequest::STATUS_REJECTED => ['label' => 'Ditolak', 'color' => '#dc2626', 'bg' => '#fee2e2'],
                            'BATAL' => ['label' => 'Dibatalkan', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                        ];
                        $currentStatusConfig = $statusConfig[$item->status] ?? ['label' => $item->status, 'color' => '#6b7280', 'bg' => '#f3f4f6'];
                    @endphp
                    <div class="edit-status-selector">
                        <select name="status" id="edit_status" class="edit-status-select">
                            @foreach($statusConfig as $statusVal => $config)
                                <option value="{{ $statusVal }}" @selected($item->status === $statusVal) data-color="{{ $config['color'] }}" data-bg="{{ $config['bg'] }}">
                                    {{ $config['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="edit-status-indicator" id="status_indicator" style="background: {{ $currentStatusConfig['bg'] }}; color: {{ $currentStatusConfig['color'] }};">
                            {{ $currentStatusConfig['label'] }}
                        </div>
                    </div>
                </div>
                @endif

                <div class="edit-section">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Informasi Utama</span>
                    </div>
                    <div class="edit-form-group">
                        <label class="edit-form-label">Jenis Pengajuan</label>
                        <select name="type" id="edit_type" class="edit-form-select">
                            @foreach(\App\Enums\LeaveType::cases() as $type)
                                <option value="{{ $type->value }}" @selected($typeValue == $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="edit-form-group" id="edit_special_wrapper" style="display: none;">
                        <label class="edit-form-label">Kategori Cuti Khusus</label>
                        <select name="special_leave_detail" class="edit-form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @php
                                $specialList = [
                                    'NIKAH_KARYAWAN' => 'Menikah', 'ISTRI_MELAHIRKAN' => 'Istri Melahirkan',
                                    'ISTRI_KEGUGURAN' => 'Istri Keguguran', 'KHITANAN_ANAK' => 'Khitanan Anak',
                                    'PEMBAPTISAN_ANAK' => 'Pembaptisan Anak', 'NIKAH_ANAK' => 'Pernikahan Anak',
                                    'DEATH_CORE' => 'Kematian Inti', 'DEATH_EXTENDED' => 'Kematian Saudara/Ipar',
                                    'DEATH_HOUSE' => 'Kematian Anggota Rumah', 'HAJI' => 'Ibadah Haji', 'UMROH' => 'Ibadah Umroh'
                                ];
                            @endphp
                            @foreach($specialList as $code => $label)
                                <option value="{{ $code }}" @selected($item->special_leave_category == $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label class="edit-form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="edit-form-input" value="{{ $item->start_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="edit-form-group">
                            <label class="edit-form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="edit-form-input" value="{{ $item->end_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="edit-section">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Detail Waktu</span>
                    </div>
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label class="edit-form-label">Jam Mulai <span class="edit-optional">(opsional)</span></label>
                            <input type="time" name="start_time" class="edit-form-input" value="{{ $item->start_time ? $item->start_time->format('H:i') : '' }}">
                        </div>
                        <div class="edit-form-group">
                            <label class="edit-form-label">Jam Selesai <span class="edit-optional">(opsional)</span></label>
                            <input type="time" name="end_time" class="edit-form-input" value="{{ $item->end_time ? $item->end_time->format('H:i') : '' }}">
                        </div>
                    </div>
                </div>

                <div class="edit-section">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Penugasan Pengganti</span>
                    </div>
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label class="edit-form-label">PIC Pengganti</label>
                            <input type="text" name="substitute_pic" class="edit-form-input" placeholder="Nama pengganti" value="{{ $item->substitute_pic }}">
                        </div>
                        <div class="edit-form-group">
                            <label class="edit-form-label">No. HP PIC</label>
                            <input type="text" name="substitute_phone" class="edit-form-input" placeholder="08xxxxxxxxxx" value="{{ $item->substitute_phone }}">
                        </div>
                    </div>
                </div>

                <div class="edit-section edit-section--notes">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Catatan</span>
                    </div>
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label class="edit-form-label">Alasan Karyawan</label>
                            <textarea name="reason" rows="3" class="edit-form-textarea" placeholder="Jelaskan alasan pengajuan...">{{ $item->reason }}</textarea>
                        </div>
                        <div class="edit-form-group">
                            <label class="edit-form-label">Catatan HRD <span class="edit-optional">(internal)</span></label>
                            <textarea name="notes_hrd" rows="3" class="edit-form-textarea" placeholder="Catatan untuk karyawan...">{{ $item->notes_hrd }}</textarea>
                        </div>
                    </div>
                </div>

                @if(in_array($typeValue, ['SAKIT', 'IZIN']))
                <div class="edit-section edit-section--warning">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"/></svg>
                        <span>Potongan</span>
                    </div>
                    <div class="edit-form-group">
                        <label class="edit-checkbox-wrapper">
                            <input type="checkbox" name="deduct_um_edit" value="1" id="deduct_um_edit" style="width: 16px; height: 16px; accent-color: #f59e0b; cursor: pointer;" {{ $item->deduct_um ? 'checked' : '' }}>
                            <span>Potong UM (Uang Makan)</span>
                        </label>
                        <small style="display: block; margin-top: 4px; color: #92400e; font-size: 12px; margin-left: 24px;">
                            Centang jika potongan uang makan apply untuk izin ini.
                        </small>
                    </div>
                </div>
                @endif

                <div class="edit-section edit-section--upload">
                    <div class="edit-section-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Lampiran</span>
                    </div>
                    <div class="edit-upload-area">
                        <input type="file" name="photo" id="edit_photo" class="edit-upload-input" accept="image/*,.pdf">
                        <label for="edit_photo" class="edit-upload-label">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Klik untuk upload atau drag file ke sini</span>
                            <small>JPG, PNG, PDF (max 8MB)</small>
                        </label>
                        @if($item->photo)
                        <div class="edit-current-file">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            File saat ini: {{ $item->photo }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="edit-modal-footer">
                <button type="button" data-modal-close="true" class="edit-btn-cancel">Batal</button>
                <button type="submit" class="edit-btn-save">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL: Delete / Cancel --}}
    <x-modal id="modal-delete" title="Yakin Ingin Membatalkan?" variant="danger" type="form">
        <form action="{{ route('leave-requests.destroy', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <p style="margin:0; color:var(--text-secondary, #374151);">
                Pengajuan ini akan dibatalkan dan tidak akan diproses lebih lanjut.
            </p>
            <p style="margin:8px 0 0 0; font-size:0.85rem; color:var(--text-muted, #6B7280);">
                Data tetap tersimpan sebagai riwayat.
            </p>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="edit-btn-cancel" style="padding:8px 16px;">Batal</button>
                <button type="submit" class="edit-btn-danger" style="padding:8px 16px;">Ya, Batalkan</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL: Reject --}}
    <x-modal id="modal-reject" title="Tolak Pengajuan Ini?" variant="danger" type="form">
        <form action="{{ route('hr.leave.reject', $item) }}" method="POST" style="width:100%;">
            @csrf
            <p style="margin:0 0 12px 0; color:var(--text-secondary, #374151);">
                Berikan alasan penolakan agar karyawan dapat memahami keputusannya.
            </p>
            <div class="edit-form-group">
                <label class="edit-form-label">Alasan Penolakan <span style="color:var(--error, #EF4444)">*</span></label>
                <textarea name="notes_hrd" rows="3" class="edit-form-textarea" placeholder="Contoh: Kuota cuti tahunan sudah habis / Dokumen tidak lengkap." required></textarea>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="edit-btn-cancel" style="padding:8px 16px;">Batal</button>
                <button type="submit" class="edit-btn-danger" style="padding:8px 16px;">Ya, Tolak</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL: Approve --}}
    <x-modal id="modal-approve" title="Terima Pengajuan Ini?" variant="success" type="form">
        <form action="{{ route('hr.leave.approve', $item) }}" method="POST" style="width:100%;">
            @csrf
            <p style="margin:0 0 12px 0; color:var(--text-secondary, #374151);">
                Pengajuan akan disetujui dan diproses sesuai kebijakan yang berlaku.
            </p>

            @if($isTypeSakit)
            <div class="edit-form-group" style="margin-bottom: 15px; background: var(--gray-50, #F5F7FA); padding: 12px; border-radius: 10px; border: 1px solid var(--border-light, #E5E7EB);">
                <label class="edit-checkbox-wrapper">
                    <input type="checkbox" name="deduct_leave_sakit" value="1" id="deduct_leave_sakit" onclick="toggleSakitOptions()">
                    <span>Potong Cuti?</span>
                </label>
                <div id="sakit-deduct-options" style="display:none; margin-top: 10px; margin-left: 24px;">
                    <label class="edit-radio-wrapper">
                        <input type="radio" name="deduct_amount_sakit" value="1">
                        <span>Potong Cuti (Full)</span>
                    </label>
                    <label class="edit-radio-wrapper">
                        <input type="radio" name="deduct_amount_sakit" value="0.5">
                        <span>Potong Cuti Setengah Hari (0.5)</span>
                    </label>
                </div>
                @if($balance <= 0)
                <div id="sakit-um-notice" style="margin-top: 8px; margin-left: 24px; display:none;">
                    <small style="color: var(--error, #EF4444); font-size: 12px;">* Saldo cuti habis, otomatis dialihkan ke Potong UM</small>
                </div>
                @endif
            </div>
            @endif

            @if($isTypeSakit && $balance <= 0)
            <div class="edit-form-group" style="margin-bottom: 15px; background: rgba(245, 158, 11, 0.08); padding: 12px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <label class="edit-checkbox-wrapper">
                    <input type="checkbox" name="deduct_um" value="1">
                    <span style="color: #92400e;">Potong UM (Uang Makan)</span>
                </label>
                <small style="display: block; margin-top: 4px; color: #92400e; font-size: 12px; margin-left: 24px;">
                    Karyawan tidak memiliki saldo cuti.
                </small>
            </div>
            @endif

            @if($isTypeIzin)
            <div class="edit-form-group" style="margin-bottom: 15px; background: var(--gray-50, #F5F7FA); padding: 12px; border-radius: 10px; border: 1px solid var(--border-light, #E5E7EB);">
                <label class="edit-checkbox-wrapper">
                    <input type="checkbox" name="deduct_leave_izin" value="1" id="deduct_leave_izin" onclick="toggleIzinOptions()">
                    <span>Potong Cuti?</span>
                </label>
                <div id="izin-deduct-options" style="display:none; margin-top: 10px; margin-left: 24px;">
                    <label class="edit-radio-wrapper">
                        <input type="radio" name="deduct_amount_izin" value="1">
                        <span>Potong Cuti (Full)</span>
                    </label>
                    <label class="edit-radio-wrapper">
                        <input type="radio" name="deduct_amount_izin" value="0.5">
                        <span>Potong Cuti Setengah Hari (0.5)</span>
                    </label>
                </div>
                @if($balance <= 0)
                <div id="izin-um-notice" style="margin-top: 8px; margin-left: 24px;">
                    <small style="color: var(--error, #EF4444); font-size: 12px;">* Saldo cuti habis, otomatis dialihkan ke Potong UM</small>
                </div>
                @endif
            </div>
            @endif

            @if($isTypeIzin && $balance <= 0)
            <div class="edit-form-group" style="margin-bottom: 15px; background: rgba(245, 158, 11, 0.08); padding: 12px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <label class="edit-checkbox-wrapper">
                    <input type="checkbox" name="deduct_um" value="1">
                    <span style="color: #92400e;">Potong UM (Uang Makan)</span>
                </label>
                <small style="display: block; margin-top: 4px; color: #92400e; font-size: 12px; margin-left: 24px;">
                    Karyawan tidak memiliki saldo cuti.
                </small>
            </div>
            @endif

            <div class="edit-form-group">
                <label class="edit-form-label">Catatan HRD (Opsional)</label>
                <textarea name="notes_hrd" rows="2" class="edit-form-textarea" placeholder="Contoh: Potong uang makan."></textarea>
                <small style="font-size:11px; color:var(--text-muted, #6B7280);">Karyawan dapat melihat catatan ini.</small>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="edit-btn-cancel" style="padding:8px 16px;">Batal</button>
                <button type="submit" class="edit-btn-success" style="padding:8px 16px;">Ya, Terima</button>
            </div>
        </form>
    </x-modal>

    @if($canApproveAsSupervisor && $item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
    {{-- MODAL: Supervisor Approve --}}
    <x-modal id="modal-supervisor-approve" title="Setujui Pengajuan Ini?" variant="success" type="form">
        <form action="{{ route('approval.approve', $item) }}" method="POST" style="width:100%;">
            @csrf
            <p style="margin:0 0 12px 0; color:var(--text-secondary, #374151);">
                Anda akan menyetujui pengajuan dari <strong>{{ $item->user->name }}</strong> sebagai atasan langsung.
            </p>
            <div class="edit-form-group">
                <label class="edit-form-label">Catatan Atasan (Opsional)</label>
                <textarea name="notes" rows="2" class="edit-form-textarea" placeholder="Contoh: Disetujui."></textarea>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="edit-btn-cancel" style="padding:8px 16px;">Batal</button>
                <button type="submit" class="edit-btn-success" style="padding:8px 16px;">Ya, Setujui</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL: Supervisor Reject --}}
    <x-modal id="modal-supervisor-reject" title="Tolak Pengajuan Ini?" variant="danger" type="form">
        <form action="{{ route('approval.reject', $item) }}" method="POST" style="width:100%;">
            @csrf
            <p style="margin:0 0 12px 0; color:var(--text-secondary, #374151);">
                Anda akan menolak pengajuan dari <strong>{{ $item->user->name }}</strong>.
            </p>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="edit-btn-cancel" style="padding:8px 16px;">Batal</button>
                <button type="submit" class="edit-btn-danger" style="padding:8px 16px;">Ya, Tolak</button>
            </div>
        </form>
    </x-modal>
    @endif


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Photo viewer
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');

            document.querySelectorAll('.js-view-photo').forEach(el => {
                el.addEventListener('click', () => {
                    const url = el.getAttribute('data-url');
                    if(url && viewer && viewerImg) {
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
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer && viewer.style.display === 'flex') closeViewer(); });

            // Toggle SAKIT deduct options
            window.toggleSakitOptions = function() {
                const checkbox = document.getElementById('deduct_leave_sakit');
                const optionsDiv = document.getElementById('sakit-deduct-options');
                const noticeDiv = document.getElementById('sakit-um-notice');
                if (checkbox && optionsDiv) {
                    optionsDiv.style.display = checkbox.checked ? 'block' : 'none';
                }
                if (noticeDiv) {
                    noticeDiv.style.display = checkbox && checkbox.checked ? 'block' : 'none';
                }
            };

            // Toggle IZIN deduct options
            window.toggleIzinOptions = function() {
                const checkbox = document.getElementById('deduct_leave_izin');
                const optionsDiv = document.getElementById('izin-deduct-options');
                if (checkbox && optionsDiv) {
                    optionsDiv.style.display = checkbox.checked ? 'block' : 'none';
                }
            };

            // Edit modal: type select toggle special leave
            const typeSelect = document.getElementById('edit_type');
            const specialWrapper = document.getElementById('edit_special_wrapper');
            if (typeSelect && specialWrapper) {
                typeSelect.addEventListener('change', function() {
                    specialWrapper.style.display = this.value === 'CUTI_KHUSUS' ? 'block' : 'none';
                });
                if (typeSelect.value === 'CUTI_KHUSUS') {
                    specialWrapper.style.display = 'block';
                }
            }

            // Edit modal: status selector color update
            const statusSelect = document.getElementById('edit_status');
            const statusIndicator = document.getElementById('status_indicator');
            if (statusSelect && statusIndicator) {
                statusSelect.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    statusIndicator.style.background = selected.dataset.bg;
                    statusIndicator.style.color = selected.dataset.color;
                    statusIndicator.textContent = selected.text;
                });
            }

            // Edit modal: file upload preview
            const photoInput = document.getElementById('edit_photo');
            if (photoInput) {
                photoInput.addEventListener('change', function() {
                    const fileName = this.files[0]?.name || 'Pilih file...';
                    const label = this.nextElementSibling;
                    if (label && label.classList.contains('edit-upload-label')) {
                        const span = label.querySelector('span');
                        if (span) span.textContent = fileName;
                    }
                });
            }
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
        }
        .apv-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
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
        .section-icon svg { width: 16px; height: 16px; }
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
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }

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
        .apv-meta-dot { color: var(--border-light, #E5E7EB); }

        .apv-card-header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
            flex-wrap: wrap;
        }
        .apv-submit-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* BALANCE CHIP                               */
        /* ========================================== */
        .hr-balance-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            width: fit-content;
        }
        .hr-balance--ok {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .hr-balance--low {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.25);
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
        .apv-duration-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--gray-50, #F5F7FA);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary, #6B7280);
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
        .apv-date-display svg { color: var(--text-muted, #6B7280); flex-shrink: 0; }
        .apv-date-sep { color: var(--text-muted, #6B7280); font-weight: 400; }
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
        .apv-warning-banner svg { color: #d97706; flex-shrink: 0; margin-top: 2px; }
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
        .apv-pic-box svg { color: var(--text-muted, #6B7280); flex-shrink: 0; }
        .apv-pic-phone { font-size: 0.75rem; color: var(--text-muted, #6B7280); }

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
        .apv-approver-time { font-size: 0.75rem; color: var(--text-muted, #6B7280); }

        /* ========================================== */
        /* HR NOTE BOXES                              */
        /* ========================================== */
        .hr-note-box {
            border-radius: 10px;
            padding: 14px 16px;
            border: 1px solid;
            margin-top: 10px;
        }
        .hr-note-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }
        .hr-note-content {
            font-size: 0.8125rem;
            line-height: 1.6;
        }
        .hr-note--warning {
            background: rgba(245, 158, 11, 0.06);
            border-color: rgba(245, 158, 11, 0.2);
        }
        .hr-note--warning .hr-note-header { color: #92400e; }
        .hr-note--warning .hr-note-content { color: #92400e; }
        .hr-note--error {
            background: rgba(239, 68, 68, 0.06);
            border-color: rgba(239, 68, 68, 0.2);
        }
        .hr-note--error .hr-note-header { color: #b91c1c; }
        .hr-note--error .hr-note-content { color: #b91c1c; }
        .hr-note--info {
            background: rgba(59, 130, 246, 0.06);
            border-color: rgba(59, 130, 246, 0.2);
        }
        .hr-note--info .hr-note-header { color: #1d4ed8; }
        .hr-note--info .hr-note-content { color: #1e3a8a; }
        .hr-note--system {
            background: rgba(245, 158, 11, 0.04);
            border-color: rgba(245, 158, 11, 0.15);
        }
        .hr-note--system .hr-note-header { color: #a16207; }
        .hr-note--system .hr-note-content { color: #92400e; }

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
            top: 0; left: 0;
            width: 100%; height: 100%;
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
        .apv-accuracy { font-weight: 400; color: var(--text-muted, #6B7280); }

        /* ========================================== */
        /* FIXED ACTION BAR                           */
        /* ========================================== */
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            z-index: 50;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        }
        .apv-action-main {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 800px;
            margin: 0 auto;
        }
        .apv-action-secondary {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .apv-action-primary-row {
            display: flex;
            gap: 8px;
        }
        .apv-action-primary-row .apv-action-btn { flex: 1; justify-content: center; }

        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
            min-height: 44px;
        }
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: var(--white, #FFFFFF);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover { background: var(--gray-100, #E5E7EB); }
        .apv-action-btn--danger {
            background: rgba(239, 68, 68, 0.08);
            color: var(--error, #EF4444);
            border: 1.5px solid rgba(239, 68, 68, 0.2);
        }
        .apv-action-btn--danger:hover { background: rgba(239, 68, 68, 0.15); }
        .apv-action-btn--outline-danger {
            background: var(--white, #FFFFFF);
            color: var(--error, #EF4444);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
        }
        .apv-action-btn--outline-danger:hover { background: rgba(239, 68, 68, 0.06); }

        .apv-status-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            width: 100%;
        }
        .apv-status-notice--gray {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-status-notice--info {
            background: rgba(59, 130, 246, 0.06);
            color: #1d4ed8;
            border: 1px solid rgba(59, 130, 246, 0.15);
        }

        /* ========================================== */
        /* BOTTOM SPACER                              */
        /* ========================================== */
        .apv-bottom-spacer { height: 140px; }

        /* ========================================== */
        /* VIEWER OVERLAY                             */
        /* ========================================== */
        .apv-viewer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .apv-viewer-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .apv-viewer-close:hover { background: rgba(255,255,255,0.3); }
        #simple-viewer-img {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
        }

        /* ========================================== */
        /* EDIT MODAL STYLES                          */
        /* ========================================== */
        .edit-modal-content {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 4px;
        }
        .edit-section {
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .edit-section:last-child { margin-bottom: 0; }
        .edit-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .edit-section-header svg { color: var(--text-muted, #6B7280); }
        .edit-section--status {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, var(--white) 100%);
            border-color: rgba(245, 158, 11, 0.25);
        }
        .edit-section--status .edit-section-header {
            color: #92400e;
            border-bottom-color: rgba(245, 158, 11, 0.2);
        }
        .edit-section--status .edit-section-header svg { color: #d97706; }
        .edit-section--warning {
            background: rgba(245, 158, 11, 0.04);
            border-color: rgba(245, 158, 11, 0.2);
        }
        .edit-section--warning .edit-section-header { color: #92400e; border-bottom-color: rgba(245, 158, 11, 0.15); }
        .edit-section--warning .edit-section-header svg { color: #d97706; }
        .edit-section--upload { background: var(--gray-50, #F5F7FA); }
        .edit-section--notes .edit-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .edit-status-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .edit-status-select {
            flex: 1;
            padding: 10px 14px;
            border: 2px solid rgba(245, 158, 11, 0.4);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            background: var(--white);
            cursor: pointer;
            font-family: inherit;
            color: var(--text-primary);
        }
        .edit-status-select:focus {
            outline: none;
            border-color: var(--warning, #F59E0B);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        .edit-status-indicator {
            padding: 8px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .edit-form-group { margin-bottom: 14px; }
        .edit-form-group:last-child { margin-bottom: 0; }
        .edit-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .edit-form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 6px;
        }
        .edit-optional { font-weight: 400; color: var(--text-muted, #9CA3AF); }
        .edit-form-input,
        .edit-form-select,
        .edit-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            transition: all 0.2s ease;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
        }
        .edit-form-input:focus,
        .edit-form-select:focus,
        .edit-form-textarea:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .edit-form-textarea { resize: vertical; min-height: 80px; }

        .edit-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .edit-checkbox-wrapper span {
            font-weight: 600;
            color: var(--text-primary, #1f2937);
            font-size: 14px;
        }
        .edit-radio-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-bottom: 4px;
        }
        .edit-radio-wrapper span {
            font-weight: 500;
            color: var(--text-primary, #1f2937);
            font-size: 14px;
        }

        .edit-upload-area { position: relative; }
        .edit-upload-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .edit-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 24px;
            border: 2px dashed var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: var(--white, #FFFFFF);
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .edit-upload-label:hover {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.04);
        }
        .edit-upload-label svg { color: var(--text-muted, #6B7280); }
        .edit-upload-label span { font-size: 14px; font-weight: 500; color: var(--text-secondary, #374151); }
        .edit-upload-label small { font-size: 12px; color: var(--text-muted, #9CA3AF); }
        .edit-current-file {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 8px 12px;
            background: rgba(34, 197, 94, 0.08);
            border-radius: 6px;
            font-size: 12px;
            color: #15803d;
        }

        .edit-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border-light, #E5E7EB);
        }
        .edit-btn-cancel {
            padding: 10px 20px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            background: var(--white, #FFFFFF);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary, #374151);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .edit-btn-cancel:hover { background: var(--gray-50, #F5F7FA); border-color: var(--text-muted, #9CA3AF); }
        .edit-btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--white, #FFFFFF);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .edit-btn-save:hover { box-shadow: 0 4px 12px rgba(20, 93, 160, 0.3); }
        .edit-btn-success {
            padding: 8px 16px;
            border: none;
            background: var(--success, #22C55E);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--white, #FFFFFF);
            cursor: pointer;
            font-family: inherit;
        }
        .edit-btn-success:hover { background: #16a34a; }
        .edit-btn-danger {
            padding: 8px 16px;
            border: none;
            background: var(--error, #EF4444);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--white, #FFFFFF);
            cursor: pointer;
            font-family: inherit;
        }
        .edit-btn-danger:hover { background: #dc2626; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 768px) {
            .apv-bottom-spacer { height: 100px; }
            .apv-action-main {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
            }
            .apv-action-secondary { justify-content: flex-start; }
            .apv-action-primary-row { flex: 1; justify-content: flex-end; }
            .apv-action-primary-row .apv-action-btn { flex: none; }
        }

        @media (min-width: 1024px) {
            .apv-bottom-spacer { height: 90px; }
        }

        @media (max-width: 640px) {
            .edit-form-row { grid-template-columns: 1fr; }
            .edit-section--notes .edit-form-row { grid-template-columns: 1fr; }
            .edit-status-selector { flex-direction: column; align-items: stretch; }
            .apv-action-primary-row .apv-action-btn { min-height: 48px; }
        }
    </style>

</x-app>
