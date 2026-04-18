<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_pengajuan', 32)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_identitas', ['ktp', 'kartu_pelajar'])->default('ktp');
            $table->string('nomor_identitas', 50);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('telepon', 50);
            $table->string('email')->nullable();
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O'])->nullable();
            $table->unsignedBigInteger('provinsi_id');
            $table->unsignedBigInteger('kota_kab_id');
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('pagoruan_id')->nullable();
            $table->string('foto_path');
            $table->string('kk_path');
            $table->string('dokumen_identitas_path');
            $table->enum('status', ['diajukan', 'ditinjau', 'dikonversi', 'ditolak'])->default('diajukan');
            $table->text('catatan_admin')->nullable();
            $table->uuid('anggota_id')->nullable();
            $table->uuid('ditinjau_oleh')->nullable();
            $table->timestamp('ditinjau_at')->nullable();
            $table->timestamp('dikonversi_at')->nullable();
            $table->string('submitted_ip', 45)->nullable();
            $table->timestamps();

            $table->foreign('provinsi_id')->references('id')->on('provinsi')->cascadeOnDelete();
            $table->foreign('kota_kab_id')->references('id')->on('kota_kab')->cascadeOnDelete();
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->nullOnDelete();
            $table->foreign('pagoruan_id')->references('id')->on('pagoruan')->nullOnDelete();
            $table->foreign('anggota_id')->references('id')->on('anggota')->nullOnDelete();
            $table->foreign('ditinjau_oleh')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'created_at']);
            $table->index(['kota_kab_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_anggota');
    }
};
