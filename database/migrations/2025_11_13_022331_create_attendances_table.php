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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('attendance_locations')->nullOnDelete();

            // clock-in
            $table->dateTime('clock_in_at')->nullable();
            $table->string('clock_in_photo')->nullable();
            $table->decimal('clock_in_lat', 12, 8)->nullable(); // lattitude
            $table->decimal('clock_in_lng', 11, 8)->nullable(); // longitude
            $table->integer('clock_in_distance_m')->nullable();

            // clock-out
            $table->dateTime('clock_out_at')->nullable();
            $table->decimal('clock_out_lat', 12, 8)->nullable(); // lattitude
            $table->decimal('clock_out_lng', 11, 8)->nullable(); // longitude
            $table->integer('clock_out_distance_m')->nullable();

            // Daily Status
            $table->string('status')->default('PENDING'); // ON_TIME,LATE, ABSENT, OUT_OF_RADIUS
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date'], 'unique_attendance_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
