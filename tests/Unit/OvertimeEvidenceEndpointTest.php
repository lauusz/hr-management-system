<?php

use App\Enums\UserRole;
use App\Http\Controllers\OvertimeRequestController;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestAttachment;
use App\Models\User;
use App\Services\OvertimeEvidenceStorage;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('checks the attachment belongs to the request and denies outsiders before reading contents', function () {
    $app = new class extends Container
    {
        public function abort($code, $message = '', array $headers = []): void
        {
            throw new HttpException($code, $message, null, $headers);
        }
    };
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    try {
        $owner = new User;
        $owner->forceFill(['id' => 17, 'role' => UserRole::EMPLOYEE, 'direct_supervisor_id' => 42]);
        $owner->setRelation('overtimeApproverAssignment', null);
        $outsider = new User;
        $outsider->forceFill(['id' => 18, 'role' => UserRole::EMPLOYEE]);
        $request = new OvertimeRequest;
        $request->forceFill(['id' => 10, 'user_id' => 17]);
        $request->setRelation('user', $owner);
        $request->setRelation('approvalActions', collect());
        $attachment = new OvertimeRequestAttachment;
        $attachment->forceFill(['id' => 3, 'overtime_request_id' => 11, 'file_path' => 'overtime_evidence/file.enc', 'original_name' => 'bukti.pdf', 'mime_type' => 'application/pdf']);

        $auth = Mockery::mock();
        $auth->shouldReceive('user')->twice()->andReturn($outsider, $owner);
        $app->instance('auth', $auth);
        $disk = Mockery::mock();
        $disk->shouldReceive('exists')->once()->with('overtime_evidence/file.enc')->andReturnTrue();
        $filesystem = Mockery::mock();
        $filesystem->shouldReceive('disk')->once()->with('overtime_evidence')->andReturn($disk);
        $app->instance('filesystem', $filesystem);
        $factory = Mockery::mock(ResponseFactory::class);
        $factory->shouldReceive('make')->once()->andReturnUsing(fn ($body, $status, $headers) => new Response($body, $status, $headers));
        $app->instance(ResponseFactory::class, $factory);
        $storage = Mockery::mock(OvertimeEvidenceStorage::class);
        $storage->shouldReceive('read')->once()->with('overtime_evidence/file.enc')->andReturn('isi bukti');
        $controller = new OvertimeRequestController;

        try {
            $controller->attachment($request, $attachment, $storage);
            throw new RuntimeException('Akses ke lampiran pengajuan lain tidak ditolak.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(404);
        }

        $attachment->overtime_request_id = 10;
        try {
            $controller->attachment($request, $attachment, $storage);
            throw new RuntimeException('Akses pengguna lain tidak ditolak.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(403);
        }

        $response = $controller->attachment($request, $attachment, $storage);
        expect($response->getContent())->toBe('isi bukti')
            ->and($response->headers->get('Content-Type'))->toBe('application/pdf')
            ->and($response->headers->get('Cache-Control'))->toContain('no-store');
    } finally {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }
});
