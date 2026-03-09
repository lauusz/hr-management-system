<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayslipTemplateExport implements FromView, ShouldAutoSize
{
    protected $startMonth;
    protected $endMonth;
    protected $year;
    protected $ptId;
    protected $search;

    public function __construct($startMonth, $endMonth, $year, $ptId, $search = null)
    {
        $this->startMonth = $startMonth;
        $this->endMonth = $endMonth;
        $this->year = $year;
        $this->ptId = $ptId;
        $this->search = $search;
    }

    public function view(): View
    {
        $payrollRows = collect();
        $selectedRows = collect(request()->input('selected_rows', []))
            ->filter(function ($value) {
                return is_string($value) && preg_match('/^\d+-\d{1,2}-\d{4}$/', $value);
            })
            ->unique()
            ->values();
        $selectedRowsLookup = $selectedRows->flip();

        $ptId = $this->ptId;
        $startMonth = $this->startMonth;
        $endMonth = $this->endMonth;
        $year = $this->year;

        $usersQuery = User::query()
            ->active()
            ->when($ptId, function ($query) use ($ptId) {
                $query->whereHas('profile', function ($profileQuery) use ($ptId) {
                    $profileQuery->where('pt_id', $ptId);
                });
            })
            ->with(['profile', 'position', 'division', 'payslips' => function ($q) use ($startMonth, $endMonth, $year) {
                $q->whereBetween('period_month', [$startMonth, $endMonth])
                    ->where('period_year', $year);
            }]);

        if (!empty($this->search)) {
            $usersQuery->where('name', 'like', '%' . $this->search . '%');
        }

        $users = $usersQuery->get();

        foreach ($users as $user) {
            for ($m = $startMonth; $m <= $endMonth; $m++) {
                $rowKey = "{$user->id}-{$m}-{$year}";

                if ($selectedRows->isNotEmpty() && !$selectedRowsLookup->has($rowKey)) {
                    continue;
                }

                $payslip = $user->payslips->firstWhere('period_month', $m);

                $payrollRows->push([
                    'user' => $user,
                    'month_number' => $m,
                    'latest_payslip' => $payslip
                ]);
            }
        }

        $totalEmployees = $payrollRows->map(function ($row) {
            return $row['user']->id;
        })->unique()->count();

        return view('hr.payroll.export_template', [
            'payrollRows'    => $payrollRows,
            'startMonth'     => $this->startMonth,
            'endMonth'       => $this->endMonth,
            'year'           => $this->year,
            'totalEmployees' => $totalEmployees
        ]);
    }
}
