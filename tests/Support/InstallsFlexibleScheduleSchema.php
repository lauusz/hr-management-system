<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InstallsFlexibleScheduleSchema
{
    protected function installFlexibleScheduleSchema(): void
    {
        if (! Schema::hasColumn('employee_shifts', 'is_flexible')) {
            Schema::table('employee_shifts', function (Blueprint $table): void {
                $table->boolean('is_flexible')->default(false);
            });
        }
    }
}
