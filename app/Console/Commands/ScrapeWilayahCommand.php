<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Provinsi;
use App\Models\KotaKab;
use App\Models\Kecamatan;

class ScrapeWilayahCommand extends Command
{
    protected $signature = 'scrape:wilayah';
    protected $description = 'Scrape data wilayah Indonesia dari API dan simpan ke database';

    public function handle()
    {
        $this->info('📦 Mulai scraping data provinsi...');

        $response = Http::get('https://api-regional-indonesia.vercel.app/api/provinces');
        $provinsiList = $response->json('data');

        if (!is_array($provinsiList)) {
            $this->error('❌ Gagal mengambil data provinsi.');
            return;
        }

        foreach ($provinsiList as $prov) {
            $provinsi = Provinsi::updateOrCreate(
                ['id' => $prov['id']],
                ['nama' => $prov['name']]
            );
            $this->info("→ Provinsi: {$prov['name']}");

            $kotaResponse = Http::get("https://api-regional-indonesia.vercel.app/api/cities/{$prov['id']}");
            $kotaList = $kotaResponse->json('data');

            if (!is_array($kotaList)) {
                $this->warn("   ⚠️ Gagal ambil kota/kab untuk provinsi {$prov['name']}");
                continue;
            }

            foreach ($kotaList as $kota) {
                $kotaKab = KotaKab::updateOrCreate(
                    ['id' => $kota['id']],
                    ['nama' => $kota['name'], 'provinsi_id' => $prov['id']]
                );
                $this->info("   ↳ Kota/Kab: {$kota['name']}");

                $kecResponse = Http::get("https://api-regional-indonesia.vercel.app/api/districts/{$kota['id']}");
                $kecamatanList = $kecResponse->json('data');

                if (!is_array($kecamatanList)) {
                    $this->warn("      ⚠️ Gagal ambil kecamatan untuk kota/kab {$kota['name']}");
                    continue;
                }

                foreach ($kecamatanList as $kec) {
                    Kecamatan::updateOrCreate(
                        ['id' => $kec['id']],
                        ['nama' => $kec['name'], 'kota_kab_id' => $kota['id']]
                    );
                    $this->info("      ↳ Kecamatan: {$kec['name']}");
                }
            }
        }

        $this->info('✅ Scraping selesai!');
    }
}