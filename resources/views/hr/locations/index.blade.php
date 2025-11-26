<x-app title="Master Lokasi Presensi">
    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Master lokasi yang dipakai untuk presensi karyawan (gudang, depo, dsb).
        </div>
        <a href="{{ route('hr.locations.create') }}"
           style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
            + Tambah Lokasi
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:800px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama Lokasi
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Alamat
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Koordinat
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Radius (m)
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Status
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;width:150px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $loc)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;">
                                    {{ $loc->name }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#374151;display:inline-block;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $loc->address ?: '-' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.83rem;color:#4b5563;">
                                    {{ $loc->latitude }}, {{ $loc->longitude }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#111827;">
                                    {{ $loc->radius_meters }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:0.78rem;font-weight:500;
                                    {{ $loc->is_active ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' }}">
                                    {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.locations.edit', $loc->id) }}"
                                       style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-modal-target="delete-location-{{ $loc->id }}"
                                            style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:16px;font-size:0.9rem;opacity:.7;">
                                Belum ada lokasi presensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $loc)
        <x-modal
            id="delete-location-{{ $loc->id }}"
            title="Hapus Lokasi?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.locations.destroy', $loc->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus lokasi berikut?
            </p>
            <p style="margin:0;font-weight:600;">
                {{ $loc->name }}
            </p>
            <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
                Jika lokasi ini masih dipakai pada jadwal presensi, pastikan sudah disesuaikan terlebih dahulu.
            </p>
        </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
