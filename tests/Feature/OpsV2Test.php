<?php

use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use App\Models\Division;
use App\Models\OpsAccessDivision;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\InstallsOpsSchema;

use function Pest\Laravel\actingAs;

uses(InstallsOpsSchema::class);

beforeEach(function () {
    $this->installOpsSchema();
});

it('filters shared items by module', function () {
    $atkItem = createOpsTestItem(['module' => 'ATK', 'name' => 'Kertas']);
    $opsItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Sarung Tangan']);

    expect(AtkItem::forModule('OPS')->pluck('id')->all())->toBe([$opsItem->id])
        ->and(AtkItem::forModule('ATK')->pluck('id')->all())->toBe([$atkItem->id]);
});

it('creates pending ops requests with an ops number and module', function () {
    $user = User::factory()->create();
    $item = createOpsTestItem(['module' => 'OPS']);

    $request = AtkRequest::createPending(
        $user,
        collect([['item' => $item, 'qty' => 2]]),
        'Untuk kegiatan lapangan',
        'OPS',
    );

    expect($request->module)->toBe('OPS')
        ->and($request->request_number)->toStartWith('OPS-');
});

it('bases regular ops access on selected divisions instead of individual ops roles', function () {
    $selectedDivision = Division::create(['name' => 'Operasional Terpilih']);
    $otherDivision = Division::create(['name' => 'Divisi Lain']);
    $selectedUser = User::factory()->create(['division_id' => $selectedDivision->id]);
    $otherUser = User::factory()->create(['division_id' => $otherDivision->id]);
    $legacyRoleUser = User::factory()->create(['division_id' => $otherDivision->id]);

    UserAccessRole::create(['user_id' => $legacyRoleUser->id, 'role' => 'OPS']);
    OpsAccessDivision::create(['division_id' => $selectedDivision->id]);

    expect($selectedUser->canAccessOps())->toBeTrue()
        ->and($otherUser->canAccessOps())->toBeFalse()
        ->and($legacyRoleUser->canAccessOps())->toBeFalse();
});

it('hides the ops access card from users without ops access', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('v2.access'))
        ->assertOk()
        ->assertDontSee('Kebutuhan Operasional');
});

it('allows selected users to see and open the ops module', function () {
    $division = Division::factory()->create();
    $user = User::factory()->create(['division_id' => $division->id]);
    OpsAccessDivision::create(['division_id' => $division->id]);

    actingAs($user)
        ->get(route('v2.access'))
        ->assertOk()
        ->assertSee('Kebutuhan Operasional')
        ->assertSee('/v2/ops');

    actingAs($user)
        ->get('/v2/ops')
        ->assertOk();
});

it('shows matching svg icons in the ops sidebar', function () {
    actingAs(createOpsUser('ADMIN OPS'))
        ->get('/v2/ops')
        ->assertOk()
        ->assertSee('id="ops-icon-catalog"', false)
        ->assertSee('href="#ops-icon-cart"', false)
        ->assertSee('href="#ops-icon-request"', false)
        ->assertSee('href="#ops-icon-stock"', false)
        ->assertSee('href="#ops-icon-access"', false)
        ->assertSee('href="#ops-icon-portal"', false);
});

it('shows the pending ops request count in the admin sidebar', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = createOpsUser();
    $opsItem = createOpsTestItem(['module' => 'OPS']);
    $atkItem = createOpsTestItem(['module' => 'ATK']);

    AtkRequest::createPending($requester, collect([['item' => $opsItem, 'qty' => 1]]), null, 'OPS');
    AtkRequest::createPending($requester, collect([['item' => $opsItem, 'qty' => 1]]), null, 'OPS');
    $approved = AtkRequest::createPending($requester, collect([['item' => $opsItem, 'qty' => 1]]), null, 'OPS');
    $approved->update(['status' => AtkRequest::STATUS_APPROVED]);
    AtkRequest::createPending($requester, collect([['item' => $atkItem, 'qty' => 1]]));

    actingAs($admin)->get('/v2/ops')
        ->assertOk()
        ->assertSee('<span class="ops-nav-badge">2</span>', false);
});

it('rejects users without ops access from the ops module', function () {
    actingAs(User::factory()->create())
        ->get('/v2/ops')
        ->assertForbidden();
});

