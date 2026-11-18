{{-- Dialog konfirmasi Nonaktifkan; dipakai daftar kelompok dan detail pola. Pemicu: [data-ws-deactivate]. --}}
<dialog id="ws-deactivate-dialog" class="ws-deact-dialog" aria-labelledby="ws-deact-title">
    <form method="POST" action="{{ route('hr.work-schedules.deactivate') }}" id="ws-deactivate-form">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="return" value="{{ $return }}">
        <input type="hidden" name="group_id" id="ws-deact-group">
        <input type="hidden" name="ids" id="ws-deact-ids">
        <input type="hidden" name="fingerprint" id="ws-deact-fingerprint">
        <h2 id="ws-deact-title">Nonaktifkan Jadwal?</h2>
        <p id="ws-deact-name" class="ws-deact-name"></p>
        <p id="ws-deact-scope" class="ws-deact-scope"></p>
        <p>Absensi baru mengikuti jadwal reguler. Data tetap tersimpan dan absensi lama tidak berubah.</p>
        <div class="ws-deact-actions">
            <button type="button" class="ws-btn ws-btn--outline" id="ws-deact-cancel">Batal</button>
            <button type="submit" class="ws-btn ws-btn--submit" id="ws-deact-submit">Nonaktifkan</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('ws-deactivate-dialog');
        const form = document.getElementById('ws-deactivate-form');
        if (!dialog || !form) return;

        // Delegasi agar tombol tetap bekerja setelah hasil daftar diganti lewat AJAX.
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-ws-deactivate]');
            if (!trigger) return;
            document.getElementById('ws-deact-group').value = trigger.dataset.groupId;
            document.getElementById('ws-deact-ids').value = trigger.dataset.ids || '';
            document.getElementById('ws-deact-fingerprint').value = trigger.dataset.fingerprint;
            document.getElementById('ws-deact-name').textContent = trigger.dataset.title;
            document.getElementById('ws-deact-scope').textContent = `${trigger.dataset.employees} karyawan · ${trigger.dataset.dates} tanggal`;
            document.getElementById('ws-deact-submit').disabled = false;
            dialog.showModal();
        });
        document.getElementById('ws-deact-cancel').addEventListener('click', () => dialog.close());
        form.addEventListener('submit', () => { document.getElementById('ws-deact-submit').disabled = true; });
    })();
</script>

<style>
    .ws-deact-dialog { width: min(520px, calc(100% - 32px)); border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; }
    .ws-deact-dialog::backdrop { background: rgba(15, 23, 42, .45); }
    .ws-deact-dialog h2 { margin: 0 0 8px; font-size: 1.05rem; }
    .ws-deact-name { margin: 0; font-weight: 700; overflow-wrap: anywhere; }
    .ws-deact-scope { margin: 2px 0 10px; color: #64748b; }
    .ws-deact-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
</style>
