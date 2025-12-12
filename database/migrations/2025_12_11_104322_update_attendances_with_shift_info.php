<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            if (!Schema::hasColumn('attendances', 'employee_shift_id')) {
                $table->unsignedBigInteger('employee_shift_id')
                    ->nullable()
                    ->after('shift_id');
            }

            if (!Schema::hasColumn('attendances', 'normal_start_time')) {
                $table->time('normal_start_time')
                    ->nullable()
                    ->after('employee_shift_id');
            }

            if (!Schema::hasColumn('attendances', 'normal_end_time')) {
                $table->time('normal_end_time')
                    ->nullable()
                    ->after('normal_start_time');
            }

            if (!Schema::hasColumn('attendances', 'early_leave_minutes')) {
                $table->unsignedInteger('early_leave_minutes')
                    ->default(0)
                    ->after('late_minutes');
            }

            if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->unsignedInteger('overtime_minutes')
                    ->default(0)
                    ->after('early_leave_minutes');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'employee_shift_id')) return;

            $table->foreign('employee_shift_id')
                ->references('id')
                ->on('employee_shifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            if (Schema::hasColumn('attendances', 'employee_shift_id')) {
                $table->dropForeign(['employee_shift_id']);
                $table->dropColumn('employee_shift_id');
            }

            if (Schema::hasColumn('attendances', 'normal_start_time')) {
                $table->dropColumn('normal_start_time');
            }

            if (Schema::hasColumn('attendances', 'normal_end_time')) {
                $table->dropColumn('normal_end_time');
            }

            if (Schema::hasColumn('attendances', 'early_leave_minutes')) {
                $table->dropColumn('early_leave_minutes');
            }

            if (Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->dropColumn('overtime_minutes');
            }
        });
    }
};
