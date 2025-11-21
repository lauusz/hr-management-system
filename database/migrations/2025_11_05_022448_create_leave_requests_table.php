<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type')->default('IZIN'); // IZIN, SAKIT, LAINNYA (bebas)
            $t->date('start_date');
            $t->date('end_date');
            $t->text('reason')->nullable();
            $t->string('status')->default('PENDING'); // PENDING|APPROVED|REJECTED
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('leave_requests'); }
};
