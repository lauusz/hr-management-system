{{-- resources/views/supervisor/leave_requests/index.blade.php --}}
@php
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
@endphp

<x-app title="Pengajuan Menunggu Supervisor">
  @if(session('success'))
  <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
    {{ session('success') }}
  </div>
  @endif
  @if ($errors->any())
  <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
    {{ $errors->first() }}
  </div>
  @endif

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
    <div style="opacity:.7;font-size:.9rem;">
      Menampilkan {{ $leaves->firstItem() ?? 0 }}–{{ $leaves->lastItem() ?? 0 }} dari {{ $leaves->total() }} pengajuan
    </div>
  </div>

  <table class="table" style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="text-align:left;border-bottom:1px solid #eee;">
        <th style="padding:10px 8px;">Karyawan</th>
        <th style="padding:10px 8px;">Jenis</th>
        <th style="padding:10px 8px;">Tanggal</th>
        <th style="padding:10px 8px;">Alasan</th>
        <th style="padding:10px 8px; text-align:center; width:110px;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($leaves as $lv)
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:10px 8px;">{{ $lv->user->name ?? '—' }}</td>
        <td style="padding:10px 8px;">{{ $lv->type }}</td>
        <td style="padding:10px 8px;">
          {{ $lv->start_date->format('d M Y') }}
          @if($lv->end_date && $lv->end_date->ne($lv->start_date))
          – {{ $lv->end_date->format('d M Y') }}
          @endif
        </td>
        <td style="padding:10px 8px;">{{ Str::limit($lv->reason, 80) }}</td>

        <td style="padding:14px 8px; text-align:center; vertical-align:middle;">
          <a class="btn"
            href="{{ route('supervisor.leave.show', $lv) }}"
            style="text-decoration:none; margin:4px 0; display:inline-block;">
            Detail
          </a>
        </td>

      </tr>
      @empty
      <tr>
        <td colspan="6" style="opacity:.7; padding:12px 8px;">Tidak ada pengajuan.</td>
      </tr>
      @endforelse
    </tbody>

  </table>

  <div style="margin-top:12px;">
    {{ $leaves->links() }}
  </div>

  <script>
    document.querySelectorAll('.js-confirm-lock').forEach(form => {
      form.addEventListener('submit', function(e) {
        const msg = form.getAttribute('data-confirm') || 'Lanjutkan aksi?';
        if (!confirm(msg)) {
          e.preventDefault();
          return;
        }
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
          btn.setAttribute('disabled', 'disabled');
          btn.style.opacity = '.7';
        }
      });
    });
  </script>
</x-app>