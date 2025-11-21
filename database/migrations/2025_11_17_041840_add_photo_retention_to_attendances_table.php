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
        Schema::table('attendances', function (Blueprint $table) {
            Schema::table('attendances', function (Blueprint $table) {
            $table->string('clock_in_photo')->nullable()->change();

            $table->timestamp('clock_in_photo_deleted_at')->nullable()->after('clock_in_photo');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('clock_in_photo_deleted_at');

            $table->string('clock_in_photo')->nullable()->change();
        });
    }
};
