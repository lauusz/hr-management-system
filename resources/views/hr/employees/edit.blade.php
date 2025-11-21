<x-app title="Edit Karyawan">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">Edit Karyawan</h1>
            <p style="font-size:.9rem;opacity:.75;">Perbarui informasi karyawan.</p>
        </div>
        <a href="{{ route('hr.employees.index') }}"
           style="font-size:.9rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    <form class="card" method="POST" action="{{ route('hr.employees.update', $item->id) }}" style="max-width:520px;">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                       style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
            </div>

            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Username</label>
                <input type="text" name="username" value="{{ old('username', $item->username) }}" required
                       style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
            </div>

            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $item->phone) }}" required
                       style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
            </div>

            <div>
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Role</label>
                <select name="role" required
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}"
                            @selected(old('role', $item->role) === $role->value)>
                            {{ $role->value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Divisi</label>
                <select name="division_id"
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                    <option value="">Tidak ada / Belum ditentukan</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->id }}"
                            @selected(old('division_id', $item->division_id) == $div->id)>
                            {{ $div->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div style="margin-top:18px;display:flex;gap:10px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan Perubahan
            </button>

            <a href="{{ route('hr.employees.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;">
                Batal
            </a>
        </div>
    </form>

</x-app>
