<?php

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkNeedRequest;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use App\Models\Pt;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

it('shows the v2 access portal for authenticated users', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('v2.access'))
        ->assertOk()
        ->assertSee('HRD System')
        ->assertSee('Kebutuhan Kantor');
});

it('allows only users with ADMIN ATK access to open the admin dashboard', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    actingAs($user)
        ->get(route('v2.atk.admin.dashboard'))
        ->assertForbidden();

    actingAs($admin)
        ->get(route('v2.atk.admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Admin ATK');
});

it('shows admin atk dashboard operational summary', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create(['name' => 'User Dashboard ATK']);
    $pt = Pt::create(['name' => 'PT Dashboard']);
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $item = AtkItem::create([
        'name' => 'Spidol Boardmarker',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 0,
        'minimum_stock' => 2,
        'is_active' => true,
    ]);

    $approvedRequest = AtkRequest::create([
        'request_number' => 'ATK-DASH-APPROVED',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'pt_id' => $pt->id,
        'pt_name_snapshot' => $pt->name,
        'status' => AtkRequest::STATUS_APPROVED,
        'approved_at' => now(),
    ]);

    AtkRequestItem::create([
        'atk_request_id' => $approvedRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 3,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
        'status' => 'APPROVED',
    ]);

    AtkStockMovement::create([
        'atk_item_id' => $item->id,
        'movement_type' => 'IN',
        'qty' => 5,
        'stock_before' => 0,
        'stock_after' => 5,
        'source_type' => 'MANUAL',
        'notes' => 'Stok awal dashboard',
        'created_by' => $admin->id,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.dashboard'))
        ->assertOk()
        ->assertSee('Approved bulan ini')
        ->assertSee('Qty keluar bulan ini')
        ->assertSee('Stok habis')
        ->assertSee('Tindakan perlu diproses')
        ->assertSee('Trend 6 bulan')
        ->assertSee('Top PT bulan ini')
        ->assertSee('PT Dashboard')
        ->assertSee('Aktivitas stok terbaru')
        ->assertSee('Stok awal dashboard')
        ->assertSee('Data master perlu dilengkapi')
        ->assertSee('Barang paling banyak keluar')
        ->assertSee('Spidol Boardmarker')
        ->assertSee('Lihat report');
});

it('decreases stock when an ATK request is approved', function () {
    $pt = Pt::create(['name' => 'PT Test']);
    $requester = User::factory()->create(['name' => 'User ATK']);
    DB::table('employee_profiles')->insert([
        'user_id' => $requester->id,
        'pt_id' => $pt->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $item = AtkItem::create([
        'name' => 'Pulpen Biru',
        'unit_name' => 'box',
        'unit_size' => 20,
        'content_unit_name' => 'pcs',
        'stock_qty' => 5,
        'is_active' => true,
    ]);

    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-TEST-0001',
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'pt_id' => $pt->id,
        'pt_name_snapshot' => $pt->name,
        'status' => 'PENDING',
    ]);

    AtkRequestItem::create([
        'atk_request_id' => $atkRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 2,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.approve', $atkRequest))
        ->assertRedirect(route('v2.atk.admin.requests.show', $atkRequest));

    expect($item->fresh()->stock_qty)->toBe(3)
        ->and($atkRequest->fresh()->status)->toBe('APPROVED')
        ->and(DB::table('atk_stock_movements')->where('atk_item_id', $item->id)->value('movement_type'))->toBe('OUT');
});

it('auto rejects a request when stock is insufficient', function () {
    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $requester = User::factory()->create(['name' => 'User ATK']);
    $item = AtkItem::create([
        'name' => 'Staples',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 1,
        'is_active' => true,
    ]);

    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-TEST-0002',
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'status' => 'PENDING',
    ]);

    AtkRequestItem::create([
        'atk_request_id' => $atkRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 2,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.approve', $atkRequest))
        ->assertRedirect(route('v2.atk.admin.requests.show', $atkRequest))
        ->assertSessionHas('warning');

    expect($item->fresh()->stock_qty)->toBe(1)
        ->and($atkRequest->fresh()->status)->toBe('REJECTED')
        ->and($atkRequest->fresh()->admin_note)->toContain('stok')
        ->and(DB::table('atk_stock_movements')->where('atk_item_id', $item->id)->count())->toBe(0);
});

it('auto rejects other pending requests when approved stock is exhausted', function () {
    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $requester = User::factory()->create(['name' => 'User ATK']);
    $item = AtkItem::create([
        'name' => 'Map Plastik',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 2,
        'is_active' => true,
    ]);

    $approvedRequest = AtkRequest::create([
        'request_number' => 'ATK-TEST-0003',
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'status' => AtkRequest::STATUS_PENDING,
    ]);
    AtkRequestItem::create([
        'atk_request_id' => $approvedRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 2,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
    ]);

    $leftoverRequest = AtkRequest::create([
        'request_number' => 'ATK-TEST-0004',
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'status' => AtkRequest::STATUS_PENDING,
    ]);
    AtkRequestItem::create([
        'atk_request_id' => $leftoverRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 1,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.approve', $approvedRequest))
        ->assertRedirect(route('v2.atk.admin.requests.show', $approvedRequest));

    expect($item->fresh()->stock_qty)->toBe(0)
        ->and($approvedRequest->fresh()->status)->toBe(AtkRequest::STATUS_APPROVED)
        ->and($leftoverRequest->fresh()->status)->toBe(AtkRequest::STATUS_REJECTED)
        ->and($leftoverRequest->fresh()->admin_note)->toContain('stok');
});

it('allows admin atk to update item master data', function () {
    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $item = AtkItem::create([
        'name' => 'Pulpen Lama',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 2,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->put(route('v2.atk.admin.items.update', $item), [
            'name' => 'Pulpen Hitam',
            'unit_name' => 'box',
            'unit_size' => 12,
            'content_unit_name' => 'pcs',
            'minimum_stock' => 3,
            'min_request_qty' => 1,
            'is_active' => '1',
        ])
        ->assertRedirect(route('v2.atk.admin.items.index'));

    expect($item->fresh())
        ->name->toBe('Pulpen Hitam')
        ->unit_name->toBe('box')
        ->unit_size->toBe(12)
        ->stock_qty->toBe(10);
});

it('compresses admin atk item image uploads', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.items.store'), [
            'name' => 'Pulpen Gambar',
            'image' => UploadedFile::fake()->image('pulpen.png', 2000, 1000),
            'unit_name' => 'pcs',
            'unit_size' => 1,
            'content_unit_name' => 'pcs',
            'stock_qty' => 5,
        ])
        ->assertRedirect(route('v2.atk.admin.items.index'));

    $item = AtkItem::where('name', 'Pulpen Gambar')->firstOrFail();

    expect($item->image_path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($item->image_path);
});

it('allows admin atk to reject need requests', function () {
    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $user = User::factory()->create(['name' => 'Peminta ATK']);
    $needRequest = AtkNeedRequest::create([
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'requested_item_name' => 'Map Ordner',
        'qty' => 2,
        'unit_name' => 'pcs',
        'reason' => 'Dokumen baru',
        'status' => 'PENDING',
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.need-requests.process', $needRequest), [
            'status' => 'REJECTED',
            'admin_note' => 'Tidak masuk daftar belanja.',
        ])
        ->assertRedirect(route('v2.atk.admin.need-requests.index'));

    expect($needRequest->fresh())
        ->status->toBe('REJECTED')
        ->processed_by->toBe($admin->id)
        ->processed_at->not->toBeNull()
        ->admin_note->toBe('Tidak masuk daftar belanja.');
});

it('renders responsive admin need request cards on mobile', function () {
    $admin = createAtkAdmin();
    $user = User::factory()->create(['name' => 'Pemohon Restock Mobile']);

    AtkNeedRequest::create([
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'pt_name_snapshot' => 'TRIGUNA',
        'requested_item_name' => 'Spidol Whiteboard',
        'qty' => 3,
        'unit_name' => 'pcs',
        'reason' => 'Persediaan ruang rapat habis.',
        'status' => 'PENDING',
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.need-requests.index'))
        ->assertOk()
        ->assertSee('atk-admin-need-mobile-table')
        ->assertSee('atk-admin-need-card')
        ->assertSee('data-label="Pemohon"', false)
        ->assertSee('data-label="Jumlah"', false)
        ->assertSee('atk-admin-need-actions')
        ->assertSee('Pemohon Restock Mobile')
        ->assertSee('Spidol Whiteboard')
        ->assertSee('3 pcs')
        ->assertSee('Tolak')
        ->assertSee('Tandai Selesai')
        ->assertDontSee('Jumlah aktual masuk')
        ->assertDontSee('Harga satuan (opsional)');
});

it('defaults optional item numeric fields when admin leaves them empty', function () {
    $admin = User::factory()->create();
    UserAccessRole::create([
        'user_id' => $admin->id,
        'role' => 'ADMIN ATK',
    ]);

    $item = AtkItem::create([
        'name' => 'Kertas A4',
        'unit_name' => 'rim',
        'unit_size' => 500,
        'content_unit_name' => 'lembar',
        'stock_qty' => 4,
        'minimum_stock' => 2,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->put(route('v2.atk.admin.items.update', $item), [
            'name' => 'Kertas A4',
            'unit_name' => 'rim',
            'unit_size' => 500,
            'content_unit_name' => 'lembar',
            'minimum_stock' => '',
            'min_request_qty' => '',
            'is_active' => '1',
        ])
        ->assertRedirect(route('v2.atk.admin.items.index'));

    expect($item->fresh())
        ->minimum_stock->toBe(0)
        ->min_request_qty->toBe(1);
});

it('filters admin requests by status', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    $user = User::factory()->create();

    AtkRequest::create([
        'request_number' => 'ATK-FILTER-1',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Request Pending Filter',
        'status' => 'PENDING',
    ]);
    AtkRequest::create([
        'request_number' => 'ATK-FILTER-2',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Request Approved Filter',
        'status' => 'APPROVED',
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.requests.index', ['status' => 'PENDING']))
        ->assertOk()
        ->assertSee('Request Pending Filter')
        ->assertDontSee('Request Approved Filter');
});

it('shows submission dates and orders admin requests with pending first then newest first', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    $user = User::factory()->create();
    $baseTime = now()->startOfMinute();

    $olderProcessedRequest = AtkRequest::create([
        'request_number' => 'ATK-ORDER-APPROVED-OLD',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Approved Lama',
        'status' => AtkRequest::STATUS_APPROVED,
    ]);

    $newerProcessedRequest = AtkRequest::create([
        'request_number' => 'ATK-ORDER-APPROVED-NEW',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Approved Baru',
        'status' => AtkRequest::STATUS_APPROVED,
    ]);

    $newerPendingRequest = AtkRequest::create([
        'request_number' => 'ATK-ORDER-PENDING-NEW',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Pending Baru',
        'status' => AtkRequest::STATUS_PENDING,
    ]);

    $olderPendingRequest = AtkRequest::create([
        'request_number' => 'ATK-ORDER-PENDING-OLD',
        'user_id' => $user->id,
        'user_name_snapshot' => 'Pending Lama',
        'status' => AtkRequest::STATUS_PENDING,
    ]);

    DB::table('atk_requests')->where('id', $olderProcessedRequest->id)->update([
        'created_at' => $baseTime->copy()->subDays(3),
        'updated_at' => $baseTime->copy()->subDays(3),
    ]);
    DB::table('atk_requests')->where('id', $newerProcessedRequest->id)->update([
        'created_at' => $baseTime->copy()->subHours(12),
        'updated_at' => $baseTime->copy()->subHours(12),
    ]);
    DB::table('atk_requests')->where('id', $olderPendingRequest->id)->update([
        'created_at' => $baseTime->copy()->subDays(2),
        'updated_at' => $baseTime->copy()->subDays(2),
    ]);
    DB::table('atk_requests')->where('id', $newerPendingRequest->id)->update([
        'created_at' => $baseTime->copy()->subDay(),
        'updated_at' => $baseTime->copy()->subDay(),
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.requests.index'))
        ->assertOk()
        ->assertSee('<th>Tgl Pengajuan</th>', false)
        ->assertDontSee('<th>No</th>', false)
        ->assertSee($baseTime->copy()->subDay()->format('d/m/Y H:i'))
        ->assertSeeInOrder([
            'Pending Baru',
            'Pending Lama',
            'Approved Baru',
            'Approved Lama',
        ]);
});

it('renders responsive admin request cards on mobile', function () {
    $admin = createAtkAdmin();
    $user = User::factory()->create(['name' => 'Pemohon Mobile Admin']);
    $submittedAt = now()->startOfMinute();
    [$atkRequest] = createAtkRequestWithItems($user, [
        ['name' => 'Pulpen Mobile', 'stock_qty' => 10, 'qty' => 2],
        ['name' => 'Kertas Mobile', 'stock_qty' => 10, 'qty' => 1],
    ]);

    DB::table('atk_requests')->where('id', $atkRequest->id)->update([
        'created_at' => $submittedAt,
        'updated_at' => $submittedAt,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.requests.index'))
        ->assertOk()
        ->assertSee('atk-admin-request-mobile-list')
        ->assertSee('atk-admin-request-desktop-table')
        ->assertSee('Pemohon Mobile Admin')
        ->assertSee($atkRequest->pt_name_snapshot)
        ->assertSee('2 Item')
        ->assertSee($submittedAt->format('d/m/Y H:i'))
        ->assertSee('Review Admin')
        ->assertSee(route('v2.atk.admin.requests.show', $atkRequest));
});

it('shows the manual ATK request form only to ATK admins', function () {
    $admin = createAtkAdmin();
    $user = User::factory()->create(['name' => 'User Pengambilan Manual']);
    AtkItem::create([
        'name' => 'Pulpen Input Manual',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    actingAs($user)
        ->get('/v2/atk/admin/requests/manual/create')
        ->assertForbidden();

    actingAs($admin)
        ->get('/v2/atk/admin/requests/manual/create')
        ->assertOk()
        ->assertSee('Input Pengambilan Manual')
        ->assertSee('User Pengambilan Manual')
        ->assertSee('Pulpen Input Manual');
});

it('creates a pending manual request that can be finalized as partial', function () {
    $admin = createAtkAdmin();
    $pt = Pt::create(['name' => 'PT Input Manual']);
    $user = User::factory()->create(['name' => 'Pengambil Manual']);
    DB::table('employee_profiles')->insert([
        'user_id' => $user->id,
        'pt_id' => $pt->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $approvedItem = AtkItem::create([
        'name' => 'Pulpen Manual Approved',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);
    $rejectedItem = AtkItem::create([
        'name' => 'Buku Manual Ditolak',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    $response = actingAs($admin)->post('/v2/atk/admin/requests/manual', [
        'user_id' => $user->id,
        'notes' => 'Dicatat manual oleh admin.',
        'quantities' => [
            $approvedItem->id => 2,
            $rejectedItem->id => 1,
        ],
    ]);

    $atkRequest = AtkRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
    $response->assertRedirect(route('v2.atk.admin.requests.show', $atkRequest));

    expect($atkRequest)
        ->status->toBe(AtkRequest::STATUS_PENDING)
        ->user_name_snapshot->toBe('Pengambil Manual')
        ->pt_id->toBe($pt->id)
        ->pt_name_snapshot->toBe('PT Input Manual')
        ->notes->toBe('Dicatat manual oleh admin.')
        ->and($atkRequest->items)->toHaveCount(2);

    $approvedRequestItem = $atkRequest->items->firstWhere('atk_item_id', $approvedItem->id);
    $rejectedRequestItem = $atkRequest->items->firstWhere('atk_item_id', $rejectedItem->id);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $approvedRequestItem]), [
        'status' => AtkRequestItem::STATUS_APPROVED,
    ])->assertRedirect();
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $rejectedRequestItem]), [
        'status' => AtkRequestItem::STATUS_REJECTED,
        'admin_note' => 'Tidak jadi diambil.',
    ])->assertRedirect();
    actingAs($admin)->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($atkRequest->fresh()->status)->toBe(AtkRequest::STATUS_PARTIAL)
        ->and($approvedItem->fresh()->stock_qty)->toBe(8)
        ->and($rejectedItem->fresh()->stock_qty)->toBe(10)
        ->and(AtkStockMovement::where('source_id', $atkRequest->id)->count())->toBe(1);
});

it('renders a responsive admin review detail with mobile item cards', function () {
    $admin = createAtkAdmin();
    $user = User::factory()->create(['name' => 'Pemohon Detail Mobile']);
    [$atkRequest] = createAtkRequestWithItems($user, [
        ['name' => 'Binder Detail Mobile', 'stock_qty' => 10, 'qty' => 2],
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.requests.show', $atkRequest))
        ->assertOk()
        ->assertSee('atk-admin-review-header')
        ->assertSee('atk-admin-review-table')
        ->assertSee('atk-admin-review-item')
        ->assertSee('data-label="Jumlah"', false)
        ->assertSee('data-label="Stok Saat Ini"', false)
        ->assertSee('atk-admin-final-actions')
        ->assertSee('Binder Detail Mobile')
        ->assertSee('Setujui')
        ->assertSee('Tidak Diproses')
        ->assertSee('Selesaikan Review');
});

it('filters admin items by out of stock status', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    AtkItem::create([
        'name' => 'Barang Habis',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 0,
        'is_active' => true,
    ]);
    AtkItem::create([
        'name' => 'Barang Ada',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.items.index', ['stock' => 'out']))
        ->assertOk()
        ->assertSee('Barang Habis')
        ->assertDontSee('Barang Ada');
});

it('renders responsive admin item cards on mobile', function () {
    $admin = createAtkAdmin();
    $item = AtkItem::create([
        'name' => 'Kertas Memo Mobile',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 8,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.items.index'))
        ->assertOk()
        ->assertSee('atk-admin-items-mobile-table')
        ->assertSee('atk-admin-item-card')
        ->assertSee('data-label="Stok"', false)
        ->assertSee('data-label="Satuan"', false)
        ->assertSee('atk-admin-item-stock-form')
        ->assertSee('Kertas Memo Mobile')
        ->assertSee('Tambah Stok')
        ->assertSee(route('v2.atk.admin.items.edit', $item));
});

it('filters usage report by pt', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    $user = User::factory()->create();
    $ptA = Pt::create(['name' => 'PT A']);
    $ptB = Pt::create(['name' => 'PT B']);
    $item = AtkItem::create([
        'name' => 'Spidol',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    foreach ([[$ptA, 'ATK-REPORT-1'], [$ptB, 'ATK-REPORT-2']] as [$pt, $number]) {
        $atkRequest = AtkRequest::create([
            'request_number' => $number,
            'user_id' => $user->id,
            'user_name_snapshot' => $user->name,
            'pt_id' => $pt->id,
            'pt_name_snapshot' => $pt->name,
            'status' => 'APPROVED',
            'approved_at' => now(),
        ]);
        AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'atk_item_id' => $item->id,
            'qty' => 1,
            'item_name_snapshot' => $item->name,
            'unit_name_snapshot' => $item->unit_name,
            'unit_size_snapshot' => $item->unit_size,
            'content_unit_name_snapshot' => $item->content_unit_name,
            'status' => 'APPROVED',
        ]);
    }

    $oldRequest = AtkRequest::create([
        'request_number' => 'ATK-REPORT-OLD',
        'user_id' => $user->id,
        'user_name_snapshot' => 'User Bulan Lalu',
        'pt_id' => $ptA->id,
        'pt_name_snapshot' => $ptA->name,
        'status' => 'APPROVED',
        'approved_at' => now()->subMonth(),
    ]);
    AtkRequestItem::create([
        'atk_request_id' => $oldRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 9,
        'item_name_snapshot' => 'Barang Bulan Lalu',
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
        'status' => 'APPROVED',
    ]);

    $pendingRequest = AtkRequest::create([
        'request_number' => 'ATK-REPORT-PENDING',
        'user_id' => $user->id,
        'user_name_snapshot' => 'User Pending',
        'pt_id' => $ptA->id,
        'pt_name_snapshot' => $ptA->name,
        'status' => 'PENDING',
    ]);
    AtkRequestItem::create([
        'atk_request_id' => $pendingRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 5,
        'item_name_snapshot' => 'Barang Pending',
        'unit_name_snapshot' => $item->unit_name,
        'unit_size_snapshot' => $item->unit_size,
        'content_unit_name_snapshot' => $item->content_unit_name,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.reports.index', ['pt_id' => $ptA->id]))
        ->assertOk()
        ->assertSeeInOrder([
            'Laporan Pemakaian ATK',
            'Ringkasan Eksekutif',
            'Rekap Penggunaan per PT',
            'Rekap Konsumsi per Barang',
            'Detail Transaksi',
        ])
        ->assertSee('Request Disetujui')
        ->assertSee('Pengambil Aktif')
        ->assertSee('PT Aktif')
        ->assertSee('Jenis Barang')
        ->assertSee('Jumlah Pengambil')
        ->assertSee('Jumlah Request')
        ->assertSee('PT Pengguna')
        ->assertSee('atk-report-summary-grid')
        ->assertSee('atk-report-section')
        ->assertSee($user->name)
        ->assertSee('PT A')
        ->assertSee('Spidol')
        ->assertDontSee('<td>PT B</td>', false)
        ->assertDontSee('Barang Bulan Lalu')
        ->assertDontSee('Barang Pending');
});

it('shows stock movement history', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    $item = AtkItem::create([
        'name' => 'Binder Clip',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 5,
        'is_active' => true,
    ]);

    AtkStockMovement::create([
        'atk_item_id' => $item->id,
        'movement_type' => 'IN',
        'qty' => 5,
        'stock_before' => 0,
        'stock_after' => 5,
        'source_type' => 'MANUAL',
        'notes' => 'Stok awal',
        'created_by' => $admin->id,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.stock-movements.index'))
        ->assertOk()
        ->assertSee('Binder Clip')
        ->assertSee('0 → 5');
});

it('renders responsive stock movement cards on mobile', function () {
    $admin = createAtkAdmin();
    $admin->update(['name' => 'Admin Stok Mobile']);
    $user = User::factory()->create(['name' => 'Pengambil ATK Mobile']);
    $item = AtkItem::create([
        'name' => 'Binder Riwayat Mobile',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 5,
        'is_active' => true,
    ]);
    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-STOCK-PT-MOBILE',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'pt_name_snapshot' => 'TRIGUNA',
        'status' => AtkRequest::STATUS_APPROVED,
    ]);

    AtkStockMovement::create([
        'atk_item_id' => $item->id,
        'movement_type' => 'OUT',
        'qty' => 5,
        'stock_before' => 10,
        'stock_after' => 5,
        'source_type' => AtkStockMovement::SOURCE_REQUEST,
        'source_id' => $atkRequest->id,
        'created_by' => $admin->id,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.stock-movements.index'))
        ->assertOk()
        ->assertSee('atk-stock-movements-mobile-table')
        ->assertSee('atk-stock-movement-card')
        ->assertSee('data-label="Perubahan Stok"', false)
        ->assertSee('data-label="PT"', false)
        ->assertSee('data-label="Nama Pengambil"', false)
        ->assertSee('data-label="Diproses Oleh"', false)
        ->assertSee('Binder Riwayat Mobile')
        ->assertSee('Keluar')
        ->assertSee('5 box')
        ->assertSee('TRIGUNA')
        ->assertSee('Pengambil ATK Mobile')
        ->assertSee('Admin Stok Mobile')
        ->assertSee('10 → 5')
        ->assertDontSee('<th>Harga</th>', false)
        ->assertDontSee('<th>Total</th>', false)
        ->assertDontSee('<th>Sumber</th>', false)
        ->assertDontSee('<th>Catatan</th>', false);
});

it('records unit price when admin adds incoming stock', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    $item = AtkItem::create([
        'name' => 'Sticky Notes',
        'unit_name' => 'pack',
        'unit_size' => 1,
        'content_unit_name' => 'pack',
        'stock_qty' => 2,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.items.stock.store', $item), [
            'movement_type' => 'IN',
            'qty' => 3,
            'unit_price' => 12500,
        ])
        ->assertRedirect();

    $movement = AtkStockMovement::where('atk_item_id', $item->id)->firstOrFail();

    expect($item->fresh()->stock_qty)->toBe(5)
        ->and($movement->unit_price)->toBe(12500)
        ->and($movement->total_price)->toBe(37500);

    actingAs($admin)
        ->get(route('v2.atk.admin.items.index'))
        ->assertOk()
        ->assertSee('Harga/unit');

    actingAs($admin)
        ->get(route('v2.atk.admin.stock-movements.index'))
        ->assertOk()
        ->assertDontSee('Rp 12.500')
        ->assertDontSee('Rp 37.500');
});

it('allows admin to correct an item stock down to the actual quantity', function () {
    $admin = createAtkAdmin();
    $item = AtkItem::create([
        'name' => 'Barang Salah Input',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.items.edit', $item))
        ->assertOk()
        ->assertSee('Koreksi Stok Aktual')
        ->assertSee('name="movement_type" value="ADJUSTMENT"', false)
        ->assertSee('name="qty"', false)
        ->assertSee('min="0"', false);

    actingAs($admin)
        ->post(route('v2.atk.admin.items.stock.store', $item), [
            'movement_type' => AtkStockMovement::TYPE_ADJUSTMENT,
            'qty' => 0,
        ])
        ->assertRedirect();

    $movement = AtkStockMovement::where('atk_item_id', $item->id)->latest('id')->firstOrFail();

    expect($item->fresh()->stock_qty)->toBe(0)
        ->and($movement->movement_type)->toBe(AtkStockMovement::TYPE_ADJUSTMENT)
        ->and($movement->qty)->toBe(10)
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(0)
        ->and($movement->notes)->toBe('Koreksi stok manual');
});

it('renders an ATK success notification only once on the item edit page', function () {
    $admin = createAtkAdmin();
    $item = AtkItem::create([
        'name' => 'Barang Notifikasi Tunggal',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 2,
        'is_active' => true,
    ]);

    $response = actingAs($admin)
        ->withSession(['success' => 'Stok berhasil diperbarui.'])
        ->get(route('v2.atk.admin.items.edit', $item));

    $response->assertOk()->assertSee('Stok berhasil diperbarui.');

    expect(substr_count($response->getContent(), 'Stok berhasil diperbarui.'))->toBe(1);
});

it('allows admin atk to create and update categories', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    actingAs($admin)
        ->post(route('v2.atk.admin.categories.store'), ['name' => 'Peralatan Tulis'])
        ->assertRedirect(route('v2.atk.admin.categories.index'));

    $category = AtkCategory::where('name', 'Peralatan Tulis')->firstOrFail();

    actingAs($admin)
        ->put(route('v2.atk.admin.categories.update', $category), [
            'name' => 'Alat Tulis',
            'is_active' => '0',
        ])
        ->assertRedirect(route('v2.atk.admin.categories.index'));

    expect($category->fresh())
        ->name->toBe('Alat Tulis')
        ->is_active->toBeFalse();
});

it('uses the project pagination component on v2 atk pages', function () {
    $user = User::factory()->create();

    foreach (range(1, 21) as $number) {
        AtkItem::create([
            'name' => sprintf('Barang Pagination %02d', $number),
            'unit_name' => 'pcs',
            'unit_size' => 1,
            'content_unit_name' => 'pcs',
            'stock_qty' => 5,
            'is_active' => true,
        ]);
    }

    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('id="atkCatalogSearchForm"', false)
        ->assertSee('id="atkCatalogResults"', false)
        ->assertSee('data-async-search', false)
        ->assertSee('fetch(url.toString())', false)
        ->assertSee('atk-catalog-grid', false)
        ->assertSee('grid-template-columns: repeat(2, minmax(0, 1fr))', false)
        ->assertSee('.atk-catalog-cart-link', false)
        ->assertSee('display: none', false)
        ->assertSee('object-fit: contain', false)
        ->assertSee('Barang Pagination 20')
        ->assertDontSee('Barang Pagination 21')
        ->assertSee('hrd-pagination')
        ->assertSee('.atk-shell .hrd-page-btn--active', false)
        ->assertDontSee('w-5 h-5', false);
});

it('orders available catalog items before out of stock items', function () {
    $user = User::factory()->create();

    foreach ([
        ['name' => 'ATK Alpha Kosong', 'stock_qty' => 0],
        ['name' => 'ATK Beta Tersedia', 'stock_qty' => 5],
        ['name' => 'ATK Zulu Tersedia', 'stock_qty' => 2],
    ] as $item) {
        AtkItem::create($item + [
            'unit_name' => 'pcs',
            'unit_size' => 1,
            'content_unit_name' => 'pcs',
            'is_active' => true,
        ]);
    }

    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSeeInOrder([
            'ATK Beta Tersedia',
            'ATK Zulu Tersedia',
            'ATK Alpha Kosong',
        ]);
});

it('renders compact and responsive atk catalog cards on mobile', function () {
    $user = User::factory()->create();

    AtkItem::create([
        'name' => 'Binder Clip Tanpa Kategori',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 5,
        'image_path' => 'atk-items/missing.jpg',
        'is_active' => true,
    ]);

    AtkItem::create([
        'name' => 'Bolpoin Box Kosong',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 0,
        'is_active' => true,
    ]);

    AtkItem::create([
        'name' => 'Pensil Menipis',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 1,
        'minimum_stock' => 5,
        'is_active' => true,
    ]);

    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('atk-catalog-search', false)
        ->assertSee("display: flex;\n            min-width: 0;\n            flex-direction: column;", false)
        ->assertSee("gap: 8px;\n            padding: 8px;", false)
        ->assertSee('height: clamp(104px, 30vw, 120px)', false)
        ->assertSee('-webkit-line-clamp: 2', false)
        ->assertSee('min-height: 44px', false)
        ->assertSee('class="atk-product-placeholder" hidden', false)
        ->assertSee('this.nextElementSibling.hidden = false', false)
        ->assertSee('atk-stock-empty-badge', false)
        ->assertSee('Stok Kosong')
        ->assertDontSee('>Tersedia</span>', false)
        ->assertDontSee('Stok Menipis')
        ->assertDontSee('Stok Habis')
        ->assertDontSee('min-height: 34px', false)
        ->assertDontSee('Tanpa kategori')
        ->assertDontSee('1 pcs = 1 pcs')
        ->assertSee('1 box = 12 pcs')
        ->assertSee('Ajukan Restock')
        ->assertDontSee('<button class="atk-btn atk-btn-muted" disabled>Stok Habis</button>', false);
});

it('renders an accessible quantity stepper on the atk catalog', function () {
    $user = User::factory()->create();

    AtkItem::create([
        'name' => 'Kertas Catatan',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 5,
        'min_request_qty' => 2,
        'is_active' => true,
    ]);

    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('data-stepper', false)
        ->assertSee('data-stepper-decrease', false)
        ->assertSee('data-stepper-input', false)
        ->assertSee('data-stepper-increase', false)
        ->assertSee('inputmode="numeric"', false)
        ->assertSee('aria-label="Kurangi jumlah Kertas Catatan"', false)
        ->assertSee('aria-label="Tambah jumlah Kertas Catatan"', false)
        ->assertSee('grid-template-columns: 44px minmax(0, 1fr) 44px', false)
        ->assertDontSee('grid-template-columns: 144px minmax(0, 1fr)', false)
        ->assertSee('quantityInput.stepDown()', false)
        ->assertSee('quantityInput.stepUp()', false)
        ->assertSee('syncAllSteppers()', false);
});

it('renders the atk mobile sidebar drawer controls and menu icons', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['atk_cart' => [101 => 2, 202 => 1]])
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('id="atkBurger"', false)
        ->assertSee('id="atkSidebar"', false)
        ->assertSee('id="atkBackdrop"', false)
        ->assertSee('.atk-menu-icon', false)
        ->assertSee('class="atk-menu-icon" viewBox="0 0 24 24"', false)
        ->assertSee('aria-label="Buka keranjang, 3 item"', false)
        ->assertSee('<span class="atk-cart-badge" aria-hidden="true">3</span>', false);
});

it('allows admin atk to grant and revoke admin panel access', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create(['name' => 'Calon Admin ATK']);
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    actingAs($admin)
        ->post(route('v2.atk.admin.access.grant', $user))
        ->assertRedirect(route('v2.atk.admin.access.index'));

    expect($user->fresh()->canManageAtk())->toBeTrue();

    actingAs($admin)
        ->delete(route('v2.atk.admin.access.revoke', $user))
        ->assertRedirect(route('v2.atk.admin.access.index'));

    expect($user->fresh()->canManageAtk())->toBeFalse();
});

it('shows pt and async search controls on admin atk access page', function () {
    $admin = User::factory()->create();
    $pt = Pt::create(['name' => 'PT Akses Otomatis']);
    $user = User::factory()->create([
        'name' => 'User Akses PT',
        'username' => 'username-tidak-jadi-kolom',
    ]);
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);
    DB::table('employee_profiles')->insert([
        'user_id' => $user->id,
        'pt_id' => $pt->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($admin)
        ->get(route('v2.atk.admin.access.index', ['q' => 'Akses Otomatis']))
        ->assertOk()
        ->assertSee('<th>PT</th>', false)
        ->assertDontSee('<th>Username</th>', false)
        ->assertSee('PT Akses Otomatis')
        ->assertSee('id="atkAccessResults"', false)
        ->assertSee('data-async-search', false)
        ->assertSee('fetch(url.toString())', false)
        ->assertDontSee('form.submit();', false)
        ->assertDontSee('>Cari</button>', false);
});

it('marks a catalog need request done without changing stock', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $item = AtkItem::create([
        'name' => 'Pulpen Biru',
        'unit_name' => 'pcs',
        'stock_qty' => 3,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    $needRequest = AtkNeedRequest::create([
        'user_id' => User::factory()->create()->id,
        'user_name_snapshot' => 'User',
        'atk_item_id' => $item->id,
        'requested_item_name' => 'Pulpen Biru',
        'qty' => 5,
        'unit_name' => 'pcs',
        'reason' => 'Stok menipis',
        'status' => 'PENDING',
    ]);

    $movementBefore = AtkStockMovement::count();

    $response = actingAs($admin)
        ->followingRedirects()
        ->post(route('v2.atk.admin.need-requests.process', $needRequest), [
            'status' => 'DONE',
        ]);

    $response->assertOk()->assertSee('Pengajuan barang diselesaikan.');

    expect(substr_count($response->getContent(), 'Pengajuan barang diselesaikan.'))->toBe(1)
        ->and($item->fresh()->stock_qty)->toBe(3)
        ->and($needRequest->fresh())
        ->status->toBe('DONE')
        ->processed_by->toBe($admin->id)
        ->and(AtkStockMovement::count())->toBe($movementBefore);
});

it('marks a non-catalog need request done without creating item or stock movement', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $needRequest = AtkNeedRequest::create([
        'user_id' => User::factory()->create()->id,
        'user_name_snapshot' => 'User',
        // atk_item_id sengaja NULL (barang non-katalog)
        'requested_item_name' => 'Stabilo Pink',
        'qty' => 10,
        'unit_name' => 'pcs',
        'reason' => 'Belum ada di katalog',
        'status' => 'PENDING',
    ]);

    $itemBefore = AtkItem::count();
    $movementBefore = AtkStockMovement::count();

    actingAs($admin)
        ->post(route('v2.atk.admin.need-requests.process', $needRequest), [
            'status' => 'DONE',
        ])
        ->assertRedirect(route('v2.atk.admin.need-requests.index'));

    expect($needRequest->fresh())
        ->status->toBe('DONE')
        ->atk_item_id->toBeNull()
        ->and(AtkItem::count())->toBe($itemBefore)
        ->and(AtkStockMovement::count())->toBe($movementBefore);
});

it('prevents double-processing of an already completed need request', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $item = AtkItem::create([
        'name' => 'Kertas HVS',
        'unit_name' => 'rim',
        'stock_qty' => 1,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    $needRequest = AtkNeedRequest::create([
        'user_id' => User::factory()->create()->id,
        'user_name_snapshot' => 'User',
        'atk_item_id' => $item->id,
        'requested_item_name' => 'Kertas HVS',
        'qty' => 2,
        'unit_name' => 'rim',
        'reason' => 'Habis',
        'status' => 'DONE', // sudah diproses sebelumnya
        'processed_at' => now()->subDay(),
    ]);

    $movementBefore = AtkStockMovement::count();

    actingAs($admin)
        ->post(route('v2.atk.admin.need-requests.process', $needRequest), [
            'status' => 'DONE',
            'qty' => 2,
        ])
        ->assertRedirect(route('v2.atk.admin.need-requests.index'))
        ->assertSessionHas('warning');

    // Stok tidak berubah (tidak ada movement baru).
    expect($item->fresh()->stock_qty)->toBe(1)
        ->and(AtkStockMovement::count())->toBe($movementBefore);
});

it('generates a non-colliding request number when two carts are submitted in sequence', function () {
    $user = User::factory()->create();

    // Seed satu request bulan ini agar ada sequence existing.
    AtkRequest::create([
        'request_number' => 'ATK-'.now()->format('Ym').'-0001',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'status' => AtkRequest::STATUS_PENDING,
    ]);

    $item = AtkItem::create([
        'name' => 'Buku Tulis',
        'unit_name' => 'pcs',
        'stock_qty' => 5,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    // Submit cart pertama.
    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();
    actingAs($user)
        ->post(route('v2.atk.cart.submit'), ['notes' => 'Pengajuan A'])
        ->assertRedirect();

    // Submit cart kedua (harus dapat nomor sequence berbeda, bukan tabrakan).
    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();
    actingAs($user)
        ->post(route('v2.atk.cart.submit'), ['notes' => 'Pengajuan B'])
        ->assertRedirect();

    $numbers = AtkRequest::whereNotNull('notes')->pluck('request_number')->sort()->values();
    expect($numbers)->toHaveCount(2)
        ->and($numbers[0])->toEndWith('-0002')
        ->and($numbers[1])->toEndWith('-0003');
});

it('does not decrement stock twice when an already-approved request is approved again', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $item = AtkItem::create([
        'name' => 'Spidol',
        'unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-DBL-APPROVE-1',
        'user_id' => User::factory()->create()->id,
        'user_name_snapshot' => 'User',
        'status' => AtkRequest::STATUS_PENDING,
    ]);
    AtkRequestItem::create([
        'atk_request_id' => $atkRequest->id,
        'atk_item_id' => $item->id,
        'qty' => 3,
        'item_name_snapshot' => $item->name,
        'unit_name_snapshot' => $item->unit_name,
    ]);

    // Approve pertama: stok 10 → 7.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.approve', $atkRequest))
        ->assertRedirect();
    expect($item->fresh()->stock_qty)->toBe(7);

    // Approve kedua: harus ditolak (warning), stok tetap 7.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.approve', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('warning');

    expect($item->fresh()->stock_qty)->toBe(7)
        ->and(AtkStockMovement::where('atk_item_id', $item->id)->count())->toBe(1);
});

it('records an opening balance movement when an item is created with stock', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    actingAs($admin)
        ->post(route('v2.atk.admin.items.store'), [
            'name' => 'Klip Kertas Besar',
            'unit_name' => 'box',
            'unit_size' => 100,
            'content_unit_name' => 'pcs',
            'stock_qty' => 8,
            'minimum_stock' => 2,
            'min_request_qty' => 1,
        ])
        ->assertRedirect(route('v2.atk.admin.items.index'));

    $item = AtkItem::where('name', 'Klip Kertas Besar')->firstOrFail();
    expect($item->stock_qty)->toBe(8);

    $movement = AtkStockMovement::where('atk_item_id', $item->id)->first();
    expect($movement)
        ->movement_type->toBe('IN')
        ->qty->toBe(8)
        ->stock_before->toBe(0)
        ->stock_after->toBe(8)
        ->source_type->toBeNull();
});

// ============================================================
// Batch D — Fitur UX user-side
// ============================================================

it('shows a user their own need-request history only', function () {
    $user = User::factory()->create(['name' => 'User A']);
    $otherUser = User::factory()->create(['name' => 'User B']);

    $own = AtkNeedRequest::create([
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'requested_item_name' => 'Buku Saya',
        'qty' => 1,
        'unit_name' => 'pcs',
        'reason' => 'Milik A',
        'status' => 'PENDING',
    ]);
    AtkNeedRequest::create([
        'user_id' => $otherUser->id,
        'user_name_snapshot' => $otherUser->name,
        'requested_item_name' => 'Buku Orang Lain',
        'qty' => 1,
        'unit_name' => 'pcs',
        'reason' => 'Milik B',
        'status' => 'PENDING',
    ]);

    actingAs($user)
        ->get(route('v2.atk.need-requests.index'))
        ->assertOk()
        ->assertSee('Buku Saya')
        ->assertDontSee('Buku Orang Lain');
});

it('renders responsive need request cards on mobile', function () {
    $user = User::factory()->create();
    $submittedAt = now()->startOfMinute();

    AtkNeedRequest::create([
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'requested_item_name' => 'Tinta Printer Hitam',
        'qty' => 2,
        'unit_name' => 'botol',
        'reason' => 'Persediaan operasional habis.',
        'status' => 'REJECTED',
        'admin_note' => 'Gunakan tinta cadangan dahulu.',
        'created_at' => $submittedAt,
        'updated_at' => $submittedAt,
    ]);

    actingAs($user)
        ->get(route('v2.atk.need-requests.index'))
        ->assertOk()
        ->assertSee('atk-need-request-mobile-list')
        ->assertSee('atk-need-request-desktop-table')
        ->assertSee('Tinta Printer Hitam')
        ->assertSee('2 botol')
        ->assertSee('Persediaan operasional habis.')
        ->assertSee('Gunakan tinta cadangan dahulu.')
        ->assertSee($submittedAt->format('d/m/Y H:i'));
});

it('renders an editable quantity stepper on the need request form', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('v2.atk.need-requests.create'))
        ->assertOk()
        ->assertSee('data-need-qty-stepper', false)
        ->assertSee('data-need-qty-decrease', false)
        ->assertSee('data-need-qty-increase', false)
        ->assertSee('class="atk-need-qty-input" type="number"', false)
        ->assertSee('name="qty"', false)
        ->assertSee('min="1"', false)
        ->assertSee('inputmode="numeric"', false)
        ->assertDontSee('readonly', false);
});

it('downloads an excel file when admin exports the usage report', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    actingAs($admin)
        ->get(route('v2.atk.admin.reports.export', ['month' => now()->format('Y-m')]))
        ->assertDownload();
});

it('renders cart items as responsive cards on mobile', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Bolpoin Mobile',
        'unit_name' => 'box',
        'unit_size' => 12,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($user)
        ->withSession(['atk_cart' => [$item->id => 2]])
        ->get(route('v2.atk.cart.show'))
        ->assertOk()
        ->assertSee('Bolpoin Mobile')
        ->assertSee('atk-cart-row', false)
        ->assertSee('atk-cart-qty-form', false)
        ->assertSee('@media (max-width: 639px)', false)
        ->assertSee('grid-template-columns: minmax(0, 1fr) auto', false);
});

it('lets a user update item quantity in their cart without removing it', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Kapur Whiteboard',
        'unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    // Tambah ke cart (qty 2).
    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 2])
        ->assertRedirect();
    expect(session('atk_cart')[$item->id])->toBe(2);

    // Update qty jadi 5.
    actingAs($user)
        ->put(route('v2.atk.cart.update', $item), ['qty' => 5])
        ->assertRedirect();
    expect(session('atk_cart')[$item->id])->toBe(5);
});

it('rejects cart quantity update exceeding available stock', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Spidol Kecil',
        'unit_name' => 'pcs',
        'stock_qty' => 3,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();

    // Coba update ke qty 10 (di atas stok 3).
    actingAs($user)
        ->put(route('v2.atk.cart.update', $item), ['qty' => 10])
        ->assertRedirect()
        ->assertSessionHas('warning');

    // Cart tetap qty 1 (tidak berubah).
    expect(session('atk_cart')[$item->id])->toBe(1);
});

it('updates cart quantity via AJAX and returns JSON without reload', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Buku Tulis',
        'unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 2])
        ->assertRedirect();

    // Update via AJAX (expectsJson) — harus return JSON, bukan redirect.
    actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
        ->put(route('v2.atk.cart.update', $item), ['qty' => 5])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('qty', 5)
        ->assertJsonPath('cartCount', 5);

    expect(session('atk_cart')[$item->id])->toBe(5);
});

it('returns JSON warning without changing cart when AJAX update exceeds stock', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Stapler',
        'unit_name' => 'pcs',
        'stock_qty' => 3,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();

    // Coba update ke qty 10 via AJAX — harus return JSON success=false.
    actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
        ->put(route('v2.atk.cart.update', $item), ['qty' => 10])
        ->assertOk()
        ->assertJsonPath('success', false);

    // Cart tetap qty 1.
    expect(session('atk_cart')[$item->id])->toBe(1);
});

it('shows request notes on the user detail page', function () {
    $user = User::factory()->create();
    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-NOTES-1',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'status' => AtkRequest::STATUS_PENDING,
        'notes' => 'Tolong diprioritaskan untuk rapat besok',
    ]);

    actingAs($user)
        ->get(route('v2.atk.requests.show', $atkRequest))
        ->assertOk()
        ->assertSee('Catatan Pengaju')
        ->assertSee('Tolong diprioritaskan untuk rapat besok');
});

it('renders responsive request cards for a users request history', function () {
    $user = User::factory()->create();
    $submittedAt = now()->startOfMinute();

    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-MOBILE-HISTORY',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'pt_name_snapshot' => 'TRIGUNA',
        'status' => AtkRequest::STATUS_PENDING,
        'created_at' => $submittedAt,
        'updated_at' => $submittedAt,
    ]);

    actingAs($user)
        ->get(route('v2.atk.requests.index'))
        ->assertOk()
        ->assertSee('atk-request-mobile-list')
        ->assertSee('atk-request-desktop-table')
        ->assertSee('ATK-MOBILE-HISTORY')
        ->assertSee('TRIGUNA')
        ->assertSee($submittedAt->format('d/m/Y H:i'))
        ->assertSee(route('v2.atk.requests.show', $atkRequest));
});

it('renders a responsive request detail with mobile item cards', function () {
    $user = User::factory()->create();
    [$atkRequest, $items] = createAtkRequestWithItems($user, [
        [
            'name' => 'Kertas A4',
            'stock_qty' => 10,
            'qty' => 2,
            'unit_name' => 'rim',
            'unit_size' => 500,
            'content_unit_name' => 'lembar',
        ],
    ]);
    $items[0]['requestItem']->update([
        'status' => 'REJECTED',
        'admin_note' => 'Gunakan stok divisi dahulu.',
    ]);

    actingAs($user)
        ->get(route('v2.atk.requests.show', $atkRequest))
        ->assertOk()
        ->assertSee('atk-request-summary')
        ->assertSee('atk-request-item-mobile-list')
        ->assertSee('atk-request-item-desktop-table')
        ->assertSee('Kertas A4')
        ->assertSee('2 rim')
        ->assertSee('1000 lembar')
        ->assertSee('Gunakan stok divisi dahulu.');
});

it('filters a users own requests by status', function () {
    $user = User::factory()->create();

    AtkRequest::create([
        'request_number' => 'ATK-FILTER-PENDING',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'status' => AtkRequest::STATUS_PENDING,
    ]);
    AtkRequest::create([
        'request_number' => 'ATK-FILTER-APPROVED',
        'user_id' => $user->id,
        'user_name_snapshot' => $user->name,
        'status' => AtkRequest::STATUS_APPROVED,
    ]);

    // Filter status APPROVED — hanya approved yang muncul.
    actingAs($user)
        ->get(route('v2.atk.requests.index', ['status' => 'APPROVED']))
        ->assertOk()
        ->assertSee('ATK-FILTER-APPROVED')
        ->assertDontSee('ATK-FILTER-PENDING');
});

// ============================================================
// Konsistensi (G1-G3) — filter is_active + validasi reject/admin_note
// ============================================================

it('does not allow submitting a cart item that has been deactivated by admin', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Pulpen Diskon',
        'unit_name' => 'pcs',
        'stock_qty' => 5,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    // Tambah ke cart saat masih aktif.
    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();

    // Admin nonaktifkan item.
    $item->update(['is_active' => false]);

    // Submit harus ditolak (cart jadi kosong setelah filter is_active).
    actingAs($user)
        ->post(route('v2.atk.cart.submit'), ['notes' => 'Test'])
        ->assertRedirect()
        ->assertSessionHas('warning');

    expect(AtkRequest::count())->toBe(0);
});

it('requires admin note when an admin rejects an atk request', function () {
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-REJECT-NOTE',
        'user_id' => User::factory()->create()->id,
        'user_name_snapshot' => 'User',
        'status' => AtkRequest::STATUS_PENDING,
    ]);

    // Reject tanpa admin_note → harus gagal validation.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.reject', $atkRequest), [
            'admin_note' => '',
        ])
        ->assertSessionHasErrors(['admin_note']);

    // Status tetap PENDING.
    expect($atkRequest->fresh()->status)->toBe(AtkRequest::STATUS_PENDING);
});

it('limits cart request notes to 1000 characters', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Kertas Catatan',
        'unit_name' => 'pcs',
        'stock_qty' => 5,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 1])
        ->assertRedirect();

    // Notes 1001 karakter → validation error.
    actingAs($user)
        ->post(route('v2.atk.cart.submit'), ['notes' => str_repeat('x', 1001)])
        ->assertSessionHasErrors(['notes']);

    // Notes 1000 karakter → lolos.
    actingAs($user)
        ->post(route('v2.atk.cart.submit'), ['notes' => str_repeat('x', 1000)])
        ->assertRedirect();
});

