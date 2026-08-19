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

        if (! Schema::hasColumn('atk_need_requests', 'module')) {
            Schema::table('atk_need_requests', function (Blueprint $table): void {
                $table->string('module', 10)->default('ATK')->index();
            });
        }

        if (! Schema::hasTable('ops_access_divisions')) {
            Schema::create('ops_access_divisions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('division_id')->unique();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('atk_mks_access_divisions')) {
            Schema::create('atk_mks_access_divisions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('division_id')->unique();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('atk_mks_access_pts')) {
            Schema::create('atk_mks_access_pts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('pt_id')->unique();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }
}
