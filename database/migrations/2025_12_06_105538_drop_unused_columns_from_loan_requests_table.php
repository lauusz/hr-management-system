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
        Schema::table('loan_requests', function (Blueprint $table) {
            if (Schema::hasColumn('loan_requests', 'amount_in_words')) {
                $table->dropColumn('amount_in_words');
            }

            if (Schema::hasColumn('loan_requests', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_requests', 'amount_in_words')) {
                $table->string('amount_in_words')->nullable();
            }

            if (!Schema::hasColumn('loan_requests', 'due_date')) {
                $table->date('due_date')->nullable();
            }
        });

    }
};
