<?php

use App\Models\Asset;
use App\Models\Pt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

it('allows HR to create and transfer employee assets', function () {
    Storage::fake('public');

    $hr = User::factory()->hrd()->create();
    $holder = User::factory()->create(['name' => 'Pemegang Awal']);
    $newHolder = User::factory()->create(['name' => 'Pemegang Baru']);
    $pt = Pt::create(['name' => 'PT Asset Test']);

    actingAs($hr)
        ->get(route('hr.assets.index'))
        ->assertOk()
        ->assertSee('Inventaris Karyawan')
        ->assertSee('Tambah Asset');

    actingAs($hr)
        ->post(route('hr.assets.store'), [
            'asset_code' => 'IT-LPT-0001',
            'category_name' => 'Laptop',
            'name' => 'Lenovo ThinkPad E14',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad E14',
            'serial_number' => 'SN-ASSET-001',
            'hostname' => 'LPT-HRD-001',
            'email_laptop' => 'laptop.awal@example.com',
            'photo' => UploadedFile::fake()->image('laptop.png', 2000, 1000),
            'handover_document' => UploadedFile::fake()->image('serah-terima.png', 2000, 1000),
            'condition_status' => 'GOOD',
            'current_user_id' => $holder->id,
            'current_pt_id' => $pt->id,
            'movement_date' => now()->toDateString(),
            'notes' => 'Serah terima awal',
        ])
        ->assertRedirect();

    $asset = Asset::where('asset_code', 'IT-LPT-0001')->firstOrFail();

    expect($asset->current_user_id)->toBe($holder->id)
        ->and($asset->asset_status)->toBe('ASSIGNED')
        ->and($asset->email_laptop)->toBe('laptop.awal@example.com')
        ->and($asset->photo_path)->not->toBeNull()
        ->and($asset->movements()->where('movement_type', 'ASSIGN')->exists())->toBeTrue();
    $initialMovement = $asset->movements()->where('movement_type', 'ASSIGN')->firstOrFail();
    expect($asset->photo_path)->toEndWith('.jpg')
        ->and($initialMovement->handover_document_path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($asset->photo_path);
    expect($initialMovement->handover_document_path)->not->toBeNull();
    Storage::disk('public')->assertExists($initialMovement->handover_document_path);

    actingAs($hr)
        ->get(route('hr.assets.edit', $asset))
        ->assertOk()
        ->assertSee('Edit Asset')
        ->assertSee('laptop.awal@example.com');

    actingAs($hr)
        ->put(route('hr.assets.update', $asset), [
            'asset_code' => 'IT-LPT-0001',
            'category_name' => 'Laptop',
            'name' => 'Lenovo ThinkPad E14 Gen 2',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad E14',
            'serial_number' => 'SN-ASSET-001',
            'hostname' => 'LPT-HRD-001',
            'email_laptop' => 'laptop.baru@example.com',
            'condition_status' => 'GOOD',
            'current_pt_id' => $pt->id,
            'purchase_date' => null,
            'notes' => 'Serah terima awal',
        ])
        ->assertRedirect(route('hr.assets.show', $asset));

    expect($asset->fresh())
        ->name->toBe('Lenovo ThinkPad E14 Gen 2')
        ->email_laptop->toBe('laptop.baru@example.com');

    actingAs($hr)
        ->post(route('hr.assets.movements.store', $asset), [
            'movement_type' => 'TRANSFER',
            'to_user_id' => $newHolder->id,
            'to_pt_id' => $pt->id,
            'condition_after' => 'GOOD',
            'movement_date' => now()->toDateString(),
            'notes' => 'Tukar laptop antar karyawan',
        ])
        ->assertRedirect(route('hr.assets.show', $asset));

    expect($asset->fresh()->current_user_id)->toBe($newHolder->id)
        ->and($asset->movements()->where('movement_type', 'TRANSFER')->exists())->toBeTrue();

    actingAs($hr)
        ->get(route('hr.assets.show', $asset))
        ->assertOk()
        ->assertSee('Lenovo ThinkPad E14 Gen 2')
        ->assertSee('laptop.baru@example.com')
        ->assertSee('Foto Asset')
        ->assertSee('Dokumen Serah Terima')
        ->assertSee('Pemegang Baru')
        ->assertSee('Tukar laptop antar karyawan');
});

it('accepts heic files for asset photo and handover document', function () {
    Storage::fake('public');

    $hr = User::factory()->hrd()->create();
    $holder = User::factory()->create();

    actingAs($hr)
        ->post(route('hr.assets.store'), [
            'asset_code' => 'IT-LPT-HEIC',
            'category_name' => 'Laptop',
            'name' => 'MacBook User',
            'photo' => UploadedFile::fake()->create('laptop.heic', 128, 'image/heic'),
            'handover_document' => UploadedFile::fake()->create('serah-terima.heic', 128, 'image/heic'),
            'condition_status' => 'GOOD',
            'current_user_id' => $holder->id,
            'movement_date' => now()->toDateString(),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $asset = Asset::where('asset_code', 'IT-LPT-HEIC')->firstOrFail();
    $movement = $asset->movements()->firstOrFail();

    Storage::disk('public')->assertExists($asset->photo_path);
    Storage::disk('public')->assertExists($movement->handover_document_path);
});
