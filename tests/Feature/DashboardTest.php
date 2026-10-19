<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AttendanceEvent;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
});

it('uses environment-aware PWA asset URLs', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.asset('manifest.json').'"', false)
        ->assertSee('href="'.asset('images/icons/icon-192x192.png').'"', false)
        ->assertSee("navigator.serviceWorker.register('".asset('sw.js')."')", false);
});

it('renders browser-side sidebar position persistence for the signed-in user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee("const sidebarScrollStorageKey = 'sidebar-scroll-{$user->id}';", false)
        ->assertSee("sidebarMenu.addEventListener('scroll', persistSidebarScroll, { passive: true });", false)
        ->assertSee('sidebarMenu.scrollTop = savedScrollTop;', false);
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

it('uses text-only attendance action cards', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);

    $attendanceCards = $xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(concat(" ", normalize-space(@class), " "), " attendance-action-card ")]');

    expect($attendanceCards->length)->toBe(2)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Clock In"]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//span[normalize-space()="Clock Out"]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//*[contains(concat(" ", normalize-space(@class), " "), " action-card__icon ")]')->length)->toBe(0)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//*[contains(concat(" ", normalize-space(@class), " "), " action-card__desc ")]')->length)->toBe(0);
});

it('uses calm Clock In and Clock Out colors on attendance cards', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(concat(" ", normalize-space(@class), " "), " attendance-action-card--clock-in ")]')->length)->toBe(1)
        ->and($xpath->query('//section[.//h2[normalize-space()="ABSEN"]]//a[contains(concat(" ", normalize-space(@class), " "), " attendance-action-card--clock-out ")]')->length)->toBe(1);
});

it('keeps attendance action colors above the generic action card style', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('.attendance-action-grid .attendance-action-card--clock-in', false)
        ->assertSee('.attendance-action-grid .attendance-action-card--clock-out', false);
});

it('shows the attendance cooldown modal after a recent Clock Out', function () {
    $user = User::factory()->create();
    $clockOutAt = now()->subMinutes(5)->second(0);
    AttendanceEvent::factory()->forUser($user)->clockOut()->create([
        'occurred_at' => $clockOutAt,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('data-attendance-cooldown', false)
        ->assertSee('attendance-cooldown-modal', false)
        ->assertSee('Anda sudah Clock Out pada pukul '.$clockOutAt->format('H:i').'.');
});

it('shows today attendance times only for recorded actions', function () {
    $user = User::factory()->create();
    $clockInAt = now()->startOfDay()->setTime(8, 3);
    $clockOutAt = now()->startOfDay()->setTime(17, 8);
    AttendanceEvent::factory()->forUser($user)->create([
        'event_type' => AttendanceEvent::CLOCK_IN,
        'occurred_at' => $clockInAt,
    ]);
    AttendanceEvent::factory()->forUser($user)->clockOut()->create([
        'occurred_at' => $clockOutAt,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Tercatat 08:03')
        ->assertSee('Tercatat 17:08');
});

it('keeps both clock actions text-only after Clock In', function () {
    $user = User::factory()->create();
    $clockInAt = now()->startOfDay()->setTime(8, 3);
    AttendanceEvent::factory()->forUser($user)->create([
        'event_type' => AttendanceEvent::CLOCK_IN,
        'occurred_at' => $clockInAt,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Clock In')
        ->assertSee('Clock Out')
        ->assertDontSee('Berhasil')
        ->assertSee(route('attendance.clockOut.form'), false);
});

it('keeps both clock actions text-only after Clock In and Clock Out', function () {
    $user = User::factory()->create();
    $clockInAt = now()->startOfDay()->setTime(8, 3);
    $clockOutAt = now()->startOfDay()->setTime(17, 8);
    AttendanceEvent::factory()->forUser($user)->create([
        'event_type' => AttendanceEvent::CLOCK_IN,
        'occurred_at' => $clockInAt,
    ]);
    AttendanceEvent::factory()->forUser($user)->clockOut()->create([
        'occurred_at' => $clockOutAt,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Clock In')
        ->assertSee('Clock Out')
        ->assertDontSee('Berhasil')
        ->assertSee(route('attendance.clockIn.form'), false)
        ->assertSee(route('attendance.clockOut.form'), false);
});

it('keeps both clock actions text-only after direct Clock Out', function () {
    $user = User::factory()->create();
    $clockOutAt = now()->startOfDay()->setTime(17, 8);
    AttendanceEvent::factory()->forUser($user)->clockOut()->create([
        'occurred_at' => $clockOutAt,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Clock In')
        ->assertSee('Clock Out')
        ->assertDontSee('Tidak tercatat')
        ->assertDontSee('Berhasil')
        ->assertSee(route('attendance.clockIn.form'), false);
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