it('gives ops admins and atk admins full ops access', function () {
    $opsAdmin = User::factory()->create();
    $atkAdmin = User::factory()->create();
    UserAccessRole::create(['user_id' => $opsAdmin->id, 'role' => 'ADMIN OPS']);
    UserAccessRole::create(['user_id' => $atkAdmin->id, 'role' => 'ADMIN ATK']);

    expect($opsAdmin->canAccessOps())->toBeTrue()
        ->and($opsAdmin->canManageOps())->toBeTrue()
        ->and($atkAdmin->canAccessOps())->toBeTrue()
        ->and($atkAdmin->canManageOps())->toBeTrue();
});

it('shows only available ops items in the ops catalog', function () {
    $user = createOpsUser();
    createOpsTestItem(['module' => 'ATK', 'name' => 'Kertas ATK']);
    createOpsTestItem(['module' => 'OPS', 'name' => 'Sarung Tangan OPS']);
    createOpsTestItem([
        'module' => 'OPS',
        'name' => 'Barang OPS Terhapus',
        'is_active' => false,
        'deleted_at' => now(),
    ]);

    actingAs($user)
        ->get('/v2/ops')
        ->assertOk()
        ->assertSee('Sarung Tangan OPS')
        ->assertDontSee('Kertas ATK')
        ->assertDontSee('Barang OPS Terhapus');
});

it('keeps the ops cart separate from the atk cart', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS']);

    actingAs($user)
        ->post('/v2/ops/cart', ['atk_item_id' => $item->id, 'qty' => 2])
        ->assertSessionHas('ops_cart.'.$item->id, 2)
        ->assertSessionMissing('atk_cart');
});

it('renders the ops cart with the atk cart table and quantity stepper', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Helm Keranjang OPS', 'stock_qty' => 8]);

    actingAs($user)->withSession(['ops_cart' => [$item->id => 2]])
        ->get('/v2/ops/cart')
        ->assertOk()
        ->assertSee('ops-cart-table', false)
        ->assertSee('data-ops-cart-stepper', false)
        ->assertSee('aria-label="Kurangi jumlah Helm Keranjang OPS"', false)
        ->assertSee('aria-label="Tambah jumlah Helm Keranjang OPS"', false)
        ->assertSee('Tambah Barang')
        ->assertSee('Ajukan Permintaan');
});

it('updates the ops cart quantity without reloading the page', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS', 'stock_qty' => 8]);

    actingAs($user)->withSession(['ops_cart' => [$item->id => 2]])
        ->putJson('/v2/ops/cart/'.$item->id, ['qty' => 3])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'qty' => 3,
            'cartCount' => 3,
        ])
        ->assertSessionHas('ops_cart.'.$item->id, 3);
});

it('submits an ops cart without reducing stock', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS', 'stock_qty' => 8]);

    actingAs($user)->withSession(['ops_cart' => [$item->id => 3]])
        ->post('/v2/ops/cart/submit', ['notes' => 'Dipakai di lapangan'])
        ->assertRedirect();

    $opsRequest = AtkRequest::forModule('OPS')->sole();

    expect($opsRequest->user_id)->toBe($user->id)
        ->and($opsRequest->items()->sole()->qty)->toBe(3)
        ->and($item->fresh()->stock_qty)->toBe(8);
});

it('shows only the authenticated users own ops requests', function () {
    $user = createOpsUser();
    $other = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS']);

    $own = AtkRequest::createPending($user, collect([['item' => $item, 'qty' => 1]]), null, 'OPS');
    $otherRequest = AtkRequest::createPending($other, collect([['item' => $item, 'qty' => 1]]), null, 'OPS');

    actingAs($user)
        ->get('/v2/ops/requests')
        ->assertOk()
        ->assertSee($own->request_number)
        ->assertDontSee($otherRequest->request_number);
});

it('renders and filters the ops request history with the atk responsive layout', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS']);
    $pending = AtkRequest::createPending($user, collect([['item' => $item, 'qty' => 1]]), null, 'OPS');
    $approved = AtkRequest::createPending($user, collect([['item' => $item, 'qty' => 2]]), null, 'OPS');
    $approved->update(['status' => AtkRequest::STATUS_APPROVED]);

    actingAs($user)->get('/v2/ops/requests?status=APPROVED')
        ->assertOk()
        ->assertSee('ops-request-mobile-list', false)
        ->assertSee('ops-request-desktop-table', false)
        ->assertSee('name="status"', false)
        ->assertSee('Disetujui')
        ->assertSee($approved->request_number)
        ->assertDontSee($pending->request_number);
});

