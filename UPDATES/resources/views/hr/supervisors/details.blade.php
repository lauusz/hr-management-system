<x-app title="Detail Supervisor">
    @php
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    @endphp

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Supervisor & Manager</h1>
                <p class="section-subtitle">Kelola jatah OFF supervisor terpilih</p>
            </div>
        </div>
    </x-slot>

    <div class="spvd-container">
        <a href="{{ route('hr.supervisors.index') }}" class="back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke Supervisor
        </a>

        @if(session('success'))
            <div class="spvd-alert">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="spvd-alert spvd-alert--error">{{ $errors->first() }}</div>
        @endif

        <section class="spvd-profile">
            <div class="spvd-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            <div class="spvd-profile__body">
                <span class="spvd-kicker">Supervisor</span>
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->position?->name ?? 'Jabatan belum diatur' }} · {{ $user->division?->name ?? 'Divisi belum diatur' }}</p>
            </div>
            <form method="GET" class="spvd-year">
                <label for="year">Tahun</label>
                <select id="year" name="year" onchange="this.form.submit()">
                    @for($optionYear = 2026; $optionYear <= max(2026, $year); $optionYear++)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                    @endfor
                </select>
            </form>
        </section>

        <div class="spvd-heading">
            <div>
                <h2>Analytics OFF SPV</h2>
                <p>Ringkasan jatah periode {{ $year }} berdasarkan cutoff tanggal 26–25.</p>
            </div>
        </div>

        <section class="spvd-stats">
            <article><span>Total Jatah</span><strong>{{ $totals['quota'] }}</strong><small>Efektif {{ $year }}</small></article>
            <article><span>Digunakan</span><strong>{{ $totals['approved'] }}</strong><small>Sudah disetujui</small></article>
            <article><span>Pending</span><strong>{{ $totals['pending'] }}</strong><small>Menunggu proses</small></article>
            <article><span>Sisa Aktif</span><strong>{{ $totals['remaining'] }}</strong><small>Tidak pernah minus</small></article>
            <article><span>Hangus</span><strong>{{ $totals['expired'] }}</strong><small>Tidak dibawa ke bulan berikutnya</small></article>
        </section>

        <section class="spvd-card">
            <div class="spvd-card__head">
                <div>
                    <h3>Jatah Bulanan</h3>
                    <p>Jatah dasar dihitung dari jumlah Sabtu dikurangi 2.</p>
                </div>
            </div>
            <div class="spvd-table-wrap">
                <table class="spvd-table">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Cutoff</th>
                            <th>Sabtu</th>
                            <th>Dasar</th>
                            <th>Efektif</th>
                            <th>Digunakan</th>
                            <th>Pending</th>
                            <th>Sisa/Hangus/Kelebihan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $monthNumber => $monthName)
                            @php($period = $periods->get($monthNumber))
                            <tr>
                                <td><strong>{{ $monthName }} {{ $year }}</strong></td>
                                @if($period)
                                    <td>{{ $period->period_start->format('d M') }}–{{ $period->period_end->format('d M') }}</td>
                                    <td>{{ $period->saturday_count }}</td>
                                    <td>{{ $period->base_quota }}</td>
                                    <td><span class="spvd-pill">{{ $period->effective_quota }}</span></td>
                                    <td>{{ $period->analytics['approved'] }}</td>
                                    <td>{{ $period->analytics['pending'] }}</td>
                                    <td>
                                        @if($period->analytics['excess'] > 0)
                                            <span class="spvd-pill spvd-pill--red">{{ $period->analytics['excess'] }} kelebihan</span>
                                        @elseif($period->period_end->isBefore(today()))
                                            <span class="spvd-pill spvd-pill--muted">{{ $period->analytics['expired'] }} hangus</span>
                                        @else
                                            <span class="spvd-pill spvd-pill--green">{{ $period->analytics['remaining'] }} tersisa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="spvd-actions">
                                            <button type="button" class="spvd-edit" data-modal-open="quota-{{ $period->id }}">Ubah</button>
                                            @if($historicalPeriods->has($monthNumber))
                                                <button type="button" class="spvd-edit" data-modal-open="history-{{ $monthNumber }}">Input Data Lampau</button>
                                            @endif
                                        </div>
                                    </td>
                                @elseif($historicalPeriods->has($monthNumber))
                                    @php($historical = $historicalPeriods->get($monthNumber))
                                    <td>{{ $historical['start']->format('d M') }}–{{ $historical['end']->format('d M') }}</td>
                                    <td>{{ $historical['saturday_count'] }}</td>
                                    <td>{{ $historical['base_quota'] }}</td>
                                    <td><span class="spvd-pill">{{ $historical['base_quota'] }}</span></td>
                                    <td colspan="3"><span class="spvd-empty-row">-</span></td>
                                    <td>
                                        <button type="button" class="spvd-edit" data-modal-open="history-{{ $monthNumber }}">Input Data Lampau</button>
                                    </td>
                                @else
                                    <td colspan="8"><span class="spvd-empty-row">{{ $monthNumber < 8 && $year === 2026 ? 'Data legacy — tidak dikelola sistem baru' : '-' }}</span></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="spvd-grid">
            <section class="spvd-card">
                <div class="spvd-card__head"><div><h3>Riwayat Pengajuan</h3><p>Pengajuan OFF pada tahun terpilih.</p></div></div>
                <div class="spvd-list">
                    @forelse($requests as $leave)
                        <div class="spvd-list__item">
                            <div><strong>{{ $leave->start_date->translatedFormat('l, d F Y') }}</strong><span>{{ $leave->reason ?: 'Tanpa keterangan' }}</span></div>
                            <span class="spvd-status">{{ $leave->status_label }}</span>
                        </div>
                    @empty
                        <div class="spvd-empty">Belum ada pengajuan OFF SPV.</div>
                    @endforelse
                </div>
            </section>

            <section class="spvd-card">
                <div class="spvd-card__head"><div><h3>Audit Perubahan</h3><p>Setiap perubahan jatah tersimpan permanen.</p></div></div>
                <div class="spvd-list">
                    @forelse($changes as $change)
                        <div class="spvd-list__item">
                            <div>
                                <strong>{{ $months[$change->period->period_month] }}: {{ $change->quota_before ?? '–' }} → {{ $change->quota_after }}</strong>
                                <span>{{ $change->reason ?: 'Tanpa catatan' }} · {{ $change->actor?->name ?? 'Sistem' }}</span>
                            </div>
                            <time>{{ $change->created_at?->format('d/m/Y H:i') }}</time>
                        </div>
                    @empty
                        <div class="spvd-empty">Belum ada perubahan jatah.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @foreach($periods as $period)
        <x-modal :id="'quota-' . $period->id" :title="'Ubah Jatah ' . $months[$period->period_month]" type="form" variant="info">
            <form method="POST" action="{{ route('hr.supervisors.off-spv-periods.update', [$user, $period]) }}" class="spvd-form">
                @csrf
                @method('PATCH')
                <label>Jatah efektif
                    <input type="number" name="effective_quota" value="{{ $period->effective_quota }}" required>
                </label>
                <label>Alasan atau catatan
                    <textarea name="reason" rows="3" placeholder="Opsional"></textarea>
                </label>
                <p>Perubahan langsung berlaku. Jika pemakaian lebih besar, sisa tetap ditampilkan 0.</p>
                <div class="spvd-form__actions">
                    <button type="button" data-modal-close="true">Batal</button>
                    <button type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </x-modal>
    @endforeach

    @foreach($historicalPeriods as $monthNumber => $historical)
        @php($historicalPeriod = $periods->get($monthNumber))
        <x-modal :id="'history-' . $monthNumber" :title="'Input Data Lampau ' . $months[$monthNumber]" type="form" variant="info">
            <form method="POST" action="{{ route('hr.supervisors.off-spv-history.store', $user) }}" class="spvd-form">
                @csrf
                <input type="hidden" name="period_year" value="2026">
                <input type="hidden" name="period_month" value="{{ $monthNumber }}">

                <div class="spvd-history-meta">
                    <span>Cutoff <strong>{{ $historical['start']->format('d M Y') }}–{{ $historical['end']->format('d M Y') }}</strong></span>
                    <span>Sabtu <strong>{{ $historical['saturday_count'] }}</strong></span>
                    <span>Jatah dasar <strong>{{ $historical['base_quota'] }}</strong></span>
                </div>

                <label>Jatah efektif
                    <input type="number" name="effective_quota" value="{{ $historicalPeriod?->effective_quota ?? $historical['base_quota'] }}" required>
                </label>

                <fieldset class="spvd-dates">
                    <legend>Tanggal OFF yang sudah digunakan</legend>
                    @foreach($historical['saturdays'] as $date)
                        @php($existing = $historicalDates->get($date))
                        <label class="spvd-date">
                            <input type="checkbox" name="dates[]" value="{{ $date }}" @checked($existing)>
                            <span>{{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d M Y') }}</span>
                            @if($existing)<small>Sudah tercatat</small>@endif
                        </label>
                    @endforeach
                </fieldset>

                <label>Catatan
                    <textarea name="reason" rows="3" placeholder="Opsional"></textarea>
                </label>
                <p>Centang tanggal yang digunakan. Tanggal yang dilepas akan dibatalkan dan tetap tersimpan untuk audit.</p>
                <div class="spvd-form__actions">
                    <button type="button" data-modal-close="true">Batal</button>
                    <button type="submit">Simpan Data Lampau</button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.modalOpen);
                if (modal) modal.style.display = 'flex';
            });
        });
        document.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal-backdrop');
                if (modal) modal.style.display = 'none';
            });
        });
    </script>

    <style>
        .section-header-inline{display:flex;align-items:center;gap:10px}
        .section-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .section-title{margin:0;font-size:1rem;font-weight:800;color:var(--text-primary,#111827);letter-spacing:-.01em;line-height:1.25}
        .section-subtitle{margin:0;font-size:.8125rem;color:var(--text-muted,#6B7280);font-weight:500;line-height:1.35}
        .icon-navy{background:rgba(10,61,98,.08);color:var(--primary-dark,#0A3D62)}
        .spvd-container{--spvd-blue:var(--primary,#145DA0);--spvd-navy:var(--text-primary,#111827);--spvd-muted:var(--text-muted,#6B7280);--spvd-line:var(--border-light,#E5E7EB);--spvd-bg:var(--gray-50,#F5F7FA);max-width:1180px;margin:0 auto;padding:20px 16px 48px;color:var(--spvd-navy)}
        .back-btn{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px 0 10px;margin-bottom:16px;background:var(--white,#fff);border:1px solid var(--border-light,#E5E7EB);border-radius:10px;color:var(--text-muted,#6B7280);text-decoration:none;transition:all .15s ease;box-shadow:0 1px 2px rgba(0,0,0,.04);font-size:.75rem;font-weight:600}.back-btn:hover{border-color:var(--primary,#145DA0);color:var(--primary,#145DA0);background:var(--gray-50,#F5F7FA)}.back-btn svg{flex-shrink:0;transition:transform .2s ease}.back-btn:hover svg{transform:translateX(-2px)}
        .spvd-alert{padding:12px 16px;margin-bottom:16px;border:1px solid #bbf7d0;border-radius:10px;background:#f0fdf4;color:#15803d}.spvd-alert--error{border-color:#fecaca;background:#fef2f2;color:#b91c1c}
        .spvd-profile{display:flex;align-items:center;gap:16px;padding:20px;background:#fff;border:1px solid var(--spvd-line);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}.spvd-avatar{width:54px;height:54px;display:grid;place-items:center;border-radius:14px;background:rgba(10,61,98,.08);color:var(--primary-dark,#0A3D62);font-size:1.2rem;font-weight:800}.spvd-profile__body{flex:1}.spvd-kicker{font-size:.7rem;font-weight:800;color:var(--spvd-blue);text-transform:uppercase;letter-spacing:.08em}.spvd-profile h1{margin:2px 0;font-size:1.35rem}.spvd-profile p,.spvd-heading p,.spvd-card__head p{margin:0;color:var(--spvd-muted);font-size:.84rem}.spvd-year label{display:block;font-size:.7rem;font-weight:700;color:var(--spvd-muted);margin-bottom:4px}.spvd-year select{padding:8px 32px 8px 10px;border:1px solid var(--spvd-line);border-radius:8px;background:#fff;color:var(--spvd-navy)}
        .spvd-heading{margin:24px 0 14px}.spvd-heading h2{margin:0 0 4px;font-size:1.15rem}
        .spvd-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px}.spvd-stats article{padding:16px;background:#fff;border:1px solid var(--spvd-line);border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.04)}.spvd-stats span,.spvd-stats small{display:block;color:var(--spvd-muted);font-size:.75rem}.spvd-stats strong{display:block;font-size:1.65rem;margin:5px 0;color:var(--primary-dark,#0A3D62)}
        .spvd-card{background:#fff;border:1px solid var(--spvd-line);border-radius:16px;overflow:hidden;margin-bottom:18px;box-shadow:0 1px 3px rgba(0,0,0,.04)}.spvd-card__head{padding:16px 18px;border-bottom:1px solid var(--spvd-line)}.spvd-card__head h3{margin:0 0 3px;font-size:1rem}.spvd-table-wrap{overflow-x:auto}.spvd-table{width:100%;border-collapse:collapse;min-width:850px}.spvd-table th{padding:11px 14px;text-align:left;background:var(--spvd-bg);color:var(--spvd-muted);font-size:.68rem;text-transform:uppercase;letter-spacing:.05em}.spvd-table td{padding:12px 14px;border-top:1px solid var(--spvd-line);font-size:.82rem}.spvd-pill{display:inline-flex;padding:3px 8px;border-radius:999px;background:rgba(20,93,160,.08);color:var(--spvd-blue);font-weight:700}.spvd-pill--green{background:#f0fdf4;color:#15803d}.spvd-pill--muted{background:#f1f5f9;color:#64748b}.spvd-pill--red{background:var(--danger-light,#fef2f2);color:#b91c1c}.spvd-empty-row{color:#94a3b8;font-size:.78rem}.spvd-actions{display:flex;gap:6px;white-space:nowrap}.spvd-edit{padding:6px 10px;border:0;border-radius:7px;background:rgba(20,93,160,.08);color:var(--spvd-blue);font-weight:700;cursor:pointer}.spvd-edit:hover{background:rgba(20,93,160,.14)}
        .spvd-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.spvd-list__item{display:flex;justify-content:space-between;gap:12px;padding:13px 18px;border-bottom:1px solid var(--spvd-line);font-size:.8rem}.spvd-list__item:last-child{border-bottom:0}.spvd-list__item strong,.spvd-list__item span{display:block}.spvd-list__item span,.spvd-list__item time{margin-top:3px;color:var(--spvd-muted);font-size:.74rem}.spvd-status{white-space:nowrap;font-weight:700}.spvd-empty{padding:28px;text-align:center;color:#94a3b8;font-size:.82rem}
        .spvd-form{--spvd-blue:var(--primary,#145DA0);--spvd-navy:var(--text-primary,#111827);--spvd-muted:var(--text-muted,#6B7280);--spvd-line:var(--border-light,#E5E7EB);--spvd-bg:var(--gray-50,#F5F7FA)}.spvd-form label{display:block;margin-bottom:14px;font-size:.8rem;font-weight:700}.spvd-form input,.spvd-form textarea{display:block;width:100%;box-sizing:border-box;margin-top:5px;padding:10px;border:1px solid var(--spvd-line);border-radius:8px;font:inherit}.spvd-form p{color:var(--spvd-muted);font-size:.76rem}.spvd-history-meta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;padding:10px;border-radius:9px;background:var(--spvd-bg);font-size:.75rem;color:var(--spvd-muted)}.spvd-history-meta strong{color:var(--spvd-navy)}.spvd-dates{margin:0 0 14px;padding:12px;border:1px solid var(--spvd-line);border-radius:9px}.spvd-dates legend{padding:0 5px;font-size:.8rem;font-weight:700}.spvd-form .spvd-date{display:grid;grid-template-columns:18px 1fr auto;align-items:center;gap:8px;margin:0;padding:8px 0;border-bottom:1px solid var(--spvd-line);font-weight:600}.spvd-form .spvd-date:last-child{border-bottom:0}.spvd-form .spvd-date input{width:16px;height:16px;margin:0;padding:0}.spvd-date small{color:var(--spvd-muted);font-weight:600}.spvd-form__actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}.spvd-form__actions button{padding:9px 14px;border:1px solid var(--spvd-line);border-radius:8px;background:#fff;font-weight:700;cursor:pointer}.spvd-form__actions button[type=submit]{border-color:var(--spvd-blue);background:var(--spvd-blue);color:#fff}
        @media(max-width:800px){.spvd-stats{grid-template-columns:repeat(2,1fr)}.spvd-grid{grid-template-columns:1fr}.spvd-profile{align-items:flex-start;flex-wrap:wrap}.spvd-year{width:100%}.spvd-year select{width:100%}}
    </style>
</x-app>
