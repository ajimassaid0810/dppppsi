<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelurahan;
use Illuminate\Support\Str;

class KelurahanTelukJambeTimurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecamatanId = '0d2c22f8-b0c3-4493-af99-2f458c10caf9';

        $kelurahanList = [
            'Sirnabaya',
            'Puseurjaya',
            'Sukaluyu',
            'Pinayungan',
            'Wadas',
            'Sukarame',
            'Karangmulya',
            'Kutajaya',
            'Sukalaksana',
            'Sukamakmur',
        ];

        foreach ($kelurahanList as $nama) {
            Kelurahan::firstOrCreate(
                [
                    'nama' => $nama,
                    'kecamatan_id' => $kecamatanId,
                ],
                [
                    'id' => (string) Str::uuid(),
                ]
            );
        }
    }
}