it('renders an ops request detail with the atk responsive summary and item layout', function () {
    $user = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Rompi Detail OPS', 'unit_name' => 'pcs']);
    $opsRequest = AtkRequest::createPending($user, collect([['item' => $item, 'qty' => 2]]), 'Dipakai untuk kunjungan.', 'OPS');
    $opsRequest->update(['pt_name_snapshot' => 'TRIGUNA OPS']);
    $opsRequest->items()->firstOrFail()->update([
        'status' => AtkRequestItem::STATUS_REJECTED,
        'admin_note' => 'Gunakan stok tim terlebih dahulu.',
    ]);

    actingAs($user)->get('/v2/ops/requests/'.$opsRequest->id)
        ->assertOk()
        ->assertSee('ops-request-summary', false)
        ->assertSee('ops-request-item-mobile-list', false)
        ->assertSee('ops-request-item-desktop-table', false)
        ->assertSee('TRIGUNA OPS')
        ->assertSee('Rompi Detail OPS')
        ->assertSee('2 pcs')
        ->assertSee('Tidak diproses')
        ->assertSee('Catatan Pengaju')
        ->assertSee('Gunakan stok tim terlebih dahulu.');
});

it('creates an ops item with an opening stock movement', function () {
    $admin = createOpsUser('ADMIN OPS');

    actingAs($admin)
        ->post('/v2/ops/admin/items', [
            'name' => 'Helm Proyek',
            'unit_name' => 'pcs',
            'stock_qty' => 12,
            'description' => 'Untuk kunjungan lapangan',
        ])
        ->assertRedirect('/v2/ops/admin/items');

    $item = AtkItem::forModule('OPS')->where('name', 'Helm Proyek')->sole();
    $movement = AtkStockMovement::where('atk_item_id', $item->id)->sole();

    expect($item->stock_qty)->toBe(12)
        ->and($movement->movement_type)->toBe(AtkStockMovement::TYPE_IN)
        ->and($movement->stock_before)->toBe(0)
        ->and($movement->stock_after)->toBe(12);
});

