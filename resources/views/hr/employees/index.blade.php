<x-app title="Daftar Karyawan">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="filter-container">
            <div class="filter-info">
                <h4>Total Karyawan: {{ $totalEmployees }}</h4>
                <p>Kelola data karyawan yang terdaftar dalam sistem.</p>
            </div>
            
            <div class="filter-actions">
                <a href="{{ route('hr.employees.create') }}" class="btn-add">
                    + Tambah Karyawan
                </a>
            </div>
        </div>

        <hr class="divider">

        <form method="GET" action="{{ route('hr.employees.index') }}" class="search-form">
            <div class="form-group search-input">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama karyawan..." class="form-control">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="pt_id" class="form-control">
                <option value="">Semua PT</option>
                @foreach($ptOptions as $ptOption)
                <option value="{{ $ptOption->id }}" @selected(($ptId ?? '' )==$ptOption->id)>
                    {{ $ptOption->name }}
                </option>
                @endforeach
            </select>

            <select name="kategori" class="form-control">
                <option value="">Semua Kategori</option>
                <option value="Karyawan Tetap" @selected(($kategori ?? '' )==='Karyawan Tetap')>Karyawan Tetap</option>
                <option value="Karyawan Kontrak" @selected(($kategori ?? '' )==='Karyawan Kontrak')>Karyawan Kontrak</option>
            </select>

            <select name="position_id" class="form-control">
                <option value="">Semua Jabatan</option>
                @foreach($positionOptions as $position)
                <option value="{{ $position->id }}" @selected(($positionId ?? '' )==$position->id)>
                    {{ $position->name }}
                </option>
                @endforeach
            </select>

            <label class="checkbox-wrapper">
                <input type="checkbox" name="near_expiry" value="1" onchange="this.form.submit()" @checked($nearExpiry ?? false)>
                <span>Kontrak mau habis</span>
            </label>

            <div class="btn-group">
                <button type="submit" class="btn-primary">Cari</button>
                
                @if(($search ?? null) || ($pt ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false))
                <a href="{{ route('hr.employees.index') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Nama / Divisi</th>
                        <th>Jabatan</th>
                        <th>PT</th>
                        <th>Kategori</th>
                        <th>Masa Kerja</th>
                        <th>Bergabung</th>
                        <th>Akhir Kontrak</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $emp)
                        @php
                            // Logic Status Warna
                            $statusRaw = $emp->status ?? '-';
                            $statusText = $statusRaw;
                            $statusBg = '#eef2ff'; // Default Blue soft
                            $statusColor = '#1e4a8d';

                            if ($statusRaw === 'INACTIVE') {
                                $labelMap = [
                                    'RESIGN' => 'Resign',
                                    'HABIS_KONTRAK' => 'Habis Kontrak',
                                    'PHK' => 'PHK',
                                    'PENSIUN' => 'Pensiun',
                                    'MENINGGAL' => 'Meninggal',
                                    'LAINNYA' => 'Nonaktif',
                                ];
                                $code = $emp->profile?->exit_reason_code;
                                $reasonLabel = $code ? ($labelMap[$code] ?? $code) : 'INACTIVE';
                                $statusText = $reasonLabel;
                                $statusBg = '#fee2e2'; // Red soft
                                $statusColor = '#b91c1c';
                            }

                            // Logic Masa Kerja
                            $masaKerjaDisplay = '-';
                            $joinDate = $emp->profile?->tgl_bergabung;
                            if ($joinDate) {
                                $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
                                $end = \Carbon\Carbon::today();
                                if ($end->greaterThanOrEqualTo($start)) {
                                    $diff = $start->diff($end);
                                    $parts = [];
                                    if ($diff->y > 0) $parts[] = $diff->y.' Thn';
                                    if ($diff->m > 0) $parts[] = $diff->m.' Bln';
                                    if (empty($parts)) $parts[] = $diff->d.' Hari';
                                    // Ambil 2 unit terbesar saja agar tidak terlalu panjang
                                    $masaKerjaDisplay = implode(' ', array_slice($parts, 0, 2));
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="user-info">
                                    <a href="{{ route('hr.employees.show', $emp->id) }}" class="user-name">
                                        {{ $emp->name }}
                                    </a>
                                    @if($emp->division?->name)
                                        <span class="user-sub">{{ $emp->division->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $emp->position?->name ?? '-' }}</td>
                            <td>
                                <span class="badge-pt">{{ $emp->profile?->pt?->name ?? '-' }}</span>
                            </td>
                            <td>{{ $emp->profile?->kategori ?? '-' }}</td>
                            <td class="text-muted">{{ $masaKerjaDisplay }}</td>
                            <td class="text-muted">{{ $emp->join_date_label ?? '-' }}</td>
                            <td class="text-muted">{{ $emp->probation_end_label ?? '-' }}</td>
                            <td>
                                <span class="badge-status" style="background: {{ $statusBg }}; color: {{ $statusColor }};">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('hr.employees.show', $emp->id) }}" class="btn-detail">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            Belum ada data karyawan yang ditemukan.
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

    <style>
        /* --- UTILITY & CARD --- */
        .mb-4 { margin-bottom: 16px; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-right { text-align: right; }
        
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            padding: 0;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 14px;
        }

        /* --- HEADER & FILTERS --- */
        .filter-container {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filter-info h4 { margin: 0 0 4px; font-size: 16px; color: #111827; }
        .filter-info p { margin: 0; font-size: 13px; color: #6b7280; }

        .btn-add {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #1e4a8d;
            color: #fff;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-add:hover { background: #163a75; }

        .divider { border: 0; border-top: 1px solid #f3f4f6; margin: 0; }

        .search-form {
            padding: 16px 20px;
            background: #f9fafb;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            min-width: 140px;
            outline: none;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 2px rgba(30,74,141,0.1); }

        .search-input { position: relative; }
        .search-input input { padding-left: 34px; min-width: 200px; }
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #374151;
            cursor: pointer;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }
        
        .btn-group { display: flex; gap: 8px; }
        
        .btn-primary {
            padding: 8px 16px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 500;
        }
        
        .btn-reset {
            padding: 8px 16px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            display: inline-block;
        }

        /* --- TABLE STYLING --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px; /* Agar kolom tidak gepeng */
        }

        .custom-table th {
            background: #f9fafb;
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13.5px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CONTENT FORMATTING --- */
        .user-info { display: flex; flex-direction: column; gap: 2px; }
        .user-name { font-weight: 600; color: #111827; text-decoration: none; }
        .user-name:hover { color: #1e4a8d; text-decoration: underline; }
        .user-sub { font-size: 12px; color: #6b7280; }

        .badge-pt {
            background: #f3f4f6;
            color: #374151;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #e5e7eb;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .btn-detail {
            padding: 6px 14px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            border-radius: 20px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-detail:hover { background: #f9fafb; border-color: #d1d5db; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        /* Mobile tweaks */
        @media(max-width: 768px) {
            .search-form { flex-direction: column; align-items: stretch; }
            .form-control, .search-input input { width: 100%; min-width: 0; }
            .btn-group { width: 100%; }
            .btn-primary, .btn-reset { flex: 1; text-align: center; }
        }
    </style>

</x-app>