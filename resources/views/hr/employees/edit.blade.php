<x-app title="Edit Karyawan">
    @if ($errors->any())
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form class="card"
        method="POST"
        action="{{ route('hr.employees.update', $item->id) }}"
        style="max-width:520px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf
        @method('PUT')

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Data Akun
        </div>

        {{-- NAMA --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $item->name) }}"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- USERNAME --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="username" style="font-size:.9rem;font-weight:500;">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username', $item->username) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- PHONE --}}
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

        {{-- ROLE --}}
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

        {{-- DIVISI --}}
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

        {{-- SHIFT (opsional) --}}
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

        {{-- STATUS --}}
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
            Password tidak ditampilkan di sini. Jika perlu reset, gunakan menu terpisah / fitur reset password.
        </div>

        <div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Informasi Karyawan
        </div>

        @php
        $profile = $item->profile;
        @endphp

        {{-- PT --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="pt" style="font-size:.9rem;font-weight:500;">PT</label>
            <select
                id="pt"
                name="pt"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;background:#fff;">
                <option value="">Pilih PT</option>
                @foreach($ptOptions as $ptOption)
                <option value="{{ $ptOption }}"
                    @selected(old('pt', $item->profile->pt ?? '') === $ptOption)>
                    {{ $ptOption }}
                </option>
                @endforeach
            </select>
        </div>


        {{-- KATEGORI --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kategori" style="font-size:.9rem;font-weight:500;">Kategori</label>
            <select
                id="kategori"
                name="kategori"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                @php $kategori = old('kategori', optional($profile)->kategori); @endphp
                <option value="">Pilih kategori</option>
                <option value="TETAP" @selected($kategori==='TETAP' )>Karyawan Tetap</option>
                <option value="KONTRAK" @selected($kategori==='KONTRAK' )>Karyawan Kontrak</option>
            </select>
        </div>

        {{-- NIK --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="nik" style="font-size:.9rem;font-weight:500;">NIK</label>
            <input
                id="nik"
                type="text"
                name="nik"
                value="{{ old('nik', optional($profile)->nik) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- WORK EMAIL --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="work_email" style="font-size:.9rem;font-weight:500;">Work Email</label>
            <input
                id="work_email"
                type="email"
                name="work_email"
                value="{{ old('work_email', optional($profile)->work_email) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- JABATAN → pakai master positions --}}
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

        {{-- KEWARGANEGARAAN --}}
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

        {{-- AGAMA --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="agama" style="font-size:.9rem;font-weight:500;">Agama</label>
            <input
                id="agama"
                type="text"
                name="agama"
                value="{{ old('agama', optional($profile)->agama) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- NO KK --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_kartu_keluarga" style="font-size:.9rem;font-weight:500;">No. Kartu Keluarga</label>
            <input
                id="no_kartu_keluarga"
                type="text"
                name="no_kartu_keluarga"
                value="{{ old('no_kartu_keluarga', optional($profile)->no_kartu_keluarga) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- NO KTP --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_ktp" style="font-size:.9rem;font-weight:500;">No. KTP</label>
            <input
                id="no_ktp"
                type="text"
                name="no_ktp"
                value="{{ old('no_ktp', optional($profile)->no_ktp) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- NAMA BANK --}}
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

        {{-- NO REKENING --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_rekening" style="font-size:.9rem;font-weight:500;">No. Rekening</label>
            <input
                id="no_rekening"
                type="text"
                name="no_rekening"
                value="{{ old('no_rekening', optional($profile)->no_rekening) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- PENDIDIKAN --}}
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

        {{-- JENIS KELAMIN --}}
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

        {{-- TANGGAL LAHIR --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_lahir" style="font-size:.9rem;font-weight:500;">Tanggal Lahir</label>
            <input
                id="tgl_lahir"
                type="date"
                name="tgl_lahir"
                value="{{ old('tgl_lahir', optional(optional($profile)->tgl_lahir)->format('Y-m-d')) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- TEMPAT LAHIR --}}
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

        {{-- ALAMAT 1 --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="alamat1" style="font-size:.9rem;font-weight:500;">Alamat 1</label>
            <textarea
                id="alamat1"
                name="alamat1"
                rows="2"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">{{ old('alamat1', optional($profile)->alamat1) }}</textarea>
        </div>

        {{-- ALAMAT 2 --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="alamat2" style="font-size:.9rem;font-weight:500;">Alamat 2</label>
            <textarea
                id="alamat2"
                name="alamat2"
                rows="2"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">{{ old('alamat2', optional($profile)->alamat2) }}</textarea>
        </div>

        {{-- PROVINSI --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="provinsi" style="font-size:.9rem;font-weight:500;">Provinsi</label>
            <input
                id="provinsi"
                type="text"
                name="provinsi"
                value="{{ old('provinsi', optional($profile)->provinsi) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- KAB / KOTA --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kab_kota" style="font-size:.9rem;font-weight:500;">Kab/Kota</label>
            <input
                id="kab_kota"
                type="text"
                name="kab_kota"
                value="{{ old('kab_kota', optional($profile)->kab_kota) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- KECAMATAN --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="kecamatan" style="font-size:.9rem;font-weight:500;">Kecamatan</label>
            <input
                id="kecamatan"
                type="text"
                name="kecamatan"
                value="{{ old('kecamatan', optional($profile)->kecamatan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- DESA / KELURAHAN --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="desa_kelurahan" style="font-size:.9rem;font-weight:500;">Desa/Kelurahan</label>
            <input
                id="desa_kelurahan"
                type="text"
                name="desa_kelurahan"
                value="{{ old('desa_kelurahan', optional($profile)->desa_kelurahan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- KODE POS --}}
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

        {{-- PTKP --}}
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

        {{-- NPWP --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_npwp" style="font-size:.9rem;font-weight:500;">No. NPWP</label>
            <input
                id="no_npwp"
                type="text"
                name="no_npwp"
                value="{{ old('no_npwp', optional($profile)->no_npwp) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- BPJS TK --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="bpjs_tk" style="font-size:.9rem;font-weight:500;">BPJS TK</label>
            <input
                id="bpjs_tk"
                type="text"
                name="bpjs_tk"
                value="{{ old('bpjs_tk', optional($profile)->bpjs_tk) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- BPJS KESEHATAN --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="no_bpjs_kesehatan" style="font-size:.9rem;font-weight:500;">No. BPJS Kesehatan</label>
            <input
                id="no_bpjs_kesehatan"
                type="text"
                name="no_bpjs_kesehatan"
                value="{{ old('no_bpjs_kesehatan', optional($profile)->no_bpjs_kesehatan) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- KELAS BPJS --}}
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

        {{-- MASA KERJA --}}
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

        {{-- TGL BERGABUNG --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_bergabung" style="font-size:.9rem;font-weight:500;">Tanggal Bergabung</label>
            <input
                id="tgl_bergabung"
                type="date"
                name="tgl_bergabung"
                value="{{ old('tgl_bergabung', optional(optional($profile)->tgl_bergabung)->format('Y-m-d')) }}"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        {{-- TGL AKHIR PERCOBAAN --}}
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="tgl_berakhir_percobaan" style="font-size:.9rem;font-weight:500;">Tanggal Berakhir Percobaan</label>
            <input
                id="tgl_berakhir_percobaan"
                type="date"
                name="tgl_berakhir_percobaan"
                value="{{ old('tgl_berakhir_percobaan', optional(optional($profile)->tgl_berakhir_percobaan)->format('Y-m-d')) }}"
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
</x-app>