it('stores and displays an optional ops item photo', function () {
    Storage::fake('public');
    $admin = createOpsUser('ADMIN OPS');

    actingAs($admin)->post('/v2/ops/admin/items', [
        'name' => 'Helm Foto OPS',
        'image' => UploadedFile::fake()->image('helm.png', 1200, 800),
        'unit_name' => 'pcs',
        'stock_qty' => 3,
    ])->assertRedirect('/v2/ops/admin/items');

    $item = AtkItem::forModule('OPS')->where('name', 'Helm Foto OPS')->firstOrFail();

    expect($item->image_path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($item->image_path);

    actingAs($admin)->get('/v2/ops')
        ->assertOk()
        ->assertSee(asset('storage/'.$item->image_path))
        ->assertSee('alt="Helm Foto OPS"', false);
});

it('keeps the ops item photo when editing without a new upload and renders form fallbacks', function () {
    $admin = createOpsUser('ADMIN OPS');
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Rompi Foto OPS', 'image_path' => 'ops-items/rompi.jpg']);
    createOpsTestItem(['module' => 'OPS', 'name' => 'Barang Tanpa Foto', 'image_path' => null]);

    actingAs($admin)->get('/v2/ops/admin/items/create')
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('Foto barang (opsional)');

    actingAs($admin)->get('/v2/ops/admin/items/'.$item->id.'/edit')
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee(asset('storage/'.$item->image_path));

    actingAs($admin)->put('/v2/ops/admin/items/'.$item->id, [
        'name' => $item->name,
        'unit_name' => $item->unit_name,
        'description' => $item->description,
        'is_active' => '1',
    ])->assertRedirect('/v2/ops/admin/items');

    expect($item->fresh()->image_path)->toBe('ops-items/rompi.jpg');
    actingAs($admin)->get('/v2/ops')->assertOk()->assertSee('Tanpa foto');
});

it('renders the ops master items as an atk-style responsive table with edit and delete actions only', function () {
    $admin = createOpsUser('ADMIN OPS');
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Rompi Master OPS', 'stock_qty' => 7]);

    actingAs($admin)->get('/v2/ops/admin/items')
        ->assertOk()
        ->assertSee('ops-admin-items-table', false)
        ->assertSee('<th>Barang</th>', false)
        ->assertSee('<th>Stok</th>', false)
        ->assertSee('<th>Status</th>', false)
        ->assertSee('<th>Aksi</th>', false)
        ->assertDontSee('<th>Satuan</th>', false)
        ->assertDontSee('<th>Tambah Stok</th>', false)
        ->assertDontSee('name="movement_type" value="IN"', false)
        ->assertDontSee('name="movement_type" value="OUT"', false)
        ->assertSee(route('v2.ops.admin.items.edit', $item), false)
        ->assertDontSee('Kurangi Stok')
        ->assertSee('Hapus');
});

it('adjusts stock from the ops edit form and records the movement', function () {
    $admin = createOpsUser('ADMIN OPS');
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Rompi Edit Stok', 'stock_qty' => 10]);

    actingAs($admin)->get('/v2/ops/admin/items/'.$item->id.'/edit')
        ->assertOk()
        ->assertSee('name="stock_qty"', false)
        ->assertSee('value="10"', false);

    actingAs($admin)->put('/v2/ops/admin/items/'.$item->id, [
        'name' => $item->name,
        'unit_name' => $item->unit_name,
        'stock_qty' => 6,
        'is_active' => '1',
    ])->assertRedirect('/v2/ops/admin/items');

    expect($item->fresh()->stock_qty)->toBe(6);

    $movement = AtkStockMovement::where('atk_item_id', $item->id)->sole();
    expect($movement->movement_type)->toBe(AtkStockMovement::TYPE_OUT)
        ->and($movement->qty)->toBe(4)
        ->and($movement->stock_before)->toBe(10)
        ->and($movement->stock_after)->toBe(6);

    actingAs($admin)->put('/v2/ops/admin/items/'.$item->id, [
        'name' => $item->name,
        'unit_name' => $item->unit_name,
        'stock_qty' => '6',
        'is_active' => '1',
    ])->assertRedirect('/v2/ops/admin/items');

    expect(AtkStockMovement::where('atk_item_id', $item->id)->count())->toBe(1);
});

it('renders an atk-style green quantity stepper on ops catalog cards', function () {
    $user = createOpsUser();
    createOpsTestItem(['module' => 'OPS', 'name' => 'Sarung Tangan Stepper', 'stock_qty' => 5]);

    actingAs($user)->get('/v2/ops')
        ->assertOk()
        ->assertSee('ops-catalog-grid', false)
        ->assertSee('data-ops-stepper', false)
        ->assertSee('data-ops-stepper-decrease', false)
        ->assertSee('data-ops-stepper-input', false)
        ->assertSee('data-ops-stepper-increase', false)
        ->assertSee('aria-label="Kurangi jumlah Sarung Tangan Stepper"', false)
        ->assertSee('aria-label="Tambah jumlah Sarung Tangan Stepper"', false)
        ->assertSee('ops-add-button', false)
        ->assertSee('quantityInput.stepDown()', false)
        ->assertSee('quantityInput.stepUp()', false);
});

it('adds and subtracts ops stock without allowing negative stock', function () {
    $admin = createOpsUser('ADMIN OPS');
    $item = createOpsTestItem(['module' => 'OPS', 'stock_qty' => 10]);

    actingAs($admin)->post('/v2/ops/admin/items/'.$item->id.'/stock', [
        'movement_type' => 'IN',
        'qty' => 5,
    ])->assertRedirect();

    actingAs($admin)->post('/v2/ops/admin/items/'.$item->id.'/stock', [
        'movement_type' => 'OUT',
        'qty' => 4,
    ])->assertRedirect();

    expect($item->fresh()->stock_qty)->toBe(11);

    actingAs($admin)->post('/v2/ops/admin/items/'.$item->id.'/stock', [
        'movement_type' => 'OUT',
        'qty' => 20,
    ])->assertSessionHas('warning');

    expect($item->fresh()->stock_qty)->toBe(11)
        ->and(AtkStockMovement::where('atk_item_id', $item->id)->count())->toBe(2);
});

it('soft deletes ops items with a required reason', function () {
    $admin = createOpsUser('ADMIN OPS');
    $item = createOpsTestItem(['module' => 'OPS']);

    actingAs($admin)
        ->delete('/v2/ops/admin/items/'.$item->id, [])
        ->assertSessionHasErrors('deletion_note');

    actingAs($admin)
        ->delete('/v2/ops/admin/items/'.$item->id, ['deletion_note' => 'Barang tidak digunakan lagi'])
        ->assertRedirect('/v2/ops/admin/items');

    $item->refresh();

    expect(AtkItem::whereKey($item->id)->exists())->toBeTrue()
        ->and($item->is_active)->toBeFalse()
        ->and($item->deleted_at)->not->toBeNull()
        ->and($item->deleted_by)->toBe($admin->id)
        ->and($item->deletion_note)->toBe('Barang tidak digunakan lagi');
});

it('lists only ops requests for ops approval', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = createOpsUser();
    $opsItem = createOpsTestItem(['module' => 'OPS']);
    $atkItem = createOpsTestItem(['module' => 'ATK']);
    $opsRequest = AtkRequest::createPending($requester, collect([['item' => $opsItem, 'qty' => 1]]), null, 'OPS');
    $atkRequest = AtkRequest::createPending($requester, collect([['item' => $atkItem, 'qty' => 1]]));

    actingAs($admin)
        ->get('/v2/ops/admin/requests')
        ->assertOk()
        ->assertSee('href="'.route('v2.ops.admin.requests.show', $opsRequest).'"', false)
        ->assertDontSee('href="'.route('v2.ops.admin.requests.show', $atkRequest).'"', false);
});

