<x-app title="Edit Shift">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('hr.shifts.update', $shift->id) }}"
          class="card"
          style="max-width:720px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:18px;">
        @csrf
        @method('PUT')

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.9rem;font-weight:500;">Nama Shift</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $shift->name) }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:0.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:0.9rem;font-weight:500;">Deskripsi</label>
            <textarea
                name="description"
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:0.9rem;min-height:70px;">{{ old('description', $shift->description) }}</textarea>
        </div>

        <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shift->is_active))>
            Aktif
        </label>

        @php
            $days = [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ];

            $dayData = $shift->days->keyBy('day_of_week');
        @endphp

        <div style="margin-top:10px;">
            <h3 style="font-size:1rem;margin-bottom:8px;">Pengaturan Shift per Hari</h3>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="background:#f3f4f6;text-align:left;">
                            <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Hari</th>
                            <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Jam Masuk</th>
                            <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Jam Pulang</th>
                            <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Libur</th>
                            <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $dayNumber => $dayName)
                            @php
                                $current = $dayData[$dayNumber] ?? null;

                                $oldStart = old("days.$dayNumber.start_time");
                                $oldEnd   = old("days.$dayNumber.end_time");

                                if ($oldStart !== null) {
                                    $startValue = $oldStart;
                                } elseif ($current && $current->start_time) {
                                    try {
                                        $startValue = \Carbon\Carbon::parse($current->start_time)->format('H:i');
                                    } catch (\Throwable $e) {
                                        $startValue = '';
                                    }
                                } else {
                                    $startValue = '';
                                }

                                if ($oldEnd !== null) {
                                    $endValue = $oldEnd;
                                } elseif ($current && $current->end_time) {
                                    try {
                                        $endValue = \Carbon\Carbon::parse($current->end_time)->format('H:i');
                                    } catch (\Throwable $e) {
                                        $endValue = '';
                                    }
                                } else {
                                    $endValue = '';
                                }

                                $oldHoliday = old("days.$dayNumber.is_holiday");
                                if ($oldHoliday !== null) {
                                    $isHoliday = (bool) $oldHoliday;
                                } else {
                                    $isHoliday = (bool) ($current?->is_holiday);
                                }

                                $noteValue = old("days.$dayNumber.note", $current?->note);
                            @endphp

                            <tr data-row="{{ $dayNumber }}">

                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    {{ $dayName }}
                                </td>

                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input
                                        type="time"
                                        name="days[{{ $dayNumber }}][start_time]"
                                        class="start-{{ $dayNumber }}"
                                        value="{{ $startValue }}"
                                        style="padding:6px;border-radius:6px;border:1px solid #d1d5db;width:120px;">
                                </td>

                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input
                                        type="time"
                                        name="days[{{ $dayNumber }}][end_time]"
                                        class="end-{{ $dayNumber }}"
                                        value="{{ $endValue }}"
                                        style="padding:6px;border-radius:6px;border:1px solid #d1d5db;width:120px;">
                                </td>

                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:center;">
                                    <input
                                        type="checkbox"
                                        name="days[{{ $dayNumber }}][is_holiday]"
                                        class="holiday-toggle"
                                        data-day="{{ $dayNumber }}"
                                        value="1"
                                        @checked($isHoliday)
                                        style="transform:scale(1.2);">
                                </td>

                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input
                                        type="text"
                                        name="days[{{ $dayNumber }}][note]"
                                        class="note-{{ $dayNumber }}"
                                        value="{{ $noteValue }}"
                                        style="padding:6px;border-radius:6px;border:1px solid #d1d5db;width:100%;">
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:14px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Update
            </button>

            <a href="{{ route('hr.shifts.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:inline-flex;align-items:center;justify-content:center;">
                Batal
            </a>
        </div>

    </form>

    <script>
        document.querySelectorAll('.holiday-toggle').forEach(cb => {
            const day = cb.dataset.day;

            const startInput = document.querySelector('.start-' + day);
            const endInput = document.querySelector('.end-' + day);
            const noteInput = document.querySelector('.note-' + day);

            const applyState = () => {
                if (cb.checked) {
                    startInput.value = '';
                    endInput.value = '';
                    noteInput.value = '';

                    startInput.disabled = true;
                    endInput.disabled = true;
                    noteInput.disabled = true;
                } else {
                    startInput.disabled = false;
                    endInput.disabled = false;
                    noteInput.disabled = false;
                }
            };

            applyState();

            cb.addEventListener('change', applyState);
        });
    </script>

</x-app>
