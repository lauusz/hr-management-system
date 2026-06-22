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
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'completion_status')) {
                $table->string('completion_status', 50)->default('OPEN')->after('notes');
            }

            if (! Schema::hasColumn('attendances', 'clock_in_photo_deleted_at')) {
                $table->timestamp('clock_in_photo_deleted_at')->nullable()->after('clock_in_photo');
            }

            if (! Schema::hasColumn('attendances', 'clock_out_photo_deleted_at')) {
                $table->timestamp('clock_out_photo_deleted_at')->nullable()->after('clock_out_photo');
            }

            if (! Schema::hasColumn('attendances', 'rejection_note')) {
                $table->text('rejection_note')->nullable()->after('approval_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'completion_status')) {
                $table->dropColumn('completion_status');
            }

            if (Schema::hasColumn('attendances', 'clock_in_photo_deleted_at')) {
                $table->dropColumn('clock_in_photo_deleted_at');
            }

            if (Schema::hasColumn('attendances', 'clock_out_photo_deleted_at')) {
                $table->dropColumn('clock_out_photo_deleted_at');
            }

            if (Schema::hasColumn('attendances', 'rejection_note')) {
                $table->dropColumn('rejection_note');
            }
        });
    }
};
