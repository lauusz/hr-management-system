<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrOvertimeController extends Controller
{
    public function index()
    {
        // Tampilkan yang sudah diapprove supervisor atau PENDING_HR (jika ada flow bypass)
        // Sesuai request user: "Disetujui SUPERVISOR -> Diputus HRD"
        // Jadi HRD melihat yang STATUS_APPROVED_SUPERVISOR
        
        $overtimes = OvertimeRequest::where('status', OvertimeRequest::STATUS_APPROVED_SUPERVISOR)
            ->with(['user.profile.pt', 'user.division', 'supervisorApprover'])
            ->orderByDesc('created_at')
            ->paginate(100);

        return view('hr.overtime_requests.index', compact('overtimes'));
    }

    public function master(Request $request)
    {
        // Master Data: Tampilkan SEMUA request (History) dengan Filter
        $query = OvertimeRequest::with(['user.profile.pt', 'user.division', 'supervisorApprover', 'hrdApprover'])
            ->orderByDesc('created_at');

        // Filter: Search User Name
        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Date Range (overtime_date)
        // Default: Current Month if not filtered
        if (!$request->filled('overtime_date_range')) {
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');
            $request->merge(['overtime_date_range' => "$startOfMonth sampai $endOfMonth"]);
        }

        if ($request->filled('overtime_date_range')) {
            $rangeVal = $request->overtime_date_range;
            $dates = [];
            
            if(str_contains($rangeVal, ' sampai ')){
                 $dates = explode(' sampai ', $rangeVal);
            } else {
                 $dates = explode(' - ', $rangeVal);
            }

            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $query->whereDate('overtime_date', '>=', $startDate)
                      ->whereDate('overtime_date', '<=', $endDate);
                
                // Determine Title Context
                try {
                    $startObj = \Carbon\Carbon::parse($startDate);
                    $endObj = \Carbon\Carbon::parse($endDate);
                    
                    if ($startObj->format('Y-m') === $endObj->format('Y-m')) {
                        $periodLabel = $startObj->translatedFormat('F Y');
                    } else {
                        $periodLabel = $startObj->translatedFormat('d M') . ' - ' . $endObj->translatedFormat('d M Y');
                    }
                } catch (\Exception $e) {
                    $periodLabel = 'Periode Tertentu';
                }

            } elseif (count($dates) === 1) {
                $query->whereDate('overtime_date', trim($dates[0]));
                $periodLabel = \Carbon\Carbon::parse($dates[0])->translatedFormat('d M Y');
            }
        }

        $overtimes = $query->paginate(100)->withQueryString();

        // Data untuk Filter UI
        $statusOptions = [
            OvertimeRequest::STATUS_PENDING_SUPERVISOR,
            OvertimeRequest::STATUS_APPROVED_SUPERVISOR,
            OvertimeRequest::STATUS_APPROVED_HRD,
            OvertimeRequest::STATUS_REJECTED,
            OvertimeRequest::STATUS_CANCELLED,
        ];

        // Keep input values
        $q = $request->q;
        $status = $request->status;
        $overtimeDateRange = $request->overtime_date_range;
        
        return view('hr.overtime_requests.master', compact('overtimes', 'statusOptions', 'q', 'status', 'overtimeDateRange', 'periodLabel'));
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        return view('hr.overtime_requests.show', compact('overtimeRequest'));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        // Validasi: Status harus pending supervisor atau approved supervisor (pending HR)
        // HR bisa approve kapanpun selama belum REJECTED atau APPROVED_HRD
        if ($overtimeRequest->status == OvertimeRequest::STATUS_REJECTED) {
            return back()->with('error', 'Pengajuan sudah ditolak sebelumnya.');
        }

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_APPROVED_HRD,
            'approved_by_hrd_id' => Auth::id(),
            'approved_by_hrd_at' => now(),
        ]);

        return redirect()->route('hr.overtime-requests.master')->with('success', 'Pengajuan lembur berhasil disetujui (Final).');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        $request->validate([
            'rejection_note' => 'required|string|max:1000',
        ]);

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_REJECTED,
            'rejected_by_id' => Auth::id(),
            'rejection_note' => $request->rejection_note,
        ]);

        return redirect()->route('hr.overtime-requests.master')->with('success', 'Pengajuan lembur berhasil ditolak.');
    }
}
