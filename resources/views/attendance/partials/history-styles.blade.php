<style>
    /* ========================================== */
    /* SECTION HEADER (x-slot) — pola /leave-requests */
    /* ========================================== */
    .section-header-inline { display: flex; align-items: center; gap: 10px; }
    .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .section-icon svg { width: 16px; height: 16px; }
    .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1.25; }
    .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted); font-weight: 500; line-height: 1.35; }
    .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

    /* ========================================== */
    /* CTA BAR                                    */
    /* ========================================== */
    .lr-cta-bar { display: flex; justify-content: flex-start; margin-bottom: 16px; }
    .ah-btn-back { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 10px 18px; box-sizing: border-box; background: var(--white); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s ease; }
    .ah-btn-back:hover { background: var(--primary); color: #fff; }
    .ah-btn-back svg { flex-shrink: 0; }

    /* ========================================== */
    /* FILTER CARD                                */
    /* ========================================== */
    .lr-filter { background: var(--white); border-radius: 16px; margin-bottom: 16px; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .lr-filter-header { display: flex; gap: 8px; padding: 12px 16px; }
    .ah-filter-header { flex-direction: column; align-items: stretch; gap: 10px; }
    .ah-period { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .ah-period-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
    .ah-period-value { font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); }
    .ah-filter-buttons { display: flex; gap: 8px; }
    .lr-btn-toggle-filter { display: inline-flex; flex: 1; align-items: center; justify-content: center; gap: 6px; min-height: 44px; padding: 8px 14px; box-sizing: border-box; background: var(--white); color: var(--primary); border: 1.5px solid var(--primary); border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
    .lr-btn-toggle-filter:hover, .lr-btn-toggle-filter.active { background: var(--primary); color: #fff; }
    .lr-filter-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9999px; background: var(--primary); color: var(--white); font-size: 0.625rem; }
    .lr-btn-toggle-filter.active .lr-filter-badge { background: var(--white); color: var(--primary); }
    .lr-filter-panel { padding: 16px; border-top: 1px solid var(--border-light); }
    .lr-filter-body { display: flex; flex-direction: column; gap: 14px; }
    .lr-filter-group { display: flex; flex-direction: column; gap: 6px; }
    .lr-filter-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
    .lr-filter-input { width: 100%; min-height: 44px; box-sizing: border-box; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; color: var(--text-primary); background: var(--white); transition: all 0.2s ease; outline: none; font-family: inherit; cursor: pointer; }
    .lr-filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }
    .lr-filter-actions { display: flex; gap: 8px; align-items: center; }
    .lr-btn-filter { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; box-sizing: border-box; padding: 10px 16px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: inherit; flex: 1; }
    .lr-btn-filter:hover { background: var(--primary-dark); }
    .lr-btn-reset { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; box-sizing: border-box; padding: 10px 14px; background: var(--white); color: var(--text-muted); border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s ease; flex-shrink: 0; }
    .lr-btn-reset:hover { background: var(--danger-light); border-color: #fecaca; color: var(--danger); }

    .ah-quick { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; }
    .ah-quick-label { width: 100%; font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }
    .ah-quick-chip { display: inline-flex; align-items: center; min-height: 36px; box-sizing: border-box; padding: 6px 14px; border-radius: 9999px; border: 1.5px solid var(--border); background: var(--white); color: var(--text-secondary); font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; }
    .ah-quick-chip:hover { border-color: var(--primary); color: var(--primary); }
    .ah-quick-chip.is-active { background: rgba(20, 93, 160, 0.08); border-color: var(--primary); color: var(--primary); }

    /* ========================================== */
    /* STATS ROW                                  */
    /* ========================================== */
    .lr-stats { display: flex; align-items: center; gap: 1px; background: var(--border-light); border-radius: 16px; overflow: hidden; margin-bottom: 16px; border: 1px solid var(--border-light); }
    .lr-stat { flex: 1; background: var(--white); padding: 10px 4px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 3px; min-width: 0; }
    .lr-stat-value { font-size: 1.0625rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
    .lr-stat-value--success { color: var(--success); }
    .lr-stat-value--warning { color: var(--warning); }
    .lr-stat-value--muted { color: var(--text-muted); }
    .lr-stat-label { font-size: 0.5625rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.02em; line-height: 1.2; }
    .lr-stat-divider { display: none; }

    /* ========================================== */
    /* CARD LIST                                  */
    /* ========================================== */
    .ah-list { display: flex; flex-direction: column; gap: 12px; padding-bottom: 80px; }
    .ah-card { background: var(--white); border-radius: 16px; padding: 16px; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.04); box-sizing: border-box; min-width: 0; transition: border-color 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; gap: 12px; }
    .ah-card:hover { border-color: rgba(20, 93, 160, 0.35); box-shadow: 0 4px 14px rgba(20, 93, 160, 0.08); }

    .ah-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .ah-type { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
    .ah-type--office   { background: rgba(20, 93, 160, 0.08); color: var(--primary); }
    .ah-type--remote   { background: rgba(147, 51, 234, 0.08); color: #7e22ce; }
    .ah-type--flexible { background: rgba(245, 158, 11, 0.1); color: #b45309; }

    .lr-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; flex-shrink: 0; }
    .lr-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
    .lr-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
    .lr-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
    .lr-badge--gray    { background: #F1F5F9; color: var(--text-secondary); }

    /* Date row (Full date + short date 30/10/26 badge) */
    .ah-card-date-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ah-card-date { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--text-primary); }
    .ah-card-date svg { color: var(--text-muted); flex-shrink: 0; }
    .ah-date-badge { padding: 3px 8px; border-radius: 6px; background: #e2e8f0; color: #334155; font-size: 0.6875rem; font-weight: 700; font-family: monospace; }

    /* ========================================== */
    /* ENDPOINT BUTTONS (MASUK / PULANG)          */
    /* ========================================== */
    .ah-points { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }

    .ah-point {
        appearance: none;
        -webkit-appearance: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        width: 100%;
        padding: 12px;
        box-sizing: border-box;
        border-radius: 12px;
        background: #f8fafc;
        border: 1.5px solid var(--border-light);
        text-align: left;
        font-family: inherit;
        color: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .ah-point--in { border-left: 3.5px solid #16a34a; }
    .ah-point--out { border-left: 3.5px solid #dc2626; }

    .ah-point:hover,
    .ah-point:focus-visible {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(20, 93, 160, 0.08);
        transform: translateY(-1px);
        outline: none;
    }
    .ah-point:active {
        transform: scale(0.99);
    }

    .ah-point-head { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
    .ah-point-label { display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
    .ah-point--in .ah-point-label { color: #15803d; }
    .ah-point--out .ah-point-label { color: #b91c1c; }
    .ah-count-badge { padding: 2px 7px; border-radius: 9999px; background: #e2e8f0; color: #334155; font-size: 0.625rem; font-weight: 700; }

    /* JAM ABSENSI DITUMPUK VERTIKAL (IMAGE 1) */
    .ah-time-stack { display: flex; flex-direction: column; gap: 4px; padding: 2px 0; min-height: 28px; }
    .ah-time-row { display: flex; align-items: baseline; gap: 5px; flex-wrap: wrap; }
    .ah-time-val {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.15;
        letter-spacing: -0.02em;
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }
    .ah-time-val--empty { color: #94a3b8; font-size: 1.125rem; font-weight: 600; }
    .ah-tz { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); }
    .ah-point-empty { display: flex; flex-direction: column; gap: 4px; }
    .ah-point-empty-label { font-size: 0.75rem; color: #94a3b8; }

    .ah-mini-badge { padding: 2px 7px; border-radius: 9999px; font-size: 0.5625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; }
    .ah-mini-badge--success { background: rgba(34, 197, 94, 0.14); color: #15803d; }
    .ah-mini-badge--warning { background: rgba(245, 158, 11, 0.16); color: #a16207; }
    .ah-mini-badge--error   { background: rgba(239, 68, 68, 0.14); color: #b91c1c; }

    .ah-point-loc { display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: var(--text-muted); min-width: 0; }
    .ah-point-loc svg { flex-shrink: 0; }
    .ah-point-loc-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .ah-point-footer { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-top: auto; padding-top: 6px; border-top: 1px dashed #e2e8f0; }
    .ah-point-cue { display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem; font-weight: 600; color: var(--primary); }
    .ah-point-indicators { display: inline-flex; align-items: center; gap: 4px; }
    .ah-indicator-icon { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 4px; background: #e2e8f0; color: #475569; }

    /* ========================================== */
    /* NOTES                                      */
    /* ========================================== */
    .ah-notes { display: flex; flex-direction: column; gap: 8px; }
    .ah-note { padding: 10px 12px; border-radius: 10px; background: var(--gray-100); border: 1px solid var(--border-light); color: var(--text-secondary); }
    .ah-note--hrd { background: #fffbeb; border-color: #fde68a; border-left: 3px solid #d97706; color: #78350f; }
    .ah-note strong { display: block; margin-bottom: 2px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
    .ah-note p { margin: 0; font-size: 0.8125rem; line-height: 1.5; overflow-wrap: anywhere; }

    /* ========================================== */
    /* CARD FOOTER                                */
    /* ========================================== */
    .ah-card-footer { display: flex; flex-direction: column; gap: 10px; padding-top: 12px; border-top: 1px solid var(--border-light); }
    .ah-card-footer-info { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px 12px; }
    .ah-card-meta { display: flex; align-items: center; gap: 5px; min-width: 0; font-size: 0.75rem; color: var(--text-light); }
    .ah-card-meta svg { flex-shrink: 0; }
    .ah-card-meta-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ah-card-duration { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); }

    .ah-card-btn-evidence {
        appearance: none;
        -webkit-appearance: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 42px;
        padding: 8px 14px;
        background: #f0f7ff;
        color: var(--primary);
        border: 1.5px solid #bfdbfe;
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    .ah-card-btn-evidence:hover,
    .ah-card-btn-evidence:focus-visible {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        outline: none;
    }

    /* ========================================== */
    /* MODAL BUKTI KEHADIRAN (IMAGE 2)            */
    /* ========================================== */
    #attendance-evidence-modal .modal-card { max-width: 1040px; width: 100%; }
    .attendance-evidence-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #111827; }
    .attendance-evidence-meta strong { font-size: 1rem; font-weight: 800; color: #111827; }
    .attendance-evidence-meta span { padding: 3px 10px; color: #176b42; font-size: 11px; font-weight: 700; background: #e9f8ef; border-radius: 999px; }

    .attendance-evidence-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .attendance-evidence-panel { min-width: 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
    .attendance-evidence-panel h4 { margin: 0 0 10px; color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .attendance-evidence-list { display: grid; gap: 14px; }
    .attendance-evidence-entry { padding-top: 14px; border-top: 1px solid #e2e8f0; }
    .attendance-evidence-entry:first-child { padding-top: 0; border-top: 0; }
    .attendance-evidence-entry-time { margin: 0 0 8px; color: #176b42; font-size: 12px; font-weight: 700; }
    .attendance-evidence-notes { margin: 0 0 10px; color: #334155; font-size: 12px; overflow-wrap: anywhere; white-space: pre-wrap; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .attendance-evidence-notes--hrd { background: #fffbeb; border-color: #fde68a; color: #78350f; border-left: 3px solid #d97706; }

    .attendance-evidence-subgrid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .attendance-evidence-block h5 { margin: 0 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; }

    .attendance-evidence-photo-btn { display: block; position: relative; width: 100%; padding: 0; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #e2e8f0; cursor: pointer; text-align: left; }
    .attendance-evidence-photo { display: block; width: 100%; height: min(30vh, 220px); object-fit: cover; }
    .attendance-evidence-photo-hint { position: absolute; bottom: 6px; right: 6px; background: rgba(15, 23, 42, 0.78); color: #fff; font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 4px; pointer-events: none; }

    .attendance-evidence-coordinates { margin: -2px 0 6px; color: #64748b; font-family: monospace; font-size: 11px; word-break: break-all; }
    .attendance-evidence-photo-spacer { visibility: hidden; }
    .attendance-evidence-map { display: block; width: 100%; height: min(30vh, 220px); border: 0; border-radius: 8px; background: #e2e8f0; }
    .attendance-evidence-empty { display: flex; align-items: center; justify-content: center; min-height: 120px; margin: 0; color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; }

    /* ========================================== */
    /* EMPTY STATE                                */
    /* ========================================== */
    .lr-empty { text-align: center; padding: 48px 24px; background: var(--white); border-radius: 16px; border: 1px solid var(--border-light); }
    .lr-empty-icon { width: 72px; height: 72px; margin: 0 auto 16px; background: var(--gray-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-light); }
    .lr-empty-title { font-size: 0.9375rem; font-weight: 600; color: var(--text-secondary); margin: 0 0 6px; }
    .lr-empty-desc { font-size: 0.8125rem; color: var(--text-muted); margin: 0 auto; max-width: 280px; line-height: 1.5; }
    .ah-empty-btn { display: inline-flex; align-items: center; min-height: 44px; box-sizing: border-box; margin-top: 16px; padding: 10px 18px; border-radius: 10px; background: var(--primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; }
    .ah-empty-btn:hover { background: var(--primary-dark); }

    /* ========================================== */
    /* RESPONSIVE OPTIMIZATIONS                   */
    /* ========================================== */
    @media (max-width: 359px) {
        .ah-points { grid-template-columns: 1fr; }
        .ah-time-val { font-size: 1.125rem; }
    }

    @media (max-width: 767px) {
        .attendance-evidence-grid { grid-template-columns: 1fr; gap: 14px; }
        .attendance-evidence-subgrid { grid-template-columns: 1fr; gap: 10px; }
        .attendance-evidence-photo-spacer { display: none; }
        .attendance-evidence-photo,
        .attendance-evidence-map { height: 180px; }
    }

    @media (min-width: 480px) {
        .lr-cta-bar { justify-content: flex-end; }
        .lr-stats { background: var(--white); padding: 14px 20px; }
        .lr-stat { padding: 0; gap: 4px; }
        .lr-stat-value { font-size: 1.375rem; }
        .lr-stat-label { font-size: 0.6875rem; letter-spacing: 0.04em; }
        .lr-stat-divider { display: block; width: 1px; height: 36px; background: var(--border); flex-shrink: 0; }
        .ah-filter-header { flex-direction: row; align-items: center; justify-content: space-between; padding: 14px 20px; }
        .lr-btn-toggle-filter { flex: none; }
        .lr-filter-panel { padding: 18px 20px; }
        .lr-filter-body { flex-direction: row; align-items: flex-end; flex-wrap: wrap; }
        .lr-filter-group { flex: 1; min-width: 200px; }
        .lr-filter-actions { flex-shrink: 0; }
        .lr-btn-filter, .lr-btn-reset { flex: none; }
        .ah-quick-label { width: auto; }
        .ah-card-footer { flex-direction: row; align-items: center; justify-content: space-between; }
        .ah-card-btn-evidence { width: auto; }
    }

    @media (min-width: 768px) {
        .ah-card { padding: 18px 20px; }
        .ah-filter-header { padding: 16px 20px; }
        .lr-filter-panel { padding: 20px; }
    }

    @media (min-width: 1024px) {
        .ah-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; align-items: start; }
        .lr-empty { grid-column: 1 / -1; }
    }

    @media (min-width: 1025px) {
        .lr-cta-bar { display: none; }
    }
</style>
