<?php

uses(Tests\TestCase::class);

use App\Services\Image\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

it('converts supported images to a resized jpeg', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('bukti.png', 2000, 1000);
    $path = app(ImageCompressor::class)->compressAndStore(
        $file,
        'photo',
        'leave_photos',
        'leave_'
    );

    expect($path)->toEndWith('.jpg');

    Storage::disk('public')->assertExists($path);

    $imageInfo = getimagesize(Storage::disk('public')->path($path));

    expect($imageInfo)->not->toBeFalse()
        ->and($imageInfo[0])->toBe(1280)
        ->and($imageInfo[1])->toBe(640)
        ->and($imageInfo['mime'])->toBe('image/jpeg');
});

it('keeps document files in their original format', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');
    $path = app(ImageCompressor::class)->compressAndStore(
        $file,
        'photo',
        'leave_photos',
        'leave_'
    );

    expect($path)->toEndWith('.pdf');

    Storage::disk('public')->assertExists($path);
});

it('rejects HEIC without a compatible server decoder', function () {
    Storage::fake('public');

    $compressor = new class extends ImageCompressor
    {
        protected function supportsHeicConversion(): bool
        {
            return false;
        }
    };

    $file = UploadedFile::fake()->create('bukti.heic', 100, 'image/heic');

    expect(fn () => $compressor->compressAndStore(
        $file,
        'photo',
        'leave_photos',
        'leave_'
    ))->toThrow(
        ValidationException::class,
        'File HEIC belum dapat diproses karena server belum memiliki Imagick dengan dukungan HEIC/HEIF.'
    );

    expect(Storage::disk('public')->allFiles('leave_photos'))->toBeEmpty();
});
