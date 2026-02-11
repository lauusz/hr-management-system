<x-app title="Master Jadwal Karyawan">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card mb-4">
        <form method="GET" action="{{ route('hr.schedules.index') }}" class="filter-container">
            
            <div class="filter-group flex-grow">
                <input type="text" 
                       name="q" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari nama karyawan..." 
                       class="form-control">
            </div>

            <div class="filter-group">
                <select name="pt_id" class="form-control">
                    <option value="">Semua PT</option>
                    @foreach($ptOptions as $ptOption)
                    <option value="{{ $ptOption->id }}" @selected(($pt ?? '' )==$ptOption->id)>
                        {{ $ptOption->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="position_id" class="form-control">
                    <option value="">Semua Jabatan</option>
                    @foreach($positionOptions as $pos)
                    <option value="{{ $pos->id }}" @selected(($positionId ?? '' )==$pos->id)>
                        {{ $pos->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="shift_id" class="form-control">
                    <option value="">Semua Shift</option>
                    <option value="none" @selected(($shiftId ?? '' )==='none' )>Belum Ada Jadwal</option>
                    @foreach($shiftOptions as $shift)
                    <option value="{{ $shift->id }}" @selected(($shiftId ?? '' )==$shift->id)>
                        {{ $shift->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Filter</button>
                
                @if(($search ?? null) || ($pt ?? null) || ($positionId ?? null) || ($shiftId ?? null))
                <a href="{{ route('hr.schedules.index') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Karyawan</th>
                        <th>PT</th>
                        <th>Shift Aktif</th>
                        <th>Lokasi</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>
                            <div class="user-info">
                                <span class="fw-bold">{{ $item->name }}</span>
                                <span class="text-muted">{{ $item->position_name ?? '-' }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="badge-basic">{{ $item->pt_name ?? '-' }}</span>
                        </td>

                        <td>
                            @if($item->shift_name)
                                <span class="badge-shift">{{ $item->shift_name }}</span>
                            @else
                                <span class="text-muted" style="font-size:12px; font-style:italic;">Belum diatur</span>
                            @endif
                        </td>

                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $item->location_name }}">
                                {{ $item->location_name ?? '-' }}
                            </div>
                        </td>

                        <td style="width: 100px;">
                            <div class="action-buttons">
                                @if($item->schedule_id)
                                    <a href="{{ route('hr.schedules.edit', $item->schedule_id) }}" class="btn-action edit">
                                        Edit
                                    </a>
                                    
                                    <button type="button" 
                                            data-modal-target="delete-schedule-{{ $item->schedule_id }}" 
                                            class="btn-action delete">
                                        Hapus
                                    </button>
                                @else
                                    <a href="{{ route('hr.schedules.create', ['user_id' => $item->id]) }}" class="btn-action primary">
                                        + Atur Jadwal
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            Tidak ada data karyawan ditemukan.
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
            <p style="margin:0; font-weight:700; color:#1f2937;">
                {{ $item->name }}
            </p>
            <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
                Karyawan tidak akan memiliki shift aktif setelah ini.
            </p>
        </x-modal>
        @endif
    @endforeach

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 11px; }
        .text-right { text-align: right; }
        .text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

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
            padding: 0;
        }

        /* --- FILTER SECTION --- */
        .filter-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .filter-group { flex: 1; min-width: 140px; }
        .filter-group.flex-grow { flex: 2; min-width: 200px; }

        .form-control {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            width: 100%;
            outline: none;
        }
        .form-control:focus { border-color: #1e4a8d; }

        .filter-actions { display: flex; gap: 8px; }

        .btn-primary {
            padding: 9px 18px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-primary:hover { background: #163a75; }

        .btn-reset {
            padding: 9px 16px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            display: inline-block;
        }
        .btn-reset:hover { background: #f9fafb; }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 900px; }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
            color: #1f2937;
            vertical-align: middle;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CONTENT STYLING --- */
        /* --- CONTENT STYLING --- */
        .user-info { display: flex; flex-direction: column; gap: 2px; text-align: left; align-items: flex-start; justify-content: flex-start; }
        .user-info .fw-bold { font-size: 13px; }
        
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
        }

        .badge-shift {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        /* --- ACTION BUTTONS --- */
        .action-buttons {
            display: flex;
            justify-content: flex-start;
            gap: 6px;
        }

        .btn-action {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            white-space: nowrap;
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

        .btn-action.primary {
            background: #1e4a8d;
            border-color: #1e4a8d;
            color: #fff;
        }
        .btn-action.primary:hover { background: #163a75; border-color: #163a75; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        @media(max-width: 768px) {
            .filter-container { flex-direction: column; align-items: stretch; }
            .filter-group { width: 100%; min-width: 0; }
            .filter-actions { margin-top: 4px; }
            .btn-primary, .btn-reset { flex: 1; text-align: center; }
        }
    </style>

</x-app>