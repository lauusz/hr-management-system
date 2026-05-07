<x-app title="Detail Hutang Karyawan">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $errors->first() }}
    </div>
    @endif

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $loan->monthly_installment ? (float) $loan->monthly_installment : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);
        $percentage = $loan->amount > 0 ? min(100, round(($totalPaid / $loan->amount) * 100)) : 0;

        $statusConfig = [
            'PENDING_HRD' => ['label' => 'Menunggu Persetujuan', 'color' => 'warning'],
            'APPROVED' => ['label' => 'Disetujui', 'color' => 'info'],
            'REJECTED' => ['label' => 'Ditolak', 'color' => 'error'],
            'LUNAS' => ['label' => 'Lunas', 'color' => 'success'],
        ];
        $currentStatus = $statusConfig[$loan->status] ?? ['label' => $loan->status, 'color' => 'neutral'];

        $methodLabel = match($loan->payment_method) {
            'TUNAI' => 'Tunai',
            'CICILAN' => 'Transfer',
            'POTONG_GAJI' => 'Potong Gaji',
            default => '-',
        };

        // Document detection
        $docUrl = $loan->document_path ? asset('storage/' . $loan->document_path) : null;
        $docExt = $loan->document_path ? strtolower(pathinfo($loan->document_path, PATHINFO_EXTENSION)) : null;
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $isImageDoc = $docExt && in_array($docExt, $imageExts);
    @endphp

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Pinjaman</h1>
                <p class="section-subtitle">{{ $loan->snapshot_name }}</p>
            </div>
        </div>
    </x-slot>

    {{-- BACK + STATUS --}}
    <div class="page-header">
        <button type="button" class="back-btn" onclick="history.back();">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </button>
        <span class="status-badge status-{{ $currentStatus['color'] }}">{{ $currentStatus['label'] }}</span>
    </div>

    {{-- COMPACT LOAN SUMMARY BAR --}}
    <div class="loan-summary-bar">
        <div class="summary-amount">
            <span class="summary-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
            <span class="summary-amount-label">Total Pinjaman</span>
        </div>
        <div class="summary-meta">
            <div class="summary-meta-item">
                <span class="summary-meta-label">Tenor</span>
                <span class="summary-meta-value">{{ $months ? $months . ' bulan' : '-' }}</span>
            </div>
            <div class="summary-meta-divider"></div>
            <div class="summary-meta-item">
                <span class="summary-meta-label">Cicilan/Bulan</span>
                <span class="summary-meta-value">{{ $monthlyInstallment ? 'Rp ' . number_format($monthlyInstallment, 0, ',', '.') : '-' }}</span>
            </div>
            <div class="summary-meta-divider"></div>
            <div class="summary-meta-item">
                <span class="summary-meta-label">Metode</span>
                <span class="summary-meta-value">{{ $methodLabel }}</span>
            </div>
        </div>
    </div>

    {{-- DESKTOP TWO-COLUMN GRID --}}
    <div class="detail-grid">
        {{-- LEFT COLUMN --}}
        <div class="detail-main">

            {{-- SECTION: DATA KARYAWAN --}}
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="section-title">Data Karyawan</h2>
                </div>
                <div class="card">
                    <div class="data-grid">
                        <div class="data-item">
                            <span class="data-label">Nama</span>
                            <span class="data-value">{{ $loan->snapshot_name }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">NIK</span>
                            <span class="data-value">{{ $loan->snapshot_nik ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Jabatan</span>
                            <span class="data-value">{{ $loan->snapshot_position ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Divisi</span>
                            <span class="data-value">{{ $loan->snapshot_division ?? '-' }}</span>
                        </div>
                        <div class="data-item full-width">
                            <span class="data-label">Perusahaan</span>
                            <span class="data-value">{{ $loan->snapshot_company ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION: DETAIL PINJAMAN --}}
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="section-title">Detail Pinjaman</h2>
                    @if(auth()->user()->isHR() && in_array($loan->status, ['PENDING_HRD', 'APPROVED']))
                    <a href="{{ route('hr.loan_requests.edit', $loan->id) }}" class="btn-edit-loan">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @endif
                </div>
                <div class="card">
                    <div class="data-grid">
                        <div class="data-item">
                            <span class="data-label">Tgl. Pengajuan</span>
                            <span class="data-value">{{ \Carbon\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Tgl. Cair</span>
                            <span class="data-value">{{ $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date)->translatedFormat('j F Y') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Cicilan/Bulan</span>
                            <span class="data-value highlight">{{ $monthlyInstallment ? 'Rp ' . number_format($monthlyInstallment, 0, ',', '.') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Metode</span>
                            <span class="data-value">{{ $methodLabel }}</span>
                        </div>
                        @if($loan->purpose)
                        <div class="data-item full-width">
                            <span class="data-label">Keperluan</span>
                            <span class="data-value">{{ $loan->purpose }}</span>
                        </div>
                        @endif
                    </div>

                    @if($loan->document_path)
                    <div class="document-link">
                        @if($isImageDoc)
                        <button type="button" onclick="openDocModal()" class="doc-preview" style="border:none;width:100%;padding:0;background:transparent;display:block;text-align:left;">
                            <img src="{{ $docUrl }}" alt="Dokumen Pendukung" loading="lazy">
                            <div class="doc-preview-overlay">
                                <svg width="20" height="20" fill="none" stroke="#fff" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Klik untuk perbesar</span>
                            </div>
                        </button>
                        @else
                        <div class="doc-file">
                            <div class="doc-file-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="doc-file-info">
                                <span class="doc-file-name">Dokumen Pendukung</span>
                                <span class="doc-file-type">{{ strtoupper($docExt) }}</span>
                            </div>
                            <a href="{{ $docUrl }}" target="_blank" class="doc-file-btn">
                                Lihat
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            {{-- SECTION: CATATAN UNTUK EMPLOYEE (Visible) --}}
            @if($loan->notes && in_array($loan->status, ['APPROVED', 'REJECTED']))
            <div class="section">
                <div class="section-header">
                    <div class="section-icon note-public-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h2 class="section-title">Catatan untuk Employee</h2>
                </div>
                <div class="card note-public-card">
                    <div class="note-content">
                        {{ $loan->notes }}
                    </div>
                </div>
            </div>
            @endif

            {{-- SECTION: HISTORY PEMBAYARAN --}}
            @if(in_array($loan->status, ['APPROVED', 'LUNAS']))
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="section-title">Riwayat Pembayaran</h2>
                    @if(auth()->user()->isHR() && $loan->status !== 'LUNAS' && $loan->status !== 'REJECTED')
                    <button type="button" class="btn-add-repayment" onclick="openAddRepaymentForm()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </button>
                    @endif
                </div>
                <div class="card">
                    @if($loan->repayments->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="empty-title">Belum Ada Cicilan</h3>
                        <p class="empty-desc">Belum ada pembayaran cicilan yang tercatat untuk pinjaman ini.</p>
                    </div>
                    @else
                    <div class="history-list" id="repayment-history">
                        @foreach($loan->repayments as $index => $repayment)
                        <div class="history-item" data-repayment-id="{{ $repayment->id }}">
                            <div class="history-index">{{ $index + 1 }}</div>
                            <div class="history-content">
                                <div class="history-main">
                                    <span class="history-date">{{ \Carbon\Carbon::parse($repayment->paid_at)->translatedFormat('j F Y') }}</span>
                                    <span class="history-amount">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="history-meta">
                                    <span class="history-method">{{ $repayment->method === 'TUNAI' ? 'Tunai' : ($repayment->method === 'TRANSFER' ? 'Transfer' : 'Potong Gaji') }}</span>
                                    @if($repayment->note)
                                    <span class="history-note">{{ $repayment->note }}</span>
                                    @endif
                                </div>
                            </div>
                            @if(auth()->user()->isHR() && in_array($loan->status, ['APPROVED', 'PENDING_HRD']))
                            <div class="history-actions">
                                <button type="button" class="btn-edit-repayment" onclick="editRepayment({{ $repayment->id }}, '{{ \Carbon\Carbon::parse($repayment->paid_at)->format('Y-m-d') }}', {{ $repayment->amount }}, '{{ $repayment->method }}', '{{ $repayment->note ?? '' }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form action="{{ route('hr.loan_requests.repayments.destroy', [$loan->id, $repayment->id]) }}" method="POST" class="form-delete-repayment" onsubmit="return confirm('Hapus cicilan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-repayment">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>{{-- end detail-main --}}

        {{-- RIGHT COLUMN --}}
        <div class="detail-side">

            {{-- SECTION: STATUS PEMBAYARAN (Only if Approved/Lunas) --}}
            @if(in_array($loan->status, ['APPROVED', 'LUNAS']))
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h2 class="section-title">Status Pembayaran</h2>
                </div>
                <div class="card">
                    <div class="progress-section">
                        <div class="progress-header">
                            <span class="progress-label">Progress</span>
                            <span class="progress-percent">{{ $percentage }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    <div class="payment-summary">
                        <div class="summary-row">
                            <span>Total Pinjaman</span>
                            <strong>Rp {{ number_format($loan->amount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="summary-row paid">
                            <span>Sudah Dibayar</span>
                            <strong>- Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row remaining">
                            <span>Sisa Hutang</span>
                            <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if(auth()->user()->isHR())
                    @if($loan->status !== 'LUNAS')
                    <div class="repayment-form" id="add-repayment-form" style="display: none;">
                        <h4 class="repayment-title">Catat Cicilan Baru</h4>

                        {{-- WARNING: Amount exceeds remaining --}}
                        @if(session('repayment_warning'))
                        <div class="repayment-warning">
                            <div class="warning-icon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="warning-content">
                                <p class="warning-title">Peringatan: Nominal melebihi sisa hutang</p>
                                <div class="warning-details">
                                    <span>Nominal diajukan: <strong>Rp {{ number_format(session('repayment_warning')['amount'], 0, ',', '.') }}</strong></span>
                                    <span>Sisa hutang: <strong>Rp {{ number_format(session('repayment_warning')['remaining'], 0, ',', '.') }}</strong></span>
                                    <span class="warning-extra">Kelebihan: <strong class="text-red">Rp {{ number_format(session('repayment_warning')['remainder'], 0, ',', '.') }}</strong></span>
                                </div>
                                <p class="warning-note">Sisa pinjaman <strong>Rp {{ number_format(session('repayment_warning')['remainder'], 0, ',', '.') }}</strong> apakah tetap ingin diajukan? Pengajuan akhir tetap menunggu persetujuan HRD.</p>
                            </div>
                        </div>
                        <form action="{{ route('hr.loan_requests.repayments.store', $loan->id) }}" method="POST" class="confirm-form">
                            @csrf
                            <input type="hidden" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}">
                            <input type="hidden" name="amount" value="{{ session('repayment_warning')['amount'] }}">
                            <input type="hidden" name="method" value="{{ old('method', $loan->payment_method === 'CICILAN' ? 'TRANSFER' : ($loan->payment_method === 'POTONG_GAJI' ? 'POTONG_GAJI' : 'TUNAI')) }}">
                            <input type="hidden" name="force_submit" value="1">
                            <div class="confirm-actions">
                                <a href="{{ url()->previous() }}" class="btn-cancel">Batal</a>
                                <button type="submit" class="btn-confirm">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Ya, Tetap Ajukan
                                </button>
                            </div>
                        </form>
                        @else
                        <form action="{{ route('hr.loan_requests.repayments.store', $loan->id) }}" method="POST" id="form-add-repayment">
                            @csrf
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label>Tanggal Bayar</label>
                                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Metode</label>
                                    <select name="method" class="form-input" required>
                                        <option value="POTONG_GAJI" @selected($loan->payment_method === 'POTONG_GAJI')>Potong Gaji</option>
                                        <option value="TRANSFER" @selected($loan->payment_method === 'CICILAN')>Transfer Bank</option>
                                        <option value="TUNAI">Tunai</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nominal (Rp)</label>
                                <input type="text" inputmode="numeric" class="form-input text-large" id="repayment_display" placeholder="0">
                                <input type="hidden" name="amount" id="repayment_amount">
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <input type="text" name="note" class="form-input" placeholder="Opsional">
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel-form" onclick="closeAddRepaymentForm()">Batal</button>
                                <button type="submit" class="btn-primary">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Simpan
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>

                    {{-- Edit Repayment Modal/Form --}}
                    <div class="repayment-form" id="edit-repayment-form" style="display: none;">
                        <h4 class="repayment-title">Edit Cicilan</h4>
                        <form action="" method="POST" id="form-edit-repayment">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="repayment_id" id="edit_repayment_id">
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label>Tanggal Bayar</label>
                                    <input type="date" name="paid_at" id="edit_paid_at" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Metode</label>
                                    <select name="method" id="edit_method" class="form-input" required>
                                        <option value="POTONG_GAJI">Potong Gaji</option>
                                        <option value="TRANSFER">Transfer Bank</option>
                                        <option value="TUNAI">Tunai</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nominal (Rp)</label>
                                <input type="text" inputmode="numeric" class="form-input text-large" id="edit_repayment_display" placeholder="0">
                                <input type="hidden" name="amount" id="edit_repayment_amount">
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <input type="text" name="note" id="edit_note" class="form-input" placeholder="Opsional">
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel-form" onclick="closeEditRepaymentForm()">Batal</button>
                                <button type="submit" class="btn-primary">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="lunas-badge">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pinjaman sudah Lunas
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            @endif

            {{-- SECTION: CATATAN INTERNAL HRD (Private) --}}
            <div class="section">
                <div class="section-header">
                    <div class="section-icon internal-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h2 class="section-title">Catatan Internal</h2>
                    <span class="internal-badge">Hanya HRD</span>
                </div>
                <div class="card internal-card">
                    <p class="internal-hint">Catatan ini tidak akan terlihat oleh employee</p>

                    @if($loan->hrd_note)
                    <div class="internal-content">
                        @foreach(explode("\n", $loan->hrd_note) as $line)
                            @if(trim($line))
                            <div class="internal-line">{{ $line }}</div>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    <form action="{{ route('hr.loan_requests.saveInternalNote', $loan->id) }}" method="POST" class="internal-form">
                        @csrf
                        @method('PUT')
                        <div class="internal-input-wrapper">
                            <textarea name="hrd_note" rows="2" class="form-input" placeholder="Tambahkan catatan baru..."></textarea>
                        </div>
                        <div class="internal-footer">
                            <button type="submit" class="btn-save-internal">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- SECTION: AKSI HRD (Only if Pending) --}}
            @if($loan->status === 'PENDING_HRD')
            <div class="section section--actions">
                <div class="section-header">
                    <div class="section-icon action-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h2 class="section-title">Tindakan HRD</h2>
                </div>

                {{-- APPROVE FORM --}}
                <div class="card action-card">
                    <form action="{{ route('hr.loan_requests.approve', $loan->id) }}" method="POST">
                        @csrf
                        <div class="action-header">
                            <span class="action-badge approve">Setujui</span>
                            <span class="action-hint">Pinjaman disetujui dan bisa dicairkan</span>
                        </div>
                        <div class="form-group">
                            <label>Catatan untuk Employee <span class="label-hint">(akan dilihat employee)</span></label>
                            <textarea name="notes" rows="2" class="form-input" placeholder="Contoh: Pinjaman disetujui, tenor 10 bulan..."></textarea>
                        </div>
                        <button type="submit" class="btn-approve">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Pinjaman
                        </button>
                    </form>
                </div>

                {{-- REJECT FORM --}}
                <div class="card action-card action-card--reject">
                    <form action="{{ route('hr.loan_requests.reject', $loan->id) }}" method="POST">
                        @csrf
                        <div class="action-header">
                            <span class="action-badge reject">Tolak</span>
                            <span class="action-hint">Pengajuan ditolak</span>
                        </div>
                        <div class="form-group">
                            <label>Alasan Penolakan <span class="label-hint">(akan dilihat employee)</span></label>
                            <textarea name="notes" rows="2" class="form-input" placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="btn-reject">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>{{-- end detail-side --}}
    </div>{{-- end detail-grid --}}

    <style>
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --accent: #D4AF37;
            --accent-light: #E6C65C;
            --accent-dark: #B8962E;
            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;
            --white: #FFFFFF;
            --gray-50: #F5F7FA;
            --gray-100: #F8FAFC;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #374151;
            --gray-700: #1F2937;
            --gray-900: #111827;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 24px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }

        * { box-sizing: border-box; }

        /* ALERT */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
            cursor: pointer;
            font-family: inherit;
        }

        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }

        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--gray-500);
            margin: 2px 0 0;
        }

        /* STATUS BADGE */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .status-success { background: rgba(34, 197, 94, 0.1); color: var(--success); }
        .status-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-error   { background: rgba(239, 68, 68, 0.1);   color: var(--error); }
        .status-info    { background: rgba(59, 130, 246, 0.1);  color: var(--info); }
        .status-neutral { background: var(--gray-100);          color: var(--gray-600); }

        /* LOAN SUMMARY BAR */
        .loan-summary-bar {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .summary-amount {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 180px;
        }

        .summary-amount-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.02em;
        }

        .summary-amount-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .summary-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            flex: 1;
        }

        .summary-meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .summary-meta-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .summary-meta-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .summary-meta-divider {
            width: 1px;
            height: 28px;
            background: var(--gray-200);
            flex-shrink: 0;
        }

        /* DETAIL GRID */
        .detail-grid {
            display: grid;
            gap: 20px;
        }

        .detail-main,
        .detail-side {
            min-width: 0;
        }

        /* SECTION */
        .section {
            margin-bottom: 16px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 93, 160, 0.08);
            border-radius: var(--radius-md);
            color: var(--primary);
            flex-shrink: 0;
        }

        .section-icon.note-public-icon { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .section-icon.action-icon { background: rgba(34, 197, 94, 0.1); color: var(--success); }
        .section-icon.internal-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            flex: 1;
        }

        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header-inline .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--gray-500);
            font-weight: 500;
            line-height: 1.35;
        }

        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        .btn-edit-loan {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            border: 1.5px solid rgba(20, 93, 160, 0.2);
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit-loan:hover {
            background: rgba(20, 93, 160, 0.14);
            border-color: rgba(20, 93, 160, 0.3);
        }

        .btn-edit-loan svg {
            width: 16px;
            height: 16px;
        }

        /* CARD */
        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }

        /* DATA GRID */
        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--gray-200);
        }

        .data-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 12px 14px;
            background: var(--white);
        }

        .data-item.full-width { grid-column: span 2; }

        .data-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-value {
            font-size: 13px;
            color: var(--gray-900);
            font-weight: 500;
        }

        .data-value.highlight {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
        }

        /* DOCUMENT LINK */
        .document-link {
            padding: 12px 14px;
            border-top: 1px solid var(--gray-200);
        }

        /* Document Preview (Image) */
        .doc-preview {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            cursor: pointer;
            background: var(--gray-100);
            aspect-ratio: 16 / 10;
            max-height: 240px;
        }

        .doc-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
            display: block;
        }

        .doc-preview:hover img {
            transform: scale(1.03);
        }

        .doc-preview-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 61, 98, 0.45);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .doc-preview:hover .doc-preview-overlay {
            opacity: 1;
        }

        /* Document File (Non-image) */
        .doc-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
        }

        .doc-file-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 93, 160, 0.08);
            border-radius: var(--radius-md);
            color: var(--primary);
            flex-shrink: 0;
        }

        .doc-file-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
            min-width: 0;
        }

        .doc-file-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .doc-file-type {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
        }

        .doc-file-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: var(--white);
            color: var(--primary);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .doc-file-btn:hover {
            background: rgba(20, 93, 160, 0.08);
            border-color: var(--primary);
        }

        /* ========================================== */
        /* DOCUMENT MODAL (Fixed Preview Modal)       */
        /* ========================================== */
        .doc-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            width: 100vw;
            height: 100vh;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .doc-modal.is-open {
            display: flex;
        }
        .doc-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.88);
            backdrop-filter: blur(4px);
        }

        /* Toolbar */
        .doc-modal-toolbar {
            position: fixed;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 11;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            background: rgba(30, 30, 35, 0.85);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 999px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            user-select: none;
        }
        .doc-tb-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }
        .doc-tb-btn:hover {
            background: rgba(255,255,255,0.15);
        }
        .doc-tb-btn:active {
            background: rgba(255,255,255,0.25);
        }
        .doc-tb-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .doc-tb-pct {
            min-width: 44px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            font-variant-numeric: tabular-nums;
            padding: 0 6px;
            user-select: none;
        }

        /* Close button */
        .doc-modal-close {
            position: fixed;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 11;
            flex-shrink: 0;
            padding: 0;
        }
        .doc-modal-close:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.05);
        }

        /* Viewer */
        .doc-modal-viewer {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 56px 8px 8px;
            box-sizing: border-box;
            cursor: default;
        }
        .doc-modal-viewer.is-grab {
            cursor: grab;
        }
        .doc-modal-viewer.is-grabbing {
            cursor: grabbing;
        }

        /* Image */
        .doc-modal-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            transition: transform 0.15s ease-out;
            transform-origin: center center;
            -webkit-user-drag: none;
            user-select: none;
            pointer-events: none;
        }

        /* Desktop refinement */
        @media (min-width: 768px) {
            .doc-modal-toolbar {
                top: 20px;
                padding: 4px 10px;
                gap: 3px;
                background: rgba(28, 28, 32, 0.82);
                border-color: rgba(255,255,255,0.08);
                box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            }
            .doc-tb-btn {
                width: 32px;
                height: 32px;
            }
            .doc-tb-btn svg {
                width: 14px;
                height: 14px;
            }
            .doc-tb-pct {
                font-size: 12px;
                min-width: 40px;
                padding: 0 4px;
                color: rgba(255,255,255,0.75);
            }
            .doc-modal-close {
                top: 16px;
                right: 16px;
                width: 44px;
                height: 44px;
            }
            .doc-modal-viewer {
                padding: 60px 16px 16px;
            }
            .doc-modal-img {
                border-radius: 10px;
            }
        }

        /* NOTE PUBLIC CARD */
        .note-public-card {
            background: rgba(59, 130, 246, 0.06);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .note-content {
            padding: 14px;
            font-size: 14px;
            color: #1e3a8a;
            line-height: 1.5;
        }

        /* PROGRESS SECTION */
        .progress-section {
            padding: 14px;
            border-bottom: 1px solid var(--gray-200);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .progress-label {
            font-size: 12px;
            color: var(--gray-500);
        }

        .progress-percent {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
        }

        .progress-bar {
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            border-radius: 4px;
        }

        /* PAYMENT SUMMARY */
        .payment-summary {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .summary-row span { color: var(--gray-500); }
        .summary-row strong { color: var(--gray-900); }
        .summary-row.paid strong { color: var(--success); }
        .summary-row.remaining strong { color: var(--error); font-size: 15px; }

        .summary-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 4px 0;
        }

        /* REPAYMENT FORM */
        .repayment-form {
            padding: 14px;
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .repayment-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            margin: 0 0 12px;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-500);
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--gray-900);
            background: var(--white);
            transition: all 0.2s ease;
            line-height: 1.5;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .form-input.text-large {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
        }

        .label-hint {
            font-size: 10px;
            font-weight: 400;
            color: var(--gray-400);
            font-style: italic;
            text-transform: none;
        }

        .req { color: var(--error); }

        .btn-primary {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        /* REPAYMENT WARNING */
        .repayment-warning {
            display: flex;
            gap: 12px;
            padding: 14px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: var(--radius-lg);
            margin-bottom: 14px;
        }

        .warning-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.1);
            border-radius: var(--radius-md);
            color: var(--error);
            flex-shrink: 0;
        }

        .warning-content { flex: 1; }

        .warning-title {
            font-size: 14px;
            font-weight: 700;
            color: #991b1b;
            margin: 0 0 8px;
        }

        .warning-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
            color: #7f1d1d;
            margin-bottom: 8px;
        }

        .warning-details strong { color: #991b1b; }
        .warning-extra { color: var(--error); }
        .text-red { color: var(--error); }

        .warning-note {
            font-size: 12px;
            color: #b91c1c;
            margin: 0;
            padding: 8px 10px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: var(--radius-sm);
        }

        .confirm-form {
            background: var(--gray-50);
            border: 1px dashed var(--gray-300);
            border-radius: var(--radius-lg);
            padding: 14px;
            margin-top: 12px;
        }

        .confirm-actions {
            display: flex;
            gap: 10px;
        }

        .btn-cancel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            background: var(--white);
            color: var(--gray-500);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .btn-confirm {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--success);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-confirm:hover { background: #15803d; }

        /* ADD / EDIT REPAYMENT FORM */
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .btn-cancel-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            background: var(--white);
            color: var(--gray-500);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel-form:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        /* HISTORY ACTIONS */
        .history-actions {
            display: flex;
            gap: 6px;
            margin-left: auto;
            align-items: center;
        }

        .btn-edit-repayment, .btn-delete-repayment {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit-repayment {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
        }

        .btn-edit-repayment:hover {
            background: rgba(20, 93, 160, 0.14);
        }

        .btn-delete-repayment {
            background: rgba(239, 68, 68, 0.08);
            color: var(--error);
        }

        .btn-delete-repayment:hover {
            background: rgba(239, 68, 68, 0.14);
        }

        /* ADD REPAYMENT BUTTON */
        .btn-add-repayment {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: auto;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
        }

        .btn-add-repayment:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        .form-delete-repayment {
            margin: 0;
        }

        /* LUNAS BADGE */
        .lunas-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            background: rgba(34, 197, 94, 0.08);
            color: #166534;
            font-size: 14px;
            font-weight: 600;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
        }

        .empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
        }

        .empty-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-600);
            margin: 0 0 6px;
        }

        .empty-desc {
            font-size: 13px;
            color: var(--gray-500);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* HISTORY LIST */
        .history-list {
            display: flex;
            flex-direction: column;
        }

        .history-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--gray-200);
        }

        .history-item:last-child { border-bottom: none; }

        .history-index {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            color: var(--gray-500);
            flex-shrink: 0;
        }

        .history-content { flex: 1; }

        .history-main {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .history-date { font-size: 13px; color: var(--gray-900); }
        .history-amount { font-size: 13px; font-weight: 700; color: var(--gray-900); }

        .history-meta {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .history-method {
            font-size: 11px;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 2px 8px;
            border-radius: 10px;
        }

        .history-note {
            font-size: 11px;
            color: var(--gray-400);
            font-style: italic;
        }

        /* ACTION CARD */
        .action-card {
            margin-bottom: 12px;
            border-radius: var(--radius-xl);
            overflow: hidden;
        }

        .action-card:last-child {
            margin-bottom: 0;
        }

        .action-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 16px;
        }

        .action-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: var(--radius-md);
            text-transform: uppercase;
        }

        .action-badge.approve { background: rgba(34, 197, 94, 0.1); color: #166534; }
        .action-badge.reject { background: rgba(239, 68, 68, 0.1); color: #991b1b; }

        .action-hint {
            font-size: 13px;
            color: var(--gray-400);
        }

        .action-card form {
            padding: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .btn-approve, .btn-reject {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 16px;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.2s ease;
        }

        .btn-approve {
            background: var(--success);
            color: var(--white);
        }

        .btn-approve:hover { background: #15803d; }

        .btn-reject {
            background: var(--error);
            color: var(--white);
        }

        .btn-reject:hover { background: #b91c1c; }

        /* INTERNAL CARD (subtle) */
        .internal-card {
            background: var(--gray-50);
            border-color: var(--gray-200);
        }

        .internal-content {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            border-bottom: 1px solid var(--gray-200);
        }

        .internal-line {
            font-size: 13px;
            color: var(--gray-600);
            line-height: 1.5;
        }

        .internal-form {
            display: flex;
            flex-direction: column;
        }

        .internal-input-wrapper {
            padding: 12px 14px;
        }

        .internal-input-wrapper textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--gray-900);
            background: var(--white);
            resize: vertical;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .internal-input-wrapper textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .internal-footer {
            padding: 0 14px 12px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-save-internal {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: var(--success);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save-internal:hover { background: #15803d; }

        .internal-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 3px 8px;
            border-radius: var(--radius-md);
        }

        .internal-hint {
            font-size: 11px;
            color: var(--gray-400);
            margin: 0 0 8px 0;
            font-style: italic;
        }

        /* RESPONSIVE */
        @media (max-width: 767px) {
            /* ===== MOBILE COMPACT OVERRIDES ===== */

            /* Alerts */
            .alert {
                padding: 10px 12px;
                margin-bottom: 12px;
                font-size: 12px;
                gap: 8px;
            }

            /* Page header */
            .page-header {
                margin-bottom: 12px;
                gap: 8px;
            }

            .back-btn {
                height: 32px;
                padding: 0 10px 0 8px;
                font-size: 11px;
            }

            .back-btn svg {
                width: 14px;
                height: 14px;
            }

            /* Summary bar */
            .loan-summary-bar {
                padding: 12px 14px;
                margin-bottom: 12px;
                gap: 10px;
            }

            .summary-amount {
                min-width: 0;
                flex: 1;
            }

            .summary-amount-value {
                font-size: 18px;
            }

            .summary-amount-label {
                font-size: 10px;
            }

            .summary-meta {
                gap: 8px 10px;
                flex: 1.5;
            }

            .summary-meta-item {
                gap: 1px;
            }

            .summary-meta-label {
                font-size: 9px;
            }

            .summary-meta-value {
                font-size: 12px;
            }

            .summary-meta-divider {
                height: 22px;
            }

            /* Grid & sections */
            .detail-grid {
                gap: 12px;
            }

            .section {
                margin-bottom: 12px;
            }

            .section-header {
                margin-bottom: 8px;
                gap: 8px;
            }

            .section-icon {
                width: 28px;
                height: 28px;
            }

            .section-icon svg {
                width: 16px;
                height: 16px;
            }

            .section-title {
                font-size: 13px;
            }

            .section-subtitle {
                font-size: 12px;
            }

            .section-header-inline {
                gap: 8px;
            }

            .section-header-inline .section-title {
                font-size: 15px;
            }

            /* Edit / Add buttons in section headers */
            .btn-edit-loan {
                padding: 5px 10px;
                font-size: 11px;
                gap: 4px;
            }

            .btn-edit-loan svg {
                width: 14px;
                height: 14px;
            }

            .btn-add-repayment {
                padding: 4px 10px;
                font-size: 11px;
            }

            /* Data grid */
            .data-item {
                padding: 10px 12px;
                gap: 2px;
            }

            .data-label {
                font-size: 9px;
            }

            .data-value {
                font-size: 12px;
                overflow-wrap: break-word;
            }

            .data-value.highlight {
                font-size: 13px;
            }

            /* Document */
            .document-link {
                padding: 10px 12px;
            }

            .doc-preview {
                aspect-ratio: 16 / 9;
                max-height: 180px;
            }

            .doc-file {
                padding: 10px;
                gap: 10px;
            }

            .doc-file-icon {
                width: 36px;
                height: 36px;
            }

            .doc-file-icon svg {
                width: 20px;
                height: 20px;
            }

            .doc-file-name {
                font-size: 12px;
                overflow-wrap: break-word;
            }

            .doc-file-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            /* Public note */
            .note-content {
                padding: 12px;
                font-size: 13px;
            }

            /* Progress */
            .progress-section {
                padding: 12px;
            }

            .progress-header {
                margin-bottom: 6px;
            }

            .progress-label {
                font-size: 11px;
            }

            .progress-percent {
                font-size: 13px;
            }

            .progress-bar {
                height: 6px;
            }

            /* Payment summary */
            .payment-summary {
                padding: 12px;
                gap: 6px;
            }

            .summary-row {
                font-size: 12px;
            }

            .summary-row.remaining strong {
                font-size: 13px;
            }

            .summary-divider {
                margin: 2px 0;
            }

            /* Repayment form */
            .repayment-form {
                padding: 12px;
            }

            .repayment-title {
                margin-bottom: 10px;
                font-size: 11px;
            }

            .form-row-2 {
                gap: 8px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-group label {
                margin-bottom: 4px;
                font-size: 11px;
            }

            .form-input {
                padding: 8px 12px;
                font-size: 13px;
            }

            .form-input.text-large {
                font-size: 15px;
            }

            .form-actions {
                margin-top: 12px;
                gap: 8px;
            }

            .btn-cancel-form {
                padding: 10px 14px;
                font-size: 13px;
            }

            .btn-primary {
                padding: 11px 14px;
                font-size: 13px;
                box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
            }

            /* Repayment warning */
            .repayment-warning {
                padding: 10px;
                gap: 8px;
                margin-bottom: 10px;
            }

            .warning-icon {
                width: 32px;
                height: 32px;
            }

            .warning-icon svg {
                width: 18px;
                height: 18px;
            }

            .warning-title {
                font-size: 12px;
                margin-bottom: 6px;
            }

            .warning-details {
                font-size: 12px;
                gap: 2px;
                margin-bottom: 6px;
            }

            .warning-note {
                padding: 6px 8px;
                font-size: 11px;
            }

            .confirm-form {
                padding: 10px;
                margin-top: 8px;
            }

            .btn-cancel,
            .btn-confirm {
                padding: 8px 12px;
                font-size: 12px;
            }

            /* History list */
            .history-item {
                padding: 10px 12px;
                gap: 10px;
            }

            .history-index {
                width: 22px;
                height: 22px;
                font-size: 10px;
            }

            .history-main {
                gap: 6px;
            }

            .history-date,
            .history-amount {
                font-size: 12px;
            }

            .history-meta {
                margin-top: 2px;
                gap: 6px;
            }

            .history-method {
                font-size: 10px;
                padding: 1px 6px;
            }

            .history-note {
                font-size: 10px;
            }

            /* Empty state */
            .empty-state {
                padding: 32px 20px;
            }

            .empty-icon {
                width: 56px;
                height: 56px;
                margin-bottom: 12px;
            }

            .empty-icon svg {
                width: 32px;
                height: 32px;
            }

            .empty-title {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .empty-desc {
                font-size: 12px;
            }

            /* Lunas badge */
            .lunas-badge {
                padding: 12px;
                font-size: 13px;
            }

            /* ===== ACTION CARDS - TINDAKAN HRD ===== */
            .action-card {
                margin-bottom: 10px;
                border-radius: var(--radius-lg);
            }

            .action-header {
                padding: 0 0 8px;
                margin-bottom: 8px;
                gap: 8px;
                flex-wrap: wrap;
                border-bottom: 1px solid var(--gray-100);
            }

            .action-badge {
                padding: 3px 8px;
                font-size: 11px;
            }

            .action-hint {
                font-size: 11px;
                line-height: 1.3;
            }

            .action-card form {
                padding: 10px 12px 12px;
            }

            .action-card .form-group {
                margin-bottom: 10px;
            }

            .action-card .form-group label {
                margin-bottom: 4px;
                font-size: 11px;
            }

            .action-card textarea.form-input {
                min-height: 56px;
                padding: 8px 10px;
                font-size: 13px;
                line-height: 1.4;
            }

            .btn-approve,
            .btn-reject {
                padding: 12px 14px;
                font-size: 14px;
                margin-top: 6px;
                border-radius: var(--radius-md);
            }

            /* Internal notes */
            .internal-card {
                border-radius: var(--radius-lg);
            }

            .internal-content {
                padding: 10px 12px;
                gap: 4px;
            }

            .internal-line {
                font-size: 12px;
                line-height: 1.4;
            }

            .internal-input-wrapper {
                padding: 10px 12px 8px;
            }

            .internal-input-wrapper textarea {
                padding: 8px 10px;
                font-size: 13px;
                min-height: 56px;
            }

            .internal-footer {
                padding: 0 12px 10px;
            }

            .internal-hint {
                font-size: 10px;
                margin: 0 0 6px 0;
            }

            .internal-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .btn-save-internal {
                padding: 8px 14px;
                font-size: 12px;
            }
        }

        @media (min-width: 768px) {
            .page-header {
                margin-bottom: 24px;
            }

            .page-title { font-size: 22px; }
            .page-subtitle { font-size: 14px; }

            .loan-summary-bar {
                padding: 18px 24px;
            }

            .summary-amount-value {
                font-size: 24px;
            }

            .data-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .data-item.full-width { grid-column: span 4; }

            .form-row-2 {
                grid-template-columns: repeat(3, 1fr);
            }

            .btn-primary {
                width: auto;
                padding: 12px 24px;
            }

            .confirm-actions {
                flex-direction: row;
            }

            .btn-cancel, .btn-confirm {
                flex: none;
                padding: 12px 24px;
            }

            .form-actions {
                flex-direction: row;
            }

            .btn-cancel-form, .btn-primary {
                flex: none;
                padding: 12px 24px;
            }
        }

        @media (min-width: 1024px) {
            .detail-grid {
                grid-template-columns: 1fr 360px;
                gap: 24px;
            }

            .detail-side {
                position: sticky;
                top: 20px;
                align-self: start;
            }

            .detail-side .form-row-2 {
                grid-template-columns: 1fr 1fr;
            }

            .btn-approve, .btn-reject {
                width: auto;
                padding: 10px 20px;
                font-size: 13px;
                margin-top: 0;
            }

            .btn-reject {
                background: var(--white);
                color: var(--error);
                border: 1.5px solid var(--error);
            }

            .btn-reject:hover {
                background: rgba(239, 68, 68, 0.06);
            }

            .action-card form {
                padding: 12px;
            }

            .action-header {
                padding: 12px;
                margin-bottom: 12px;
                gap: 8px;
            }
        }
    </style>

    {{-- Image Preview Modal (Fixed Preview Modal) --}}
    @if($isImageDoc)
    <div id="docModal" class="doc-modal" onclick="closeDocModal(event)">
        <div class="doc-modal-backdrop"></div>

        {{-- Toolbar --}}
        <div class="doc-modal-toolbar">
            <button type="button" class="doc-tb-btn" onclick="zoomDoc(-0.25)" aria-label="Perkecil gambar">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <span id="docZoomPct" class="doc-tb-pct">100%</span>
            <button type="button" class="doc-tb-btn" onclick="zoomDoc(0.25)" aria-label="Perbesar gambar">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <button type="button" class="doc-tb-btn" onclick="resetDocZoom()" aria-label="Reset zoom">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            </button>
        </div>

        {{-- Close button --}}
        <button type="button" class="doc-modal-close" onclick="closeDocModal()" aria-label="Tutup preview">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Viewer --}}
        <div class="doc-modal-viewer">
            <img id="docModalImage" src="{{ $docUrl }}" alt="Dokumen Pendukung" class="doc-modal-img" draggable="false">
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        function formatRupiahNumber(value) {
            if (!value || isNaN(value)) return '';
            return Number(value).toLocaleString('id-ID');
        }

        function updateRepaymentAmount() {
            var display = document.getElementById('repayment_display');
            var hidden = document.getElementById('repayment_amount');
            if (!display || !hidden) return;

            var digits = (display.value || '').replace(/\D/g, '');
            if (digits.length === 0) {
                hidden.value = '';
                display.value = '';
                return;
            }

            var numeric = parseInt(digits);
            hidden.value = numeric;
            display.value = formatRupiahNumber(numeric);
        }

        // === ADD REPAYMENT FORM ===
        function openAddRepaymentForm() {
            document.getElementById('add-repayment-form').style.display = 'block';
            document.getElementById('edit-repayment-form').style.display = 'none';
            // Auto-fill with monthly installment
            setTimeout(function() {
                var display = document.getElementById('repayment_display');
                var hidden = document.getElementById('repayment_amount');
                var monthly = {{ (int) ($monthlyInstallment ?? 0) }};
                if (monthly > 0) {
                    display.value = formatRupiahNumber(monthly);
                    hidden.value = monthly;
                }
                if (display) display.focus();
            }, 100);
        }

        function closeAddRepaymentForm() {
            document.getElementById('add-repayment-form').style.display = 'none';
            // Reset form
            var form = document.getElementById('form-add-repayment');
            if (form) form.reset();
            document.getElementById('repayment_display').value = '';
            document.getElementById('repayment_amount').value = '';
        }

        // === EDIT REPAYMENT FORM ===
        function editRepayment(id, paidAt, amount, method, note) {
            document.getElementById('add-repayment-form').style.display = 'none';
            document.getElementById('edit-repayment-form').style.display = 'block';

            document.getElementById('edit_repayment_id').value = id;
            document.getElementById('edit_paid_at').value = paidAt;
            document.getElementById('edit_method').value = method;
            document.getElementById('edit_note').value = note || '';

            // Format amount for display
            var display = document.getElementById('edit_repayment_display');
            var hidden = document.getElementById('edit_repayment_amount');
            display.value = formatRupiahNumber(amount);
            hidden.value = amount;

            // Set form action
            var loanId = {{ $loan->id }};
            var form = document.getElementById('form-edit-repayment');
            form.action = '/hr/loan-requests/' + loanId + '/repayments/' + id;

            // Focus on amount
            setTimeout(function() {
                display.focus();
            }, 100);
        }

        function closeEditRepaymentForm() {
            document.getElementById('edit-repayment-form').style.display = 'none';
        }

        function updateEditRepaymentAmount() {
            var display = document.getElementById('edit_repayment_display');
            var hidden = document.getElementById('edit_repayment_amount');
            if (!display || !hidden) return;

            var digits = (display.value || '').replace(/\D/g, '');
            if (digits.length === 0) {
                hidden.value = '';
                display.value = '';
                return;
            }

            var numeric = parseInt(digits);
            hidden.value = numeric;
            display.value = formatRupiahNumber(numeric);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Repayment amount inputs
            var display = document.getElementById('repayment_display');
            if (display) {
                display.addEventListener('input', updateRepaymentAmount);
                display.addEventListener('blur', updateRepaymentAmount);
            }
            var editDisplay = document.getElementById('edit_repayment_display');
            if (editDisplay) {
                editDisplay.addEventListener('input', updateEditRepaymentAmount);
                editDisplay.addEventListener('blur', updateEditRepaymentAmount);
            }

            // Document viewer (Fixed Preview Modal)
            @if($isImageDoc)
            (function() {
                const MIN_ZOOM = 0.5;
                const MAX_ZOOM = 4;
                const ZOOM_STEP = 0.25;

                let docZoom = 1;
                let docPanX = 0;
                let docPanY = 0;
                let isDragging = false;
                let dragStartX = 0;
                let dragStartY = 0;
                let panStartX = 0;
                let panStartY = 0;

                const modal = document.getElementById('docModal');
                const viewer = modal.querySelector('.doc-modal-viewer');
                const img = document.getElementById('docModalImage');
                const pctEl = document.getElementById('docZoomPct');

                function updateDocTransform() {
                    img.style.transform = 'translate(' + docPanX + 'px, ' + docPanY + 'px) scale(' + docZoom + ')';
                    if (pctEl) pctEl.textContent = Math.round(docZoom * 100) + '%';
                    updateCursor();
                }

                function updateCursor() {
                    viewer.classList.remove('is-grab', 'is-grabbing');
                    if (docZoom > 1) {
                        viewer.classList.add(isDragging ? 'is-grabbing' : 'is-grab');
                    }
                }

                window.zoomDoc = function(delta) {
                    let newZoom = Math.round((docZoom + delta) / ZOOM_STEP) * ZOOM_STEP;
                    newZoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, newZoom));
                    if (newZoom === docZoom) return;
                    docZoom = newZoom;
                    if (docZoom <= 1) {
                        docPanX = 0;
                        docPanY = 0;
                    }
                    updateDocTransform();
                };

                window.resetDocZoom = function() {
                    docZoom = 1;
                    docPanX = 0;
                    docPanY = 0;
                    isDragging = false;
                    updateDocTransform();
                };

                window.openDocModal = function() {
                    modal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    window.resetDocZoom();
                };

                window.closeDocModal = function(e) {
                    if (e && e.type === 'click') {
                        if (!(e.target.classList.contains('doc-modal') || e.target.classList.contains('doc-modal-backdrop'))) {
                            return;
                        }
                    }
                    modal.classList.remove('is-open');
                    document.body.style.overflow = '';
                    window.resetDocZoom();
                };

                // Mouse drag pan
                viewer.addEventListener('mousedown', function(e) {
                    if (e.button !== 0) return;
                    if (docZoom <= 1) return;
                    isDragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    panStartX = docPanX;
                    panStartY = docPanY;
                    updateCursor();
                    e.preventDefault();
                });

                window.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    docPanX = panStartX + (e.clientX - dragStartX);
                    docPanY = panStartY + (e.clientY - dragStartY);
                    updateDocTransform();
                });

                window.addEventListener('mouseup', function() {
                    if (!isDragging) return;
                    isDragging = false;
                    updateCursor();
                });

                // Touch drag pan
                viewer.addEventListener('touchstart', function(e) {
                    if (docZoom <= 1) return;
                    if (e.touches.length !== 1) return;
                    isDragging = true;
                    dragStartX = e.touches[0].clientX;
                    dragStartY = e.touches[0].clientY;
                    panStartX = docPanX;
                    panStartY = docPanY;
                    updateCursor();
                }, { passive: false });

                viewer.addEventListener('touchmove', function(e) {
                    if (!isDragging || e.touches.length !== 1) return;
                    e.preventDefault();
                    docPanX = panStartX + (e.touches[0].clientX - dragStartX);
                    docPanY = panStartY + (e.touches[0].clientY - dragStartY);
                    updateDocTransform();
                }, { passive: false });

                viewer.addEventListener('touchend', function() {
                    isDragging = false;
                    updateCursor();
                });

                // Wheel zoom
                viewer.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    if (e.deltaY < 0) {
                        window.zoomDoc(ZOOM_STEP);
                    } else {
                        window.zoomDoc(-ZOOM_STEP);
                    }
                }, { passive: false });

                // Double-click toggle 1x / 2x
                viewer.addEventListener('dblclick', function(e) {
                    e.preventDefault();
                    if (docZoom === 1) {
                        docZoom = 2;
                    } else {
                        docZoom = 1;
                        docPanX = 0;
                        docPanY = 0;
                    }
                    updateDocTransform();
                });

                // Keyboard
                document.addEventListener('keydown', function(e) {
                    if (!modal.classList.contains('is-open')) return;
                    if (e.key === 'Escape') {
                        window.closeDocModal();
                    } else if (e.key === '+' || e.key === '=') {
                        e.preventDefault();
                        window.zoomDoc(ZOOM_STEP);
                    } else if (e.key === '-') {
                        e.preventDefault();
                        window.zoomDoc(-ZOOM_STEP);
                    } else if (e.key === '0') {
                        e.preventDefault();
                        window.resetDocZoom();
                    }
                });
            })();
            @endif
        });
    </script>
    @endpush
</x-app>
