<x-app title="Izin / Cuti">
  @if (session('ok'))
  <div class="card" style="margin-bottom:12px; background:#e6ffec; color:#065f46; padding:8px 10px; border-radius:8px;">
    {{ session('ok') }}
  </div>
  @endif

  <div style="margin-bottom:16px;">
    <a href="{{ route('leave-requests.create') }}" class="btn primary" style="text-decoration:none;">
      + Buat Pengajuan
    </a>
  </div>

  <div class="card" style="padding:0; overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; min-width:640px;">
      <thead>
        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
          <th style="padding:12px 10px; text-align:left; font-weight:600;">#</th>
          <th style="padding:12px 10px; text-align:left; font-weight:600;">Pemohon</th>
          <th style="padding:12px 10px; text-align:left; font-weight:600;">Tanggal</th>
          <th style="padding:12px 10px; text-align:left; font-weight:600;">Jenis</th>
          <th style="padding:12px 10px; text-align:center; font-weight:600;">Status</th>
          <th style="padding:12px 10px; text-align:center; font-weight:600; width:100px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($items as $i => $row)
        <tr style="border-bottom:1px solid #f3f4f6;">
          <td style="padding:12px 10px; color:#6b7280;">{{ $items->firstItem() + $i }}</td>
          <td style="padding:12px 10px;">{{ $row->user->name }}</td>
          <td style="padding:12px 10px;">
            {{ $row->start_date->format('d M Y') }}
            @if($row->end_date && $row->end_date->ne($row->start_date))
            – {{ $row->end_date->format('d M Y') }}
            @endif
          </td>
          <td style="padding:12px 10px;">{{ $row->type_label }}</td>
          <td style="padding:12px 10px; text-align:center;">
            <span class="badge badge-{{ strtolower($row->status_label) }}">
              {{ $row->status_label }}
            </span>
          </td>
          <td style="padding:12px 10px; text-align:center;">
            <a href="{{ route('leave-requests.show', $row) }}" class="btn" style="text-decoration:none; margin:4px 0; display:inline-block;">
              Detail
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding:16px; text-align:center; color:#6b7280;">
            Belum ada pengajuan.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div style="margin-top:16px;">
    {{ $items->links() }}
  </div>

  <style>
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 13px;
      text-align: center;
      min-width: 90px;
      white-space: nowrap;
    }

    .badge-disetujui {
      background: #dcfce7;
      color: #166534;
    }

    .badge-menunggu {
      background: #fef9c3;
      color: #854d0e;
    }

    .badge-ditolak {
      background: #fee2e2;
      color: #991b1b;
    }

    table th,
    table td {
      vertical-align: middle;
    }

    table tr:hover td {
      background: #f9fafb;
    }

    @media(max-width:600px) {

      table th,
      table td {
        padding: 8px;
        font-size: 13px;
      }
    }
  </style>
</x-app>