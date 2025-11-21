<x-app title="Detail Pengajuan">
  <div class="lr-detail">
    <div class="back-btn">
      <a href="{{ route('leave-requests.index') }}" class="btn-back">← Kembali</a>
    </div>

    <div class="card">
      <p><b>Pemohon:</b> {{ $item->user->name }} ({{ $item->user->role }})</p>

      @if($item->notes)
      <div class="card" style="background:#fff3cd;color:#856404;border:1px solid #ffeeba">
        {{ $item->notes }}
      </div>
      @endif

      <p><b>Jenis:</b> {{ $item->type_label }}</p>
      <p><b>Periode:</b> {{ $item->start_date->format('d M Y') }} – {{ $item->end_date->format('d M Y') }}</p>
      <p><b>Status:</b>
        <span class="badge {{ strtolower($item->status) }}">{{ $item->status_label }}</span>
      </p>

      @php
          $raw = $item->photo;
          $isFull = \Illuminate\Support\Str::startsWith($raw, 'leave_photos/');
          $rel = $raw ? ($isFull ? $raw : ('leave_photos/' . $raw)) : null;
          $exists = $rel ? \Illuminate\Support\Facades\Storage::disk('public')->exists($rel) : false;
          $url = $exists ? \Illuminate\Support\Facades\Storage::url($rel) : null;
      @endphp

      @if ($url)
        <p><b>Foto:</b></p>
        <div class="photo-box">
          <a href="{{ $url }}" target="_blank" rel="noopener">
            <img src="{{ $url }}" alt="Lampiran Pengajuan Izin">
          </a>
        </div>
      @elseif ($item->photo)
        <div class="card" style="background:#ffecec;color:#a40000;border:1px solid #f5c2c7">
          File foto tidak ditemukan di storage. ({{ $item->photo }})
        </div>
      @endif

      @if($item->reason)
      <p><b>Alasan:</b> {{ $item->reason }}</p>
      @endif

      @if($item->approved_by)
      <p><b>Disetujui/Terakhir Diputus:</b>
        {{ $item->approver?->name }} pada {{ $item->approved_at?->format('d M Y H:i') }}
      </p>
      @endif

      <div class="actions">
        @can('approve', $item)
        <form class="inline" method="POST" action="{{ route('leave-requests.approve',$item) }}">
          @csrf
          <button class="btn btn-success" type="submit">Approve</button>
        </form>
        <form class="inline" method="POST" action="{{ route('leave-requests.reject',$item) }}">
          @csrf
          <button class="btn btn-danger" type="submit">Reject</button>
        </form>
        @endcan

        @can('delete', $item)
        <form class="inline" method="POST" action="{{ route('leave-requests.destroy',$item) }}">
          @csrf @method('DELETE')
          <button class="btn btn-danger-outline" type="submit">Hapus</button>
        </form>
        @endcan
      </div>
    </div>
  </div>

  <style>
    .lr-detail {
      max-width: 640px;
      margin: auto;
    }

    .back-btn {
      margin-bottom: 10px;
    }

    .btn-back {
      display: inline-block;
      font-size: 13px;
      padding: 4px 8px;
      border-radius: 6px;
      border: 1px solid #ddd;
      background: #fff;
      color: #333;
      text-decoration: none;
      transition: background .2s ease, color .2s ease;
    }

    .btn-back:hover {
      background: #f1f1f1;
      color: #1e4a8d;
    }

    .photo-box {
      margin: 8px 0;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
      max-width: 320px;
    }

    .photo-box img {
      width: 100%;
      height: auto;
      display: block;
    }

    .actions {
      margin-top: 16px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .btn.btn-success {
      background: #169b62;
      color: #fff;
      border-color: #169b62;
    }

    .btn.btn-danger {
      background: #c62828;
      color: #fff;
      border-color: #c62828;
    }

    .btn.btn-danger-outline {
      background: #fff;
      color: #c62828;
      border-color: #e3a4a4;
    }

    .btn.btn-danger-outline:hover {
      background: #fff5f5;
    }

    @media(max-width:600px) {
      .lr-detail {
        padding: 0 8px;
      }

      .actions {
        flex-direction: column;
        align-items: stretch;
      }

      .actions .btn {
        width: 100%;
      }
    }
  </style>
</x-app>
