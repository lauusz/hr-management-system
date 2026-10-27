{{-- Satu baris karyawan: tampil sebagai baris tabel di desktop, kartu ringkas di mobile. --}}
@php
    $profile = $emp->profile;

    $statusLabel = 'Aktif';
    $statusClass = 'emp-badge--success';
    if (($emp->status ?? null) === 'INACTIVE') {
        $exitLabels = [
            'RESIGN' => 'Resign',
            'HABIS_KONTRAK' => 'Habis Kontrak',
            'PHK' => 'PHK',
            'PENSIUN' => 'Pensiun',
            'MENINGGAL' => 'Meninggal',
            'LAINNYA' => 'Nonaktif',
        ];
        $exitCode = $profile?->exit_reason_code;
        $statusLabel = $exitCode ? ($exitLabels[$exitCode] ?? $exitCode) : 'Nonaktif';
        $statusClass = 'emp-badge--error';
    }
    $isActive = $statusClass === 'emp-badge--success';

    $joinDate = $profile?->tgl_bergabung ? \Carbon\Carbon::parse($profile->tgl_bergabung)->startOfDay() : null;
    $masaKerja = '-';
    if ($joinDate && \Carbon\Carbon::today()->greaterThanOrEqualTo($joinDate)) {
        $diff = $joinDate->diff(\Carbon\Carbon::today());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' thn';
        if ($diff->m > 0) $parts[] = $diff->m . ' bln';
        if (empty($parts)) $parts[] = $diff->d . ' hari';
        $masaKerja = implode(' ', array_slice($parts, 0, 2));
    }

    $categoryLabels = ['TETAP' => 'Tetap', 'KONTRAK' => 'Kontrak', 'MAGANG' => 'Magang'];
    $categoryLabel = $categoryLabels[$profile?->kategori ?? ''] ?? ($profile?->kategori ?: '-');

    $contractEnd = $profile?->tgl_akhir_percobaan ? \Carbon\Carbon::parse($profile->tgl_akhir_percobaan)->startOfDay() : null;
    $contractClass = '';
    if ($contractEnd && $isActive) {
        $daysLeft = \Carbon\Carbon::today()->diffInDays($contractEnd, false);
        $contractClass = $daysLeft < 0 ? 'is-expired' : ($daysLeft <= 30 ? 'is-near' : '');
    }

    $leaveBalance = rtrim(rtrim(sprintf('%.1f', $emp->leave_balance ?? 0), '0'), '.');
@endphp

<tr class="emp-row {{ $isActive ? '' : 'is-inactive' }}">
    <td class="emp-td emp-td--name">
        <div class="emp-name-line">
            <a href="{{ route('hr.employees.show', $emp->id) }}" class="emp-name">{{ $emp->name }}</a>
            <span class="emp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <div class="emp-sub emp-sub--contact">
            {{ $emp->email ?? '-' }}@if($emp->phone) · {{ $emp->phone }}@endif
        </div>
    </td>

    <td class="emp-td" data-label="Jabatan">
        <div class="emp-main-text">{{ $emp->position?->name ?? '-' }}</div>
        <div class="emp-sub">{{ $emp->division?->name ?? '-' }}</div>
    </td>

    <td class="emp-td" data-label="PT">
        <div class="emp-main-text">{{ $profile?->pt?->name ?? '-' }}</div>
    </td>

    <td class="emp-td" data-label="Masa Kerja">
        <div class="emp-main-text">{{ $masaKerja }}</div>
        <div class="emp-sub">{{ $joinDate ? $joinDate->translatedFormat('j M Y') : '-' }}</div>
    </td>

    <td class="emp-td" data-label="Kategori">
        <div class="emp-main-text">{{ $categoryLabel }}</div>
        @if($contractEnd)
        <div class="emp-sub emp-contract {{ $contractClass }}">Berakhir: {{ $contractEnd->translatedFormat('j M Y') }}</div>
        @endif
    </td>

    <td class="emp-td emp-td--num" data-label="Sisa Cuti">
        <button type="button" class="emp-leave emp-leave--button {{ $leaveBalance === '0' ? 'is-zero' : '' }}"
            data-leave-balance-history-url="{{ route('hr.employees.leave-balance-history', $emp) }}"
            data-leave-balance-employee="{{ $emp->name }}" aria-haspopup="dialog" title="Lihat riwayat saldo cuti {{ $emp->name }}">
            {{ $leaveBalance }}
        </button>
    </td>

    <td class="emp-td emp-td--action">
        <a href="{{ route('hr.employees.show', $emp->id) }}" class="emp-btn-detail">Detail</a>
    </td>
</tr>
