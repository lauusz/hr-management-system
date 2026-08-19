<?php

use App\Enums\UserRole;
use App\Models\AtkItem;
use App\Models\AtkMksAccessPt;
use App\Models\AtkNeedRequest;
use App\Models\AtkRequest;
use App\Models\AtkStockMovement;
use App\Models\EmployeeProfile;
use App\Models\Pt;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Support\Facades\DB;
use Tests\Support\InstallsOpsSchema;

use function Pest\Laravel\actingAs;

uses(InstallsOpsSchema::class);

beforeEach(function () {
    $this->installOpsSchema();
});

it('lets admin atk open the atk mks module as master', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN_ATK]);

    actingAs($admin)
        ->get('/v2/atk-mks')
        ->assertOk()
        ->assertSee('Stok ATK MKS');

    actingAs($admin)
        ->get('/v2/atk-mks/admin/items')
        ->assertOk()
        ->assertSee('Master Barang ATK MKS');
});

it('limits atk mks access to selected pts and atk mks admins', function () {
    $selectedPt = Pt::factory()->create(['name' => 'PT MKS Terpilih']);
    $unselectedPt = Pt::factory()->create(['name' => 'PT MKS Lain']);
    $selectedUser = User::factory()->create();
    $unselectedUser = User::factory()->create();
    $userWithoutPt = User::factory()->create();
    $atkMksAdmin = User::factory()->create();

    EmployeeProfile::create(['user_id' => $selectedUser->id, 'pt_id' => $selectedPt->id]);
    EmployeeProfile::create(['user_id' => $unselectedUser->id, 'pt_id' => $unselectedPt->id]);
    DB::table('atk_mks_access_pts')->insert([
        'pt_id' => $selectedPt->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    UserAccessRole::create(['user_id' => $atkMksAdmin->id, 'role' => 'ADMIN ATK MKS']);

    actingAs($selectedUser)->get('/v2/atk-mks')->assertOk();
    actingAs($selectedUser)->get('/v2/access')->assertOk()->assertSee('Stok ATK MKS');
    actingAs($atkMksAdmin)->get('/v2/atk-mks')->assertOk();
    actingAs($unselectedUser)->get('/v2/atk-mks')->assertForbidden();
    actingAs($unselectedUser)->get('/v2/access')->assertOk()->assertDontSee('Stok ATK MKS');
    actingAs($userWithoutPt)->get('/v2/atk-mks')->assertForbidden();
});

it('shows only atk mks items in its catalog', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN_ATK]);
    foreach ([
        ['module' => 'ATK', 'name' => 'Barang Kantor Pusat'],
        ['module' => 'OPS', 'name' => 'Barang OPS'],
        ['module' => 'ATK_MKS', 'name' => 'Barang MKS'],
    ] as $data) {
        AtkItem::create($data + [
            'unit_name' => 'pcs',
            'stock_qty' => 5,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
    }

    actingAs($admin)
        ->get('/v2/atk-mks')
        ->assertOk()
        ->assertSee('Barang MKS')
        ->assertDontSee('Barang Kantor Pusat')
        ->assertDontSee('Barang OPS');
});

it('keeps its cart separate and creates atk mks requests', function () {
    $user = createAtkMksUser();
    $item = createAtkMksItem(['name' => 'Kertas MKS', 'stock_qty' => 8]);

    actingAs($user)
        ->withSession(['ops_cart' => [$item->id => 4], 'atk_cart' => [$item->id => 3]])
        ->post('/v2/atk-mks/cart', ['atk_item_id' => $item->id, 'qty' => 2])
        ->assertSessionHas('atk_mks_cart.'.$item->id, 2);

    actingAs($user)
        ->post('/v2/atk-mks/cart/submit', ['notes' => 'Untuk kantor MKS'])
        ->assertRedirect();

    $request = AtkRequest::latest('id')->firstOrFail();
    expect($request->module)->toBe('ATK_MKS')
        ->and($request->request_number)->toStartWith('ATK_MKS-')
        ->and(session('atk_mks_cart'))->toBeNull()
        ->and(session('ops_cart'))->toBe([$item->id => 4])
        ->and(session('atk_cart'))->toBe([$item->id => 3]);
});

it('lets atk mks admins manage items stock and soft deletion', function () {
    $admin = createAtkMksUser('ADMIN ATK MKS');

    actingAs($admin)->post('/v2/atk-mks/admin/items', [
        'name' => 'Map MKS',
        'unit_name' => 'pcs',
        'stock_qty' => 5,
        'is_active' => 1,
    ])->assertRedirect('/v2/atk-mks/admin/items');

    $item = AtkItem::where('name', 'Map MKS')->firstOrFail();
    expect($item->module)->toBe('ATK_MKS');

    actingAs($admin)->post('/v2/atk-mks/admin/items/'.$item->id.'/stock', [
        'movement_type' => 'OUT',
        'qty' => 2,
        'notes' => 'Dipakai MKS',
    ])->assertRedirect();

    expect($item->fresh()->stock_qty)->toBe(3)
        ->and(AtkStockMovement::where('atk_item_id', $item->id)->count())->toBe(2);

    actingAs($admin)->delete('/v2/atk-mks/admin/items/'.$item->id, [
        'deletion_note' => 'Barang tidak digunakan lagi',
    ])->assertRedirect('/v2/atk-mks/admin/items');

    expect($item->fresh()->deleted_at)->not->toBeNull();
});

it('syncs pt and per-user admin access', function () {
    $admin = createAtkMksUser('ADMIN ATK MKS');
    $oldPt = Pt::factory()->create(['name' => 'PT MKS Lama']);
    $newPt = Pt::factory()->create(['name' => 'PT MKS Baru']);
    $oldMember = User::factory()->create();
    $newMember = User::factory()->create();
    $newAdmin = User::factory()->create();
    EmployeeProfile::create(['user_id' => $oldMember->id, 'pt_id' => $oldPt->id]);
    EmployeeProfile::create(['user_id' => $newMember->id, 'pt_id' => $newPt->id]);
    AtkMksAccessPt::create(['pt_id' => $oldPt->id, 'created_by' => $admin->id]);

    actingAs($admin)->post('/v2/atk-mks/admin/access/pts', [
        'pt_ids' => [$newPt->id],
    ])->assertRedirect('/v2/atk-mks/admin/access');

    expect(AtkMksAccessPt::pluck('pt_id')->all())->toBe([$newPt->id])
        ->and($oldMember->canAccessAtkMks())->toBeFalse()
        ->and($newMember->canAccessAtkMks())->toBeTrue();

    actingAs($admin)
        ->post('/v2/atk-mks/admin/access/'.$newAdmin->id.'/grant-admin')
        ->assertRedirect('/v2/atk-mks/admin/access');

    expect($newAdmin->fresh()->canManageAtkMks())->toBeTrue();
});

it('shows pt access checkboxes with active user counts', function () {
    $admin = createAtkMksUser('ADMIN ATK MKS');
    $pt = Pt::factory()->create(['name' => 'PT. CABANG MAKASSAR']);

    foreach ([User::STATUS_ACTIVE, User::STATUS_ACTIVE, 'INACTIVE'] as $status) {
        $user = User::factory()->create(['status' => $status]);
        EmployeeProfile::create(['user_id' => $user->id, 'pt_id' => $pt->id]);
    }

    actingAs($admin)
        ->get('/v2/atk-mks/admin/access')
        ->assertOk()
        ->assertSee('Pilih PT pengguna')
        ->assertSee('PT. CABANG MAKASSAR')
        ->assertSee('2 pengguna aktif')
        ->assertSee('name="pt_ids[]"', false)
        ->assertSee('/v2/atk-mks/admin/access/pts', false)
        ->assertDontSee('name="division_ids[]"', false);
});

it('lets atk mks admins request goods while only admin atk processes them', function () {
    $mksAdmin = createAtkMksUser('ADMIN ATK MKS');
    $atkAdmin = User::factory()->create(['role' => UserRole::ADMIN_ATK]);

    actingAs($mksAdmin)->post('/v2/atk-mks/admin/need-requests', [
        'requested_item_name' => 'Toner MKS',
        'qty' => 2,
        'unit_name' => 'pcs',
        'reason' => 'Stok MKS habis',
    ])->assertRedirect('/v2/atk-mks/admin/need-requests');

    $needRequest = AtkNeedRequest::latest('id')->firstOrFail();
    expect($needRequest->module)->toBe('ATK_MKS');

    actingAs($atkAdmin)
        ->get('/v2/atk/admin/need-requests')
        ->assertOk()
        ->assertSee('>ATK MKS</span>', false);

    actingAs($mksAdmin)->post('/v2/atk/admin/need-requests/'.$needRequest->id.'/process', [
        'status' => AtkNeedRequest::STATUS_DONE,
    ])->assertForbidden();

    actingAs($atkAdmin)->post('/v2/atk/admin/need-requests/'.$needRequest->id.'/process', [
        'status' => AtkNeedRequest::STATUS_DONE,
    ])->assertRedirect('/v2/atk/admin/need-requests');
});

it('shows an amber access card and admin sidebar only to authorized users', function () {
    $admin = createAtkMksUser('ADMIN ATK MKS');
    $regular = User::factory()->create();

    actingAs($admin)->get('/v2/access')
        ->assertOk()
        ->assertSee('Stok ATK MKS')
        ->assertSee('/v2/atk-mks', false);

    actingAs($admin)->get('/v2/atk-mks/admin/items')
        ->assertOk()
        ->assertSee('#A16207', false)
        ->assertSee('Master Barang ATK MKS');

    actingAs($regular)->get('/v2/access')
        ->assertOk()
        ->assertDontSee('Stok ATK MKS');
});

it('shows the pending atk mks need request count in the admin sidebar', function () {
    $admin = createAtkMksUser('ADMIN ATK MKS');

    foreach ([AtkNeedRequest::STATUS_PENDING, AtkNeedRequest::STATUS_PENDING, AtkNeedRequest::STATUS_DONE] as $status) {
        AtkNeedRequest::create([
            'module' => AtkNeedRequest::MODULE_ATK_MKS,
            'user_id' => $admin->id,
            'user_name_snapshot' => $admin->name,
            'requested_item_name' => 'Barang kebutuhan MKS',
            'qty' => 1,
            'unit_name' => 'pcs',
            'reason' => 'Tes badge sidebar',
            'status' => $status,
        ]);
    }

    AtkNeedRequest::create([
        'module' => AtkNeedRequest::MODULE_OPS,
        'user_id' => $admin->id,
        'user_name_snapshot' => $admin->name,
        'requested_item_name' => 'Barang OPS',
        'qty' => 1,
        'unit_name' => 'pcs',
        'reason' => 'Tidak masuk hitungan MKS',
        'status' => AtkNeedRequest::STATUS_PENDING,
    ]);

    actingAs($admin)
        ->get('/v2/atk-mks/admin/items')
        ->assertOk()
        ->assertSee('class="atk-mks-nav-badge atk-mks-need-request-nav-badge">2</span>', false);
});

it('lets atk mks admins approve and finalize requests with stock history', function () {
    $user = createAtkMksUser();
    $admin = createAtkMksUser('ADMIN ATK MKS');
    $item = createAtkMksItem(['name' => 'Pulpen MKS', 'stock_qty' => 6]);

    actingAs($user)
        ->withSession(['atk_mks_cart' => [$item->id => 2]])
        ->post('/v2/atk-mks/cart/submit')
        ->assertRedirect();

    $atkRequest = AtkRequest::latest('id')->firstOrFail();

    actingAs($admin)
        ->post('/v2/atk-mks/admin/requests/'.$atkRequest->id.'/approve-all')
        ->assertRedirect('/v2/atk-mks/admin/requests/'.$atkRequest->id);

    actingAs($admin)
        ->post('/v2/atk-mks/admin/requests/'.$atkRequest->id.'/finalize')
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($item->fresh()->stock_qty)->toBe(4)
        ->and($atkRequest->fresh()->status)->toBe(AtkRequest::STATUS_APPROVED);

    actingAs($admin)
        ->get('/v2/atk-mks/admin/stock-movements')
        ->assertOk()
        ->assertSee('Pulpen MKS')
        ->assertSee('6 → 4');

    $masterAdmin = User::factory()->create(['role' => UserRole::ADMIN_ATK]);

    actingAs($masterAdmin)
        ->get('/v2/atk/admin/stock-movements?module=ATK_MKS')
        ->assertOk()
        ->assertSee('Pulpen MKS')
        ->assertSee('>ATK MKS</span>', false);
});

function createAtkMksItem(array $overrides = []): AtkItem
{
    return AtkItem::create(array_merge([
        'module' => 'ATK_MKS',
        'name' => 'Barang ATK MKS',
        'unit_name' => 'pcs',
        'unit_size' => 1,
        'content_unit_name' => 'pcs',
        'stock_qty' => 10,
        'minimum_stock' => 0,
        'min_request_qty' => 1,
        'is_active' => true,
    ], $overrides));
}

function createAtkMksUser(string $role = 'USER'): User
{
    $user = User::factory()->create();

    if ($role === 'ADMIN ATK MKS') {
        UserAccessRole::create(['user_id' => $user->id, 'role' => $role]);

        return $user;
    }

    $pt = Pt::factory()->create();
    EmployeeProfile::create(['user_id' => $user->id, 'pt_id' => $pt->id]);
    DB::table('atk_mks_access_pts')->insert(['pt_id' => $pt->id]);

    return $user;
}
