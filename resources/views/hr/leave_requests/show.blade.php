<x-app title="Detail Pengajuan (HR)">

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

    {{-- [GLOBAL NORMALIZATION] Pastikan Type selalu string --}}
    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;
        
        // Cek apakah tipe aslinya adalah CUTI (untuk default checkbox)
        $isTypeCuti = ($typeValue === 'CUTI');
    @endphp

    <div class="card">
        <div class="profile-header">
            <div class="profile-main">
                <div class="profile-avatar">
                    {{ substr($item->user->name, 0, 1) }}
                </div>
                <div class="profile-info">
                    <h2 class="profile-name">{{ $item->user->name }}</h2>
                    
                    {{-- [INFORMASI SISA CUTI] --}}
                    @php
                        $balance = $item->user->leave_balance ?? 0;
                        $balanceClass = $balance > 0 ? 'chip-balance-green' : 'chip-balance-red';
                    @endphp
                    
                    <div style="margin-bottom: 6px;">
                        <span class="{{ $balanceClass }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Sisa Cuti Tahunan: <strong>{{ $balance }} Hari</strong>
                        </span>
                    </div>

                    <div class="profile-meta">
                        <span class="chip-role">{{ $item->user->role }}</span>
                        <span class="dot">•</span>
                        <span>Diajukan: {{ $item->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @php
                // [LOGIC STATUS - HR VIEW]
                $status = $item->status;
                $badgeClass = 'badge-gray';
                $statusLabel = $item->status_label ?? $status; 
                
                if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $badgeClass = 'badge-green';
                    // Cek Role Owner
                    $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
                    $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
                    
                    $statusLabel = $isOwnerHRD ? '✅ Disetujui General Manager' : 'Disetujui HRD';
                } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $badgeClass = 'badge-red';
                    $statusLabel = 'Ditolak';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $badgeClass = 'badge-yellow';
                    $statusLabel = '⏳ Menunggu Persetujuan Atasan';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
                    $badgeClass = 'badge-teal';
                    $statusLabel = '✅ Disetujui Atasan (Verifikasi HRD)';
                } elseif ($status === 'CANCEL_REQ') { 
                    $badgeClass = 'badge-red';
                    $statusLabel = '⚠️ Request Pembatalan (SPV)';
                } elseif ($status === 'BATAL') { 
                    $badgeClass = 'badge-gray'; 
                    $statusLabel = '🚫 DIBATALKAN';
                }
            @endphp
            <div class="status-wrapper">
                <span class="badge-status {{ $badgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        <div class="divider-full"></div>

        <div class="detail-container">
            
            <div class="detail-section">
                <h4 class="section-title">Informasi Pengajuan</h4>

                <div class="info-row">
                    <div class="info-label">Jenis Pengajuan</div>
                    <div class="info-value">
                        <span class="badge-basic">{{ $item->type_label ?? $item->type }}</span>

                        {{-- [DETAIL KATEGORI CUTI KHUSUS] --}}
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
                            <div style="margin-top:6px;">
                                <span style="font-size:12px; font-weight:600; color:#1e4a8d; background:#eff6ff; padding:4px 10px; border-radius:6px; border:1px solid #dbeafe; display: inline-block;">
                                    Detail: {{ $catLabel }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Periode</div>
                    <div class="info-value">
                        {{ $item->start_date->format('d M Y') }}
                        @if($item->end_date && $item->end_date->ne($item->start_date))
                            – {{ $item->end_date->format('d M Y') }}
                        @endif
                    </div>
                </div>

                {{-- [LOGIC LABEL JAM DINAMIS] --}}
                @php
                     $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                     $endTimeLabel   = $item->end_time ? $item->end_time->format('H:i') : null;
                @endphp

                @if($startTimeLabel)
                    <div class="info-row">
                        <div class="info-label">
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
                        <div class="info-value">
                            {{ $startTimeLabel }}
                            @if($endTimeLabel)
                                – {{ $endTimeLabel }}
                            @endif
                        </div>
                    </div>
                @endif

                @if($item->approved_by)
                <div class="info-row">
                    <div class="info-label">Diputus/Direvisi Oleh</div>
                    <div class="info-value">
                        {{ $item->approver?->name }}
                        @if($item->approved_at)
                            <div class="text-muted" style="font-size:12px; margin-top:2px;">
                                {{ $item->approved_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- [INFO PIC PENGGANTI] --}}
                @if($item->substitute_pic)
                <div class="info-row">
                    <div class="info-label">PIC Pengganti</div>
                    <div class="info-value">
                        {{ $item->substitute_pic }}
                        @if($item->substitute_phone)
                            <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                                {{ $item->substitute_phone }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- [SYSTEM NOTES - AUDIT TRAIL] --}}
                @if($item->notes)
                <div class="system-note-box">
                    <div class="note-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px; margin-bottom:-2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Catatan Sistem / Revisi:
                    </div>
                    <div class="note-content">{!! nl2br(e($item->notes)) !!}</div>
                </div>
                @endif

                {{-- [CATATAN HRD - DINAMIS] --}}
                @if($item->notes_hrd)
                    @php
                        if ($item->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                            $boxBg = '#fef2f2'; $boxBorder = '#fecaca'; $titleColor = '#991b1b'; $textColor = '#7f1d1d';
                            $titleLabel = 'Alasan Penolakan (HRD):';
                        } else {
                            $boxBg = '#eff6ff'; $boxBorder = '#dbeafe'; $titleColor = '#1e40af'; $textColor = '#1e3a8a';
                            $titleLabel = 'Catatan HRD:';
                        }
                    @endphp

                    <div class="system-note-box" style="background-color: {{ $boxBg }}; border-color: {{ $boxBorder }}; margin-top:12px;">
                        <div class="note-label" style="color: {{ $titleColor }};">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px; margin-bottom:-2px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            {{ $titleLabel }}
                        </div>
                        <div class="note-content" style="color: {{ $textColor }}; font-weight:500;">
                            {{ $item->notes_hrd }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="detail-section">
                <h4 class="section-title">Keterangan & Bukti</h4>

                <div class="info-row">
                    <div class="info-label">Alasan</div>
                    <div class="info-value box-reason">
                        {{ $item->reason }}
                    </div>
                </div>

                @php
                    $url = $item->photo
                        ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                        : null;
                @endphp

                <div class="info-row">
                    <div class="info-label">Lampiran Foto</div>
                    <div class="info-value">
                        @if($url)
                            <div class="photo-preview js-view-photo" data-url="{{ $url }}">
                                <img src="{{ $url }}" alt="Bukti Izin">
                                <div class="overlay">
                                    <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24" style="margin-bottom:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Full Screen</span>
                                </div>
                            </div>
                        @else
                            <span class="text-muted" style="font-style:italic;">Tidak ada lampiran foto.</span>
                        @endif
                    </div>
                </div>

                @if($item->latitude && $item->longitude)
                <div class="info-row" style="margin-top:20px;">
                    <div class="info-label">
                        Lokasi Pengajuan
                        <span style="font-weight:400; color:#6b7280; font-size:11px;">(±{{ (int)$item->accuracy_m }}m)</span>
                    </div>
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed"
                            loading="lazy"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div style="margin-top:6px;">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" 
                           target="_blank" class="link-map">
                           Buka di Google Maps ↗
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="action-footer">
            <div class="left-action">
                <a href="{{ route('hr.leave.index') }}" class="btn-modern btn-back">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>

            <div class="right-action">
                {{-- [LOGIC TOMBOL AKSI HRD] --}}

                @if($item->status === 'CANCEL_REQ')
                    {{-- SKENARIO 1: REQUEST BATAL --}}
                    <div style="margin-right: 12px; font-weight:600; color:#9f1239; font-size:13.5px; display:flex; align-items:center; gap:6px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Supervisor Mengajukan Pembatalan
                    </div>
                    <button type="button" data-modal-target="modal-delete" class="btn-modern btn-reject">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Setujui Pembatalan
                    </button>

                @elseif($item->status === 'BATAL')
                    {{-- SKENARIO 2: SUDAH DIBATALKAN --}}
                    <div class="processed-info" style="color:#6b7280; font-weight:600;">
                        ⛔ Pengajuan Ini Sudah Dibatalkan
                    </div>

                @else
                    {{-- SKENARIO 3: NORMAL OPERATION --}}

                    {{-- [FULL EDIT / GOD MODE] Tombol Edit --}}
                    <button type="button" data-modal-target="modal-edit-hr" class="btn-modern btn-warning-outline" style="margin-right:8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit
                    </button>

                    <button type="button" data-modal-target="modal-delete" class="btn-modern btn-danger-outline" style="margin-right:16px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Batalkan
                    </button>

                    @if($item->status == \App\Models\LeaveRequest::PENDING_HR)
                        
                        <div style="height:24px; width:1px; background:#e5e7eb; margin-right:16px;"></div>

                        {{-- Tombol Reject --}}
                        <button type="button" data-modal-target="modal-reject" class="btn-modern btn-reject">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>

                        {{-- Tombol Approve --}}
                        <button type="button" data-modal-target="modal-approve" class="btn-modern btn-approve">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Final
                        </button>

                    @else
                        <div class="processed-info">
                            @if($item->status === \App\Models\LeaveRequest::STATUS_APPROVED)
                                @php
                                    $roleVal = $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->value : $item->user->role;
                                    $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
                                @endphp
                                <span style="color:#166534; font-weight:600;">
                                    {{ $isOwnerHRD ? '✅ Disetujui General Manager' : 'Status: Disetujui HRD' }}
                                </span>
                            @else
                                Status: <strong>{{ $statusLabel }}</strong>
                            @endif
                        </div>
                    @endif

                @endif
            </div>
        </div>
    </div>

    {{-- [SIMPLE FULL SCREEN VIEWER] --}}
    <div id="simple-viewer" class="simple-viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="btn-close-simple">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    {{-- [MODAL EDIT FULL (GOD MODE)] --}}
    <x-modal id="modal-edit-hr" title="Edit Data Pengajuan (Full Access)" type="form">
        <form action="{{ route('leave-requests.update', $item->id) }}" method="POST" id="form-edit-hr" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div style="max-height: 70vh; overflow-y: auto; padding-right: 5px;">
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
                        <label class="lbl-edit">Jam Mulai / Datang</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $item->start_time ? $item->start_time->format('H:i') : '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="lbl-edit">Jam Selesai / Pulang</label>
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
                    <label class="lbl-edit">Alasan / Keterangan</label>
                    <textarea name="reason" rows="3" class="form-control">{{ $item->reason }}</textarea>
                </div>

                <div class="form-group" style="background:#f9fafb; padding:10px; border-radius:6px; border:1px dashed #d1d5db;">
                    <label class="lbl-edit">Upload Bukti / Foto (Opsional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*,.pdf" style="font-size:12px;">
                    <small style="font-size:11px; color:#6b7280; display:block; margin-top:4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:text-bottom"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Upload baru akan menggantikan foto lama (jika ada).
                    </small>
                </div>
            </div>
            
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #eee; padding-top:15px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-approve" style="border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">Simpan Perubahan (HRD)</button>
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
                    if(typeSelect.value === 'CUTI_KHUSUS') {
                        specialWrapper.style.display = 'block';
                    } else {
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

    {{-- [MODAL DELETE (BATALKAN)] --}}
    <x-modal id="modal-delete" title="Ubah Status menjadi BATAL?" type="form">
        <form action="{{ route('leave-requests.destroy', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')

            @if($item->status === 'CANCEL_REQ')
                <p style="margin:0; color:#374151; font-weight:600;">
                    Konfirmasi Pembatalan (Request Supervisor).
                </p>
                <p style="margin:8px 0 0 0; font-size:0.9em; color:#6b7280;">
                    Status pengajuan akan diubah menjadi <strong>BATAL</strong>.
                </p>
            @else
                <p style="margin:0; color:#374151;">
                    Anda akan membatalkan pengajuan ini secara paksa.
                </p>
                <p style="margin:8px 0 0 0; font-size:0.85rem; color:#dc2626;">
                    Status akan berubah menjadi BATAL. Data tetap tersimpan sebagai riwayat.
                </p>
            @endif

            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-modal-close="true" class="btn-secondary" style="padding:8px 16px; border:1px solid #d1d5db; background:#fff; border-radius:6px; cursor:pointer;">Tidak</button>
                <button type="submit" class="btn-danger-outline" style="background:#dc2626; color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">Ya, Batalkan</button>
            </div>
        </form>
    </x-modal>

    {{-- [MODAL REJECT] --}}
    <x-modal id="modal-reject" title="Tolak Pengajuan?" type="form">
        <form action="{{ route('hr.leave.reject', $item) }}" method="POST" style="width:100%;">
            @csrf
            
            <p style="margin:0; color:#374151; margin-bottom:12px;">
                Anda akan menolak pengajuan ini. Silakan berikan alasannya.
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
                <button type="submit" class="btn-reject" style="background:#dc2626; color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">Tolak Pengajuan</button>
            </div>
        </form>
    </x-modal>

    {{-- [MODAL APPROVE (DENGAN OPSI POTONG CUTI)] --}}
    <x-modal id="modal-approve" title="Setujui Pengajuan?" type="form">
        <form action="{{ route('hr.leave.approve', $item) }}" method="POST" style="width:100%;">
            @csrf
            
            <p style="margin:0; color:#374151; margin-bottom:12px;">
                Konfirmasi persetujuan final untuk pengajuan ini.
            </p>

            {{-- [BARU] OPSI POTONG CUTI --}}
            <div class="form-group" style="margin-bottom: 15px; background: #f3f4f6; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <label class="checkbox-wrapper" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="deduct_leave" value="1" style="width: 16px; height: 16px; accent-color: #1e4a8d; cursor: pointer;"
                        {{ $isTypeCuti ? 'checked' : '' }}>
                    <span style="font-weight: 600; color: #1f2937; font-size: 14px;">Potong Cuti</span>
                </label>
                <small style="display: block; margin-top: 4px; color: #6b7280; font-size: 12px; margin-left: 24px;">
                    Jika dicentang, saldo cuti karyawan akan dikurangi sesuai durasi pengajuan.
                </small>
            </div>

            {{-- Input Catatan (Opsional) --}}
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
                <button type="submit" class="btn-approve" style="border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">Ya, Setujui</button>
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
        /* Shared Styles */
        .simple-viewer-overlay { position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.95); z-index: 99999; display: flex; align-items: center; justify-content: center; }
        .btn-close-simple { position: absolute; top: 20px; right: 20px; background: rgba(255, 255, 255, 0.1); border: none; color: #fff; width: 48px; height: 48px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 100000; }
        .btn-close-simple:hover { background: rgba(255, 255, 255, 0.3); }
        #simple-viewer-img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }

        /* --- UTILITY & ALERTS --- */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }
        .text-muted { color: #6b7280; }

        /* --- CARD --- */
        .card { 
            background: #fff; 
            border-radius: 12px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); 
            border: 1px solid #f3f4f6; 
            overflow: hidden; 
        }

        /* --- PROFILE HEADER --- */
        .profile-header { padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; background: #fff; }
        .profile-main { display: flex; gap: 16px; align-items: center; }
        .profile-avatar { 
            width: 56px; height: 56px; 
            background: #eef2ff; color: #1e4a8d; 
            border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 22px; font-weight: 700; 
        }
        .profile-info { display: flex; flex-direction: column; gap: 4px; }
        .profile-name { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        
        .profile-meta { font-size: 13px; color: #6b7280; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
        .dot { color: #d1d5db; display: inline-block; transform: scale(1.2); }
        .chip-role { 
            background: #f3f4f6; color: #4b5563; 
            padding: 2px 8px; border-radius: 6px; 
            font-size: 11px; text-transform: uppercase; 
            letter-spacing: 0.04em; font-weight: 600; 
        }
        
        .chip-balance-green {
            background: #ecfdf5; color: #059669; 
            padding: 2px 8px; border-radius: 6px; 
            font-size: 12px; font-weight: 600; 
            display: inline-flex; align-items: center;
            border: 1px solid #d1fae5;
        }
        .chip-balance-red {
            background: #fef2f2; color: #b91c1c; 
            padding: 2px 8px; border-radius: 6px; 
            font-size: 12px; font-weight: 600; 
            display: inline-flex; align-items: center;
            border: 1px solid #fee2e2;
        }

        .divider-full { height: 1px; background: #f3f4f6; width: 100%; }
        
        /* --- DETAILS LAYOUT --- */
        .detail-container { 
            padding: 32px; 
            display: grid; 
            grid-template-columns: 1fr 1.5fr; 
            gap: 48px; 
        }

        .section-title { 
            font-size: 14px; font-weight: 700; color: #111827; 
            text-transform: uppercase; letter-spacing: 0.05em; 
            margin: 0 0 20px 0; padding-bottom: 8px; 
            border-bottom: 2px solid #f3f4f6; display: inline-block; 
        }
        
        .info-row { margin-bottom: 20px; }
        .info-label { font-size: 12px; color: #6b7280; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; }
        .info-value { font-size: 15px; color: #1f2937; font-weight: 500; line-height: 1.6; }
        
        .box-reason { 
            background: #fdfdfd; 
            padding: 16px; 
            border-radius: 12px; 
            border: 1px solid #f3f4f6; 
            color: #374151; font-size: 14.5px; 
            line-height: 1.6;
        }

        /* --- SYSTEM NOTES --- */
        .system-note-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 16px; margin-top: 10px; }
        .note-label { font-size: 12px; font-weight: 700; color: #92400e; margin-bottom: 6px; text-transform: uppercase; display: flex; align-items: center; }
        .note-content { font-size: 14px; color: #b45309; line-height: 1.5; }

        /* --- BADGES (Unified with Index) --- */
        .badge-basic { background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid #e5e7eb; display: inline-block; }
        
        .badge-status { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

        /* --- PHOTO PREVIEW --- */
        .photo-preview { position: relative; width: 100%; max-width: 320px; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .photo-preview:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .photo-preview img { width: 100%; height: auto; display: block; }
        .photo-preview .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; flex-direction:column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
        .photo-preview:hover .overlay { opacity: 1; }
        .photo-preview .overlay span { color: #fff; font-size: 12px; font-weight: 600; background: rgba(0,0,0,0.6); padding: 6px 14px; border-radius: 20px; backdrop-filter: blur(4px); }

        /* --- MAP --- */
        .map-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; border: 1px solid #e5e7eb; margin-top: 6px; }
        .map-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
        .link-map { font-size: 13px; color: #1e4a8d; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .link-map:hover { text-decoration: underline; }

        /* --- FOOTER ACTION --- */
        .action-footer { background: #f9fafb; padding: 20px 32px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .right-action { display: flex; gap: 12px; align-items: center; }

        /* --- BUTTONS --- */
        .btn-modern { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 22px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; border: 1px solid transparent; text-decoration: none; line-height: 1.25; min-width: 140px; }
        
        .btn-back { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-back:hover { background: #f3f4f6; border-color: #9ca3af; color: #111827; }

        .btn-approve { background: #1e4a8d; color: #fff; border: 1px solid #1e4a8d; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .btn-approve:hover { background: #163a75; border-color: #163a75; transform: translateY(-1px); }

        .btn-reject { background: #fff; border-color: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }

        .btn-warning-outline { background: #fff; border-color: #fcd34d; color: #b45309; }
        .btn-warning-outline:hover { background: #fffbeb; }

        .btn-danger-outline { background: #fff; border-color: #fee2e2; color: #dc2626; }
        .btn-danger-outline:hover { background: #fef2f2; border-color: #fca5a5; }

        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }

        .processed-info { font-size: 13.5px; color: #6b7280; background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; font-weight: 500; }

        /* --- RESPONSIVE --- */
        @media(max-width: 1024px) {
            .detail-container { grid-template-columns: 1fr; gap: 32px; padding: 24px; }
            .section-title { width: 100%; border-bottom-width: 1px; }
        }

        @media(max-width: 640px) {
            .profile-header { flex-direction: column; gap: 16px; align-items: stretch; padding: 20px; }
            .status-wrapper { align-self: flex-start; }
            
            .action-footer { flex-direction: column; gap: 16px; align-items: stretch; padding: 16px 20px; }
            .left-action, .right-action { width: 100%; justify-content: stretch; }
            .right-action { flex-direction: column; gap: 10px; }
            .btn-modern { width: 100%; justify-content: center; min-width: 0; }
            .btn-modern svg { margin-right: 4px; }
            
            /* Hide vertical separator in simplified mobile view */
            .right-action > div[style*="width:1px"] { display: none; }
            
            .info-value { font-size: 14px; }
        }
    </style>

</x-app>