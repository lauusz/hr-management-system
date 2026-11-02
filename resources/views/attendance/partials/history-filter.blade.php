{{--
    Filter rentang tanggal Riwayat Absensi (pola UI mengikuti filter /leave-requests).
    @param string $date_start  Y-m-d
    @param string $date_end    Y-m-d
    @param bool   $isFilterActive
--}}
@php
    $today = now();
    $periodLabel = \Carbon\Carbon::parse($date_start)->translatedFormat('j M Y')
        .($date_start !== $date_end ? ' – '.\Carbon\Carbon::parse($date_end)->translatedFormat('j M Y') : '');
    $quickRanges = [
        '7 Hari Terakhir' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
        'Bulan Ini' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
        'Bulan Lalu' => [
            $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
            $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
        ],
    ];
@endphp

<div class="lr-filter">
    <form method="GET" action="{{ route('attendance.history') }}" id="attendanceHistoryFilterForm">
        <div class="lr-filter-header ah-filter-header">
            <div class="ah-period" aria-live="polite">
                <span class="ah-period-label">Periode ditampilkan</span>
                <span class="ah-period-value">{{ $periodLabel }}</span>
            </div>
            <div class="ah-filter-buttons">
                <button type="button"
                    class="lr-btn-toggle-filter {{ $isFilterActive ? 'active' : '' }}"
                    id="attendanceHistoryFilterToggle"
                    aria-controls="attendanceHistoryFilterPanel"
                    aria-expanded="{{ $isFilterActive ? 'true' : 'false' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if($isFilterActive)
                        <span class="lr-filter-badge">1</span>
                    @endif
                </button>
                @if($isFilterActive)
                    <a href="{{ route('attendance.history') }}" class="lr-btn-reset" aria-label="Reset filter ke bulan ini">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        <div id="attendanceHistoryFilterPanel" class="lr-filter-panel" style="{{ $isFilterActive ? '' : 'display: none;' }}">
            <div class="lr-filter-body">
                <div class="lr-filter-group">
                    <label for="attendance_date_range" class="lr-filter-label">Tanggal Absensi</label>
                    <input type="text"
                        id="attendance_date_range"
                        value="{{ $date_start }} sampai {{ $date_end }}"
                        data-date-start="{{ $date_start }}"
                        data-date-end="{{ $date_end }}"
                        data-max-date="{{ $today->toDateString() }}"
                        placeholder="Pilih rentang tanggal"
                        class="lr-filter-input"
                        autocomplete="off"
                        readonly>
                    <input type="hidden" name="date_start" id="attendance_date_start" value="{{ $date_start }}">
                    <input type="hidden" name="date_end" id="attendance_date_end" value="{{ $date_end }}">
                </div>

                <div class="lr-filter-actions">
                    <button type="submit" class="lr-btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"/>
                        </svg>
                        Cari
                    </button>
                </div>
            </div>

            <div class="ah-quick" role="group" aria-label="Pilihan cepat periode">
                <span class="ah-quick-label">Pilihan cepat:</span>
                @foreach($quickRanges as $quickLabel => [$quickStart, $quickEnd])
                    @php $isQuickActive = $quickStart === $date_start && $quickEnd === $date_end; @endphp
                    <a href="{{ route('attendance.history', ['date_start' => $quickStart, 'date_end' => $quickEnd]) }}"
                        class="ah-quick-chip {{ $isQuickActive ? 'is-active' : '' }}"
                        @if($isQuickActive) aria-current="true" @endif>
                        {{ $quickLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    </form>
</div>
