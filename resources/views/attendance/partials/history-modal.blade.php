{{--
    Modal Bukti Kehadiran (mengikuti modal yang ada di /hr/attendances)
    Menampilkan detail foto, Google Maps, koordinat, alasan, dan catatan HRD.
--}}
<x-modal id="attendance-evidence-modal" title="Bukti Kehadiran" type="info" variant="info" cancelLabel="Tutup">
    <div class="attendance-evidence-meta">
        <strong id="attendance-evidence-name">{{ auth()->user()->name }}</strong>
        <span id="attendance-evidence-date"></span>
    </div>
    <div id="attendance-evidence-modal-body"></div>
</x-modal>
