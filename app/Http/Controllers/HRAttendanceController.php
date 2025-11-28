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
        $q      = $request->get('q');

        $query = Attendance::with(['user', 'shift'])
            ->where('date', $date)
            ->orderBy('clock_in_at');

        if ($status === 'TERLAMBAT' || $status === 'HADIR') {
            $query->where('status', $status);
        }

        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(100)->withQueryString();

        return view('hr.attendances.index', compact('items', 'date', 'status', 'q'));
    }
}
