@php
    $dailyOptions = [
        'NONE' => 'Tanpa Potongan',
        'MEAL_ALLOWANCE' => 'Potong UM',
        'LEAVE_BALANCE_1' => 'Cuti 1 Hari',
        'LEAVE_BALANCE_0_5' => 'Cuti 0,5 Hari',
    ];
@endphp

<div class="edit-section" data-daily-treatment-table>
    <div class="edit-section-header">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
        </svg>
        <span>Perlakuan per Tanggal</span>
    </div>

    <div style="overflow:hidden; border:1px solid var(--border-light, #E5E7EB); border-radius:10px;">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:var(--gray-50, #F5F7FA); color:var(--text-secondary, #374151);">
                    <th style="padding:10px 12px; text-align:left; white-space:nowrap; width:112px;">Tanggal</th>
                    <th style="padding:10px 12px; text-align:left;">Perlakuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyTreatmentDays as $day)
                    @php
                        $dateKey = $day->leave_date->toDateString();
                        $selectedValue = match ($day->treatment) {
                            \App\Models\LeaveRequestDay::MEAL_ALLOWANCE => 'MEAL_ALLOWANCE',
                            \App\Models\LeaveRequestDay::LEAVE_BALANCE => (float) $day->deduction_amount === 0.5
                                ? 'LEAVE_BALANCE_0_5'
                                : 'LEAVE_BALANCE_1',
                            default => 'NONE',
                        };
                        $selectedValue = old("daily_treatments.$dateKey", $selectedValue);
                    @endphp
                    <tr style="border-top:1px solid var(--border-light, #E5E7EB);">
                        <td style="padding:10px 12px; font-weight:600; white-space:nowrap;">
                            {{ $day->leave_date->format('d/m/y') }}
                        </td>
                        <td style="padding:8px 12px;">
                            @php $selectId = $radioPrefix.'-'.str_replace('-', '', $dateKey).'-treatment'; @endphp
                            <select
                                id="{{ $selectId }}"
                                name="daily_treatments[{{ $dateKey }}]"
                                class="edit-form-select"
                                required
                                aria-label="{{ $day->leave_date->format('d/m/y') }} - Perlakuan"
                            >
                                @foreach($dailyOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedValue === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <small style="display:block; margin-top:8px; color:var(--text-muted, #6B7280); font-size:11px;">
        Pilih tepat satu perlakuan untuk setiap tanggal.
    </small>
</div>
