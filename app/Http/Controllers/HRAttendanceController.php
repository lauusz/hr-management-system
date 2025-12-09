<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HRAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateStart = $request->query('date_start');
        $dateEnd   = $request->query('date_end');
        $status    = $request->query('status');
        $q         = $request->query('q');

        if (!$dateStart && !$dateEnd) {
            $today     = now()->toDateString();
            $dateStart = $today;
            $dateEnd   = $today;
        } elseif ($dateStart && !$dateEnd) {
            $dateEnd = $dateStart;
        } elseif (!$dateStart && $dateEnd) {
            $dateStart = $dateEnd;
        }

        $query = Attendance::with(['user', 'shift'])
            ->orderBy('clock_in_at');

        if ($dateStart && $dateEnd) {
            $from = Carbon::parse($dateStart)->toDateString();
            $to   = Carbon::parse($dateEnd)->toDateString();

            if ($from > $to) {
                $tmp  = $from;
                $from = $to;
                $to   = $tmp;
            }

            $query->whereBetween('date', [$from, $to]);
            $dateStart = $from;
            $dateEnd   = $to;
        }

        if ($status === 'TERLAMBAT' || $status === 'HADIR') {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(100)->appends([
            'date_start' => $dateStart,
            'date_end'   => $dateEnd,
            'status'     => $status,
            'q'          => $q,
        ]);

        return view('hr.attendances.index', [
            'items'      => $items,
            'date_start' => $dateStart,
            'date_end'   => $dateEnd,
            'status'     => $status,
            'q'          => $q,
        ]);
    }
}