it('renders the ops admin request list with the same responsive flow as atk', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS']);
    $opsRequest = AtkRequest::createPending($requester, collect([['item' => $item, 'qty' => 1]]), null, 'OPS');
    $opsRequest->update(['pt_name_snapshot' => 'TRIGUNA OPS']);

    actingAs($admin)->get('/v2/ops/admin/requests?q=TRIGUNA+OPS&status=PENDING')
        ->assertOk()
        ->assertSee('ops-admin-request-mobile-list', false)
        ->assertSee('ops-admin-request-desktop-table', false)
        ->assertSee('Cari no request, user, atau PT')
        ->assertSee('/v2/ops/admin/requests/manual/create', false)
        ->assertSee($requester->name)
        ->assertSee('TRIGUNA OPS')
        ->assertSee('Review Admin');
});

it('creates a manual ops request using only active ops items', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = User::factory()->create(['name' => 'Pengambil Manual OPS']);
    $opsItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Helm Manual OPS', 'stock_qty' => 8]);
    $atkItem = createOpsTestItem(['module' => 'ATK', 'name' => 'Pulpen Manual ATK']);

    actingAs($admin)->get('/v2/ops/admin/requests/manual/create')
        ->assertOk()
        ->assertSee('Input Pengambilan Manual')
        ->assertSee($requester->name)
        ->assertSee($opsItem->name)
        ->assertDontSee($atkItem->name);

    $response = actingAs($admin)->post('/v2/ops/admin/requests/manual', [
        'user_id' => $requester->id,
        'notes' => 'Dicatat admin OPS.',
        'quantities' => [$opsItem->id => 2],
    ]);

    $opsRequest = AtkRequest::forModule('OPS')->where('user_id', $requester->id)->sole();
    $response->assertRedirect('/v2/ops/admin/requests/'.$opsRequest->id);

    expect($opsRequest->status)->toBe(AtkRequest::STATUS_PENDING)
        ->and($opsRequest->notes)->toBe('Dicatat admin OPS.')
        ->and($opsRequest->items()->sole()->atk_item_id)->toBe($opsItem->id);
});

