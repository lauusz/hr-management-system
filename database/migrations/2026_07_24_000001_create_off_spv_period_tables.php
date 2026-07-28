<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('off_spv_periods')) {
            Schema::create('off_spv_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->unsignedSmallInteger('period_year');
                $table->unsignedTinyInteger('period_month');
                $table->date('period_start');
                $table->date('period_end');
                $table->unsignedTinyInteger('saturday_count');
                $table->unsignedSmallInteger('base_quota');
                $table->unsignedSmallInteger('effective_quota');
                $table->timestamps();

                $table->unique(['user_id', 'period_year', 'period_month'], 'off_spv_periods_user_period_unique');
                $table->index(['period_year', 'period_month'], 'off_spv_periods_period_index');
            });
        }

        if (! Schema::hasTable('off_spv_changes')) {
            Schema::create('off_spv_changes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('off_spv_period_id')->constrained('off_spv_periods')->restrictOnDelete();
                $table->string('change_type', 50);
                $table->unsignedSmallInteger('quota_before')->nullable();
                $table->unsignedSmallInteger('quota_after');
                $table->text('reason')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();

                $table->index('created_at', 'off_spv_changes_created_at_index');
            });
        }

        if (! Schema::hasColumn('leave_requests', 'off_spv_period_id')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->foreignId('off_spv_period_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('off_spv_periods')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // No-op: production may already own these manually injected tables.
        // Removing them on rollback would destroy OFF SPV audit history.
    }
};
