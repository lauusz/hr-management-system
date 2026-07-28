<x-app title="Edit Jadwal Karyawan">

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
                    <h2 class="form-title">Edit Jadwal</h2>
                    <p class="form-subtitle">Perbarui data shift atau lokasi presensi karyawan.</p>
                </div>
                <a href="{{ route('hr.schedules.index') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
            </div>

            <div class="divider"></div>

            <form action="{{ route('hr.schedules.update', $schedule->id) }}" method="POST" class="form-content">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Karyawan</label>
                    <div class="readonly-input">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#6b7280"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ $schedule->user->name }}</span>
                    </div>
                    <input type="hidden" name="user_id" value="{{ $schedule->user_id }}">
                    <small class="helper-text">Data karyawan tidak dapat diubah pada menu edit.</small>
                </div>

                <div class="form-group">
                    <label for="shift_id">Shift Kerja <span class="req">*</span></label>
                    <select name="shift_id" id="shift_id" class="form-control" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" @selected($schedule->shift_id == $s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_id">Lokasi Presensi <span class="req">*</span></label>
                    <select name="location_id" id="location_id" class="form-control" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected($schedule->location_id == $loc->id)>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Update Jadwal
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
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
        }

        /* Card System */
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6; overflow: hidden;
        }

        .card-header {
            padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
        }

        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0 0; font-size: 13.5px; color: #6b7280; line-height: 1.4; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        
        .form-content { padding: 24px; }

        /* Form Controls */
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .req { color: #dc2626; }

        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; background: #fff; color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }

        /* Readonly Box Styling */
        .readonly-input {
            padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
            background: #f9fafb; color: #374151; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .helper-text { font-size: 12px; color: #6b7280; margin-top: 2px; }

        /* Buttons */
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
            border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151;
            font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .form-actions { margin-top: 10px; display: flex; justify-content: flex-end; }
        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; min-width: 140px;
        }
        .btn-primary:hover { background: #163a75; }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 20px; }
            .btn-primary { width: 100%; }
        }
    </style>
</x-app>