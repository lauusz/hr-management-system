<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_ops_schedule_member')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_ops_schedule_member')
                    ->default(false)
                    ->after('status');
            });
        }

        if (Schema::hasTable('employee_shift_changes')) {
            return;
        }

        Schema::create('employee_shift_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()
                ->constrained('attendance_locations')->nullOnDelete();
            $table->date('effective_date');
            $table->string('status', 20)->default('PENDING');
            $table->unsignedTinyInteger('pending_slot')->nullable()->default(1);
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('shift_name_snapshot');
            $table->string('location_name_snapshot');
            $table->dateTime('applied_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'pending_slot'],
                'employee_shift_changes_unique_pending_user'
            );
            $table->index(
                ['user_id', 'effective_date'],
                'employee_shift_changes_user_effective_index'
            );
            $table->index(
                ['status', 'effective_date'],
                'employee_shift_changes_status_effective_index'
            );
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE employee_shift_changes
                 ADD CONSTRAINT employee_shift_changes_status_check
                 CHECK (
                     (status = 'PENDING' AND pending_slot = 1)
                     OR
                     (status IN ('APPLIED', 'CANCELLED') AND pending_slot IS NULL)
                 )"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_changes');

        if (Schema::hasColumn('users', 'is_ops_schedule_member')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_ops_schedule_member');
            });
        }
    }
};
