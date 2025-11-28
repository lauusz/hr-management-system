<x-app title="Daftar Karyawan">

    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
        {{ session('success') }}
    </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Daftar karyawan yang terdaftar di sistem. HR dapat mengelola data karyawan.
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" action="{{ route('hr.employees.index') }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama..."
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;">

                <select
                    name="pt"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;background:#fff;">
                    <option value="">Semua PT</option>
                    @foreach($ptOptions as $ptOption)
                    <option value="{{ $ptOption }}" @selected(($pt ?? '' )===$ptOption)>
                        {{ $ptOption }}
                    </option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:999px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                    Cari
                </button>

                @if(($search ?? null) || ($pt ?? null))
                <a href="{{ route('hr.employees.index') }}"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:0.8rem;text-decoration:none;white-space:nowrap;">
                    Reset
                </a>
                @endif
            </form>

            <a href="{{ route('hr.employees.create') }}"
                style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Karyawan
            </a>
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:720px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            PT
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Role
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Status
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;width:170px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $emp)
                    <tr style="border-bottom:1px solid #f3f4f6;">

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <div style="display:flex;flex-direction:column;gap:2px;max-width:260px;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $emp->name }}
                                </span>
                                @if($emp->division?->name)
                                <span style="font-size:0.78rem;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $emp->division->name }}
                                </span>
                                @endif
                            </div>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:0.85rem;color:#374151;">
                                {{ $emp->profile->pt ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:0.85rem;color:#374151;">
                                {{ $emp->role ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:.78rem;font-weight:500;
                        background:#f3f4f6;color:#374151;">
                                {{ $emp->status ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                <a href="{{ route('hr.employees.edit', $emp->id) }}"
                                    style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                    Edit
                                </a>
                                <button type="button"
                                    data-modal-target="delete-employee-{{ $emp->id }}"
                                    style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                    Hapus
                                </button>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:16px;text-align:center;font-size:0.9rem;opacity:.7;">
                            Belum ada karyawan terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    @foreach($items as $emp)
    <x-modal
        id="delete-employee-{{ $emp->id }}"
        title="Hapus Karyawan?"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.employees.destroy', $emp->id)"
        confirmFormMethod="DELETE">
        <p style="margin:0 0 4px 0;">
            Yakin ingin menghapus karyawan berikut dari sistem?
        </p>
        <p style="margin:0;font-weight:600;">
            {{ $emp->name }}
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