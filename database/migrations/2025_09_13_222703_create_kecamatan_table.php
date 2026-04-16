<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->nullable();
            $table->string('nama');
            $table->unsignedBigInteger('kota_kab_id');
            $table->string('nama_ketua_dpc')->nullable();
            $table->text('alamat_sekretariat')->nullable();
            $table->string('telepon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('kota_kab_id')->references('id')->on('kota_kab')->onDelete('cascade');
            $table->unique(['kota_kab_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
