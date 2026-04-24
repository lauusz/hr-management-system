<x-app title="Detail Pengajuan Izin">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert-error">
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
        $badgeClass = 'badge-gray';
        $statusLabel = $item->status_label ?? $status;
        $statusIcon = '';

        if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $badgeClass = 'badge-green';
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
            $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $badgeClass = 'badge-red';
            $statusLabel = 'Ditolak';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $badgeClass = 'badge-yellow';
            $statusLabel = 'Menunggu Persetujuan Atasan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
            if ($isHRStaff) {
                $badgeClass = 'badge-yellow';
                $statusLabel = 'Menunggu Persetujuan';
            } else {
                $badgeClass = 'badge-teal';
                $statusLabel = 'Atasan Mengetahui';
            }
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        } elseif ($status === 'CANCEL_REQ') {
            $badgeClass = 'badge-red';
            $statusLabel = 'Mengajukan Pembatalan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
        } elseif ($status === 'BATAL') {
            $badgeClass = 'badge-gray';
            $statusLabel = 'Dibatalkan';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>';
        }

        // Type badge color
        $typeBadgeClass = 'type-blue';
        if (in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
            $typeBadgeClass = 'type-blue';
        } elseif ($typeValue === \App\Enums\LeaveType::SAKIT->value) {
            $typeBadgeClass = 'type-yellow';
        } elseif (in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
            $typeBadgeClass = 'type-orange';
        } elseif ($typeValue === \App\Enums\LeaveType::DINAS_LUAR->value) {
            $typeBadgeClass = 'type-purple';
        }
    @endphp

    {{-- HEADER SECTION --}}
    <div class="detail-header">
        <a href="{{ route('leave-requests.index') }}" class="back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="detail-layout">

        {{-- LEFT COLUMN: Info Utama --}}
        <div class="detail-main">

            {{-- Employee Card --}}
            <div class="card-section employee-card">
                <div class="employee-header">
                    <div class="avatar-circle">
                        {{ substr($item->user->name, 0, 1) }}
                    </div>
                    <div class="employee-info">
                        <h1 class="employee-name">{{ $item->user->name }}</h1>
                        <div class="employee-meta">
                            <span class="role-tag">{{ $item->user->role }}</span>
                            <span class="meta-dot">•</span>
                            <span class="division-name">{{ $item->user->division->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Status Banner --}}
                <div class="status-banner {{ $badgeClass }}">
                    <div class="status-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $statusIcon !!}
                        </svg>
                    </div>
                    <div class="status-text">
                        <span class="status-label">{{ $statusLabel }}</span>
                        @if($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                            <span class="status-sub">Menunggu: {{ $item->user->directSupervisor->name ?? $item->user->manager->name ?? '-' }}</span>
                        @elseif($status === \App\Models\LeaveRequest::PENDING_HR)
                            <span class="status-sub">Menunggu: HRD</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Leave Type Card --}}
            <div class="card-section">
                <h3 class="section-heading">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Detail Pengajuan
                </h3>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Jenis</span>
                        <span class="info-value">
                            <span class="type-badge {{ $typeBadgeClass }}">{{ $item->type_label ?? $item->type }}</span>
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
                        <div class="info-item full-width">
                            <span class="info-label">Kategori</span>
                            <span class="info-value">{{ $catLabel }}</span>
                        </div>
                    @endif

                    <div class="info-item">
                        <span class="info-label">Tanggal Mulai</span>
                        <span class="info-value">{{ $item->start_date->translatedFormat('j F Y') }}</span>
                    </div>

                    @if($item->end_date && $item->end_date->ne($item->start_date))
                    <div class="info-item">
                        <span class="info-label">Tanggal Selesai</span>
                        <span class="info-value">{{ $item->end_date->translatedFormat('j F Y') }}</span>
                    </div>
                    @endif

                    @php
                        $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                        $endTimeLabel = $item->end_time ? $item->end_time->format('H:i') : null;
                    @endphp

                    @if($startTimeLabel)
                    <div class="info-item">
                        <span class="info-label">
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
                        <span class="info-value">
                            {{ $startTimeLabel }}
                            @if($endTimeLabel)
                                – {{ $endTimeLabel }}
                            @endif
                        </span>
                    </div>
                    @endif

                    <div class="info-item">
                        <span class="info-label">Diajukan</span>
                        <span class="info-value">{{ $item->created_at->translatedFormat('j F Y') }} pukul {{ $item->created_at->format('H:i') }}</span>
                    </div>

                    @php
                        // H-7 Warning Calculation (for CUTI type only)
                        // Based on days from submission date (created_at) to start_date
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
                    <div class="info-item">
                        <span class="info-label">Perhatian</span>
                        <span class="info-value" style="color: #dc2626; font-weight: 600;">
                            Pengajuan H-{{ $shortNoticeDaysDiff }} (kurang dari H-7)
                        </span>
                    </div>
                    @endif

                    @if($item->substitute_pic)
                    <div class="info-item">
                        <span class="info-label">PIC Pengganti</span>
                        <span class="info-value">{{ $item->substitute_pic }}</span>
                    </div>
                    @endif

                    @if($item->substitute_phone)
                    <div class="info-item">
                        <span class="info-label">No. Telepon PIC</span>
                        <span class="info-value">{{ $item->substitute_phone }}</span>
                    </div>
                    @endif

                    @if($item->approved_by)
                    <div class="info-item">
                        <span class="info-label">Diputus Oleh</span>
                        <span class="info-value">
                            {{ $item->approver?->name }}
                            @if($item->approved_at)
                                <span class="info-sub">{{ $item->approved_at->translatedFormat('j F Y H:i') }}</span>
                            @endif
                        </span>
                    </div>
                    @endif

                    @php
                        $atasanName = $item->user->directSupervisor->name ?? $item->user->manager->name ?? null;
                    @endphp
                    @if($atasanName)
                    <div class="info-item">
                        <span class="info-label">Atasan Mengetahui</span>
                        <span class="info-value">
                            {{ $atasanName }}
                            @if($item->supervisor_ack_at)
                                <span class="info-sub">{{ $item->supervisor_ack_at->translatedFormat('j F Y H:i') }}</span>
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- POTONG UM INDICATOR --}}
            @if($item->deduct_um)
            <div class="note-box" style="background: #fef3c7; border-color: #f59e0b; margin-bottom: 16px;">
                <span class="note-label" style="color: #92400e; font-size: 12px; font-weight: 600;">⚠️ Potong Uang Makan (UM)</span>
                <p class="note-text" style="color: #92400e; margin-top: 4px;">
                    {!! nl2br(e($item->notes_hrd ?? 'Potong UM')) !!}
                </p>
            </div>
            @endif

            {{-- Notes Section (skip if notes_hrd only and deduct_um - already shown above) --}}
            @if($item->notes || ($item->notes_hrd && !$item->deduct_um))
            <div class="card-section">
                <h3 class="section-heading">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    Catatan
                </h3>

                @if($item->notes)
                <div class="note-box note-system">
                    <span class="note-label">Catatan Sistem</span>
                    <p class="note-text">{{ $item->notes }}</p>
                </div>
                @endif

                @if($item->notes_hrd && !$item->deduct_um)
                    @php
                        if ($item->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                            $noteBg = '#fef2f2';
                            $noteBorder = '#fecaca';
                            $noteLabelColor = '#991b1b';
                            $noteTextColor = '#7f1d1d';
                            $noteLabelText = 'Alasan Penolakan (HRD)';
                        } else {
                            $noteBg = '#eff6ff';
                            $noteBorder = '#dbeafe';
                            $noteLabelColor = '#1e40af';
                            $noteTextColor = '#1e3a8a';
                            $noteLabelText = 'Catatan HRD';
                        }
                    @endphp
                    <div class="note-box" style="background: {{ $noteBg }}; border-color: {{ $noteBorder }};">
                        <span class="note-label" style="color: {{ $noteLabelColor }};">{{ $noteLabelText }}</span>
                        <p class="note-text" style="color: {{ $noteTextColor }};">{{ $item->notes_hrd }}</p>
                    </div>
                @endif
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: Reason & Attachments --}}
        <div class="detail-side">

            {{-- Reason Card --}}
            <div class="card-section">
                <h3 class="section-heading">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Alasan Pengajuan
                </h3>
                <div class="reason-box">
                    {{ $item->reason }}
                </div>
            </div>

            {{-- Photo & Attachment Card --}}
            <div class="card-section">
                <h3 class="section-heading">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Lampiran
                </h3>

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

                @if($url)
                    <div class="photo-card js-view-photo" data-url="{{ $url }}">
                        <img src="{{ $url }}" alt="Bukti Izin">
                        <div class="photo-overlay">
                            <svg width="28" height="28" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Lihat Full Screen</span>
                        </div>
                    </div>
                @else
                    <div class="no-photo">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Tidak ada lampiran foto</span>
                    </div>
                @endif

                @if($canUploadFollowupPhoto)
                    <div class="upload-section">
                        <p class="upload-hint">Foto bisa diunggah jika belum tersedia saat pengajuan dibuat.</p>
                        <form method="POST" action="{{ route('leave-requests.upload-photo', $item) }}" enctype="multipart/form-data" class="upload-form">
                            @csrf
                            <div class="file-input-wrapper">
                                <input type="file" name="photo" id="followupPhotoInput" class="file-input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx" required>
                                <label for="followupPhotoInput" class="file-label">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Pilih File
                                </label>
                            </div>
                            <button type="submit" id="followupUploadBtn" class="btn-upload is-hidden">
                                Upload
                            </button>
                        </form>
                        <div id="followupPhotoPreviewContainer" class="preview-container" style="display:none;">
                            <p class="preview-label">Preview:</p>
                            <img id="followupPhotoPreview" src="#" alt="Preview">
                        </div>
                        <p class="format-hint">Format: JPG, PNG, HEIC, PDF, DOCX, XLSX. Maksimal 8 MB.</p>
                    </div>
                @endif
            </div>

            {{-- Location Card --}}
            @if($item->latitude && $item->longitude)
            <div class="card-section">
                <h3 class="section-heading">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Lokasi Pengajuan
                </h3>
                <div class="map-wrapper">
                    <iframe src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed" loading="lazy" allowfullscreen></iframe>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="map-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka di Google Maps
                </a>
                <span class="accuracy-badge">±{{ (int)$item->accuracy_m }}m</span>
            </div>
            @endif
        </div>
    </div>

    @if(in_array($item->status, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR]))
        @can('delete', $item)
    <div class="action-bar">
        <button type="button" data-modal-target="modal-delete" class="btn-action btn-cancel">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Batalkan Pengajuan
        </button>
    </div>
        @endcan
    @endif

    {{-- FULL SCREEN VIEWER --}}
    <div id="simple-viewer" class="viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="viewer-close">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

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
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer.style.display === 'flex') closeViewer(); });

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

    <style>
        /* === BASE === */
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 10px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }

        /* === HEADER === */
        .detail-header {
            padding: 16px 24px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--navy); }
        .back-link svg { transition: transform 0.2s; }
        .back-link:hover svg { transform: translateX(-4px); }

        /* === MAIN LAYOUT === */
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
            padding: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card-section {
            background: var(--white);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .section-heading svg { color: var(--navy); }

        /* === EMPLOYEE CARD === */
        .employee-card {
            background: var(--white);
            border: 1px solid var(--border);
            padding: 28px;
        }

        .employee-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .avatar-circle {
            width: 64px;
            height: 64px;
            background: var(--navy);
            color: var(--white);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .employee-info { flex: 1; min-width: 0; }

        .employee-name {
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .role-tag {
            background: #eff6ff;
            color: var(--navy);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #dbeafe;
        }

        .meta-dot { color: var(--text-muted); }

        .division-name {
            color: var(--text-secondary);
            font-size: 13px;
        }

        /* Status Banner */
        .status-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-radius: 12px;
            margin-top: 16px;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .status-banner.badge-green { background: #dcfce7; border: 1px solid #bbf7d0; }
        .status-banner.badge-green .status-icon { background: #166534; color: #fff; }
        .status-banner.badge-green .status-text { color: #166534; }

        .status-banner.badge-red { background: #fee2e2; border: 1px solid #fecaca; }
        .status-banner.badge-red .status-icon { background: #991b1b; color: #fff; }
        .status-banner.badge-red .status-text { color: #991b1b; }

        .status-banner.badge-yellow { background: #fefce8; border: 1px solid #fef08a; }
        .status-banner.badge-yellow .status-icon { background: #a16207; color: #fff; }
        .status-banner.badge-yellow .status-text { color: #a16207; }

        .status-banner.badge-teal { background: #ccfbf1; border: 1px solid #99f6e4; }
        .status-banner.badge-teal .status-icon { background: #0f766e; color: #fff; }
        .status-banner.badge-teal .status-text { color: #0f766e; }

        .status-banner.badge-gray { background: #f3f4f6; border: 1px solid #e5e7eb; }
        .status-banner.badge-gray .status-icon { background: #374151; color: #fff; }
        .status-banner.badge-gray .status-text { color: #374151; }

        .status-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .status-label {
            font-size: 15px;
            font-weight: 700;
        }

        .status-sub {
            font-size: 12px;
            opacity: 0.8;
        }

        /* === INFO GRID === */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-primary);
            line-height: 1.4;
        }

        .info-sub {
            display: block;
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        /* Type Badge */
        .type-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .type-blue { background: #eff6ff; color: #1e4a8d; }
        .type-yellow { background: #fefce8; color: #a16207; }
        .type-orange { background: #fff7ed; color: #c2410c; }
        .type-purple { background: #f3e8ff; color: #7e22ce; }

        /* Status Badge (for detail page type display) */
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; }
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* === NOTES === */
        .note-box {
            padding: 16px;
            border-radius: 10px;
            border: 1px solid;
            margin-bottom: 12px;
        }

        .note-system {
            background: #fffbeb;
            border-color: #fef3c7;
        }

        .note-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            color: #92400e;
        }

        .note-text {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: #b45309;
        }

        /* === REASON BOX === */
        .reason-box {
            background: #fafafa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 15px;
            line-height: 1.7;
            color: #374151;
        }

        /* === PHOTO === */
        .photo-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .photo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .photo-card img {
            width: 100%;
            height: auto;
            display: block;
        }

        .photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .photo-card:hover .photo-overlay { opacity: 1; }

        .photo-overlay span {
            color: var(--white);
            font-size: 13px;
            font-weight: 600;
            background: rgba(0,0,0,0.6);
            padding: 6px 14px;
            border-radius: 20px;
        }

        .no-photo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 40px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px dashed var(--border);
            color: var(--text-muted);
            text-align: center;
        }

        .no-photo span { font-size: 13px; }

        /* === UPLOAD === */
        .upload-section { margin-top: 20px; }

        .upload-hint {
            font-size: 13px;
            color: var(--navy);
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            padding: 10px 12px;
            margin: 0 0 12px 0;
        }

        .upload-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .file-input-wrapper { position: relative; }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-label:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .btn-upload {
            padding: 10px 20px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-upload:hover { background: var(--navy-dark); }
        .btn-upload.is-hidden { display: none; }

        .preview-container {
            margin-top: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .preview-label {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .preview-container img {
            width: 100%;
            max-width: 280px;
            border-radius: 8px;
        }

        .format-hint {
            margin: 8px 0 0 0;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* === MAP === */
        .map-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 12px;
        }

        .map-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .map-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--navy);
            text-decoration: none;
        }

        .map-link:hover { text-decoration: underline; }

        .accuracy-badge {
            display: inline-block;
            margin-left: 12px;
            font-size: 11px;
            color: var(--text-muted);
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* === ACTION BAR === */
        .action-bar {
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 16px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-cancel {
            background: var(--white);
            border-color: #fecaca;
            color: #dc2626;
        }

        .btn-cancel:hover {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        /* === VIEWER === */
        .viewer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.95);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .viewer-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: var(--white);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .viewer-close:hover { background: rgba(255,255,255,0.25); }

        #simple-viewer-img {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
            border-radius: 4px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .detail-layout {
                grid-template-columns: 1fr;
                padding: 16px;
                gap: 16px;
            }

            .detail-side { order: -1; }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .detail-header {
                padding: 12px 16px;
            }

            .card-section {
                padding: 16px;
                margin-bottom: 12px;
            }

            .employee-card {
                padding: 20px;
            }

            .avatar-circle {
                width: 52px;
                height: 52px;
                font-size: 22px;
                border-radius: 12px;
            }

            .employee-name {
                font-size: 18px;
            }

            .status-banner {
                padding: 14px 16px;
            }

            .map-link, .accuracy-badge {
                display: block;
                margin-left: 0;
                margin-top: 8px;
            }
        }
    </style>

</x-app>
