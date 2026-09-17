<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'can_approve_leave')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_approve_leave')
                    ->default(false)
                    ->after('approver_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'can_approve_leave')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_approve_leave');
            });
        }
    }
};
