{{--
    Ringkasan angka Riwayat Absensi (pola UI mengikuti .lr-stats di /leave-requests).
    @param \Illuminate\Support\Collection $sessions
--}}
@php
    $summary = [
        ['value' => $sessions->count(), 'label' => 'Hari Tercatat', 'class' => ''],
        ['value' => $sessions->filter(fn ($s) => $s->clock_in && $s->clock_out)->count(), 'label' => 'Lengkap', 'class' => 'lr-stat-value--success'],
        ['value' => $sessions->filter(fn ($s) => $s->clock_in && ! $s->clock_out)->count(), 'label' => 'Belum Pulang', 'class' => 'lr-stat-value--muted'],
        ['value' => $sessions->where('has_pending', true)->count(), 'label' => 'Menunggu', 'class' => 'lr-stat-value--warning'],
    ];
@endphp

<div class="lr-stats" aria-label="Ringkasan absensi pada periode ini">
    @foreach($summary as $index => $item)
        @if($index > 0)
            <div class="lr-stat-divider" aria-hidden="true"></div>
        @endif
        <div class="lr-stat">
            <div class="lr-stat-value {{ $item['class'] }}">{{ $item['value'] }}</div>
            <div class="lr-stat-label">{{ $item['label'] }}</div>
        </div>
    @endforeach
</div>
