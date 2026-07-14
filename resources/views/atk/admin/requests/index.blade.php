<x-atk-app title="Request Masuk ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Request Masuk</h1>
            <p class="atk-subtitle">Buka halaman review admin untuk cek item dan finalisasi stok.</p>
        </div>
    </div>
    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <input class="atk-input" name="q" value="{{ request('q') }}" placeholder="Cari no request, user, atau PT" autocomplete="off">
        <select class="atk-select" name="status">
            <option value="">Semua status</option>
            <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
            <option value="APPROVED" @selected(request('status') === 'APPROVED')>Approved</option>
            <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Partial</option>
            <option value="REJECTED" @selected(request('status') === 'REJECTED')>Rejected</option>
        </select>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.requests.index') }}">Reset</a>
        </div>
    </form>
    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>No</th><th>User</th><th>PT</th><th>Item</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->request_number }}</td>
                        <td>{{ $request->user_name_snapshot }}</td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td>{{ $request->items->count() }}</td>
                        <td><span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">{{ $request->status }}</span></td>
                        <td>
                            <a class="atk-btn atk-btn-secondary atk-btn-sm" href="{{ route('v2.atk.admin.requests.show', $request) }}">
                                {{ $request->status === 'PENDING' ? 'Review Admin' : 'Lihat Review' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />
</x-atk-app>
