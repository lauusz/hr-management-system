<x-app title="Master PT">

    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
            <p style="font-size:.9rem;opacity:.75;margin:0;">
                Daftar PT yang digunakan di data karyawan.
            </p>
        </div>
        <a href="{{ route('hr.pts.create') }}"
           style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:.85rem;text-decoration:none;white-space:nowrap;">
            + Tambah PT
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:520px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama PT
                        </th>
                        <th style="width:160px;text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $pt)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:.9rem;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;max-width:360px;">
                                {{ $pt->name }}
                            </span>
                        </td>
                        <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                <a href="{{ route('hr.pts.edit', $pt->id) }}"
                                   style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                    Edit
                                </a>
                                <button type="button"
                                        data-modal-target="delete-pt-{{ $pt->id }}"
                                        style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="padding:14px;text-align:center;font-size:.9rem;opacity:.7;">
                            Belum ada data PT terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $pt)
    <x-modal
        id="delete-pt-{{ $pt->id }}"
        title="Hapus PT?"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.pts.destroy', $pt->id)"
        confirmFormMethod="DELETE">
        <p style="margin:0 0 4px 0;">
            Yakin ingin menghapus PT berikut dari master?
        </p>
        <p style="margin:0;font-weight:600;">
            {{ $pt->name }}
        </p>
        <p style="margin:6px 0 0 0;font-size:.85rem;opacity:.8;">
            PT yang sudah tidak digunakan di data karyawan aman untuk dihapus.
        </p>
    </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
