<?php

namespace App\Services\Inventory;

use App\Models\InvPesonaItem;

class PesonaUnitSnapshot
{
    public function sign(InvPesonaItem $item): string
    {
        $key = config('app.key');
        if (! is_string($key) || $key === '') {
            throw new \RuntimeException('Kunci aplikasi tidak tersedia.');
        }
        $units = json_encode([(string) $item->id, $item->unit_name, $item->unit_size, $item->content_unit_name], JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $units, $key);
    }

    public function matches(InvPesonaItem $item, string $signature): bool
    {
        return hash_equals($this->sign($item), $signature);
    }
}
