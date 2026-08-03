<?php

use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use App\Models\Division;
use App\Models\OpsAccessDivision;
use App\Models\User;
use App\Models\UserAccessRole;
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
        ->assertSee($opsRequest->request_number)
        ->assertDontSee($atkRequest->request_number);
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

it('shows only ops item movements in ops stock history', function () {
    $admin = createOpsUser('ADMIN OPS');
    $opsItem = createOpsTestItem(['module' => 'OPS', 'name' => 'Sepatu Safety']);
    $atkItem = createOpsTestItem(['module' => 'ATK', 'name' => 'Pulpen ATK']);

    foreach ([[$opsItem, 'Stok OPS'], [$atkItem, 'Stok ATK']] as [$item, $notes]) {
        AtkStockMovement::create([
            'atk_item_id' => $item->id,
            'movement_type' => 'IN',
            'qty' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'notes' => $notes,
            'created_by' => $admin->id,
        ]);
    }

    actingAs($admin)
        ->get('/v2/ops/admin/stock-movements?q=Sepatu')
        ->assertOk()
        ->assertSee('Sepatu Safety')
        ->assertSee('Stok OPS')
        ->assertDontSee('Pulpen ATK')
        ->assertDontSee('Stok ATK');
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
