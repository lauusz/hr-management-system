<?php

use App\Models\LeaveRequestDay;

it('renders daily treatments as compact selects with the existing payload contract', function () {
    $dailyTreatmentDays = collect([
        new LeaveRequestDay([
            'leave_date' => '2026-09-14',
            'treatment' => LeaveRequestDay::LEAVE_BALANCE,
            'deduction_amount' => 0.5,
        ]),
    ]);

    $html = view('hr.leave_requests._daily_treatments', [
        'dailyTreatmentDays' => $dailyTreatmentDays,
        'radioPrefix' => 'approve-daily',
    ])->render();

    expect($html)
        ->toContain('name="daily_treatments[2026-09-14]"')
        ->toContain('value="NONE"')
        ->toContain('value="MEAL_ALLOWANCE"')
        ->toContain('value="LEAVE_BALANCE_1"')
        ->toContain('value="LEAVE_BALANCE_0_5"')
        ->toContain('<select')
        ->not->toContain('type="radio"');
});

it('scopes a wider approve modal without changing other modals', function () {
    $html = file_get_contents(resource_path('views/hr/leave_requests/show.blade.php'));

    expect($html)
        ->toContain('#modal-approve .modal-card')
        ->toContain('max-width: 560px');
});
