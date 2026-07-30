<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShiftChange;
use App\Models\Position;
use App\Models\Pt;
use App\Models\Shift;
use App\Models\User;
use App\Services\OperationalScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OperationalScheduleController extends Controller
{
    public function __construct(
        private readonly OperationalScheduleService $schedules
    ) {}

    public function index(Request $request)
    {
        $this->schedules->applyAllDue(now());

        $query = User::query()
            ->active()
            ->where('is_ops_schedule_member', true)
            ->with([
                'profile.pt',
                'position',
                'employeeShift.shift',
                'employeeShift.location',
                'pendingShiftChange',
            ])
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->value();
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pt_id')) {
            $query->whereHas(
                'profile',
                fn ($builder) => $builder->where('pt_id', $request->integer('pt_id'))
            );
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->integer('position_id'));
        }

        if ($request->input('shift_id') === 'none') {
            $query->whereDoesntHave('employeeShift');
        } elseif ($request->filled('shift_id')) {
            $query->whereHas(
                'employeeShift',
                fn ($builder) => $builder->where('shift_id', $request->integer('shift_id'))
            );
        }

        if ($request->input('pending_status') === 'pending') {
            $query->whereHas('pendingShiftChange');
        } elseif ($request->input('pending_status') === 'none') {
            $query->whereDoesntHave('pendingShiftChange');
        }

        return view('hr.operational_schedules.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'q' => $request->input('q'),
            'ptId' => $request->input('pt_id'),
            'positionId' => $request->input('position_id'),
            'shiftId' => $request->input('shift_id'),
            'pendingStatus' => $request->input('pending_status'),
            'ptOptions' => Pt::query()->orderBy('name')->get(),
            'positionOptions' => Position::query()->orderBy('name')->get(),
            'shiftOptions' => Shift::query()->where('is_active', true)->orderBy('name')->get(),
            'locationOptions' => AttendanceLocation::query()
                ->where('is_active', true)->orderBy('name')->get(),
            'availableUsers' => User::query()
                ->active()
                ->where('is_ops_schedule_member', false)
                ->with('position')
                ->orderBy('name')
                ->get(['id', 'name', 'position_id']),
            'nextMonthDate' => now()->addMonthNoOverflow()->startOfMonth(),
        ]);
    }

    public function storeMembers(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query
                        ->where('status', User::STATUS_ACTIVE)
                        ->where('is_ops_schedule_member', false)),
            ],
        ]);

        User::query()
            ->whereIn('id', $validated['user_ids'])
            ->update(['is_ops_schedule_member' => true]);

        return back()->with('success', count($validated['user_ids']).' karyawan ditambahkan ke Jadwal OPS.');
    }

    public function destroyMember(User $user)
    {
        DB::transaction(function () use ($user) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->update(['is_ops_schedule_member' => false]);

            EmployeeShiftChange::query()
                ->where('user_id', $lockedUser->id)
                ->where('status', EmployeeShiftChange::STATUS_PENDING)
                ->lockForUpdate()
                ->update([
                    'status' => EmployeeShiftChange::STATUS_CANCELLED,
                    'pending_slot' => null,
                    'cancelled_at' => now(),
                ]);
        });

        return back()->with('success', $user->name.' dihapus dari daftar Jadwal OPS.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query
                        ->where('status', User::STATUS_ACTIVE)
                        ->where('is_ops_schedule_member', true)),
            ],
            'shift_id' => [
                'required',
                Rule::exists('shifts', 'id')->where('is_active', true),
            ],
            'location_id' => [
                'required',
                Rule::exists('attendance_locations', 'id')->where('is_active', true),
            ],
            'apply_mode' => ['required', Rule::in(['NOW', 'NEXT_MONTH'])],
        ]);

        $shift = Shift::findOrFail($validated['shift_id']);
        $location = AttendanceLocation::findOrFail($validated['location_id']);

        if ($validated['apply_mode'] === 'NOW') {
            $this->schedules->applyNow(
                $validated['user_ids'],
                $shift,
                $location,
                $request->user()
            );
            $message = 'Jadwal langsung diterapkan untuk '.count($validated['user_ids']).' karyawan.';
        } else {
            $this->schedules->scheduleNextMonth(
                $validated['user_ids'],
                $shift,
                $location,
                $request->user(),
                now()
            );
            $message = 'Jadwal bulan depan disimpan untuk '.count($validated['user_ids']).' karyawan.';
        }

        return back()->with('success', $message);
    }
}
