<x-atk-app title="Kategori ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Kategori</h1>
            <p class="atk-subtitle">Kelola pengelompokan barang ATK.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('v2.atk.admin.categories.store') }}" class="atk-card atk-form-grid" style="margin-bottom:14px">
        @csrf
        <input class="atk-input" name="name" placeholder="Nama kategori" required>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Tambah Kategori</button>
        </div>
    </form>
    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>Nama</th><th>Barang</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <form id="category-{{ $category->id }}" method="POST" action="{{ route('v2.atk.admin.categories.update', $category) }}">
                                @csrf
                                @method('PUT')
                                <input class="atk-input" name="name" value="{{ $category->name }}" required>
                            </form>
                        </td>
                        <td>{{ $category->items_count }}</td>
                        <td>
                            <label class="atk-actions" form="category-{{ $category->id }}">
                                <input type="checkbox" name="is_active" value="1" form="category-{{ $category->id }}" @checked($category->is_active)>
                                <span>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </label>
                        </td>
                        <td>
                            <button class="atk-btn atk-btn-secondary" type="submit" form="category-{{ $category->id }}">Simpan</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$categories" preserve-query />
</x-atk-app>
