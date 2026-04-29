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
            'PENDING_HRD' => ['label' => 'Menunggu Persetujuan', 'color' => 'yellow'],
            'APPROVED' => ['label' => 'Disetujui', 'color' => 'blue'],
            'REJECTED' => ['label' => 'Ditolak', 'color' => 'red'],
            'LUNAS' => ['label' => 'Lunas', 'color' => 'green'],
        ];
        $currentStatus = $statusConfig[$loan->status] ?? ['label' => $loan->status, 'color' => 'gray'];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <a href="{{ route('hr.loan_requests.index') }}" class="back-btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Detail Pinjaman</h1>
            <p class="page-subtitle">{{ $loan->snapshot_name }}</p>
        </div>
        <span class="status-badge status-{{ $currentStatus['color'] }}">{{ $currentStatus['label'] }}</span>
    </div>

    {{-- SUMMARY STATS (Mobile First) --}}
    <div class="stats-row">
        <div class="stat-card stat-main">
            <div class="stat-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</div>
            <div class="stat-label">Total Pinjaman</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $months ?: '-' }}</div>
            <div class="stat-label">Tenor</div>
        </div>
    </div>

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
                    <span class="data-label">Cicilan/User</span>
                    <span class="data-value highlight">{{ $monthlyInstallment ? 'Rp ' . number_format($monthlyInstallment, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Metode</span>
                    <span class="data-value">
                        @if($loan->payment_method === 'TUNAI') Tunai
                        @elseif($loan->payment_method === 'CICILAN') Transfer
                        @elseif($loan->payment_method === 'POTONG_GAJI') Potong Gaji
                        @else -
                        @endif
                    </span>
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
                <a href="{{ asset('storage/' . $loan->document_path) }}" target="_blank" class="btn-doc">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Lihat Dokumen Pendukung
                </a>
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
                    <span class="progress-label">Terakhir</span>
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

    {{-- SECTION: HISTORY PEMBAYARAN --}}
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
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p>Belum ada cicilan yang dibayarkan</p>
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

    {{-- SECTION: CATATAN INTERNAL HRD (Private) - BEFORE ACTION --}}
    <div class="section">
        <div class="section-header">
            <div class="section-icon internal-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="section-title">Catatan Internal HRD</h2>
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
    <div class="section">
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
        <div class="card action-card reject">
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

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        /* ALERT */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: var(--bg-page);
            border-color: var(--navy);
            color: var(--navy);
        }

        .page-header-text { flex: 1; min-width: 0; }

        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 2px 0 0;
        }

        /* STATUS BADGE */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .status-yellow { background: #fef9c3; color: #854d0e; }
        .status-blue { background: #dbeafe; color: #1e3a8a; }
        .status-red { background: #fee2e2; color: #991b1b; }
        .status-green { background: #dcfce7; color: #166534; }
        .status-gray { background: #f3f4f6; color: #374151; }

        /* STATS ROW */
        .stats-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
        }

        .stat-main {
            background: var(--navy);
            border-color: var(--navy);
        }

        .stat-main .stat-value { color: var(--white); }
        .stat-main .stat-label { color: rgba(255,255,255,0.8); }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
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
            background: #eff6ff;
            border-radius: 8px;
            color: var(--navy);
            flex-shrink: 0;
        }

        .section-icon.note-public-icon { background: #dbeafe; color: #1e3a8a; }
        .section-icon.action-icon { background: #dcfce7; color: #166534; }
        .section-icon.internal-icon { background: #fef3c7; color: #92400e; }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            flex: 1;
        }

        .btn-edit-loan {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-edit-loan:hover {
            background: #dbeafe;
        }

        .btn-edit-loan svg {
            width: 16px;
            height: 16px;
        }

        /* CARD */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        /* DATA GRID */
        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--border);
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
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-value {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .data-value.highlight {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
        }

        /* DOCUMENT LINK */
        .document-link {
            padding: 12px 14px;
            border-top: 1px solid var(--border);
        }

        .btn-doc {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-doc:hover { background: #dbeafe; }

        /* NOTE PUBLIC CARD */
        .note-public-card {
            background: #eff6ff;
            border-color: #bfdbfe;
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
            border-bottom: 1px solid var(--border);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .progress-label {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .progress-percent {
            font-size: 14px;
            font-weight: 700;
            color: var(--navy);
        }

        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--navy), #3b82f6);
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

        .summary-row span { color: var(--text-secondary); }
        .summary-row strong { color: var(--text-primary); }
        .summary-row.paid strong { color: #059669; }
        .summary-row.remaining strong { color: #dc2626; font-size: 15px; }

        .summary-divider {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        /* REPAYMENT FORM */
        .repayment-form {
            padding: 14px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }

        .repayment-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
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
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: border-color 0.2s;
            line-height: 1.5;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-input.text-large {
            font-size: 16px;
            font-weight: 600;
            color: var(--navy);
        }

        .label-hint {
            font-size: 10px;
            font-weight: 400;
            color: var(--text-muted);
            font-style: italic;
            text-transform: none;
        }

        .req { color: #dc2626; }

        .btn-primary {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover { background: var(--navy-dark); }

        /* REPAYMENT WARNING */
        .repayment-warning {
            display: flex;
            gap: 12px;
            padding: 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            margin-bottom: 14px;
        }

        .warning-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fee2e2;
            border-radius: 10px;
            color: #dc2626;
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
        .warning-extra { color: #dc2626; }
        .text-red { color: #dc2626; }

        .warning-note {
            font-size: 12px;
            color: #b91c1c;
            margin: 0;
            padding: 8px 10px;
            background: #fee2e2;
            border-radius: 6px;
        }

        .confirm-form {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
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
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .btn-confirm {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: #16a34a;
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-confirm:hover { background: #15803d; }

        /* ADD / EDIT REPAYMENT FORM */
        .repayment-form {
            padding: 14px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }

        .repayment-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin: 0 0 12px;
        }

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
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel-form:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
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
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit-repayment {
            background: #eff6ff;
            color: #2563eb;
        }

        .btn-edit-repayment:hover {
            background: #dbeafe;
        }

        .btn-delete-repayment {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-delete-repayment:hover {
            background: #fee2e2;
        }

        /* ADD REPAYMENT BUTTON */
        .btn-add-repayment {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: auto;
        }

        .btn-add-repayment:hover {
            background: var(--navy-dark);
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
            background: #f0fdf4;
            color: #166534;
            font-size: 14px;
            font-weight: 600;
        }

        /* EMPTY STATE */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state svg { margin-bottom: 12px; }
        .empty-state p { margin: 0; font-size: 13px; }

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
            border-bottom: 1px solid var(--border);
        }

        .history-item:last-child { border-bottom: none; }

        .history-index {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            flex-shrink: 0;
        }

        .history-content { flex: 1; }

        .history-main {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .history-date { font-size: 13px; color: var(--text-primary); }
        .history-amount { font-size: 13px; font-weight: 700; color: var(--text-primary); }

        .history-meta {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .history-method {
            font-size: 11px;
            color: var(--text-muted);
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .history-note {
            font-size: 11px;
            color: var(--text-muted);
            font-style: italic;
        }

        /* ACTION CARD */
        .action-card {
            margin-bottom: 16px;
            border-radius: 14px;
            overflow: hidden;
        }

        .action-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .action-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .action-badge.approve { background: #dcfce7; color: #166534; }
        .action-badge.reject { background: #fee2e2; color: #991b1b; }

        .action-hint {
            font-size: 13px;
            color: var(--text-muted);
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
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
        }

        .btn-approve {
            background: #16a34a;
            color: var(--white);
        }

        .btn-approve:hover { background: #15803d; }

        .btn-reject {
            background: #dc2626;
            color: var(--white);
        }

        .btn-reject:hover { background: #b91c1c; }

        /* INTERNAL CARD */
        .internal-card {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .internal-content {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            border-bottom: 1px dashed #fde68a;
        }

        .internal-line {
            font-size: 13px;
            color: #92400e;
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
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            resize: vertical;
        }

        .internal-input-wrapper textarea:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
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
            background: #16a34a;
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save-internal:hover { background: #15803d; }

        .internal-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            color: #92400e;
            background: #fef3c7;
            padding: 3px 8px;
            border-radius: 8px;
        }

        .internal-hint {
            font-size: 11px;
            color: #b45309;
            margin: 0 0 10px 0;
            font-style: italic;
        }

        /* RESPONSIVE */
        @media (min-width: 768px) {
            .page-header {
                margin-bottom: 24px;
            }

            .page-title { font-size: 22px; }
            .page-subtitle { font-size: 14px; }

            .stats-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
            }

            .stat-card { padding: 20px; }

            .stat-value { font-size: 24px; }
            .stat-label { font-size: 12px; }

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
    </style>

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
        });
    </script>
    @endpush
</x-app>