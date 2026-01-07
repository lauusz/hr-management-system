<x-app title="Tambah Jabatan">

    <div class="form-container">

        @if ($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-title">Tambah Jabatan Baru</h2>
                    <p class="form-subtitle">Daftarkan nama jabatan dan hubungkan dengan divisi terkait.</p>
                </div>
                <a href="{{ route('hr.organization') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
            </div>

            <div class="divider"></div>

            <form class="form-content" method="POST" action="{{ route('hr.positions.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Jabatan <span class="req">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Senior Staff, Manager, Helper"
                        required>
                </div>

                <div class="form-group">
                    <label for="division_id">Divisi</label>
                    <select id="division_id" name="division_id" class="form-control">
                        <option value="">Tidak ada / Umum</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="checkbox-wrapper">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', 1))>
                        <span class="checkbox-label">Jabatan aktif dan dapat digunakan</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Simpan Jabatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Container Layout */
        .form-container {
            max-width: 550px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Alert Styling */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        /* Card System */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
        }

        .card-header {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .form-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .form-subtitle {
            margin: 4px 0 0 0;
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.4;
        }

        .divider {
            height: 1px;
            background: #f3f4f6;
            width: 100%;
        }

        .form-content {
            padding: 24px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label:not(.checkbox-wrapper) {
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
        }

        .req { color: #dc2626; }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            outline: none;
            background: #fff;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        /* Checkbox Styling */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 0;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1e4a8d;
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 14px;
            color: #374151;
        }

        /* Buttons */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .form-actions {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-primary {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 12px 24px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            min-width: 140px;
        }

        .btn-primary:hover {
            background: #163a75;
        }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .card-header {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn-back {
                align-self: flex-start;
            }

            .form-content {
                padding: 20px;
            }

            .btn-primary {
                width: 100%;
            }
        }
    </style>
</x-app>