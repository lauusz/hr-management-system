<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $items = Shift::orderBy('start_time')->paginate(100);
        return view('hr.shifts.index', compact('items'));
    }

    public function create()
    {
        return view('hr.shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i'
        ]);

        $today = now()->toDateString(); // contoh: 2025-11-14

        $start = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $request->start_time);
        $end   = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $request->end_time);


        Shift::create([
            'name'       => $request->name,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        return redirect()->route('hr.shifts.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        return view('hr.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i'
        ]);

        $today = now()->toDateString();

        $start = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $request->start_time);
        $end   = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $request->end_time);

        $shift->update([
            'name'       => $request->name,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        return redirect()->route('hr.shifts.index')
            ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('hr.shifts.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}
