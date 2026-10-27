<details class="overtime-edit" style="margin:14px 0">
    <summary>Ubah pengajuan</summary>
    <form class="overtime-edit-form" method="POST" action="{{ route('overtime-requests.update', $overtime) }}" style="display:grid;gap:8px;margin-top:10px;max-width:440px">
        @csrf @method('PUT')
        <label>Tanggal <input name="overtime_date" type="date" value="{{ $overtime->overtime_date->format('Y-m-d') }}" required></label>
        <label>Jam mulai <input name="start_time" type="time" value="{{ $overtime->start_time->format('H:i') }}" required></label>
        <label>Jam selesai <input name="end_time" type="time" value="{{ $overtime->end_time->format('H:i') }}" required></label>
        <label>Istirahat <input name="break_hours" type="number" min="0" max="23" value="{{ $overtime->break_minutes ? intdiv($overtime->break_minutes, 60) : '' }}" inputmode="numeric"> jam <input name="break_minutes" type="number" min="0" max="59" value="{{ $overtime->break_minutes ? $overtime->break_minutes % 60 : '' }}" inputmode="numeric"> menit</label>
        <label>Keterangan <textarea name="description" maxlength="5000" required>{{ $overtime->description }}</textarea></label>
        @if($overtime->currentApprovalActions()->contains('action', 'APPROVE'))
            <p>Persetujuan yang sudah diberikan akan diulang setelah perubahan disimpan.</p>
        @endif
        <button type="submit">Simpan perubahan</button>
    </form>
</details>
