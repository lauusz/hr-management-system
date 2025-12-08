<x-app title="Divisi & Jabatan">

        <div style="display:flex;gap:24px;border-bottom:1px solid #e5e7eb;">
            
            <button
                class="org-tab-btn active"
                data-target="#tab-divisi"
                style="
                    padding:10px 0;
                    background:none;
                    border:none;
                    font-size:.9rem;
                    cursor:pointer;
                    color:#1e4a8d;
                    font-weight:600;
                    border-bottom:3px solid #1e4a8d;
                ">
                Divisi
            </button>

            <button
                class="org-tab-btn"
                data-target="#tab-jabatan"
                style="
                    padding:10px 0;
                    background:none;
                    border:none;
                    font-size:.9rem;
                    cursor:pointer;
                    color:#6b7280;
                ">
                Jabatan
            </button>

    </div>

    <div id="tab-divisi" class="org-tab-content" style="display:block;">
        <div class="card" style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:1rem;">Daftar Divisi</h3>

                <a href="{{ route('hr.divisions.create') }}"
                   style="padding:8px 12px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:.85rem;text-decoration:none;">
                   + Tambah Divisi
                </a>
            </div>
        </div>

        <div class="card" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Nama Divisi</th>
                        <th>Supervisor</th>
                        <th style="width:150px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisions as $division)
                        <tr>
                            <td>{{ $division->name }}</td>
                            <td>{{ $division->supervisor?->name ?? '-' }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('hr.divisions.edit', $division->id) }}"
                                   style="padding:5px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('hr.divisions.destroy', $division->id) }}"
                                      style="display:inline-block;margin-left:4px;"
                                      onsubmit="return confirm('Hapus divisi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="padding:5px 10px;border-radius:8px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:16px;opacity:.7;">
                                Belum ada divisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    <div id="tab-jabatan" class="org-tab-content" style="display:none;">
        <div class="card" style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:1rem;">Daftar Jabatan</h3>

                <a href="{{ route('hr.positions.create') }}"
                   style="padding:8px 12px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:.85rem;text-decoration:none;">
                   + Tambah Jabatan
                </a>
            </div>
        </div>

        <div class="card" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Nama Jabatan</th>
                        <th>Divisi</th>
                        <th style="width:150px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $position)
                        <tr>
                            <td>{{ $position->name }}</td>
                            <td>{{ $position->division?->name ?? '-' }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('hr.positions.edit', $position->id) }}"
                                   style="padding:5px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('hr.positions.destroy', $position->id) }}"
                                      style="display:inline-block;margin-left:4px;"
                                      onsubmit="return confirm('Hapus jabatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="padding:5px 10px;border-radius:8px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:16px;opacity:.7;">
                                Belum ada jabatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.org-tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {

                document.querySelectorAll('.org-tab-btn').forEach(b => {
                    b.classList.remove('active');
                    b.style.color = '#6b7280';
                    b.style.borderBottom = 'none';
                    b.style.fontWeight = 'normal';
                });

                this.classList.add('active');
                this.style.color = '#1e4a8d';
                this.style.fontWeight = '600';
                this.style.borderBottom = '3px solid #1e4a8d';

                const target = this.getAttribute('data-target');

                document.querySelectorAll('.org-tab-content').forEach(c => {
                    c.style.display = 'none';
                });

                document.querySelector(target).style.display = 'block';
            });
        });
    </script>
    @endpush

</x-app>
