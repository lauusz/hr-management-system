<x-app title="Detail Pengajuan Izin">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Pengajuan Izin</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan izin dan cuti</p>
            </div>
        </div>
    </x-slot>

    <div class="lrs-page">

        {{-- Back Button --}}
        <a href="{{ route('leave-requests.index') }}" class="back-btn" aria-label="Kembali ke riwayat pengajuan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- ============================================== --}}
        {{-- ALERTS                                       --}}
        {{-- ============================================== --}}
        @if (session('success'))
            <div class="lrs-alert lrs-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="lrs-alert lrs-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;

        // Status logic
        $status = $item->status;
        $badgeClass = 'lrs-status--neutral';
        $statusLabel = $item->status_label ?? $status;
        $statusIcon = '';
        $statusAccentColor = '#6B7280';

        if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $badgeClass = 'lrs-status--success';
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
            $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
            $statusAccentColor = '#22C55E';
        } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $badgeClass = 'lrs-status--error';
            $statusLabel = 'Ditolak';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
            $statusAccentColor = '#EF4444';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $badgeClass = 'lrs-status--warning';
            $statusLabel = 'Menunggu Persetujuan Atasan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
            $statusAccentColor = '#F59E0B';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
            if ($isHRStaff) {
                $badgeClass = 'lrs-status--warning';
                $statusLabel = 'Menunggu Persetujuan';
                $statusAccentColor = '#F59E0B';
            } else {
                $badgeClass = 'lrs-status--info';
                $statusLabel = 'Atasan Mengetahui';
                $statusAccentColor = '#3B82F6';
            }
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        } elseif ($status === 'CANCEL_REQ') {
            $badgeClass = 'lrs-status--error';
            $statusLabel = 'Mengajukan Pembatalan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
            $statusAccentColor = '#EF4444';
        } elseif ($status === 'BATAL') {
            $badgeClass = 'lrs-status--neutral';
            $statusLabel = 'Dibatalkan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>';
            $statusAccentColor = '#6B7280';
        }

        // Type badge color
        $typeBadgeClass = 'lrs-type--blue';
        if (in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
            $typeBadgeClass = 'lrs-type--blue';
        } elseif ($typeValue === \App\Enums\LeaveType::SAKIT->value) {
            $typeBadgeClass = 'lrs-type--yellow';
        } elseif (in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
            $typeBadgeClass = 'lrs-type--orange';
        } elseif ($typeValue === \App\Enums\LeaveType::DINAS_LUAR->value) {
            $typeBadgeClass = 'lrs-type--purple';
        }
    @endphp

        {{-- ============================================== --}}
        {{-- MAIN LAYOUT                                    --}}
        {{-- ============================================== --}}
        <div class="lrs-layout">

        {{-- ========================================== --}}
        {{-- HERO: Employee + Status                    --}}
        {{-- ========================================== --}}
        <div class="lrs-section lrs-hero">
            <div class="lrs-employee">
                <div class="lrs-avatar">{{ substr($item->user->name, 0, 1) }}</div>
                <div class="lrs-employee-info">
                    <h2 class="lrs-employee-name">{{ $item->user->name }}</h2>
                    <div class="lrs-employee-meta">
                        <span class="lrs-role-badge">{{ $item->user->role }}</span>
                        <span class="lrs-meta-dot">·</span>
                        <span class="lrs-meta-text">{{ $item->user->division->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="lrs-status-banner {{ $badgeClass }}">
                <div class="lrs-status-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $statusIcon !!}
                    </svg>
                </div>
                <div class="lrs-status-body">
                    <span class="lrs-status-label">{{ $statusLabel }}</span>
                    @if($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                        <span class="lrs-status-sub">Menunggu: {{ $item->user->directSupervisor->name ?? $item->user->manager->name ?? '-' }}</span>
                    @elseif($status === \App\Models\LeaveRequest::PENDING_HR)
                        <span class="lrs-status-sub">Menunggu: HRD</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- LEFT COLUMN                                  --}}
        {{-- ========================================== --}}
        <div class="lrs-main-col">

        {{-- ========================================== --}}
        {{-- SUMMARY: Key Request Facts                   --}}
        {{-- ========================================== --}}
        <div class="lrs-section lrs-summary">
            <div class="lrs-card-header">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="lrs-card-title">Ringkasan Pengajuan</h3>
            </div>

            <div class="lrs-info-grid">
                <div class="lrs-info-item">
                    <span class="lrs-info-label">Jenis</span>
                    <span class="lrs-info-value">
                        <span class="lrs-type-badge {{ $typeBadgeClass }}">{{ $item->type_label ?? $item->type }}</span>
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
                    <div class="lrs-info-item lrs-info-item--full">
                        <span class="lrs-info-label">Kategori</span>
                        <span class="lrs-info-value">{{ $catLabel }}</span>
                    </div>
                @endif

                <div class="lrs-info-item">
                    <span class="lrs-info-label">Tanggal Mulai</span>
                    <span class="lrs-info-value">{{ $item->start_date->translatedFormat('j F Y') }}</span>
                </div>

                @if($item->end_date && $item->end_date->ne($item->start_date))
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">Tanggal Selesai</span>
                        <span class="lrs-info-value">{{ $item->end_date->translatedFormat('j F Y') }}</span>
                    </div>
                @endif

                @php
                    $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                    $endTimeLabel = $item->end_time ? $item->end_time->format('H:i') : null;
                @endphp

                @if($startTimeLabel)
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">
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
                        <span class="lrs-info-value">
                            {{ $startTimeLabel }}
                            @if($endTimeLabel)
                                – {{ $endTimeLabel }}
                            @endif
                        </span>
                    </div>
                @endif

                <div class="lrs-info-item">
                    <span class="lrs-info-label">Diajukan</span>
                    <span class="lrs-info-value">{{ $item->created_at->translatedFormat('j F Y') }} · {{ $item->created_at->format('H:i') }}</span>
                </div>

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
                    <div class="lrs-info-item lrs-info-item--full">
                        <span class="lrs-info-label">Perhatian</span>
                        <span class="lrs-info-value lrs-text-error lrs-font-semibold">Pengajuan H-{{ $shortNoticeDaysDiff }} (kurang dari H-7)</span>
                    </div>
                @endif

                @if($item->substitute_pic)
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">PIC Pengganti</span>
                        <span class="lrs-info-value">{{ $item->substitute_pic }}</span>
                    </div>
                @endif

                @if($item->substitute_phone)
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">No. Telepon PIC</span>
                        <span class="lrs-info-value">{{ $item->substitute_phone }}</span>
                    </div>
                @endif

                @if($item->approved_by)
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">Diputus Oleh</span>
                        <span class="lrs-info-value">
                            {{ $item->approver?->name }}
                            @if($item->approved_at)
                                <span class="lrs-info-sub">{{ $item->approved_at->translatedFormat('j F Y H:i') }}</span>
                            @endif
                        </span>
                    </div>
                @endif

                @php
                    $atasanName = $item->user->directSupervisor->name ?? $item->user->manager->name ?? null;
                @endphp
                @if($atasanName)
                    <div class="lrs-info-item">
                        <span class="lrs-info-label">Atasan Mengetahui</span>
                        <span class="lrs-info-value">
                            {{ $atasanName }}
                            @if($item->supervisor_ack_at)
                                <span class="lrs-info-sub">{{ $item->supervisor_ack_at->translatedFormat('j F Y H:i') }}</span>
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- REASON                                       --}}
        {{-- ========================================== --}}
        <div class="lrs-section lrs-reason">
            <div class="lrs-card-header">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="lrs-card-title">Alasan Pengajuan</h3>
            </div>
            <div class="lrs-reason-body">
                {{ $item->reason }}
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- NOTES & WARNINGS                             --}}
        {{-- ========================================== --}}
        @if($item->deduct_um || $item->notes || ($item->notes_hrd && !$item->deduct_um))
            <div class="lrs-section lrs-notes">
                <div class="lrs-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <h3 class="lrs-card-title">Catatan & Keputusan</h3>
                </div>

                @if($item->deduct_um)
                    <div class="lrs-note lrs-note--warning">
                        <div class="lrs-note-header">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="lrs-note-title">Potong Uang Makan (UM)</span>
                        </div>
                        <p class="lrs-note-text">{!! nl2br(e($item->notes_hrd ?? 'Potong UM')) !!}</p>
                    </div>
                @endif

                @if($item->notes)
                    <div class="lrs-note lrs-note--system">
                        <div class="lrs-note-header">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="lrs-note-title">Catatan Sistem</span>
                        </div>
                        <p class="lrs-note-text">{!! nl2br(e($item->notes)) !!}</p>
                    </div>
                @endif

                @if($item->notes_hrd && !$item->deduct_um)
                    @php
                        if ($item->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                            $noteVariant = 'rejection';
                            $noteLabelText = 'Alasan Penolakan (HRD)';
                        } else {
                            $noteVariant = 'info';
                            $noteLabelText = 'Catatan HRD';
                        }
                    @endphp
                    <div class="lrs-note lrs-note--{{ $noteVariant }}">
                        <div class="lrs-note-header">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($noteVariant === 'rejection')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @endif
                            </svg>
                            <span class="lrs-note-title">{{ $noteLabelText }}</span>
                        </div>
                        <p class="lrs-note-text">{!! nl2br(e($item->notes_hrd)) !!}</p>
                    </div>
                @endif
            </div>
        @endif

        </div>

        {{-- ========================================== --}}
        {{-- RIGHT COLUMN                                 --}}
        {{-- ========================================== --}}
        <div class="lrs-side-col">

        {{-- ========================================== --}}
        {{-- ATTACHMENT & UPLOAD                          --}}
        {{-- ========================================== --}}
        @php
            $url = $item->photo
                ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                : null;
            $authUser = auth()->user();
            $authRole = $authUser->role instanceof \App\Enums\UserRole ? $authUser->role->value : $authUser->role;
            $isHrdUploader = in_array(strtoupper((string) $authRole), ['HRD', 'HR STAFF', 'MANAGER'], true);
            $isOwnerUploader = $authUser->id === $item->user_id;
            $isPendingStatus = in_array($item->status, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR], true);
            $canUploadFollowupPhoto = $isHrdUploader || $isOwnerUploader;
        @endphp

        <div class="lrs-section lrs-attachment">
            <div class="lrs-card-header">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="lrs-card-title">Lampiran</h3>
            </div>

            @if($url)
                <div class="lrs-photo-card js-view-photo" data-url="{{ $url }}">
                    <img src="{{ $url }}" alt="Bukti Izin" loading="lazy">
                    <div class="lrs-photo-overlay">
                        <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Lihat Full Screen</span>
                    </div>
                </div>
            @else
                <div class="lrs-empty-photo">
                    <div class="lrs-empty-photo-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="lrs-empty-photo-text">Tidak ada lampiran foto</span>
                </div>
            @endif

            @if($canUploadFollowupPhoto)
                <div class="lrs-upload-area">
                    <p class="lrs-upload-hint">Foto bisa diunggah jika belum tersedia saat pengajuan dibuat.</p>
                    <form method="POST" action="{{ route('leave-requests.upload-photo', $item) }}" enctype="multipart/form-data" class="lrs-upload-form">
                        @csrf
                        <div class="lrs-file-input-wrap">
                            <input type="file" name="photo" id="followupPhotoInput" class="lrs-file-input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx" required>
                            <label for="followupPhotoInput" class="lrs-file-label">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Pilih File
                            </label>
                        </div>
                        <button type="submit" id="followupUploadBtn" class="lrs-btn-upload is-hidden">Upload</button>
                    </form>
                    <div id="followupPhotoPreviewContainer" class="lrs-preview-box" style="display:none;">
                        <p class="lrs-preview-label">Preview:</p>
                        <img id="followupPhotoPreview" src="#" alt="Preview">
                    </div>
                    <p class="lrs-format-hint">Format: JPG, PNG, HEIC, PDF, DOCX, XLSX. Maksimal 8 MB.</p>
                </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- LOCATION                                     --}}
        {{-- ========================================== --}}
        @if($item->latitude && $item->longitude)
            <div class="lrs-section lrs-location">
                <div class="lrs-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="lrs-card-title">Lokasi Pengajuan</h3>
                </div>
                <div class="lrs-map-wrap">
                    <iframe src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed" loading="lazy" allowfullscreen></iframe>
                </div>
                <div class="lrs-map-meta">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="lrs-map-link">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                    <span class="lrs-accuracy-badge">±{{ (int)$item->accuracy_m }}m</span>
                </div>
            </div>
        @endif

        </div>

        {{-- ========================================== --}}
        {{-- CANCEL ACTION                                --}}
        {{-- ========================================== --}}
        @if(in_array($item->status, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR]))
            @can('delete', $item)
                <div class="lrs-section lrs-cancel">
                    <div class="lrs-cancel-inner">
                        <button type="button" data-modal-target="modal-delete" class="lrs-btn-cancel">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batalkan Pengajuan
                        </button>
                    </div>
                </div>
            @endcan
        @endif
    </div>

    </div>

    {{-- ============================================== --}}
    {{-- FULL SCREEN PHOTO VIEWER                       --}}
    {{-- ============================================== --}}
    <div id="simple-viewer" class="lrs-viewer" style="display: none;">
        <button type="button" id="btn-close-simple" class="lrs-viewer-close">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    {{-- ============================================== --}}
    {{-- CANCEL MODAL                                   --}}
    {{-- ============================================== --}}
    @can('delete', $item)
        <x-modal
            id="modal-delete"
            title="Yakin Ingin Membatalkan?"
            type="confirm"
            variant="danger"
            confirmLabel="Ya, Batalkan"
            cancelLabel="Batal"
            :confirmFormAction="route('leave-requests.destroy', $item)"
            confirmFormMethod="DELETE">
            <p style="margin:0; color:#374151;">
                Pengajuan ini akan dibatalkan dan tidak akan diproses lebih lanjut.
            </p>
            <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
                Status akan berubah menjadi <strong>BATAL</strong>. Data tetap tersimpan sebagai riwayat.
            </p>
        </x-modal>
    @endcan

    {{-- ============================================== --}}
    {{-- SCRIPTS                                        --}}
    {{-- ============================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');
            const followupPhotoInput = document.getElementById('followupPhotoInput');
            const followupUploadBtn = document.getElementById('followupUploadBtn');
            const followupPreviewContainer = document.getElementById('followupPhotoPreviewContainer');
            const followupPreviewImg = document.getElementById('followupPhotoPreview');

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
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer && viewer.style.display === 'flex') closeViewer(); });

            if (followupPhotoInput && followupUploadBtn) {
                const toggleUploadButton = () => {
                    const hasFile = followupPhotoInput.files && followupPhotoInput.files.length > 0;
                    followupUploadBtn.classList.toggle('is-hidden', !hasFile);
                    const file = hasFile ? followupPhotoInput.files[0] : null;
                    if (file && file.type && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            if (followupPreviewImg) followupPreviewImg.src = e.target.result;
                            if (followupPreviewContainer) followupPreviewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        if (followupPreviewContainer) followupPreviewContainer.style.display = 'none';
                        if (followupPreviewImg) followupPreviewImg.src = '';
                    }
                };
                followupPhotoInput.addEventListener('change', toggleUploadButton);
                toggleUploadButton();
            }
        });
    </script>

    {{-- ============================================== --}}
    {{-- STYLES                                         --}}
    {{-- ============================================== --}}
    <style>
        /* ========================================== */
        /* CSS VARIABLES                              */
        /* ========================================== */
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --accent: #D4AF37;
            --white: #FFFFFF;
            --gray-50: #F5F7FA;
            --gray-100: #F8FAFC;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #374151;
            --gray-700: #1F2937;
            --gray-900: #111827;
            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;
            --teal: #14B8A6;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lrs-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .lrs-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .lrs-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* PAGE WRAPPER                               */
        /* ========================================== */
        .lrs-page {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        @media (min-width: 1024px) {
            .lrs-page {
                gap: 12px;
            }
        }

        /* ========================================== */
        /* HEADER SLOT (x-slot name="header")         */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
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

        /* ========================================== */
        /* BACK BUTTON                                */
        /* ========================================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
        }
        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
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
        @media (min-width: 480px) {
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text {
                font-size: 0.8125rem;
            }
        }

        /* ========================================== */
        /* LAYOUT                                     */
        /* ========================================== */
        .lrs-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .lrs-main-col,
        .lrs-side-col {
            display: contents;
        }
        @media (min-width: 1024px) {
            .lrs-layout {
                grid-template-columns: 1fr 380px;
                gap: 20px;
                align-items: start;
            }
            .lrs-hero { grid-column: 1 / -1; }
            .lrs-main-col {
                grid-column: 1;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .lrs-side-col {
                grid-column: 2;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .lrs-cancel { grid-column: 1 / -1; }
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .lrs-section {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        @media (min-width: 768px) {
            .lrs-section { padding: 24px; }
        }

        .lrs-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--gray-200);
            color: var(--primary-dark);
        }
        .lrs-card-title {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--gray-900);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ========================================== */
        /* HERO: EMPLOYEE + STATUS                    */
        /* ========================================== */
        .lrs-employee {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }
        .lrs-avatar {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            flex-shrink: 0;
        }
        @media (min-width: 768px) {
            .lrs-avatar {
                width: 60px;
                height: 60px;
                font-size: 24px;
                border-radius: 16px;
            }
        }
        .lrs-employee-info { flex: 1; min-width: 0; }
        .lrs-employee-name {
            margin: 0 0 4px 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        @media (min-width: 768px) {
            .lrs-employee-name { font-size: 1.25rem; }
        }
        .lrs-employee-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .lrs-role-badge {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lrs-meta-dot { color: var(--gray-400); font-size: 12px; }
        .lrs-meta-text { color: var(--gray-500); font-size: 13px; font-weight: 500; }

        /* Status Banner */
        .lrs-status-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid transparent;
        }
        .lrs-status-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--white);
        }
        .lrs-status-body {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .lrs-status-label {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }
        .lrs-status-sub {
            font-size: 12px;
            opacity: 0.85;
            font-weight: 500;
        }

        .lrs-status--success { background: rgba(34, 197, 94, 0.08); border-color: rgba(34, 197, 94, 0.2); }
        .lrs-status--success .lrs-status-icon { color: #15803d; background: rgba(34, 197, 94, 0.12); }
        .lrs-status--success .lrs-status-label { color: #15803d; }
        .lrs-status--success .lrs-status-sub { color: #166534; }

        .lrs-status--error { background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.2); }
        .lrs-status--error .lrs-status-icon { color: #b91c1c; background: rgba(239, 68, 68, 0.12); }
        .lrs-status--error .lrs-status-label { color: #b91c1c; }
        .lrs-status--error .lrs-status-sub { color: #991b1b; }

        .lrs-status--warning { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.2); }
        .lrs-status--warning .lrs-status-icon { color: #a16207; background: rgba(245, 158, 11, 0.12); }
        .lrs-status--warning .lrs-status-label { color: #a16207; }
        .lrs-status--warning .lrs-status-sub { color: #92400e; }

        .lrs-status--info { background: rgba(59, 130, 246, 0.08); border-color: rgba(59, 130, 246, 0.2); }
        .lrs-status--info .lrs-status-icon { color: #1d4ed8; background: rgba(59, 130, 246, 0.12); }
        .lrs-status--info .lrs-status-label { color: #1d4ed8; }
        .lrs-status--info .lrs-status-sub { color: #1e40af; }

        .lrs-status--neutral { background: var(--gray-100); border-color: var(--gray-200); }
        .lrs-status--neutral .lrs-status-icon { color: var(--gray-600); background: var(--gray-200); }
        .lrs-status--neutral .lrs-status-label { color: var(--gray-700); }
        .lrs-status--neutral .lrs-status-sub { color: var(--gray-500); }

        /* ========================================== */
        /* SUMMARY INFO GRID                          */
        /* ========================================== */
        .lrs-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        @media (min-width: 640px) {
            .lrs-info-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            .lrs-info-item--full { grid-column: 1 / -1; }
        }
        .lrs-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .lrs-info-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lrs-info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.4;
        }
        .lrs-info-sub {
            display: block;
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 500;
            margin-top: 2px;
        }
        .lrs-text-error { color: var(--error); }
        .lrs-font-semibold { font-weight: 700; }

        /* Type Badge */
        .lrs-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        .lrs-type--blue { background: rgba(59, 130, 246, 0.08); color: var(--primary); }
        .lrs-type--yellow { background: rgba(245, 158, 11, 0.08); color: #b45309; }
        .lrs-type--orange { background: rgba(234, 88, 12, 0.06); color: #c2410c; }
        .lrs-type--purple { background: rgba(147, 51, 234, 0.06); color: #7e22ce; }

        /* ========================================== */
        /* REASON                                     */
        /* ========================================== */
        .lrs-reason-body {
            background: var(--gray-50);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            font-size: 14px;
            line-height: 1.7;
            color: var(--gray-600);
        }
        @media (min-width: 768px) {
            .lrs-reason-body { padding: 20px; }
        }

        /* ========================================== */
        /* NOTES                                      */
        /* ========================================== */
        .lrs-note {
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid transparent;
            margin-bottom: 12px;
        }
        .lrs-note:last-child { margin-bottom: 0; }
        .lrs-note-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .lrs-note-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lrs-note-text {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
        }

        .lrs-note--warning {
            background: rgba(245, 158, 11, 0.06);
            border-color: rgba(245, 158, 11, 0.2);
            color: #92400e;
        }
        .lrs-note--warning .lrs-note-title { color: #a16207; }
        .lrs-note--warning .lrs-note-text { color: #92400e; }

        .lrs-note--system {
            background: rgba(245, 158, 11, 0.05);
            border-color: rgba(245, 158, 11, 0.15);
            color: #b45309;
        }
        .lrs-note--system .lrs-note-title { color: #a16207; }
        .lrs-note--system .lrs-note-text { color: #b45309; }

        .lrs-note--info {
            background: rgba(59, 130, 246, 0.06);
            border-color: rgba(59, 130, 246, 0.15);
            color: #1e3a8a;
        }
        .lrs-note--info .lrs-note-title { color: #1d4ed8; }
        .lrs-note--info .lrs-note-text { color: #1e40af; }

        .lrs-note--rejection {
            background: rgba(239, 68, 68, 0.06);
            border-color: rgba(239, 68, 68, 0.15);
            color: #991b1b;
        }
        .lrs-note--rejection .lrs-note-title { color: #b91c1c; }
        .lrs-note--rejection .lrs-note-text { color: #991b1b; }

        /* ========================================== */
        /* ATTACHMENT                                 */
        /* ========================================== */
        .lrs-photo-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--gray-200);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .lrs-photo-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .lrs-photo-card img {
            width: 100%;
            height: auto;
            display: block;
            min-height: 160px;
            object-fit: cover;
            background: var(--gray-100);
        }
        .lrs-photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .lrs-photo-card:hover .lrs-photo-overlay { opacity: 1; }
        .lrs-photo-overlay span {
            color: var(--white);
            font-size: 12px;
            font-weight: 600;
            background: rgba(0,0,0,0.6);
            padding: 6px 14px;
            border-radius: 20px;
        }

        .lrs-empty-photo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 32px 20px;
            background: var(--gray-50);
            border-radius: 12px;
            border: 1px dashed var(--gray-200);
            color: var(--gray-400);
            text-align: center;
        }
        .lrs-empty-photo-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lrs-empty-photo-text { font-size: 13px; font-weight: 500; }

        /* Upload */
        .lrs-upload-area { margin-top: 16px; }
        .lrs-upload-hint {
            font-size: 12px;
            color: var(--primary);
            background: rgba(20, 93, 160, 0.05);
            border: 1px solid rgba(20, 93, 160, 0.1);
            border-radius: 8px;
            padding: 10px 12px;
            margin: 0 0 12px 0;
            font-weight: 500;
            line-height: 1.4;
        }
        .lrs-upload-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .lrs-file-input-wrap { position: relative; }
        .lrs-file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .lrs-file-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--white);
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .lrs-file-label:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .lrs-btn-upload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.2);
        }
        .lrs-btn-upload:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .lrs-btn-upload.is-hidden { display: none; }

        .lrs-preview-box {
            margin-top: 12px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 10px;
            border: 1px solid var(--gray-200);
        }
        .lrs-preview-label {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-500);
        }
        .lrs-preview-box img {
            width: 100%;
            max-width: 260px;
            border-radius: 8px;
            display: block;
        }
        .lrs-format-hint {
            margin: 8px 0 0 0;
            font-size: 11px;
            color: var(--gray-400);
        }

        /* ========================================== */
        /* MAP                                        */
        /* ========================================== */
        .lrs-map-wrap {
            position: relative;
            padding-bottom: 50%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            margin-bottom: 12px;
            background: var(--gray-100);
        }
        .lrs-map-wrap iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .lrs-map-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .lrs-map-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }
        .lrs-map-link:hover { color: var(--primary-dark); text-decoration: underline; }
        .lrs-accuracy-badge {
            font-size: 11px;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 3px 10px;
            border-radius: 9999px;
            font-weight: 600;
        }

        /* ========================================== */
        /* CANCEL ACTION                              */
        /* ========================================== */
        .lrs-cancel { padding: 16px; }
        @media (min-width: 768px) {
            .lrs-cancel { padding: 20px 24px; }
        }
        .lrs-cancel-inner {
            display: flex;
            justify-content: center;
        }
        .lrs-btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--white);
            border: 1.5px solid #fecaca;
            color: #dc2626;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            width: 100%;
        }
        @media (min-width: 480px) {
            .lrs-btn-cancel { width: auto; }
        }
        .lrs-btn-cancel:hover {
            background: #fef2f2;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* FULL SCREEN VIEWER                         */
        /* ========================================== */
        .lrs-viewer {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .lrs-viewer-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: var(--white);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 1;
        }
        .lrs-viewer-close:hover { background: rgba(255,255,255,0.25); }
        #simple-viewer-img {
            max-width: 95vw;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }
    </style>

</x-app>
