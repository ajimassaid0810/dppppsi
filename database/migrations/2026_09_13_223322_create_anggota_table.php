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
        Schema::create('anggota', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nama_lengkap');
    $table->string('nik', 16)->unique();
    $table->date('tanggal_lahir');
    $table->text('alamat')->nullable(); 
    $table->unsignedBigInteger('pagoruan_id')->nullable();
    $table->uuid('user_id')->nullable();
    $table->string('telepon')->nullable();
    $table->text('foto')->nullable();
    $table->text('kk')->nullable();
    $table->text('id_card')->nullable();
    $table->enum('golongan_darah', ['A', 'B', 'AB', 'O'])->nullable();
    $table->text('barcode_data')->nullable();
    $table->timestamps();
    $table->foreign('pagoruan_id')->references('id')->on('pagoruan')->onDelete('set null');
   $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
