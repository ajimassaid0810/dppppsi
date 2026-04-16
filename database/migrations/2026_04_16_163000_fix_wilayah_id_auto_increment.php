<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE provinsi MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE kota_kab MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE kecamatan MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE provinsi MODIFY id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE kota_kab MODIFY id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE kecamatan MODIFY id BIGINT UNSIGNED NOT NULL');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
