<x-app title="Master Shift Kerja">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Master Shift digunakan untuk mengatur jadwal kerja karyawan.
        </div>
        <a href="{{ route('hr.shifts.create') }}"
           style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
            + Tambah Shift
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:640px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama Shift
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jam Masuk
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jam Pulang
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;width:150px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $sh)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;">
                                    {{ $sh->name }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#111827;">
                                    {{ $sh->start_time_label }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#111827;">
                                    {{ $sh->end_time_label }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.shifts.edit', $sh->id) }}"
                                       style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>

                                    <button type="button"
                                            data-modal-target="delete-shift-{{ $sh->id }}"
                                            style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:16px;text-align:center;font-size:0.9rem;opacity:.7;">
                                Belum ada data shift kerja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $sh)
        <x-modal
            id="delete-shift-{{ $sh->id }}"
            title="Hapus Shift?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.shifts.destroy', $sh->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus shift berikut?
            </p>
            <p style="margin:0;font-weight:600;">
                {{ $sh->name }}
            </p>
            <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
                Tindakan ini akan mempengaruhi jadwal yang memakai shift ini.
            </p>
        </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
