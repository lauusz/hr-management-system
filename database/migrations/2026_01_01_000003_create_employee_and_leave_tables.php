<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Employee profiles table
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nik', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('status_perkawinan', 50)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('kewarganegaraan', 50)->default('INDONESIA');
            $table->string('pendidikan', 100)->nullable();
            $table->string('jurusan', 150)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('nama_rekening', 100)->nullable();
            $table->string('BPJS_ketenagakerjaan', 50)->nullable();
            $table->string('BPJS_kesehatan', 50)->nullable();
            $table->string('golongan_darah', 10)->nullable();
            $table->string('kontak_darurat')->nullable();
            $table->string('no_kontak_darurat')->nullable();
            $table->text('alamat')->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_peringatan')->nullable()->comment('Tanggal surat peringatan');
            $table->date('tanggal_keluar')->nullable()->comment('Tanggal keluar/resign');
            $table->string('alasan_keluar')->nullable();
            $table->string('exit_interview_path')->nullable();
            $table->foreignId('pt_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Leave requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // CUTI, SAKIT, IZIN, IZIN_TELAT, IZIN_PULANG_AWAL, IZIN_TENGAH_KERJA, CUTI_KHUSUS, DINAS_LUAR, OFF_SPV
            $table->string('special_leave_category', 50)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('reason')->nullable();
            $table->string('substitute_pic')->nullable();
            $table->string('substitute_phone', 50)->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_hrd')->nullable();
            $table->timestamp('supervisor_ack_at')->nullable();
            $table->string('status')->default('PENDING_SUPERVISOR'); // PENDING_SUPERVISOR, PENDING_HR, APPROVED, REJECTED, CANCEL_REQ, BATAL
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('accuracy_m', 6, 2)->nullable();
            $table->timestamp('location_captured_at')->nullable();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('employee_profiles');
    }
};
