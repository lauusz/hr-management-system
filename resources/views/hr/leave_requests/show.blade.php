<x-app title="Detail Pengajuan (HR)">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        {{ $errors->first() }}
    </div>
    @endif

    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;
        $isTypeCuti = ($typeValue === 'CUTI');

        // Status
        $status = $item->status;
        $badgeClass = 'badge-gray';
        $statusLabel = $item->status_label ?? $status;

        if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $badgeClass = 'badge-green';
            $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
            $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
            $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
        } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $badgeClass = 'badge-red';
            $statusLabel = 'Ditolak';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $badgeClass = 'badge-yellow';
            $statusLabel = 'Menunggu Atasan';
        } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
            $badgeClass = 'badge-teal';
            $statusLabel = 'Verifikasi HRD';
        } elseif ($status === 'BATAL') {
            $badgeClass = 'badge-gray';
            $statusLabel = 'Dibatalkan';
        }

        // Leave balance
        $balance = $item->user->leave_balance ?? 0;

        // Duration calculation
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
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <button type="button" class="btn-back" onclick="sessionStorage.setItem('hrLeaveForceRefreshOnBack', '1'); history.back();">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </button>
        <div class="header-text">
            <h1 class="page-title">Detail Pengajuan</h1>
            <p class="page-subtitle">{{ $item->type_label ?? $item->type }}</p>
        </div>
    </div>

    {{-- EMPLOYEE CARD --}}
    <div class="card-employee">
        <div class="employee-left">
            <div class="avatar-lg">
                {{ substr($item->user->name, 0, 1) }}
            </div>
            <div class="employee-info">
                <h2 class="employee-name">{{ $item->user->name }}</h2>
                <div class="employee-meta">
                    <span class="meta-role">{{ $item->user->role }}</span>
                    <span class="meta-dot"></span>
                    <span class="meta-division">{{ $item->user->division->name ?? '-' }}</span>
                </div>
                <div class="employee-submitted">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Diajukan {{ $item->created_at->diffForHumans() }}
                </div>
            </div>
        </div>
        <div class="employee-right">
            <div class="status-badge-lg {{ $badgeClass }}">
                {{ $statusLabel }}
            </div>
            @if($isTypeCuti || $typeValue === 'CUTI_KHUSUS')
            <div class="balance-chip {{ $balance > 0 ? 'balance-ok' : 'balance-low' }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Sisa Cuti: <strong>{{ $balance }} hari</strong>
            </div>
            @endif
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="content-grid">
        {{-- LEFT COLUMN --}}
        <div class="content-col">
            {{-- PERIOD CARD --}}
            <div class="card">
                <div class="card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">Periode Izin</div>
                    <div class="card-value-lg">
                        {{ $item->start_date->format('d M Y') }}
                        @if($end->ne($start))
                            <span class="date-separator">—</span>
                            {{ $end->format('d M Y') }}
                        @endif
                    </div>
                    <div class="card-meta">
                        <span class="duration-badge">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $durationLabel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- TIME DETAILS --}}
            @if($startTimeLabel)
            <div class="card">
                <div class="card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">
                        @if($endTimeLabel)
                            Jam Izin
                        @elseif($typeValue === 'IZIN_TELAT')
                            Estimasi Jam Tiba
                        @elseif($typeValue === 'IZIN_PULANG_AWAL')
                            Jam Pulang Awal
                        @else
                            Jam Mulai
                        @endif
                    </div>
                    <div class="card-value-lg">
                        {{ $startTimeLabel }}
                        @if($endTimeLabel)
                            <span class="date-separator">—</span>
                            {{ $endTimeLabel }}
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- PIC DETAILS --}}
            @if($item->substitute_pic)
            <div class="card">
                <div class="card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">PIC Pengganti</div>
                    <div class="card-value">{{ $item->substitute_pic }}</div>
                    @if($item->substitute_phone)
                    <div class="card-meta">{{ $item->substitute_phone }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- APPROVER INFO --}}
            @if($item->approved_by && $item->approver)
            <div class="card">
                <div class="card-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">Diputuskan Oleh</div>
                    <div class="card-value">{{ $item->approver->name }}</div>
                    @if($item->approved_at)
                    <div class="card-meta">{{ $item->approved_at->format('d M Y, H:i') }}</div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="content-col">
            {{-- REASON CARD --}}
            <div class="card card-full">
                <div class="card-header">
                    <div class="card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <span class="card-title">Alasan Pengajuan</span>
                </div>
                <div class="reason-box">
                    {{ $item->reason }}
                </div>
            </div>

            {{-- PHOTO CARD --}}
            @if($photoUrl)
            <div class="card card-full">
                <div class="card-header">
                    <div class="card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="card-title">Bukti / Lampiran</span>
                </div>
                <div class="photo-preview js-view-photo" data-url="{{ $photoUrl }}">
                    <img src="{{ $photoUrl }}" alt="Bukti Izin">
                    <div class="photo-overlay">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Lihat Full</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- LOCATION CARD --}}
            @if($item->latitude && $item->longitude)
            <div class="card card-full card-map">
                <div class="map-header">
                    <div class="card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="card-title">Lokasi Pengajuan</span>
                    <span class="accuracy-badge">±{{ (int)$item->accuracy_m }}m</span>
                </div>
                <div class="map-wrapper">
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed"
                            loading="lazy"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}"
                       target="_blank" class="map-external-link">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
            @endif

            {{-- HR NOTES --}}
            @if($item->notes_hrd)
                @php
                    if ($item->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                        $notesBoxBg = '#fef2f2';
                        $notesBoxBorder = '#fecaca';
                        $notesTitleColor = '#991b1b';
                        $notesTextColor = '#7f1d1d';
                        $notesIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
                        $notesLabel = 'Alasan Penolakan';
                    } else {
                        $notesBoxBg = '#eff6ff';
                        $notesBoxBorder = '#dbeafe';
                        $notesTitleColor = '#1e4a8d';
                        $notesTextColor = '#1e3a8a';
                        $notesIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>';
                        $notesLabel = 'Catatan HRD';
                    }
                @endphp
            <div class="notes-box" style="background: {{ $notesBoxBg }}; border-color: {{ $notesBoxBorder }};">
                <div class="notes-header" style="color: {{ $notesTitleColor }};">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">{{ $notesIcon }}</svg>
                    {{ $notesLabel }}
                </div>
                <div class="notes-content" style="color: {{ $notesTextColor }};">
                    {{ $item->notes_hrd }}
                </div>
            </div>
            @endif

            {{-- SYSTEM NOTES --}}
            @if($item->notes)
            <div class="notes-box notes-system">
                <div class="notes-header">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Catatan Sistem
                </div>
                <div class="notes-content">
                    {!! nl2br(e($item->notes)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
        <div class="action-main">
            @if($item->status === 'BATAL')
                <div class="status-notice status-notice-gray">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Pengajuan Sudah Dibatalkan
                </div>

            @elseif(!in_array($item->status, [\App\Models\LeaveRequest::STATUS_REJECTED, 'BATAL'], true))
                {{-- Edit & Batalkan: tampil untuk semua status (selain REJECTED/BATAL) --}}
                <button type="button" data-modal-target="modal-edit-hr" class="action-btn action-btn-edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
                </button>
                <button type="button" data-modal-target="modal-delete" class="action-btn action-btn-batal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Batalkan
                </button>
                {{-- Tolak & Terima: hanya untuk PENDING + user punya hak approve --}}
                @if($canApprove && in_array($item->status, [\App\Models\LeaveRequest::PENDING_HR, \App\Models\LeaveRequest::PENDING_SUPERVISOR], true))
                    <button type="button" data-modal-target="modal-reject" class="action-btn action-btn-tolak">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>
                    <button type="button" data-modal-target="modal-approve" class="action-btn action-btn-setuju">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        Terima
                    </button>
                @endif

            @else
                <div class="status-notice status-notice-gray">
                    {{ $statusLabel }}
                </div>
            @endif
        </div>
    </div>

    {{-- PHOTO VIEWER --}}
    <div id="simple-viewer" class="viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="viewer-close">
            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    {{-- MODALS --}}
    <x-modal id="modal-edit-hr" title="Edit Data Pengajuan" type="form">
        <form action="{{ route('hr.leave.update', $item->id) }}" method="POST" id="form-edit-hr" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="max-height: 60vh; overflow-y: auto; padding-right: 4px;">
                <div class="form-group">
                    <label class="lbl-edit">Jenis Pengajuan</label>
                    <select name="type" id="edit_type" class="form-control">
                        @foreach(\App\Enums\LeaveType::cases() as $type)
                            <option value="{{ $type->value }}" @selected($typeValue == $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="edit_special_wrapper" style="display:none; background:#eff6ff; padding:10px; border-radius:6px; margin-bottom:12px;">
                    <label class="lbl-edit" style="color:#1e40af;">Kategori Cuti Khusus</label>
                    <select name="special_leave_detail" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @php
                            $specialList = [
                                'NIKAH_KARYAWAN' => 'Menikah', 'ISTRI_MELAHIRKAN' => 'Istri Melahirkan',
                                'ISTRI_KEGUGURAN' => 'Istri Keguguran', 'KHITANAN_ANAK' => 'Khitanan Anak',
                                'PEMBAPTISAN_ANAK' => 'Pembaptisan Anak', 'NIKAH_ANAK' => 'Pernikahan Anak',
                                'DEATH_CORE' => 'Kematian Inti', 'DEATH_EXTENDED' => 'Kematian Saudara/Ipar',
                                'DEATH_HOUSE' => 'Kematian Orang Serumah', 'HAJI' => 'Ibadah Haji', 'UMROH' => 'Ibadah Umroh'
                            ];
                        @endphp
                        @foreach($specialList as $code => $label)
                            <option value="{{ $code }}" @selected($item->special_leave_category == $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $item->start_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $item->end_date->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">Jam Mulai</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $item->start_time ? $item->start_time->format('H:i') : '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">Jam Selesai</label>
                        <input type="time" name="end_time" class="form-control" value="{{ $item->end_time ? $item->end_time->format('H:i') : '' }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">PIC Pengganti</label>
                        <input type="text" name="substitute_pic" class="form-control" value="{{ $item->substitute_pic }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">No. HP PIC</label>
                        <input type="text" name="substitute_phone" class="form-control" value="{{ $item->substitute_phone }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="lbl-edit">Alasan</label>
                    <textarea name="reason" rows="3" class="form-control">{{ $item->reason }}</textarea>
                </div>

                <div class="form-group" style="margin-bottom:12px;">
                    <label class="lbl-edit">Catatan HRD</label>
                    <textarea name="notes_hrd" rows="2" class="form-control" placeholder="Contoh: Potong uang makan.">{{ $item->notes_hrd }}</textarea>
                </div>

                <div class="form-group" style="background:#f9fafb; padding:10px; border-radius:6px; border:1px dashed #d1d5db;">
                    <label class="lbl-edit">Upload Foto (Opsional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*,.pdf" style="font-size:12px;">
                    <small style="font-size:11px; color:#6b7280; display:block; margin-top:4px;">Upload baru akan menggantikan foto lama.</small>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; padding-top:15px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-approve" style="border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">Simpan</button>
            </div>
        </form>

        <style>
            .lbl-edit { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:4px; }
            .form-control { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13.5px; }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('edit_type');
                const specialWrapper = document.getElementById('edit_special_wrapper');

                function checkType() {
                    if(typeSelect && typeSelect.value === 'CUTI_KHUSUS') {
                        specialWrapper.style.display = 'block';
                    } else if (specialWrapper) {
                        specialWrapper.style.display = 'none';
                    }
                }

                if(typeSelect) {
                    typeSelect.addEventListener('change', checkType);
                    checkType();
                }
            });
        </script>
    </x-modal>

    <x-modal id="modal-delete" title="Yakin Ingin Membatalkan?" variant="danger" type="form">
        <form action="{{ route('leave-requests.destroy', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')

            <p style="margin:0; color:#374151;">
                Pengajuan ini akan dibatalkan dan tidak akan diproses lebih lanjut.
            </p>
            <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
                Data tetap tersimpan sebagai riwayat.
            </p>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-danger-outline" style="background:#dc2626; color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">Ya, Batalkan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-reject" title="Tolak Pengajuan Ini?" variant="danger" type="form">
        <form action="{{ route('hr.leave.reject', $item) }}" method="POST" style="width:100%;">
            @csrf

            <p style="margin:0 0 12px 0; color:#374151;">
                Berikan alasan penolakan agar karyawan dapat memahami keputusannya.
            </p>

            <div class="form-group">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">
                    Alasan Penolakan <span style="color:red">*</span>
                </label>
                <textarea
                    name="notes_hrd"
                    rows="3"
                    class="form-control"
                    placeholder="Contoh: Kuota cuti tahunan sudah habis / Dokumen tidak lengkap."
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:10px; font-size:14px; font-family:inherit;"></textarea>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-reject" style="background:#dc2626; color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">Ya, Tolak</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="modal-approve" title="Terima Pengajuan Ini?" variant="success" type="form">
        <form action="{{ route('hr.leave.approve', $item) }}" method="POST" style="width:100%;">
            @csrf

            <p style="margin:0 0 12px 0; color:#374151;">
                Pengajuan akan disetujui dan diproses sesuai kebijakan yang berlaku.
            </p>

            @if($isTypeCuti)
            <div class="form-group" style="margin-bottom: 15px; background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <label class="checkbox-wrapper" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="deduct_leave" value="1" style="width: 16px; height: 16px; accent-color: #1e4a8d; cursor: pointer;" checked>
                    <span style="font-weight: 600; color: #1f2937; font-size: 14px;">Potong Cuti</span>
                </label>
                <small style="display: block; margin-top: 4px; color: #6b7280; font-size: 12px; margin-left: 24px;">
                    Jika dicentang, saldo cuti karyawan akan dikurangi sesuai durasi pengajuan.
                </small>
            </div>
            @endif

            <div class="form-group">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">
                    Catatan HRD (Opsional)
                </label>
                <textarea
                    name="notes_hrd"
                    rows="2"
                    class="form-control"
                    placeholder="Contoh: Potong uang makan."
                    style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:10px; font-size:14px; font-family:inherit;"></textarea>
                <small style="font-size:11px; color:#6b7280;">Karyawan dapat melihat catatan ini.</small>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-approve" style="border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">Ya, Terima</button>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer.style.display === 'flex') closeViewer(); });
        });
    </script>

    <style>
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

        /* --- ALERTS --- */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* --- PAGE HEADER --- */
        .page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .btn-back {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-back:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .btn-back:hover svg {
            transform: translateX(-2px);
        }

        .btn-back svg { transition: transform 0.2s; }

        .header-text { flex: 1; }

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .page-subtitle {
            margin: 2px 0 0;
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* --- EMPLOYEE CARD --- */
        .card-employee {
            background: var(--white);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .employee-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar-lg {
            width: 56px;
            height: 56px;
            background: var(--navy);
            color: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .employee-info { min-width: 0; }

        .employee-name {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .meta-role {
            background: #eff6ff;
            color: var(--navy);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .meta-dot {
            width: 4px;
            height: 4px;
            background: var(--text-muted);
            border-radius: 50%;
        }

        .meta-division {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .employee-submitted {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .employee-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .status-badge-lg {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .balance-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .balance-ok {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .balance-low {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* --- BADGES --- */
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; }
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* --- CONTENT GRID --- */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
            margin-bottom: 100px;
        }

        .content-col {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* --- CARDS --- */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .card-full {
            flex-direction: column;
            gap: 12px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--navy);
            flex-shrink: 0;
        }

        .card-content { flex: 1; min-width: 0; }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .card-value-lg {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .date-separator {
            color: var(--text-muted);
            margin: 0 8px;
        }

        .card-meta {
            margin-top: 6px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .duration-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .accuracy-badge {
            margin-left: auto;
            font-size: 11px;
            color: var(--text-muted);
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* --- REASON BOX --- */
        .reason-box {
            background: #fafafa;
            padding: 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* --- PHOTO --- */
        .photo-preview {
            position: relative;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border);
            cursor: pointer;
        }

        .photo-preview img {
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
            opacity: 0;
            transition: opacity 0.2s;
            color: white;
            gap: 8px;
        }

        .photo-preview:hover .photo-overlay { opacity: 1; }

        .photo-overlay span {
            font-size: 12px;
            font-weight: 600;
            background: rgba(0,0,0,0.6);
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* --- MAP --- */
        .card-map {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .map-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-wrapper {
            margin-top: 4px;
        }

        .map-container {
            position: relative;
            width: 100%;
            height: 280px;
            overflow: hidden;
            border-radius: 12px;
            border: 2px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .map-external-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--navy);
            text-decoration: none;
            font-weight: 600;
            margin-top: 12px;
            padding: 8px 14px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .map-external-link:hover {
            background: var(--navy);
            color: var(--white);
            border-color: var(--navy);
        }

        /* --- NOTES BOX --- */
        .notes-box {
            border-radius: 10px;
            padding: 14px 16px;
            border: 1px solid;
        }

        .notes-system {
            background: #fffbeb;
            border-color: #fef3c7;
        }

        .notes-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
            color: #92400e;
        }

        .notes-system .notes-header { color: #92400e; }

        .notes-content {
            font-size: 14px;
            line-height: 1.5;
            color: #b45309;
        }

        /* --- ACTION BAR --- */
        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            z-index: 100;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
        }

        .action-main {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            width: 100%;
            justify-content: center;
        }

        .action-btn {
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
        }

        .action-btn-edit {
            background: var(--gray-100);
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .action-btn-edit:hover { background: var(--gray-200); }

        .action-btn-batal {
            background: var(--white);
            color: #dc2626;
            border: 1px solid #fee2e2;
        }
        .action-btn-batal:hover { background: #fef2f2; }

        .action-btn-tolak {
            background: var(--white);
            color: #dc2626;
            border: 1px solid #fee2e2;
        }
        .action-btn-tolak:hover { background: #fef2f2; }

        .action-btn-setuju {
            background: var(--navy);
            color: var(--white);
        }
        .action-btn-setuju:hover { background: var(--navy-dark); }

        .status-notice {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            background: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid #fde68a;
        }

        .status-notice svg { flex-shrink: 0; }

        .status-notice-gray {
            background: var(--gray-100);
            color: var(--text-secondary);
            border-color: var(--border);
        }

        .btn-back-md {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-back-md:hover {
            border-color: var(--text-muted);
            color: var(--text-primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--navy);
            color: var(--white);
        }

        .btn-primary:hover { background: var(--navy-dark); }

        .btn-secondary {
            background: var(--white);
            border-color: var(--border);
            color: var(--text-secondary);
        }

        .btn-secondary:hover { background: #f9fafb; }

        .btn-outline-danger {
            background: var(--white);
            border-color: #fecaca;
            color: #dc2626;
        }

        .btn-outline-danger:hover { background: #fef2f2; }

        .btn-danger-outline {
            background: var(--white);
            border: 1px solid #fee2e2;
            color: #dc2626;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-danger-outline:hover { background: #fef2f2; }

        .btn-approve {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-reject {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary {
            padding: 10px 18px;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .cancel-request-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #991b1b;
        }

        .status-processed {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            background: #f9fafb;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        /* --- VIEWER --- */
        .viewer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
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

        .viewer-close:hover { background: rgba(255,255,255,0.3); }

        #simple-viewer-img {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .map-container {
                height: 220px;
            }

            .map-external-link {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }

            .page-header {
                gap: 12px;
            }

            .btn-back {
                width: 36px;
                height: 36px;
            }

            .page-title { font-size: 18px; }

            .card-employee {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px 20px;
            }

            .employee-right {
                align-items: flex-start;
                width: 100%;
                padding-top: 12px;
                border-top: 1px solid var(--border);
            }

            .status-badge-lg {
                font-size: 11px;
            }

            .action-bar {
                flex-direction: column;
                gap: 12px;
                padding: 16px;
            }

            .btn-back-md {
                width: 100%;
                justify-content: center;
            }

            .action-group {
                width: 100%;
                justify-content: center;
            }

            .action-divider { display: none; }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .cancel-request-notice,
            .status-processed {
                width: 100%;
                justify-content: center;
            }

            .content-grid {
                margin-bottom: 200px;
            }
        }

        @media (max-width: 480px) {
            .card-employee {
                padding: 14px 16px;
            }

            .avatar-lg {
                width: 48px;
                height: 48px;
                font-size: 18px;
            }

            .employee-name { font-size: 16px; }

            .action-group {
                gap: 8px;
            }

            .btn {
                padding: 10px 14px;
                font-size: 13px;
            }
        }
    </style>

</x-app>
