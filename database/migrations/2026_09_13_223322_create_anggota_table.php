<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_anggota', 20)->unique();
            $table->unsignedInteger('nomor_urut_dpd');
            $table->unsignedBigInteger('kota_kab_id');
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('pagoruan_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('dibuat_oleh')->nullable();
            $table->uuid('diverifikasi_dpw_oleh')->nullable();
            $table->uuid('disetujui_dpp_oleh')->nullable();

            $table->string('nama_lengkap');
            $table->enum('jenis_identitas', ['ktp', 'kartu_pelajar'])->default('ktp');
            $table->string('nomor_identitas', 50)->nullable();
            $table->date('tanggal_lahir');
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('kk_path')->nullable();
            $table->string('dokumen_identitas_path')->nullable();
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O'])->nullable();
            $table->date('tanggal_gabung')->nullable();
            $table->date('masa_berlaku_sampai')->nullable();

            $table->enum('status_pengajuan', [
                'Pengajuan_anggota',
                'draft_dpd',
                'diajukan_ke_dpw',
                'diverifikasi_dpw',
                'diajukan_ke_dpp',
                'disetujui_dpp',
                'ditolak',
            ])->default('draft_dpd');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('diajukan_ke_dpw_at')->nullable();
            $table->timestamp('diverifikasi_dpw_at')->nullable();
            $table->timestamp('diajukan_ke_dpp_at')->nullable();
            $table->timestamp('disetujui_dpp_at')->nullable();
            $table->timestamp('ditolak_at')->nullable();
            $table->text('barcode_data')->nullable();
            $table->timestamps();

            $table->foreign('kota_kab_id')->references('id')->on('kota_kab')->onDelete('cascade');
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->nullOnDelete();
            $table->foreign('pagoruan_id')->references('id')->on('pagoruan')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('diverifikasi_dpw_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('disetujui_dpp_oleh')->references('id')->on('users')->nullOnDelete();

            $table->unique(['kota_kab_id', 'nomor_urut_dpd']);
            $table->index(['status_pengajuan', 'kota_kab_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
