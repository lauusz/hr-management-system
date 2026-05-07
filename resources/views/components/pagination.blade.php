@props(['items', 'preserveQuery' => false])

@if ($items->hasPages())
    @php
        $paginator = $preserveQuery ? $items->appends(request()->query()) : $items;
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();

        $links = $paginator->linkCollection();
        $prev = $links->first();
        $next = $links->last();
        $pages = $links->slice(1, $links->count() - 2)->values();
    @endphp

    <nav class="hrd-pagination" aria-label="Navigasi Halaman">
        <div class="hrd-pagination-summary">
            Menampilkan <strong>{{ $from }}&ndash;{{ $to }}</strong> dari <strong>{{ $total }}</strong> data
        </div>

        {{-- Desktop numbered pages --}}
        <div class="hrd-pagination-links hrd-pagination-links--desktop">
            @if ($prev['url'])
                <a href="{{ $prev['url'] }}" class="hrd-page-btn" aria-label="Sebelumnya">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @else
                <span class="hrd-page-btn hrd-page-btn--disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @endif

            @foreach ($pages as $link)
                @if ($link['label'] === '...')
                    <span class="hrd-page-ellipsis">{{ $link['label'] }}</span>
                @elseif ($link['active'])
                    <span class="hrd-page-btn hrd-page-btn--active" aria-current="page">{{ $link['label'] }}</span>
                @else
                    <a href="{{ $link['url'] }}" class="hrd-page-btn" aria-label="Halaman {{ $link['label'] }}">{{ $link['label'] }}</a>
                @endif
            @endforeach

            @if ($next['url'])
                <a href="{{ $next['url'] }}" class="hrd-page-btn" aria-label="Berikutnya">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="hrd-page-btn hrd-page-btn--disabled" aria-disabled="true" aria-label="Berikutnya">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Mobile simplified controls --}}
        <div class="hrd-pagination-links hrd-pagination-links--mobile">
            @if ($paginator->onFirstPage())
                <span class="hrd-page-btn hrd-page-btn--disabled hrd-page-btn--wide" aria-disabled="true">
                    &lsaquo; Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="hrd-page-btn hrd-page-btn--wide" aria-label="Halaman sebelumnya">
                    &lsaquo; Sebelumnya
                </a>
            @endif

            <span class="hrd-page-current">Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="hrd-page-btn hrd-page-btn--wide" aria-label="Halaman berikutnya">
                    Berikutnya &rsaquo;
                </a>
            @else
                <span class="hrd-page-btn hrd-page-btn--disabled hrd-page-btn--wide" aria-disabled="true">
                    Berikutnya &rsaquo;
                </span>
            @endif
        </div>
    </nav>

    <style>
        .hrd-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #E5E7EB;
        }
        .hrd-pagination-summary {
            font-size: 0.78rem;
            color: #6B7280;
            font-weight: 500;
            line-height: 1.4;
        }
        .hrd-pagination-summary strong {
            color: #111827;
            font-weight: 600;
        }
        .hrd-pagination-links {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hrd-pagination-links--mobile {
            display: none;
        }
        .hrd-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
            background: #FFFFFF;
            color: #374151;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            line-height: 1;
            user-select: none;
        }
        .hrd-page-btn:hover:not(.hrd-page-btn--active):not(.hrd-page-btn--disabled) {
            background: rgba(20, 93, 160, 0.06);
            border-color: #145DA0;
            color: #145DA0;
        }
        .hrd-page-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.15);
        }
        .hrd-page-btn--active {
            background: #145DA0;
            border-color: #145DA0;
            color: #FFFFFF;
            font-weight: 600;
            cursor: default;
        }
        .hrd-page-btn--disabled {
            background: #F5F7FA;
            border-color: #E5E7EB;
            color: #9CA3AF;
            cursor: not-allowed;
        }
        .hrd-page-btn svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }
        .hrd-page-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 36px;
            color: #9CA3AF;
            font-size: 0.82rem;
            font-weight: 500;
            user-select: none;
        }
        .hrd-page-current {
            font-size: 0.82rem;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
            padding: 0 6px;
        }

        @media (max-width: 639px) {
            .hrd-pagination {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .hrd-pagination-summary {
                text-align: center;
                font-size: 0.8rem;
            }
            .hrd-pagination-links--desktop {
                display: none;
            }
            .hrd-pagination-links--mobile {
                display: flex;
                justify-content: center;
                gap: 8px;
            }
            .hrd-page-btn--wide {
                min-width: auto;
                padding: 0 14px;
                font-size: 0.8rem;
                height: 40px;
                flex: 1;
                max-width: 140px;
            }
            .hrd-page-current {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 40px;
            }
        }
    </style>
@endif
