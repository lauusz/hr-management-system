<x-atk-mks-app title="Ubah Barang ATK MKS">
    <div class="atk-mks-header"><h1 class="atk-mks-title">Ubah Barang</h1><p class="atk-mks-subtitle">{{ $item->name }}</p></div>
    <form method="POST" action="{{ route('v2.atk-mks.admin.items.update', $item) }}" enctype="multipart/form-data" class="atk-mks-card atk-mks-form-grid">
        @csrf @method('PUT')
        <div><label class="atk-mks-label" for="name">Nama barang</label><input class="atk-mks-input" id="name" name="name" value="{{ old('name', $item->name) }}" required maxlength="150"></div>
        <div><label class="atk-mks-label" for="unit_name">Satuan</label><input class="atk-mks-input" id="unit_name" name="unit_name" value="{{ old('unit_name', $item->unit_name) }}" required maxlength="30"></div>
        <div>
            <label class="atk-mks-label" for="stock_qty">Stok</label>
            <input class="atk-mks-input" id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', $item->stock_qty) }}" min="0" required>
            <p class="atk-mks-muted" style="margin:6px 0 0">Jumlah stok yang tersedia.</p>
        </div>
        <div>
            <label class="atk-mks-label" for="image">Foto barang (opsional)</label>
            @if($item->image_path)<img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" style="display:block;width:120px;aspect-ratio:4/3;object-fit:cover;border-radius:12px;margin-bottom:8px">@endif
            <input class="atk-mks-input" id="image" type="file" name="image" accept="image/*,.heic,.heif">
        </div>
        <div style="grid-column:1/-1"><label class="atk-mks-label" for="description">Keterangan (opsional)</label><textarea class="atk-mks-textarea" id="description" name="description" maxlength="1000">{{ old('description', $item->description) }}</textarea></div>
        <label class="atk-mks-actions"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Aktif</label>
        <div class="atk-mks-actions"><button class="atk-mks-btn atk-mks-btn-primary" type="submit">Simpan</button><a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.items.index') }}">Kembali</a></div>
    </form>
</x-atk-mks-app>


