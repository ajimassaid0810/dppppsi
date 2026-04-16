<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh konfigurasi dan data aplikasi.',
            ],
            [
                'name' => 'admin_dpp',
                'display_name' => 'Admin DPP',
                'description' => 'Mengelola verifikasi dan administrasi di tingkat DPP.',
            ],
            [
                'name' => 'admin_dpw',
                'display_name' => 'Admin DPW',
                'description' => 'Mengelola data dan verifikasi di tingkat provinsi/DPW.',
            ],
            [
                'name' => 'admin_dpd',
                'display_name' => 'Admin DPD',
                'description' => 'Mengelola data dan pengajuan anggota di tingkat kota/kabupaten.',
            ],
            [
                'name' => 'admin_dpc',
                'display_name' => 'Admin DPC',
                'description' => 'Mengelola data dan operasional di tingkat kecamatan.',
            ],
            [
                'name' => 'anggota',
                'display_name' => 'Anggota',
                'description' => 'Akun anggota untuk akses mandiri bila diaktifkan.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role + ['is_active' => true]
            );
        }
    }
}
