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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('pt', 150)->nullable();
            $table->string('kategori', 50)->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('work_email', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('kewarganegaraan', 50)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('no_kartu_keluarga', 50)->nullable();
            $table->string('no_ktp', 50)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('no_rekening', 50)->nullable();
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
            $table->string('masa_kerja', 50)->nullable();
            $table->date('tgl_bergabung')->nullable();
            $table->date('tgl_akhir_percobaan')->nullable();
            $table->string('lokasi_kerja', 100)->nullable();
            $table->string('alamat_sesuai_ktp', 100)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
