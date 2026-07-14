<x-atk-app title="Pengajuan Saya">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Saya</h1>
            <p class="atk-subtitle">Pantau status permintaan ATK Anda.</p>
        </div>
    </div>

    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <div>
            <label class="atk-label" for="status">Status</label>
            <select class="atk-select" id="status" name="status">
                <option value="">Semua</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
                <option value="APPROVED" @selected(request('status') === 'APPROVED')>Approved</option>
                <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Partial</option>
                <option value="REJECTED" @selected(request('status') === 'REJECTED')>Rejected</option>
            </select>
        </div>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.requests.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>No Request</th><th>PT</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td><strong>{{ $request->request_number }}</strong></td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td><span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">{{ $request->status }}</span></td>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td><a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.requests.show', $request) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />
</x-atk-app>
