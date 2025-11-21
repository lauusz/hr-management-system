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
        Schema::table('users', function (Blueprint $table) {
    // pastikan kolomnya ADA & nullable
    if (!Schema::hasColumn('users', 'division_id')) {
        $table->foreignId('division_id')->nullable()->after('role');
    }

    // index komposit buat query kenceng
    $table->index(['division_id', 'role', 'status']);

    // FK ke tabel divisions (pastikan nama tabelnya bener, lihat poin di bawah)
    $table->foreign('division_id')
          ->references('id')
          ->on('divisions')
          ->nullOnDelete();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_division_id', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropIndex(['division_id', 'role', 'status']);
        });
    }
};
