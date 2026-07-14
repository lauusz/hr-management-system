<x-atk-app title="Pengajuan Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Barang</h1>
            <p class="atk-subtitle">Restock atau barang baru yang diajukan user. Menandai "Selesai" akan menambah stok.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="atk-alert atk-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="atk-alert atk-alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>PT</th>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($needRequests as $needRequest)
                    <tr>
                        <td>{{ $needRequest->user_name_snapshot }}</td>
                        <td>{{ $needRequest->pt_name_snapshot ?? '-' }}</td>
                        <td>
                            <strong>{{ $needRequest->requested_item_name }}</strong>
                            <div class="atk-product-meta">{{ $needRequest->reason }}</div>
                        </td>
                        <td>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                                {{ $needRequest->status }}
                            </span>
                        </td>
                        <td>
                            @if($needRequest->status === 'PENDING')
                                <details class="atk-actions" style="display:inline-block">
                                    <summary class="atk-btn atk-btn-secondary" style="cursor:pointer;list-style:none">Tolak</summary>
                                    <form method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}" style="margin-top:8px;border-top:1px solid rgba(0,0,0,.08);padding-top:8px">
                                        @csrf
                                        <input type="hidden" name="status" value="REJECTED">
                                        <label class="atk-label">Catatan penolakan</label>
                                        <textarea class="atk-input" name="admin_note" rows="2" style="width:240px" placeholder="Alasan ditolak"></textarea>
                                        <button class="atk-btn atk-btn-danger" type="submit" style="margin-top:6px">Konfirmasi Tolak</button>
                                    </form>
                                </details>

                                <details class="atk-actions" style="display:inline-block">
                                    <summary class="atk-btn atk-btn-primary" style="cursor:pointer;list-style:none">Selesai / Restock</summary>
                                    <form method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}" style="margin-top:8px;border-top:1px solid rgba(0,0,0,.08);padding-top:8px;min-width:280px">
                                        @csrf
                                        <input type="hidden" name="status" value="DONE">

                                        @if($needRequest->atk_item_id)
                                            {{-- Barang katalog: target sudah pasti --}}
                                            <p class="atk-product-meta">Barang katalog: <strong>{{ $needRequest->item?->name ?? '-' }}</strong></p>
                                        @else
                                            {{-- Non-katalog: pilih existing atau buat baru --}}
                                            <label class="atk-label">Item tujuan</label>
                                            <select class="atk-select" name="existing_item_id" id="target-{{ $needRequest->id }}" style="width:240px" onchange="toggleNewItem({{ $needRequest->id }})">
                                                <option value="">— Pilih barang katalog —</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }} (stok: {{ $item->stock_qty }} {{ $item->unit_name }})</option>
                                                @endforeach
                                                <option value="__new__">➕ Buat barang baru</option>
                                            </select>

                                            <div id="new-item-{{ $needRequest->id }}" style="display:none;margin-top:8px">
                                                <label class="atk-label">Nama barang baru</label>
                                                <input class="atk-input" type="text" name="new_item_name" value="{{ $needRequest->requested_item_name }}" style="width:240px" maxlength="150">

                                                <label class="atk-label" style="margin-top:6px">Satuan</label>
                                                <input class="atk-input" type="text" name="new_item_unit_name" value="{{ $needRequest->unit_name }}" style="width:240px" maxlength="30">

                                                <label class="atk-label" style="margin-top:6px">Kategori (opsional)</label>
                                                <select class="atk-select" name="new_item_category_id" style="width:240px">
                                                    <option value="">— Tanpa kategori —</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <label class="atk-label" style="margin-top:8px">Jumlah aktual masuk</label>
                                        <input class="atk-input" type="number" name="qty" value="{{ $needRequest->qty }}" min="1" style="width:120px">

                                        <label class="atk-label" style="margin-top:6px">Harga satuan (opsional)</label>
                                        <input class="atk-input" type="number" name="unit_price" min="0" style="width:160px">

                                        <label class="atk-label" style="margin-top:6px">Catatan</label>
                                        <input class="atk-input" type="text" name="admin_note" style="width:240px" placeholder="Catatan admin">

                                        <button class="atk-btn atk-btn-primary" type="submit" style="margin-top:8px">Tambah Stok & Selesai</button>
                                    </form>
                                </details>
                            @else
                                {{ $needRequest->admin_note ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada pengajuan barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$needRequests" preserve-query />

    <script>
        // Toggle show/hide blok "barang baru" berdasarkan pilihan select item tujuan.
        // Dibuat minimal: hanya satu fungsi global, pakai id unik per need-request.
        function toggleNewItem(id) {
            var select = document.getElementById('target-' + id);
            var block = document.getElementById('new-item-' + id);
            if (!select || !block) return;
            block.style.display = (select.value === '__new__') ? 'block' : 'none';
            // Bersihkan field barang baru bila admin beralih ke item existing.
            if (select.value !== '__new__') {
                block.querySelectorAll('input, select').forEach(function (el) { if (el.name !== 'new_item_category_id') el.value = ''; });
            }
        }
    </script>
</x-atk-app>
