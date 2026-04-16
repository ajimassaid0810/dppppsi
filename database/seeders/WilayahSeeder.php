<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\KotaKab;
use App\Models\Provinsi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class WilayahSeeder extends Seeder
{
    private const BASE_URL = 'https://wilayah.id/api';

    public function run(): void
    {
        $provinsiList = $this->fetchJson('/provinces.json');

        foreach ($provinsiList as $provinsi) {
            $provinsiCode = $this->normalizeCode($provinsi['code']);

            Provinsi::updateOrCreate(
                ['id' => $provinsiCode],
                [
                    'kode' => $this->extractLastSegment($provinsi['code']),
                    'nama' => $provinsi['name'],
                    'is_active' => true,
                ]
            );

            $regencies = $this->fetchJson('/regencies/' . $provinsi['code'] . '.json');

            foreach ($regencies as $regency) {
                $regencyCode = $this->normalizeCode($regency['code']);

                KotaKab::updateOrCreate(
                    ['id' => $regencyCode],
                    [
                        'kode' => $this->extractLastSegment($regency['code']),
                        'nama' => $regency['name'],
                        'provinsi_id' => $provinsiCode,
                        'is_active' => true,
                    ]
                );

                $districts = $this->fetchJson('/districts/' . $regency['code'] . '.json');

                foreach ($districts as $district) {
                    Kecamatan::updateOrCreate(
                        ['id' => $this->normalizeCode($district['code'])],
                        [
                            'kode' => $district['code'],
                            'nama' => $district['name'],
                            'kota_kab_id' => $regencyCode,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fetchJson(string $path): array
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->retry(3, 500)
            ->timeout(60)
            ->get($path)
            ->throw();

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    private function normalizeCode(string $code): int
    {
        return (int) str_replace('.', '', $code);
    }

    private function extractLastSegment(string $code): string
    {
        $segments = explode('.', $code);

        return end($segments);
    }
}
