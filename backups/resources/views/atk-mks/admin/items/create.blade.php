<x-atk-mks-app title="Tambah Barang ATK MKS">
    <div class="atk-mks-header"><h1 class="atk-mks-title">Tambah Barang</h1><p class="atk-mks-subtitle">Isi data utama barang.</p></div>
    <form method="POST" action="{{ route('v2.atk-mks.admin.items.store') }}" enctype="multipart/form-data" class="atk-mks-card atk-mks-form-grid">
        @csrf
        <div><label class="atk-mks-label" for="name">Nama barang</label><input class="atk-mks-input" id="name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
        <div><label class="atk-mks-label" for="unit_name">Satuan</label><input class="atk-mks-input" id="unit_name" name="unit_name" value="{{ old('unit_name', 'pcs') }}" required maxlength="30"></div>
        <div><label class="atk-mks-label" for="stock_qty">Stok awal</label><input class="atk-mks-input" id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" required></div>
        <div><label class="atk-mks-label" for="image">Foto barang (opsional)</label><input class="atk-mks-input" id="image" type="file" name="image" accept="image/*,.heic,.heif"></div>
        <div style="grid-column:1/-1"><label class="atk-mks-label" for="description">Keterangan (opsional)</label><textarea class="atk-mks-textarea" id="description" name="description" maxlength="1000">{{ old('description') }}</textarea></div>
        <div class="atk-mks-actions"><button class="atk-mks-btn atk-mks-btn-primary" type="submit">Simpan</button><a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.items.index') }}">Kembali</a></div>
    </form>
</x-atk-mks-app>


