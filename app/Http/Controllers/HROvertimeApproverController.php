<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Division;
use App\Models\OvertimeApproverAssignment;
use App\Models\Position;
use App\Models\Pt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HROvertimeApproverController extends Controller
{
    public function index()
    {
        $employees = User::with(['overtimeApproverAssignment.supervisor', 'overtimeApproverAssignment.manager', 'division', 'profile'])->orderBy('name')->get();
        $supervisors = User::active()->where('role', UserRole::SUPERVISOR->value)->orderBy('name')->get();
        $managers = User::active()->where('role', UserRole::MANAGER->value)->orderBy('name')->get();
        $pts = Pt::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.approvers.overtime', compact('employees', 'supervisors', 'managers', 'pts', 'divisions', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(false));
        $employee = User::findOrFail($data['employee_id']);
        if (in_array($employee->id, array_filter([$data['supervisor_id'] ?? null, $data['manager_id'] ?? null]))) {
            return back()->with('error', 'Karyawan tidak dapat menjadi approver lembur untuk dirinya sendiri.');
        }
        $this->saveAssignment($employee, $data['supervisor_id'] ?? null, $data['manager_id'] ?? null);

        return back()->with('success', 'Approver lembur berhasil disimpan.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate($this->rules(true));
        $employees = User::whereIn('id', $data['employee_ids'])->orderBy('id')->get();
        $skipped = collect($data['employee_ids'])->diff($employees->pluck('id'))->map(fn ($id) => 'ID '.$id.' (karyawan tidak ditemukan)')->all();
        $saved = 0;
        foreach ($employees as $employee) {
            if (in_array($employee->id, [$data['supervisor_id'], $data['manager_id']])) {
                $skipped[] = $employee->name.' (tidak dapat menjadi approver dirinya sendiri)';
                continue;
            }
            $this->saveAssignment($employee, $data['supervisor_id'], $data['manager_id']);
            $saved++;
        }

        return back()->with('success', "$saved karyawan berhasil diperbarui.")
            ->with('overtime_skipped', $skipped);
    }

    private function rules(bool $bulk): array
    {
        $requirement = $bulk ? 'required' : 'nullable';

        return [
            'employee_id' => $bulk ? 'nullable' : 'required|integer|exists:users,id',
            'employee_ids' => $bulk ? 'required|array|min:1' : 'nullable',
            'employee_ids.*' => 'integer|distinct',
            'supervisor_id' => [$requirement, 'integer', Rule::exists('users', 'id')->where('role', UserRole::SUPERVISOR->value)->where('status', User::STATUS_ACTIVE)],
            'manager_id' => [$requirement, 'integer', 'different:supervisor_id', Rule::exists('users', 'id')->where('role', UserRole::MANAGER->value)->where('status', User::STATUS_ACTIVE)],
        ];
    }

    private function saveAssignment(User $employee, $supervisorId, $managerId): void
    {
        DB::transaction(function () use ($employee, $supervisorId, $managerId) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $assignment = OvertimeApproverAssignment::whereKey($employee->id)->lockForUpdate()->first();
            if (! $assignment) {
                $assignment = new OvertimeApproverAssignment(['employee_id' => $employee->id]);
            }
            $assignment->fill(['supervisor_id' => $supervisorId, 'manager_id' => $managerId])->save();
        });
    }
}
