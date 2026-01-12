<x-app title="Tambah Supervisor">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid #f3f4f6;">
            <h3 style="margin:0;">Tambah Supervisor / Manager</h3>
        </div>
        
        {{-- [FIX] Route pakai 'supervisors' --}}
        <form action="{{ route('hr.supervisors.store') }}" method="POST" style="padding:20px;">
            @csrf
            
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Pilih Karyawan</label>
                <select name="user_id" class="form-control" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                    <option value="">-- Pilih Nama --</option>
                    @foreach($candidates as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->name }} ({{ $c->division->name ?? 'No Div' }})
                        </option>
                    @endforeach
                </select>
                <small style="color:#6b7280;">Hanya menampilkan karyawan biasa (Employee).</small>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Level Jabatan Baru</label>
                <select name="role" class="form-control" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                    <option value="SUPERVISOR">SUPERVISOR</option>
                    <option value="MANAGER">MANAGER</option>
                </select>
            </div>

            <div style="text-align:right; margin-top:30px;">
                {{-- [FIX] Route pakai 'supervisors' --}}
                <a href="{{ route('hr.supervisors.index') }}" style="color:#6b7280; text-decoration:none; margin-right:15px;">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
    
    <style>
        .btn-primary { background: #1e4a8d; color: white; padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</x-app>