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
        Schema::table('employee_shifts', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_shifts', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('shift_id');
                $table->foreign('location_id')->references('id')->on('attendance_locations')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('employee_shifts', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
        });
    }
};
