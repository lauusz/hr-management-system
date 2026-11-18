@php
    // Combobox ban siap pakai (pencarian AJAX ke server). Yang dikirim hanya ID pada input hidden; teks label bukan nilai transaksi.
    // Param: $name (tire_id|new_tire_id), $caption, $pick (ban pilihan lama setelah validasi gagal, atau null), $replace (ringkasan Ganti).
    $uid = 'rc-'.$name;
    $replace = $replace ?? false;
@endphp
<div class="tire-field rc" data-ready-combo data-url="{{ route('inventory.pesona.admin.tires.ready-search') }}">
    <label class="tire-label" for="{{ $uid }}-input">{{ $caption }}</label>
    <div class="rc-box">
        <input class="tire-input" id="{{ $uid }}-input" name="{{ $name }}_label" data-rc-input data-draft-keep type="text" required maxlength="150" autocomplete="off"
               role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="false" aria-controls="{{ $uid }}-list"
               placeholder="Cari nomor seri, merek, ukuran, atau tipe…" value="{{ $pick?->pickerLabel() }}">
        <button class="rc-clear" type="button" data-rc-clear aria-label="Hapus pilihan ban">×</button>
        <input type="hidden" name="{{ $name }}" data-rc-id data-draft-keep @if($replace) data-replace-select @endif value="{{ $pick?->id }}">
    </div>
    <ul class="rc-list" id="{{ $uid }}-list" role="listbox" data-rc-list aria-label="{{ $caption }}" hidden></ul>
    <div class="tire-hint rc-status" role="status" aria-live="polite" data-rc-status></div>
</div>
