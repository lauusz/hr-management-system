<x-app title="Riwayat Absensi">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Riwayat Absensi</h1>
                <p class="section-subtitle">Catatan riwayat kehadiran harian Anda</p>
            </div>
        </div>
    </x-slot>

    @php
        $defaultStart = now()->startOfMonth()->toDateString();
        $defaultEnd = now()->toDateString();
        $isFilterActive = ($date_start !== $defaultStart) || ($date_end !== $defaultEnd);
    @endphp

    {{-- CTA --}}
    <div class="lr-cta-bar">
        <a href="{{ route('attendance.dashboard') }}" class="ah-btn-back">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Presensi
        </a>
    </div>

    {{-- FILTER --}}
    @include('attendance.partials.history-filter', [
        'date_start' => $date_start,
        'date_end' => $date_end,
        'isFilterActive' => $isFilterActive,
    ])

    {{-- RINGKASAN --}}
    @include('attendance.partials.history-stats', ['sessions' => $sessions])

    {{-- DAFTAR RIWAYAT --}}
    <div class="ah-list">
        @forelse($sessions as $session)
            @include('attendance.partials.history-session-card', ['session' => $session])
        @empty
            <div class="lr-empty">
                <div class="lr-empty-icon" aria-hidden="true">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="lr-empty-title">Belum Ada Riwayat Absensi</h3>
                <p class="lr-empty-desc">Tidak ditemukan catatan absensi pada rentang tanggal yang dipilih.</p>
                @if($isFilterActive)
                    <a href="{{ route('attendance.history') }}" class="ah-empty-btn">Lihat Bulan Ini</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Modal Bukti Kehadiran (mengikuti modal /hr/attendances) --}}
    @include('attendance.partials.history-modal')

    {{-- Penampil foto layar penuh (zoom & geser) --}}
    <x-image-viewer />

    @include('attendance.partials.history-styles')
    @include('attendance.partials.history-scripts')
</x-app>
