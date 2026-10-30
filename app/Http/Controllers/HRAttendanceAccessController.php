<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HRAttendanceAccessController extends Controller
{
    public function index(Request $request)
    {
        $viewers = User::active()
            ->whereNotIn('role', [UserRole::HRD->value, UserRole::HR_STAFF->value])
            ->with(['division', 'position', 'profile.pt'])
            ->withCount('attendanceVisibleEmployees')
            ->orderBy('name')
            ->get();

        if ($request->filled('viewer')) {
            $viewer = $viewers->firstWhere('id', (int) $request->query('viewer'));
            abort_if(! $viewer, 404);

            $selectedIds = $viewer->attendanceVisibleEmployees()->pluck('users.id')->all();
            $employees = User::with(['division', 'position', 'profile.pt'])
                ->where(fn ($query) => $query->active()->orWhereIn('id', $selectedIds))
                ->orderBy('name')
                ->get();

            return view('hr.attendances.access-detail', compact('viewer', 'employees', 'selectedIds'));
        }

        return view('hr.attendances.access', compact('viewers'));
    }

    public function update(Request $request, User $viewer)
    {
        abort_if(! $viewer->isActive() || $viewer->isHR(), 404);
        $input = $request->validate(['employee_ids_json' => ['required', 'json']]);
        $data = Validator::make(['employee_ids' => json_decode($input['employee_ids_json'], true)], [
            'employee_ids' => ['present', 'array', 'list'],
            'employee_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
        ])->validate();

        DB::transaction(function () use ($viewer, $data) {
            User::whereKey($viewer->id)->lockForUpdate()->firstOrFail();
            $viewer->attendanceVisibleEmployees()->sync($data['employee_ids'] ?? []);
        });

        return redirect()->route('hr.attendance-access.index', ['viewer' => $viewer->id])
            ->with('success', 'Akses lihat absensi berhasil disimpan.');
    }
}
