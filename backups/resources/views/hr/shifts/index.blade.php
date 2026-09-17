<x-app title="Master Shift Kerja">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="header-info">
                <h3 class="card-title">Daftar Shift Kerja</h3>
                <p class="card-subtitle">Atur pola jam kerja harian untuk karyawan.</p>
            </div>
            <a href="{{ route('hr.shifts.create') }}" class="btn-add">
                + Tambah Shift
            </a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Shift</th>
                        <th>Status</th>
                        <th>Jumlah Hari Diatur</th>
                        <th class="text-right" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $sh)
                    <tr>
                        <td class="fw-bold">{{ $sh->name }}</td>
                        <td>
                            @if($sh->is_active)
                                <span class="badge-status active">Aktif</span>
                            @else
                                <span class="badge-status inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ $sh->days_count ?? $sh->days()->count() }} Hari
                        </td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <a href="{{ route('hr.shifts.edit', $sh->id) }}" class="btn-action edit">
                                    Edit
                                </a>

                                <button type="button"
                                    data-modal-target="delete-shift-{{ $sh->id }}"
                                    class="btn-action delete">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            Belum ada data shift kerja yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$items" />
    </div>

    @foreach($items as $sh)
    <x-modal
        id="delete-shift-{{ $sh->id }}"
        title="Hapus Shift?"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.shifts.destroy', $sh->id)"
        confirmFormMethod="DELETE">
        <p style="margin:0 0 4px 0;">
            Yakin ingin menghapus shift berikut?
        </p>
        <p style="margin:0; font-weight:700; color:#1f2937;">
            {{ $sh->name }}
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
            Perhatian: Jadwal karyawan yang menggunakan shift ini mungkin akan terpengaruh.
        </p>
    </x-modal>
    @endforeach

    <style>
        /* --- UTILITY --- */
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 14px; }
        .text-right { text-align: right; }

        /* --- ALERT --- */
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 14px;
        }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
            flex-wrap: wrap;
            gap: 12px;
        }

        .header-info h3 { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .header-info p { margin: 2px 0 0 0; font-size: 13px; color: #6b7280; }

        .btn-add {
            padding: 8px 16px;
            border-radius: 8px;
            background: #1e4a8d;
            color: #fff;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-add:hover { background: #163a75; }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 600px; }

        .custom-table th {
            background: #f9fafb;
            padding: 12px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- BADGES --- */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-status.active { background: #dcfce7; color: #166534; }
        .badge-status.inactive { background: #f3f4f6; color: #4b5563; }

        /* --- ACTION BUTTONS --- */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            line-height: 1.4;
        }

        .btn-action.edit {
            background: #fff;
            border-color: #d1d5db;
            color: #374151;
        }
        .btn-action.edit:hover { background: #f3f4f6; border-color: #9ca3af; }

        .btn-action.delete {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        .btn-action.delete:hover { background: #fecaca; }

        .empty-state {
            padding: 40px !important;
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }
    </style>

</x-app>