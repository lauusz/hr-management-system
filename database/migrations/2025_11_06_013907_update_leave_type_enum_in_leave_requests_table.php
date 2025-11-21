<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_requests')->where('type', 'LEAVE')->update(['type' => 'CUTI']);
        DB::table('leave_requests')->where('type', 'SICK')->update(['type' => 'SAKIT']);
        DB::table('leave_requests')->where('type', 'OTHER')->update(['type' => 'IZIN_TENGAH_KERJA']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `leave_requests` MODIFY `type` VARCHAR(255) NULL DEFAULT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE leave_requests ALTER COLUMN type DROP NOT NULL");
            DB::statement("ALTER TABLE leave_requests ALTER COLUMN type DROP DEFAULT");
        } else {
            Schema::table('leave_requests', function (Blueprint $t) {
                $t->string('type')->nullable()->default(null)->change();
            });
        }

        $allowed = [
            'IZIN_TELAT',
            'IZIN_PULANG_AWAL',
            'IZIN_TENGAH_KERJA',
            'CUTI',
            'SAKIT',
            'IZIN_KELUARGA_SAKIT',
            'CUTI_KHUSUS',
            'DINAS_LUAR',
        ];
        $inList = "'" . implode("','", $allowed) . "'";

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `leave_requests`
                    ADD CONSTRAINT `chk_leave_requests_type`
                    CHECK (`type` IS NULL OR `type` IN ($inList))");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE leave_requests
                    ADD CONSTRAINT chk_leave_requests_type
                    CHECK (type IS NULL OR type IN ($inList))");
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `leave_requests` DROP CHECK `chk_leave_requests_type`");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS chk_leave_requests_type");
            }
        } catch (\Throwable $e) {
            //
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `leave_requests` MODIFY `type` VARCHAR(255) NOT NULL DEFAULT 'IZIN'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE leave_requests ALTER COLUMN type SET DEFAULT 'IZIN'");
            DB::statement("ALTER TABLE leave_requests ALTER COLUMN type SET NOT NULL");
        } else {
            Schema::table('leave_requests', function (Blueprint $t) {
                $t->string('type')->default('IZIN')->nullable(false)->change();
            });
        }

        DB::table('leave_requests')->where('type', 'CUTI')->update(['type' => 'LEAVE']);
        DB::table('leave_requests')->where('type', 'SAKIT')->update(['type' => 'SICK']);
        DB::table('leave_requests')->where('type', 'IZIN_TENGAH_KERJA')->update(['type' => 'OTHER']);
    }
};
