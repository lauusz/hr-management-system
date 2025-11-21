<x-app title="Pengaturan Akun">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card" style="max-width:460px; margin:0 auto;">
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:14px;">Ganti Password</h2>

        <form method="POST" action="{{ route('settings.password.update') }}" style="display:grid; gap:14px;">
            @csrf
            @method('PUT')

            {{-- Password Saat Ini --}}
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label style="font-size:0.9rem;">Password Saat Ini</label>
                <input type="password" name="current_password" required
                       style="padding:8px 10px;border-radius:8px;border:1px solid #ddd;">
            </div>

            {{-- Password Baru --}}
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label style="font-size:0.9rem;">Password Baru</label>
                <input type="password" name="password" required
                       style="padding:8px 10px;border-radius:8px;border:1px solid #ddd;">
            </div>

            {{-- Konfirmasi --}}
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label style="font-size:0.9rem;">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                       style="padding:8px 10px;border-radius:8px;border:1px solid #ddd;">
            </div>

            <button type="submit"
                    style="padding:8px 12px;background:#1e4a8d;color:#fff;border-radius:8px;border:none;font-size:0.9rem;cursor:pointer;">
                Simpan Password
            </button>
        </form>
    </div>

</x-app>
