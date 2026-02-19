<x-app title="Master Payroll">
    <div class="card">
        <div class="card-header-simple">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h4 class="card-title-sm">
                        Master Payroll
                    </h4>
                    <p class="card-subtitle-sm">
                        Kelola data penggajian karyawan bulanan.
                    </p>
                </div>
                <div style="text-align:right;">
                    <a href="{{ route('hr.payroll.create') }}" class="btn-action btn-action-primary" style="padding: 6px 16px; font-size: 12px;">
                        + Input Gaji Manual
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="padding: 16px; background: #f9fafb; border-bottom: 1px solid #f3f4f6;">
            <form action="{{ route('hr.payroll.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; align-items: end;">
                <div>
                    <label for="month" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Bulan</label>
                    <select name="month" id="month" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="year" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Tahun</label>
                    @php
                    // Set tahun berapa aplikasi ini/perusahaan mulai beroperasi
                    $tahunMulai = 2026;
                    $tahunSekarang = date('Y');
                    @endphp

                    <select name="year" id="year" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach(range($tahunSekarang + 1, $tahunMulai) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pt_id" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Perusahaan (PT)</label>
                    <select name="pt_id" id="pt_id" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ $ptId == $pt->id ? 'selected' : '' }}>
                            {{ $pt->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-action btn-action-primary" style="width: 100%; justify-content: center; padding: 7px;">
                        Filter Data
                    </button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Karyawan</th>
                        <th style="min-width: 150px;">Jabatan & Divisi</th>
                        <th style="min-width: 100px;">Status</th>
                        <th style="text-align: right; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="employee-info">
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">{{ $employee->name }}</div>
                                    <div class="text-muted">{{ $employee->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #1f2937;">{{ $employee->position->name ?? $employee->profile->jabatan ?? '-' }}</div>
                            <div style="font-size: 11px; color: #6b7280;">{{ $employee->division->name ?? '-' }}</div>
                        </td>
                        <td>
                            @if($employee->payslip_status === 'PUBLISHED')
                            <span class="badge-type badge-green">PUBLISHED</span>
                            @elseif($employee->payslip_status === 'DRAFT')
                            <span class="badge-type badge-yellow">DRAFT</span>
                            @else
                            <span class="badge-type badge-red">BELUM DIBUAT</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($employee->latest_payslip)
                            <a href="{{ route('hr.payroll.edit', [
                                  'payslip' => $employee->latest_payslip->id,
                                'month' => request('month'),
                                'year' => request('year'),
                                'pt_id' => request('pt_id')
                            ]) }}" class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>
                            @else
                            <a href="{{ route('hr.payroll.create', ['user_id' => $employee->id, 'month' => $month, 'year' => $year, 'pt_id' => $ptId]) }}" class="btn-action btn-action-primary">
                                Input
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            Tidak ada data karyawan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- STYLES COPIED FROM supervisor/leave_requests/index.blade.php FOR CONSISTENCY -->
    <style>
        /* --- UTILITY --- */
        .fw-bold {
            font-weight: 600;
            color: #111827;
        }

        .text-muted {
            color: #6b7280;
            font-size: 12px;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            padding: 0;
            overflow: hidden;
        }

        .card-header-simple {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
        }

        .card-title-sm {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .card-subtitle-sm {
            margin: 4px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        /* --- TABLE --- */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 16px;
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
            font-size: 13px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover td {
            background: #fdfdfd;
        }

        /* --- BADGES --- */
        .badge-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-yellow {
            background: #fefce8;
            color: #a16207;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        /* --- ACTION BUTTONS --- */
        .btn-action {
            padding: 4px 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-action:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .btn-action-primary {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }

        .btn-action-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
            color: #fff;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }

        /* --- RESPONSIVE --- */
        @media screen and (max-width: 768px) {

            .custom-table,
            .custom-table tbody,
            .custom-table tr,
            .custom-table td {
                display: block;
                width: 100%;
            }

            .custom-table thead {
                display: none;
            }

            .custom-table tr {
                margin-bottom: 12px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 12px;
            }

            .custom-table td {
                padding: 8px 0;
                border: none;
            }
        }
    </style>
</x-app>