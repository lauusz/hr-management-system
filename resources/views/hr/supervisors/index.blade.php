<x-app title="Data Supervisor & Manager">
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding:20px;">
            <div>
                <h3 style="margin:0; font-weight:700;">Daftar Supervisor</h3>
                <p style="margin:5px 0 0; color:#6b7280; font-size:0.9rem;">
                    Orang-orang ini memiliki akses menu Approval.
                </p>
            </div>
            {{-- [FIX] Ganti route jadi PLURAL (pake 's') --}}
            <a href="{{ route('hr.supervisors.create') }}" class="btn-primary">
                + Tambah Supervisor
            </a>
        </div>

        <div class="table-responsive">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb; text-align:left;">
                        <th style="padding:12px;">Nama</th>
                        <th style="padding:12px;">Jabatan & Divisi</th>
                        <th style="padding:12px;">Level Akses</th>
                        <th style="padding:12px; text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supervisors as $spv)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:12px;">
                            <div style="font-weight:600;">{{ $spv->name }}</div>
                            <small style="color:#6b7280;">{{ $spv->email }}</small>
                        </td>
                        <td style="padding:12px;">
                            <div>{{ $spv->position->name ?? '-' }}</div>
                            <small style="color:#6b7280;">{{ $spv->division->name ?? '-' }}</small>
                        </td>
                        <td style="padding:12px;">
                            @if($spv->role === \App\Enums\UserRole::MANAGER)
                                <span style="background:#dbeafe; color:#1e40af; padding:4px 8px; border-radius:99px; font-size:12px; font-weight:600;">MANAGER</span>
                            @else
                                <span style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:99px; font-size:12px; font-weight:600;">SUPERVISOR</span>
                            @endif
                        </td>
                        <td style="padding:12px; text-align:right;">
                            {{-- [FIX] Ganti route jadi PLURAL --}}
                            <a href="{{ route('hr.supervisors.edit', $spv->id) }}" style="color:#2563eb; text-decoration:none; margin-right:10px; font-weight:500;">Edit</a>
                            
                            {{-- [FIX] Ganti route jadi PLURAL --}}
                            <form action="{{ route('hr.supervisors.destroy', $spv->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin turunkan jabatan orang ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none; border:none; color:#dc2626; cursor:pointer; font-weight:500;">Demote</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px;">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div style="padding:20px;">
                {{ $supervisors->links() }}
            </div>
        </div>
    </div>
    
    <style>
        .btn-primary { background: #1e4a8d; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
        .btn-primary:hover { background: #163a75; }
    </style>
</x-app>