it('renders the atk-style ops review and lets the admin reject all items', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS', 'name' => 'Rompi Review OPS', 'stock_qty' => 5]);
    $opsRequest = AtkRequest::createPending($requester, collect([['item' => $item, 'qty' => 2]]), null, 'OPS');

    actingAs($admin)->get('/v2/ops/admin/requests/'.$opsRequest->id)
        ->assertOk()
        ->assertSee('ops-admin-review-header', false)
        ->assertSee('ops-admin-review-table', false)
        ->assertSee('ops-admin-review-item', false)
        ->assertSee('data-label="Jumlah"', false)
        ->assertSee('data-label="Stok Saat Ini"', false)
        ->assertSee('Tolak Semua')
        ->assertSee('Selesaikan Review');

    actingAs($admin)->post('/v2/ops/admin/requests/'.$opsRequest->id.'/reject', [
        'admin_note' => 'Tidak digunakan.',
    ])->assertRedirect('/v2/ops/admin/requests/'.$opsRequest->id);

    $freshRequest = $opsRequest->fresh();
    expect($freshRequest->status)->toBe(AtkRequest::STATUS_REJECTED)
        ->and($freshRequest->admin_note)->toBe('Tidak digunakan.')
        ->and($opsRequest->items()->sole()->status)->toBe(AtkRequestItem::STATUS_REJECTED);
});

it('finalizes a partial ops approval once and reduces only approved stock', function () {
    $admin = createOpsUser('ADMIN OPS');
    $requester = createOpsUser();
    $approvedItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Helm', 'stock_qty' => 10]);
    $rejectedItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Jas Hujan', 'stock_qty' => 10]);
    $opsRequest = AtkRequest::createPending($requester, collect([
        ['item' => $approvedItem, 'qty' => 3],
        ['item' => $rejectedItem, 'qty' => 2],
    ]), null, 'OPS');
    [$approveRow, $rejectRow] = $opsRequest->items()->orderBy('id')->get();

    actingAs($admin)->post('/v2/ops/admin/requests/'.$opsRequest->id.'/items/'.$approveRow->id.'/review', [
        'status' => AtkRequestItem::STATUS_APPROVED,
    ])->assertRedirect();

    actingAs($admin)->post('/v2/ops/admin/requests/'.$opsRequest->id.'/items/'.$rejectRow->id.'/review', [
        'status' => AtkRequestItem::STATUS_REJECTED,
        'admin_note' => 'Belum diperlukan',
    ])->assertRedirect();

    expect($approvedItem->fresh()->stock_qty)->toBe(10);

    actingAs($admin)->post('/v2/ops/admin/requests/'.$opsRequest->id.'/finalize')->assertRedirect();

    expect($opsRequest->fresh()->status)->toBe(AtkRequest::STATUS_PARTIAL)
        ->and($approvedItem->fresh()->stock_qty)->toBe(7)
        ->and($rejectedItem->fresh()->stock_qty)->toBe(10)
        ->and(AtkStockMovement::where('atk_item_id', $approvedItem->id)->count())->toBe(1);

    actingAs($admin)->post('/v2/ops/admin/requests/'.$opsRequest->id.'/finalize')->assertSessionHas('warning');

    expect($approvedItem->fresh()->stock_qty)->toBe(7)
        ->and(AtkStockMovement::where('atk_item_id', $approvedItem->id)->count())->toBe(1);
});

it('lets atk admins process ops approvals from the ops module', function () {
    $admin = createOpsUser('ADMIN ATK');
    $requester = createOpsUser();
    $item = createOpsTestItem(['module' => 'OPS']);
    $opsRequest = AtkRequest::createPending($requester, collect([['item' => $item, 'qty' => 1]]), null, 'OPS');

    actingAs($admin)
        ->get('/v2/ops/admin/requests/'.$opsRequest->id)
        ->assertOk()
        ->assertSee($opsRequest->request_number);
});

