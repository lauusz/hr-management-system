<?php

use App\Http\Controllers\OvertimeRequestController;
use App\Models\User;
use App\Services\OvertimeEvidenceStorage;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

it('validates optional evidence with the controller rules for count, type and size', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hrd-overtime-test-'.bin2hex(random_bytes(8));
    mkdir($root, 0700, true);
    $app = new Container;
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    try {
        $employee = new User;
        $employee->forceFill(['id' => 17, 'direct_supervisor_id' => 42]);
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->once()->andReturn($employee);
        $app->instance('auth', $auth);

        $rules = [];
        $messages = [];
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturnUsing(function ($givenRules, $givenMessages) use (&$rules, &$messages) {
            $rules = $givenRules;
            $messages = $givenMessages;
            throw new RuntimeException('validation rules captured');
        });
        try {
            (new OvertimeRequestController)->store($request, Mockery::mock(OvertimeEvidenceStorage::class));
        } catch (RuntimeException $exception) {
            expect($exception->getMessage())->toBe('validation rules captured');
        }

        $pdfPath = $root.'/bukti.pdf';
        $badPath = $root.'/bukti.txt';
        $bigPath = $root.'/besar.pdf';
        file_put_contents($pdfPath, "%PDF-1.4\nBukti\n%%EOF");
        file_put_contents($badPath, 'Bukan dokumen yang didukung');
        file_put_contents($bigPath, "%PDF-1.4\n".str_repeat('a', 8 * 1024 * 1024));
        $pdf = new UploadedFile($pdfPath, 'bukti.pdf', 'application/pdf', null, true);
        $bad = new UploadedFile($badPath, 'bukti.txt', 'text/plain', null, true);
        $big = new UploadedFile($bigPath, 'besar.pdf', 'application/pdf', null, true);
        $input = ['overtime_date' => '2026-10-17', 'start_time' => '18:00', 'end_time' => '20:00', 'description' => 'Pekerjaan'];
        $validator = new Factory(new Translator(new ArrayLoader, 'id'), $app);

        expect($validator->make($input, $rules, $messages)->passes())->toBeTrue()
            ->and($validator->make($input + ['photos' => [$pdf, $pdf, $pdf]], $rules, $messages)->passes())->toBeTrue()
            ->and($validator->make($input + ['photos' => [$pdf, $pdf, $pdf, $pdf]], $rules, $messages)->fails())->toBeTrue()
            ->and($validator->make($input + ['photos' => [$bad]], $rules, $messages)->fails())->toBeTrue()
            ->and($validator->make($input + ['photos' => [$big]], $rules, $messages)->fails())->toBeTrue();
    } finally {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        $resolved = realpath($root);
        if ($resolved && dirname($resolved) === realpath(sys_get_temp_dir()) && str_starts_with(basename($resolved), 'hrd-overtime-test-')) {
            (new Filesystem)->deleteDirectory($resolved);
        }
    }
});

it('removes earlier evidence when a later file fails before saving the request', function () {
    $app = new Container;
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    try {
        $employee = new User;
        $employee->forceFill(['id' => 17, 'direct_supervisor_id' => 42]);
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->once()->andReturn($employee);
        $app->instance('auth', $auth);

        $first = Mockery::mock(UploadedFile::class);
        $second = Mockery::mock(UploadedFile::class);
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn([
            'overtime_date' => '2026-10-17', 'start_time' => '18:00',
            'end_time' => '20:00', 'description' => 'Pekerjaan',
        ]);
        $request->shouldReceive('file')->once()->with('photos', [])->andReturn([$first, $second]);
        $validator = (new Factory(new Translator(new ArrayLoader, 'id'), $app))->make([], []);
        $validator->errors()->add('photo', 'Foto gagal diproses.');
        $validationError = new ValidationException($validator);
        $storage = Mockery::mock(OvertimeEvidenceStorage::class);
        $storage->shouldReceive('store')->once()->with($first)->andReturn(['file_path' => 'overtime_evidence/first.enc']);
        $storage->shouldReceive('store')->once()->with($second)->andThrow($validationError);
        $storage->shouldReceive('delete')->once()->with('overtime_evidence/first.enc');

        expect(fn () => (new OvertimeRequestController)->store($request, $storage))->toThrow(ValidationException::class);
    } finally {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }
});

it('removes stored evidence when saving the request fails', function () {
    $app = new Container;
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    try {
        $employee = new User;
        $employee->forceFill(['id' => 17, 'direct_supervisor_id' => 42]);
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->once()->andReturn($employee);
        $app->instance('auth', $auth);

        $file = Mockery::mock(UploadedFile::class);
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn([
            'overtime_date' => '2026-10-17', 'start_time' => '18:00',
            'end_time' => '20:00', 'description' => 'Pekerjaan',
        ]);
        $request->shouldReceive('file')->once()->with('photos', [])->andReturn([$file]);
        $storage = Mockery::mock(OvertimeEvidenceStorage::class);
        $storage->shouldReceive('store')->once()->with($file)->andReturn(['file_path' => 'overtime_evidence/file.enc']);
        $storage->shouldReceive('delete')->once()->with('overtime_evidence/file.enc');

        $failure = new RuntimeException('Gagal menyimpan data.');
        $database = Mockery::mock();
        $database->shouldReceive('transaction')->once()->andThrow($failure);
        $app->instance('db', $database);
        $handler = Mockery::mock(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $handler->shouldReceive('report')->once()->with($failure);
        $app->instance(\Illuminate\Contracts\Debug\ExceptionHandler::class, $handler);
        $response = Mockery::mock(\Illuminate\Http\RedirectResponse::class);
        $response->shouldReceive('withInput')->once()->andReturnSelf();
        $response->shouldReceive('with')->once()->with('error', 'Pengajuan atau bukti lembur gagal disimpan. Silakan coba lagi.')->andReturnSelf();
        $redirect = Mockery::mock();
        $redirect->shouldReceive('back')->once()->andReturn($response);
        $app->instance('redirect', $redirect);

        expect((new OvertimeRequestController)->store($request, $storage))->toBe($response);
    } finally {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }
});
