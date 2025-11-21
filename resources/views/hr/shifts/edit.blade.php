<x-app title="Edit Shift">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('hr.shifts.update', $shift->id) }}" class="card" style="max-width:400px">
        @csrf 
        @method('PUT')

        <label>Nama Shift</label>
        <input 
            type="text" 
            name="name" 
            value="{{ old('name', $shift->name) }}" 
            required
        >

        <label>Jam Masuk</label>
        <input 
            type="time" 
            name="start_time" 
            value="{{ old('start_time', $shift->start_time?->format('H:i')) }}" 
            required
        >

        <label>Jam Pulang</label>
        <input 
            type="time" 
            name="end_time" 
            value="{{ old('end_time', $shift->end_time?->format('H:i')) }}" 
            required
        >

        <button class="btn-primary" style="margin-top:8px">Update</button>
    </form>

</x-app>
