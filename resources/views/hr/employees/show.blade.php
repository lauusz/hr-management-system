<x-app title="Detail Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Karyawan</h1>
                <p class="section-subtitle">Profil dan dokumen karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
    // Masa Kerja
    $masaKerjaDisplay = '-';
    if ($profile && $profile->tgl_bergabung) {
        $start = \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay();
        $end = $profile->exit_date ? \Carbon\Carbon::parse($profile->exit_date)->startOfDay() : \Carbon\Carbon::today();
        if ($end->greaterThanOrEqualTo($start)) {
            $diff = $start->diff($end);
            $masaKerjaDisplay = ($diff->y > 0 ? $diff->y . ' Thn ' : '') . ($diff->m > 0 ? $diff->m . ' Bln' : '');
            if(empty($masaKerjaDisplay)) $masaKerjaDisplay = $diff->d . ' Hr';
        }
    }
    // Probation
    $probationPercent = 0; $isProbation = false;
    if($profile && $profile->tgl_bergabung && $profile->tgl_akhir_percobaan && $employee->status === 'ACTIVE') {
        $startP = \Carbon\Carbon::parse($profile->tgl_bergabung);
        $endP = \Carbon\Carbon::parse($profile->tgl_akhir_percobaan);
        $now = \Carbon\Carbon::now();
        if($now->lessThanOrEqualTo($endP)) {
            $isProbation = true;
            $totalDays = $startP->diffInDays($endP);
            $currentDays = $startP->diffInDays($now);
            $probationPercent = $totalDays > 0 ? round(($currentDays / $totalDays) * 100) : 0;
        }
    }
    @endphp

    <div class="emp-page">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Top Actions --}}
        <div class="page-header">
            <button type="button" class="back-btn" onclick="history.back();">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="back-btn-text">Kembali</span>
            </button>
            <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn-edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Data
            </a>
        </div>

        {{-- Profile Hero Card --}}
        <div class="profile-card">
            <div class="profile-card-accent"></div>
            <div class="profile-card-body">
                <div class="profile-identity">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar">{{ substr($employee->name, 0, 1) }}</div>
                        <div class="profile-status-dot {{ $employee->status === 'ACTIVE' ? 'active' : 'inactive' }}"></div>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name-row">
                            <h1 class="profile-name">{{ $employee->name }}</h1>
                            <span class="profile-badge {{ $employee->status === 'ACTIVE' ? 'badge-on' : 'badge-off' }}">
                                {{ $employee->status === 'ACTIVE' ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                        <p class="profile-role">{{ $employee->position?->name ?? ($profile?->jabatan ?? 'Tanpa Jabatan') }}</p>
                        <div class="profile-meta">
                            <span class="profile-meta-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $employee->division->name ?? '-' }}
                            </span>
                            @if($profile && $profile->pt)
                            <span class="profile-meta-item">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                {{ $profile->pt->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="profile-badges">
                    <span class="profile-leave-badge">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Sisa Cuti: <strong>{{ rtrim(rtrim(sprintf('%.1f', $employee->leave_balance ?? 0), '0'), '.') }} hari</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Info Strip --}}
        <div class="quick-strip">
            <div class="quick-item">
                <span class="quick-label">NIK</span>
                <span class="quick-value">{{ $profile?->nik ?? '-' }}</span>
            </div>
            <div class="quick-item">
                <span class="quick-label">Kategori</span>
                <span class="quick-value">{{ $profile?->kategori ?? '-' }}</span>
            </div>
            <div class="quick-item">
                <span class="quick-label">Bergabung</span>
                <span class="quick-value">{{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->translatedFormat('j F Y') : '-' }}</span>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="tab-bar">
            <button class="tab-btn active" data-tab="overview">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Ringkasan
            </button>
            <button class="tab-btn" data-tab="documents">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Dokumen
            </button>
        </div>

        {{-- Tab: Overview --}}
        <div id="tab-overview" class="tab-panel active">

            {{-- Masa Kerja Card --}}
            <div class="summary-card">
                <div class="summary-card-main">
                    <div class="summary-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-label">Masa Kerja</span>
                        <span class="summary-value">{{ $masaKerjaDisplay }}</span>
                        <span class="summary-since">Sejak {{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->translatedFormat('j F Y') : '-' }}</span>
                    </div>
                </div>
                @if($isProbation)
                <div class="probation-wrap">
                    <div class="probation-header">
                        <span>Masa Percobaan</span>
                        <span>{{ $probationPercent }}%</span>
                    </div>
                    <div class="probation-bar">
                        <div class="probation-fill" style="width: {{ $probationPercent }}%"></div>
                    </div>
                    <span class="probation-end">Berakhir {{ $profile->tgl_akhir_percobaan->translatedFormat('j F Y') }}</span>
                </div>
                @elseif($employee->status !== 'ACTIVE')
                <div class="exit-wrap">
                    <span class="exit-badge">Keluar {{ $profile?->exit_date ? $profile->exit_date->translatedFormat('j F Y') : '-' }}</span>
                    <span class="exit-reason">Alasan: {{ $profile?->exit_reason_code ?? '-' }}</span>
                </div>
                @endif
            </div>

            {{-- Info Grid --}}
            <div class="info-grid">

                {{-- Identitas Diri --}}
                <div class="info-section">
                    <div class="info-section-header">
                        <div class="info-section-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <span>Identitas Diri</span>
                    </div>
                    <div class="data-list">
                        <div class="data-item">
                            <span class="data-label">Tempat, Tgl Lahir</span>
                            <span class="data-value">
                                @php
                                    $tl = $profile?->tempat_lahir ?? '';
                                    $tgl = $profile?->tgl_lahir ? $profile->tgl_lahir->translatedFormat('j F Y') : '';
                                @endphp
                                @if($tl && $tgl)
                                    {{ $tl }}, {{ $tgl }}
                                @elseif($tl)
                                    {{ $tl }}
                                @elseif($tgl)
                                    {{ $tgl }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Jenis Kelamin</span>
                            <span class="data-value">{{ $profile?->jenis_kelamin == 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Agama</span>
                            <span class="data-value">{{ $profile?->agama ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Pendidikan</span>
                            <span class="data-value">{{ $profile?->pendidikan ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kewarganegaraan</span>
                            <span class="data-value">{{ $profile?->kewarganegaraan ?? '-' }}</span>
                        </div>
                    </div>
                    @if($profile?->path_ktp || $profile?->path_kartu_keluarga)
                    <div class="doc-chips">
                        @if($profile?->path_ktp)
                        <a href="{{ asset('storage/'.$profile->path_ktp) }}" target="_blank" class="doc-chip">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                            KTP
                        </a>
                        @endif
                        @if($profile?->path_kartu_keluarga)
                        <a href="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" target="_blank" class="doc-chip">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                            KK
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Kontak & Domisili --}}
                <div class="info-section">
                    <div class="info-section-header">
                        <div class="info-section-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                        </div>
                        <span>Kontak & Domisili</span>
                    </div>
                    <div class="data-list">
                        <div class="data-item">
                            <span class="data-label">Email</span>
                            <span class="data-value data-email">{{ $profile?->email ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">No. HP</span>
                            <span class="data-value">{{ $employee->phone ?? '-' }}</span>
                        </div>
                        <div class="data-item full-width">
                            <span class="data-label">Alamat</span>
                            <span class="data-value">{{ $profile?->alamat1 ?? '-' }}</span>
                            @if($profile?->alamat2)<span class="data-sub">{{ $profile->alamat2 }}</span>@endif
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kota/Kab</span>
                            <span class="data-value">{{ $profile?->kab_kota ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kode Pos</span>
                            <span class="data-value">{{ $profile?->kode_pos ?? '-' }}</span>
                        </div>
                        @if($profile?->alamat_sesuai_ktp)
                        <div class="data-item full-width">
                            <span class="data-label">Alamat KTP</span>
                            <span class="data-value">{{ $profile->alamat_sesuai_ktp }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Payroll & BPJS --}}
                <div class="info-section">
                    <div class="info-section-header">
                        <div class="info-section-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                        <span>Payroll & BPJS</span>
                    </div>
                    <div class="data-list">
                        <div class="data-item">
                            <span class="data-label">Nama Bank</span>
                            <span class="data-value">{{ $profile?->nama_bank ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">No. Rekening</span>
                            <span class="data-value data-num">{{ $profile?->no_rekening ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">NPWP</span>
                            <span class="data-value data-num">{{ $profile?->nomor_npwp ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">PTKP</span>
                            <span class="data-value">{{ $profile?->ptkp ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">BPJS TK</span>
                            <span class="data-value data-num">{{ $profile?->bpjs_tk ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">BPJS Kesehatan</span>
                            <span class="data-value data-num">{{ $profile?->nomor_bpjs_kesehatan ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kelas BPJS</span>
                            <span class="data-value">{{ $profile?->kelas_bpjs ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Lokasi Kerja --}}
                <div class="info-section">
                    <div class="info-section-header">
                        <div class="info-section-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <span>Lokasi Kerja</span>
                    </div>
                    <div class="data-list">
                        <div class="data-item">
                            <span class="data-label">Lokasi</span>
                            <span class="data-value">{{ $profile?->lokasi_kerja ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Provinsi</span>
                            <span class="data-value">{{ $profile?->provinsi ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Kecamatan</span>
                            <span class="data-value">{{ $profile?->kecamatan ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Desa/Kelurahan</span>
                            <span class="data-value">{{ $profile?->desa_kelurahan ?? '-' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tab: Documents --}}
        <div id="tab-documents" class="tab-panel">
            <div class="docs-grid">

                {{-- Upload Form --}}
                <div class="docs-upload">
                    <div class="card">
                        <div class="card-header">Unggah Dokumen</div>
                        <form method="POST" action="{{ route('hr.employees.documents.store', $employee->id) }}" enctype="multipart/form-data" class="upload-form">
                            @csrf
                            <div class="form-group">
                                <label>Tipe Dokumen <span class="required">*</span></label>
                                <select name="type" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach($documentTypes as $type)
                                    <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Judul</label>
                                <input type="text" name="title" placeholder="Contoh: Kontrak Kerja 2026">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tgl. Efektif</label>
                                    <input type="date" name="effective_date">
                                </div>
                                <div class="form-group">
                                    <label>Tgl. Berakhir</label>
                                    <input type="date" name="expired_date">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>File <span class="required">*</span></label>
                                <input type="file" name="file" required>
                            </div>
                            <button type="submit" class="btn-primary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Document List --}}
                <div class="docs-history">
                    <div class="card">
                        <div class="card-header flex-between">
                            <span>Riwayat Dokumen</span>
                            <span class="doc-count">{{ $documents->count() }} file</span>
                        </div>

                        @if($documents->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <p class="empty-title">Belum ada dokumen</p>
                            <p class="empty-desc">Unggah dokumen karyawan melalui form di samping.</p>
                        </div>
                        @else
                        <div class="doc-list">
                            @foreach($documents as $doc)
                            <div class="doc-list-item">
                                <div class="doc-list-icon">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                </div>
                                <div class="doc-list-content">
                                    <div class="doc-list-title">{{ $doc->title ?: $doc->type_label }}</div>
                                    <div class="doc-list-meta">
                                        <span>{{ $doc->type_label }}</span>
                                        @if($doc->effective_date)
                                        <span class="doc-list-sep">•</span>
                                        <span>{{ $doc->effective_date->format('d/m/Y') }}</span>
                                        @if($doc->expired_date) — {{ $doc->expired_date->format('d/m/Y') }} @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="doc-list-actions">
                                    @if($doc->file_path)
                                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="icon-btn" title="Lihat">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                    @endif
                                    <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?');" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn-danger" title="Hapus">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-panel');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
                });
            });
        });
    </script>

    <style>
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --accent: #D4AF37;
            --accent-light: #E6C65C;
            --accent-dark: #B8962E;
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
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }

        .emp-page {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 100px;
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* Section Header (x-slot) */
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
            color: var(--gray-900);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--gray-500);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        /* Profile Card */
        .profile-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 12px;
        }
        .profile-card-accent {
            height: 48px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }
        .profile-card-body {
            padding: 0 16px 16px;
            margin-top: -24px;
        }
        .profile-identity {
            display: flex;
            align-items: flex-end;
            gap: 14px;
        }
        .profile-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--white);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .profile-status-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2.5px solid var(--white);
            position: absolute;
            bottom: 3px;
            right: 3px;
        }
        .profile-status-dot.active { background: var(--success); }
        .profile-status-dot.inactive { background: var(--error); }
        .profile-info {
            flex: 1;
            min-width: 0;
            padding-top: 28px;
        }
        .profile-name-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .profile-name {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0;
            color: var(--gray-900);
            letter-spacing: -0.01em;
        }
        .profile-badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }
        .badge-on { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .badge-off { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .profile-role {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin: 3px 0 6px;
            font-weight: 500;
        }
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.78rem;
            color: var(--gray-500);
        }
        .profile-meta-item svg { flex-shrink: 0; }
        .profile-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--gray-200);
        }
        .profile-leave-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            border: 1px solid rgba(20, 93, 160, 0.15);
        }
        .profile-leave-badge strong { font-weight: 700; }

        /* Quick Strip */
        .quick-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .quick-item {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 12px 14px;
            box-shadow: var(--shadow-sm);
        }
        .quick-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 3px;
        }
        .quick-value {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-900);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Tabs */
        .tab-bar {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 4px;
            box-shadow: var(--shadow-sm);
        }
        .tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            border: none;
            background: transparent;
            color: var(--gray-500);
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .tab-btn svg { flex-shrink: 0; }
        .tab-btn.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Summary Card (Masa Kerja) */
        .summary-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }
        .summary-card-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .summary-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }
        .summary-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .summary-value {
            display: block;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .summary-since {
            display: block;
            font-size: 0.78rem;
            color: var(--gray-500);
            margin-top: 2px;
        }
        .probation-wrap {
            flex: 1;
            min-width: 200px;
        }
        .probation-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 6px;
        }
        .probation-bar {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
        }
        .probation-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .probation-end {
            display: block;
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 4px;
            text-align: right;
        }
        .exit-wrap {
            text-align: right;
        }
        .exit-badge {
            display: inline-block;
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 4px;
        }
        .exit-reason {
            display: block;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: start;
        }
        .info-section {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 16px;
            box-shadow: var(--shadow-sm);
        }
        .info-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid var(--gray-100);
        }
        .info-section-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 93, 160, 0.08);
            border-radius: var(--radius-md);
            color: var(--primary);
            flex-shrink: 0;
        }
        .data-list {
            display: flex;
            flex-direction: column;
        }
        .data-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 8px 0;
            border-bottom: 1px solid var(--gray-100);
        }
        .data-item:first-child { padding-top: 0; }
        .data-item:last-child { border-bottom: none; padding-bottom: 0; }
        .data-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            line-height: 1.4;
        }
        .data-value {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray-900);
            line-height: 1.45;
            word-break: break-word;
        }
        .data-sub {
            font-size: 0.78rem;
            color: var(--gray-500);
            line-height: 1.4;
            margin-top: 1px;
        }
        .data-email { word-break: break-all; }
        .data-num {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }
        .doc-chips {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--gray-100);
        }
        .doc-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 6px 14px;
            background: var(--gray-50);
            color: var(--primary);
            border: 1px solid var(--gray-200);
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .doc-chip:hover {
            background: rgba(20, 93, 160, 0.08);
            border-color: rgba(20, 93, 160, 0.25);
        }

        /* Documents Grid */
        .docs-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        /* Card Component */
        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .card-header {
            padding: 14px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--gray-900);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doc-count {
            font-weight: 500;
            color: var(--gray-500);
            font-size: 0.8rem;
        }

        /* Upload Form */
        .upload-form {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--gray-500);
        }
        .required { color: var(--error); }
        .form-group input,
        .form-group select {
            padding: 9px 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            color: var(--gray-900);
            background: var(--white);
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        /* Buttons */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            font-family: inherit;
            width: 100%;
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--white);
            color: var(--gray-600);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
            color: var(--gray-900);
        }

        /* Empty State */
        .empty-state {
            padding: 40px 24px;
            text-align: center;
            color: var(--gray-500);
        }
        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 12px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
        }
        .empty-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-600);
            margin: 0 0 4px;
        }
        .empty-desc {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin: 0 auto;
            max-width: 260px;
            line-height: 1.5;
        }

        /* Document List */
        .doc-list {
            display: flex;
            flex-direction: column;
        }
        .doc-list-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--gray-200);
            transition: background 0.15s ease;
        }
        .doc-list-item:last-child { border-bottom: none; }
        .doc-list-item:hover { background: var(--gray-50); }
        .doc-list-icon {
            width: 36px;
            height: 36px;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            flex-shrink: 0;
        }
        .doc-list-content {
            flex: 1;
            min-width: 0;
        }
        .doc-list-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-900);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .doc-list-meta {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 2px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
        }
        .doc-list-sep { color: var(--gray-300); }
        .doc-list-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        .icon-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            border: none;
            background: transparent;
            color: var(--gray-500);
            cursor: pointer;
            transition: all 0.2s ease;
            vertical-align: middle;
            text-decoration: none;
            padding: 0;
        }
        .icon-btn:hover { background: var(--gray-100); color: var(--primary); }
        .icon-btn-danger:hover { background: rgba(239, 68, 68, 0.08); color: var(--error); }

        /* Top Actions */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
            cursor: pointer;
            font-family: inherit;
        }
        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }
        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }
        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 16px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            cursor: pointer;
            font-family: inherit;
            margin-left: auto;
        }
        .btn-edit:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .btn-edit svg { flex-shrink: 0; }

        /* ========================================== */
        /* TABLET & DESKTOP (768px+)                  */
        /* ========================================== */
        @media (min-width: 768px) {
            .emp-page { padding-bottom: 24px; }

            .profile-card-accent { height: 56px; }
            .profile-card-body {
                padding: 0 20px 20px;
                margin-top: -28px;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 16px;
            }
            .profile-identity { align-items: flex-end; }
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            .profile-info { padding-top: 32px; }
            .profile-name { font-size: 1.35rem; }
            .profile-badges {
                margin-top: 0;
                padding-top: 0;
                border-top: none;
                justify-content: flex-end;
            }

            .quick-strip { gap: 12px; }
            .quick-item { padding: 14px 16px; }
            .quick-value { font-size: 0.9rem; }

            .tab-bar { margin-bottom: 20px; }
            .tab-btn { padding: 10px 18px; font-size: 0.88rem; }

            .summary-card {
                padding: 20px;
                gap: 20px;
            }
            .summary-value { font-size: 1.5rem; }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
                align-items: start;
            }
            .info-section { padding: 20px; }

            .docs-grid {
                grid-template-columns: 300px 1fr;
                gap: 16px;
            }
            .upload-form { padding: 20px; }
            .card-header { padding: 14px 20px; }
            .doc-list-item { padding: 14px 20px; }
            .empty-state { padding: 48px 24px; }

            .page-header {
                margin-bottom: 20px;
            }
            .back-btn {
                height: 38px;
                padding: 0 14px 0 12px;
                font-size: 0.8rem;
            }
            .btn-edit {
                height: 38px;
                padding: 0 18px;
                font-size: 0.8rem;
            }
        }

        /* ========================================== */
        /* SMALL MOBILE (480px and below)             */
        /* ========================================== */
        @media (max-width: 480px) {
            .profile-card-body {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
                margin-top: -32px;
            }
            .profile-identity {
                flex-direction: row;
                align-items: flex-end;
                width: 100%;
            }
            .profile-info { padding-top: 32px; }
            .profile-name-row { flex-wrap: wrap; }
            .profile-badges { width: 100%; }

            .quick-strip { grid-template-columns: repeat(3, 1fr); }
            .quick-item { padding: 10px 12px; }
            .quick-value {
                font-size: 0.78rem;
                white-space: normal;
            }

            .tab-btn { padding: 8px 10px; font-size: 0.8rem; }
            .tab-btn svg { width: 14px; height: 14px; }

            .summary-card { flex-direction: column; align-items: flex-start; }
            .summary-value { font-size: 1.25rem; }
            .exit-wrap { text-align: left; width: 100%; }

            .info-section { padding: 14px; }
            .data-value { font-size: 0.82rem; }

            .form-row { grid-template-columns: 1fr; gap: 12px; }

            .doc-list-item {
                flex-wrap: wrap;
                gap: 10px;
            }
            .doc-list-content {
                min-width: 0;
                order: 1;
                flex: 1;
            }
            .doc-list-actions {
                order: 2;
                margin-left: auto;
            }
            .doc-list-title {
                white-space: normal;
                overflow: visible;
            }
        }

        /* ========================================== */
        /* EXTRA SMALL (360px and below)              */
        /* ========================================== */
        @media (max-width: 360px) {
            .quick-strip { grid-template-columns: 1fr; }
            .quick-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
            }
            .quick-label { margin-bottom: 0; }
            .quick-value { text-align: right; }

            .profile-name { font-size: 1.05rem; }
            .profile-avatar {
                width: 60px;
                height: 60px;
                font-size: 22px;
            }
        }
    </style>
</x-app>
