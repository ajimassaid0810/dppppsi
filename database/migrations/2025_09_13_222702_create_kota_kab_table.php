<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kota_kab', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 2);
            $table->string('nama');
            $table->unsignedBigInteger('provinsi_id');
            $table->string('nama_ketua_dpd')->nullable();
            $table->string('url_ttd_ketua')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('provinsi_id')->references('id')->on('provinsi')->onDelete('cascade');
            $table->unique(['provinsi_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kota_kab');
    }
};
