<?php

use App\Services\Image\ImageCompressor;
use App\Services\OvertimeEvidenceStorage;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Storage;
use Psr\Log\NullLogger;

beforeEach(function () {
    $this->testRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'hrd-overtime-test-'.bin2hex(random_bytes(8));
    mkdir($this->testRoot, 0700, true);

    $app = new Container;
    $app->instance('config', new Repository(['filesystems' => ['disks' => [
        'local' => ['driver' => 'local', 'root' => $this->testRoot.'/private'],
        'public' => ['driver' => 'local', 'root' => $this->testRoot.'/existing-public'],
        'overtime_evidence' => ['driver' => 'local', 'root' => $this->testRoot.'/public'],
    ]]]));
    $filesystem = new FilesystemManager($app);
    $app->instance('filesystem', $filesystem);
    $app->instance(\Illuminate\Contracts\Filesystem\Factory::class, $filesystem);
    $app->instance('encrypter', new Encrypter(str_repeat('a', 32), 'AES-256-CBC'));
    $app->instance('log', new NullLogger);
    Container::setInstance($app);
    Facade::setFacadeApplication($app);
});

it('compresses photos to encrypted JPEG while keeping the existing default public disk', function () {
    $source = $this->testRoot.'/bukti.png';
    $image = imagecreatetruecolor(10, 10);
    imagepng($image, $source);
    imagedestroy($image);
    $file = new UploadedFile($source, 'bukti.png', 'image/png', null, true);
    $compressor = new ImageCompressor;

    $originalPath = $compressor->compressAndStore($file, 'photo', 'existing');
    expect(Storage::disk('public')->exists($originalPath))->toBeTrue();

    $service = new OvertimeEvidenceStorage($compressor);
    $stored = $service->store($file);
    expect($stored['mime_type'])->toBe('image/jpeg')
        ->and(substr($service->read($stored['file_path']), 0, 2))->toBe("\xff\xd8")
        ->and(Storage::disk('overtime_evidence')->get($stored['file_path']))->not->toStartWith("\xff\xd8")
        ->and(Storage::disk('local')->allFiles('overtime-evidence-tmp'))->toBeEmpty();
});

afterEach(function () {
    Facade::clearResolvedInstances();
    Facade::setFacadeApplication(null);
    Container::setInstance(null);
    $root = realpath($this->testRoot);
    if ($root && dirname($root) === realpath(sys_get_temp_dir()) && str_starts_with(basename($root), 'hrd-overtime-test-')) {
        (new Filesystem)->deleteDirectory($root);
    }
});

it('encrypts a document in public storage and removes the temporary copy', function () {
    expect(class_exists(OvertimeEvidenceStorage::class))->toBeTrue();

    $contents = "%PDF-1.4\nBukti lembur contoh\n%%EOF";
    $source = $this->testRoot.'/bukti.pdf';
    file_put_contents($source, $contents);
    $file = new UploadedFile($source, 'bukti.pdf', 'application/pdf', null, true);

    $service = new OvertimeEvidenceStorage(new ImageCompressor);
    $stored = $service->store($file);

    expect($stored['original_name'])->toBe('bukti.pdf')
        ->and($stored['mime_type'])->toBe('application/pdf')
        ->and(Storage::disk('overtime_evidence')->get($stored['file_path']))->not->toContain('Bukti lembur contoh')
        ->and($service->read($stored['file_path']))->toBe($contents)
        ->and(Storage::disk('local')->allFiles('overtime-evidence-tmp'))->toBeEmpty();

    $service->delete($stored['file_path']);
    Storage::disk('overtime_evidence')->assertMissing($stored['file_path']);
});
