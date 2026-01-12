<x-app title="Data Supervisor & Manager">

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Daftar Supervisor</h3>
                <p class="text-muted" style="margin: 4px 0 0; font-size: 13px;">
                    Orang-orang ini memiliki akses menu Approval.
                </p>
            </div>
            <a href="{{ route('hr.supervisors.create') }}" class="btn-add">
                + Tambah Supervisor
            </a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Jabatan & Divisi</th>
                        <th>Level Akses</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supervisors as $spv)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $spv->name }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ $spv->email }}</div>
                        </td>
                        <td>
                            <div class="fw-bold" style="font-size: 13px;">{{ $spv->position->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ $spv->division->name ?? '-' }}</div>
                        </td>
                        <td>
                            @if($spv->role === \App\Enums\UserRole::MANAGER)
                                <span class="badge-role manager">MANAGER</span>
                            @else
                                <span class="badge-role supervisor">SUPERVISOR</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('hr.supervisors.edit', $spv->id) }}" class="btn-action edit">
                                    Edit
                                </a>
                                
                                <form action="{{ route('hr.supervisors.destroy', $spv->id) }}" method="POST" onsubmit="return confirm('Yakin turunkan jabatan orang ini?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete">
                                        Demote
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            Belum ada data supervisor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $supervisors->links() }}
        </div>
    </div>
    
    <style>
        /* --- CARD & HEADER --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        /* --- BUTTONS --- */
        .btn-add {
            padding: 8px 16px;
            border-radius: 8px;
            background: #1e4a8d;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .btn-add:hover {
            background: #163a75;
        }

        /* --- TABLE STYLING --- */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .custom-table th,
        .custom-table td {
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            vertical-align: middle;
        }

        .custom-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        .custom-table tr:hover td {
            background: #fdfdfd;
        }

        .fw-bold {
            font-weight: 600;
            color: #1f2937;
        }

        .text-muted {
            color: #9ca3af;
        }
        
        .text-center {
            text-align: center !important;
        }

        /* --- BADGES --- */
        .badge-role {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .badge-role.manager {
            background: #dbeafe; /* Blue 100 */
            color: #1e40af;     /* Blue 800 */
        }

        .badge-role.supervisor {
            background: #fef3c7; /* Amber 100 */
            color: #92400e;      /* Amber 800 */
        }

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
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-action.edit {
            background: #fff;
            border-color: #d1d5db;
            color: #374151;
        }

        .btn-action.edit:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .btn-action.delete {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .btn-action.delete:hover {
            background: #fecaca;
        }

        /* --- UTILS --- */
        .empty-state {
            text-align: center;
            padding: 40px !important;
            color: #9ca3af;
            font-style: italic;
        }

        .pagination-wrapper {
            padding: 20px;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</x-app>