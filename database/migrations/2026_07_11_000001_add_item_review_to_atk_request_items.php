<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom review per item pada atk_request_items.
 *
 * Memungkinkan admin menyetujui / menolak item secara individual.
 * Schema ini sudah diterapkan manual di database laptop user sebelum migration ini dibuat;
 * migration ini menjaga repo tetap sinkron untuk environment lain & test sqlite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atk_request_items', function (Blueprint $table): void {
            // Cek eksplisit agar aman jika kolom sudah ditambah manual (laptop user).
            if (! Schema::hasColumn('atk_request_items', 'status')) {
                $table->string('status', 30)->default('PENDING')->after('content_unit_name_snapshot');
                $table->index('status');
            }

            if (! Schema::hasColumn('atk_request_items', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }

            if (! Schema::hasColumn('atk_request_items', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('atk_request_items', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('reviewed_at');
            }
        });

        // Index kombinasi untuk query rekap status request + filter report.
        // Dibuat terpisah agar kompatibel dengan DB yang sudah menambah kolom manual.
        try {
            Schema::table('atk_request_items', function (Blueprint $table): void {
                $table->index(['atk_request_id', 'status'], 'atk_request_items_req_status_index');
            });
        } catch (\Exception $e) {
            // Index sudah ada — aman diabaikan.
        }

        // Backfill status item agar konsisten dengan header request existing.
        // - request APPROVED -> semua item APPROVED (sebelum flow review per-item, approve = semua item disetujui)
        // - request REJECTED -> semua item REJECTED
        // - request PENDING -> item tetap PENDING (default)
        DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->where('atk_requests.status', 'APPROVED')
            ->where('atk_request_items.status', 'PENDING')
            ->update(['atk_request_items.status' => 'APPROVED']);

        DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->where('atk_requests.status', 'REJECTED')
            ->where('atk_request_items.status', 'PENDING')
            ->update(['atk_request_items.status' => 'REJECTED']);
    }

    public function down(): void
    {
        try {
            Schema::table('atk_request_items', function (Blueprint $table): void {
                $table->dropIndex('atk_request_items_req_status_index');
            });
        } catch (\Exception $e) {
            // Index mungkin tidak ada di beberapa DB — aman diabaikan.
        }

        Schema::table('atk_request_items', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'reviewed_by', 'reviewed_at', 'admin_note']);
        });
    }
};
