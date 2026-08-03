<x-ops-app title="Tambah Barang OPS">
    <div class="ops-header"><h1 class="ops-title">Tambah Barang</h1><p class="ops-subtitle">Isi data utama barang.</p></div>
    <form method="POST" action="{{ route('v2.ops.admin.items.store') }}" class="ops-card ops-form-grid">
        @csrf
        <div><label class="ops-label" for="name">Nama barang</label><input class="ops-input" id="name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
        <div><label class="ops-label" for="unit_name">Satuan</label><input class="ops-input" id="unit_name" name="unit_name" value="{{ old('unit_name', 'pcs') }}" required maxlength="30"></div>
        <div><label class="ops-label" for="stock_qty">Stok awal</label><input class="ops-input" id="stock_qty" type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" required></div>
        <div style="grid-column:1/-1"><label class="ops-label" for="description">Keterangan (opsional)</label><textarea class="ops-textarea" id="description" name="description" maxlength="1000">{{ old('description') }}</textarea></div>
        <div class="ops-actions"><button class="ops-btn ops-btn-primary" type="submit">Simpan</button><a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.items.index') }}">Kembali</a></div>
    </form>
</x-ops-app>
