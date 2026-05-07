<x-app title="Master Hutang Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Master Hutang Karyawan</h1>
                <p class="section-subtitle">Kelola dan proses semua pengajuan pinjaman & kasbon karyawan</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="ln-alert ln-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ln-alert ln-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- SUMMARY STATS                                  --}}
    {{-- ============================================== --}}
    <div class="ln-stats">
        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-stat-icon--total">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="ln-stat-content">
                <div class="ln-stat-value">{{ $totalCount }}</div>
                <div class="ln-stat-label">Total Pengajuan</div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-stat-icon--pending">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ln-stat-content">
                <div class="ln-stat-value ln-stat-value--warning">{{ $pendingCount }}</div>
                <div class="ln-stat-label">Menunggu HRD</div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-stat-icon--approved">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ln-stat-content">
                <div class="ln-stat-value ln-stat-value--success">{{ $approvedCount }}</div>
                <div class="ln-stat-label">Disetujui</div>
            </div>
        </div>

        <div class="ln-stat-card">
            <div class="ln-stat-icon ln-stat-icon--rejected">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ln-stat-content">
                <div class="ln-stat-value ln-stat-value--error">{{ $rejectedCount }}</div>
                <div class="ln-stat-label">Ditolak</div>
            </div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER CARD                                    --}}
    {{-- ============================================== --}}
    <div class="ln-filter">
        <form method="GET" action="{{ route('hr.loan_requests.index') }}">
            <div class="ln-filter-body">
                <div class="ln-filter-group">
                    <label class="ln-filter-label">Status Pengajuan</label>
                    <select name="status" class="ln-filter-input">
                        <option value="">Semua Status</option>
                        <option value="PENDING_HRD" @selected(request('status') === 'PENDING_HRD')>Menunggu HRD</option>
                        <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui</option>
                        <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak</option>
                        <option value="LUNAS" @selected(request('status') === 'LUNAS')>Lunas</option>
                    </select>
                </div>

                <div class="ln-filter-group ln-filter-group--grow">
                    <label class="ln-filter-label">Cari Karyawan</label>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari nama karyawan..."
                           class="ln-filter-input">
                </div>

                <div class="ln-filter-group">
                    <label class="ln-filter-label">Tgl Pengajuan</label>
                    <input type="date"
                           name="submitted_at"
                           value="{{ request('submitted_at') }}"
                           class="ln-filter-input">
                </div>

                <div class="ln-filter-actions">
                    <button type="submit" class="ln-btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>

                    @if(request('status') || request('q') || request('submitted_at'))
                        <a href="{{ route('hr.loan_requests.index') }}" class="ln-btn-reset">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================== --}}
    {{-- MOBILE CARD LIST                               --}}
    {{-- ============================================== --}}
    <div class="ln-list">
        @forelse($loans as $loan)
            @php
                $st = $loan->status;
                $badgeClass = 'ln-badge--gray';
                $statusLabel = $st;
                $statusIcon = '';

                if ($st === 'PENDING_HRD') {
                    $badgeClass = 'ln-badge--warning';
                    $statusLabel = 'Menunggu HRD';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === 'APPROVED') {
                    $badgeClass = 'ln-badge--success';
                    $statusLabel = 'Disetujui';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === 'REJECTED') {
                    $badgeClass = 'ln-badge--error';
                    $statusLabel = 'Ditolak';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === 'LUNAS') {
                    $badgeClass = 'ln-badge--info';
                    $statusLabel = 'Lunas';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }

                $method = $loan->payment_method;
                if ($method === 'TUNAI') $methodLabel = 'Tunai';
                elseif ($method === 'CICILAN') $methodLabel = 'Cicilan';
                elseif ($method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
                else $methodLabel = $method;

                $cardAccent = '';
                if ($st === 'PENDING_HRD') $cardAccent = 'ln-card--pending';
                elseif ($st === 'APPROVED') $cardAccent = 'ln-card--approved';
                elseif ($st === 'REJECTED') $cardAccent = 'ln-card--rejected';
                elseif ($st === 'LUNAS') $cardAccent = 'ln-card--lunas';
            @endphp

            <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="ln-card {{ $cardAccent }}">
                <div class="ln-card-top">
                    <span class="ln-method-badge">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ $methodLabel }}
                    </span>

                    <span class="ln-badge {{ $badgeClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="ln-card-employee">
                    <div class="ln-avatar">{{ substr($loan->snapshot_name, 0, 1) }}</div>
                    <div class="ln-employee-info">
                        <span class="ln-employee-name">{{ $loan->snapshot_name }}</span>
                        <span class="ln-employee-detail">{{ $loan->snapshot_position ?? '-' }} — {{ $loan->snapshot_company ?? '-' }}</span>
                    </div>
                </div>

                <div class="ln-card-amount">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="ln-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                </div>

                @if($loan->amount_in_words)
                    <div class="ln-card-note">{{ Str::limit($loan->amount_in_words, 60) }}</div>
                @endif

                <div class="ln-card-footer">
                    <div class="ln-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                    </div>
                    <div class="ln-card-action" title="Lihat Detail">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="ln-empty">
                <div class="ln-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="ln-empty-title">Belum Ada Pengajuan</h3>
                <p class="ln-empty-desc">Tidak ada pengajuan pinjaman yang ditemukan dengan filter saat ini.</p>
            </div>
        @endforelse
    </div>

    {{-- ============================================== --}}
    {{-- DESKTOP TABLE                                  --}}
    {{-- ============================================== --}}
    <div class="ln-table-wrap">
        <div class="ln-table-card">
            <div class="ln-table-responsive">
                <table class="ln-table">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Karyawan</th>
                            <th>Besar Pinjaman</th>
                            <th>Metode Bayar</th>
                            <th>Tgl Pengajuan</th>
                            <th>Status</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            @php
                                $st = $loan->status;
                                $badgeClass = 'ln-badge--gray';
                                $statusLabel = $st;

                                if ($st === 'PENDING_HRD') {
                                    $badgeClass = 'ln-badge--warning';
                                    $statusLabel = 'Menunggu HRD';
                                } elseif ($st === 'APPROVED') {
                                    $badgeClass = 'ln-badge--success';
                                    $statusLabel = 'Disetujui';
                                } elseif ($st === 'REJECTED') {
                                    $badgeClass = 'ln-badge--error';
                                    $statusLabel = 'Ditolak';
                                } elseif ($st === 'LUNAS') {
                                    $badgeClass = 'ln-badge--info';
                                    $statusLabel = 'Lunas';
                                }

                                $method = $loan->payment_method;
                                if ($method === 'TUNAI') $methodLabel = 'Tunai';
                                elseif ($method === 'CICILAN') $methodLabel = 'Cicilan';
                                elseif ($method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
                                else $methodLabel = $method;
                            @endphp

                            <tr class="ln-table-row" onclick="window.location.href='{{ route('hr.loan_requests.show', $loan->id) }}'">
                                <td>
                                    <div class="ln-table-user">
                                        <div class="ln-table-avatar">{{ substr($loan->snapshot_name, 0, 1) }}</div>
                                        <div class="ln-table-user-info">
                                            <span class="ln-table-user-name">{{ $loan->snapshot_name }}</span>
                                            <span class="ln-table-user-detail">{{ $loan->snapshot_position ?? '-' }} — {{ $loan->snapshot_company ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="ln-table-money">
                                        <span class="ln-table-money-amount">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                                        <span class="ln-table-money-words" title="{{ $loan->amount_in_words }}">
                                            {{ Str::limit($loan->amount_in_words, 40) }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="ln-method-badge">{{ $methodLabel }}</span>
                                </td>

                                <td>
                                    <span class="ln-table-date">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                                </td>

                                <td>
                                    <span class="ln-badge {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="ln-actions-cell" onclick="event.stopPropagation()">
                                    <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="ln-action-btn" title="Lihat Detail">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ln-table-empty">
                                    <div class="ln-empty">
                                        <div class="ln-empty-icon">
                                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="ln-empty-title">Belum Ada Pengajuan</h3>
                                        <p class="ln-empty-desc">Tidak ada pengajuan pinjaman yang ditemukan dengan filter saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :items="$loans" />

    <style>
        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .ln-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .ln-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .ln-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* STATS GRID                                 */
        /* ========================================== */
        .ln-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .ln-stat-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .ln-stat-card:hover {
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-1px);
        }
        .ln-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ln-stat-icon svg { width: 16px; height: 16px; }
        .ln-stat-icon--total    { background: rgba(20, 93, 160, 0.08); color: var(--primary, #145DA0); }
        .ln-stat-icon--pending  { background: rgba(245, 158, 11, 0.1);  color: #a16207; }
        .ln-stat-icon--approved { background: rgba(34, 197, 94, 0.1);   color: #15803d; }
        .ln-stat-icon--rejected { background: rgba(239, 68, 68, 0.1);   color: #b91c1c; }
        .ln-stat-content { flex: 1; min-width: 0; }
        .ln-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.2;
        }
        .ln-stat-value--success { color: var(--success, #22C55E); }
        .ln-stat-value--warning { color: var(--warning, #F59E0B); }
        .ln-stat-value--error   { color: var(--error, #EF4444); }
        .ln-stat-label {
            font-size: 10px;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 600;
            margin-top: 2px;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .ln-filter {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .ln-filter-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .ln-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .ln-filter-group--grow { flex: 2; min-width: 200px; }
        .ln-filter-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ln-filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .ln-filter-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .ln-filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .ln-btn-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--primary, #145DA0);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            flex: 1;
        }
        .ln-btn-filter:hover {
            background: var(--primary-dark, #0A3D62);
        }
        .ln-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white, #FFFFFF);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
        }
        .ln-btn-reset:hover {
            background: rgba(239, 68, 68, 0.04);
            border-color: rgba(239, 68, 68, 0.25);
            color: var(--error, #EF4444);
        }

        /* ========================================== */
        /* CARD LIST (MOBILE DEFAULT)                 */
        /* ========================================== */
        .ln-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ln-card {
            display: block;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .ln-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .ln-card--pending  { border-left: 4px solid var(--warning, #F59E0B); }
        .ln-card--approved { border-left: 4px solid var(--success, #22C55E); }
        .ln-card--rejected { border-left: 4px solid var(--error, #EF4444); opacity: 0.85; }
        .ln-card--lunas    { border-left: 4px solid var(--info, #3B82F6); }

        .ln-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* Method badge */
        .ln-method-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #F8FAFC;
            color: var(--text-secondary, #374151);
        }

        /* Status badge (pill) */
        .ln-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .ln-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .ln-badge--success { background: rgba(34, 197, 94, 0.1);  color: #15803d; }
        .ln-badge--error   { background: rgba(239, 68, 68, 0.1);   color: #b91c1c; }
        .ln-badge--info    { background: rgba(59, 130, 246, 0.1);  color: #1d4ed8; }
        .ln-badge--gray    { background: #F8FAFC;                  color: var(--text-secondary, #374151); }

        /* Employee info */
        .ln-card-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .ln-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ln-employee-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .ln-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ln-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Amount */
        .ln-card-amount {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 6px;
        }
        .ln-card-amount svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .ln-amount-value {
            color: var(--primary, #145DA0);
        }

        /* Note */
        .ln-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* Footer */
        .ln-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
        }
        .ln-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .ln-card-meta svg {
            flex-shrink: 0;
        }
        .ln-card-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted, #6B7280);
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .ln-card:hover .ln-card-action {
            background: var(--gray-50, #F5F7FA);
            color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .ln-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .ln-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50, #F5F7FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light, #9CA3AF);
        }
        .ln-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .ln-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* DESKTOP TABLE (hidden on mobile)           */
        /* ========================================== */
        .ln-table-wrap {
            display: none;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .ln-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
            }
            .ln-stat-card {
                padding: 16px;
                gap: 12px;
            }
            .ln-stat-icon {
                width: 40px;
                height: 40px;
            }
            .ln-stat-icon svg { width: 18px; height: 18px; }
            .ln-stat-value { font-size: 22px; }
            .ln-stat-label { font-size: 11px; }

            .ln-filter { padding: 18px 20px; }
            .ln-filter-body {
                flex-direction: row;
                align-items: flex-end;
                flex-wrap: wrap;
            }
            .ln-filter-group {
                flex: 1;
                min-width: 160px;
            }
            .ln-filter-actions {
                flex-shrink: 0;
            }
            .ln-btn-filter,
            .ln-btn-reset {
                flex: none;
            }
        }

        @media (min-width: 768px) {
            .ln-card { padding: 18px 20px; }
            .ln-filter { padding: 20px; }
        }

        @media (min-width: 1024px) {
            .ln-list {
                display: none;
            }
            .ln-table-wrap {
                display: block;
            }
            .ln-table-card {
                background: var(--white, #FFFFFF);
                border-radius: 16px;
                border: 1px solid var(--border-light, #E5E7EB);
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                overflow: hidden;
            }
            .ln-table-responsive {
                width: 100%;
                overflow-x: auto;
            }
            .ln-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 800px;
            }
            .ln-table th {
                background: var(--gray-50, #F5F7FA);
                padding: 12px 16px;
                text-align: left;
                font-size: 0.6875rem;
                font-weight: 700;
                color: var(--text-muted, #6B7280);
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border-bottom: 1px solid var(--border-light, #E5E7EB);
            }
            .ln-table td {
                padding: 14px 16px;
                border-bottom: 1px solid var(--border-light, #E5E7EB);
                font-size: 0.8125rem;
                color: var(--text-primary, #111827);
                vertical-align: middle;
            }
            .ln-table tr:last-child td { border-bottom: none; }
            .ln-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
            .ln-table-row { cursor: pointer; }
            .ln-table-row:hover { background: rgba(20, 93, 160, 0.02); }

            /* Table user */
            .ln-table-user {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .ln-table-avatar {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: rgba(20, 93, 160, 0.08);
                color: var(--primary, #145DA0);
                font-size: 0.8125rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .ln-table-user-info {
                display: flex;
                flex-direction: column;
                gap: 2px;
                min-width: 0;
            }
            .ln-table-user-name {
                font-weight: 600;
                color: var(--text-primary, #111827);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .ln-table-user-detail {
                font-size: 0.75rem;
                color: var(--text-muted, #6B7280);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Table money */
            .ln-table-money {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            .ln-table-money-amount {
                font-weight: 700;
                color: var(--primary, #145DA0);
                font-size: 0.875rem;
            }
            .ln-table-money-words {
                font-size: 0.6875rem;
                color: var(--text-muted, #6B7280);
                font-style: italic;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
                max-width: 180px;
            }

            .ln-table-date {
                font-weight: 500;
                color: var(--text-secondary, #374151);
            }

            .ln-actions-cell {
                text-align: right;
                white-space: nowrap;
            }
            .ln-action-btn {
                width: 32px;
                height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                border: none;
                background: transparent;
                color: var(--text-muted, #6B7280);
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
            }
            .ln-action-btn:hover {
                background: var(--gray-50, #F5F7FA);
                color: var(--primary, #145DA0);
            }

            .ln-table-empty .ln-empty {
                border: none;
                box-shadow: none;
                background: transparent;
            }
        }
    </style>

</x-app>
