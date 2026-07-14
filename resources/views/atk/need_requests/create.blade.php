<x-atk-app title="Ajukan Barang">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Ajukan Barang</h1>
            <p class="atk-subtitle">Gunakan untuk restock atau kebutuhan barang baru.</p>
        </div>
    </div>
    <form class="atk-card" method="POST" action="{{ route('v2.atk.need-requests.store') }}">
        @csrf
        <input type="hidden" name="atk_item_id" value="{{ $item?->id }}">
        <div class="atk-form-grid">
            <div>
                <label class="atk-label">Nama Barang</label>
                <input class="atk-input" name="requested_item_name" value="{{ old('requested_item_name', $item?->name) }}" required>
            </div>
            <div>
                <label class="atk-label">Jumlah</label>
                <input class="atk-input" type="number" min="1" name="qty" value="{{ old('qty', 1) }}" required>
            </div>
            <div>
                <label class="atk-label">Satuan</label>
                <input class="atk-input" name="unit_name" value="{{ old('unit_name', $item?->unit_name ?? 'pcs') }}" required>
            </div>
        </div>
        <div style="margin-top:14px">
            <label class="atk-label">Alasan</label>
            <textarea class="atk-textarea" name="reason" required>{{ old('reason') }}</textarea>
        </div>
        <div class="atk-actions" style="justify-content:flex-end;margin-top:14px">
            <button class="atk-btn atk-btn-primary" type="submit">Kirim Pengajuan</button>
        </div>
    </form>
</x-atk-app>
