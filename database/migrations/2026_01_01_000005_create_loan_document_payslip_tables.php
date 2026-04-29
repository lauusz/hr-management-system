<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Loan requests table
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('snapshot_name');
            $table->string('snapshot_nik')->nullable();
            $table->string('snapshot_position')->nullable();
            $table->string('snapshot_division')->nullable();
            $table->string('snapshot_company')->nullable();
            $table->date('submitted_at');
            $table->string('document_path')->nullable();
            $table->decimal('amount', 16, 2);
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('monthly_installment', 16, 2)->nullable();
            $table->string('repayment_term')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->string('payment_method', 50)->default('POTONG_GAJI'); // TUNAI, CICILAN, POTONG_GAJI
            $table->string('status', 50)->default('PENDING_HRD'); // PENDING_HRD, APPROVED, REJECTED, LUNAS, CANCELED
            $table->unsignedBigInteger('hrd_id')->nullable();
            $table->timestamp('hrd_decided_at')->nullable();
            $table->text('hrd_note')->nullable()->comment('Notes for HR internal');
            $table->timestamps();

            $table->foreign('hrd_id')->references('id')->on('users')->nullOnDelete();
        });

        // Loan repayments table
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->string('status', 50)->default('PENDING'); // PENDING, PAID, OVERDUE
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Employee documents table
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // KTP, KK, IJAZAH, CONTRACT, dll
            $table->string('title')->nullable();
            $table->string('path');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        // Payslips table
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_name')->nullable();
            $table->string('employee_id', 50)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('pt_name', 150)->nullable();
            $table->string('department', 100)->nullable();
            $table->integer('month')->default(1);
            $table->integer('year')->default(2024);
            $table->string('base_salary', 50)->nullable();
            $table->string('allowances')->nullable()->comment('JSON string');
            $table->string('deductions')->nullable()->comment('JSON string');
            $table->string('prorata_adjustments')->nullable()->comment('JSON string');
            $table->string('total_salary', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('file_path')->nullable();
            $table->string('email_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });

        // Overtime requests table
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('reason')->nullable();
            $table->string('status', 50)->default('PENDING_SUPERVISOR'); // PENDING_SUPERVISOR, PENDING_HR, APPROVED, REJECTED
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loan_requests');
    }
};
