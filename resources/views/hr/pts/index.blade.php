<x-app title="Master PT">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="header-info">
                <h3 class="card-title">Daftar PT</h3>
                <p class="card-subtitle">Master data perusahaan untuk karyawan.</p>
            </div>
            <a href="{{ route('hr.pts.create') }}" class="btn-add">
                + Tambah PT
            </a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Perusahaan (PT)</th>
                        <th class="text-right" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $pt)
                    <tr>
                        <td class="fw-bold">{{ $pt->name }}</td>
                        <td class="text-right">
                            <div class="action-buttons">
                                <a href="{{ route('hr.pts.edit', $pt->id) }}" class="btn-action edit">
                                    Edit
                                </a>
                                
                                <button type="button" 
                                        data-modal-target="delete-pt-{{ $pt->id }}" 
                                        class="btn-action delete">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="empty-state">
                            Belum ada data PT yang terdaftar.
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
            Yakin ingin menghapus PT berikut?
        </p>
        <p style="margin:0; font-weight:700; color:#1f2937;">
            {{ $pt->name }}
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
            Pastikan PT ini tidak sedang digunakan oleh karyawan aktif.
        </p>
    </x-modal>
    @endforeach

    <style>
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

        .custom-table { width: 100%; border-collapse: collapse; min-width: 500px; }

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

        .fw-bold { font-weight: 600; color: #111827; }
        .text-right { text-align: right; }

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