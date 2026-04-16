<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagoruan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->nullable()->unique();
            $table->string('nama');
            $table->unsignedBigInteger('kecamatan_id');
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('nama_pimpinan')->nullable();
            $table->string('nama_pelatih')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->onDelete('cascade');
            $table->unique(['kecamatan_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagoruan');
    }
};
