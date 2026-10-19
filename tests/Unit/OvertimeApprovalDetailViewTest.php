<?php

use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Concerns\ManagesLoops;

function renderOvertimeApprovalDetail($mapping, $actions, $status = 'PENDING_SUPERVISOR', $role = 'SUPERVISOR', $attachments = [], $oldNote = null): string
{
    $previousContainer = Container::getInstance();
    $container = new Container;
    Container::setInstance($container);

    $actor = new User;
    $actor->forceFill(['id' => 10, 'role' => $role]);
    $auth = Mockery::mock(Guard::class);
    $auth->shouldReceive('id')->andReturn(10);
    $auth->shouldReceive('user')->andReturn($actor);
    $container->instance(AuthFactory::class, $auth);
    $container->instance('url', new class {
        public function route($name, $parameters = [], $absolute = true) { return '/'.$name; }
    });
    $container->instance('session', new class {
        public function get($key, $default = null) { return $default; }
        public function token() { return 'test-csrf'; }
    });
    $container->instance('request', new class($oldNote) {
        public function __construct(private ?string $oldNote) {}
        public function old($key = null, $default = null) { return $key === 'rejection_note' ? ($this->oldNote ?? $default) : $default; }
    });

    $employee = new User;
    $employee->forceFill(['id' => 1, 'name' => 'Nama Karyawan', 'role' => 'EMPLOYEE']);
    $employee->setRelation('overtimeApproverAssignment', $mapping);
    $employee->setRelation('position', null);
    $employee->setRelation('division', null);
    $overtimeRequest = new OvertimeRequest;
    $overtimeRequest->setDateFormat('Y-m-d H:i:s');
    $overtimeRequest->setRawAttributes([
        'user_id' => 1, 'status' => $status, 'overtime_date' => '2026-10-14',
        'start_time' => '2026-10-14 17:00:00', 'end_time' => '2026-10-14 21:45:00',
        'duration_minutes' => 285, 'description' => 'Pekerjaan lembur', 'created_at' => '2026-10-15 13:05:00',
    ]);
    $overtimeRequest->setRelation('user', $employee);
    $overtimeRequest->setRelation('approvalActions', collect($actions));
    $overtimeRequest->setRelation('attachments', collect($attachments));
    $errors = new ViewErrorBag;
    $__env = new class {
        use ManagesLoops;
        use \Illuminate\View\Concerns\ManagesStacks;
        protected $renderCount = 0;
        public function make($view, $data = [], $mergeData = []) {
            return new class($view) {
                public function __construct(private string $view) {}
                public function render() { return '<span data-included="'.$this->view.'"></span>'; }
            };
        }
    };
    $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
    $compiler->withoutComponentTags();
    $compiled = $compiler->compileString(file_get_contents('resources/views/overtime_approvals/show.blade.php'));
    ob_start();
    try {
        eval('?>'.$compiled);
        return ob_get_contents();
    } finally {
        ob_end_clean();
        Mockery::close();
        Container::setInstance($previousContainer);
    }
}

function overtimeDetailXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    return new DOMXPath($document);
}

it('submits the required rejection reason and csrf token in the rejection modal form', function () {
    $mapping = (object) ['supervisor_id' => 10, 'manager_id' => 20];
    $xpath = overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, []));

    expect($xpath->query('//button[@data-modal-target="overtime-reject"]')->length)->toBe(1)
        ->and($xpath->query('//form[@method="POST" and @action="/overtime-approvals.reject"]//textarea[@name="rejection_note" and @required and @maxlength="255"]')->length)->toBe(1)
        ->and($xpath->query('//form[@action="/overtime-approvals.reject"]//input[@name="_token" and @value="test-csrf"]')->length)->toBe(1);
});

it('retains the previously entered rejection reason after validation redirects back', function () {
    $mapping = (object) ['supervisor_id' => 10, 'manager_id' => 20];
    $xpath = overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, [], oldNote: 'Alasan sebelumnya'));
    expect($xpath->query('//textarea[@name="rejection_note"]')->item(0)->textContent)->toBe('Alasan sebelumnya');
});

it('only offers decision controls to a pending assigned approver with both assignments complete', function () {
    $mapping = (object) ['supervisor_id' => 10, 'manager_id' => 20];
    $incomplete = (object) ['supervisor_id' => 10, 'manager_id' => null];
    $approved = [(object) ['approval_round' => 1, 'action' => 'APPROVE', 'decision_slot' => 'SUPERVISOR', 'actor_id' => 10]];

    foreach ([renderOvertimeApprovalDetail($incomplete, []), renderOvertimeApprovalDetail($mapping, $approved), renderOvertimeApprovalDetail($mapping, [], 'REJECTED')] as $html) {
        $xpath = overtimeDetailXPath($html);
        expect($xpath->query('//button[@data-modal-target="overtime-approve" or @data-modal-target="overtime-reject"]')->length)->toBe(0);
    }
    expect(overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, []))->query('//button[@data-modal-target="overtime-approve"]')->length)->toBe(1);
});

it('uses the protected evidence route for image previews and document links', function () {
    $mapping = (object) ['supervisor_id' => 10, 'manager_id' => 20];
    $files = [
        (object) ['mime_type' => 'image/jpeg', 'original_name' => 'foto.jpg'],
        (object) ['mime_type' => 'application/pdf', 'original_name' => 'dokumen.pdf'],
    ];
    $xpath = overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, [], attachments: $files));

    expect($xpath->query('//button[@data-image-viewer-src="/overtime-requests.attachments.show"]//img[@src="/overtime-requests.attachments.show"]')->length)->toBe(1)
        ->and($xpath->query('//a[@href="/overtime-requests.attachments.show" and @target="_blank"]')->length)->toBe(2);
});

it('keeps the manager edit form for another employee while the request is pending', function () {
    $mapping = (object) ['supervisor_id' => 20, 'manager_id' => 10];
    $pending = overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, [], role: 'MANAGER'));
    $rejected = overtimeDetailXPath(renderOvertimeApprovalDetail($mapping, [], 'REJECTED', 'MANAGER'));

    expect($pending->query('//span[@data-included="overtime_requests._edit_form"]')->length)->toBe(1)
        ->and($rejected->query('//span[@data-included="overtime_requests._edit_form"]')->length)->toBe(0);
});

it('compiles the detail template including its Blade components into valid PHP', function () {
    $previousContainer = Container::getInstance();
    $container = new Container;
    Container::setInstance($container);
    $app = Mockery::mock(\Illuminate\Contracts\Foundation\Application::class);
    $app->shouldReceive('getNamespace')->andReturn('App\\');
    $view = Mockery::mock(\Illuminate\Contracts\View\Factory::class);
    $view->shouldReceive('exists')->andReturn(true);
    $container->instance(\Illuminate\Contracts\Foundation\Application::class, $app);
    $container->instance(\Illuminate\Contracts\View\Factory::class, $view);
    try {
        $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
        $compiled = $compiler->compileString(file_get_contents('resources/views/overtime_approvals/show.blade.php'));
        expect(is_array(token_get_all($compiled, TOKEN_PARSE)))->toBe(true);
    } finally {
        Mockery::close();
        Container::setInstance($previousContainer);
    }
});