it('shows an in-cart badge on the catalog when an item is already in the cart', function () {
    $user = User::factory()->create();
    $item = AtkItem::create([
        'name' => 'Stapler Hi-Tech',
        'unit_name' => 'pcs',
        'stock_qty' => 8,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ]);

    // Awal: item belum di cart, tidak ada badge "Di Keranjang".
    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('Stapler Hi-Tech')
        ->assertDontSee('Di Keranjang');

    // Tambah ke cart (qty 3).
    actingAs($user)
        ->post(route('v2.atk.cart.add'), ['atk_item_id' => $item->id, 'qty' => 3])
        ->assertRedirect();

    // Sekarang katalog harus menampilkan badge "Di Keranjang: 3".
    actingAs($user)
        ->get(route('v2.atk.catalog'))
        ->assertOk()
        ->assertSee('Di Keranjang: 3');
});

// =============================================================================
// Flow review per-item + finalize (PARTIAL / APPROVED / REJECTED)
// =============================================================================

function createAtkRequestWithItems(User $requester, array $itemsData): array
{
    $pt = Pt::factory()->create();
    $atkRequest = AtkRequest::create([
        'request_number' => 'ATK-REVIEW-'.uniqid(),
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'pt_id' => $pt->id,
        'pt_name_snapshot' => $pt->name,
        'status' => AtkRequest::STATUS_PENDING,
    ]);

    $items = [];
    foreach ($itemsData as $data) {
        $item = AtkItem::create(array_merge([
            'unit_name' => 'pcs',
            'unit_size' => 1,
            'content_unit_name' => 'pcs',
            'minimum_stock' => 0,
            'min_request_qty' => 1,
            'is_active' => true,
        ], $data));

        $requestItem = AtkRequestItem::create([
            'atk_request_id' => $atkRequest->id,
            'atk_item_id' => $item->id,
            'qty' => $data['qty'] ?? 1,
            'item_name_snapshot' => $item->name,
            'unit_name_snapshot' => $item->unit_name,
            'unit_size_snapshot' => $item->unit_size,
            'content_unit_name_snapshot' => $item->content_unit_name,
        ]);
        $items[] = ['item' => $item, 'requestItem' => $requestItem];
    }

    return [$atkRequest, $items];
}

