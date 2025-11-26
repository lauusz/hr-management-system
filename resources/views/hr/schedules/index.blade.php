<x-app title="Master Jadwal Karyawan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
        <div style="opacity:.7;font-size:.9rem;">
            Daftar jadwal shift karyawan berdasarkan tanggal.
        </div>

        <a href="{{ route('hr.schedules.create') }}"
           style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
            + Tambah Jadwal
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:640px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Karyawan
                        </th>
                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Shift
                        </th>
                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Lokasi
                        </th>
                        <th style="width:150px;text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <div style="display:flex;flex-direction:column;gap:2px;max-width:220px;">
                                    <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $item->user->name }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:0.78rem;font-weight:500;">
                                    {{ $item->shift->name ?? '-' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;display:inline-block;">
                                    {{ $item->location->name ?? '-' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.schedules.edit', $item->id) }}"
                                       style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>

                                    <button type="button"
                                            data-modal-target="delete-schedule-{{ $item->id }}"
                                            style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:14px;font-size:.9rem;opacity:.7;">
                                Belum ada jadwal karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $item)
        <x-modal
            id="delete-schedule-{{ $item->id }}"
            title="Hapus Jadwal?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.schedules.destroy', $item->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus jadwal untuk:
            </p>
            <p style="margin:0;font-weight:600;">
                {{ $item->user->name }}
            </p>
            <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
