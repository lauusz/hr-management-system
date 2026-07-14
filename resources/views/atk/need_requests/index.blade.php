<x-atk-app title="Pengajuan Barang Saya">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Barang Saya</h1>
            <p class="atk-subtitle">Pantau status pengajuan restock atau barang baru.</p>
        </div>
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.need-requests.create') }}">Ajukan Barang</a>
    </div>
    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($needRequests as $needRequest)
                    <tr>
                        <td>
                            <strong>{{ $needRequest->requested_item_name }}</strong>
                            @if($needRequest->item)
                                <div class="atk-product-meta">{{ $needRequest->item->name }}</div>
                            @endif
                        </td>
                        <td>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</td>
                        <td>{{ $needRequest->reason }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                                {{ $needRequest->status }}
                            </span>
                        </td>
                        <td>{{ $needRequest->admin_note ?? '-' }}</td>
                        <td>{{ $needRequest->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada pengajuan barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$needRequests" preserve-query />
</x-atk-app>
