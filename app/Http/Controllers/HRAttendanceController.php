<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class HRAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date   = $request->get('date', now()->toDateString());
        $status = $request->get('status');

        $query = Attendance::with(['user', 'shift'])
            ->where('date', $date)
            ->orderBy('clock_in_at');

        if ($status === 'TERLAMBAT') {
            $query->where('status', 'TERLAMBAT');
        } elseif ($status === 'HADIR') {
            $query->where('status', 'HADIR');
        }

        $items = $query->paginate(15)->withQueryString();

        return view('hr.attendances.index', compact('items', 'date', 'status'));
    }
}
