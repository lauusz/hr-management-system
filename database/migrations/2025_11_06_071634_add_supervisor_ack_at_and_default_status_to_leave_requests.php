<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'supervisor_ack_at')) {
                $table->timestamp('supervisor_ack_at')->nullable()->after('notes');
            }
            $table->string('status')->default('PENDING_SUPERVISOR')->change();
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('supervisor_ack_at');
            $table->string('status')->default(null)->change();
        });
    }
};