function createAtkAdmin(): User
{
    $admin = User::factory()->create();
    UserAccessRole::create(['user_id' => $admin->id, 'role' => 'ADMIN ATK']);

    return $admin;
}

it('lets admin approve a single item without immediately reducing stock', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Pulpen A', 'stock_qty' => 10, 'qty' => 2],
    ]);

    $beforeStock = $items[0]['item']->stock_qty;

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'APPROVED',
        ])
        ->assertRedirect(route('v2.atk.admin.requests.show', $atkRequest));

    // Item ditandai APPROVED, tapi stok belum berkurang (finalisasi belum dilakukan).
    expect($items[0]['requestItem']->fresh()->status)->toBe('APPROVED')
        ->and($items[0]['item']->fresh()->stock_qty)->toBe($beforeStock)
        ->and($atkRequest->fresh()->status)->toBe('PENDING'); // header belum selesai
});

it('prevents admin from approving an item when current stock is already insufficient', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Bolpoin Kritis', 'stock_qty' => 0, 'qty' => 1],
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'APPROVED',
        ])
        ->assertSessionHas('warning');

    expect($items[0]['requestItem']->fresh()->status)->toBe('PENDING');
});

it('lets admin reject a single item with required note', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Buku Tulis', 'stock_qty' => 5, 'qty' => 1],
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'REJECTED',
            'admin_note' => 'Dipakai bersama lantai 2',
        ])
        ->assertRedirect(route('v2.atk.admin.requests.show', $atkRequest));

    expect($items[0]['requestItem']->fresh()->status)->toBe('REJECTED')
        ->and($items[0]['requestItem']->fresh()->admin_note)->toBe('Dipakai bersama lantai 2');
});