it('renders ops stock history with the same responsive table structure as atk', function () {
    $admin = createOpsUser('ADMIN OPS');
    $admin->update(['name' => 'Admin Riwayat OPS']);
    $requester = User::factory()->create(['name' => 'Pengambil OPS']);
    $opsItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Sepatu Safety']);
    $atkItem = createOpsTestItem(['module' => 'ATK', 'name' => 'Pulpen ATK']);
    $opsRequest = AtkRequest::create([
        'module' => 'OPS',
        'request_number' => 'OPS-STOCK-HISTORY',
        'user_id' => $requester->id,
        'user_name_snapshot' => $requester->name,
        'pt_name_snapshot' => 'TRIGUNA',
        'status' => AtkRequest::STATUS_APPROVED,
    ]);

    AtkStockMovement::create([
        'atk_item_id' => $opsItem->id,
        'movement_type' => 'OUT',
        'qty' => 2,
        'stock_before' => 7,
        'stock_after' => 5,
        'source_type' => AtkStockMovement::SOURCE_REQUEST,
        'source_id' => $opsRequest->id,
        'created_by' => $admin->id,
    ]);
    AtkStockMovement::create([
        'atk_item_id' => $atkItem->id,
        'movement_type' => 'IN',
        'qty' => 5,
        'stock_before' => 0,
        'stock_after' => 5,
        'created_by' => $admin->id,
    ]);

    actingAs($admin)
        ->get('/v2/ops/admin/stock-movements?item_id='.$opsItem->id.'&movement_type=OUT')
        ->assertOk()
        ->assertSee('name="item_id"', false)
        ->assertSee('name="movement_type"', false)
        ->assertSee('ops-stock-movements-mobile-table', false)
        ->assertSee('ops-stock-movement-card', false)
        ->assertSee('data-label="PT"', false)
        ->assertSee('data-label="Nama Pengambil"', false)
        ->assertSee('data-label="Diproses Oleh"', false)
        ->assertSee('data-label="Perubahan Stok"', false)
        ->assertSee('Sepatu Safety')
        ->assertSee('Keluar')
        ->assertSee('2 pcs')
        ->assertSee('TRIGUNA')
        ->assertSee('Pengambil OPS')
        ->assertSee('Admin Riwayat OPS')
        ->assertSee('7 → 5')
        ->assertDontSee('Pulpen ATK');
});

it('shows bulk division access instead of per-user access buttons', function () {
    $admin = createOpsUser('ADMIN OPS');
    $division = Division::factory()->create(['name' => 'OPS Lapangan']);
    User::factory()->count(2)->create(['division_id' => $division->id, 'status' => User::STATUS_ACTIVE]);
    User::factory()->create(['division_id' => $division->id, 'status' => 'INACTIVE']);
    OpsAccessDivision::create(['division_id' => $division->id, 'created_by' => $admin->id]);

    actingAs($admin)->get('/v2/ops/admin/access')
        ->assertOk()
        ->assertSee('OPS Lapangan')
        ->assertSee('2 pengguna aktif')
        ->assertSee('name="division_ids[]"', false)
        ->assertSee('value="'.$division->id.'"', false)
        ->assertSee('Simpan Akses Pengguna')
        ->assertDontSee('Jadikan Pengguna')
        ->assertDontSee('Cabut Pengguna');
});

it('syncs bulk division access while keeping ops admins managed per user', function () {
    $admin = createOpsUser('ADMIN OPS');
    $oldDivision = Division::factory()->create();
    $newDivision = Division::factory()->create();
    $oldUser = User::factory()->create(['division_id' => $oldDivision->id]);
    $newUser = User::factory()->create(['division_id' => $newDivision->id]);
    $newAdmin = User::factory()->create();
    OpsAccessDivision::create(['division_id' => $oldDivision->id, 'created_by' => $admin->id]);

    actingAs($admin)->post('/v2/ops/admin/access/divisions', [
        'division_ids' => [$newDivision->id],
    ])->assertRedirect('/v2/ops/admin/access');

    expect(OpsAccessDivision::pluck('division_id')->all())->toBe([$newDivision->id])
        ->and($oldUser->canAccessOps())->toBeFalse()
        ->and($newUser->canAccessOps())->toBeTrue();

    actingAs($admin)->post('/v2/ops/admin/access/'.$newAdmin->id.'/grant-admin')->assertRedirect();
    expect($newAdmin->fresh()->canManageOps())->toBeTrue();

    actingAs($admin)->delete('/v2/ops/admin/access/'.$admin->id.'/revoke-admin')
        ->assertSessionHas('warning');

    expect($admin->fresh()->hasAccessRole('ADMIN OPS'))->toBeTrue();
});

function createOpsTestItem(array $overrides = []): AtkItem
{
    return AtkItem::create(array_merge([
        'atk_category_id' => null,
        'name' => 'Barang OPS',
        'description' => null,
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ], $overrides));
}

function createOpsUser(string $role = 'OPS'): User
{
    if ($role === 'OPS') {
        $division = Division::factory()->create();
        $user = User::factory()->create(['division_id' => $division->id]);
        OpsAccessDivision::create(['division_id' => $division->id]);

        return $user;
    }

    $user = User::factory()->create();
    UserAccessRole::create(['user_id' => $user->id, 'role' => $role]);

    return $user;
}
