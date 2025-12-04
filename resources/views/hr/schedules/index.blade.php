<x-app title="Master Jadwal Karyawan">

    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
        <div style="opacity:.7;font-size:.9rem;">
            Daftar jadwal shift karyawan berdasarkan tanggal.
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET"
                action="{{ route('hr.schedules.index') }}"
                style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">

                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama / username..."
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;">

                <select
                    name="pt_id"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:150px;background:#fff;">
                    <option value="">Semua PT</option>
                    @foreach($ptOptions as $ptOption)
                    <option value="{{ $ptOption->id }}" @selected(($pt ?? '' )==$ptOption->id)>
                        {{ $ptOption->name }}
                    </option>
                    @endforeach
                </select>


                <select
                    name="position_id"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:150px;background:#fff;">
                    <option value="">Semua Jabatan</option>
                    @foreach($positionOptions as $pos)
                    <option value="{{ $pos->id }}" @selected(($positionId ?? '' )==$pos->id)>
                        {{ $pos->name }}
                    </option>
                    @endforeach
                </select>

                <select
                    name="shift_id"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:150px;background:#fff;">
                    <option value="">Semua Shift</option>
                    <option value="none" @selected(($shiftId ?? '' )==='none' )>Belum Ada Jadwal</option>
                    @foreach($shiftOptions as $shift)
                    <option value="{{ $shift->id }}" @selected(($shiftId ?? '' )==$shift->id)>
                        {{ $shift->name }}
                    </option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:999px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                    Cari
                </button>

                @if(($search ?? null) || ($pt ?? null) || ($positionId ?? null) || ($shiftId ?? null))
                <a href="{{ route('hr.schedules.index') }}"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:0.8rem;text-decoration:none;white-space:nowrap;">
                    Reset
                </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:760px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Karyawan
                        </th>

                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            PT
                        </th>

                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Shift
                        </th>

                        <th style="white-space:nowrap;text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Lokasi
                        </th>

                        <th style="width:170px;text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $item)
                    <tr style="border-bottom:1px solid #f3f4f6;">

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <div style="display:flex;flex-direction:column;gap:2px;max-width:260px;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->name }}
                                </span>
                                <span style="font-size:0.78rem;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $item->position_name ?? '-' }}
                                </span>
                            </div>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;white-space:nowrap;">
                            <span style="font-size:0.85rem;color:#374151;">
                                {{ $item->pt_name ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($item->shift_name)
                            <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:0.78rem;font-weight:500;">
                                {{ $item->shift_name }}
                            </span>
                            @else
                            <span style="font-size:0.8rem;color:#9ca3af;">Belum diatur</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:0.85rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;display:inline-block;">
                                {{ $item->location_name ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                @if($item->schedule_id)
                                <a href="{{ route('hr.schedules.edit', $item->schedule_id) }}"
                                    style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                    Edit
                                </a>
                                <button type="button"
                                    data-modal-target="delete-schedule-{{ $item->schedule_id }}"
                                    style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                    Hapus
                                </button>
                                @else
                                <a href="{{ route('hr.schedules.create', ['user_id' => $item->id]) }}"
                                    style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                    Atur Jadwal
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:14px;font-size:.9rem;opacity:.7;">
                            Belum ada data karyawan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    @foreach($items as $item)
    @if($item->schedule_id)
    <x-modal
        id="delete-schedule-{{ $item->schedule_id }}"
        title="Hapus Jadwal?"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.schedules.destroy', $item->schedule_id)"
        confirmFormMethod="DELETE">
        <p style="margin:0 0 4px 0;">
            Yakin ingin menghapus jadwal untuk:
        </p>
        <p style="margin:0;font-weight:600;">
            {{ $item->name }}
        </p>
        <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
            Tindakan ini tidak dapat dibatalkan.
        </p>
    </x-modal>
    @endif
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>