<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WilayahKarawangSeeder extends Seeder
{
    public function run(): void
    {
        // Provinsi
        $provinsiId = Str::uuid();
        DB::table('provinsi')->insert([
            'id' => $provinsiId,
            'nama' => 'Jawa Barat',
        ]);

        // Kabupaten/Kota
        $kotaId = Str::uuid();
        DB::table('kota_kab')->insert([
            'id' => $kotaId,
            'nama' => 'Karawang',
            'provinsi_id' => $provinsiId,
        ]);

        // Kecamatan
        $kecamatan = [
            'Karawang Barat',
            'Karawang Timur',
            'Telukjambe Timur',
            'Cikampek',
            'Klari',
        ];

        $kecamatanIds = [];
        foreach ($kecamatan as $nama) {
            $id = Str::uuid();
            $kecamatanIds[] = $id;
            DB::table('kecamatan')->insert([
                'id' => $id,
                'nama' => $nama,
                'kota_kab_id' => $kotaId,
            ]);
        }

        // Cabang PPSI
        $cabang = [
            ['nama_cabang' => 'PPSI Karawang Barat', 'nama_pelatih' => 'Pelatih A'],
            ['nama_cabang' => 'PPSI Karawang Timur', 'nama_pelatih' => 'Pelatih B'],
            ['nama_cabang' => 'PPSI Telukjambe', 'nama_pelatih' => 'Pelatih C'],
        ];

        foreach ($cabang as $item) {
            DB::table('cabang')->insert([
                'id' => Str::uuid(),
                'nama_cabang' => $item['nama_cabang'],
                'alamat' => 'Karawang, Jawa Barat',
                'telepon' => null,
                'nama_pelatih' => $item['nama_pelatih'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}