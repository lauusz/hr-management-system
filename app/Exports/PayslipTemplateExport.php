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
        $totalEmployees = 0;

        if ($this->ptId) {
            $ptId = $this->ptId;
            $startMonth = $this->startMonth;
            $endMonth = $this->endMonth;
            $year = $this->year;

            $usersQuery = User::whereHas('profile', function ($query) use ($ptId) {
                $query->where('pt_id', $ptId);
            })
                ->with(['profile', 'position', 'division', 'payslips' => function ($q) use ($startMonth, $endMonth, $year) {
                    $q->whereBetween('period_month', [$startMonth, $endMonth])
                        ->where('period_year', $year);
                }]);

            if (!empty($this->search)) {
                $usersQuery->where('name', 'like', '%' . $this->search . '%');
            }

            $users = $usersQuery->get();

            $totalEmployees = $users->count();

            foreach ($users as $user) {
                for ($m = $startMonth; $m <= $endMonth; $m++) {
                    $payslip = $user->payslips->firstWhere('period_month', $m);

                    $payrollRows->push([
                        'user' => $user,
                        'month_number' => $m,
                        'latest_payslip' => $payslip
                    ]);
                }
            }
        }

        return view('hr.payroll.export_template', [
            'payrollRows'    => $payrollRows,
            'startMonth'     => $this->startMonth,
            'endMonth'       => $this->endMonth,
            'year'           => $this->year,
            'totalEmployees' => $totalEmployees
        ]);
    }
}
