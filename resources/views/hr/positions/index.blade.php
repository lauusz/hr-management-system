<x-app title="Master Jabatan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div>
            <p style="font-size:.9rem;opacity:.75;margin:0;">
                Daftar jabatan yang terdaftar di sistem. HR dapat mengelola jabatan per divisi.
            </p>
        </div>
        <a href="{{ route('hr.positions.create') }}"
               style="padding:6px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Jabatan
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr style="background:#f3f4f6;">
                <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;width:48px;">No</th>
                <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Jabatan</th>
                <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;">Divisi</th>
                <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;width:120px;">Status</th>
                <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb;width:140px;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($positions as $index => $position)
                <tr>
                    <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                        {{ $positions->firstItem() + $index }}
                    </td>
                    <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                        {{ $position->name }}
                    </td>
                    <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                        {{ $position->division?->name ?? 'Tidak ada / Umum' }}
                    </td>
                    <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                        @if($position->is_active)
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-size:.75rem;">
                                Aktif
                            </span>
                        @else
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:.75rem;">
                                Nonaktif
                            </span>
                        @endif
                    </td>
                    <td style="padding:8px 10px;border-bottom:1px solid #f3f4f6;">
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('hr.positions.edit', $position->id) }}"
                               style="padding:4px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;">
                                Edit
                            </a>
                            <form method="POST"
                                  action="{{ route('hr.positions.destroy', $position->id) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus jabatan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="padding:4px 10px;border-radius:999px;border:1px solid #fca5a5;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:12px 10px;text-align:center;font-size:.9rem;opacity:.7;">
                        Belum ada data jabatan.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($positions->hasPages())
        <div style="margin-top:10px;">
            {{ $positions->links() }}
        </div>
    @endif

</x-app>
