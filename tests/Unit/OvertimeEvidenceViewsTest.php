<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

it('compiles the changed overtime Blade directives into valid PHP', function () {
    $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
    $compiler->withoutComponentTags();

    foreach ([
        'resources/views/overtime_requests/create.blade.php',
        'resources/views/overtime_requests/index.blade.php',
        'resources/views/overtime_requests/_attachments.blade.php',
        'resources/views/hr/overtime_requests/show.blade.php',
        'resources/views/supervisor/overtime_requests/show.blade.php',
    ] as $view) {
        expect(token_get_all($compiler->compileString(file_get_contents($view)), TOKEN_PARSE))->not->toBeEmpty();
    }
});
