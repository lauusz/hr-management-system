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
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->date('exit_date')->nullable()->after('tgl_akhir_percobaan');
            $table->string('exit_reason_code', 50)->nullable()->after('exit_date');
            $table->text('exit_reason_note')->nullable()->after('exit_reason_code');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn(['exit_date', 'exit_reason_code', 'exit_reason_note']);
        });

    }
};
