<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InstallsAttendanceEventSchema
{
    protected function installAttendanceEventSchema(): void
    {
        if (Schema::hasTable('attendance_events')) {
            if (! Schema::hasColumn('attendance_events', 'is_flexible')) {
                Schema::table('attendance_events', function (Blueprint $table): void {
                    $table->boolean('is_flexible')->nullable();
                });
            }

            if (! Schema::hasColumn('attendance_events', 'is_outside_reference_location')) {
                Schema::table('attendance_events', function (Blueprint $table): void {
                    $table->boolean('is_outside_reference_location')->nullable();
                });
            }

            return;
        }

        Schema::create('attendance_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 20);
            $table->string('type', 50)->default('WFO');
            $table->dateTime('occurred_at');
            $table->foreignId('location_id')->nullable()->constrained('attendance_locations')->nullOnDelete();
            $table->string('photo')->nullable();
            $table->timestamp('photo_deleted_at')->nullable();
            $table->decimal('latitude', 12, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->string('approval_status', 50)->default('PENDING');
            $table->text('rejection_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_flexible')->nullable();
            $table->boolean('is_outside_reference_location')->nullable();
            $table->unsignedBigInteger('legacy_attendance_id')->nullable();
            $table->string('legacy_side', 20)->nullable();
            $table->timestamps();

            $table->unique(['legacy_attendance_id', 'legacy_side']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['approval_status', 'occurred_at']);
        });
    }
}
