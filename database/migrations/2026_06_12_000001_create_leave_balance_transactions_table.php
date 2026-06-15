<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type'); // OPENING_BALANCE, DEDUCT, REFUND, ADJUSTMENT
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('balance_before', 8, 2)->default(0);
            $table->decimal('balance_after', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['leave_request_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_transactions');
    }
};