it('rejects review when note is missing for rejected item', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Spidol', 'stock_qty' => 5, 'qty' => 1],
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'REJECTED',
            'admin_note' => '',
        ])
        ->assertSessionHasErrors('admin_note');

    expect($items[0]['requestItem']->fresh()->status)->toBe('PENDING');
});

it('prevents finalize when items are still pending', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Item A', 'stock_qty' => 10, 'qty' => 1],
        ['name' => 'Item B', 'stock_qty' => 10, 'qty' => 1],
    ]);

    // Hanya review item pertama, item kedua masih PENDING.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'APPROVED',
        ])
        ->assertRedirect();

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('warning');

    expect($atkRequest->fresh()->status)->toBe('PENDING');
});

it('shows a warning and keeps finalize disabled when approved item stock becomes insufficient', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Staples Kritis', 'stock_qty' => 1, 'qty' => 1],
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
            'status' => 'APPROVED',
        ]);

    $items[0]['item']->update(['stock_qty' => 0]);

    actingAs($admin)
        ->get(route('v2.atk.admin.requests.show', $atkRequest))
        ->assertOk()
        ->assertSee('Perlu ditinjau ulang, stok saat ini tidak cukup.')
        ->assertSee('Ada 1 item yang sudah disetujui tetapi stok saat ini tidak cukup. Ubah review item atau tunggu restock.')
        ->assertSee('disabled', false);
});

