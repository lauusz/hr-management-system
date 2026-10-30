@if($overtime->system_note)
    <section class="overtime-system-note">
        <strong>Catatan Sistem</strong>
        <div>{{ $overtime->system_note }}</div>
    </section>
    <style>
        .overtime-system-note { padding: 16px; border: 1px solid #bfdbfe; border-radius: 12px; background: #eff6ff; color: #1e40af; font-size: 13px; line-height: 1.6; }
        .overtime-system-note strong { display: block; margin-bottom: 6px; font-size: 12px; }
        .overtime-system-note div { white-space: pre-line; overflow-wrap: anywhere; }
    </style>
@endif
