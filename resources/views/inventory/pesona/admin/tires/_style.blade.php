<style>
    .tire-wrap { display: flex; flex-direction: column; gap: 14px; }
    .tire-card { background: #fff; border: 1px solid var(--pesona-border); border-radius: 14px; padding: 14px 16px; }
    .tire-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .tire-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
    @media (min-width: 768px) { .tire-grid { grid-template-columns: 1fr 1fr; } .tire-grid .full { grid-column: 1 / -1; } }
    .tire-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .tire-label { font-size: 12px; font-weight: 700; color: #334155; }
    .tire-hint { font-size: 11.5px; color: var(--pesona-muted); }
    .tire-error { font-size: 12px; color: #B91C1C; font-weight: 600; }
    .tire-input { width: 100%; min-height: 40px; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 10px; background: #F8FAFC; font: inherit; font-size: 13px; color: #0F172A; }
    .tire-input:focus { outline: none; background: #fff; border-color: var(--pesona-primary); box-shadow: 0 0 0 3px rgba(40, 53, 90, .12); }
    textarea.tire-input { min-height: 70px; resize: vertical; }
    .tire-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 6px 14px; border-radius: 10px; border: 1px solid var(--pesona-border); background: #fff; color: #374151; font: inherit; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
    .tire-btn:hover { background: var(--pesona-primary-soft); }
    .tire-btn-primary { background: var(--pesona-primary); border-color: var(--pesona-primary); color: #fff; }
    .tire-btn-primary:hover { background: var(--pesona-primary-dark); }
    .tire-btn-danger { color: #B91C1C; border-color: #FECACA; }
    .tire-badge { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #F1F5F9; color: #475569; }
    .tire-badge-ok { background: rgba(34,197,94,.12); color: #15803D; }
    .tire-badge-warn { background: rgba(245,158,11,.14); color: #B45309; }
    .tire-badge-bad { background: rgba(239,68,68,.12); color: #B91C1C; }
    .tire-serial { font-weight: 800; letter-spacing: .02em; word-break: break-all; }
    .tire-table-wrap { overflow-x: auto; }
    .tire-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .tire-table th { text-align: left; padding: 8px 10px; font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--pesona-muted); border-bottom: 1px solid var(--pesona-border); white-space: nowrap; }
    .tire-table td { padding: 9px 10px; border-bottom: 1px solid var(--pesona-border-soft); vertical-align: top; }
    .tire-dl { display: grid; grid-template-columns: 1fr; gap: 10px 20px; margin: 0; }
    @media (min-width: 640px) { .tire-dl { grid-template-columns: repeat(3, 1fr); } }
    .tire-dl dt { font-size: 11.5px; color: var(--pesona-muted); font-weight: 700; }
    .tire-dl dd { margin: 2px 0 0; font-size: 13.5px; overflow-wrap: anywhere; }
    .tire-actions details { border: 1px solid var(--pesona-border); border-radius: 12px; padding: 10px 14px; background: #fff; }
    .tire-actions summary { cursor: pointer; font-weight: 700; font-size: 13.5px; color: var(--pesona-primary); }
    .tire-actions form { margin-top: 12px; }
</style>
