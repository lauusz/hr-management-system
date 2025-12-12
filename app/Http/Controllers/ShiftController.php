<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftDay;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $items = Shift::withCount('days')
            ->orderBy('name')
            ->paginate(100);

        return view('hr.shifts.index', compact('items'));
    }

    public function create()
    {
        return view('hr.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'days'        => 'required|array',
            'days.*.is_holiday'  => 'nullable|boolean',
            'days.*.start_time'  => 'nullable|date_format:H:i',
            'days.*.end_time'    => 'nullable|date_format:H:i',
            'days.*.note'        => 'nullable|string|max:255',
        ]);

        $shift = Shift::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        foreach ($validated['days'] as $dayOfWeek => $dayData) {
            $isHoliday = isset($dayData['is_holiday']) ? (bool) $dayData['is_holiday'] : false;
            $startTime = $dayData['start_time'] ?? null;
            $endTime   = $dayData['end_time'] ?? null;
            $note      = $dayData['note'] ?? null;

            if (!$isHoliday && (!$startTime || !$endTime)) {
                continue;
            }

            ShiftDay::create([
                'shift_id'    => $shift->id,
                'day_of_week' => (int) $dayOfWeek,
                'start_time'  => $isHoliday ? null : $startTime,
                'end_time'    => $isHoliday ? null : $endTime,
                'is_holiday'  => $isHoliday,
                'note'        => $note,
            ]);
        }

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        $shift->load('days');

        return view('hr.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'days'        => 'required|array',
            'days.*.is_holiday'  => 'nullable|boolean',
            'days.*.start_time'  => 'nullable|date_format:H:i',
            'days.*.end_time'    => 'nullable|date_format:H:i',
            'days.*.note'        => 'nullable|string|max:255',
        ]);

        $shift->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        $shift->days()->delete();

        foreach ($validated['days'] as $dayOfWeek => $dayData) {
            $isHoliday = isset($dayData['is_holiday']) ? (bool) $dayData['is_holiday'] : false;
            $startTime = $dayData['start_time'] ?? null;
            $endTime   = $dayData['end_time'] ?? null;
            $note      = $dayData['note'] ?? null;

            if (!$isHoliday && (!$startTime || !$endTime)) {
                continue;
            }

            ShiftDay::create([
                'shift_id'    => $shift->id,
                'day_of_week' => (int) $dayOfWeek,
                'start_time'  => $isHoliday ? null : $startTime,
                'end_time'    => $isHoliday ? null : $endTime,
                'is_holiday'  => $isHoliday,
                'note'        => $note,
            ]);
        }

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}
