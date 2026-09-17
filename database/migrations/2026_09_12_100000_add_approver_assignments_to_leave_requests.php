<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'approver_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('approver_id')
                    ->nullable()
                    ->after('manager_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('leave_request_approval_actions')) {
            Schema::create('leave_request_approval_actions', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->id();
                $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_name');
                $table->string('actor_role', 50)->nullable();
                $table->string('capacity', 30);
                $table->string('action', 30);
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('acted_at');
                $table->timestamps();

                $table->index(['leave_request_id', 'acted_at'], 'lr_approval_actions_request_time_index');
                $table->index(['actor_id', 'acted_at'], 'lr_approval_actions_actor_time_index');
                $table->index(['capacity', 'action'], 'lr_approval_actions_capacity_action_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_approval_actions');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'approver_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('approver_id');
            });
        }
    }
};
