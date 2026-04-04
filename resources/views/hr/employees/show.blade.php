<x-app title="Detail Karyawan">

    <div class="emp-container">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flash flash-success">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Header Card --}}
        <div class="emp-header-card">
            <div class="emp-header-bg"></div>
            <div class="emp-header-body">
                <div class="emp-avatar-wrap">
                    <div class="emp-avatar">{{ substr($employee->name, 0, 1) }}</div>
                    <div class="emp-status-dot {{ $employee->status === 'ACTIVE' ? 'active' : 'inactive' }}"></div>
                </div>
                <div class="emp-header-info">
                    <div class="emp-name-row">
                        <h1 class="emp-name">{{ $employee->name }}</h1>
                        <span class="emp-status-badge {{ $employee->status === 'ACTIVE' ? 'badge-on' : 'badge-off' }}">
                            {{ $employee->status === 'ACTIVE' ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>
                    <p class="emp-role">{{ $employee->position?->name ?? ($profile?->jabatan ?? 'Tanpa Jabatan') }}</p>
                    <div class="emp-meta-row">
                        <span class="emp-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $employee->division->name ?? '-' }}
                        </span>
                        @if($profile && $profile->pt)
                        <span class="emp-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            {{ $profile->pt->name }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Info Strip --}}
        <div class="emp-quickstrip">
            <div class="emp-quickitem">
                <span class="emp-quicklabel">NIK</span>
                <span class="emp-quickvalue">{{ $profile?->nik ?? '-' }}</span>
            </div>
            <div class="emp-quickitem">
                <span class="emp-quicklabel">Kategori</span>
                <span class="emp-quickvalue">{{ $profile?->kategori ?? '-' }}</span>
            </div>
            <div class="emp-quickitem">
                <span class="emp-quicklabel">Bergabung</span>
                <span class="emp-quickvalue">{{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('d M Y') : '-' }}</span>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="emp-tabs">
            <button class="emp-tab-btn active" data-tab="overview">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Ringkasan
            </button>
            <button class="emp-tab-btn" data-tab="documents">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Dokumen
            </button>
        </div>

        {{-- Tab: Overview --}}
        <div id="tab-overview" class="emp-tab-content active">

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

            {{-- Masa Kerja Card --}}
            <div class="emp-work-card">
                <div class="emp-work-main">
                    <div class="emp-work-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="emp-work-info">
                        <span class="emp-work-label">Masa Kerja</span>
                        <span class="emp-work-value">{{ $masaKerjaDisplay }}</span>
                        <span class="emp-work-since">Sejak {{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('d M Y') : '-' }}</span>
                    </div>
                </div>
                @if($isProbation)
                <div class="emp-probation">
                    <div class="emp-prob-header">
                        <span>Masa Percobaan</span>
                        <span>{{ $probationPercent }}%</span>
                    </div>
                    <div class="emp-prob-bar">
                        <div class="emp-prob-fill" style="width: {{ $probationPercent }}%"></div>
                    </div>
                    <span class="emp-prob-end">Berakhir {{ $profile->tgl_akhir_percobaan->format('d M Y') }}</span>
                </div>
                @elseif($employee->status !== 'ACTIVE')
                <div class="emp-exit-info">
                    <span class="emp-exit-badge">Keluar {{ $profile?->exit_date ? $profile->exit_date->format('d M Y') : '-' }}</span>
                    <span class="emp-exit-reason">Alasan: {{ $profile?->exit_reason_code ?? '-' }}</span>
                </div>
                @endif
            </div>

            {{-- Info Grid --}}
            <div class="emp-info-grid">

                {{-- Col 1: Data Pribadi --}}
                <div class="emp-info-section">
                    <div class="emp-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Identitas Diri
                    </div>
                    <div class="emp-data-list">
                        <div class="emp-data-row">
                            <span class="emp-data-label">Tempat, Tgl Lahir</span>
                            <span class="emp-data-value">{{ $profile?->tempat_lahir ?? '-' }}{{ $profile?->tempat_lahir && $profile?->tgl_lahir ? ', ' : '' }}{{ $profile?->tgl_lahir ? $profile->tgl_lahir->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Jenis Kelamin</span>
                            <span class="emp-data-value">{{ $profile?->jenis_kelamin == 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Agama</span>
                            <span class="emp-data-value">{{ $profile?->agama ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Pendidikan</span>
                            <span class="emp-data-value">{{ $profile?->pendidikan ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Kewarganegaraan</span>
                            <span class="emp-data-value">{{ $profile?->kewarganegaraan ?? '-' }}</span>
                        </div>
                    </div>
                    @if($profile?->path_ktp || $profile?->path_kartu_keluarga)
                    <div class="emp-docs-btns">
                        @if($profile?->path_ktp)
                        <a href="{{ asset('storage/'.$profile->path_ktp) }}" target="_blank" class="emp-doc-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                            KTP
                        </a>
                        @endif
                        @if($profile?->path_kartu_keluarga)
                        <a href="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" target="_blank" class="emp-doc-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                            KK
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Col 2: Kontak & Domisili --}}
                <div class="emp-info-section">
                    <div class="emp-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Kontak & Domisili
                    </div>
                    <div class="emp-data-list">
                        <div class="emp-data-row">
                            <span class="emp-data-label">Email</span>
                            <span class="emp-data-value emp-email">{{ $profile?->email ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">No. HP</span>
                            <span class="emp-data-value">{{ $employee->phone ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row emp-data-full">
                            <span class="emp-data-label">Alamat</span>
                            <span class="emp-data-value">{{ $profile?->alamat1 ?? '-' }}</span>
                            @if($profile?->alamat2)<span class="emp-data-sub">{{ $profile->alamat2 }}</span>@endif
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Kota/Kab</span>
                            <span class="emp-data-value">{{ $profile?->kab_kota ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Kode Pos</span>
                            <span class="emp-data-value">{{ $profile?->kode_pos ?? '-' }}</span>
                        </div>
                        @if($profile?->alamat_sesuai_ktp)
                        <div class="emp-data-row emp-data-full">
                            <span class="emp-data-label">Alamat KTP</span>
                            <span class="emp-data-value">{{ $profile->alamat_sesuai_ktp }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Col 3: Payroll & BPJS --}}
                <div class="emp-info-section">
                    <div class="emp-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Payroll & BPJS
                    </div>
                    <div class="emp-data-list">
                        <div class="emp-data-row">
                            <span class="emp-data-label">Nama Bank</span>
                            <span class="emp-data-value">{{ $profile?->nama_bank ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">No. Rekening</span>
                            <span class="emp-data-value emp-mono">{{ $profile?->no_rekening ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">NPWP</span>
                            <span class="emp-data-value emp-mono">{{ $profile?->nomor_npwp ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">PTKP</span>
                            <span class="emp-data-value">{{ $profile?->ptkp ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">BPJS TK</span>
                            <span class="emp-data-value emp-mono">{{ $profile?->bpjs_tk ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">BPJS Kesehatan</span>
                            <span class="emp-data-value emp-mono">{{ $profile?->nomor_bpjs_kesehatan ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Kelas BPJS</span>
                            <span class="emp-data-value">{{ $profile?->kelas_bpjs ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Col 4: Lokasi Kerja --}}
                <div class="emp-info-section">
                    <div class="emp-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Lokasi Kerja
                    </div>
                    <div class="emp-data-list">
                        <div class="emp-data-row">
                            <span class="emp-data-label">Lokasi</span>
                            <span class="emp-data-value">{{ $profile?->lokasi_kerja ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Provinsi</span>
                            <span class="emp-data-value">{{ $profile?->provinsi ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Kecamatan</span>
                            <span class="emp-data-value">{{ $profile?->kecamatan ?? '-' }}</span>
                        </div>
                        <div class="emp-data-row">
                            <span class="emp-data-label">Desa/Kelurahan</span>
                            <span class="emp-data-value">{{ $profile?->desa_kelurahan ?? '-' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tab: Documents --}}
        <div id="tab-documents" class="emp-tab-content">
            <div class="emp-docs-layout">

                {{-- Upload Form --}}
                <div class="emp-upload-card">
                    <div class="emp-upload-header">Unggah Dokumen</div>
                    <form method="POST" action="{{ route('hr.employees.documents.store', $employee->id) }}" enctype="multipart/form-data" class="emp-upload-form">
                        @csrf
                        <div class="emp-form-group">
                            <label>Tipe Dokumen <span class="required">*</span></label>
                            <select name="type" required>
                                <option value="">-- Pilih --</option>
                                @foreach($documentTypes as $type)
                                <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="emp-form-group">
                            <label>Judul</label>
                            <input type="text" name="title" placeholder="Contoh: Kontrak Kerja 2026">
                        </div>
                        <div class="emp-form-row">
                            <div class="emp-form-group">
                                <label>Tgl. Efektif</label>
                                <input type="date" name="effective_date">
                            </div>
                            <div class="emp-form-group">
                                <label>Tgl. Berakhir</label>
                                <input type="date" name="expired_date">
                            </div>
                        </div>
                        <div class="emp-form-group">
                            <label>File <span class="required">*</span></label>
                            <input type="file" name="file" required>
                        </div>
                        <button type="submit" class="emp-btn emp-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Simpan
                        </button>
                    </form>
                </div>

                {{-- Document List --}}
                <div class="emp-doclist-card">
                    <div class="emp-doclist-header">
                        <span>Riwayat Dokumen</span>
                        <span class="emp-doc-count">{{ $documents->count() }} file</span>
                    </div>

                    @if($documents->isEmpty())
                    <div class="emp-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p>Belum ada dokumen.</p>
                    </div>
                    @else
                    <div class="emp-doc-table-wrap">
                        <table class="emp-doc-table">
                            <thead>
                                <tr>
                                    <th>Dokumen</th>
                                    <th>Berlaku</th>
                                    <th>Diunggah</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        <div class="emp-doc-info">
                                            <div class="emp-doc-icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                            </div>
                                            <div>
                                                <div class="emp-doc-title">{{ $doc->title ?: $doc->type_label }}</div>
                                                <div class="emp-doc-type">{{ $doc->type_label }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="emp-doc-date">
                                        @if($doc->effective_date)
                                        {{ $doc->effective_date->format('d/m/Y') }}
                                        @if($doc->expired_date) <span class="emp-doc-sep">—</span> {{ $doc->expired_date->format('d/m/Y') }}
                                        @endif
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="emp-doc-upload">{{ $doc->created_at->format('d M Y') }}</td>
                                    <td class="emp-doc-actions">
                                        @if($doc->file_path)
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="emp-icon-btn" title="Lihat">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                        @endif
                                        <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus?');" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="emp-icon-btn emp-icon-btn-danger" title="Hapus">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Bottom Action Bar (Mobile-First Thumb Zone) --}}
        <div class="emp-bottom-bar">
            <a href="{{ route('hr.employees.index') }}" class="emp-btn emp-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
            <a href="{{ route('hr.employees.edit', $employee->id) }}" class="emp-btn emp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Data
            </a>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.emp-tab-btn');
            const contents = document.querySelectorAll('.emp-tab-content');
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
        /* === BASE VARIABLES === */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success-bg: #f0fdf4;
            --success-text: #15803d;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --danger-border: #fecaca;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .emp-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 16px;
            padding-bottom: 100px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        /* === FLASH MESSAGES === */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === HEADER CARD === */
        .emp-header-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 12px;
        }
        .emp-header-bg {
            height: 72px;
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
        }
        .emp-header-body {
            display: flex;
            align-items: flex-end;
            gap: 16px;
            padding: 0 20px 20px;
            margin-top: -48px;
        }
        .emp-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .emp-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: var(--primary);
            font-size: 36px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .emp-status-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #fff;
            position: absolute;
            bottom: 4px;
            right: 4px;
        }
        .emp-status-dot.active { background: #22c55e; }
        .emp-status-dot.inactive { background: #ef4444; }
        .emp-header-info {
            flex: 1;
            min-width: 0;
            padding-top: 48px;
        }
        .emp-name-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .emp-name {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
        }
        .emp-status-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .badge-on { background: var(--success-bg); color: var(--success-text); }
        .badge-off { background: var(--danger-bg); color: var(--danger-text); }
        .emp-role {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 4px 0 8px;
            font-weight: 500;
        }
        .emp-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .emp-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .emp-meta-item svg { width: 14px; height: 14px; }

        /* === QUICK STRIP === */
        .emp-quickstrip {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        @media (min-width: 640px) {
            .emp-quickstrip { grid-template-columns: repeat(4, 1fr); }
        }
        .emp-quickitem {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }
        .emp-quicklabel {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        .emp-quickvalue {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* === TABS === */
        .emp-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 4px;
        }
        .emp-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
        }
        .emp-tab-btn svg { width: 16px; height: 16px; }
        .emp-tab-btn.active {
            background: var(--primary);
            color: #fff;
        }
        .emp-tab-content { display: none; }
        .emp-tab-content.active { display: block; }

        /* === WORK CARD === */
        .emp-work-card {
            background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .emp-work-main { display: flex; align-items: center; gap: 14px; }
        .emp-work-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .emp-work-icon svg { width: 24px; height: 24px; }
        .emp-work-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .emp-work-value { display: block; font-size: 1.5rem; font-weight: 700; color: var(--text-main); line-height: 1.2; }
        .emp-work-since { display: block; font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }
        .emp-probation { flex: 1; min-width: 200px; }
        .emp-prob-header { display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
        .emp-prob-bar { height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
        .emp-prob-fill { height: 100%; background: var(--primary); border-radius: 3px; transition: width 0.3s; }
        .emp-prob-end { display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; text-align: right; }
        .emp-exit-info { text-align: right; }
        .emp-exit-badge { display: inline-block; background: var(--danger-bg); color: var(--danger-text); font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; margin-bottom: 4px; }
        .emp-exit-reason { display: block; font-size: 0.75rem; color: var(--text-muted); }

        /* === INFO GRID === */
        .emp-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        @media (min-width: 640px) {
            .emp-info-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .emp-info-section {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 18px;
        }
        .emp-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--bg-body);
        }
        .emp-section-header svg { width: 16px; height: 16px; color: var(--primary); }
        .emp-data-list { display: flex; flex-direction: column; gap: 10px; }
        .emp-data-row { display: flex; flex-direction: column; gap: 1px; }
        .emp-data-label { font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .emp-data-value { font-size: 0.9rem; font-weight: 500; color: var(--text-main); }
        .emp-data-sub { font-size: 0.8rem; color: var(--text-muted); }
        .emp-data-full { }
        .emp-email { word-break: break-all; font-size: 0.85rem; }
        .emp-mono { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 0.85rem; background: var(--bg-body); padding: 2px 6px; border-radius: 4px; }
        .emp-docs-btns { display: flex; gap: 8px; margin-top: 12px; }
        .emp-doc-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 12px;
            background: var(--bg-body);
            color: var(--primary);
            border-radius: 20px;
            text-decoration: none;
            transition: 0.2s;
        }
        .emp-doc-btn:hover { background: #e0e7ff; }
        .emp-doc-btn svg { width: 12px; height: 12px; }

        /* === DOCUMENTS === */
        .emp-docs-layout { display: grid; grid-template-columns: 1fr; gap: 12px; }
        @media (min-width: 768px) {
            .emp-docs-layout { grid-template-columns: 280px 1fr; }
        }
        .emp-upload-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
        }
        .emp-upload-header {
            padding: 14px 18px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            border-bottom: 1px solid var(--border);
            background: #fafafa;
        }
        .emp-upload-form { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
        .emp-form-group { display: flex; flex-direction: column; gap: 4px; }
        .emp-form-group label { font-size: 0.8rem; font-weight: 500; color: var(--text-muted); }
        .emp-form-group .required { color: var(--danger-text); }
        .emp-form-group input, .emp-form-group select {
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            color: var(--text-main);
            background: #fff;
            transition: border-color 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .emp-form-group input:focus, .emp-form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        .emp-form-row { display: flex; flex-direction: column; gap: 8px; }
        .emp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .emp-btn svg { width: 16px; height: 16px; }
        .emp-btn-primary { background: var(--primary); color: #fff; }
        .emp-btn-primary:hover { background: var(--primary-dark); }
        .emp-btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .emp-btn-ghost:hover { background: var(--bg-body); color: var(--text-main); }

        .emp-doclist-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
        }
        .emp-doclist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            border-bottom: 1px solid var(--border);
            background: #fafafa;
        }
        .emp-doc-count { font-weight: 500; color: var(--text-muted); font-size: 0.8rem; }
        .emp-empty {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .emp-empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.4; }
        .emp-empty p { font-size: 0.9rem; margin: 0; }
        .emp-doc-table-wrap { overflow-x: auto; }
        .emp-doc-table { width: 100%; border-collapse: collapse; min-width: 500px; }
        .emp-doc-table th {
            text-align: left;
            padding: 10px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--bg-body);
            border-bottom: 1px solid var(--border);
        }
        .emp-doc-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .emp-doc-table tr:last-child td { border-bottom: none; }
        .emp-doc-info { display: flex; align-items: center; gap: 10px; }
        .emp-doc-icon {
            width: 36px;
            height: 36px;
            background: var(--bg-body);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            flex-shrink: 0;
        }
        .emp-doc-icon svg { width: 16px; height: 16px; }
        .emp-doc-title { font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
        .emp-doc-type { font-size: 0.75rem; color: var(--text-muted); }
        .emp-doc-date { font-size: 0.85rem; color: var(--text-main); white-space: nowrap; }
        .emp-doc-sep { color: var(--text-muted); margin: 0 4px; }
        .emp-doc-upload { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; }
        .emp-doc-actions { text-align: right; white-space: nowrap; }
        .emp-icon-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.2s;
            vertical-align: middle;
        }
        .emp-icon-btn:hover { background: var(--bg-body); color: var(--primary); }
        .emp-icon-btn-danger:hover { background: var(--danger-bg); color: var(--danger-text); }
        .emp-icon-btn svg { width: 15px; height: 15px; }

        /* === BOTTOM ACTION BAR === */
        .emp-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 12px 16px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            z-index: 50;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        }
        @media (min-width: 768px) {
            .emp-bottom-bar {
                position: static;
                background: transparent;
                border-top: none;
                padding: 0;
                margin-top: 16px;
                box-shadow: none;
                justify-content: flex-start;
            }
            .emp-container { padding-bottom: 16px; }
        }

        /* === MOBILE OPTIMIZATION === */
        @media (max-width: 480px) {
            .emp-header-body { flex-direction: column; align-items: center; text-align: center; margin-top: -56px; }
            .emp-header-info { padding-top: 56px; }
            .emp-name-row { justify-content: center; }
            .emp-meta-row { justify-content: center; }
            .emp-quickstrip { grid-template-columns: repeat(2, 1fr); }
            .emp-form-row { grid-template-columns: 1fr; }
            .emp-tab-btn { padding: 8px 12px; font-size: 0.8rem; }
            .emp-tab-btn svg { width: 14px; height: 14px; }
        }
    </style>
</x-app>