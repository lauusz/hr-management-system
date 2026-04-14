<?php

namespace App\Exports;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class LeaveMasterExport implements FromView, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division',
                'user.position',
                'user.profile.pt',
                'approver'
            ])
            ->orderByDesc('created_at');

        $this->applyFilters($query);

        $items = $query->get();

        $statusLabels = [
            LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Supervisor',
            LeaveRequest::PENDING_HR => 'Menunggu HRD',
            LeaveRequest::STATUS_APPROVED => 'Disetujui',
            LeaveRequest::STATUS_REJECTED => 'Ditolak',
            'BATAL' => 'Dibatalkan',
            'CANCEL_REQ' => 'Pengajuan Batal',
        ];

        return view('hr.leave_requests.export_master', [
            'items' => $items,
            'statusLabels' => $statusLabels,
        ]);
    }

    protected function applyFilters($query)
    {
        // Status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Type filter
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        // Submitted range filter
        if (!empty($this->filters['submitted_range'])) {
            $parts = preg_split('/\s+(to|sampai)\s+/i', $this->filters['submitted_range']);
            try {
                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = (clone $from)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                } elseif (count($parts) >= 2) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = Carbon::parse(trim($parts[1]))->endOfDay();
                    if ($from->gt($to)) {
                        $temp = $from;
                        $from = $to;
                        $to = $temp;
                    }
                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // Period range filter
        if (!empty($this->filters['period_range'])) {
            $parts = preg_split('/\s+(to|sampai)\s+/i', $this->filters['period_range']);
            try {
                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->toDateString();
                    $to = $from;
                } else {
                    $fromDate = Carbon::parse(trim($parts[0]))->startOfDay();
                    $toDate = Carbon::parse(trim($parts[1]))->endOfDay();
                    if ($fromDate->gt($toDate)) {
                        $temp = $fromDate;
                        $fromDate = $toDate;
                        $toDate = $temp;
                    }
                    $from = $fromDate->toDateString();
                    $to = $toDate->toDateString();
                }
                $query->whereDate('start_date', '<=', $to)
                    ->whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [$from]);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // PT filter
        if (!empty($this->filters['pt_id'])) {
            $query->whereHas('user.profile', function ($q) {
                $q->where('pt_id', $this->filters['pt_id']);
            });
        }

        // Search filter
        if (!empty($this->filters['q'])) {
            $query->whereHas('user', function ($sub) {
                $sub->where('name', 'like', '%' . $this->filters['q'] . '%');
            });
        }
    }
}