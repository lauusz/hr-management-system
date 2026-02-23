<x-app title="Pengaturan Akses Master Payroll">
    <div class="card">
        <div class="card-header-simple">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h4 class="card-title-sm">
                        Pengaturan Akses Master Payroll
                    </h4>
                    <p class="card-subtitle-sm">
                        Tentukan siapa saja yang memiliki izin mengelola Data Penggajian.
                    </p>
                </div>
                <div style="text-align:right;">
                    <a href="{{ route('hr.payroll.index') }}" class="btn-action" style="padding: 6px 16px; font-size: 12px; cursor: pointer;">
                        Kembali ke Payroll
                    </a>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('hr.payroll.settings') }}" class="filter-container">
            <div class="filter-group flex-grow">
                <label>Cari Karyawan</label>
                <input type="text"
                    name="search"
                    id="search"
                    value="{{ $search }}"
                    placeholder="Ketik nama atau email..."
                    class="form-control"
                    autocomplete="off">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Cari</button>
                @if($search)
                <a href="{{ route('hr.payroll.settings') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>

        @if(session('success'))
        <div class="alert-success" style="margin: 0 20px 16px;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert-success" style="background: #fee2e2; color: #991b1b; border-color: #fecaca; margin: 0 20px 16px;">
            {{ session('error') }}
        </div>
        @endif

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Karyawan</th>
                        <th>Role Saat Ini</th>
                        <th class="text-right" style="width: 150px;">Aksi Akses Payroll</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="text-muted" style="text-align: center;">
                            {{ $users->firstItem() + $index }}
                        </td>
                        <td>
                            <span class="fw-bold">{{ $user->name }}</span><br>
                            <span class="text-muted">{{ $user->email }}</span>
                        </td>
                        <td>
                            <span class="badge-basic">
                                {{ $user->role->label() ?? $user->role->name ?? $user->role }}
                            </span>
                        </td>
                        <td class="text-right">
                            <form action="{{ route('hr.payroll.settings.update') }}" method="POST" style="margin: 0; display: inline-block;">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="can_manage_payroll" value="{{ $user->can_manage_payroll ? '0' : '1' }}">

                                @if($user->role === App\Enums\UserRole::HRD)
                                <button type="button" disabled style="padding: 4px 12px; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; background: #d1d5db; color: #4b5563; cursor: not-allowed; white-space: nowrap;">
                                    HR Manager
                                </button>
                                @else
                                @if($user->can_manage_payroll)
                                <button type="submit" onclick="return confirm('Cabut akses untuk karyawan ini?')" style="padding: 4px 12px; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; background: #fee2e2; color: #991b1b; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                    Cabut Akses
                                </button>
                                @else
                                <button type="submit" onclick="return confirm('Berikan akses Master Payroll untuk karyawan ini?')" style="padding: 4px 12px; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; background: #d1fae5; color: #065f46; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#a7f3d0'" onmouseout="this.style.background='#d1fae5'">
                                    Beri Akses
                                </button>
                                @endif
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <div style="margin-bottom: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5; margin: 0 auto;">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            Karyawan tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div style="margin-top: 20px;">
            <x-pagination :items="$users" />
        </div>
        @endif
    </div>

    <style>
        /* --- UTILITY --- */
        .mb-4 {
            margin-bottom: 16px;
        }

        .fw-bold {
            font-weight: 600;
            color: #111827;
        }

        .text-muted {
            color: #6b7280;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-date {
            font-weight: 500;
            color: #1f2937;
        }

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

        .card-header-simple {
            padding: 16px 20px 0;
            margin-bottom: 8px;
        }

        .card-title-sm {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
        }

        .card-subtitle-sm {
            margin: 2px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        /* --- FILTER SECTION --- */
        .filter-container {
            padding: 16px 20px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 160px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group.flex-grow {
            flex: 1.5;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #1e4a8d;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            padding-bottom: 2px;
        }

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

        .btn-primary:hover {
            background: #163a75;
        }

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

        .btn-reset:hover {
            background: #f9fafb;
        }

        /* --- TABLE --- */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
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

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover td {
            background: #fdfdfd;
        }

        /* --- BADGES --- */
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }

        /* --- ACTION BUTTONS --- */
        .btn-action {
            padding: 6px 14px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</x-app>