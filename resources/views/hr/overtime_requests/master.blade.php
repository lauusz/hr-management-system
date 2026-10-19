<x-app :title="$pageTitle ?? 'Rekap Lembur'">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Data Lembur</h1>
                <p class="section-subtitle">Riwayat pengajuan lembur seluruh karyawan</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="hom-alert hom-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="hom-alert hom-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @php
        $defaultDateRange = now()->startOfMonth()->format('Y-m-d').' sampai '.now()->endOfMonth()->format('Y-m-d');
        $hasDateFilter = !empty($overtimeDateRange) && $overtimeDateRange !== $defaultDateRange;
        $activeFilterCount = (int) $hasDateFilter + (int) !empty($status);
        $hasAdvancedFilter = $activeFilterCount > 0;
        $statusLabels = [
            \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'Menunggu SPV dan Manager',
            \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
            \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'Disetujui',
            \App\Models\OvertimeRequest::STATUS_REJECTED => 'Ditolak',
            \App\Models\OvertimeRequest::STATUS_CANCELLED => 'Dibatalkan',
        ];
    @endphp

    <div class="hom-card">
        <form method="GET" action="{{ route('hr.overtime-requests.master') }}" id="hom-filter-form" data-default-range="{{ $defaultDateRange }}">
            <div class="hom-search-row">
                <div class="hom-search-wrap">
                    <svg class="hom-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" id="q" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama karyawan..." autocomplete="off" class="hom-search-input" aria-label="Cari nama karyawan" data-overtime-live-search>
                </div>
                <a href="{{ route('hr.overtime-requests.master') }}" id="hom-search-reset" class="hom-btn-reset" @if(trim((string) ($q ?? '')) === '' && !$hasAdvancedFilter) hidden @endif>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                <button type="button" id="hom-filter-toggle" class="hom-btn-toggle {{ $hasAdvancedFilter ? 'active' : '' }}" aria-controls="hom-filter-panel" aria-expanded="{{ $hasAdvancedFilter ? 'true' : 'false' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    <span id="hom-filter-badge" class="hom-filter-badge" @unless($hasAdvancedFilter) hidden @endunless>{{ $activeFilterCount }}</span>
                </button>
            </div>
            <div class="hom-filter-panel" id="hom-filter-panel" @unless($hasAdvancedFilter) hidden @endunless>
                <div class="hom-filter-grid">
                    <div class="hom-filter-field">
                        <label for="overtime_date_range">Tanggal Lembur</label>
                        <input type="text" id="overtime_date_range" name="overtime_date_range" value="{{ $overtimeDateRange ?? '' }}" placeholder="Rentang tanggal..." class="hom-input" autocomplete="off">
                    </div>
                    <div class="hom-filter-field">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="hom-input">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected($status === $opt)>{{ $statusLabels[$opt] ?? $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hom-filter-actions">
                        <button type="submit" class="hom-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="hom-search-error" class="hom-alert hom-alert--error" role="alert" hidden>Hasil pencarian tidak dapat dimuat. Coba lagi atau gunakan tombol Terapkan Filter.</div>
    <div data-overtime-results aria-live="polite" aria-busy="false">

    {{-- ============================================== --}}
    {{-- TABLE CARD                                   --}}
    {{-- ============================================== --}}
    <div class="hom-card hom-card--table">
        <div class="hom-table-wrapper">
            <table class="hom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Karyawan</th>
                        <th>Tanggal Lembur</th>
                        <th>Waktu & Durasi</th>
                        <th>Status / Supervisor</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimes as $i => $overtime)
                        @php
                            $recap = $recapData[$overtime->id] ?? null;

                            $st = $overtime->status;
                            $badgeClass = 'hom-badge--gray';
                            $statusLabel = $overtime->hrd_status_label;
                            $statusIcon = '';

                            if ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) {
                                $badgeClass = 'hom-badge--success';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
                                $badgeClass = 'hom-badge--info';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                                $badgeClass = 'hom-badge--error';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                                $badgeClass = 'hom-badge--warning';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                                $badgeClass = 'hom-badge--gray';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                            }

                        @endphp
                        <tr>
                            <td class="hom-text-muted hom-text-center">
                                {{ $overtimes->firstItem() + $i }}
                            </td>
                            <td>
                                <div class="hom-employee">
                                    <div class="hom-employee-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                                    <div class="hom-employee-info">
                                        <span class="hom-employee-name">{{ $overtime->user->name }}</span>
                                        <span class="hom-employee-role">{{ $overtime->user->division->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="hom-text-date">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                            </td>
                            <td>
                                <span class="hom-text-date">{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</span>
                                <div class="hom-text-muted hom-text-xs" style="font-weight: 600;">{{ $overtime->duration_human }}</div>
                            </td>
                            <td>
                                <span class="hom-badge {{ $badgeClass }}">
                                    {!! $statusIcon !!}
                                    {{ $statusLabel }}
                                </span>
                                @include('overtime_requests._approval_status', ['overtime' => $overtime, 'compact' => true])
                                @if($overtime->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtime->rejection_note)
                                    <div class="hom-rejection-mini">
                                        Note: {{ $overtime->rejection_note }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($recap && $recap['clock_in'])
                                    <span class="hom-text-date">{{ $recap['clock_in']->format('H:i') }}</span>
                                @else
                                    <span class="hom-text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($recap && $recap['clock_out'])
                                    <span class="hom-text-date">{{ $recap['clock_out']->format('H:i') }}</span>
                                @else
                                    <span class="hom-text-muted">-</span>
                                @endif
                            </td>
                            <td class="hom-text-right">
                                <a href="{{ route('hr.overtime-requests.show', $overtime->id) }}" class="hom-action-link" data-overtime-detail aria-haspopup="dialog" aria-controls="hom-detail-modal">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="hom-empty-cell">
                                <div class="hom-empty">
                                    <div class="hom-empty-icon">
                                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="hom-empty-title">Belum Ada Data</h3>
                                    <p class="hom-empty-desc">Belum ada data lembur yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($overtimes->hasPages())
        <div class="hom-pagination">
            <x-pagination :items="$overtimes" />
        </div>
    @endif
    </div>

    <dialog id="hom-detail-modal" aria-labelledby="hom-detail-modal-title">
        <div class="hom-detail-modal-header">
            <h2 id="hom-detail-modal-title">Detail Rekap Lembur</h2>
            <button type="button" id="hom-detail-modal-close" aria-label="Tutup detail lembur">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <iframe id="hom-detail-frame" title="Detail pengajuan lembur"></iframe>
    </dialog>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#overtime_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "2020-01-01",
                locale: { rangeSeparator: " sampai " }
            });
        });
    </script>
    <script data-overtime-detail-modal>
        (() => {
            const dialog = document.getElementById('hom-detail-modal');
            const frame = document.getElementById('hom-detail-frame');
            const closeButton = document.getElementById('hom-detail-modal-close');
            let opener = null;
            let refreshOnClose = false;

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[data-overtime-detail]');
                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || typeof dialog.showModal !== 'function') return;
                event.preventDefault();
                opener = link;
                refreshOnClose = false;
                const url = new URL(link.href, window.location.origin);
                url.searchParams.set('modal', '1');
                frame.src = url.toString();
                dialog.showModal();
            });

            closeButton.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
            frame.addEventListener('load', () => {
                if (!dialog.open || frame.getAttribute('src') === 'about:blank') return;
                try {
                    const content = frame.contentDocument;
                    if (content.querySelector('.hov-alert--success')) refreshOnClose = true;
                    content.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') dialog.close();
                    });
                } catch (error) {
                    // Dokumen dari luar origin aplikasi tidak dapat dibaca.
                }
            });
            dialog.addEventListener('close', () => {
                frame.src = 'about:blank';
                if (refreshOnClose) window.location.reload();
                else opener?.focus();
            });
        })();
    </script>
    <script data-overtime-live-search>
        (() => {
            const form = document.getElementById('hom-filter-form');
            const input = form?.querySelector('[data-overtime-live-search]');
            const results = document.querySelector('[data-overtime-results]');
            const panel = document.getElementById('hom-filter-panel');
            const toggle = document.getElementById('hom-filter-toggle');
            const badge = document.getElementById('hom-filter-badge');
            const reset = document.getElementById('hom-search-reset');
            const errorMessage = document.getElementById('hom-search-error');
            if (!form || !input || !results || !panel || !toggle || !badge || !reset || !errorMessage) return;

            let timer;
            let request;
            const activeCount = () => Number(!!form.elements.status.value) + Number(!!form.elements.overtime_date_range.value && form.elements.overtime_date_range.value !== form.dataset.defaultRange);
            const syncReset = () => { reset.hidden = !input.value.trim() && activeCount() === 0; };
            const syncFilter = () => {
                const count = activeCount();
                badge.textContent = count;
                badge.hidden = count === 0;
                toggle.classList.toggle('active', !panel.hidden || count > 0);
            };

            toggle.addEventListener('click', () => {
                panel.hidden = !panel.hidden;
                toggle.setAttribute('aria-expanded', String(!panel.hidden));
                toggle.classList.toggle('active', !panel.hidden || !badge.hidden);
            });
            const onFilterChange = () => { request?.abort(); syncReset(); };
            form.elements.status.addEventListener('change', onFilterChange);
            form.elements.overtime_date_range.addEventListener('change', onFilterChange);
            form.addEventListener('submit', () => { clearTimeout(timer); request?.abort(); });

            async function loadResults() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                params.delete('page');
                url.search = params.toString();
                const currentRequest = new AbortController();
                request = currentRequest;
                results.classList.add('is-loading');
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian lembur gagal');
                    const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const nextResults = documentResult.querySelector('[data-overtime-results]');
                    if (!nextResults) throw new Error('Hasil pencarian tidak lengkap');
                    if (request !== currentRequest || currentRequest.signal.aborted) return;
                    results.innerHTML = nextResults.innerHTML;
                    window.history.replaceState(null, '', url);
                    syncFilter();
                } catch (error) {
                    if (error.name !== 'AbortError' && request === currentRequest && !currentRequest.signal.aborted) errorMessage.hidden = false;
                } finally {
                    if (request === currentRequest) {
                        results.classList.remove('is-loading');
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                request?.abort();
                errorMessage.hidden = true;
                syncReset();
                timer = setTimeout(loadResults, 350);
            });
        })();
    </script>

    <style>
        #hom-detail-modal {
            width: min(960px, calc(100% - 32px));
            max-width: none;
            height: min(820px, 90vh);
            max-height: calc(100% - 32px);
            padding: 0;
            border: 0;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.2);
        }
        #hom-detail-modal[open] { display: flex; flex-direction: column; }
        #hom-detail-modal::backdrop { background: rgba(0, 0, 0, 0.4); }
        .hom-detail-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .hom-detail-modal-header h2 { margin: 0; font-size: 1rem; color: #111827; }
        .hom-detail-modal-header button {
            display: flex;
            padding: 8px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
        }
        .hom-detail-modal-header button:hover { background: #f3f4f6; color: #111827; }
        #hom-detail-frame { width: 100%; min-height: 0; flex: 1; border: 0; }
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .hom-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .hom-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .hom-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

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
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark, #0A3D62); }

        /* Kartu dan filter mengikuti Master Absensi. */
        .hom-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 16px; }
        .hom-card--table { padding: 0; }
        .hom-search-row { display: flex; flex-direction: column; gap: 10px; padding: 14px 16px; }
        .hom-search-wrap { position: relative; flex: 1; }
        .hom-search-icon { position: absolute; top: 50%; left: 14px; color: #9ca3af; pointer-events: none; transform: translateY(-50%); }
        .hom-search-input, .hom-input { width: 100%; color: #111827; outline: none; font-family: inherit; transition: all 0.2s ease; background: #fff; border: 1.5px solid #d1d5db; }
        .hom-search-input { padding: 10px 14px 10px 42px; border-radius: 12px; font-size: 14px; }
        .hom-input { padding: 10px 14px; border-radius: 10px; font-size: 13px; }
        .hom-search-input:focus, .hom-input:focus { border-color: #145da0; box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }
        .hom-btn-reset, .hom-btn-toggle, .hom-btn-apply { display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-family: inherit; font-size: 13px; font-weight: 500; transition: all 0.2s ease; }
        .hom-btn-reset, .hom-btn-toggle { width: 100%; padding: 10px 14px; color: #6b7280; background: #fff; border: 1.5px solid #d1d5db; border-radius: 12px; }
        .hom-btn-reset { text-decoration: none; }
        .hom-btn-reset:hover { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .hom-btn-toggle { cursor: pointer; }
        .hom-btn-toggle:hover { color: #145da0; border-color: #145da0; }
        .hom-btn-toggle.active, .hom-btn-apply { color: #fff; background: #145da0; border-color: #145da0; }
        .hom-filter-badge { min-width: 18px; padding: 2px 6px; color: #fff; font-size: 10px; font-weight: 700; text-align: center; background: rgba(255,255,255,0.3); border-radius: 10px; }
        .hom-filter-panel { padding: 14px 16px; background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .hom-filter-grid { display: flex; flex-direction: column; gap: 14px; }
        .hom-filter-field { display: flex; flex-direction: column; gap: 6px; }
        .hom-filter-field label { color: #6b7280; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
        .hom-filter-actions { display: flex; justify-content: flex-end; }
        .hom-btn-apply { padding: 10px 20px; cursor: pointer; border: 1px solid #145da0; border-radius: 10px; font-weight: 600; }
        .hom-btn-apply:hover { background: #0d4a83; }
        .hom-btn-reset[hidden], .hom-filter-panel[hidden], .hom-filter-badge[hidden], #hom-search-error[hidden] { display: none; }
        [data-overtime-results].is-loading { opacity: 0.55; pointer-events: none; }
        @media (min-width: 600px) {
            .hom-search-row { flex-direction: row; align-items: center; flex-wrap: wrap; padding: 16px 20px; }
            .hom-search-wrap { min-width: 260px; }
            .hom-btn-reset, .hom-btn-toggle { flex-shrink: 0; width: auto; }
            .hom-filter-panel { padding: 16px 20px; }
            .hom-filter-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 16px; align-items: end; }
            .hom-filter-actions { grid-column: 1 / -1; }
        }
        @media (min-width: 768px) { .hom-filter-grid { grid-template-columns: repeat(3, 1fr); } }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .hom-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .hom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }
        .hom-table thead th {
            background: var(--gray-50, #F5F7FA);
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border, #E5E7EB);
            white-space: nowrap;
        }
        .hom-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            font-size: 13.5px;
            color: var(--text-primary, #1f2937);
            vertical-align: top;
        }
        .hom-table tbody tr:last-child td { border-bottom: none; }
        .hom-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
        .hom-table tbody tr { transition: background 0.15s ease; }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .hom-badge {
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
        }
        .hom-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .hom-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .hom-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .hom-badge--info    { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
        .hom-badge--gray    { background: var(--gray-50, #F8FAFC); color: var(--text-secondary, #374151); }

        /* ========================================== */
        /* EMPLOYEE CELL                              */
        /* ========================================== */
        .hom-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hom-employee-avatar {
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
        .hom-employee-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .hom-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hom-employee-role {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* APPROVER & REJECTION INFO                  */
        /* ========================================== */
        .hom-approver-info {
            font-size: 11px;
            color: var(--text-muted, #6B7280);
            margin-top: 4px;
            line-height: 1.3;
        }
        .hom-rejection-mini {
            font-size: 10px;
            color: var(--error, #EF4444);
            margin-top: 4px;
            max-width: 150px;
            line-height: 1.2;
        }

        /* ========================================== */
        /* ACTION LINK                                */
        /* ========================================== */
        .hom-action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            background: var(--white, #FFFFFF);
            color: var(--primary, #145DA0);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .hom-action-link:hover {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .hom-empty-cell {
            padding: 0 !important;
        }
        .hom-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .hom-empty-icon {
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
        .hom-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .hom-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .hom-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* UTILITY                                    */
        /* ========================================== */
        .hom-text-muted { color: var(--text-muted, #6B7280); font-size: 13px; }
        .hom-text-center { text-align: center; }
        .hom-text-right { text-align: right; }
        .hom-text-date { font-weight: 600; color: var(--text-primary, #1f2937); font-size: 13.5px; }
        .hom-text-xs { font-size: 11px; margin-top: 2px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 768px) {
            .hom-table tbody td {
                padding: 12px 14px;
            }
            .hom-table thead th {
                padding: 12px 14px;
            }
        }

        @media (min-width: 1025px) {
            /* Compact table for desktop */
            .hom-table thead th {
                padding: 8px 10px;
                font-size: 10px;
            }
            .hom-table tbody td {
                padding: 8px 10px;
                font-size: 12px;
            }
            .hom-table {
                min-width: 950px;
            }

            /* Compact employee */
            .hom-employee-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
                border-radius: 6px;
            }
            .hom-employee-name {
                font-size: 13px;
            }
            .hom-employee-role {
                font-size: 11px;
            }

            /* Compact badges */
            .hom-badge {
                padding: 3px 8px;
                font-size: 0.625rem;
            }

            /* Compact text */
            .hom-text-date {
                font-size: 12px;
            }
            .hom-text-muted {
                font-size: 12px;
            }
            .hom-text-xs {
                font-size: 10px;
            }

            /* Compact approver & rejection */
            .hom-approver-info {
                font-size: 10px;
            }
            .hom-rejection-mini {
                font-size: 9px;
                max-width: 120px;
            }

            /* Compact action */
            .hom-action-link {
                padding: 4px 10px;
                font-size: 11px;
                border-radius: 6px;
            }
        }

        @media (max-width: 1024px) {
            .hom-table-wrapper { background: transparent; }

            .hom-table,
            .hom-table tbody,
            .hom-table tr,
            .hom-table td {
                display: block;
                width: 100%;
                min-width: 0;
            }

            .hom-table thead { display: none; }

            .hom-table tbody tr {
                background: var(--white, #FFFFFF);
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                margin-bottom: 12px;
                border: 1px solid var(--border-light, #E5E7EB);
                padding: 16px;
                position: relative;
            }

            .hom-table td {
                padding: 4px 0;
                border: none;
                text-align: left;
            }

            /* Hide Index */
            .hom-table td:nth-child(1) { display: none; }

            /* Employee Name */
            .hom-table td:nth-child(2) {
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border-light, #E5E7EB);
            }
            .hom-table td:nth-child(2) .hom-employee-name { font-size: 15px; }

            /* Status / Supervisor */
            .hom-table td:nth-child(5) {
                display: block;
                margin-bottom: 12px;
            }

            /* Tanggal, Waktu, Clock In, Clock Out */
            .hom-table td:nth-child(3),
            .hom-table td:nth-child(4),
            .hom-table td:nth-child(6),
            .hom-table td:nth-child(7) {
                display: block;
                background: var(--gray-50, #F5F7FA);
                padding: 6px 10px;
                border-radius: 8px;
                color: var(--text-secondary, #4b5563);
                font-size: 13px;
                margin-bottom: 4px;
            }

            .hom-table td:nth-child(3)::before { content: 'Tgl Lembur: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(4)::before { content: 'Waktu: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(6)::before { content: 'Clock In: '; font-weight: 600; color: var(--text-primary, #374151); }
            .hom-table td:nth-child(7)::before { content: 'Clock Out: '; font-weight: 600; color: var(--text-primary, #374151); }
            /* Action */
            .hom-table td:last-child {
                border-top: 1px solid var(--border-light, #E5E7EB);
                margin-top: 12px;
                padding-top: 12px;
                text-align: center;
            }
            .hom-action-link {
                display: block;
                width: 100%;
                background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
                color: #fff;
                border: none;
                padding: 10px;
                border-radius: 10px;
            }
            .hom-action-link:hover {
                background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
                opacity: 0.95;
            }

            .hom-table tr:has(.hom-empty-cell) {
                text-align: center;
                padding: 40px 20px;
            }
        }
    </style>
</x-app>
