<x-app title="Detail Karyawan">

    <div class="main-container">

        @if(session('success'))
            <div class="alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="card profile-card">
            <div class="profile-content">
                <div class="avatar-circle">
                    {{ substr($employee->name, 0, 1) }}
                </div>
                <div class="profile-text">
                    <h1 class="profile-name">{{ $employee->name }}</h1>
                    <div class="profile-meta">
                        <span>{{ $employee->position?->name ?? ($profile?->jabatan ?? 'Tanpa Jabatan') }}</span>
                        @if($employee->division)
                            <span class="dot">•</span>
                            <span>{{ $employee->division->name }}</span>
                        @endif
                        @if($profile && $profile->pt)
                            <span class="dot">•</span>
                            <span>{{ $profile->pt->name }}</span>
                        @endif
                    </div>
                    <div class="profile-badges">
                        <span class="badge {{ $employee->status === 'ACTIVE' ? 'badge-success' : 'badge-danger' }}">
                            {{ $employee->status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span class="badge badge-neutral">{{ $employee->role }}</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-actions">
                <a href="{{ route('hr.employees.index') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
                <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Data
                </a>
            </div>
        </div>

        <div class="tabs-nav">
            <a href="#" data-tab-target="info" class="tab-link tab-active">Informasi Personal</a>
            <a href="#" data-tab-target="documents" class="tab-link">Dokumen Digital</a>
        </div>

        <div id="tab-info" class="tab-section section-active">
            
            <div class="grid-layout">
                <div class="card detail-card">
                    <div class="card-header-sm">Akun & Pekerjaan</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Username</label>
                            <div>{{ $employee->username ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Email Kerja</label>
                            <div>{{ $profile?->email ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Nomor HP</label>
                            <div>{{ $employee->phone }}</div>
                        </div>
                        <div class="info-item">
                            <label>Kategori Karyawan</label>
                            <div>{{ $profile?->kategori ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Lokasi Kerja</label>
                            <div>{{ $profile?->lokasi_kerja ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Badge ID</label>
                            <div>{{ $profile?->badge_id ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>PIN Absen</label>
                            <div>{{ $profile?->pin ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-header-sm">Data Pribadi</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>NIK (KTP)</label>
                            <div>{{ $profile?->nik ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Jenis Kelamin</label>
                            <div>
                                @if($profile?->jenis_kelamin == 'L') Laki-laki
                                @elseif($profile?->jenis_kelamin == 'P') Perempuan
                                @else -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Tempat, Tanggal Lahir</label>
                            <div>
                                {{ $profile?->tempat_lahir ?? '' }}
                                {{ $profile?->tgl_lahir ? ', ' . $profile->tgl_lahir->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label>Agama</label>
                            <div>{{ $profile?->agama ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Kewarganegaraan</label>
                            <div>{{ $profile?->kewarganegaraan ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Pendidikan Terakhir</label>
                            <div>{{ $profile?->pendidikan ?? '-' }}</div>
                        </div>
                        <div class="info-item full-width">
                            <label>File Identitas</label>
                            <div class="file-links">
                                @if($profile?->path_kartu_keluarga)
                                    <a href="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" target="_blank" class="file-chip">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Kartu Keluarga
                                    </a>
                                @endif
                                @if($profile?->path_ktp)
                                    <a href="{{ asset('storage/'.$profile->path_ktp) }}" target="_blank" class="file-chip">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        KTP
                                    </a>
                                @endif
                                @if(!$profile?->path_kartu_keluarga && !$profile?->path_ktp)
                                    <span class="text-muted">Tidak ada file.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-header-sm">Alamat Domisili</div>
                    <div class="info-grid">
                        <div class="info-item full-width">
                            <label>Alamat Lengkap</label>
                            <div>{{ $profile?->alamat1 ?? '-' }}</div>
                            @if($profile?->alamat2)
                                <div style="margin-top:4px;color:#6b7280;">{{ $profile->alamat2 }}</div>
                            @endif
                        </div>
                        <div class="info-item">
                            <label>Provinsi</label>
                            <div>{{ $profile?->provinsi ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Kabupaten / Kota</label>
                            <div>{{ $profile?->kab_kota ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Kecamatan</label>
                            <div>{{ $profile?->kecamatan ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Desa / Kelurahan</label>
                            <div>{{ $profile?->desa_kelurahan ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Kode Pos</label>
                            <div>{{ $profile?->kode_pos ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-header-sm">Keuangan & BPJS</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nama Bank</label>
                            <div>{{ $profile?->nama_bank ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>No. Rekening</label>
                            <div>{{ $profile?->no_rekening ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>NPWP</label>
                            <div>{{ $profile?->nomor_npwp ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>PTKP</label>
                            <div>{{ $profile?->ptkp ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>BPJS Ketenagakerjaan</label>
                            <div>{{ $profile?->bpjs_tk ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>BPJS Kesehatan</label>
                            <div>{{ $profile?->nomor_bpjs_kesehatan ?? '-' }} (Kelas {{ $profile?->kelas_bpjs ?? '-' }})</div>
                        </div>
                    </div>
                </div>

                <div class="card detail-card">
                    <div class="card-header-sm">Masa Kerja & Status</div>
                    @php
                        $masaKerjaDisplay = '-';
                        if ($profile && $profile->tgl_bergabung) {
                            $start = \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay();
                            $end = $profile->exit_date ? \Carbon\Carbon::parse($profile->exit_date)->startOfDay() : \Carbon\Carbon::today();
                            if ($end->greaterThanOrEqualTo($start)) {
                                $diff = $start->diff($end);
                                $parts = [];
                                if ($diff->y > 0) $parts[] = $diff->y . ' Thn';
                                if ($diff->m > 0) $parts[] = $diff->m . ' Bln';
                                if ($diff->y === 0 && $diff->m === 0 && $diff->d > 0) $parts[] = $diff->d . ' Hari';
                                $masaKerjaDisplay = empty($parts) ? '0 Hari' : implode(' ', $parts);
                            }
                        }
                    @endphp
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Tanggal Bergabung</label>
                            <div>{{ $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('d M Y') : '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Total Masa Kerja</label>
                            <div>{{ $masaKerjaDisplay }}</div>
                        </div>
                        <div class="info-item">
                            <label>Akhir Probation</label>
                            <div>{{ $profile?->tgl_akhir_percobaan ? $profile->tgl_akhir_percobaan->format('d M Y') : '-' }}</div>
                        </div>
                        @if($employee->status !== 'ACTIVE')
                            <div class="info-item full-width exit-box">
                                <label>Info Keluar</label>
                                <div>
                                    <span class="badge badge-danger">Keluar: {{ $profile?->exit_date ? $profile->exit_date->format('d M Y') : '-' }}</span>
                                    <div style="margin-top:4px;">Alasan: {{ $profile?->exit_reason_code ?? '-' }}</div>
                                    @if($profile?->exit_reason_note)
                                        <div style="font-size:0.85rem; color:#6b7280; font-style:italic;">"{{ $profile->exit_reason_note }}"</div>
                                    @endif
                                    @if($profile?->exit_document_path)
                                        <a href="{{ asset('storage/'.$profile->exit_document_path) }}" target="_blank" class="link-blue">Lihat Dokumen Exit</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div> </div>

        <div id="tab-documents" class="tab-section">
            
            <div class="card mb-4">
                <div class="card-header-sm">Upload Dokumen Baru</div>
                <form method="POST" action="{{ route('hr.employees.documents.store', $employee->id) }}" enctype="multipart/form-data" class="doc-form">
                    @csrf
                    <div class="doc-grid">
                        <div class="form-group">
                            <label>Jenis Dokumen</label>
                            <select name="type" class="form-control" required>
                                <option value="">Pilih Jenis...</option>
                                @foreach($documentTypes as $type)
                                    <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Judul (Opsional)</label>
                            <input type="text" name="title" class="form-control" placeholder="Misal: Kontrak 2025">
                        </div>
                        <div class="form-group">
                            <label>Tgl. Berlaku</label>
                            <input type="date" name="effective_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Tgl. Berakhir</label>
                            <input type="date" name="expired_date" class="form-control">
                        </div>
                        <div class="form-group full-width">
                            <label>File</label>
                            <input type="file" name="file" class="form-control-file" required>
                            <small>Format: PDF, JPG, PNG, DOCX. Maks 5MB.</small>
                        </div>
                        <div class="form-group full-width">
                            <label>Catatan</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Upload Dokumen</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header-sm">Daftar Dokumen</div>
                @if($documents->isEmpty())
                    <div class="empty-state">
                        <p>Belum ada dokumen yang diunggah.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Judul</th>
                                    <th>Periode</th>
                                    <th>Diunggah</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                    @php
                                        $fileUrl = $doc->file_path ? asset('storage/'.$doc->file_path) : null;
                                        $start = $doc->effective_date ? $doc->effective_date->format('d/m/Y') : null;
                                        $end = $doc->expired_date ? $doc->expired_date->format('d/m/Y') : null;
                                    @endphp
                                    <tr>
                                        <td><span class="badge badge-neutral">{{ $doc->type_label }}</span></td>
                                        <td>
                                            <div class="font-medium">{{ $doc->title ?? '-' }}</div>
                                            @if($doc->notes) <div class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($doc->notes, 30) }}</div> @endif
                                        </td>
                                        <td class="text-sm">
                                            @if($start && $end) {{ $start }} - {{ $end }}
                                            @elseif($start) Efektif: {{ $start }}
                                            @else - @endif
                                        </td>
                                        <td class="text-sm">
                                            <div>{{ $doc->creator?->name ?? 'Sistem' }}</div>
                                            <div class="text-xs text-muted">{{ $doc->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-group">
                                                @if($fileUrl)
                                                    <a href="{{ $fileUrl }}" target="_blank" class="btn-icon view" title="Lihat">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                    </a>
                                                @endif
                                                <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon delete" title="Hapus">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab-link');
            const sections = document.querySelectorAll('.tab-section');

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Remove active class
                    tabs.forEach(t => t.classList.remove('tab-active'));
                    sections.forEach(s => s.classList.remove('section-active'));

                    // Add active class
                    this.classList.add('tab-active');
                    const target = this.getAttribute('data-tab-target');
                    document.getElementById('tab-' + target).classList.add('section-active');
                });
            });
        });
    </script>

    <style>
        /* Container */
        .main-container { max-width: 900px; margin: 0 auto; padding-bottom: 40px; }

        /* Alerts */
        .alert-success, .alert-error { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Profile Header Card */
        .card { background: #fff; border-radius: 12px; border: 1px solid #f3f4f6; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 20px; }
        .profile-card { padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .profile-content { display: flex; align-items: center; gap: 16px; }
        .avatar-circle { width: 56px; height: 56px; background: #e0e7ff; color: #1e4a8d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; flex-shrink: 0; }
        .profile-name { margin: 0 0 4px 0; font-size: 20px; font-weight: 700; color: #111827; }
        .profile-meta { font-size: 14px; color: #6b7280; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .profile-badges { margin-top: 8px; display: flex; gap: 8px; }
        .dot { color: #d1d5db; }

        /* Buttons */
        .profile-actions { display: flex; gap: 10px; }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; font-weight: 500; text-decoration: none; transition: 0.2s; }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }
        .btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: none; background: #1e4a8d; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-primary:hover { background: #163a75; }

        /* Tabs */
        .tabs-nav { display: flex; gap: 20px; border-bottom: 1px solid #e5e7eb; margin-bottom: 20px; padding: 0 4px; overflow-x: auto; }
        .tab-link { padding: 10px 4px; text-decoration: none; color: #6b7280; font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent; transition: 0.2s; white-space: nowrap; }
        .tab-link:hover { color: #374151; }
        .tab-active { color: #1e4a8d; border-bottom-color: #1e4a8d; font-weight: 600; }
        .tab-section { display: none; }
        .section-active { display: block; }

        /* Detail Info Grid */
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-card { padding: 20px; }
        .card-header-sm { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-item label { display: block; font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.02em; }
        .info-item div { font-size: 14px; color: #1f2937; font-weight: 500; line-height: 1.4; }
        .full-width { grid-column: 1 / -1; }
        
        .file-links { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
        .file-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f0f9ff; color: #0284c7; border-radius: 20px; font-size: 12px; font-weight: 500; text-decoration: none; border: 1px solid #bae6fd; transition: 0.2s; }
        .file-chip:hover { background: #e0f2fe; }
        
        .exit-box { background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px dashed #fecaca; }
        .link-blue { color: #1d4ed8; text-decoration: underline; font-size: 13px; }

        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-neutral { background: #f3f4f6; color: #374151; }

        /* Documents Form */
        .doc-form { padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; }
        .doc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .form-control-file { font-size: 13px; }
        
        /* Table */
        .table-responsive { overflow-x: auto; }
        .modern-table { width: 100%; border-collapse: collapse; min-width: 600px; font-size: 14px; }
        .modern-table th { text-align: left; padding: 12px 16px; background: #f9fafb; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .modern-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .text-right { text-align: right; }
        .text-xs { font-size: 12px; }
        .text-muted { color: #6b7280; }
        .text-sm { font-size: 13px; }
        .font-medium { font-weight: 500; color: #111827; }
        
        .action-group { display: flex; gap: 6px; justify-content: flex-end; }
        .btn-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid transparent; cursor: pointer; transition: 0.2s; background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-icon.view:hover { color: #1e4a8d; border-color: #1e4a8d; background: #f0f9ff; }
        .btn-icon.delete:hover { color: #dc2626; border-color: #dc2626; background: #fef2f2; }

        /* Mobile */
        @media (max-width: 768px) {
            .profile-card { flex-direction: column; text-align: center; }
            .profile-content { flex-direction: column; }
            .profile-meta { justify-content: center; }
            .profile-badges { justify-content: center; }
            .profile-actions { width: 100%; justify-content: center; }
            .btn-back, .btn-primary { flex: 1; justify-content: center; }
            
            .grid-layout { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; gap: 12px; }
            .doc-grid { grid-template-columns: 1fr; }
        }
    </style>
</x-app>