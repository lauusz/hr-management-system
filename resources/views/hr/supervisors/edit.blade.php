<x-app title="Edit Level Pejabat">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header" style="padding:20px; border-bottom:1px solid #f3f4f6;">
            <h3 style="margin:0;">Edit Jabatan: {{ $user->name }}</h3>
        </div>
        
        {{-- [FIX] Route pakai 'supervisors' --}}
        <form action="{{ route('hr.supervisors.update', $user->id) }}" method="POST" style="padding:20px;">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Nama Pejabat</label>
                <input type="text" value="{{ $user->name }}" disabled 
                       style="width:100%; padding:10px; background:#f3f4f6; border:1px solid #d1d5db; border-radius:8px; color:#6b7280;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Level Jabatan</label>
                <select name="role" class="form-control" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                    <option value="SUPERVISOR" {{ $user->role == \App\Enums\UserRole::SUPERVISOR ? 'selected' : '' }}>SUPERVISOR</option>
                    <option value="MANAGER" {{ $user->role == \App\Enums\UserRole::MANAGER ? 'selected' : '' }}>MANAGER</option>
                </select>
            </div>

            <div style="text-align:right; margin-top:30px;">
                {{-- [FIX] Route pakai 'supervisors' --}}
                <a href="{{ route('hr.supervisors.index') }}" style="color:#6b7280; text-decoration:none; margin-right:15px;">Batal</a>
                <button type="submit" class="btn-primary">Update Jabatan</button>
            </div>
        </form>
    </div>
    <style>
        .btn-primary { background: #1e4a8d; color: white; padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
    </style>
</x-app>