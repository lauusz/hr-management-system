<x-app title="Tambah Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Karyawan Baru</h1>
                <p class="section-subtitle">Lengkapi formulir di bawah untuk mendaftarkan karyawan ke dalam sistem.</p>
            </div>
        </div>
    </x-slot>

    <div class="edit-page">

        @if ($errors->any())
        <div class="alert-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <strong>Terjadi Kesalahan</strong>
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
        @endif

        <div class="page-header">
            <a href="{{ route('hr.employees.index') }}" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('hr.employees.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="section-title">Data Karyawan</h2>
                </div>
                <div class="section-body">
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
                                <option value="TETAP" @selected(old('kategori')==='TETAP')>Karyawan Tetap</option>
                                <option value="KONTRAK" @selected(old('kategori')==='KONTRAK')>Karyawan Kontrak</option>
                                <option value="MAGANG" @selected(old('kategori')==='MAGANG')>Magang</option>
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
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
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
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
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
                                <option value="L" @selected(old('jenis_kelamin')==='L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin')==='P')>Perempuan</option>
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
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="section-title">Alamat Domisili</h2>
                </div>
                <div class="section-body">
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
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                    <h2 class="section-title">Pajak & BPJS</h2>
                </div>
                <div class="section-body">
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
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="section-title">Masa Kerja</h2>
                </div>
                <div class="section-body">
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
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="section-title">Data Akun & Akses</h2>
                </div>
                <div class="section-body">
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

                        <div class="form-group full-width">
                            <div class="info-alert">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <p>Password default untuk karyawan baru adalah <strong>123456</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="window.location='{{ route('hr.employees.index') }}'">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Data Karyawan
                </button>
            </div>

        </form>
    </div>

    <style>
        :root {
            --edit-primary: #1e4a8d;
            --edit-primary-dark: #163a75;
            --edit-primary-soft: rgba(30, 74, 141, 0.08);
            --edit-bg: #f6f7fb;
            --edit-surface: #ffffff;
            --edit-border: #d9e0ea;
            --edit-border-light: #eef1f6;
            --edit-text: #122033;
            --edit-text-secondary: #5b6b7f;
            --edit-text-muted: #8a97a8;
            --edit-success-soft: #eaf8ee;
            --edit-success-text: #1f8f4d;
            --edit-danger-soft: #fff1f0;
            --edit-danger-text: #d64545;
            --edit-warning-soft: #fff7e8;
            --edit-warning-text: #d38b16;
            --edit-radius-sm: 8px;
            --edit-radius-md: 10px;
            --edit-radius-lg: 14px;
            --edit-radius-xl: 16px;
            --edit-shadow-sm: 0 1px 3px rgba(18, 32, 51, 0.04);
            --edit-shadow-md: 0 4px 16px rgba(18, 32, 51, 0.06);
            --edit-shadow-lg: 0 8px 30px rgba(18, 32, 51, 0.08);
        }

        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-header-inline .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-header-inline .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-header-inline .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--edit-text);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-header-inline .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--edit-text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy {
            background: var(--edit-primary-soft);
            color: var(--edit-primary);
        }

        .edit-page {
            max-width: 860px;
            margin: 0 auto;
            padding: 0 16px 32px;
        }

        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--edit-radius-lg);
            background: var(--edit-danger-soft);
            border: 1px solid rgba(214, 69, 69, 0.15);
            color: var(--edit-danger-text);
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-error strong {
            display: block;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .alert-error span {
            opacity: 0.9;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 12px;
            margin-bottom: 20px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 34px;
            padding: 0 12px;
            background: var(--edit-surface);
            border: 1px solid var(--edit-border);
            border-radius: var(--edit-radius-md);
            color: var(--edit-text-secondary);
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: var(--edit-shadow-sm);
        }
        .btn-back:hover {
            border-color: var(--edit-primary);
            color: var(--edit-primary);
            background: var(--edit-primary-soft);
        }
        .btn-back svg {
            flex-shrink: 0;
        }

        .section-card {
            background: var(--edit-surface);
            border: 1px solid var(--edit-border);
            border-radius: var(--edit-radius-xl);
            box-shadow: var(--edit-shadow-sm);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 16px 12px;
            border-bottom: 1px solid var(--edit-border-light);
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--edit-radius-md);
            background: var(--edit-primary-soft);
            color: var(--edit-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--edit-text);
            margin: 0;
            letter-spacing: -0.01em;
        }
        .section-body {
            padding: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--edit-text-secondary);
        }
        .req {
            color: var(--edit-danger-text);
        }

        .form-control {
            padding: 10px 12px;
            border: 1.5px solid var(--edit-border);
            border-radius: var(--edit-radius-md);
            font-size: 0.88rem;
            color: var(--edit-text);
            background: var(--edit-surface);
            transition: all 0.2s ease;
            font-family: inherit;
            width: 100%;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--edit-primary);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }
        .form-control::placeholder {
            color: var(--edit-text-muted);
            opacity: 0.7;
        }
        .form-control:disabled,
        .form-control[readonly] {
            background: var(--edit-bg);
            color: var(--edit-text-muted);
            cursor: not-allowed;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 64px;
        }
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%238a97a8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }

        .helper-text {
            font-size: 0.72rem;
            color: var(--edit-text-muted);
            line-height: 1.4;
        }

        .file-upload-wrapper {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .file-upload-input {
            display: none;
        }
        .file-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--edit-bg);
            border: 1.5px dashed var(--edit-border);
            border-radius: var(--edit-radius-md);
            color: var(--edit-text-secondary);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            width: fit-content;
        }
        .file-upload-label:hover {
            background: var(--edit-primary-soft);
            border-color: var(--edit-primary);
            color: var(--edit-primary);
        }
        .file-upload-label svg {
            flex-shrink: 0;
        }

        .info-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--edit-radius-md);
            background: #eff6ff;
            border: 1px solid rgba(30, 74, 141, 0.1);
            color: var(--edit-primary);
            font-size: 0.78rem;
            line-height: 1.5;
        }
        .info-alert p {
            margin: 0;
        }
        .info-alert strong {
            font-weight: 700;
        }
        .info-alert svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 8px;
            padding-top: 16px;
            border-top: 1px solid var(--edit-border-light);
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 20px;
            background: var(--edit-primary);
            color: #fff;
            border: none;
            border-radius: var(--edit-radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(30, 74, 141, 0.25);
        }
        .btn-primary:hover {
            background: var(--edit-primary-dark);
            box-shadow: 0 6px 18px rgba(30, 74, 141, 0.35);
            transform: translateY(-1px);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 20px;
            background: var(--edit-surface);
            color: var(--edit-text-secondary);
            border: 1.5px solid var(--edit-border);
            border-radius: var(--edit-radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .btn-secondary:hover {
            background: var(--edit-bg);
            border-color: var(--edit-text-muted);
            color: var(--edit-text);
        }

        @media (min-width: 640px) {
            .edit-page {
                padding: 0 24px 40px;
            }
            .page-header {
                margin-bottom: 24px;
            }
            .section-header {
                padding: 20px 20px 14px;
            }
            .section-body {
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            .section-card {
                margin-bottom: 20px;
            }
        }

        @media (min-width: 768px) {
            .edit-page {
                padding: 0 24px 48px;
            }
            .btn-back {
                height: 36px;
                padding: 0 14px;
                font-size: 0.8rem;
            }
            .section-title {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 639px) {
            .form-actions {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                margin: 0;
                padding: 12px 16px;
                background: rgba(255,255,255,0.96);
                backdrop-filter: blur(8px);
                border-top: 1px solid var(--edit-border);
                box-shadow: 0 -4px 20px rgba(18, 32, 51, 0.08);
                z-index: 40;
                gap: 10px;
            }
            .edit-page {
                padding-bottom: 80px;
            }
            .btn-primary,
            .btn-secondary {
                flex: 1;
            }
        }
    </style>
</x-app>
