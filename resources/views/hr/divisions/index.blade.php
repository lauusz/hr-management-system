<x-app title="Master Divisi">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Daftar divisi yang terdaftar di sistem. Setiap divisi dapat memiliki supervisor.
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" action="{{ route('hr.divisions.index') }}" style="display:flex;gap:6px;align-items:center;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari divisi..."
                    style="padding:6px 8px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;">
                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:999px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                    Cari
                </button>
            </form>

            <a href="{{ route('hr.divisions.create') }}"
               style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Divisi
            </a>
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:640px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama Divisi
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Supervisor
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;width:150px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $division)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.9rem;font-weight:500;color:#111827;">
                                    {{ $division->name }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $division->supervisor->name ?? '-' }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.divisions.edit', $division->id) }}"
                                       style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-modal-target="delete-division-{{ $division->id }}"
                                            style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:16px;text-align:center;font-size:0.9rem;opacity:.7;">
                                Belum ada divisi ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $division)
        <x-modal
            id="delete-division-{{ $division->id }}"
            title="Hapus Divisi?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.divisions.destroy', $division->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus divisi berikut?
            </p>
            <p style="margin:0;font-weight:600;">
                {{ $division->name }}
            </p>
            <p style="margin:6px 0 0 0;font-size:0.85rem;opacity:.8;">
                Jika divisi ini masih digunakan oleh karyawan, hapus atau pindahkan karyawan terlebih dahulu.
            </p>
        </x-modal>
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