it('finalizes as PARTIAL and only reduces stock for approved items', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Staples', 'stock_qty' => 10, 'qty' => 2],
        ['name' => 'Bolpoin Joyko', 'stock_qty' => 10, 'qty' => 1],
        ['name' => 'Isi Staples Kecil', 'stock_qty' => 10, 'qty' => 1],
    ]);

    // Approve 2 item, reject 1 item dengan note.
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[1]['requestItem']]), ['status' => 'APPROVED']);
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[2]['requestItem']]), [
        'status' => 'REJECTED',
        'admin_note' => 'Gunakan stok bersama lantai 2',
    ]);

    // Finalisasi.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('success');

    // Header harus PARTIAL.
    expect($atkRequest->fresh()->status)->toBe('PARTIAL');

    // Stok hanya berkurang untuk 2 item approved (2 + 1 = 3 qty), item rejected tetap 10.
    expect($items[0]['item']->fresh()->stock_qty)->toBe(8)   // 10 - 2
        ->and($items[1]['item']->fresh()->stock_qty)->toBe(9) // 10 - 1
        ->and($items[2]['item']->fresh()->stock_qty)->toBe(10); // tidak berkurang
});

it('finalizes as APPROVED when all items are approved', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Pulpen', 'stock_qty' => 10, 'qty' => 1],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertSessionHas('success');

    expect($atkRequest->fresh()->status)->toBe('APPROVED')
        ->and($items[0]['item']->fresh()->stock_qty)->toBe(9);
});

