<x-app title="Tambah Karyawan">

    <div class="main-container">
        @if ($errors->any())
        <div class="alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div>
                <span style="font-weight:600; display:block; margin-bottom:2px;">Terjadi Kesalahan</span>
                <span style="font-size:0.9em">{{ $errors->first() }}</span>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-title">Tambah Karyawan Baru</h2>
                    <p class="form-subtitle">Lengkapi formulir di bawah untuk mendaftarkan karyawan ke dalam sistem.</p>
                </div>
                <a href="{{ route('hr.employees.index') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5" />
                        <path d="M12 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="divider"></div>

            <form class="form-content" method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- SECTION 1: DATA KARYAWAN (URUT EXCEL) --}}
                <div class="form-section-header">
                    <div class="form-section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="form-section-title">Data Karyawan</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="name">Nama Lengkap <span class="req">*</span></label>
                        <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama sesuai KTP" required>
                    </div>

                    <div class="form-group">
                        <label for="pt_id">Perusahaan (PT)</label>
                        <select id="pt_id" name="pt_id" class="form-control">
                            <option value="">Pilih PT</option>
                            @foreach($ptOptions as $ptOption)
                            <option value="{{ $ptOption->id }}" @selected(old('pt_id')==$ptOption->id)>{{ $ptOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori Pegawai</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <option value="Karyawan Tetap" @selected(old('kategori')==='Karyawan Tetap')>Karyawan Tetap</option>
                            <option value="Karyawan Kontrak" @selected(old('kategori')==='Karyawan Kontrak')>Karyawan Kontrak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (Nomor Induk Karyawan)</label>
                        <input id="nik" type="text" name="nik" class="form-control" value="{{ old('nik') }}">
                    </div>

                    <div class="form-group">
                        <label for="phone">No. Telepon</label>
                        <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Kantor</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="position_id">Jabatan</label>
                        <select id="position_id" name="position_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected(old('position_id')==$position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="division_id">Divisi</label>
                        <select id="division_id" name="division_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id')==$division->id)>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kewarganegaraan">Kewarganegaraan</label>
                        <input id="kewarganegaraan" type="text" name="kewarganegaraan" class="form-control" value="{{ old('kewarganegaraan', 'Indonesia') }}" placeholder="Misal: WNI" readonly>
                    </div>

                    <div class="form-group">
                        <label for="agama">Agama</label>
                        <select id="agama" name="agama" class="form-control">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" @selected(old('agama')==='Islam')>Islam</option>
                            <option value="Kristen" @selected(old('agama')==='Kristen')>Kristen</option>
                            <option value="Katolik" @selected(old('agama')==='Katolik')>Katolik</option>
                            <option value="Hindu" @selected(old('agama')==='Hindu')>Hindu</option>
                            <option value="Buddha" @selected(old('agama')==='Buddha')>Buddha</option>
                            <option value="Konghucu" @selected(old('agama')==='Konghucu')>Konghucu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="path_kartu_keluarga">Upload Kartu Keluarga</label>
                        <div class="file-upload-wrapper">
                            <input id="path_kartu_keluarga" type="file" name="path_kartu_keluarga" accept=".jpg,.jpeg,.png" class="file-upload-input">
                            <label for="path_kartu_keluarga" class="file-upload-label">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                Pilih File
                            </label>
                            <small class="helper-text">Format: JPG/PNG, Maks 2MB</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="path_ktp">Upload KTP</label>
                        <div class="file-upload-wrapper">
                            <input id="path_ktp" type="file" name="path_ktp" accept=".jpg,.jpeg,.png" class="file-upload-input">
                            <label for="path_ktp" class="file-upload-label">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                Pilih File
                            </label>
                            <small class="helper-text">Format: JPG/PNG, Maks 2MB</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama_bank">Nama Bank</label>
                        <input id="nama_bank" type="text" name="nama_bank" class="form-control" value="{{ old('nama_bank') }}" placeholder="Contoh: BCA">
                    </div>
                    <div class="form-group">
                        <label for="no_rekening">Nomor Rekening</label>
                        <input id="no_rekening" type="text" name="no_rekening" class="form-control" value="{{ old('no_rekening') }}">
                    </div>

                    <div class="form-group">
                        <label for="pendidikan">Pendidikan Terakhir</label>
                        <input id="pendidikan" type="text" name="pendidikan" class="form-control" value="{{ old('pendidikan') }}" placeholder="Contoh: S1 Teknik Informatika">
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('jenis_kelamin')==='L' )>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin')==='P' )>Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tgl_lahir">Tanggal Lahir</label>
                        <input id="tgl_lahir" type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir') }}">
                    </div>
                    <div class="form-group">
                        <label for="tempat_lahir">Tempat Lahir</label>
                        <input id="tempat_lahir" type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 2: DOMISILI --}}
                <div class="form-section-header">
                    <div class="form-section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="form-section-title">Alamat Domisili</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="alamat1">Alamat Utama</label>
                        <textarea id="alamat1" name="alamat1" rows="2" class="form-control">{{ old('alamat1') }}</textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="alamat2">Detail Alamat / Alamat Tambahan</label>
                        <textarea id="alamat2" name="alamat2" rows="2" class="form-control" placeholder="Contoh: Blok B No. 12">{{ old('alamat2') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="provinsi">Provinsi</label>
                        <input id="provinsi" type="text" name="provinsi" class="form-control" value="{{ old('provinsi') }}">
                    </div>
                    <div class="form-group">
                        <label for="kab_kota">Kabupaten / Kota</label>
                        <input id="kab_kota" type="text" name="kab_kota" class="form-control" value="{{ old('kab_kota') }}">
                    </div>
                    <div class="form-group">
                        <label for="kecamatan">Kecamatan</label>
                        <input id="kecamatan" type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan') }}">
                    </div>
                    <div class="form-group">
                        <label for="desa_kelurahan">Desa / Kelurahan</label>
                        <input id="desa_kelurahan" type="text" name="desa_kelurahan" class="form-control" value="{{ old('desa_kelurahan') }}">
                    </div>
                    <div class="form-group">
                        <label for="kode_pos">Kode Pos</label>
                        <input id="kode_pos" type="text" name="kode_pos" class="form-control" value="{{ old('kode_pos') }}">
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 3: PAJAK & BPJS --}}
                <div class="form-section-header">
                    <div class="form-section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                    </div>
                    <h3 class="form-section-title">Pajak & BPJS</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="ptkp">Status PTKP</label>
                        <input id="ptkp" type="text" name="ptkp" class="form-control" value="{{ old('ptkp') }}" placeholder="Contoh: TK/0">
                    </div>

                    <div class="form-group">
                        <label for="nomor_npwp">Nomor NPWP</label>
                        <input id="nomor_npwp" type="text" name="nomor_npwp" class="form-control" value="{{ old('nomor_npwp') }}">
                    </div>

                    <div class="form-group">
                        <label for="bpjs_tk">BPJS Ketenagakerjaan</label>
                        <input id="bpjs_tk" type="text" name="bpjs_tk" class="form-control" value="{{ old('bpjs_tk') }}">
                    </div>
                    <div class="form-group">
                        <label for="no_bpjs_kesehatan">BPJS Kesehatan</label>
                        <input id="no_bpjs_kesehatan" type="text" name="no_bpjs_kesehatan" class="form-control" value="{{ old('no_bpjs_kesehatan') }}">
                    </div>
                    <div class="form-group">
                        <label for="kelas_bpjs">Kelas BPJS</label>
                        <select id="kelas_bpjs" name="kelas_bpjs" class="form-control">
                            <option value="">Pilih Kelas</option>
                            <option value="Kelas 1" @selected(old('kelas_bpjs')==='Kelas 1')>Kelas 1</option>
                            <option value="Kelas 2" @selected(old('kelas_bpjs')==='Kelas 2')>Kelas 2</option>
                            <option value="Kelas 3" @selected(old('kelas_bpjs')==='Kelas 3')>Kelas 3</option>
                        </select>
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 4: MASA KERJA --}}
                <div class="form-section-header">
                    <div class="form-section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="form-section-title">Masa Kerja</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tgl_bergabung">Tanggal Bergabung</label>
                        <input id="tgl_bergabung" type="date" name="tgl_bergabung" class="form-control" value="{{ old('tgl_bergabung') }}">
                    </div>
                    <div class="form-group">
                        <label for="tgl_akhir_percobaan">Tanggal Akhir Percobaan</label>
                        <input id="tgl_akhir_percobaan" type="date" name="tgl_akhir_percobaan" class="form-control" value="{{ old('tgl_akhir_percobaan') }}">
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 5: AKUN & AKSES (DI LUAR EXCEL) --}}
                <div class="form-section-header">
                    <div class="form-section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="form-section-title">Data Akun & Akses</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Opsional - kosongkan untuk auto">
                    </div>

                    <div class="form-group">
                        <label for="role">Role / Peran <span class="req">*</span></label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role')===$role->value)>{{ $role->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 1. MANAGER (APPROVER) --}}
                    <div class="form-group">
                        <label for="manager_id">Manager (Approver)</label>
                        <select id="manager_id" name="manager_id" class="form-control">
                            <option value="">-- Tidak Ada / Langsung HRD --</option>
                            @if(isset($managers))
                            @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" @selected(old('manager_id')==$mgr->id)>
                                {{ $mgr->name }} ({{ $mgr->position->name ?? 'Manager' }})
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- 2. DIRECT SUPERVISOR (OBSERVER) --}}
                    <div class="form-group">
                        <label for="direct_supervisor_id">Supervisor</label>
                        <select id="direct_supervisor_id" name="direct_supervisor_id" class="form-control">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" @selected(old('direct_supervisor_id')==$spv->id)>
                                {{ $spv->name }} - {{ $spv->position->name ?? $spv->role->value }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @isset($shifts)
                    <div class="form-group">
                        <label for="shift_id">Shift Kerja</label>
                        <select id="shift_id" name="shift_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" @selected(old('shift_id')==$shift->id)>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endisset

                    <div class="form-info full-width">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Password default untuk karyawan baru adalah <strong>123456</strong>.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="window.location='{{ route('hr.employees.index') }}'">Batal</button>
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Data Karyawan
                    </button>
                </div>

            </form>
        </div>
    </div>

    <style>
        /* Container */
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Alert */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
        }

        /* Card */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
        }

        .card-header {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .form-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .form-subtitle {
            margin: 6px 0 0;
            font-size: 14px;
            color: #6b7280;
        }

        .divider {
            height: 1px;
            background: #f3f4f6;
            width: 100%;
        }

        .section-divider {
            grid-column: 1 / -1;
            height: 1px;
            background: #e5e7eb;
            margin: 16px 0;
        }

        /* Form Layout */
        .form-content {
            padding: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* 2 Columns on Desktop */
            gap: 16px;
            margin-bottom: 10px;
        }

        .form-section-title {
            grid-column: 1 / -1;
            font-size: 16px;
            font-weight: 700;
            color: #1e4a8d;
            margin-bottom: 12px;
            margin-top: 8px;
            /* Space before section */
        }

        .form-section-title:first-child {
            margin-top: 0;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        /* Inputs */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
        }

        .req {
            color: #dc2626;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            background: #fff;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
            outline: none;
        }

        .form-control-file {
            font-size: 13.5px;
            padding: 6px 0;
        }

        .helper-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .form-info {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Buttons */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #f9fafb;
        }

        .form-actions {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-primary {
            padding: 12px 24px;
            background: #1e4a8d;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #163a75;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
        }

        /* Section Header */
        .form-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-section-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #1e4a8d;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        /* File Upload */
        .file-upload-wrapper {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            width: fit-content;
        }

        .file-upload-label:hover {
            background: #f3f4f6;
            border-color: #1e4a8d;
            color: #1e4a8d;
        }

        /* MOBILE RESPONSIVE TWEAKS */
        @media (max-width: 640px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                /* Stack everything on mobile */
                gap: 16px;
            }

            .form-content {
                padding: 16px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }
    </style>
</x-app>