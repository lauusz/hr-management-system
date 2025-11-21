<x-app title="Tambah Karyawan">
    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form class="card"
          method="POST"
          action="{{ route('hr.employees.store') }}"
          style="max-width:480px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="username" style="font-size:.9rem;font-weight:500;">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="phone" style="font-size:.9rem;font-weight:500;">No. Telepon</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone') }}"
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
                    <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
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
                    <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top:4px;font-size:.8rem;opacity:.7;">
            Password default karyawan baru adalah <b>123456</b>. Disarankan diganti setelah login pertama.
        </div>

        <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('hr.employees.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>
</x-app>
