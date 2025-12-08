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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('snapshot_name');
            $table->string('snapshot_nik')->nullable();
            $table->string('snapshot_position')->nullable();
            $table->string('snapshot_division')->nullable();
            $table->string('snapshot_company')->nullable();

            $table->date('submitted_at');

            $table->string('document_path')->nullable();

            $table->decimal('amount', 16, 2);
            $table->string('amount_in_words');
            $table->text('purpose')->nullable();
            $table->string('repayment_term')->nullable();

            $table->date('disbursement_date')->nullable();
            $table->date('due_date')->nullable();

            $table->enum('payment_method', ['TUNAI', 'CICILAN', 'POTONG_GAJI'])->default('POTONG_GAJI');

            $table->enum('status', ['PENDING_HRD', 'APPROVED', 'REJECTED'])->default('PENDING_HRD');

            $table->unsignedBigInteger('hrd_id')->nullable();
            $table->timestamp('hrd_decided_at')->nullable();
            $table->text('hrd_note')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hrd_id')->references('id')->on('users')->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
