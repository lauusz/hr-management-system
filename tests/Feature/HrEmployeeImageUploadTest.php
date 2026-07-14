<?php

use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

it('compresses employee KTP and family card image uploads', function () {
    Storage::fake('public');

    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    actingAs($hrd, 'web')
        ->post(route('hr.employees.store'), [
            'name' => 'Karyawan Dokumen',
            'role' => UserRole::EMPLOYEE->value,
            'path_kartu_keluarga' => UploadedFile::fake()->image('kk.png', 2000, 1000),
            'path_ktp' => UploadedFile::fake()->image('ktp.png', 2000, 1000),
        ])
        ->assertRedirect(route('hr.employees.index'));

    $employee = User::where('name', 'Karyawan Dokumen')->firstOrFail();
    $profile = EmployeeProfile::where('user_id', $employee->id)->firstOrFail();

    expect($profile->path_kartu_keluarga)->toEndWith('.jpg')
        ->and($profile->path_ktp)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($profile->path_kartu_keluarga);
    Storage::disk('public')->assertExists($profile->path_ktp);
});
