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
        :root {
            --bg-soft: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --card-border: #e5e7eb;
            --brand: #1e40af;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 22px;
        }

        .page-title {
            margin: 0;
            font-size: 24px;
            line-height: 1.2;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .page-subtitle {
            margin: 6px 0 0;
            font-size: 14px;
            color: var(--text-muted);
        }

        .alert-success,
        .alert-error {
            padding: 12px 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s ease;
            white-space: nowrap;
        }

        .btn-back:hover { border-color: #cbd5e1; background: var(--bg-soft); }

        .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s ease;
        }

        .btn-download:hover { background: #dbeafe; }

        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.35fr);
            gap: 20px;
            align-items: start;
        }

        .left-column,
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .card-title,
        .card-title-sm {
            margin: 0;
            padding: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-title-sm {
            padding: 16px 18px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-body-padded { padding: 16px 18px 18px; }
        .divider { height: 1px; background: #f1f5f9; }
        .divider-dashed { border-top: 1px dashed #cbd5e1; margin: 8px 0; }

        .info-group {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .info-row { display: flex; flex-direction: column; gap: 4px; }
        .info-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.5;
        }

        .font-bold { font-weight: 700; }
        .highlight-text { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -.01em; }

        .badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .badge-yellow { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-blue { background: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-gray { background: #e2e8f0; color: #334155; }

        .badge-sm {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-weight: 600;
        }

        .action-body {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group { margin-bottom: 12px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #334155; }
        .req { color: #dc2626; }

        .form-control {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
            outline: none;
            transition: .18s ease;
        }

        .form-control:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn-approve-full,
        .btn-reject-full,
        .btn-primary-full {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: .18s ease;
        }

        .btn-approve-full { background: #16a34a; }
        .btn-approve-full:hover { background: #15803d; }
        .btn-reject-full { background: #dc2626; }
        .btn-reject-full:hover { background: #b91c1c; }
        .btn-primary-full { background: var(--brand); }
        .btn-primary-full:hover { background: #1d4ed8; }

        .repayment-form-wrapper {
            margin-top: 16px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .form-section-title {
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .progress-container { margin-bottom: 14px; }

        .progress-labels {
            display: flex;
            justify-content: flex-end;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .progress-track {
            width: 100%;
            height: 9px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #22c55e);
            border-radius: 999px;
            transition: width .45s ease;
        }

        .summary-list { display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #475569; }
        .summary-item { display: flex; justify-content: space-between; gap: 8px; }
        .summary-item strong { color: #0f172a; }
        .text-green { color: #059669; }
        .total-item { font-size: 15px; color: #0f172a; font-weight: 700; }
        .total-item strong { color: #b91c1c; }

        .table-responsive {
            overflow-x: auto;
            border-top: 1px solid #f1f5f9;
        }

        .table-history {
            width: 100%;
            border-collapse: collapse;
            min-width: 350px;
            font-size: 13px;
        }

        .table-history th {
            text-align: left;
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .table-history td {
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: top;
        }

        .table-history tr:last-child td { border-bottom: none; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 700; color: #0f172a; }
        .text-center { text-align: center; }

        .note-row td {
            background: #f8fafc;
            color: #64748b;
            padding-top: 6px;
            padding-bottom: 12px;
        }

        .empty-state {
            padding: 28px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        .lunas-message {
            margin-top: 16px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 960px) {
            .detail-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .btn-back { align-self: flex-start; }
            .info-grid-2 { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; }
            .card-title-sm { padding: 14px 14px 10px; }
            .card-body-padded,
            .info-group,
            .action-body { padding: 14px; }
        }
    </style>
</x-app>