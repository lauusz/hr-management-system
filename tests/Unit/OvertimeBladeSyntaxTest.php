<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

it('compiles the changed overtime templates into valid PHP', function () {
    $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
    $compiler->withoutComponentTags();
    foreach ([
        'resources/views/hr/approvers/index.blade.php',
        'resources/views/hr/approvers/overtime.blade.php',
        'resources/views/layouts/app.blade.php',
        'resources/views/overtime_requests/index.blade.php',
        'resources/views/overtime_requests/_approval_status.blade.php',
        'resources/views/overtime_requests/_edit_form.blade.php',
        'resources/views/overtime_approvals/index.blade.php',
        'resources/views/overtime_approvals/show.blade.php',
        'resources/views/hr/overtime_requests/master.blade.php',
        'resources/views/hr/overtime_requests/show.blade.php',
        'resources/views/hr/overtime_requests/_detail.blade.php',
        'resources/views/hr/overtime_requests/detail-modal.blade.php',
        'resources/views/supervisor/overtime_requests/show.blade.php',
        'resources/views/supervisor/overtime_requests/master.blade.php',
    ] as $file) {
        $compiled = $compiler->compileString(file_get_contents($file));
        expect(token_get_all($compiled, TOKEN_PARSE))->toBeArray();
    }
});
