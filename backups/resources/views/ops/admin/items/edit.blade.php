<x-ops-app title="Ubah Barang OPS">
    <div class="ops-header"><h1 class="ops-title">Ubah Barang</h1><p class="ops-subtitle">{{ $item->name }}</p></div>
    <form method="POST" action="{{ route('v2.ops.admin.items.update', $item) }}" enctype="multipart/form-data" class="ops-card ops-form-grid">
        @csrf @method('PUT')
        <div><label class="ops-label" for="name">Nama barang</label><input class="ops-input" id="name" name="name" value="{{ old('name', $item->name) }}" required maxlength="150"></div>
        <div><label class="ops-label" for="unit_name">Satuan</label><input class="ops-input" id="unit_name" name="unit_name" value="{{ old('unit_name', $item->unit_name) }}" required maxlength="30"></div>
        <div>
            <label class="ops-label" for="stock_qty">Stok</label>
            <input class="ops-input" id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', $item->stock_qty) }}" min="0" required>
            <p class="ops-muted" style="margin:6px 0 0">Jumlah stok yang tersedia.</p>
        </div>
        <div>
            <label class="ops-label" for="image">Foto barang (opsional)</label>
            @if($item->image_path)<img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" style="display:block;width:120px;aspect-ratio:4/3;object-fit:cover;border-radius:12px;margin-bottom:8px">@endif
            <input class="ops-input" id="image" type="file" name="image" accept="image/*,.heic,.heif">
        </div>
        <div style="grid-column:1/-1"><label class="ops-label" for="description">Keterangan (opsional)</label><textarea class="ops-textarea" id="description" name="description" maxlength="1000">{{ old('description', $item->description) }}</textarea></div>
        <label class="ops-actions"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Aktif</label>
        <div class="ops-actions"><button class="ops-btn ops-btn-primary" type="submit">Simpan</button><a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.items.index') }}">Kembali</a></div>
    </form>
</x-ops-app>
