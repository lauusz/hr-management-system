<?php

use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\User;
use Tests\Support\InstallsOpsSchema;

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
