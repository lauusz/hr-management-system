<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_request_attachments')) {
            return;
        }

        Schema::create('leave_request_attachments', function (Blueprint $table) {
            // Samakan collation dengan tabel leave_requests (utf8mb4_unicode_ci)
            // agar join perbandingan file_name tidak kena error illegal mix of collations.
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('original_name')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('leave_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_attachments');
    }
};
