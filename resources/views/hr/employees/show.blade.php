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
        $fmtDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->translatedFormat('j M Y') : null;
        $isActive = $employee->status === 'ACTIVE';

        // Masa kerja (sampai tanggal keluar bila sudah nonaktif)
        $masaKerja = '-';
        if ($profile?->tgl_bergabung) {
            $start = \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay();
            $end = $profile->exit_date ? \Carbon\Carbon::parse($profile->exit_date)->startOfDay() : \Carbon\Carbon::today();
            if ($end->greaterThanOrEqualTo($start)) {
                $diff = $start->diff($end);
                $parts = [];
                if ($diff->y > 0) $parts[] = $diff->y . ' thn';
                if ($diff->m > 0) $parts[] = $diff->m . ' bln';
                $masaKerja = $parts ? implode(' ', $parts) : $diff->d . ' hari';
            }
        }

        // Akhir kontrak / percobaan
        $contractEnd = $profile?->tgl_akhir_percobaan ? \Carbon\Carbon::parse($profile->tgl_akhir_percobaan)->startOfDay() : null;
        $contractNote = null;
        $contractClass = '';
        if ($contractEnd && $isActive) {
            $daysLeft = (int) \Carbon\Carbon::today()->diffInDays($contractEnd, false);
            if ($daysLeft < 0) {
                $contractNote = 'lewat ' . abs($daysLeft) . ' hari';
                $contractClass = 'is-danger';
            } else {
                $contractNote = $daysLeft === 0 ? 'hari ini' : 'sisa ' . $daysLeft . ' hari';
                $contractClass = $daysLeft <= 30 ? 'is-warning' : '';
            }
        }

        $exitLabels = [
            'RESIGN' => 'Resign', 'HABIS_KONTRAK' => 'Habis Kontrak', 'PHK' => 'PHK',
            'PENSIUN' => 'Pensiun', 'MENINGGAL' => 'Meninggal', 'LAINNYA' => 'Lainnya',
        ];
        $exitCode = $profile?->exit_reason_code;
        $statusLabel = $isActive ? 'Aktif' : ($exitCode ? ($exitLabels[$exitCode] ?? $exitCode) : 'Nonaktif');

        $categoryLabels = ['TETAP' => 'Tetap', 'KONTRAK' => 'Kontrak', 'MAGANG' => 'Magang'];
        $categoryLabel = $categoryLabels[$profile?->kategori ?? ''] ?? ($profile?->kategori ?: '-');

        $leaveBalance = rtrim(rtrim(sprintf('%.1f', $employee->leave_balance ?? 0), '0'), '.');

        $birthPlace = $profile?->tempat_lahir;
        $birthDate = $fmtDate($profile?->tgl_lahir);
        $gender = ['L' => 'Laki-laki', 'P' => 'Perempuan'][$profile?->jenis_kelamin ?? ''] ?? null;

        // Data per seksi: label => nilai (null/kosong ditampilkan sebagai "-")
        $sections = [
            'Pekerjaan & Approval' => [
                'Jabatan' => $employee->position?->name ?? $profile?->jabatan,
                'Divisi' => $employee->division?->name,
                'PT' => $profile?->pt?->name,
                'Lokasi Kerja' => $profile?->lokasi_kerja,
                'Tgl Bergabung' => $fmtDate($profile?->tgl_bergabung),
                'Manager' => $employee->manager?->name,
                'Supervisor' => $employee->directSupervisor?->name,
                'Approver Izin/Cuti' => $employee->assignedApprover?->name,
            ],
            'Identitas Diri' => [
                'Tempat, Tgl Lahir' => implode(', ', array_filter([$birthPlace, $birthDate])),
                'Jenis Kelamin' => $gender,
                'Agama' => $profile?->agama,
                'Pendidikan' => $profile?->pendidikan,
                'Kewarganegaraan' => $profile?->kewarganegaraan,
            ],
            'Kontak & Alamat' => [
                'Email' => $profile?->email ?? $employee->email,
                'No. HP' => $employee->phone,
                'Alamat' => implode(' ', array_filter([$profile?->alamat1, $profile?->alamat2])),
                'Desa/Kelurahan' => $profile?->desa_kelurahan,
                'Kecamatan' => $profile?->kecamatan,
                'Kota/Kab' => $profile?->kab_kota,
                'Provinsi' => $profile?->provinsi,
                'Kode Pos' => $profile?->kode_pos,
                'Alamat KTP' => $profile?->alamat_sesuai_ktp,
            ],
            'Payroll & BPJS' => [
                'Bank' => $profile?->nama_bank,
                'No. Rekening' => $profile?->no_rekening,
                'NPWP' => $profile?->nomor_npwp,
                'PTKP' => $profile?->ptkp,
                'BPJS TK' => $profile?->bpjs_tk,
                'BPJS Kesehatan' => $profile?->nomor_bpjs_kesehatan,
                'Kelas BPJS' => $profile?->kelas_bpjs,
            ],
        ];
    @endphp

    <div class="ed-page">

        @if(session('success'))
        <div class="ed-alert ed-alert--success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="ed-alert ed-alert--error">{{ $errors->first() }}</div>
        @endif

        {{-- Aksi atas: kembali ke daftar dengan filter/halaman sebelumnya bila ada --}}
        @php
            $previousUrl = url()->previous();
            $backUrl = parse_url($previousUrl, PHP_URL_PATH) === parse_url(route('hr.employees.index'), PHP_URL_PATH)
                ? $previousUrl
                : route('hr.employees.index');
        @endphp
        <div class="ed-actions">
            <a href="{{ $backUrl }}" class="ed-btn ed-btn--ghost">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Daftar Karyawan
            </a>
            <a href="{{ route('hr.employees.edit', $employee->id) }}" class="ed-btn ed-btn--primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Data
            </a>
        </div>

        {{-- Ringkasan identitas + angka kunci --}}
        <section class="ed-hero">
            <div class="ed-hero-id">
                <div class="ed-name-row">
                    <h2 class="ed-name">{{ $employee->name }}</h2>
                    <span class="ed-badge {{ $isActive ? 'ed-badge--on' : 'ed-badge--off' }}">{{ $statusLabel }}</span>
                </div>
                <p class="ed-role">
                    {{ $employee->position?->name ?? ($profile?->jabatan ?? 'Tanpa Jabatan') }}
                    <span class="ed-dot">·</span> {{ $employee->division?->name ?? '-' }}
                    <span class="ed-dot">·</span> {{ $profile?->pt?->name ?? '-' }}
                </p>
                <p class="ed-nik">NIK <strong>{{ $profile?->nik ?? '-' }}</strong></p>
            </div>

            <div class="ed-stats">
                <div class="ed-stat">
                    <span class="ed-stat-label">Masa Kerja</span>
                    <span class="ed-stat-value">{{ $masaKerja }}</span>
                    <span class="ed-stat-sub">Sejak {{ $fmtDate($profile?->tgl_bergabung) ?? '-' }}</span>
                </div>
                <div class="ed-stat">
                    <span class="ed-stat-label">Sisa Cuti</span>
                    <span class="ed-stat-value ed-stat-value--info">{{ $leaveBalance }} <small>hari</small></span>
                </div>
                <div class="ed-stat">
                    <span class="ed-stat-label">Kategori</span>
                    <span class="ed-stat-value">{{ $categoryLabel }}</span>
                    @if($contractEnd)
                    <span class="ed-stat-sub {{ $contractClass }}">
                        Berakhir {{ $fmtDate($contractEnd) }}@if($contractNote) ({{ $contractNote }})@endif
                    </span>
                    @endif
                </div>
                @unless($isActive)
                <div class="ed-stat ed-stat--exit">
                    <span class="ed-stat-label">Keluar</span>
                    <span class="ed-stat-value">{{ $fmtDate($profile?->exit_date) ?? '-' }}</span>
                    <span class="ed-stat-sub">{{ $statusLabel }}</span>
                </div>
                @endunless
            </div>
        </section>

        {{-- Data detail --}}
        <div class="ed-grid">
            @foreach($sections as $title => $rows)
            <section class="ed-card">
                <h3 class="ed-card-title">{{ $title }}</h3>
                <dl class="ed-dl">
                    @foreach($rows as $label => $value)
                    <div class="ed-dl-row">
                        <dt>{{ $label }}</dt>
                        <dd class="{{ filled($value) ? '' : 'is-empty' }}">{{ filled($value) ? $value : '-' }}</dd>
                    </div>
                    @endforeach

                    @if($title === 'Identitas Diri')
                    <div class="ed-dl-row">
                        <dt>Berkas</dt>
                        <dd>
                            @if($profile?->path_ktp || $profile?->path_kartu_keluarga)
                                @if($profile?->path_ktp)
                                <button type="button" class="ed-chip" data-image-viewer-src="{{ asset('storage/'.$profile->path_ktp) }}" data-image-viewer-alt="KTP">Lihat KTP</button>
                                @endif
                                @if($profile?->path_kartu_keluarga)
                                <button type="button" class="ed-chip" data-image-viewer-src="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" data-image-viewer-alt="Kartu Keluarga">Lihat KK</button>
                                @endif
                            @else
                                <span class="is-empty">-</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>
            </section>
            @endforeach
        </div>

        {{-- Dokumen --}}
        <section class="ed-card ed-docs">
            <div class="ed-docs-head">
                <h3 class="ed-card-title">Dokumen <span class="ed-count">{{ $documents->count() }}</span></h3>
                <button type="button" class="ed-btn ed-btn--ghost ed-btn--sm" onclick="toggleDocUpload(this)" aria-controls="docUpload" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Unggah Dokumen
                </button>
            </div>

            <form method="POST" action="{{ route('hr.employees.documents.store', $employee->id) }}" enctype="multipart/form-data"
                  class="ed-upload {{ $errors->any() ? 'is-open' : '' }}" id="docUpload">
                @csrf
                <label class="ed-field">
                    <span>Tipe Dokumen <b class="ed-req">*</b></span>
                    <select name="type" required>
                        <option value="">-- Pilih --</option>
                        @foreach($documentTypes as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ed-field">
                    <span>Judul</span>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Kontrak Kerja 2026">
                </label>
                <label class="ed-field">
                    <span>Tgl. Efektif</span>
                    <input type="date" name="effective_date" value="{{ old('effective_date') }}">
                </label>
                <label class="ed-field">
                    <span>Tgl. Berakhir</span>
                    <input type="date" name="expired_date" value="{{ old('expired_date') }}">
                </label>
                <label class="ed-field ed-field--wide">
                    <span>File <b class="ed-req">*</b></span>
                    <input type="file" name="file" required>
                </label>
                <div class="ed-upload-actions">
                    <button type="submit" class="ed-btn ed-btn--primary">Simpan Dokumen</button>
                </div>
            </form>

            @if($documents->isEmpty())
            <p class="ed-empty">Belum ada dokumen.</p>
            @else
            <ul class="ed-doc-list">
                @foreach($documents as $doc)
                <li class="ed-doc">
                    <div class="ed-doc-main">
                        <span class="ed-doc-title">{{ $doc->title ?: $doc->type_label }}</span>
                        <span class="ed-doc-meta">
                            {{ $doc->type_label }}
                            @if($doc->effective_date)
                            · {{ $doc->effective_date->format('d/m/Y') }}@if($doc->expired_date) – {{ $doc->expired_date->format('d/m/Y') }}@endif
                            @endif
                        </span>
                    </div>
                    <div class="ed-doc-actions">
                        @if($doc->file_path)
                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" rel="noopener noreferrer" class="ed-btn ed-btn--ghost ed-btn--sm">Lihat</a>
                        @endif
                        <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="ed-btn ed-btn--danger ed-btn--sm">Hapus</button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </section>
    </div>

    <script>
        function toggleDocUpload(btn) {
            const form = document.getElementById('docUpload');
            const isOpen = form.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    </script>

    <style>
        /* ========== HEADER (x-slot) ========== */
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-icon svg { width: 16px; height: 16px; }
        .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1.25; }
        .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted); font-weight: 500; line-height: 1.35; }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        .ed-page { max-width: 1200px; margin: 0 auto; padding-bottom: 80px; }

        /* ========== ALERT ========== */
        .ed-alert { padding: 10px 14px; border-radius: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 500; }
        .ed-alert--success { background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); color: #16a34a; }
        .ed-alert--error { background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #dc2626; }

        /* ========== BUTTONS ========== */
        .ed-actions { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .ed-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            height: 36px; padding: 0 14px; box-sizing: border-box; border-radius: 10px;
            font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap;
            cursor: pointer; font-family: inherit; transition: all .15s ease;
        }
        .ed-btn--sm { height: 30px; padding: 0 10px; font-size: 12px; border-radius: 8px; }
        .ed-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: none;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .ed-btn--primary:hover { box-shadow: 0 4px 14px rgba(10, 61, 98, 0.3); }
        .ed-btn--ghost { background: var(--white); color: var(--text-secondary); border: 1.5px solid var(--border); }
        .ed-btn--ghost:hover { border-color: var(--primary); color: var(--primary); }
        .ed-btn--danger { background: var(--white); color: #b91c1c; border: 1.5px solid #fecaca; }
        .ed-btn--danger:hover { background: var(--danger-light); }

        /* ========== HERO ========== */
        .ed-hero {
            background: var(--white); border: 1px solid var(--border); border-radius: 12px;
            border-top: 4px solid var(--primary); padding: 16px; box-sizing: border-box;
            display: flex; flex-direction: column; gap: 14px; margin-bottom: 12px;
        }
        .ed-name-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .ed-name { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; }
        .ed-badge {
            display: inline-flex; padding: 3px 10px; border-radius: 9999px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
        }
        .ed-badge--on { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .ed-badge--off { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .ed-role { margin: 4px 0 0; font-size: 13px; font-weight: 500; color: var(--text-secondary); }
        .ed-dot { color: var(--text-light); margin: 0 2px; }
        .ed-nik { margin: 4px 0 0; font-size: 12px; color: var(--text-muted); }
        .ed-nik strong { color: var(--text-primary); font-weight: 700; font-variant-numeric: tabular-nums; }

        .ed-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .ed-stat {
            display: flex; flex-direction: column; gap: 2px; padding: 10px 12px;
            background: var(--gray-50); border-radius: 10px; min-width: 0;
        }
        .ed-stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-light); }
        .ed-stat-value { font-size: 15px; font-weight: 700; color: var(--text-primary); }
        .ed-stat-value small { font-size: 12px; font-weight: 500; color: var(--text-muted); }
        .ed-stat-value--info { color: var(--primary); }
        .ed-stat-sub { font-size: 11.5px; color: var(--text-muted); }
        .ed-stat-sub.is-warning { color: #a16207; font-weight: 600; }
        .ed-stat-sub.is-danger { color: #b91c1c; font-weight: 600; }
        .ed-stat--exit { background: var(--danger-light); }

        /* ========== DETAIL CARDS ========== */
        .ed-grid { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 12px; }
        .ed-card {
            background: var(--white); border: 1px solid var(--border); border-radius: 12px;
            padding: 14px 16px; box-sizing: border-box; min-width: 0;
        }
        .ed-card-title {
            margin: 0 0 8px; font-size: 13px; font-weight: 700; color: var(--primary-dark);
            display: flex; align-items: center; gap: 6px;
        }
        .ed-dl { margin: 0; }
        .ed-dl-row {
            display: grid; grid-template-columns: 130px minmax(0, 1fr); gap: 10px;
            padding: 7px 0; border-top: 1px solid var(--border-light);
        }
        .ed-dl-row:first-child { border-top: none; }
        .ed-dl dt { font-size: 12px; color: var(--text-muted); font-weight: 500; }
        .ed-dl dd { margin: 0; font-size: 13px; color: var(--text-primary); font-weight: 500; overflow-wrap: anywhere; display: flex; flex-wrap: wrap; gap: 6px; }
        .ed-dl dd.is-empty, .ed-dl .is-empty { color: var(--text-light); }
        .ed-chip {
            display: inline-flex; align-items: center; height: 26px; padding: 0 10px;
            border-radius: 8px; border: 1px solid rgba(20, 93, 160, 0.2); background: rgba(20, 93, 160, 0.06);
            color: var(--primary); font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .ed-chip:hover { background: var(--primary); color: #fff; }

        /* ========== DOKUMEN ========== */
        .ed-docs-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
        .ed-docs-head .ed-card-title { margin: 0; }
        .ed-count {
            font-size: 11px; font-weight: 700; padding: 1px 8px; border-radius: 9999px;
            background: var(--gray-50); color: var(--text-muted);
        }
        .ed-upload {
            display: none; grid-template-columns: 1fr; gap: 10px;
            margin: 10px 0 4px; padding: 12px; background: var(--gray-50); border-radius: 10px;
        }
        .ed-upload.is-open { display: grid; }
        .ed-field { display: flex; flex-direction: column; gap: 4px; font-size: 12px; font-weight: 600; color: var(--text-secondary); }
        .ed-field input, .ed-field select {
            height: 36px; box-sizing: border-box; padding: 0 10px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 13px; background: var(--white); font-family: inherit; color: var(--text-primary);
        }
        .ed-field input[type="file"] { padding: 6px 10px; }
        .ed-field input:focus, .ed-field select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1); }
        .ed-req { color: var(--error); }
        .ed-upload-actions { display: flex; justify-content: flex-end; }

        .ed-empty { margin: 8px 0 0; font-size: 13px; color: var(--text-muted); }
        .ed-doc-list { list-style: none; margin: 4px 0 0; padding: 0; }
        .ed-doc {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 9px 0; border-top: 1px solid var(--border-light);
        }
        .ed-doc:first-child { border-top: none; }
        .ed-doc-main { display: flex; flex-direction: column; min-width: 0; }
        .ed-doc-title { font-size: 13px; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ed-doc-meta { font-size: 11.5px; color: var(--text-muted); }
        .ed-doc-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .ed-doc-actions form { margin: 0; }

        /* ========== TABLET (640px+) ========== */
        @media (min-width: 640px) {
            .ed-stats { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
            .ed-upload { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ed-field--wide, .ed-upload-actions { grid-column: 1 / -1; }
        }

        /* ========== DESKTOP (1024px+) ========== */
        @media (min-width: 1024px) {
            .ed-hero { flex-direction: row; align-items: center; justify-content: space-between; padding: 18px 20px; }
            .ed-hero-id { min-width: 0; flex: 1 1 auto; }
            .ed-stats { flex: 0 0 auto; display: flex; gap: 8px; }
            .ed-stat { min-width: 130px; }
            .ed-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
            .ed-upload { grid-template-columns: 1.2fr 1.5fr 1fr 1fr; align-items: end; }
            .ed-field--wide { grid-column: 1 / 4; }
            .ed-upload-actions { grid-column: 4; }
        }
    </style>

</x-app>
