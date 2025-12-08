<x-app title="Detail Hutang Saya">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <p style="font-size:.9rem;opacity:.75;margin:0;">
            Detail pengajuan hutang dan progres cicilan yang telah dicatat oleh HRD.
        </p>

        <a href="{{ route('employee.loan_requests.index') }}"
           style="font-size:.85rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $months > 0 ? floor($loan->amount / $months) : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);

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

    <div style="display:grid;grid-template-columns:2fr 1.5fr;gap:12px;align-items:flex-start;flex-wrap:wrap;">
        <div class="card" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
            <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                Data Pengajuan Hutang
            </div>

            <div style="display:grid;grid-template-columns:1fr;gap:6px;font-size:.85rem;">
                <div>
                    <div style="font-weight:500;">Status</div>
                    <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:500;background:{{ $bg }};color:{{ $color }};">
                        {{ $statusLabel }}
                    </span>
                    @if($loan->hrd_decided_at)
                        <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">
                            Diproses HRD: {{ $loan->hrd_decided_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>

                <div>
                    <div style="font-weight:500;">Tanggal Pengajuan</div>
                    <div>{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d/m/Y') }}</div>
                </div>

                <div>
                    <div style="font-weight:500;">Besar Pinjaman</div>
                    <div style="font-weight:600;">
                        Rp {{ number_format($loan->amount, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Keperluan</div>
                    <div>{{ $loan->purpose ?: '-' }}</div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;">
                    <div>
                        <div style="font-weight:500;">Tanggal Pinjam</div>
                        <div>
                            @if($loan->disbursement_date)
                                {{ \Illuminate\Support\Carbon::parse($loan->disbursement_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-weight:500;">Jangka Waktu Cicilan</div>
                        <div>
                            @if($months > 0)
                                {{ $months }} bulan
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Perkiraan Cicilan Per Bulan</div>
                    <div>
                        @if($monthlyInstallment)
                            Rp {{ number_format($monthlyInstallment, 0, ',', '.') }} per bulan
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Cara Pengembalian</div>
                    <div>
                        @if($loan->payment_method === 'TUNAI')
                            Tunai
                        @elseif($loan->payment_method === 'CICILAN')
                            Cicilan (transfer ke rekening perusahaan)
                        @elseif($loan->payment_method === 'POTONG_GAJI')
                            Pemotongan gaji
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Catatan HRD</div>
                    <div>{{ $loan->hrd_note ?: '-' }}</div>
                </div>

                <div>
                    <div style="font-weight:500;">Bukti Dokumen</div>
                    @if($loan->document_path)
                        <a href="{{ asset('storage/' . $loan->document_path) }}"
                           target="_blank"
                           style="font-size:.8rem;color:#1e4a8d;text-decoration:underline;">
                            Lihat dokumen
                        </a>
                    @else
                        <span>-</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="card" style="padding:12px;display:flex;flex-direction:column;gap:8px;">
                <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                    Ringkasan Hutang
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:6px;font-size:.85rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Total pinjaman</span>
                        <span>Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Total yang sudah dibayar</span>
                        <span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Sisa hutang</span>
                        <span>Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="card" style="padding:12px;display:flex;flex-direction:column;gap:8px;">
                <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                    Riwayat Cicilan / Potongan
                </div>

                <div style="margin-top:4px;">
                    @if($loan->repayments->isEmpty())
                        <div style="font-size:.8rem;color:#6b7280;">
                            Belum ada cicilan atau potongan yang dicatat oleh HRD.
                        </div>
                    @else
                        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                            <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                <th style="text-align:left;padding:6px 8px;">Cicilan ke</th>
                                <th style="text-align:left;padding:6px 8px;">Tanggal</th>
                                <th style="text-align:left;padding:6px 8px;">Nominal</th>
                                <th style="text-align:left;padding:6px 8px;">Metode</th>
                                <th style="text-align:left;padding:6px 8px;">Catatan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loan->repayments as $index => $repayment)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 8px;">{{ $index + 1 }}</td>
                                    <td style="padding:6px 8px;">
                                        {{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->format('d/m/Y') }}
                                    </td>
                                    <td style="padding:6px 8px;">
                                        Rp {{ number_format($repayment->amount, 0, ',', '.') }}
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @if($repayment->method === 'TUNAI')
                                            Tunai
                                        @elseif($repayment->method === 'TRANSFER')
                                            Transfer
                                        @elseif($repayment->method === 'POTONG_GAJI')
                                            Potong gaji
                                        @endif
                                    </td>
                                    <td style="padding:6px 8px;max-width:200px;">
                                        {{ $repayment->note ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app>
