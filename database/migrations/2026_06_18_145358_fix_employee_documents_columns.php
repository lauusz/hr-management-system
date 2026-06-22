<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            // Sinkronkan schema dengan database aktual & model.
            // Kolom lama 'path' diganti menjadi 'file_path' agar sesuai model dan backup SQL.
            if (Schema::hasColumn('employee_documents', 'path') && ! Schema::hasColumn('employee_documents', 'file_path')) {
                $table->renameColumn('path', 'file_path');
            } elseif (! Schema::hasColumn('employee_documents', 'file_path')) {
                $table->string('file_path')->after('title')->nullable();
            }

            if (! Schema::hasColumn('employee_documents', 'effective_date')) {
                $table->date('effective_date')->nullable()->after('file_path');
            }

            if (! Schema::hasColumn('employee_documents', 'expired_date')) {
                $table->date('expired_date')->nullable()->after('effective_date');
            }

            if (! Schema::hasColumn('employee_documents', 'notes')) {
                $table->text('notes')->nullable()->after('expired_date');
            }

            if (! Schema::hasColumn('employee_documents', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            if (Schema::hasColumn('employee_documents', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('employee_documents', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('employee_documents', 'expired_date')) {
                $table->dropColumn('expired_date');
            }

            if (Schema::hasColumn('employee_documents', 'effective_date')) {
                $table->dropColumn('effective_date');
            }

            if (Schema::hasColumn('employee_documents', 'file_path') && ! Schema::hasColumn('employee_documents', 'path')) {
                $table->renameColumn('file_path', 'path');
            } elseif (! Schema::hasColumn('employee_documents', 'path')) {
                $table->string('path')->after('title');
            }
        });
    }
};
