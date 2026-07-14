<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Support\Carbon;

it('shows employee tenure on dashboard', function () {
    Carbon::setTestNow('2026-06-29');

    $user = User::factory()->create();
    EmployeeProfile::create([
        'user_id' => $user->id,
        'tgl_bergabung' => '2024-03-10',
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Masa Kerja')
        ->assertSee('2 Tahun 3 Bulan');
});

it('shows division in the hero badge instead of role', function () {
    $division = Division::create(['name' => 'Operasional']);
    $user = User::factory()->create(['division_id' => $division->id]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    expect($response->getContent())->toMatch('/<span class="hero-role-badge">\s*Operasional\s*<\/span>/');
});

it('does not show the topbar user chip', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    expect($response->getContent())->not->toContain('class="user-chip"');
});

it('shows logged in user info in sidebar footer above logout button', function () {
    $user = User::factory()->create(['name' => 'NIKOLAUS SATRIA']);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    expect($response->getContent())->toMatch('/<div class="sidebar-user">.*NIKOLAUS SATRIA.*Karyawan.*<button class="btn-logout"/s');
});

it('shows company assets received by the logged in user only', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = AssetCategory::create(['name' => 'Laptop']);

    Asset::create([
        'asset_code' => 'IT-LPT-001',
        'asset_category_id' => $category->id,
        'name' => 'Lenovo ThinkPad E14',
        'brand' => 'Lenovo',
        'model' => 'ThinkPad E14',
        'serial_number' => 'SN-USER-001',
        'email_laptop' => 'laptop.user@example.com',
        'condition_status' => 'GOOD',
        'asset_status' => Asset::STATUS_ASSIGNED,
        'current_user_id' => $user->id,
    ]);

    Asset::create([
        'asset_code' => 'IT-LPT-999',
        'asset_category_id' => $category->id,
        'name' => 'Asset Orang Lain',
        'condition_status' => 'GOOD',
        'asset_status' => Asset::STATUS_ASSIGNED,
        'current_user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Aset Perusahaan yang Diterima')
        ->assertSee('Lenovo ThinkPad E14')
        ->assertSee('IT-LPT-001')
        ->assertSee('laptop.user@example.com')
        ->assertDontSee('Asset Orang Lain');
});
