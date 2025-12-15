<x-app title="Edit Karyawan">
    @if ($errors->any())
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form class="card"
        method="POST"
        action="{{ route('hr.employees.update', $item->id) }}"
        enctype="multipart/form-data"
        style="max-width:520px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf
        @method('PUT')

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Data Akun
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $item->name) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="username" style="font-size:.9rem;font-weight:500;">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username', $item->username) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="phone" style="font-size:.9rem;font-weight:500;">No. Telepon</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $item->phone) }}"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="role" style="font-size:.9rem;font-weight:500;">Role</label>
            <select
                id="role"
                name="role"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Pilih role</option>
                @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $item->role) === $role->value)>
                    {{ $role->value }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="division_id" style="font-size:.9rem;font-weight:500;">Divisi</label>
            <select
                id="division_id"
                name="division_id"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Tidak ada / Belum ditentukan</option>
                @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected(old('division_id', $item->division_id) == $division->id)>
                    {{ $division->name }}
                </option>
                @endforeach
            </select>
        </div>

        @isset($shifts)
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="shift_id" style="font-size:.9rem;font-weight:500;">Shift</label>
            <select
                id="shift_id"
                name="shift_id"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Tidak ada / Belum ditentukan</option>
                @foreach ($shifts as $shift)
                <option value="{{ $shift->id }}" @selected(old('shift_id', $item->shift_id) == $shift->id)>
                    {{ $shift->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endisset

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="status" style="font-size:.9rem;font-weight:500;">Status Akun</label>
            <select
                id="status"
                name="status"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                @php $currentStatus = old('status', $item->status); @endphp
                <option value="ACTIVE" @selected($currentStatus==='ACTIVE' )>Aktif</option>
                <option value="INACTIVE" @selected($currentStatus==='INACTIVE' )>Nonaktif</option>
            </select>
        </div>

        <div style="margin-top:4px;font-size:.8rem;opacity:.7;">
            Password tidak ditampilkan di sini. Jika perlu reset, gunakan menu terpisah atau fitur reset password.
        </div>

        <div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Informasi Karyawan
        </div>

        @php
        $profile = $item->profile;
        @endphp

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="pt_id" style="font-size:.9rem;font-weight:500;">PT</label>
            <select
                id="pt_id"
                name="pt_id"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;background:#fff;">
                <option value="">Pilih PT</option>
                @foreach($ptOptions as $ptOption)
                <option value="{{ $ptOption->id }}"
                    @selected(old('pt_id', $item->profile->pt_id ?? null) == $ptOption->id)>
                    {{ $ptOption->name }}
                </option>
                @endforeach
            </select>
        </div>


        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kategori" style="font-size:.9rem;font-weight:500;">Kategori</label>
            @php $kategori = old('kategori', optional($profile)->kategori); @endphp
            <select
                id="kategori"
                name="kategori"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Pilih kategori</option>
                <option value="TETAP" @selected($kategori==='TETAP' )>Karyawan Tetap</option>
                <option value="KONTRAK" @selected($kategori==='KONTRAK' )>Karyawan Kontrak</option>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="nik" style="font-size:.9rem;font-weight:500;">NIK</label>
            <input
                id="nik"
                type="text"
                name="nik"
                value="{{ old('nik', optional($profile)->nik) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="email" style="font-size:.9rem;font-weight:500;">Work Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', optional($profile)->email) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="position_id" style="font-size:.9rem;font-weight:500;">Jabatan</label>
            <select
                id="position_id"
                name="position_id"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Tidak ada / Belum ditentukan</option>
                @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', $item->position_id) == $position->id)>
                    {{ $position->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kewarganegaraan" style="font-size:.9rem;font-weight:500;">Kewarganegaraan</label>
            <input
                id="kewarganegaraan"
                type="text"
                name="kewarganegaraan"
                value="{{ old('kewarganegaraan', optional($profile)->kewarganegaraan) }}"
                placeholder="Misal: WNI"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="agama" style="font-size:.9rem;font-weight:500;">Agama</label>
            <input
                id="agama"
                type="text"
                name="agama"
                value="{{ old('agama', optional($profile)->agama) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="path_kartu_keluarga" style="font-size:.9rem;font-weight:500;">Kartu Keluarga (Upload)</label>
            @if(optional($profile)->path_kartu_keluarga)
            @php $kkModalId = 'modal-kk-'.$item->id; @endphp
            <div style="margin-bottom:4px;font-size:.8rem;">
                <button type="button"
                    data-modal-open="{{ $kkModalId }}"
                    style="padding:4px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#1e4a8d;font-size:.8rem;cursor:pointer;">
                    Lihat file saat ini
                </button>
            </div>

            <x-modal
                :id="$kkModalId"
                title="Kartu Keluarga"
                type="info">
                <div style="text-align:center;">
                    <img src="{{ asset('storage/' . $profile->path_kartu_keluarga) }}"
                        alt="Kartu Keluarga"
                        style="max-width:100%;border-radius:8px;display:inline-block;">
                </div>
            </x-modal>
            @endif
            <input
                id="path_kartu_keluarga"
                type="file"
                name="path_kartu_keluarga"
                accept=".jpg,.jpeg,.png"
                style="width:100%;padding:6px 0;border-radius:8px;font-size:.9rem;">
            <div style="font-size:.75rem;opacity:.7;margin-top:2px;">
                Kosongkan jika tidak ingin mengubah. Format: JPG/PNG, maksimal 2MB.
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="path_ktp" style="font-size:.9rem;font-weight:500;">KTP (Upload)</label>
            @if(optional($profile)->path_ktp)
            @php $ktpModalId = 'modal-ktp-'.$item->id; @endphp
            <div style="margin-bottom:4px;font-size:.8rem;">
                <button type="button"
                    data-modal-open="{{ $ktpModalId }}"
                    style="padding:4px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#1e4a8d;font-size:.8rem;cursor:pointer;">
                    Lihat file saat ini
                </button>
            </div>

            <x-modal
                :id="$ktpModalId"
                title="KTP"
                type="info">
                <div style="text-align:center;">
                    <img src="{{ asset('storage/' . $profile->path_ktp) }}"
                        alt="KTP"
                        style="max-width:100%;border-radius:8px;display:inline-block;">
                </div>
            </x-modal>
            @endif
            <input
                id="path_ktp"
                type="file"
                name="path_ktp"
                accept=".jpg,.jpeg,.png"
                style="width:100%;padding:6px 0;border-radius:8px;font-size:.9rem;">
            <div style="font-size:.75rem;opacity:.7;margin-top:2px;">
                Kosongkan jika tidak ingin mengubah. Format: JPG/PNG, maksimal 2MB.
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="nama_bank" style="font-size:.9rem;font-weight:500;">Nama Bank</label>
            <input
                id="nama_bank"
                type="text"
                name="nama_bank"
                value="{{ old('nama_bank', optional($profile)->nama_bank) }}"
                placeholder="Misal: BCA, BNI"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_rekening" style="font-size:.9rem;font-weight:500;">No. Rekening</label>
            <input
                id="no_rekening"
                type="text"
                name="no_rekening"
                value="{{ old('no_rekening', optional($profile)->no_rekening) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="pendidikan" style="font-size:.9rem;font-weight:500;">Pendidikan Terakhir</label>
            <input
                id="pendidikan"
                type="text"
                name="pendidikan"
                value="{{ old('pendidikan', optional($profile)->pendidikan) }}"
                placeholder="Misal: S1"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="jenis_kelamin" style="font-size:.9rem;font-weight:500;">Jenis Kelamin</label>
            @php $jk = old('jenis_kelamin', optional($profile)->jenis_kelamin); @endphp
            <select
                id="jenis_kelamin"
                name="jenis_kelamin"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Pilih</option>
                <option value="L" @selected($jk==='L' )>Laki-laki</option>
                <option value="P" @selected($jk==='P' )>Perempuan</option>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_lahir" style="font-size:.9rem;font-weight:500;">Tanggal Lahir</label>
            <input
                id="tgl_lahir"
                type="date"
                name="tgl_lahir"
                value="{{ old('tgl_lahir', optional(optional($profile)->tgl_lahir)->format('Y-m-d')) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tempat_lahir" style="font-size:.9rem;font-weight:500;">Tempat Lahir</label>
            <input
                id="tempat_lahir"
                type="text"
                name="tempat_lahir"
                value="{{ old('tempat_lahir', optional($profile)->tempat_lahir) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Alamat
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="alamat1" style="font-size:.9rem;font-weight:500;">Alamat 1</label>
            <textarea
                id="alamat1"
                name="alamat1"
                rows="2"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">{{ old('alamat1', optional($profile)->alamat1) }}</textarea>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="alamat2" style="font-size:.9rem;font-weight:500;">Alamat 2</label>
            <textarea
                id="alamat2"
                name="alamat2"
                rows="2"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">{{ old('alamat2', optional($profile)->alamat2) }}</textarea>
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="provinsi" style="font-size:.9rem;font-weight:500;">Provinsi</label>
            <input
                id="provinsi"
                type="text"
                name="provinsi"
                value="{{ old('provinsi', optional($profile)->provinsi) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kab_kota" style="font-size:.9rem;font-weight:500;">Kab/Kota</label>
            <input
                id="kab_kota"
                type="text"
                name="kab_kota"
                value="{{ old('kab_kota', optional($profile)->kab_kota) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kecamatan" style="font-size:.9rem;font-weight:500;">Kecamatan</label>
            <input
                id="kecamatan"
                type="text"
                name="kecamatan"
                value="{{ old('kecamatan', optional($profile)->kecamatan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="desa_kelurahan" style="font-size:.9rem;font-weight:500;">Desa/Kelurahan</label>
            <input
                id="desa_kelurahan"
                type="text"
                name="desa_kelurahan"
                value="{{ old('desa_kelurahan', optional($profile)->desa_kelurahan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kode_pos" style="font-size:.9rem;font-weight:500;">Kode Pos</label>
            <input
                id="kode_pos"
                type="text"
                name="kode_pos"
                value="{{ old('kode_pos', optional($profile)->kode_pos) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Info Pajak & BPJS
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="ptkp" style="font-size:.9rem;font-weight:500;">PTKP</label>
            <input
                id="ptkp"
                type="text"
                name="ptkp"
                value="{{ old('ptkp', optional($profile)->ptkp) }}"
                placeholder="Misal: TK/0"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_npwp" style="font-size:.9rem;font-weight:500;">No. NPWP</label>
            <input
                id="no_npwp"
                type="text"
                name="no_npwp"
                value="{{ old('no_npwp', optional($profile)->no_npwp) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="bpjs_tk" style="font-size:.9rem;font-weight:500;">BPJS TK</label>
            <input
                id="bpjs_tk"
                type="text"
                name="bpjs_tk"
                value="{{ old('bpjs_tk', optional($profile)->bpjs_tk) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_bpjs_kesehatan" style="font-size:.9rem;font-weight:500;">No. BPJS Kesehatan</label>
            <input
                id="no_bpjs_kesehatan"
                type="text"
                name="no_bpjs_kesehatan"
                value="{{ old('no_bpjs_kesehatan', optional($profile)->no_bpjs_kesehatan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kelas_bpjs" style="font-size:.9rem;font-weight:500;">Kelas BPJS</label>
            <input
                id="kelas_bpjs"
                type="text"
                name="kelas_bpjs"
                value="{{ old('kelas_bpjs', optional($profile)->kelas_bpjs) }}"
                placeholder="Misal: Kelas 1"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="masa_kerja" style="font-size:.9rem;font-weight:500;">Masa Kerja</label>
            <input
                id="masa_kerja"
                type="text"
                name="masa_kerja"
                value="{{ old('masa_kerja', optional($profile)->masa_kerja) }}"
                placeholder="Opsional, misal: 2 tahun"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_bergabung" style="font-size:.9rem;font-weight:500;">Tanggal Bergabung</label>
            <input
                id="tgl_bergabung"
                type="date"
                name="tgl_bergabung"
                value="{{ old('tgl_bergabung', optional(optional($profile)->tgl_bergabung)->format('Y-m-d')) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_akhir_percobaan" style="font-size:.9rem;font-weight:500;">Tanggal Berakhir Percobaan</label>
            <input
                id="tgl_akhir_percobaan"
                type="date"
                name="tgl_akhir_percobaan"
                value="{{ old('tgl_akhir_percobaan', optional(optional($profile)->tgl_akhir_percobaan)->format('Y-m-d')) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit"
                style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Update
            </button>
            <a href="{{ route('hr.employees.index') }}"
                style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>

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

            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) {
                backdrop.addEventListener('click', function(e) {
                    if (e.target === backdrop) {
                        backdrop.style.display = 'none';
                    }
                });
            });
        });
    </script>
</x-app>