<x-app title="Master Hutang Karyawan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div style="opacity:.8;font-size:.9rem;">
            Daftar seluruh pengajuan hutang karyawan yang masuk ke HRD.
        </div>

        <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.75rem;color:#6b7280;">Status</label>
                <select name="status"
                        style="padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;min-width:160px;">
                    <option value="">Semua</option>
                    <option value="PENDING_HRD" @selected(request('status') === 'PENDING_HRD')>Menunggu persetujuan HRD</option>
                    <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui HRD</option>
                    <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak HRD</option>
                </select>
            </div>

            <button type="submit"
                    style="margin-top:16px;padding:6px 12px;border-radius:8px;border:1px solid #d1d5db;background:#fff;font-size:.8rem;cursor:pointer;">
                Terapkan
            </button>
        </form>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Karyawan</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Besar Pinjaman</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Cara Pengembalian</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Tanggal Pengajuan</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Status</th>
                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#4b5563;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($loans as $loan)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        <div style="font-weight:600;">
                            {{ $loan->snapshot_name }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            NIK: {{ $loan->snapshot_nik ?? '-' }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            {{ $loan->snapshot_position ?? '-' }} • {{ $loan->snapshot_division ?? '-' }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            {{ $loan->snapshot_company ?? '-' }}
                        </div>
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        <div style="font-weight:600;">
                            Rp {{ number_format($loan->amount, 0, ',', '.') }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            {{ $loan->amount_in_words }}
                        </div>
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
                    <td style="padding:8px 12px;vertical-align:top;color:#111827;">
                        <div>
                            {{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d/m/Y') }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            Dibuat: {{ $loan->created_at->format('d/m/Y H:i') }}
                        </div>
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
                            }
                        @endphp
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:500;background:{{ $bg }};color:{{ $color }};">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td style="padding:8px 12px;vertical-align:top;">
                        <a href="{{ route('hr.loan_requests.show', $loan->id) }}"
                           style="font-size:.8rem;padding:6px 10px;border-radius:8px;border:1px solid #d1d5db;text-decoration:none;color:#111827;display:inline-block;">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:16px 12px;text-align:center;font-size:.85rem;color:#6b7280;">
                        Belum ada pengajuan hutang yang tercatat.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</x-app>
