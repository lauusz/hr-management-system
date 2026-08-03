<?php

use App\Models\AtkItem;
use App\Models\AtkRequest;
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

it('hides the ops access card from users without ops access', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('v2.access'))
        ->assertOk()
        ->assertDontSee('Kebutuhan Operasional');
});

it('allows selected users to see and open the ops module', function () {
    $user = User::factory()->create();
    UserAccessRole::create(['user_id' => $user->id, 'role' => 'OPS']);

    actingAs($user)
        ->get(route('v2.access'))
        ->assertOk()
        ->assertSee('Kebutuhan Operasional')
        ->assertSee('/v2/ops');

    actingAs($user)
        ->get('/v2/ops')
        ->assertOk();
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
