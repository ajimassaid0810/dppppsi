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
       Schema::create('kecamatan', function (Blueprint $table) {
              $table->unsignedBigInteger('id')->primary();
            $table->string('nama');
            $table->unsignedBigInteger('kota_kab_id');
            $table->timestamps();

            $table->foreign('kota_kab_id')->references('id')->on('kota_kab')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
