<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_days', function (Blueprint $table) {
            $table->boolean('is_overnight')->default(false)->after('is_holiday');
        });
    }

    public function down(): void
    {
        Schema::table('shift_days', function (Blueprint $table) {
            $table->dropColumn('is_overnight');
        });
    }
};
