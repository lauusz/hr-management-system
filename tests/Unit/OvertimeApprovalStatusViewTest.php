<?php

use App\Models\OvertimeRequest;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Concerns\ManagesLoops;

function renderOvertimeApprovalStatus($overtime, bool $compact): string
{
    $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
    $compiled = $compiler->compileString(file_get_contents('resources/views/overtime_requests/_approval_status.blade.php'));
    $__env = new class
    {
        use ManagesLoops;
    };
    ob_start();
    try {
        eval('?>'.$compiled);

        return ob_get_contents();
    } finally {
        ob_end_clean();
    }
}

function fakeOvertimeApproval($mapping = null, $actions = null)
{
    return new class($mapping, $actions)
    {
        public $user;
        public $status = OvertimeRequest::STATUS_PENDING_SUPERVISOR;
        public $approvalActions;

        public function __construct($mapping, $actions)
        {
            $this->user = (object) ['overtimeApproverAssignment' => $mapping];
            $this->approvalActions = $actions ?? collect();
        }

        public function currentApprovalActions()
        {
            return $this->approvalActions;
        }
    };
}

it('shows a dash for missing approvers in the compact recap', function () {
    $html = renderOvertimeApprovalStatus(fakeOvertimeApproval(), true);
    $text = preg_replace('/\s+/u', ' ', strip_tags($html));

    expect($text)->toContain('Disetujui: - — Menunggu', 'Mengetahui: -')
        ->not->toContain('Belum ditentukan', 'Menunggu')
        ->and($html)->toContain('font-size:11px');
});

it('keeps assigned approvers and recorded decisions in the compact recap', function () {
    $mapping = (object) ['supervisor' => (object) ['name' => 'Dinda'], 'manager' => (object) ['name' => 'Bima']];
    $actions = collect([(object) [
        'decision_slot' => 'SUPERVISOR', 'actor_name' => 'Dinda', 'actor_role' => 'Supervisor',
        'action' => 'APPROVE', 'acted_at' => null, 'approval_round' => 1, 'notes' => null,
    ]]);
    $text = preg_replace('/\s+/u', ' ', strip_tags(renderOvertimeApprovalStatus(fakeOvertimeApproval($mapping, $actions), true)));

    expect($text)->toContain('Disetujui: Dinda — Disetujui', 'Mengetahui: Bima', 'Riwayat keputusan');
});

it('keeps the existing detailed wording outside the compact recap', function () {
    $html = renderOvertimeApprovalStatus(fakeOvertimeApproval(), false);
    $text = preg_replace('/\s+/u', ' ', strip_tags($html));

    expect($text)->toContain('Disetujui: Belum ditentukan — Menunggu', 'Mengetahui: Belum ditentukan')
        ->and($html)->toContain('font-size:13px');
});
