<x-app title="Detail Karyawan - {{ $employee->name }}">

    <div class="main-container">

        @if(session('success'))
        <div class="alert alert-success">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <div class="card profile-header">
            <div class="profile-body">
                <div class="profile-main">
                    <div class="avatar-wrapper">
                        <div class="avatar-lg">{{ substr($employee->name, 0, 1) }}</div>
                        <div class="status-indicator {{ $employee->status === 'ACTIVE' ? 'bg-success' : 'bg-danger' }}"></div>
                    </div>
                    <div class="profile-identity">
                        <div class="d-flex align-center gap-2">
                            <h1 class="text-xl font-bold">{{ $employee->name }}</h1>
                            <span class="badge {{ $employee->status === 'ACTIVE' ? 'badge-success' : 'badge-danger' }}">
                                {{ $employee->status === 'ACTIVE' ? 'Active' : 'Non-Active' }}
                            </span>
                        </div>
                        <div class="text-muted text-sm mt-1">
                            {{ $employee->position?->name ?? ($profile?->jabatan ?? 'Tanpa Jabatan') }}
                            <span class="mx-1">•</span>
                            {{ $employee->division->name ?? '-' }}
                            @if($profile && $profile->pt)
                            <span class="mx-1">•</span> {{ $profile->pt->name }}
                            @endif
                        </div>

                        <div class="quick-stats mt-3">
                            <div class="stat-pill">
                                <span class="label">ID:</span>
                                <span class="val">{{ $profile?->badge_id ?? $employee->username }}</span>
                            </div>
                            <div class="stat-pill">
                                <span class="label">Tipe:</span>
                                <span class="val">{{ $profile?->kategori ?? 'Tetap' }}</span>
                            </div>
                            <div class="stat-pill">
                                <span class="label">Join:</span>
                                <span class="val">{{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('hr.employees.index') }}" class="btn btn-default">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5" />
                            <path d="M12 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                    <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn btn-primary">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit Data
                    </a>
                </div>
            </div>
        </div>

        <div class="tabs-nav">
            <button data-tab-target="overview" class="tab-link tab-active">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <line x1="3" y1="9" x2="21" y2="9" />
                    <line x1="9" y1="21" x2="9" y2="9" />
                </svg>
                Overview & Personal
            </button>
            <button data-tab-target="documents" class="tab-link">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                Arsip Dokumen
            </button>
        </div>

        <div id="tab-overview" class="tab-section section-active">

            <div class="card unified-card">
                <div class="card-body p-0">
                    <div class="unified-grid">

                        <div class="grid-col left-col">

                            <div class="info-group">
                                <div class="inner-header">
                                    <svg class="icon-sm text-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>Status Kepegawaian</span>
                                </div>

                                @php
                                // Logic Masa Kerja
                                $masaKerjaDisplay = '-';
                                if ($profile && $profile->tgl_bergabung) {
                                $start = \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay();
                                $end = $profile->exit_date ? \Carbon\Carbon::parse($profile->exit_date)->startOfDay() : \Carbon\Carbon::today();
                                if ($end->greaterThanOrEqualTo($start)) {
                                $diff = $start->diff($end);
                                $masaKerjaDisplay = ($diff->y > 0 ? $diff->y . ' Thn ' : '') . ($diff->m > 0 ? $diff->m . ' Bln' : '');
                                if(empty($masaKerjaDisplay)) $masaKerjaDisplay = $diff->d . ' Hari';
                                }
                                }
                                // Logic Probation
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

                                <div class="highlight-box mb-4">
                                    <div class="text-xs text-muted uppercase tracking-wide">Total Masa Kerja</div>
                                    <div class="text-2xl font-bold text-dark mt-1">{{ $masaKerjaDisplay }}</div>
                                    <div class="text-sm text-muted mt-1">Sejak {{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('d M Y') : '-' }}</div>

                                    @if($isProbation)
                                    <div class="mt-3 pt-3 border-top-dashed">
                                        <div class="d-flex justify-between text-xs font-semibold mb-1">
                                            <span>Probation Progress</span>
                                            <span>{{ $probationPercent }}%</span>
                                        </div>
                                        <div class="progress-bar-bg">
                                            <div class="progress-bar-fill" style="width: {{ $probationPercent }}%"></div>
                                        </div>
                                        <div class="text-xs text-muted mt-1 text-right">Berakhir: {{ $profile->tgl_akhir_percobaan->format('d M Y') }}</div>
                                    </div>
                                    @elseif($employee->status !== 'ACTIVE')
                                    <div class="mt-3 pt-3 border-top-dashed">
                                        <span class="badge badge-danger">Keluar: {{ $profile?->exit_date ? $profile->exit_date->format('d M Y') : '-' }}</span>
                                        <div class="text-xs text-muted mt-1">Alasan: {{ $profile?->exit_reason_code ?? '-' }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="info-group">
                                <div class="inner-header">
                                    <svg class="icon-sm text-purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span>Data Pribadi</span>
                                </div>
                                <div class="info-list">
                                    <div class="info-row">
                                        <label>NIK (KTP)</label>
                                        <div>{{ $profile?->nik ?? '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Tempat, Tgl Lahir</label>
                                        <div>{{ $profile?->tempat_lahir ?? '' }}{{ $profile?->tempat_lahir && $profile?->tgl_lahir ? ', ' : '' }}{{ $profile?->tgl_lahir ? $profile->tgl_lahir->format('d M Y') : '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Jenis Kelamin</label>
                                        <div>{{ $profile?->jenis_kelamin == 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Agama</label>
                                        <div>{{ $profile?->agama ?? '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Pendidikan Terakhir</label>
                                        <div>{{ $profile?->pendidikan ?? '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Dokumen Identitas</label>
                                        <div class="d-flex gap-2 mt-1">
                                            @if($profile?->path_ktp)
                                            <a href="{{ asset('storage/'.$profile->path_ktp) }}" target="_blank" class="chip">Lihat KTP</a>
                                            @endif
                                            @if($profile?->path_kartu_keluarga)
                                            <a href="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" target="_blank" class="chip">Lihat KK</a>
                                            @endif
                                            @if(!$profile?->path_ktp && !$profile?->path_kartu_keluarga) <span class="text-muted text-sm">-</span> @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="grid-col right-col">

                            <div class="info-group">
                                <div class="inner-header">
                                    <svg class="icon-sm text-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span>Kontak & Domisili</span>
                                </div>
                                <div class="info-list">
                                    <div class="info-row">
                                        <label>Email Pribadi</label>
                                        <div class="text-truncate" title="{{ $profile?->email }}">{{ $profile?->email ?? '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>No. Handphone</label>
                                        <div>{{ $employee->phone }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>Alamat Lengkap</label>
                                        <div>
                                            {{ $profile?->alamat1 ?? '-' }}
                                            @if($profile?->alamat2) <br><span class="text-muted text-sm">{{ $profile->alamat2 }}</span> @endif
                                        </div>
                                    </div>
                                    <div class="form-row mt-1">
                                        <div class="info-row">
                                            <label>Kota/Kab</label>
                                            <div>{{ $profile?->kab_kota ?? '-' }}</div>
                                        </div>
                                        <div class="info-row">
                                            <label>Kode Pos</label>
                                            <div>{{ $profile?->kode_pos ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="separator-dashed"></div>

                            <div class="info-group">
                                <div class="inner-header">
                                    <svg class="icon-sm text-orange" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                                        <line x1="1" y1="10" x2="23" y2="10" />
                                    </svg>
                                    <span>Payroll & BPJS</span>
                                </div>
                                <div class="info-list">
                                    <div class="d-flex justify-between gap-4">
                                        <div class="info-row flex-1">
                                            <label>Nama Bank</label>
                                            <div>{{ $profile?->nama_bank ?? '-' }}</div>
                                        </div>
                                        <div class="info-row flex-1">
                                            <label>No. Rekening</label>
                                            <div class="font-mono">{{ $profile?->no_rekening ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <label>NPWP</label>
                                        <div>{{ $profile?->nomor_npwp ?? '-' }} <span class="text-muted text-xs">({{ $profile?->ptkp ?? '-' }})</span></div>
                                    </div>
                                    <div class="info-row">
                                        <label>BPJS Ketenagakerjaan</label>
                                        <div class="font-mono">{{ $profile?->bpjs_tk ?? '-' }}</div>
                                    </div>
                                    <div class="info-row">
                                        <label>BPJS Kesehatan</label>
                                        <div class="font-mono">{{ $profile?->nomor_bpjs_kesehatan ?? '-' }} (Kelas {{ $profile?->kelas_bpjs ?? '-' }})</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div id="tab-documents" class="tab-section">
            <div class="grid-layout-doc">
                <div class="card">
                    <div class="card-header-sm">Upload File Baru</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('hr.employees.documents.store', $employee->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-stack">
                                <div class="form-group">
                                    <label>Tipe Dokumen <span class="text-danger">*</span></label>
                                    <select name="type" class="form-input" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach($documentTypes as $type)
                                        <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Judul File</label>
                                    <input type="text" name="title" class="form-input" placeholder="Contoh: Kontrak Kerja 2026">
                                </div>

                                <div class="form-group">
                                    <label>Tgl. Efektif</label>
                                    <input type="date" name="effective_date" class="form-input">
                                </div>

                                <div class="form-group">
                                    <label>Tgl. Berakhir</label>
                                    <input type="date" name="expired_date" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label>File Upload</label>
                                    <input type="file" name="file" class="form-input-file" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-2">Simpan Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card doc-list-container">
                    <div class="card-header-sm d-flex justify-between">
                        <span>Riwayat Dokumen</span>
                        <span class="badge badge-neutral">{{ $documents->count() }} File</span>
                    </div>

                    @if($documents->isEmpty())
                    <div class="empty-state">
                        <svg class="icon-lg text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <p>Belum ada dokumen tersimpan.</p>
                    </div>
                    @else
                    <div class="table-container">
                        <table class="table-clean">
                            <thead>
                                <tr>
                                    <th>Dokumen</th>
                                    <th>Masa Berlaku</th>
                                    <th>Diunggah</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-center gap-2">
                                            <div class="icon-file">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                                    <polyline points="13 2 13 9 20 9" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-dark">{{ $doc->title ?: $doc->type_label }}</div>
                                                <div class="text-xs text-muted">{{ $doc->type_label }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm">
                                        @if($doc->effective_date)
                                        {{ $doc->effective_date->format('d/m/y') }}
                                        @if($doc->expired_date) <br> s/d {{ $doc->expired_date->format('d/m/y') }} @endif
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="text-xs text-muted">
                                        {{ $doc->created_at->format('d M Y') }}
                                    </td>
                                    <td class="text-right">
                                        <div class="action-buttons">
                                            @if($doc->file_path)
                                            <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn-icon text-blue" title="Lihat">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                                    <polyline points="15 3 21 3 21 9" />
                                                    <line x1="10" y1="14" x2="21" y2="3" />
                                                </svg>
                                            </a>
                                            @endif
                                            <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus file?');" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button class="btn-icon text-red" title="Hapus">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6" />
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
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

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-link');
            const sections = document.querySelectorAll('.tab-section');

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    tabs.forEach(t => t.classList.remove('tab-active'));
                    sections.forEach(s => s.classList.remove('section-active'));

                    tab.classList.add('tab-active');
                    const targetId = tab.getAttribute('data-tab-target');
                    document.getElementById('tab-' + targetId).classList.add('section-active');
                });
            });
        });
    </script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius-md: 12px;
            --radius-sm: 8px;

            /* Accents */
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --green-light: #f0fdf4;
            --green-text: #15803d;
            --purple-light: #faf5ff;
            --purple-text: #7e22ce;
            --orange-light: #fff7ed;
            --orange-text: #c2410c;
            --red-light: #fef2f2;
            --red-text: #b91c1c;
        }

        /* Base */
        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 60px;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-main);
        }

        .d-flex {
            display: flex;
        }

        .align-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-2 {
            gap: 8px;
        }

        .gap-4 {
            gap: 16px;
        }

        .mt-1 {
            margin-top: 4px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mx-1 {
            margin: 0 4px;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-muted {
            color: var(--text-muted);
        }

        .text-dark {
            color: var(--text-main);
        }

        .text-danger {
            color: var(--red-text);
        }

        .uppercase {
            text-transform: uppercase;
        }

        .tracking-wide {
            letter-spacing: 0.05em;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 200px;
        }

        .font-mono {
            font-family: monospace;
            letter-spacing: -0.5px;
            background: #f8fafc;
            padding: 2px 6px;
            border-radius: 4px;
            color: #334155;
        }

        .flex-1 {
            flex: 1;
        }

        /* Icons & Utilities */
        .icon-sm {
            width: 18px;
            height: 18px;
        }

        .icon-lg {
            width: 48px;
            height: 48px;
        }

        .text-blue {
            color: var(--primary);
        }

        .text-purple {
            color: #9333ea;
        }

        .text-green {
            color: #16a34a;
        }

        .text-orange {
            color: #ea580c;
        }

        .p-0 {
            padding: 0 !important;
        }

        /* Alerts & Buttons */
        .alert {
            display: flex;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 0.9rem;
            align-items: center;
        }

        .alert-success {
            background: var(--green-light);
            color: var(--green-text);
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: var(--red-light);
            color: var(--red-text);
            border: 1px solid #fecaca;
        }

        .btn {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-default {
            background: #fff;
            border-color: var(--border);
            color: var(--secondary);
        }

        .btn-default:hover {
            background: #f8fafc;
            color: var(--text-main);
            border-color: #cbd5e1;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Profile Header */
        .profile-header {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .profile-cover {
            height: 120px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
        }

        .profile-body {
            padding: 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
        }

        .profile-main {
            display: flex;
            gap: 24px;
            align-items: flex-end;
            margin-top: -40px;
        }

        .avatar-lg {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #e0e7ff;
            color: var(--primary);
            font-size: 40px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid #fff;
            position: absolute;
            bottom: 0;
            right: 0;
            z-index: 2;
            transform: translate(50%, -20%);
            margin-bottom: 15px;
            margin-right: 15px;
        }

        /* Adjusted position relative to wrapper */
        .avatar-wrapper {
            position: relative;
        }

        .bg-success {
            background: #22c55e;
        }

        .bg-danger {
            background: #ef4444;
        }

        .quick-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stat-pill {
            background: #f8fafc;
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: flex;
            gap: 6px;
        }

        .stat-pill .label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .stat-pill .val {
            color: var(--text-main);
            font-weight: 600;
        }

        /* Tabs */
        .tabs-nav {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .tab-link {
            background: none;
            border: none;
            padding: 12px 0;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .tab-link.tab-active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-section {
            display: none;
        }

        .section-active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* UNIFIED CARD STYLES */
        .unified-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .unified-grid {
            display: grid;
            grid-template-columns: 1fr;
        }

        .grid-col {
            padding: 30px;
        }

        @media (min-width: 900px) {
            .unified-grid {
                grid-template-columns: 1fr 1fr;
            }

            .left-col {
                border-right: 1px solid #f1f5f9;
            }
        }

        .info-group {
            margin-bottom: 32px;
        }

        .info-group:last-child {
            margin-bottom: 0;
        }

        .inner-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8fafc;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-row label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .info-row div {
            font-size: 0.9rem;
            color: var(--text-main);
            font-weight: 500;
        }

        .highlight-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .border-top-dashed {
            border-top: 1px dashed var(--border);
        }

        .separator-dashed {
            height: 1px;
            background-image: linear-gradient(to right, #e2e8f0 50%, rgba(255, 255, 255, 0) 0%);
            background-position: bottom;
            background-size: 8px 1px;
            background-repeat: repeat-x;
            margin: 24px 0;
        }

        /* Progress Bar */
        .progress-bar-bg {
            height: 6px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
        }

        /* Chips & Badges */
        .chip {
            display: inline-flex;
            font-size: 0.75rem;
            padding: 4px 10px;
            background: var(--blue-light);
            color: var(--blue-text);
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .chip:hover {
            background: #dbeafe;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: var(--green-light);
            color: var(--green-text);
            border: 1px solid #bbf7d0;
        }

        .badge-danger {
            background: var(--red-light);
            color: var(--red-text);
            border: 1px solid #fecaca;
        }

        .badge-neutral {
            background: #f1f5f9;
            color: #64748b;
        }

        /* Documents */
        .grid-layout-doc {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media(min-width: 768px) {
            .grid-layout-doc {
                grid-template-columns: 300px 1fr;
            }
        }

        .card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }

        .card-header-sm {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            background: #fcfcfc;
        }

        .card-body {
            padding: 20px;
        }

        .form-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 4px;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .table-container {
            overflow-x: auto;
        }

        .table-clean {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .table-clean th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .table-clean td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table-clean tr:last-child td {
            border-bottom: none;
        }

        .icon-file {
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-icon:hover {
            background: #f1f5f9;
        }

        .text-red {
            color: #ef4444;
        }

        .text-right {
            text-align: right;
        }

        @media (max-width: 768px) {
            .profile-main {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-top: -50px;
            }

            .profile-body {
                justify-content: center;
            }

            .grid-layout-doc {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-app>