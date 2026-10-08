<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // All Locations menggunakan NULL; mengembalikan NOT NULL tidak aman.
    }
};
