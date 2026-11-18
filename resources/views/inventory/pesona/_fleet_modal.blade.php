@php
    // Satu modal untuk seluruh posisi (admin Ban Armada dan Ajukan Ban). Isi: partial $bodyView dengan $bodyData.
    // $open: modal dirender terbuka (URL dengan ?pos=, atau setelah validasi gagal). Dengan JavaScript, atribut open diganti
    // menjadi modal sungguhan; tanpa JavaScript, dialog tampil inline sebagai fallback.
@endphp
<dialog id="fleet-modal" class="fleet-dialog" aria-labelledby="fleet-modal-title" @if($open) open data-pos="{{ $pos }}" @endif>
    <div class="fleet-dialog-card">
        <div class="fleet-dialog-head">
            <h2 id="fleet-modal-title" data-modal-title>{{ $open ? $title : '' }}</h2>
            <button type="button" class="tire-btn" data-modal-close aria-label="Tutup">Tutup</button>
        </div>
        <div class="fleet-dialog-body" data-modal-body>
            @if($open)
                @include($bodyView, $bodyData)
            @endif
        </div>
    </div>
</dialog>
