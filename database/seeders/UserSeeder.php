<?php

namespace Database\Seeders;

use App\Models\KotaKab;
use App\Models\Provinsi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = env('SEED_USER_PASSWORD', 'PPSI12345!');
        $roles = Role::query()->pluck('id', 'name');

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'email' => 'superadmin@ppsi.local',
                'password' => Hash::make($defaultPassword),
                'role_id' => $roles['superadmin'],
                'provinsi_id' => null,
                'kota_kab_id' => null,
                'kecamatan_id' => null,
                'pagoruan_id' => null,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin_dpp'],
            [
                'email' => 'admin.dpp@ppsi.local',
                'password' => Hash::make($defaultPassword),
                'role_id' => $roles['admin_dpp'],
                'provinsi_id' => null,
                'kota_kab_id' => null,
                'kecamatan_id' => null,
                'pagoruan_id' => null,
                'email_verified_at' => now(),
            ]
        );

        Provinsi::query()
            ->orderBy('id')
            ->each(function (Provinsi $provinsi) use ($defaultPassword, $roles): void {
                User::updateOrCreate(
                    ['username' => 'dpw_' . $provinsi->kode],
                    [
                        'email' => 'dpw.' . $provinsi->kode . '@ppsi.local',
                        'password' => Hash::make($defaultPassword),
                        'role_id' => $roles['admin_dpw'],
                        'provinsi_id' => $provinsi->id,
                        'kota_kab_id' => null,
                        'kecamatan_id' => null,
                        'pagoruan_id' => null,
                        'email_verified_at' => now(),
                    ]
                );
            });

        KotaKab::query()
            ->orderBy('id')
            ->each(function (KotaKab $kotaKab) use ($defaultPassword, $roles): void {
                User::updateOrCreate(
                    ['username' => 'dpd_' . $kotaKab->id],
                    [
                        'email' => 'dpd.' . $kotaKab->id . '@ppsi.local',
                        'password' => Hash::make($defaultPassword),
                        'role_id' => $roles['admin_dpd'],
                        'provinsi_id' => $kotaKab->provinsi_id,
                        'kota_kab_id' => $kotaKab->id,
                        'kecamatan_id' => null,
                        'pagoruan_id' => null,
                        'email_verified_at' => now(),
                    ]
                );
            });
    }
}