it('finalizes as REJECTED when all items are rejected', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Buku', 'stock_qty' => 10, 'qty' => 1],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
        'status' => 'REJECTED',
        'admin_note' => 'Tidak diproses',
    ]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertSessionHas('success');

    expect($atkRequest->fresh()->status)->toBe('REJECTED')
        ->and($items[0]['item']->fresh()->stock_qty)->toBe(10); // stok tidak berkurang
});

it('prevents double finalize from reducing stock twice', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Map', 'stock_qty' => 10, 'qty' => 2],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);
    actingAs($admin)->post(route('v2.atk.admin.requests.finalize', $atkRequest));

    // Coba finalize kedua kali — harus ditolak karena status bukan PENDING lagi.
    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('warning');

    // Stok hanya berkurang sekali (10 - 2 = 8).
    expect($items[0]['item']->fresh()->stock_qty)->toBe(8);
});

it('prevents finalize when approved item has insufficient stock', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Tinta', 'stock_qty' => 5, 'qty' => 5],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);
    $items[0]['item']->update(['stock_qty' => 1]);

    actingAs($admin)
        ->post(route('v2.atk.admin.requests.finalize', $atkRequest))
        ->assertRedirect()
        ->assertSessionHas('warning');

    // Finalisasi gagal — status tetap PENDING, stok tidak berkurang.
    expect($atkRequest->fresh()->status)->toBe('PENDING')
        ->and($items[0]['item']->fresh()->stock_qty)->toBe(1);
});

