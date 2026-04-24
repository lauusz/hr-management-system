<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Attendance locations table
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 12, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius_meters')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Attendances table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('WFO'); // WFO, DINAS_LUAR
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('employee_shift_id')->nullable();
            $table->time('normal_start_time')->nullable();
            $table->time('normal_end_time')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->dateTime('clock_in_at')->nullable();
            $table->string('clock_in_photo')->nullable();
            $table->timestamp('clock_in_photo_deleted_at')->nullable();
            $table->decimal('clock_in_lat', 12, 8)->nullable();
            $table->decimal('clock_in_lng', 11, 8)->nullable();
            $table->integer('clock_in_distance_m')->nullable();
            $table->dateTime('clock_out_at')->nullable();
            $table->string('clock_out_photo')->nullable();
            $table->timestamp('clock_out_photo_deleted_at')->nullable();
            $table->decimal('clock_out_lat', 12, 8)->nullable();
            $table->decimal('clock_out_lng', 11, 8)->nullable();
            $table->integer('clock_out_distance_m')->nullable();
            $table->string('status', 255)->default('PENDING'); // PENDING, TERLAMBAT, HADIR, dll
            $table->string('approval_status', 50)->default('APPROVED'); // PENDING, APPROVED, REJECTED
            $table->text('rejection_note')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->foreign('location_id')->references('id')->on('attendance_locations')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['user_id', 'date']);
        });

        // Employee shifts table
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_locations');
    }
};
