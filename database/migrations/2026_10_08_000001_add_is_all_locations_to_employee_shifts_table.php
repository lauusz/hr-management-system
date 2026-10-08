<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->boolean('is_all_locations')->default(false)->after('location_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->dropColumn('is_all_locations');
        });
    }
};
