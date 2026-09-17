<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_request_days')) {
            return;
        }

        Schema::create('leave_request_days', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->date('leave_date');
            $table->string('treatment', 30)->default('NONE');
            $table->decimal('deduction_amount', 3, 2)->default(0);
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['leave_request_id', 'leave_date']);
            $table->index(['leave_date', 'treatment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_days');
    }
};
