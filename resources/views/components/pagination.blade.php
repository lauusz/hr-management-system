@props(['items'])

<div class="pagination-container" style="margin-top:12px;">
    <style>
        .pagination-container nav[role="navigation"] {
            margin-top: 8px;
            font-size: 0.85rem;
        }
        .pagination-container nav[role="navigation"] > div:first-child {
            display: none;
        }
        .pagination-container nav[role="navigation"] > div:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .pagination-container nav[role="navigation"] > div:last-child > div:first-child p {
            margin: 0;
            opacity: .7;
        }
        .pagination-container nav[role="navigation"] ul {
            list-style: none;
            display: flex;
            padding: 0;
            margin: 0;
            gap: 4px;
        }
        .pagination-container nav[role="navigation"] li {
            display: inline-flex;
        }
        .pagination-container nav[role="navigation"] a,
        .pagination-container nav[role="navigation"] span[aria-current="page"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            text-decoration: none;
            color: #374151;
        }
        .pagination-container nav[role="navigation"] span[aria-current="page"] {
            background: #1e4a8d;
            border-color: #1e4a8d;
            color: #fff;
            font-weight: 600;
        }
        .pagination-container nav[role="navigation"] a:hover {
            background: #eef3ff;
            border-color: #1e4a8d;
            color: #1e4a8d;
        }
        .pagination-container nav[role="navigation"] svg {
            width: 16px;
            height: 16px;
        }
    </style>

    {{ $items->links() }}
</div>
