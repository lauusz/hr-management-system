<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERINGATAN: Migration ini hanya untuk sinkronisasi schema dengan database production.
     * JANGAN dijalankan di production jika database sudah berjalan dengan baik.
     * Migration ini dirancang untuk environment baru (development, test, staging baru).
     *
     * Strategy:
     * - Tabel yang belum ada -> create dengan struktur production.
     * - Tabel yang sudah ada tapi punya kolom lama (struktur salah) -> drop & recreate.
     * - Tabel yang sudah ada dan sesuai -> skip (tidak ada perubahan).
     */
    public function up(): void
    {
        $this->fixOvertimeRequests();
        $this->fixPayslips();
        $this->fixEmployeeProfiles();
        $this->fixLoanRepayments();
        $this->fixEmployeeShifts();
        $this->fixPositions();
        $this->fixShiftDays();
        $this->fixPts();
    }

    /**
     * Reverse the migrations.
     *
     * Perubahan ini tidak bisa di-reverse secara sempurna karena melibatkan
     * drop & recreate tabel. Method down() hanya akan drop tabel yang dibuat.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('loan_repayments');
    }

    // =================================================================
    // 1. OVERTIME REQUESTS
    // =================================================================
    private function fixOvertimeRequests(): void
    {
        if (! Schema::hasTable('overtime_requests')) {
            $this->createOvertimeRequests();

            return;
        }

        // If old column 'date' exists (from old wrong migration), recreate
        if (Schema::hasColumn('overtime_requests', 'date')) {
            Schema::dropIfExists('overtime_requests');
            $this->createOvertimeRequests();
        }
    }

    private function createOvertimeRequests(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->default(0);
            $table->text('description');
            $table->enum('status', ['PENDING_SUPERVISOR', 'APPROVED_SUPERVISOR', 'APPROVED_HRD', 'REJECTED', 'CANCELLED'])
                ->default('PENDING_SUPERVISOR');
            $table->unsignedBigInteger('approved_by_supervisor_id')->nullable();
            $table->timestamp('approved_by_supervisor_at')->nullable();
            $table->unsignedBigInteger('approved_by_hrd_id')->nullable();
            $table->timestamp('approved_by_hrd_at')->nullable();
            $table->unsignedBigInteger('rejected_by_id')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->foreign('approved_by_supervisor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by_hrd_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    // =================================================================
    // 2. PAYSLIPS
    // =================================================================
    private function fixPayslips(): void
    {
        if (! Schema::hasTable('payslips')) {
            $this->createPayslips();

            return;
        }

        // If old column 'month' exists (from old wrong migration), recreate
        if (Schema::hasColumn('payslips', 'month')) {
            Schema::dropIfExists('payslips');
            $this->createPayslips();
        }
    }

    private function createPayslips(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ID Karyawan yang menerima gaji');
            $table->tinyInteger('period_month')->comment('Bulan (1-12)');
            $table->year('period_year')->comment('Tahun (contoh: 2026)');
            $table->decimal('gaji_pokok', 16, 2)->default(0.00);
            $table->decimal('tunjangan_jabatan', 16, 2)->default(0.00);
            $table->decimal('tunjangan_makan', 16, 2)->default(0.00);
            $table->decimal('fee_marketing', 16, 2)->default(0.00);
            $table->decimal('bonus_bulanan', 16, 2)->nullable();
            $table->decimal('tunjangan_telekomunikasi', 16, 2)->default(0.00);
            $table->decimal('tunjangan_lainnya', 16, 2)->nullable();
            $table->decimal('tunjangan_penempatan', 16, 2)->default(0.00);
            $table->decimal('tunjangan_asuransi', 16, 2)->default(0.00);
            $table->decimal('tunjangan_kelancaran', 16, 2)->default(0.00);
            $table->decimal('pendapatan_lain', 16, 2)->default(0.00);
            $table->decimal('tunjangan_transportasi', 16, 2)->default(0.00);
            $table->decimal('lembur', 16, 2)->default(0.00);
            $table->decimal('thr', 16, 2)->nullable();
            $table->decimal('bonus', 16, 2)->nullable();
            $table->decimal('potongan_bpjs_tk', 16, 2)->default(0.00);
            $table->decimal('potongan_pph21', 16, 2)->default(0.00);
            $table->decimal('potongan_hutang', 16, 2)->default(0.00);
            $table->decimal('potongan_bpjs_kes', 16, 2)->default(0.00);
            $table->decimal('potongan_terlambat', 16, 2)->default(0.00);
            $table->decimal('total_pendapatan', 16, 2)->default(0.00);
            $table->decimal('total_potongan', 16, 2)->default(0.00);
            $table->decimal('gaji_bersih', 16, 2)->default(0.00);
            $table->string('sisa_utang')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED'])->default('DRAFT')
                ->comment('DRAFT: Cuma HR yg lihat, PUBLISHED: Karyawan bisa lihat/download');
            $table->unsignedBigInteger('created_by')->nullable()->comment('HRD yang input data');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    // =================================================================
    // 3. EMPLOYEE PROFILES
    // =================================================================
    private function fixEmployeeProfiles(): void
    {
        if (! Schema::hasTable('employee_profiles')) {
            $this->createEmployeeProfiles();

            return;
        }

        // If old column 'tanggal_lahir' exists (from old wrong migration), recreate
        if (Schema::hasColumn('employee_profiles', 'tanggal_lahir')) {
            Schema::dropIfExists('employee_profiles');
            $this->createEmployeeProfiles();
        }
    }

    private function createEmployeeProfiles(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pt_id')->nullable()->constrained('pts')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('kategori', ['TETAP', 'KONTRAK', 'MAGANG'])->nullable()
                ->comment('Kategori karyawan: TETAP, KONTRAK, MAGANG');
            $table->string('nik', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('kewarganegaraan', 50)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('path_kartu_keluarga', 255)->nullable();
            $table->string('path_ktp', 255)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('no_kartu_keluarga', 20)->nullable();
            $table->string('no_ktp', 20)->nullable();
            $table->string('pendidikan', 100)->nullable();
            $table->string('golongan_darah', 50)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->text('alamat1')->nullable();
            $table->text('alamat2')->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kab_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('badge_id', 50)->nullable();
            $table->string('pin', 50)->nullable();
            $table->string('ptkp', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('nomor_npwp', 50)->nullable();
            $table->string('bpjs_tk', 50)->nullable();
            $table->string('nomor_bpjs_kesehatan', 50)->nullable();
            $table->string('kelas_bpjs', 50)->nullable();
            $table->date('tgl_bergabung')->nullable()->comment('Canonical join date field used by the application');
            $table->date('tgl_akhir_percobaan')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('exit_reason_code', 50)->nullable();
            $table->text('exit_reason_note')->nullable();
            $table->string('exit_document_path', 255)->nullable();
            $table->string('lokasi_kerja', 100)->nullable();
            $table->string('alamat_sesuai_ktp', 100)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    // =================================================================
    // 4. LOAN REPAYMENTS
    // =================================================================
    private function fixLoanRepayments(): void
    {
        if (! Schema::hasTable('loan_repayments')) {
            $this->createLoanRepayments();

            return;
        }

        // If old column 'installment_number' exists (from old wrong migration), recreate
        if (Schema::hasColumn('loan_repayments', 'installment_number')) {
            Schema::dropIfExists('loan_repayments');
            $this->createLoanRepayments();
        }
    }

    private function createLoanRepayments(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_at');
            $table->decimal('amount', 16, 2);
            $table->enum('method', ['TUNAI', 'TRANSFER', 'POTONG_GAJI']);
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    // =================================================================
    // 5. EMPLOYEE SHIFTS
    // =================================================================
    private function fixEmployeeShifts(): void
    {
        if (Schema::hasColumn('employee_shifts', 'effective_date')) {
            Schema::table('employee_shifts', function (Blueprint $table) {
                $table->dropColumn('effective_date');
            });
        }

        if (Schema::hasColumn('employee_shifts', 'end_date')) {
            Schema::table('employee_shifts', function (Blueprint $table) {
                $table->dropColumn('end_date');
            });
        }
    }

    // =================================================================
    // 6. POSITIONS
    // =================================================================
    private function fixPositions(): void
    {
        if (! Schema::hasColumn('positions', 'division_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->unsignedBigInteger('division_id')->nullable()->after('id');
                $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('positions', 'is_active')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('name');
            });
        }
    }

    // =================================================================
    // 7. SHIFT DAYS
    // =================================================================
    private function fixShiftDays(): void
    {
        if (! Schema::hasColumn('shift_days', 'note')) {
            Schema::table('shift_days', function (Blueprint $table) {
                $table->string('note', 255)->nullable()->after('is_holiday');
            });
        }
    }

    // =================================================================
    // 8. PTS
    // =================================================================
    private function fixPts(): void
    {
        if (Schema::hasColumn('pts', 'address')) {
            Schema::table('pts', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }

        if (Schema::hasColumn('pts', 'phone')) {
            Schema::table('pts', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasColumn('pts', 'is_active')) {
            Schema::table('pts', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
