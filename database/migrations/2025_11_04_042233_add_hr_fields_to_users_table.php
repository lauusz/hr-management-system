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
        $table->string('username')->unique()->after('name');
        $table->string('phone')->nullable()->unique()->after('username');
        $table->string('role')->default('EMPLOYEE')->after('phone');
        $table->unsignedBigInteger('division_id')->nullable()->after('role');
        $table->string('status')->default('ACTIVE')->after('division_id');
        $table->timestamp('last_login_at')->nullable()->after('status');

        $table->index(['role', 'status']);
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['username','phone','role','division_id','status','last_login_at']);
    });
}

};
