<x-app title="Master Jabatan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap;">
        <div>
            <p style="font-size:.9rem;opacity:.75;margin:0;">
                Daftar jabatan yang terdaftar di sistem. HR dapat mengelola jabatan per divisi.
            </p>
        </div>
        <a href="{{ route('hr.positions.create') }}"
           style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
            + Tambah Jabatan
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:720px;border-collapse:collapse;font-size:.9rem;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;width:56px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            No
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jabatan
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Divisi
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;width:140px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Status
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;width:150px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $index => $position)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:middle;">
                                {{ $positions->firstItem() + $index }}
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;">
                                    {{ $position->name }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $position->division?->name ?? 'Tidak ada / Umum' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                @if($position->is_active)
                                    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:#dcfce7;color:#166534;font-size:.78rem;font-weight:500;">
                                        Aktif
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:.78rem;font-weight:500;">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.positions.edit', $position->id) }}"
                                       style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-modal-target="delete-position-{{ $position->id }}"
                                            style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:16px 10px;text-align:center;font-size:.9rem;opacity:.7;">
                                Belum ada data jabatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($positions as $position)
        <x-modal
            id="delete-position-{{ $position->id }}"
            title="Hapus Jabatan?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.positions.destroy', $position->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus jabatan berikut?
            </p>
            <p style="margin:0;font-weight:600;">
                {{ $position->name }}
            </p>
            <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
                Pastikan tidak ada karyawan yang masih menggunakan jabatan ini.
            </p>
        </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$positions" />
    </div>

</x-app>
