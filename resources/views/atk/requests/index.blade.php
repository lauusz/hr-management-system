@php
    $currentStatus = request('status', '');
    $statusOptions = [
        '' => 'Semua Status',
        'PENDING' => 'Pending',
        'APPROVED' => 'Approved',
        'PARTIAL' => 'Partial',
        'REJECTED' => 'Rejected',
    ];
    $badgeType = fn (string $status) => match ($status) {
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        default => 'warning',
    };
@endphp
<x-atk-app title="Pengajuan Saya">
    <div class="atk-req-toolbar">
        <div class="atk-req-heading">
            <h1 class="atk-title">Pengajuan Saya</h1>
            <span class="atk-req-count">{{ $requests->total() }} pengajuan</span>
        </div>

        <form method="GET" action="{{ route('v2.atk.requests.index') }}" class="atk-req-filter-form">
            <div class="atk-status-pills" role="group" aria-label="Filter status">
                @foreach($statusOptions as $value => $label)
                    <a href="{{ $value === '' ? route('v2.atk.requests.index') : route('v2.atk.requests.index', ['status' => $value]) }}"
                       class="atk-status-pill {{ $currentStatus === $value ? 'active' : '' }}">
                        {{ $value === '' ? 'Semua' : $label }}
                    </a>
                @endforeach
            </div>

            <div class="atk-req-filter-select-wrap">
                <label class="sr-only" for="status">Status</label>
                <select class="atk-select atk-req-select" id="status" name="status" onchange="this.form.submit()">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if($currentStatus !== '')
                    <a class="atk-btn atk-btn-muted atk-req-reset" href="{{ route('v2.atk.requests.index') }}">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Mobile Card List --}}
    <div class="atk-request-mobile-list">
        @forelse($requests as $request)
            @php
                $itemCount = $request->items->count();
                $itemPreview = $request->items->take(2)->map(fn ($i) => $i->item_name_snapshot . ' (' . $i->qty . ' ' . $i->unit_name_snapshot . ')')->implode(', ');
                if ($itemCount > 2) {
                    $itemPreview .= ' +' . ($itemCount - 2) . ' lainnya';
                }
            @endphp
            <article class="atk-request-card">
                <div class="atk-request-card-top">
                    <div>
                        <a href="{{ route('v2.atk.requests.show', $request) }}" class="atk-request-number">{{ $request->request_number }}</a>
                        <div class="atk-request-sub">{{ $request->pt_name_snapshot ?? '-' }} · {{ $request->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <span class="atk-badge atk-badge-{{ $badgeType($request->status) }}">
                        {{ $request->status }}
                    </span>
                </div>

                @if($itemCount > 0)
                    <div class="atk-request-items-preview">
                        <span class="atk-request-items-count">{{ $itemCount }} barang:</span>
                        {{ $itemPreview }}
                    </div>
                @endif

                <div class="atk-request-card-foot">
                    <a class="atk-btn atk-btn-secondary atk-request-detail" href="{{ route('v2.atk.requests.show', $request) }}">Detail</a>
                </div>
            </article>
        @empty
            <div class="atk-card atk-empty">Belum ada pengajuan ATK.</div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="atk-table-wrap atk-request-desktop-table">
        <table class="atk-table atk-req-table">
            <thead>
                <tr>
                    <th>No. Request</th>
                    <th>Barang</th>
                    <th>PT</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    @php
                        $itemCount = $request->items->count();
                        $itemPreview = $request->items->take(2)->map(fn ($i) => $i->item_name_snapshot . ' (' . $i->qty . ' ' . $i->unit_name_snapshot . ')')->implode(', ');
                        if ($itemCount > 2) {
                            $itemPreview .= ' +' . ($itemCount - 2) . ' lainnya';
                        }
                    @endphp
                    <tr>
                        <td class="atk-req-num-cell">
                            <a href="{{ route('v2.atk.requests.show', $request) }}" class="atk-req-link">{{ $request->request_number }}</a>
                        </td>
                        <td class="atk-req-items-cell">
                            @if($itemCount > 0)
                                <span class="atk-req-item-summary">{{ $itemPreview }}</span>
                                <span class="atk-req-item-count">{{ $itemCount }} jenis barang</span>
                            @else
                                <span class="atk-req-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td class="atk-req-date-cell">{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $badgeType($request->status) }}">{{ $request->status }}</span>
                        </td>
                        <td style="text-align:right">
                            <a class="atk-btn atk-btn-secondary atk-btn-sm" href="{{ route('v2.atk.requests.show', $request) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="atk-empty">Belum ada pengajuan ATK.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :items="$requests" preserve-query />

    <style>
        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }

        /* ========== TOOLBAR & FILTERS ========== */
        .atk-req-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .atk-req-heading {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-req-count {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-primary-dark);
            background: var(--atk-primary-soft);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .atk-req-filter-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-status-pills {
            display: none;
            align-items: center;
            gap: 4px;
            padding: 3px;
            background: var(--atk-surface);
            border: 1px solid var(--atk-border);
            border-radius: 10px;
        }
        .atk-status-pill {
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-muted);
            text-decoration: none;
            transition: background-color .12s ease, color .12s ease;
        }
        .atk-status-pill:hover {
            color: var(--atk-text);
            background: var(--atk-bg);
        }
        .atk-status-pill.active {
            background: var(--atk-primary);
            color: #fff;
        }
        .atk-req-filter-select-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        .atk-req-select {
            height: 36px;
            min-height: 36px;
        }
        .atk-req-reset {
            height: 36px;
            min-height: 36px;
            padding: 0 12px;
            font-size: 12px;
        }

        /* ========== MOBILE CARDS ========== */
        .atk-request-mobile-list {
            display: grid;
            gap: 10px;
        }
        .atk-request-card {
            padding: 12px 14px;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            background: var(--atk-surface);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .atk-request-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .atk-request-number {
            display: inline-block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--atk-text);
            text-decoration: none;
            line-height: 1.3;
        }
        .atk-request-number:hover {
            color: var(--atk-primary);
        }
        .atk-request-sub {
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--atk-muted);
        }
        .atk-request-items-preview {
            font-size: 12px;
            color: var(--atk-text-secondary);
            background: var(--atk-bg);
            padding: 7px 10px;
            border-radius: 8px;
            line-height: 1.4;
        }
        .atk-request-items-count {
            font-weight: 700;
            color: var(--atk-text);
        }
        .atk-request-card-foot {
            display: flex;
            justify-content: flex-end;
        }
        .atk-request-detail {
            width: 100%;
            height: 34px;
            min-height: 34px;
            font-size: 12px;
            border-radius: 8px;
        }

        /* ========== DESKTOP TABLE ========== */
        .atk-request-desktop-table {
            display: none;
        }
        .atk-req-table {
            min-width: 680px;
        }
        .atk-req-table td {
            vertical-align: middle;
        }
        .atk-req-table tbody tr:hover {
            background: var(--atk-primary-softer);
        }
        .atk-req-link {
            font-weight: 700;
            color: var(--atk-text);
            text-decoration: none;
        }
        .atk-req-link:hover {
            color: var(--atk-primary);
            text-decoration: underline;
        }
        .atk-req-items-cell {
            max-width: 340px;
        }
        .atk-req-item-summary {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--atk-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .atk-req-item-count {
            display: block;
            font-size: 11px;
            color: var(--atk-muted);
        }
        .atk-req-date-cell {
            white-space: nowrap;
            color: var(--atk-muted);
            font-size: 12.5px;
        }
        .atk-req-muted {
            color: var(--atk-muted);
        }
        .atk-btn-sm {
            height: 30px;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 12px;
        }

        @media (min-width: 640px) {
            .atk-req-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .atk-status-pills {
                display: inline-flex;
            }
            .atk-req-filter-select-wrap {
                display: none;
            }
            .atk-request-mobile-list {
                display: none;
            }
            .atk-request-desktop-table {
                display: block;
            }
        }
    </style>
</x-atk-app>
