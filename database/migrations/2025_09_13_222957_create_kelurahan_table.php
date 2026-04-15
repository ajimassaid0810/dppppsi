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
        Schema::create('pagoruan', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('nama');
            $table->unsignedBigInteger('kecamatan_id');
              $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('nama_pelatih')->nullable();
            $table->timestamps();

            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};
