<?php

namespace App\Jobs;

use App\Services\ShiftRotationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class ProcessShiftRotationsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function handle(ShiftRotationService $service): void
    {
        if (! Schema::hasTable('shift_rotations') || ! Schema::hasTable('shift_rotation_members') || ! Schema::hasTable('shift_rotation_snapshots')) {
            return;
        }

        $service->process(now('Asia/Jakarta')->startOfDay());
    }
}
