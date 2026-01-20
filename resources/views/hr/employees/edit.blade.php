<x-app title="Edit Karyawan">

    <div class="main-container">
        {{-- Error Alert --}}
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
                    <h2 class="form-title">Edit Data Karyawan</h2>
                    <p class="form-subtitle">Perbarui informasi data diri, akun, dan dokumen karyawan.</p>
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

            <form class="form-content" method="POST" action="{{ route('hr.employees.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @php
                $profile = $item->profile;
                @endphp

                {{-- SECTION 1: AKUN & AKSES --}}
                <div class="form-section-title">Data Akun & Akses</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="name">Nama Lengkap <span class="req">*</span></label>
                        <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" class="form-control" value="{{ old('username', $item->username) }}">
                    </div>

                    <div class="form-group">
                        <label for="phone">No. Telepon <span class="req">*</span></label>
                        <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $item->phone) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Role / Peran <span class="req">*</span></label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            @foreach ($roles as $role)
                            @php
                            $currentRole = old('role', $item->role);
                            $currentRoleValue = $currentRole instanceof \UnitEnum ? $currentRole->value : $currentRole;
                            @endphp

                            <option value="{{ $role->value }}" @selected($currentRoleValue==$role->value)>{{ $role->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="division_id">Divisi</label>
                        <select id="division_id" name="division_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id', $item->division_id) == $division->id)>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- FIELD MANAGER (APPROVER) --}}
                    <div class="form-group">
                        <label for="manager_id" style="color:#1e4a8d; font-weight:600;">Manager (Approver / Penyetuju)</label>
                        <select id="manager_id" name="manager_id" class="form-control">
                            <option value="">-- Tidak Ada / Langsung HRD --</option>
                            @if(isset($managers))
                            @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" @selected(old('manager_id', $item->manager_id) == $mgr->id)>
                                {{ $mgr->name }} ({{ $mgr->position->name ?? 'Manager' }})
                            </option>
                            @endforeach
                            @endif
                        </select>
                        <small class="helper-text">User ini yang berhak melakukan <b>Approve/Reject</b> cuti.</small>
                    </div>

                    {{-- FIELD SUPERVISOR (OBSERVER) --}}
                    <div class="form-group">
                        <label for="direct_supervisor_id">Supervisor (Observer)</label>
                        <select id="direct_supervisor_id" name="direct_supervisor_id" class="form-control">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" @selected(old('direct_supervisor_id', $item->direct_supervisor_id) == $spv->id)>
                                {{ $spv->name }} - {{ $spv->position->name ?? $spv->role->value }}
                            </option>
                            @endforeach
                        </select>
                        <small class="helper-text">Hanya menerima notifikasi "Mengetahui" (CC), tidak melakukan approval.</small>
                    </div>

                    @isset($shifts)
                    <div class="form-group">
                        <label for="shift_id">Shift Kerja</label>
                        <select id="shift_id" name="shift_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" @selected(old('shift_id', $item->shift_id) == $shift->id)>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endisset

                    <div class="form-group">
                        <label for="status">Status Akun</label>
                        <select id="status" name="status" class="form-control">
                            <option value="ACTIVE" @selected(old('status', $item->status) === 'ACTIVE')>Aktif</option>
                            <option value="INACTIVE" @selected(old('status', $item->status) === 'INACTIVE')>Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-info full-width">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Password tidak ditampilkan. Gunakan tombol di bawah ini jika ingin mereset password menjadi default (123456).
                    </div>

                    {{-- [BARU] Tombol Reset Password --}}
                    <div class="form-group full-width" style="margin-top: 8px;">
                        <button type="button" class="btn-reset-danger" data-modal-open="modal-reset-password">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                            </svg>
                            Reset Password
                        </button>
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 2: INFO KARYAWAN --}}
                <div class="form-section-title">Informasi Karyawan</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pt_id">Perusahaan (PT)</label>
                        <select id="pt_id" name="pt_id" class="form-control">
                            <option value="">Pilih PT</option>
                            @foreach($ptOptions as $ptOption)
                            <option value="{{ $ptOption->id }}" @selected(old('pt_id', optional($profile)->pt_id) == $ptOption->id)>{{ $ptOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori Pegawai</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <option value="TETAP" @selected(old('kategori', optional($profile)->kategori) === 'TETAP')>Karyawan Tetap</option>
                            <option value="KONTRAK" @selected(old('kategori', optional($profile)->kategori) === 'KONTRAK')>Karyawan Kontrak</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (Nomor Induk Karyawan)</label>
                        <input id="nik" type="text" name="nik" class="form-control" value="{{ old('nik', optional($profile)->nik) }}">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Kantor</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', optional($profile)->email) }}">
                    </div>

                    <div class="form-group">
                        <label for="position_id">Jabatan</label>
                        <select id="position_id" name="position_id" class="form-control">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected(old('position_id', $item->position_id) == $position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kewarganegaraan">Kewarganegaraan</label>
                        <input id="kewarganegaraan" type="text" name="kewarganegaraan" class="form-control" value="{{ old('kewarganegaraan', optional($profile)->kewarganegaraan) }}" placeholder="Misal: WNI">
                    </div>

                    <div class="form-group">
                        <label for="agama">Agama</label>
                        <input id="agama" type="text" name="agama" class="form-control" value="{{ old('agama', optional($profile)->agama) }}">
                    </div>

                    <div class="form-group">
                        <label for="pendidikan">Pendidikan Terakhir</label>
                        <input id="pendidikan" type="text" name="pendidikan" class="form-control" value="{{ old('pendidikan', optional($profile)->pendidikan) }}" placeholder="Contoh: S1 Teknik Informatika">
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('jenis_kelamin', optional($profile)->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', optional($profile)->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tgl_lahir">Tanggal Lahir</label>
                        <input id="tgl_lahir" type="date" name="tgl_lahir" class="form-control" value="{{ old('tgl_lahir', optional(optional($profile)->tgl_lahir)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label for="tempat_lahir">Tempat Lahir</label>
                        <input id="tempat_lahir" type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', optional($profile)->tempat_lahir) }}">
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 3: DOMISILI --}}
                <div class="form-section-title">Alamat Domisili</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="alamat1">Alamat Utama</label>
                        <textarea id="alamat1" name="alamat1" rows="2" class="form-control">{{ old('alamat1', optional($profile)->alamat1) }}</textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="alamat2">Detail Alamat / Alamat Tambahan</label>
                        <textarea id="alamat2" name="alamat2" rows="2" class="form-control" placeholder="Contoh: Blok B No. 12">{{ old('alamat2', optional($profile)->alamat2) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="provinsi">Provinsi</label>
                        <input id="provinsi" type="text" name="provinsi" class="form-control" value="{{ old('provinsi', optional($profile)->provinsi) }}">
                    </div>
                    <div class="form-group">
                        <label for="kab_kota">Kabupaten / Kota</label>
                        <input id="kab_kota" type="text" name="kab_kota" class="form-control" value="{{ old('kab_kota', optional($profile)->kab_kota) }}">
                    </div>
                    <div class="form-group">
                        <label for="kecamatan">Kecamatan</label>
                        <input id="kecamatan" type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', optional($profile)->kecamatan) }}">
                    </div>
                    <div class="form-group">
                        <label for="desa_kelurahan">Desa / Kelurahan</label>
                        <input id="desa_kelurahan" type="text" name="desa_kelurahan" class="form-control" value="{{ old('desa_kelurahan', optional($profile)->desa_kelurahan) }}">
                    </div>
                    <div class="form-group">
                        <label for="kode_pos">Kode Pos</label>
                        <input id="kode_pos" type="text" name="kode_pos" class="form-control" value="{{ old('kode_pos', optional($profile)->kode_pos) }}">
                    </div>
                </div>

                <div class="section-divider"></div>

                {{-- SECTION 4: KEUANGAN & DOKUMEN --}}
                <div class="form-section-title">Keuangan, Pajak & Dokumen</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_bank">Nama Bank</label>
                        <input id="nama_bank" type="text" name="nama_bank" class="form-control" value="{{ old('nama_bank', optional($profile)->nama_bank) }}" placeholder="Contoh: BCA">
                    </div>
                    <div class="form-group">
                        <label for="no_rekening">Nomor Rekening</label>
                        <input id="no_rekening" type="text" name="no_rekening" class="form-control" value="{{ old('no_rekening', optional($profile)->no_rekening) }}">
                    </div>

                    <div class="form-group">
                        <label for="no_npwp">Nomor NPWP</label>
                        <input id="no_npwp" type="text" name="no_npwp" class="form-control" value="{{ old('no_npwp', optional($profile)->no_npwp) }}">
                    </div>
                    <div class="form-group">
                        <label for="ptkp">Status PTKP</label>
                        <input id="ptkp" type="text" name="ptkp" class="form-control" value="{{ old('ptkp', optional($profile)->ptkp) }}" placeholder="Contoh: TK/0">
                    </div>

                    <div class="form-group">
                        <label for="bpjs_tk">BPJS Ketenagakerjaan</label>
                        <input id="bpjs_tk" type="text" name="bpjs_tk" class="form-control" value="{{ old('bpjs_tk', optional($profile)->bpjs_tk) }}">
                    </div>
                    <div class="form-group">
                        <label for="no_bpjs_kesehatan">BPJS Kesehatan</label>
                        <input id="no_bpjs_kesehatan" type="text" name="no_bpjs_kesehatan" class="form-control" value="{{ old('no_bpjs_kesehatan', optional($profile)->no_bpjs_kesehatan) }}">
                    </div>
                    <div class="form-group full-width">
                        <label for="kelas_bpjs">Kelas BPJS</label>
                        <input id="kelas_bpjs" type="text" name="kelas_bpjs" class="form-control" value="{{ old('kelas_bpjs', optional($profile)->kelas_bpjs) }}" placeholder="Contoh: Kelas 1">
                    </div>

                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label for="path_ktp">Upload KTP</label>
                            @if(optional($profile)->path_ktp)
                            @php $ktpModalId = 'modal-ktp-'.$item->id; @endphp
                            <button type="button" class="btn-sm-view" data-modal-open="{{ $ktpModalId }}">Lihat File Saat Ini</button>

                            <x-modal :id="$ktpModalId" title="KTP" type="info">
                                <div style="text-align:center;">
                                    <img src="{{ asset('storage/' . $profile->path_ktp) }}" alt="KTP" style="max-width:100%; border-radius:8px;">
                                </div>
                            </x-modal>
                            @endif
                        </div>
                        <input id="path_ktp" type="file" name="path_ktp" accept=".jpg,.jpeg,.png" class="form-control-file">
                        <small class="helper-text">Format: JPG/PNG, Maks 2MB. Kosongkan jika tidak ingin mengubah.</small>
                    </div>

                    <div class="form-group">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label for="path_kartu_keluarga">Upload Kartu Keluarga</label>
                            @if(optional($profile)->path_kartu_keluarga)
                            @php $kkModalId = 'modal-kk-'.$item->id; @endphp
                            <button type="button" class="btn-sm-view" data-modal-open="{{ $kkModalId }}">Lihat File Saat Ini</button>

                            <x-modal :id="$kkModalId" title="Kartu Keluarga" type="info">
                                <div style="text-align:center;">
                                    <img src="{{ asset('storage/' . $profile->path_kartu_keluarga) }}" alt="Kartu Keluarga" style="max-width:100%; border-radius:8px;">
                                </div>
                            </x-modal>
                            @endif
                        </div>
                        <input id="path_kartu_keluarga" type="file" name="path_kartu_keluarga" accept=".jpg,.jpeg,.png" class="form-control-file">
                        <small class="helper-text">Format: JPG/PNG, Maks 2MB. Kosongkan jika tidak ingin mengubah.</small>
                    </div>
                </div>

                <div class="section-divider"></div>
                {{-- SECTION 5: MASA KERJA --}}
                <div class="form-section-title">Masa Kerja</div>
                <div class="form-grid">
                    
                    {{-- [FIXED LOGIC MASA KERJA] --}}
                    @php
                        // 1. Ambil data manual dari database
                        $masaKerjaValue = old('masa_kerja', optional($profile)->masa_kerja);

                        // 2. Jika kosong atau formatnya aneh (ada desimal panjang), kita hitung ulang otomatis
                        $joinDate = optional($profile)->tgl_bergabung;
                        
                        if ($joinDate && (!$masaKerjaValue || str_contains($masaKerjaValue, '.'))) {
                            try {
                                // Hitung selisih waktu dari Tgl Bergabung sampai Sekarang
                                $joinCarbon = \Carbon\Carbon::parse($joinDate);
                                $now = \Carbon\Carbon::now();
                                
                                $diff = $joinCarbon->diff($now);
                                
                                // Format string rapi: "2 Tahun 5 Bulan"
                                $parts = [];
                                if ($diff->y > 0) $parts[] = $diff->y . ' Tahun';
                                if ($diff->m > 0) $parts[] = $diff->m . ' Bulan';
                                
                                // Jika baru gabung (kurang dari sebulan), tampilkan hari
                                if (empty($parts) && $diff->d > 0) {
                                    $parts[] = $diff->d . ' Hari';
                                }

                                // Gabungkan jadi string
                                $masaKerjaValue = implode(' ', $parts);

                            } catch (\Exception $e) {
                                // Fallback jika tanggal error
                                $masaKerjaValue = '';
                            }
                        }
                    @endphp

                    <div class="form-group full-width">
                        <label for="masa_kerja">Masa Kerja (Hitung Otomatis / Opsional)</label>
                        <input id="masa_kerja" type="text" name="masa_kerja" class="form-control" 
                               value="{{ $masaKerjaValue }}" 
                               placeholder="Contoh: 2 Tahun 5 Bulan"
                               disabled>
                        <small class="helper-text" style="color:#6b7280;">Sistem otomatis menghitung berdasarkan Tanggal Bergabung di bawah.</small>
                    </div>

                    <div class="form-group">
                        <label for="tgl_bergabung">Tanggal Bergabung</label>
                        <input id="tgl_bergabung" type="date" name="tgl_bergabung" class="form-control" 
                               value="{{ old('tgl_bergabung', optional(optional($profile)->tgl_bergabung)->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label for="tgl_akhir_percobaan">Tanggal Akhir Percobaan (Probation)</label>
                        <input id="tgl_akhir_percobaan" type="date" name="tgl_akhir_percobaan" class="form-control" 
                               value="{{ old('tgl_akhir_percobaan', optional(optional($profile)->tgl_akhir_percobaan)->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="window.location='{{ route('hr.employees.index') }}'">Batal</button>
                    <button type="submit" class="btn-primary">Update Data Karyawan</button>
                </div>

            </form>
        </div>
    </div>

    {{-- [BARU] Modal Reset Password --}}
    <div id="modal-reset-password" class="modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 50; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 24px; border-radius: 12px; max-width: 400px; width: 90%; text-align: center;">
            <div style="margin-bottom: 16px; color: #dc2626;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3 style="margin: 0 0 8px; font-size: 18px; color: #111827;">Reset Password?</h3>
            <p style="margin: 0 0 20px; color: #6b7280; font-size: 14px;">
                Password karyawan <b>{{ $item->name }}</b> akan diubah menjadi default <b>123456</b>. Tindakan ini tidak dapat dibatalkan.
            </p>

            {{-- Form Khusus Reset --}}
            <form action="{{ route('hr.employees.reset-password', $item->id) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button type="button" class="btn-secondary" data-modal-close="true">Batal</button>
                    <button type="submit" class="btn-primary" style="background-color: #dc2626; border-color: #dc2626;">Ya, Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var openButtons = document.querySelectorAll('[data-modal-open]');
            openButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = btn.getAttribute('data-modal-open');
                    if (!id) return;
                    var modal = document.getElementById(id);
                    if (!modal) return;
                    modal.style.display = 'flex';
                });
            });

            var closeButtons = document.querySelectorAll('[data-modal-close="true"]');
            closeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var modal = btn.closest('.modal-backdrop');
                    if (!modal) return;
                    modal.style.display = 'none';
                });
            });
        });
    </script>

    {{-- STYLES --}}
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

        /* Small Button for View File */
        .btn-sm-view {
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #1e4a8d;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-sm-view:hover {
            background: #f0f9ff;
            border-color: #1e4a8d;
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

        /* [BARU] Style Tombol Reset */
        .btn-reset-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: fit-content;
        }

        .btn-reset-danger:hover {
            background-color: #fee2e2;
            border-color: #fda4af;
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