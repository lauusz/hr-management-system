<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftDay;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function index()
    {
        $items = Shift::withCount('days')
            ->orderBy('name')
            ->paginate(20);

        return view('hr.shifts.index', compact('items'));
    }

    public function create()
    {
        return view('hr.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'days' => 'required|array',
            'days.*.is_holiday' => 'nullable|boolean',
            'days.*.is_overnight' => 'nullable|boolean',
            'days.*.start_time' => 'nullable|date_format:H:i',
            'days.*.end_time' => 'nullable|date_format:H:i',
            'days.*.note' => 'nullable|string|max:255',
        ]);

        $days = $this->normalizeDays($validated['days']);

        $shift = Shift::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->storeDays($shift, $days);

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
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'days' => 'required|array',
            'days.*.is_holiday' => 'nullable|boolean',
            'days.*.is_overnight' => 'nullable|boolean',
            'days.*.start_time' => 'nullable|date_format:H:i',
            'days.*.end_time' => 'nullable|date_format:H:i',
            'days.*.note' => 'nullable|string|max:255',
        ]);

        $days = $this->normalizeDays($validated['days']);

        $shift->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $shift->days()->delete();

        $this->storeDays($shift, $days);

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

    private function normalizeDays(array $days): array
    {
        $patterns = [];

        foreach ($days as $dayOfWeek => $dayData) {
            $isHoliday = filter_var($dayData['is_holiday'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOvernight = filter_var($dayData['is_overnight'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $startTime = $dayData['start_time'] ?? null;
            $endTime = $dayData['end_time'] ?? null;

            if ($isHoliday) {
                if ($isOvernight) {
                    throw ValidationException::withMessages([
                        "days.$dayOfWeek.is_overnight" => 'Hari libur tidak dapat ditandai sebagai shift lintas hari.',
                    ]);
                }

                $patterns[] = [
                    'dayOfWeek' => $dayOfWeek,
                    'isHoliday' => $isHoliday,
                    'isOvernight' => false,
                    'startTime' => null,
                    'endTime' => null,
                    'note' => $dayData['note'] ?? null,
                ];

                continue;
            }

            if (! $startTime && ! $endTime) {
                continue;
            }

            if (! $startTime || ! $endTime) {
                throw ValidationException::withMessages([
                    "days.$dayOfWeek.start_time" => 'Jam masuk dan jam pulang harus diisi bersamaan.',
                ]);
            }

            if ($isOvernight && $endTime > $startTime) {
                throw ValidationException::withMessages([
                    "days.$dayOfWeek.is_overnight" => 'Shift lintas hari harus memiliki jam pulang sebelum atau sama dengan jam masuk.',
                ]);
            }

            if (! $isOvernight && $endTime <= $startTime) {
                throw ValidationException::withMessages([
                    "days.$dayOfWeek.is_overnight" => 'Tandai shift lintas hari bila jam pulang sebelum atau sama dengan jam masuk.',
                ]);
            }

            $patterns[] = [
                'dayOfWeek' => $dayOfWeek,
                'isHoliday' => false,
                'isOvernight' => $isOvernight,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'note' => $dayData['note'] ?? null,
            ];
        }

        return $patterns;
    }

    private function storeDays(Shift $shift, array $patterns): void
    {
        foreach ($patterns as $pattern) {
            ShiftDay::create([
                'shift_id' => $shift->id,
                'day_of_week' => (int) $pattern['dayOfWeek'],
                'start_time' => $pattern['startTime'],
                'end_time' => $pattern['endTime'],
                'is_holiday' => $pattern['isHoliday'],
                'is_overnight' => $pattern['isOvernight'],
                'note' => $pattern['note'],
            ]);
        }
    }
}
