<x-app title="Detail Karyawan">

    <style>
        .employee-tab-link {
            padding: 8px 10px;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            color: #4b5563;
            font-size: .9rem;
            cursor: pointer;
        }
        .employee-tab-link-active {
            border-bottom-color: #1e4a8d;
            color: #1e4a8d;
            font-weight: 600;
        }
        .employee-tab-section {
            display: none;
        }
        .employee-tab-section-active {
            display: block;
        }
    </style>

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:1.05rem;font-weight:600;text-transform:uppercase;">
                {{ $employee->name }}
            </div>
            <div style="font-size:.85rem;opacity:.8;margin-top:2px;text-transform:uppercase;">
                {{ $employee->position?->name ?? ($profile?->jabatan ?? '-') }}
                @if($employee->division)
                    • {{ $employee->division->name }}
                @endif
                @if($profile && $profile->pt)
                    • {{ $profile->pt->name }}
                @endif
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:.8rem;padding:4px 10px;border-radius:999px;
                {{ $employee->status === 'ACTIVE'
                    ? 'background:#e0f9ec;color:#047857;'
                    : 'background:#fee2e2;color:#b91c1c;' }}">
                {{ $employee->status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}
            </span>

            <a href="{{ route('hr.employees.edit', $employee->id) }}"
               style="font-size:.85rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;white-space:nowrap;background:#fff;">
                Edit Data
            </a>

            <a href="{{ route('hr.employees.index') }}"
               style="font-size:.85rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;white-space:nowrap;background:#fff;">
                ← Kembali ke daftar
            </a>
        </div>
    </div>

    <div style="margin-bottom:14px;border-bottom:1px solid #e5e7eb;">
        <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:.9rem;">
            <a href="#"
               data-tab-target="info"
               class="employee-tab-link employee-tab-link-active">
                Informasi
            </a>
            <a href="#"
               data-tab-target="documents"
               class="employee-tab-link">
                Dokumen
            </a>
        </div>
    </div>

    <div id="tab-info" class="employee-tab-section employee-tab-section-active">

        <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:18px;">

            <div class="card" style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:.9rem;font-weight:600;">
                    Akun & Pekerjaan
                </div>

                <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px 16px;font-size:.85rem;">
                    <div>
                        <div style="opacity:.7;">Nama Lengkap</div>
                        <div style="font-weight:500;">{{ $employee->name }}</div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Username</div>
                        <div style="font-weight:500;">{{ $employee->username ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Nomor HP</div>
                        <div style="font-weight:500;">{{ $employee->phone }}</div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Role Sistem</div>
                        <div style="font-weight:500;">{{ $employee->role ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Perusahaan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->pt?->name ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Divisi</div>
                        <div style="font-weight:500;">
                            {{ $employee->division?->name ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Jabatan</div>
                        <div style="font-weight:500;">
                            {{ $employee->position?->name ?? ($profile?->jabatan ?? '-') }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kategori Karyawan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kategori ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Lokasi Kerja</div>
                        <div style="font-weight:500;">
                            {{ $profile?->lokasi_kerja ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Email Kerja</div>
                        <div style="font-weight:500;">
                            {{ $profile?->email ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Badge ID</div>
                        <div style="font-weight:500;">
                            {{ $profile?->badge_id ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">PIN</div>
                        <div style="font-weight:500;">
                            {{ $profile?->pin ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:.9rem;font-weight:600;">
                    Data Pribadi
                </div>

                <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px 16px;font-size:.85rem;">
                    <div>
                        <div style="opacity:.7;">NIK</div>
                        <div style="font-weight:500;">
                            {{ $profile?->nik ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Jenis Kelamin</div>
                        <div style="font-weight:500;">
                            {{ $profile?->jenis_kelamin ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Tempat Lahir</div>
                        <div style="font-weight:500;">
                            {{ $profile?->tempat_lahir ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Tanggal Lahir</div>
                        <div style="font-weight:500;">
                            @if($profile?->tgl_lahir)
                                {{ $profile->tgl_lahir->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Golongan Darah</div>
                        <div style="font-weight:500;">
                            {{ $profile?->golongan_darah ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kewarganegaraan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kewarganegaraan ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Agama</div>
                        <div style="font-weight:500;">
                            {{ $profile?->agama ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Pendidikan Terakhir</div>
                        <div style="font-weight:500;">
                            {{ $profile?->pendidikan ?? '-' }}
                        </div>
                    </div>
                </div>

                <div style="margin-top:10px;border-top:1px dashed #e5e7eb;padding-top:8px;font-size:.85rem;">
                    <div style="opacity:.7;margin-bottom:4px;">Dokumen Identitas</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <div>
                            KK:
                            @if($profile?->path_kartu_keluarga)
                                <a href="{{ asset('storage/'.$profile->path_kartu_keluarga) }}" target="_blank" style="color:#1d4ed8;text-decoration:underline;font-size:.85rem;">
                                    Lihat
                                </a>
                            @else
                                <span style="opacity:.6;">Belum diunggah</span>
                            @endif
                        </div>
                        <div>
                            KTP:
                            @if($profile?->path_ktp)
                                <a href="{{ asset('storage/'.$profile->path_ktp) }}" target="_blank" style="color:#1d4ed8;text-decoration:underline;font-size:.85rem;">
                                    Lihat
                                </a>
                            @else
                                <span style="opacity:.6;">Belum diunggah</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
                <div style="font-size:.9rem;font-weight:600;">
                    Alamat & Kepesertaan
                </div>

                <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px 16px;font-size:.85rem;">
                    <div>
                        <div style="opacity:.7;">Alamat Domisili 1</div>
                        <div style="font-weight:500;">
                            {{ $profile?->alamat1 ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Alamat Domisili 2</div>
                        <div style="font-weight:500;">
                            {{ $profile?->alamat2 ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Alamat Sesuai KTP</div>
                        <div style="font-weight:500;">
                            {{ $profile?->alamat_sesuai_ktp ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Provinsi</div>
                        <div style="font-weight:500;">
                            {{ $profile?->provinsi ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kabupaten / Kota</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kab_kota ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kecamatan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kecamatan ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Desa / Kelurahan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->desa_kelurahan ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kode Pos</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kode_pos ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="opacity:.7;">Nama Bank</div>
                        <div style="font-weight:500;">
                            {{ $profile?->nama_bank ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Nomor Rekening</div>
                        <div style="font-weight:500;">
                            {{ $profile?->no_rekening ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="opacity:.7;">NPWP</div>
                        <div style="font-weight:500;">
                            {{ $profile?->npwp ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Nomor NPWP</div>
                        <div style="font-weight:500;">
                            {{ $profile?->nomor_npwp ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="opacity:.7;">PTKP</div>
                        <div style="font-weight:500;">
                            {{ $profile?->ptkp ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div style="opacity:.7;">BPJS Ketenagakerjaan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->bpjs_tk ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Nomor BPJS Kesehatan</div>
                        <div style="font-weight:500;">
                            {{ $profile?->nomor_bpjs_kesehatan ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div style="opacity:.7;">Kelas BPJS</div>
                        <div style="font-weight:500;">
                            {{ $profile?->kelas_bpjs ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="padding:14px 16px;display:flex;flex-direction:column;gap:8px;font-size:.85rem;">
                <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                    Status Kepegawaian & Exit
                </div>

                @php
                    $joinDateLabel = $profile?->tgl_bergabung ? $profile->tgl_bergabung->format('d-m-Y') : null;
                    $probationEndLabel = $profile?->tgl_akhir_percobaan ? $profile->tgl_akhir_percobaan->format('d-m-Y') : null;

                    $masaKerjaDisplay = '-';
                    if ($profile && $profile->tgl_bergabung) {
                        $start = \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay();
                        $end = $profile->exit_date
                            ? \Carbon\Carbon::parse($profile->exit_date)->startOfDay()
                            : \Carbon\Carbon::today();
                        if ($end->greaterThanOrEqualTo($start)) {
                            $diff = $start->diff($end);
                            $parts = [];
                            if ($diff->y > 0) {
                                $parts[] = $diff->y . ' Tahun';
                            }
                            if ($diff->m > 0) {
                                $parts[] = $diff->m . ' Bulan';
                            }
                            if ($diff->y === 0 && $diff->m === 0 && $diff->d > 0) {
                                $parts[] = $diff->d . ' Hari';
                            }
                            if (empty($parts)) {
                                $parts[] = '0 Hari';
                            }
                            $masaKerjaDisplay = implode(' ', $parts);
                        }
                    }

                    $exitDateLabel = $profile?->exit_date ? $profile->exit_date->format('d-m-Y') : null;
                    $exitReasonLabel = null;
                    if ($profile && $profile->exit_reason_code) {
                        $map = [
                            'RESIGN' => 'Resign',
                            'HABIS_KONTRAK' => 'Habis kontrak',
                            'PHK' => 'PHK',
                            'PENSIUN' => 'Pensiun',
                            'MENINGGAL' => 'Meninggal',
                            'LAINNYA' => 'Lainnya',
                        ];
                        $exitReasonLabel = $map[$profile->exit_reason_code] ?? $profile->exit_reason_code;
                    }
                @endphp

                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="opacity:.7;">Tanggal Bergabung</div>
                    <div style="font-weight:500;">
                        {{ $joinDateLabel ?? '-' }}
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="opacity:.7;">Akhir Masa Percobaan</div>
                    <div style="font-weight:500;">
                        {{ $probationEndLabel ?? '-' }}
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="opacity:.7;">Masa Kerja</div>
                    <div style="font-weight:500;">
                        {{ $masaKerjaDisplay }}
                    </div>
                </div>

                <div style="margin-top:8px;border-top:1px dashed #e5e7eb;padding-top:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div style="opacity:.7;">Status</div>
                        <div style="font-weight:500;">
                            {{ $employee->status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:6px;">
                        <div style="opacity:.7;">Tanggal Keluar</div>
                        <div style="font-weight:500;">
                            {{ $exitDateLabel ?? '-' }}
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:6px;">
                        <div style="opacity:.7;">Alasan Keluar</div>
                        <div style="font-weight:500;text-align:right;">
                            {{ $exitReasonLabel ?? '-' }}
                        </div>
                    </div>

                    <div style="margin-top:6px;">
                        <div style="opacity:.7;margin-bottom:2px;">Catatan Keluar</div>
                        <div style="font-weight:500;white-space:pre-wrap;">
                            {{ $profile?->exit_reason_note ?? '-' }}
                        </div>
                    </div>

                    <div style="margin-top:6px;">
                        <div style="opacity:.7;margin-bottom:2px;">Dokumen Exit</div>
                        @if($profile?->exit_document_path)
                            <a href="{{ asset('storage/'.$profile->exit_document_path) }}"
                               target="_blank"
                               style="color:#1d4ed8;text-decoration:underline;font-size:.85rem;">
                                Lihat dokumen exit
                            </a>
                        @else
                            <div style="font-size:.85rem;opacity:.6;">Belum ada dokumen exit</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="tab-documents" class="employee-tab-section">
        <div class="card" style="padding:14px 16px;margin-bottom:18px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:.9rem;font-weight:600;">Dokumen Karyawan</div>
                    <div style="font-size:.8rem;opacity:.7;margin-top:2px;">
                        SK, kontrak kerja, SP, mutasi, rotasi, promosi, dan dokumen terkait lainnya.
                    </div>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('hr.employees.documents.store', $employee->id) }}"
                  enctype="multipart/form-data"
                  style="margin-bottom:16px;padding:10px 12px;border-radius:10px;background:#f9fafb;display:flex;flex-direction:column;gap:8px;">
                @csrf

                <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(0,1fr);gap:8px 12px;align-items:flex-end;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">Jenis Dokumen</label>
                        <select name="type"
                                required
                                style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                            <option value="">Pilih jenis...</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">Judul Dokumen (opsional)</label>
                        <input
                            type="text"
                            name="title"
                            placeholder="Contoh: Kontrak Kerja 2025–2026"
                            style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                    </div>

                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">Tanggal Berlaku</label>
                        <input
                            type="date"
                            name="effective_date"
                            style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                    </div>

                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">Tanggal Berakhir (opsional)</label>
                        <input
                            type="date"
                            name="expired_date"
                            style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                    </div>

                    <div style="grid-column:1 / -1;display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">File Dokumen</label>
                        <input
                            type="file"
                            name="file"
                            required
                            style="font-size:.85rem;">
                        <div style="font-size:.75rem;opacity:.6;margin-top:2px;">
                            Format: pdf, jpg, jpeg, png, doc, docx. Maks 5 MB.
                        </div>
                    </div>

                    <div style="grid-column:1 / -1;display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:.8rem;font-weight:500;">Catatan (opsional)</label>
                        <textarea
                            name="notes"
                            rows="2"
                            style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;resize:vertical;"></textarea>
                    </div>

                    <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;margin-top:4px;">
                        <button type="submit"
                                style="padding:7px 14px;border-radius:8px;border:none;background:#1e4a8d;color:#fff;font-size:.85rem;cursor:pointer;">
                            + Tambah Dokumen
                        </button>
                    </div>
                </div>
            </form>

            @if($documents->isEmpty())
                <div style="font-size:.85rem;opacity:.7;">
                    Belum ada dokumen yang diunggah untuk karyawan ini.
                </div>
            @else
                <div style="overflow:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Jenis</th>
                            <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Judul</th>
                            <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;white-space:nowrap;">Periode</th>
                            <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Catatan</th>
                            <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;white-space:nowrap;">Diunggah Oleh</th>
                            <th style="text-align:right;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($documents as $doc)
                            @php
                                $fileUrl = $doc->file_path ? asset('storage/'.$doc->file_path) : null;
                                $isPdf = $fileUrl && \Illuminate\Support\Str::endsWith(strtolower($doc->file_path), '.pdf');
                            @endphp
                            <tr>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;white-space:nowrap;">
                                    {{ $doc->type_label }}
                                </td>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                                    {{ $doc->title ?? '-' }}
                                </td>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;font-size:.8rem;">
                                    @php
                                        $start = $doc->effective_date ? $doc->effective_date->format('d-m-Y') : null;
                                        $end = $doc->expired_date ? $doc->expired_date->format('d-m-Y') : null;
                                    @endphp
                                    @if($start && $end)
                                        {{ $start }} s/d {{ $end }}
                                    @elseif($start)
                                        {{ $start }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;font-size:.8rem;max-width:260px;">
                                    @if($doc->notes)
                                        <div style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            {{ $doc->notes }}
                                        </div>
                                    @else
                                        <span style="opacity:.5;">-</span>
                                    @endif
                                </td>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;font-size:.8rem;white-space:nowrap;">
                                    {{ $doc->creator?->name ?? '-' }}<br>
                                    <span style="opacity:.6;">
                                        {{ $doc->created_at?->format('d-m-Y H:i') }}
                                    </span>
                                </td>
                                <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;text-align:right;white-space:nowrap;">
                                    @if($fileUrl)
                                        <a href="{{ $fileUrl }}"
                                           target="_blank"
                                           style="font-size:.8rem;padding:4px 8px;border-radius:8px;border:1px solid #d1d5db;text-decoration:none;color:#111827;margin-right:4px;">
                                            {{ $isPdf ? 'Preview' : 'Lihat' }}
                                        </a>
                                    @endif

                                    <form action="{{ route('hr.employee_documents.destroy', $doc->id) }}"
                                          method="POST"
                                          style="display:inline-block;margin:0;"
                                          onsubmit="return confirm('Hapus dokumen ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="font-size:.8rem;padding:4px 8px;border-radius:8px;border:1px solid #fee2e2;background:#fef2f2;color:#b91c1c;cursor:pointer;">
                                            Hapus
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabLinks = document.querySelectorAll('.employee-tab-link');
            var sections = {
                info: document.getElementById('tab-info'),
                documents: document.getElementById('tab-documents')
            };

            tabLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var target = this.getAttribute('data-tab-target');

                    Object.keys(sections).forEach(function (key) {
                        sections[key].classList.remove('employee-tab-section-active');
                    });

                    tabLinks.forEach(function (l) {
                        l.classList.remove('employee-tab-link-active');
                    });

                    if (sections[target]) {
                        sections[target].classList.add('employee-tab-section-active');
                    }
                    this.classList.add('employee-tab-link-active');
                });
            });
        });
    </script>

</x-app>
