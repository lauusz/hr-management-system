<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

it('compiles the supervisor leave master view into valid PHP', function () {
    $compiler = new BladeCompiler(new Filesystem, sys_get_temp_dir());
    $compiler->withoutComponentTags();

    $compiled = $compiler->compileString(file_get_contents('resources/views/supervisor/leave_requests/master.blade.php'));

    expect(token_get_all($compiled, TOKEN_PARSE))->toBeArray();
});
