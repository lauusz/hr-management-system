<x-app title="Pengajuan Hutang Saya">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="opacity:.75;font-size:.9rem;">
            Daftar pengajuan hutang yang Anda ajukan kepada HRD.
        </div>

        <a href="{{ route('employee.loan_requests.create') }}"
           style="padding:6px 12px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:.85rem;text-decoration:none;white-space:nowrap;">
            + Ajukan Hutang
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Tanggal Pengajuan</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Besar Pinjaman</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Jangka Waktu</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Cara Pengembalian</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Status</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($loans as $loan)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        <div style="font-weight:500;">
                            {{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d/m/Y') }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            Dibuat: {{ $loan->created_at->format('d/m/Y H:i') }}
                        </div>
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        <div style="font-weight:600;">
                            Rp {{ number_format($loan->amount, 0, ',', '.') }}
                        </div>
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        @if($loan->repayment_term)
                            {{ $loan->repayment_term }} bulan
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        @if($loan->payment_method === 'TUNAI')
                            Tunai
                        @elseif($loan->payment_method === 'CICILAN')
                            Cicilan (transfer ke rekening perusahaan)
                        @elseif($loan->payment_method === 'POTONG_GAJI')
                            Pemotongan gaji
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;">
                        @php
                            $statusLabel = $loan->status;
                            $bg = '#e5e7eb';
                            $color = '#374151';

                            if ($loan->status === 'PENDING_HRD') {
                                $statusLabel = 'Menunggu persetujuan HRD';
                                $bg = '#fef3c7';
                                $color = '#92400e';
                            } elseif ($loan->status === 'APPROVED') {
                                $statusLabel = 'Disetujui HRD';
                                $bg = '#dcfce7';
                                $color = '#166534';
                            } elseif ($loan->status === 'REJECTED') {
                                $statusLabel = 'Ditolak HRD';
                                $bg = '#fee2e2';
                                $color = '#b91c1c';
                            } elseif ($loan->status === 'LUNAS') {
                                $statusLabel = 'Lunas';
                                $bg = '#bbf7d0';
                                $color = '#166534';
                            }
                        @endphp
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:500;background:{{ $bg }};color:{{ $color }};">
                            {{ $statusLabel }}
                        </span>
                        @if($loan->hrd_decided_at)
                            <div style="font-size:.75rem;color:#6b7280;margin-top:4px;">
                                Diproses: {{ $loan->hrd_decided_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;">
                        <a href="{{ route('employee.loan_requests.show', $loan->id) }}"
                           style="font-size:.8rem;padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;text-decoration:none;color:#111827;display:inline-block;">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:16px 12px;text-align:center;font-size:.85rem;color:#6b7280;">
                        Belum ada pengajuan hutang. Klik tombol "Ajukan Hutang" untuk membuat pengajuan baru.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</x-app>
