<x-app title="Riwayat Lembur Saya">
    @if(session('success'))
        <div class="alert-success" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; margin-bottom: 20px;">
        <div class="card-header" style="padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f3f4f6;">
            <div class="header-info">
                <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #111827;">Riwayat Lembur</h3>
                <p class="card-subtitle" style="margin: 4px 0 0; font-size: 13.5px; color: #6b7280;">Daftar pengajuan lembur Anda.</p>
            </div>
            <a href="{{ route('overtime-requests.create') }}" class="btn-add" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; background: #1e4a8d; color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s;">
                + Ajukan Lembur
            </a>
        </div>

        <div class="table-wrapper" style="overflow-x: auto;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em;">Tanggal</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em;">Waktu</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em;">Durasi</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em;">Keterangan</th>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimes as $overtime)
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.15s;">
                            <td style="padding: 16px; vertical-align: middle;">
                                <div style="font-size: 14px; color: #111827; font-weight: 500;">
                                    {{ $overtime->overtime_date->format('d M Y') }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                    {{ $overtime->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td style="padding: 16px; vertical-align: middle; font-size: 14px; color: #374151;">
                                {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                            </td>
                            <td style="padding: 16px; vertical-align: middle;">
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #ecfdf5; color: #065f46;">
                                    {{ $overtime->duration_human }}
                                </span>
                            </td>
                            <td style="padding: 16px; vertical-align: middle;">
                                <p style="margin: 0; font-size: 13.5px; color: #4b5563; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $overtime->description }}">
                                    {{ Str::limit($overtime->description, 50) }}
                                </p>
                            </td>
                            <td style="padding: 16px; vertical-align: middle;">
                                @php
                                    $statusColor = match($overtime->status) {
                                        'APPROVED_HRD' => 'green',
                                        'REJECTED' => 'red',
                                        default => 'yellow',
                                    };
                                    $bgColor = match($statusColor) {
                                        'green' => '#ecfdf5',
                                        'red' => '#fef2f2',
                                        default => '#fefce8',
                                    };
                                    $textColor = match($statusColor) {
                                        'green' => '#065f46',
                                        'red' => '#991b1b',
                                        default => '#854d0e',
                                    };
                                @endphp
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: {{ $bgColor }}; color: {{ $textColor }};">
                                    {{ $overtime->status_label }}
                                </span>
                                @if($overtime->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtime->rejection_note)
                                    <div style="font-size: 11px; color: #dc2626; margin-top: 4px;">{{ $overtime->rejection_note }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 32px; text-align: center; color: #6b7280; font-size: 14px;">
                                Belum ada riwayat pengajuan lembur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($overtimes->hasPages())
                <div style="padding: 16px; border-top: 1px solid #f3f4f6;">
                    {{ $overtimes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app>
