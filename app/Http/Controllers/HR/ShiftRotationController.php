<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShiftRotationController extends Controller
{
    public function index()
    {
        $ready = Schema::hasTable('shift_rotations') && Schema::hasTable('shift_rotation_members') && Schema::hasTable('shift_rotation_snapshots');
        $rotations = $ready ? DB::table('shift_rotations')->orderByDesc('is_active')->orderBy('name')->get() : collect();
        $members = $ready ? DB::table('shift_rotation_members')->join('users', 'users.id', '=', 'shift_rotation_members.user_id')
            ->join('shifts', 'shifts.id', '=', 'shift_rotation_members.start_shift_id')
            ->select('shift_rotation_members.shift_rotation_id', 'users.name', 'shifts.name as shift_name')
            ->orderBy('users.name')->get()->groupBy('shift_rotation_id') : collect();
        $userRotationNames = $ready ? DB::table('shift_rotation_members')
            ->join('shift_rotations', 'shift_rotations.id', '=', 'shift_rotation_members.shift_rotation_id')
            ->where('shift_rotations.is_active', true)
            ->pluck('shift_rotations.name', 'shift_rotation_members.user_id') : collect();
        $snapshots = collect();
        foreach ($rotations as $rotation) {
            $snapshots->put($rotation->id, DB::table('shift_rotation_snapshots')->join('users', 'users.id', '=', 'shift_rotation_snapshots.user_id')
                ->where('shift_rotation_id', $rotation->id)->select('shift_rotation_snapshots.*', 'users.name as user_name')
                ->orderByDesc('work_date')->orderBy('users.name')->limit(200)->get());
        }

        return view('hr.shift-rotations.index', [
            'ready' => $ready,
            'rotations' => $rotations,
            'members' => $members,
            'userRotationNames' => $userRotationNames,
            'snapshots' => $snapshots,
            'shifts' => Shift::with(['days' => fn ($query) => $query->where('day_of_week', 1)])->where('is_active', true)->orderBy('name')->get(),
            'shiftNames' => Shift::orderBy('name')->pluck('name', 'id'),
            'users' => User::active()->whereHas('employeeShift')->with('employeeShift.shift')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('shift_rotations') || ! Schema::hasTable('shift_rotation_members') || ! Schema::hasTable('shift_rotation_snapshots')) {
            throw ValidationException::withMessages(['name' => 'Pengaturan rotasi belum tersedia. Hubungi HR atau IT.']);
        }

        $input = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'frequency' => ['required', Rule::in(['weekly', 'monthly', 'days'])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'weekly_day' => ['nullable', 'integer', 'between:1,7'],
            'interval_days' => ['required_if:frequency,days', 'nullable', 'integer', 'between:1,365'],
            'shift_ids' => ['required', 'array', 'min:2', 'max:20'],
            'shift_ids.*' => ['required', 'integer', 'distinct', Rule::exists('shifts', 'id')->where('is_active', true)],
            'members' => ['required', 'array'],
            'members.*' => ['nullable', 'integer'],
        ]);

        $members = array_filter($input['members'], fn ($value) => $value !== null && $value !== '');
        $shiftIds = array_map('intval', $input['shift_ids']);
        if (! $members || collect(array_keys($members))->contains(fn ($id) => ! ctype_digit((string) $id) || (int) $id < 1)) {
            throw ValidationException::withMessages(['members' => 'Pilih karyawan yang akan mengikuti rotasi.']);
        }
        DB::transaction(function () use ($input, $members, $shiftIds) {
            $userIds = array_map('intval', array_keys($members));
            $users = User::active()->whereIn('id', $userIds)->lockForUpdate()->get();
            $schedules = DB::table('employee_shifts')->whereIn('user_id', $userIds)->lockForUpdate()->get(['user_id', 'shift_id']);
            if ($users->count() !== count($userIds) || $schedules->count() !== count($userIds) || $schedules->pluck('user_id')->unique()->count() !== count($userIds)) {
                throw ValidationException::withMessages(['members' => 'Setiap karyawan harus aktif dan memiliki jadwal.']);
            }
            if ($schedules->contains(fn ($schedule) => ! in_array((int) $schedule->shift_id, $shiftIds, true))) {
                throw ValidationException::withMessages(['members' => 'Shift pada jadwal karyawan yang dipilih harus ada dalam urutan siklus.']);
            }
            if (DB::table('shift_rotation_members')->whereIn('user_id', $userIds)->exists()) {
                throw ValidationException::withMessages(['members' => 'Ada karyawan yang sudah mengikuti rotasi lain.']);
            }
            if (DB::table('shift_rotation_snapshots')->whereIn('user_id', $userIds)->whereDate('work_date', '>=', $input['start_date'])->exists()) {
                throw ValidationException::withMessages(['start_date' => 'Karyawan sudah memiliki riwayat rotasi pada tanggal tersebut. Pilih tanggal mulai setelahnya.']);
            }
            $shifts = Shift::whereIn('id', $shiftIds)->lockForUpdate()->get();
            if ($shifts->count() !== count($shiftIds) || $shifts->contains(fn ($shift) => ! $shift->is_active) || $shifts->pluck('is_wita')->map(fn ($value) => (bool) $value)->unique()->count() !== 1) {
                throw ValidationException::withMessages(['shift_ids' => 'Shift harus aktif dan menggunakan zona waktu yang sama.']);
            }

            $id = DB::table('shift_rotations')->insertGetId([
                'name' => $input['name'],
                'frequency' => $input['frequency'],
                'start_date' => $input['start_date'],
                'weekly_day' => $input['weekly_day'] ?? 1,
                'interval_days' => $input['frequency'] === 'days' ? $input['interval_days'] : null,
                'shift_ids' => json_encode($shiftIds, JSON_THROW_ON_ERROR),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($schedules as $schedule) {
                DB::table('shift_rotation_members')->insert([
                    'shift_rotation_id' => $id,
                    'user_id' => (int) $schedule->user_id,
                    'start_shift_id' => (int) $schedule->shift_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('hr.shift-rotations.index')->with('success', 'Rotasi disimpan dan akan diproses oleh worker.');
    }

    public function stop(int $rotation)
    {
        DB::transaction(function () use ($rotation) {
            $item = DB::table('shift_rotations')->where('id', $rotation)->lockForUpdate()->first();
            abort_unless($item && $item->is_active, 404);
            DB::table('shift_rotations')->where('id', $rotation)->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('shift_rotation_members')->where('shift_rotation_id', $rotation)->delete();
        });

        return redirect()->route('hr.shift-rotations.index')->with('success', 'Rotasi dihentikan. Riwayat jadwal tetap tersimpan.');
    }
}
