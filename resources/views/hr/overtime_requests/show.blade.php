<x-app title="Detail Lembur (HR)">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Lembur</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan lembur karyawan</p>
            </div>
        </div>
    </x-slot>

    @include('hr.overtime_requests._detail')
</x-app>
