<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InstallsOpsSchema
{
    protected function installOpsSchema(): void
    {
        if (! Schema::hasColumn('atk_items', 'module')) {
            Schema::table('atk_items', function (Blueprint $table): void {
                $table->string('module', 10)->default('ATK')->index();
                $table->timestamp('deleted_at')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->text('deletion_note')->nullable();
            });
        }

        if (! Schema::hasColumn('atk_requests', 'module')) {
            Schema::table('atk_requests', function (Blueprint $table): void {
                $table->string('module', 10)->default('ATK')->index();
            });
        }
    }
}
