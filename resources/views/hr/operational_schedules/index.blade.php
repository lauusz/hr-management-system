<x-app title="Master Jadwal OPS">
    <x-slot name="header">
        <div>
            <h1 class="ops-title">Master Jadwal OPS</h1>
            <p class="ops-subtitle">Atur shift dan lokasi tim operasional secara bulk</p>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="ops-alert">{{ session('success') }}</div>
    @endif

    @php
        $hasAdvancedFilter = ($ptId ?? null)
            || ($positionId ?? null)
            || ($shiftId ?? null)
            || ($pendingStatus ?? null);
    @endphp

    <div class="ops-actions">
        <button type="button" class="ops-btn ops-btn-primary" data-modal-target="add-ops-members">
            + Tambah Anggota OPS
        </button>
    </div>

    <div class="ops-card ops-filter-card">
        <form method="GET" action="{{ route('hr.operational-schedules.index') }}">
            <div class="ops-search-row">
                <input
                    type="search"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Cari nama karyawan..."
                    autocomplete="off"
                    class="ops-control ops-search"
                >
                <button type="submit" class="ops-btn ops-btn-primary">Cari</button>
                @if(($q ?? null) || $hasAdvancedFilter)
                    <a href="{{ route('hr.operational-schedules.index') }}" class="ops-btn ops-btn-secondary">Reset</a>
                @endif
                <button
                    type="button"
                    class="ops-btn ops-btn-secondary"
                    id="opsFilterToggle"
                    aria-expanded="{{ $hasAdvancedFilter ? 'true' : 'false' }}"
                    aria-controls="opsFilterPanel"
                >
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/>
                    </svg>
                    Filter
                </button>
            </div>

            <div id="opsFilterPanel" class="ops-filter-panel" @if(! $hasAdvancedFilter) hidden @endif>
                <label>
                    <span>PT</span>
                    <select name="pt_id" class="ops-control">
                        <option value="">Semua PT</option>
                        @foreach($ptOptions as $pt)
                            <option value="{{ $pt->id }}" @selected(($ptId ?? '') == $pt->id)>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Jabatan</span>
                    <select name="position_id" class="ops-control">
                        <option value="">Semua Jabatan</option>
                        @foreach($positionOptions as $position)
                            <option value="{{ $position->id }}" @selected(($positionId ?? '') == $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Shift Aktif</span>
                    <select name="shift_id" class="ops-control">
                        <option value="">Semua Shift</option>
                        <option value="none" @selected(($shiftId ?? '') === 'none')>Belum Ada Jadwal</option>
                        @foreach($shiftOptions as $shift)
                            <option value="{{ $shift->id }}" @selected(($shiftId ?? '') == $shift->id)>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Jadwal Bulan Depan</span>
                    <select name="pending_status" class="ops-control">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(($pendingStatus ?? '') === 'pending')>Sudah Diatur</option>
                        <option value="none" @selected(($pendingStatus ?? '') === 'none')>Belum Diatur</option>
                    </select>
                </label>
                <button type="submit" class="ops-btn ops-btn-primary">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <div class="ops-card">
        <div class="ops-bulk-bar">
            <div class="ops-selected"><strong id="opsSelectedCount">0</strong> karyawan dipilih</div>
            <label>
                <span>Shift</span>
                <select id="opsShift" class="ops-control">
                    <option value="">Pilih shift</option>
                    @foreach($shiftOptions as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Lokasi</span>
                <select id="opsLocation" class="ops-control">
                    <option value="">Pilih lokasi</option>
                    @foreach($locationOptions as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </label>
            <fieldset class="ops-mode">
                <legend>Berlaku</legend>
                <label><input type="radio" name="ops_apply_mode" value="NOW" checked> Sekarang</label>
                <label>
                    <input type="radio" name="ops_apply_mode" value="NEXT_MONTH">
                    {{ $nextMonthDate->translatedFormat('F Y') }}
                </label>
            </fieldset>
            <button
                type="button"
                id="opsBulkOpen"
                class="ops-btn ops-btn-primary"
                data-modal-target="confirm-ops-bulk"
                disabled
            >
                Ubah Jadwal
            </button>
        </div>

        <div class="ops-table-wrap">
            <table class="ops-table">
                <thead>
                    <tr>
                        <th class="ops-sticky-check">
                            <input type="checkbox" id="opsSelectAll" aria-label="Pilih semua karyawan pada halaman ini">
                        </th>
                        <th class="ops-sticky-name">Karyawan</th>
                        <th>PT / Jabatan</th>
                        <th>Shift Aktif</th>
                        <th>Lokasi</th>
                        <th>Jadwal Bulan Depan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="ops-sticky-check">
                                <input
                                    type="checkbox"
                                    class="ops-row-check"
                                    value="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    aria-label="Pilih {{ $item->name }}"
                                >
                            </td>
                            <td class="ops-sticky-name">
                                <div class="ops-user-name" title="{{ $item->name }}">{{ $item->name }}</div>
                                <div class="ops-muted">{{ $item->username }}</div>
                            </td>
                            <td>
                                <div>{{ $item->profile?->pt?->name ?? '-' }}</div>
                                <div class="ops-muted">{{ $item->position?->name ?? '-' }}</div>
                            </td>
                            <td>
                                @if($item->employeeShift?->shift)
                                    <span class="ops-badge ops-badge-blue">{{ $item->employeeShift->shift->name }}</span>
                                @else
                                    <span class="ops-muted">Belum diatur</span>
                                @endif
                            </td>
                            <td>{{ $item->employeeShift?->location?->name ?? '-' }}</td>
                            <td>
                                @if($item->pendingShiftChange)
                                    <strong>{{ $item->pendingShiftChange->shift_name_snapshot }}</strong>
                                    <div class="ops-muted">
                                        {{ $item->pendingShiftChange->location_name_snapshot }}
                                        · {{ $item->pendingShiftChange->effective_date->format('d/m/Y') }}
                                    </div>
                                @else
                                    <span class="ops-muted">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="ops-btn-link ops-btn-danger"
                                    data-modal-target="remove-ops-member-{{ $item->id }}"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ops-empty">Belum ada anggota Jadwal OPS.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="ops-pagination"><x-pagination :items="$items" /></div>

    <x-modal id="add-ops-members" title="Tambah Anggota OPS" type="form" variant="info">
        <form method="POST" action="{{ route('hr.operational-schedules.members.store') }}">
            @csrf
            <p class="ops-modal-help">Pilih satu atau beberapa karyawan untuk dimasukkan ke daftar operasional.</p>
            <label class="ops-member-filter">
                <span>Filter Jabatan</span>
                <select id="opsMemberPositionFilter" class="ops-control">
                    <option value="">Semua Jabatan</option>
                    @foreach($positionOptions as $position)
                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="ops-member-list">
                @forelse($availableUsers as $user)
                    <label class="ops-member-item" data-position-id="{{ $user->position_id ?? '' }}">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                        <span>
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->position?->name ?? 'Tanpa jabatan' }}</small>
                        </span>
                    </label>
                @empty
                    <p class="ops-muted">Semua karyawan aktif sudah menjadi anggota OPS.</p>
                @endforelse
                <p id="opsMemberFilterEmpty" class="ops-muted ops-member-filter-empty" hidden>
                    Tidak ada karyawan dengan jabatan tersebut.
                </p>
            </div>
            <div class="ops-modal-actions">
                <button type="button" class="ops-btn ops-btn-secondary" data-modal-close="true">Batal</button>
                <button type="submit" class="ops-btn ops-btn-primary" @disabled($availableUsers->isEmpty())>Tambahkan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="confirm-ops-bulk" title="Konfirmasi Perubahan Jadwal" type="form" variant="warning">
        <form method="POST" action="{{ route('hr.operational-schedules.bulk') }}" id="opsBulkForm">
            @csrf
            <div id="opsBulkUserIds"></div>
            <input type="hidden" name="shift_id" id="opsBulkShift">
            <input type="hidden" name="location_id" id="opsBulkLocation">
            <input type="hidden" name="apply_mode" id="opsBulkMode">
            <p id="opsBulkSummary" class="ops-modal-help"></p>
            <div class="ops-modal-actions">
                <button type="button" class="ops-btn ops-btn-secondary" data-modal-close="true">Batal</button>
                <button type="submit" class="ops-btn ops-btn-primary">Terapkan Jadwal</button>
            </div>
        </form>
    </x-modal>

    @foreach($items as $item)
        <x-modal
            id="remove-ops-member-{{ $item->id }}"
            title="Hapus Anggota OPS?"
            type="confirm"
            variant="danger"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.operational-schedules.members.destroy', $item)"
            confirmFormMethod="DELETE"
        >
            Jadwal aktif {{ $item->name }} tetap tersimpan. Jadwal bulan depan yang masih pending akan dibatalkan.
        </x-modal>
    @endforeach

    <style>
        .ops-title { margin: 0; font-size: 1.6rem; color: #0f172a; }
        .ops-subtitle { margin: 4px 0 0; color: #64748b; font-size: .86rem; }
        .ops-alert { margin-bottom: 14px; padding: 11px 14px; border: 1px solid #bbf7d0; border-radius: 10px; background: #f0fdf4; color: #166534; font-size: 13px; }
        .ops-actions { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .ops-card { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; box-shadow: 0 3px 14px rgba(15, 23, 42, .04); overflow: hidden; }
        .ops-filter-card { margin-bottom: 14px; padding: 18px; overflow: visible; }
        .ops-search-row { display: flex; gap: 10px; align-items: center; }
        .ops-search { flex: 1; min-width: 220px; }
        .ops-control { width: 100%; min-height: 40px; padding: 8px 11px; border: 1px solid #d7dee8; border-radius: 9px; background: #fff; color: #1f2937; font: inherit; font-size: 13px; }
        .ops-control:focus { outline: 2px solid rgba(30, 74, 141, .14); border-color: #1e4a8d; }
        .ops-btn { min-height: 40px; padding: 8px 15px; border-radius: 9px; border: 1px solid transparent; display: inline-flex; gap: 7px; align-items: center; justify-content: center; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; white-space: nowrap; }
        .ops-btn-primary { background: #244b8f; color: #fff; }
        .ops-btn-primary:hover { background: #193a73; }
        .ops-btn-secondary { background: #fff; color: #334155; border-color: #d7dee8; }
        .ops-btn:disabled { opacity: .48; cursor: not-allowed; }
        .ops-filter-panel { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)) auto; gap: 12px; align-items: end; padding-top: 16px; margin-top: 16px; border-top: 1px solid #eef2f7; }
        .ops-filter-panel[hidden] { display: none; }
        .ops-filter-panel label, .ops-bulk-bar > label { display: grid; gap: 5px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .ops-bulk-bar { padding: 14px; display: grid; grid-template-columns: auto minmax(160px, 1fr) minmax(160px, 1fr) auto auto; gap: 12px; align-items: end; border-bottom: 1px solid #eef2f7; background: #fbfcfe; }
        .ops-selected { align-self: center; color: #475569; font-size: 13px; white-space: nowrap; }
        .ops-mode { display: flex; gap: 12px; min-height: 40px; align-items: center; padding: 5px 10px; border: 1px solid #d7dee8; border-radius: 9px; }
        .ops-mode legend { padding: 0 4px; color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .ops-mode label { display: flex; gap: 5px; align-items: center; white-space: nowrap; font-size: 12px; }
        .ops-table-wrap { overflow-x: auto; }
        .ops-table { width: 100%; min-width: 1050px; border-collapse: separate; border-spacing: 0; }
        .ops-table th { padding: 11px 12px; background: #f8fafc; border-bottom: 1px solid #e5eaf1; color: #64748b; font-size: 11px; font-weight: 700; text-align: left; text-transform: uppercase; letter-spacing: .03em; }
        .ops-table td { padding: 12px; border-bottom: 1px solid #f0f3f7; color: #1f2937; font-size: 13px; vertical-align: middle; background: #fff; }
        .ops-table tbody tr:last-child td { border-bottom: 0; }
        .ops-table tbody tr:hover td { background: #fbfdff; }
        .ops-sticky-check { position: sticky; left: 0; z-index: 2; width: 42px; text-align: center !important; }
        .ops-sticky-name { position: sticky; left: 42px; z-index: 2; min-width: 210px; box-shadow: 1px 0 #eef2f7; }
        thead .ops-sticky-check, thead .ops-sticky-name { z-index: 3; background: #f8fafc; }
        .ops-user-name { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 700; color: #111827; }
        .ops-muted { margin-top: 2px; color: #8490a3; font-size: 12px; }
        .ops-badge { display: inline-block; max-width: 190px; padding: 4px 9px; border-radius: 999px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 600; }
        .ops-badge-blue { background: #edf4ff; color: #1d4ed8; }
        .ops-btn-link { padding: 4px 8px; border: 0; border-radius: 6px; background: transparent; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
        .ops-btn-danger { color: #b91c1c; }
        .ops-btn-danger:hover { background: #fef2f2; }
        .ops-empty { padding: 40px !important; text-align: center; color: #8490a3 !important; }
        .ops-pagination { margin-top: 16px; }
        .ops-member-list { max-height: 330px; margin-top: 12px; overflow-y: auto; border: 1px solid #e5eaf1; border-radius: 10px; }
        .ops-member-list label { display: flex; gap: 10px; align-items: center; padding: 10px 12px; border-bottom: 1px solid #f0f3f7; cursor: pointer; }
        .ops-member-list label:last-child { border-bottom: 0; }
        .ops-member-filter { display: grid; gap: 5px; margin-top: 14px; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .ops-member-item strong, .ops-member-item small { display: block; }
        .ops-member-item[hidden] { display: none; }
        .ops-member-item strong { color: #334155; font-size: 13px; }
        .ops-member-item small { margin-top: 2px; color: #8490a3; font-size: 11px; font-weight: 500; }
        .ops-member-filter-empty { padding: 14px; text-align: center; }
        .ops-modal-help { margin: 0; color: #64748b; font-size: 13px; }
        .ops-modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
        @media (max-width: 1050px) {
            .ops-filter-panel { grid-template-columns: repeat(2, 1fr); }
            .ops-bulk-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .ops-search-row, .ops-actions { align-items: stretch; flex-wrap: wrap; }
            .ops-search { flex-basis: 100%; min-width: 0; }
            .ops-filter-panel, .ops-bulk-bar { grid-template-columns: 1fr; }
            .ops-sticky-name { min-width: 180px; }
        }
    </style>

    <script>
        (() => {
            const panel = document.getElementById('opsFilterPanel');
            const toggle = document.getElementById('opsFilterToggle');
            const selectAll = document.getElementById('opsSelectAll');
            const checks = [...document.querySelectorAll('.ops-row-check')];
            const count = document.getElementById('opsSelectedCount');
            const bulkButton = document.getElementById('opsBulkOpen');
            const shift = document.getElementById('opsShift');
            const location = document.getElementById('opsLocation');
            const memberPosition = document.getElementById('opsMemberPositionFilter');
            const memberItems = [...document.querySelectorAll('.ops-member-item')];
            const memberFilterEmpty = document.getElementById('opsMemberFilterEmpty');

            toggle?.addEventListener('click', () => {
                panel.hidden = !panel.hidden;
                toggle.setAttribute('aria-expanded', String(!panel.hidden));
            });

            const syncBulk = () => {
                const selected = checks.filter(item => item.checked);
                count.textContent = selected.length;
                bulkButton.disabled = selected.length === 0 || !shift.value || !location.value;
                selectAll.checked = checks.length > 0 && selected.length === checks.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < checks.length;

                document.getElementById('opsBulkUserIds').replaceChildren(
                    ...selected.map(item => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = item.value;
                        return input;
                    })
                );
                document.getElementById('opsBulkShift').value = shift.value;
                document.getElementById('opsBulkLocation').value = location.value;
                const mode = document.querySelector('input[name="ops_apply_mode"]:checked')?.value || 'NOW';
                document.getElementById('opsBulkMode').value = mode;
                const shiftName = shift.options[shift.selectedIndex]?.text || '-';
                const locationName = location.options[location.selectedIndex]?.text || '-';
                const effective = mode === 'NOW' ? 'sekarang' : '{{ $nextMonthDate->translatedFormat('F Y') }}';
                document.getElementById('opsBulkSummary').textContent =
                    `${selected.length} karyawan akan memakai ${shiftName} di ${locationName}, berlaku ${effective}.`;
            };

            selectAll?.addEventListener('change', () => {
                checks.forEach(item => item.checked = selectAll.checked);
                syncBulk();
            });
            checks.forEach(item => item.addEventListener('change', syncBulk));
            shift?.addEventListener('change', syncBulk);
            location?.addEventListener('change', syncBulk);
            document.querySelectorAll('input[name="ops_apply_mode"]')
                .forEach(item => item.addEventListener('change', syncBulk));
            memberPosition?.addEventListener('change', () => {
                let visible = 0;
                memberItems.forEach(item => {
                    const matches = !memberPosition.value
                        || item.dataset.positionId === memberPosition.value;
                    item.hidden = !matches;
                    if (matches) visible++;
                });
                memberFilterEmpty.hidden = visible > 0;
            });
            syncBulk();
        })();
    </script>
</x-app>