it('lets user see rejected items with admin reason on their request detail', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Staples', 'stock_qty' => 10, 'qty' => 1],
        ['name' => 'Isi Staples Kecil', 'stock_qty' => 10, 'qty' => 1],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[1]['requestItem']]), [
        'status' => 'REJECTED',
        'admin_note' => 'Gunakan stok bersama lantai 2',
    ]);
    actingAs($admin)->post(route('v2.atk.admin.requests.finalize', $atkRequest));

    // User melihat detail request-nya sendiri.
    actingAs($requester)
        ->get(route('v2.atk.requests.show', $atkRequest))
        ->assertOk()
        ->assertSee('PARTIAL')
        ->assertSee('Isi Staples Kecil')
        ->assertSee('Gunakan stok bersama lantai 2')
        ->assertSee('Tidak diproses');
});

it('does not count rejected items in report for partial requests', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Kertas A4', 'stock_qty' => 100, 'qty' => 5],
        ['name' => 'Map Ditolak', 'stock_qty' => 100, 'qty' => 3],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), ['status' => 'APPROVED']);
    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[1]['requestItem']]), [
        'status' => 'REJECTED',
        'admin_note' => 'Stok dialokasikan',
    ]);
    actingAs($admin)->post(route('v2.atk.admin.requests.finalize', $atkRequest));

    // Report bulan ini harus hanya menghitung item approved (qty 5), bukan total 8.
    $report = DB::table('atk_request_items')
        ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
        ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
        ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
        ->where('atk_requests.id', $atkRequest->id)
        ->sum('atk_request_items.qty');

    expect($report)->toBe(5);
});

it('keeps rejected item in request history without hard delete', function () {
    [$requester, $admin] = [User::factory()->create(), createAtkAdmin()];
    [$atkRequest, $items] = createAtkRequestWithItems($requester, [
        ['name' => 'Item Keep', 'stock_qty' => 10, 'qty' => 1],
    ]);

    actingAs($admin)->post(route('v2.atk.admin.requests.items.review', [$atkRequest, $items[0]['requestItem']]), [
        'status' => 'REJECTED',
        'admin_note' => 'Tidak diproses',
    ]);

    // Row item tetap ada di database — bukan hard delete.
    expect(DB::table('atk_request_items')->where('id', $items[0]['requestItem']->id)->exists())->toBeTrue();
});
