<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Attendance;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Support\Carbon;

it('uses environment-aware PWA asset URLs', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.asset('manifest.json').'"', false)
        ->assertSee('href="'.asset('images/icons/icon-192x192.png').'"', false)
        ->assertSee("navigator.serviceWorker.register('".asset('sw.js')."')", false);
});

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

it('marks the tenure card to span the full mobile summary row', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " summary-item--mobile-wide ")][.//div[normalize-space()="Masa Kerja"]]')->length)->toBe(1);
});

it('groups Clock In and Clock Out in the attendance section before quick actions', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);

    $attendanceSection = $xpath->query('//section[.//h2[normalize-space()="ABSEN"]]');
    $quickActionSection = $xpath->query('//section[.//h2[normalize-space()="Aksi Cepat"]]');

    expect($attendanceSection->length)->toBe(1)
        ->and($quickActionSection->length)->toBe(1)
        ->and(strpos($response->getContent(), 'ABSEN'))->toBeLessThan(strpos($response->getContent(), 'Aksi Cepat'))
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(@href, "/attendance/clock-in")]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(@href, "/attendance/clock-out")]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="Aksi Cepat"]]//span[normalize-space()="Clock In" or normalize-space()="Clock Out"]')->length)->toBe(0);
});

it('uses prominent attendance action cards without changing their copy', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);

    $attendanceCards = $xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(concat(" ", normalize-space(@class), " "), " attendance-action-card ")]');

    expect($attendanceCards->length)->toBe(2)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Clock In"]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Masuk kerja"]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Clock Out"]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Pulang kerja"]')->length)->toBe(1);
});

it('shows Clock In as completed while Clock Out remains actionable', function () {
    $user = User::factory()->create();
    $clockInAt = now()->startOfDay()->setTime(8, 3);
    Attendance::factory()->forUser($user)->today()->create([
        'clock_in_at' => $clockInAt,
        'completion_status' => Attendance::COMPLETION_OPEN,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('attendance-action-card--completed', false)
        ->assertSee('Berhasil · 08:03')
        ->assertSee(route('attendance.clockOut.form'), false);
});

it('shows both attendance cards as completed after Clock In and Clock Out', function () {
    $user = User::factory()->create();
    $clockInAt = now()->startOfDay()->setTime(8, 3);
    $clockOutAt = now()->startOfDay()->setTime(17, 8);
    Attendance::factory()->forUser($user)->today()->create([
        'clock_in_at' => $clockInAt,
        'clock_out_at' => $clockOutAt,
        'completion_status' => Attendance::COMPLETION_CLOSED,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Berhasil · 08:03')
        ->assertSee('Berhasil · 17:08')
        ->assertDontSee(route('attendance.clockIn.form'), false)
        ->assertDontSee(route('attendance.clockOut.form'), false);
});

it('shows Clock In as not recorded after a direct Clock Out', function () {
    $user = User::factory()->create();
    $clockOutAt = now()->startOfDay()->setTime(17, 8);
    Attendance::factory()->forUser($user)->today()->create([
        'clock_out_at' => $clockOutAt,
        'completion_status' => Attendance::COMPLETION_MISSED_CLOCK_IN,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Tidak tercatat')
        ->assertSee('Berhasil · 17:08')
        ->assertDontSee(route('attendance.clockIn.form'), false);
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
