<x-app title="Detail Hutang Karyawan">

    @if(session('success'))
        <div class="alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Header Page --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Detail Pengajuan Hutang</h1>
            <p class="page-subtitle">Kelola pengajuan, persetujuan, dan pencatatan cicilan karyawan.</p>
        </div>
        <a href="{{ route('hr.loan_requests.index') }}" class="btn-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $months > 0 ? floor($loan->amount / $months) : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);
        $percentage = $loan->amount > 0 ? min(100, round(($totalPaid / $loan->amount) * 100)) : 0;

        // Badge Logic
        $statusLabel = $loan->status;
        $badgeClass = 'badge-gray';
        if ($loan->status === 'PENDING_HRD') {
            $statusLabel = 'Menunggu Persetujuan HRD';
            $badgeClass = 'badge-yellow';
        } elseif ($loan->status === 'APPROVED') {
            $statusLabel = 'Disetujui HRD';
            $badgeClass = 'badge-blue';
        } elseif ($loan->status === 'REJECTED') {
            $statusLabel = 'Ditolak';
            $badgeClass = 'badge-red';
        } elseif ($loan->status === 'LUNAS') {
            $statusLabel = 'Lunas';
            $badgeClass = 'badge-green';
        }
    @endphp

    <div class="detail-grid">
        
        {{-- KOLOM KIRI: Informasi Utama --}}
        <div class="left-column">
            
            {{-- Card Data Karyawan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Karyawan</h3>
                </div>
                <div class="divider"></div>
                <div class="info-group">
                    <div class="info-row">
                        <div class="label">Nama Karyawan</div>
                        <div class="value font-bold">{{ $loan->snapshot_name }}</div>
                    </div>
                    <div class="info-grid-2">
                        <div class="info-row">
                            <div class="label">NIK</div>
                            <div class="value">{{ $loan->snapshot_nik ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">Jabatan</div>
                            <div class="value">{{ $loan->snapshot_position ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="label">Divisi / Departemen</div>
                        <div class="value">{{ $loan->snapshot_division ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">Perusahaan</div>
                        <div class="value">{{ $loan->snapshot_company ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Card Detail Pinjaman --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Pinjaman</h3>
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="divider"></div>
                <div class="info-group">
                    <div class="info-grid-2">
                        <div class="info-row">
                            <div class="label">Tgl. Pengajuan</div>
                            <div class="value">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d/m/Y') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">Tgl. Diproses</div>
                            <div class="value">
                                @if($loan->hrd_decided_at)
                                    {{ $loan->hrd_decided_at->format('d/m/Y H:i') }}
                                @else - @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">Besar Pinjaman</div>
                        <div class="value highlight-text">Rp {{ number_format($loan->amount, 0, ',', '.') }}</div>
                    </div>

                    <div class="info-row">
                        <div class="label">Keperluan</div>
                        <div class="value">{{ $loan->purpose ?: '-' }}</div>
                    </div>

                    <div class="info-grid-2">
                        <div class="info-row">
                            <div class="label">Tgl. Cair</div>
                            <div class="value">
                                @if($loan->disbursement_date)
                                    {{ \Illuminate\Support\Carbon::parse($loan->disbursement_date)->format('d/m/Y') }}
                                @else - @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">Tenor</div>
                            <div class="value">
                                @if($months > 0) {{ $months }} Bulan @else - @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">Estimasi Cicilan</div>
                        <div class="value">
                            @if($monthlyInstallment)
                                Rp {{ number_format($monthlyInstallment, 0, ',', '.') }} / bulan
                            @else - @endif
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">Metode Pembayaran</div>
                        <div class="value">
                            @if($loan->payment_method === 'TUNAI') Tunai
                            @elseif($loan->payment_method === 'CICILAN') Transfer
                            @elseif($loan->payment_method === 'POTONG_GAJI') Potong Gaji
                            @else - @endif
                        </div>
                    </div>
                    
                    @if($loan->document_path)
                    <div class="info-row" style="margin-top:10px;">
                        <a href="{{ asset('storage/' . $loan->document_path) }}" target="_blank" class="btn-download">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Lihat Dokumen Pendukung
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Aksi & History --}}
        <div class="right-column">
            
            {{-- CARD ACTION: APPROVAL (Only if Pending) --}}
            @if($loan->status === 'PENDING_HRD')
            <div class="card action-card">
                <h3 class="card-title-sm">Tindakan HRD</h3>
                <div class="divider"></div>
                <div class="action-body">
                    
                    <form action="{{ route('hr.loan_requests.approve', $loan->id) }}" method="POST" class="form-action">
                        @csrf
                        <div class="form-group">
                            <label for="hrd_note_approve">Catatan Persetujuan (Opsional)</label>
                            <textarea id="hrd_note_approve" name="hrd_note" rows="2" class="form-control" placeholder="Contoh: Disetujui, cair tanggal..."></textarea>
                        </div>
                        <button type="submit" class="btn-approve-full">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Pengajuan
                        </button>
                    </form>

                    <div class="divider-dashed"></div>

                    <form action="{{ route('hr.loan_requests.reject', $loan->id) }}" method="POST" class="form-action">
                        @csrf
                        <div class="form-group">
                            <label for="hrd_note_reject">Alasan Penolakan <span class="req">*</span></label>
                            <textarea id="hrd_note_reject" name="hrd_note" rows="2" class="form-control" required placeholder="Wajib diisi..."></textarea>
                        </div>
                        <button type="submit" class="btn-reject-full">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- CARD SUMMARY & REPAYMENT (If Approved/Lunas) --}}
            @if(in_array($loan->status, ['APPROVED', 'LUNAS']))
            <div class="card">
                <h3 class="card-title-sm">Status Pembayaran</h3>
                <div class="card-body-padded">
                    
                    {{-- Progress Bar --}}
                    <div class="progress-container">
                        <div class="progress-labels">
                            <span>Lunas: {{ $percentage }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <div class="summary-list">
                        <div class="summary-item">
                            <span>Total Pinjaman</span>
                            <strong>Rp {{ number_format($loan->amount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="summary-item text-green">
                            <span>Total Dibayar</span>
                            <strong>- Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong>
                        </div>
                        <div class="divider-dashed"></div>
                        <div class="summary-item total-item">
                            <span>Sisa Hutang</span>
                            <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    {{-- Form Catat Cicilan (Only if NOT LUNAS) --}}
                    @if($loan->status !== 'LUNAS')
                    <div class="repayment-form-wrapper">
                        <div class="form-section-title">Catat Cicilan / Potongan Baru</div>
                        <form action="{{ route('hr.loan_requests.repayments.store', $loan->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Tanggal Bayar</label>
                                <input type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Nominal (Rp)</label>
                                <input id="repayment_amount_display" type="text" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Rp 0" required>
                                <input id="repayment_amount" type="hidden" name="amount" value="{{ old('amount') }}">
                            </div>

                            <div class="form-group">
                                <label>Metode</label>
                                <select name="method" class="form-control" required>
                                    <option value="POTONG_GAJI" @selected(old('method', $loan->payment_method === 'POTONG_GAJI' ? 'POTONG_GAJI' : null) === 'POTONG_GAJI')>Potong Gaji</option>
                                    <option value="TRANSFER" @selected(old('method', $loan->payment_method === 'CICILAN' ? 'TRANSFER' : null) === 'TRANSFER')>Transfer Bank</option>
                                    <option value="TUNAI" @selected(old('method') === 'TUNAI')>Tunai</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Catatan (Opsional)</label>
                                <textarea name="note" rows="2" class="form-control" placeholder="Keterangan tambahan...">{{ old('note') }}</textarea>
                            </div>

                            <button type="submit" class="btn-primary-full">
                                Simpan Transaksi
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="lunas-message">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pinjaman ini sudah lunas.
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- CARD RIWAYAT --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title-sm">Riwayat Pembayaran</h3>
                </div>
                <div class="table-responsive">
                    @if($loan->repayments->isEmpty())
                        <div class="empty-state">Belum ada data pembayaran.</div>
                    @else
                        <table class="table-history">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->repayments as $index => $repayment)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->format('d/m/Y') }}</td>
                                    <td class="font-mono">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge-sm">
                                            @if($repayment->method === 'TUNAI') Tunai
                                            @elseif($repayment->method === 'TRANSFER') Transfer
                                            @elseif($repayment->method === 'POTONG_GAJI') Potong Gaji
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @if($repayment->note)
                                <tr>
                                    <td colspan="4" class="note-row"><small>Catatan: {{ $repayment->note }}</small></td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            function formatRupiahNumber(value) {
                if (!value || isNaN(value)) return '';
                return Number(value).toLocaleString('id-ID');
            }

            function updateRepaymentAmountFormatting() {
                var displayInput = document.getElementById('repayment_amount_display');
                var hiddenInput = document.getElementById('repayment_amount');
                if (!displayInput || !hiddenInput) return;

                var raw = displayInput.value || '';
                var digits = raw.replace(/\D/g, '');
                if (digits.length === 0) {
                    hiddenInput.value = '';
                    displayInput.value = '';
                    return;
                }

                var numeric = parseInt(digits);
                hiddenInput.value = numeric;
                displayInput.value = 'Rp ' + formatRupiahNumber(numeric);
            }

            document.addEventListener('DOMContentLoaded', function () {
                var displayInput = document.getElementById('repayment_amount_display');
                if (displayInput) {
                    displayInput.addEventListener('input', updateRepaymentAmountFormatting);
                    displayInput.addEventListener('blur', updateRepaymentAmountFormatting);
                }
                var hiddenInput = document.getElementById('repayment_amount');
                if (hiddenInput && hiddenInput.value && displayInput) {
                    displayInput.value = 'Rp ' + formatRupiahNumber(hiddenInput.value);
                }
            });
        </script>
    @endpush

    <style>
        /* --- UTILS --- */
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; }
        .page-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
        .page-subtitle { font-size: 14px; color: #6b7280; margin: 4px 0 0 0; }
        
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 14px; }
        
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 99px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .btn-download { display: inline-flex; align-items: center; gap: 6px; color: #1e4a8d; font-size: 13px; font-weight: 500; text-decoration: none; padding: 8px 12px; background: #eff6ff; border-radius: 8px; border: 1px solid transparent; width: 100%; justify-content: center; }
        .btn-download:hover { background: #dbeafe; }

        /* --- LAYOUT --- */
        .detail-grid { display: grid; grid-template-columns: 2fr 1.4fr; gap: 20px; align-items: start; }

        /* --- CARDS --- */
        .card { background: #fff; border-radius: 12px; border: 1px solid #f3f4f6; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 0; }
        .card-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
        .card-title-sm { font-size: 15px; font-weight: 700; color: #111827; margin: 0; padding: 16px 20px 0 20px; }
        .card-body-padded { padding: 20px; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        .divider-dashed { border-top: 1px dashed #d1d5db; margin: 8px 0; }

        /* --- INFO GROUPS --- */
        .info-group { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
        .info-row { display: flex; flex-direction: column; gap: 4px; }
        .info-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.03em; }
        .value { font-size: 14.5px; color: #1f2937; font-weight: 500; line-height: 1.4; }
        .font-bold { font-weight: 700; }
        .highlight-text { font-size: 18px; font-weight: 700; color: #111827; }
        .link-doc { display: inline-flex; align-items: center; gap: 5px; color: #1e4a8d; font-size: 13px; font-weight: 500; text-decoration: underline; }
        
        /* --- BADGES --- */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
        .badge-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .badge-red { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-green { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-sm { font-size: 11px; padding: 2px 6px; background: #f3f4f6; border-radius: 4px; color: #4b5563; font-weight: 500; }

        /* --- FORMS & INPUTS --- */
        .form-group { margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
        .req { color: #dc2626; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 2px rgba(30,74,141,0.1); }
        
        .action-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
        .btn-approve-full { width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 10px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .btn-approve-full:hover { background: #15803d; }
        .btn-reject-full { width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; padding: 10px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .btn-reject-full:hover { background: #b91c1c; }
        
        .repayment-form-wrapper { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .form-section-title { font-size: 13px; font-weight: 700; color: #1e4a8d; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.03em; }
        .btn-primary-full { width: 100%; padding: 10px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .btn-primary-full:hover { background: #163a75; }

        /* --- SUMMARY & PROGRESS --- */
        .progress-container { margin-bottom: 16px; }
        .progress-labels { display: flex; justify-content: flex-end; font-size: 12px; color: #6b7280; margin-bottom: 6px; font-weight: 500; }
        .progress-track { width: 100%; height: 8px; background: #f3f4f6; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: #10b981; border-radius: 4px; transition: width 0.5s ease; }

        .summary-list { display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #4b5563; }
        .summary-item { display: flex; justify-content: space-between; }
        .summary-item strong { color: #111827; }
        .text-green { color: #059669; }
        .total-item { font-size: 15px; color: #111827; }
        .total-item strong { color: #b91c1c; }

        /* --- TABLE HISTORY --- */
        .table-responsive { overflow-x: auto; margin-top: 10px; }
        .table-history { width: 100%; border-collapse: collapse; min-width: 350px; font-size: 13.5px; }
        .table-history th { text-align: left; padding: 10px 16px; background: #f9fafb; font-size: 12px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-history td { padding: 10px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .table-history tr:last-child td { border-bottom: none; }
        .font-mono { font-family: monospace; font-weight: 600; color: #111827; }
        .text-center { text-align: center; }
        .note-row td { background: #fafaf9; color: #6b7280; padding-top: 4px; padding-bottom: 12px; border-bottom: 1px solid #f3f4f6; }
        .empty-state { padding: 30px; text-align: center; color: #9ca3af; font-size: 13.5px; }

        .lunas-message { margin-top: 16px; background: #f0fdf4; border: 1px solid #dcfce7; color: #166534; padding: 12px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; }

        /* --- MOBILE --- */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .detail-grid { grid-template-columns: 1fr; gap: 16px; }
            .right-column { display: flex; flex-direction: column; gap: 16px; }
        }
    </style>
</x-app>