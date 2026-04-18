<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\SimpleXlsxWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ExportRegionalCredentialsCommand extends Command
{
    protected $signature = 'users:export-regional-credentials {--path= : Path output XLSX, default ke storage/app/exports}';

    protected $description = 'Export username regional DPW, DPD, dan DPC ke file XLSX beserta password yang masih cocok dengan seed password.';

    public function handle(): int
    {
        $outputPath = $this->resolveOutputPath((string) $this->option('path'));
        $seedPassword = (string) env('SEED_USER_PASSWORD', 'PPSI12345!');

        $users = User::query()
            ->with(['role', 'provinsi', 'kotaKab', 'kecamatan'])
            ->whereHas('role', function ($query): void {
                $query->whereIn('name', ['admin_dpw', 'admin_dpd', 'admin_dpc']);
            })
            ->get()
            ->sortBy(fn (User $user) => sprintf('%02d-%s', $this->roleOrder($user->role?->name), $user->username))
            ->values();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user regional admin_dpw/admin_dpd/admin_dpc yang ditemukan.');

            return self::SUCCESS;
        }

        $rows = $this->buildRows($users, $seedPassword);

        SimpleXlsxWriter::store($outputPath, 'Kredensial Regional', $rows);

        $knownPasswordCount = $users->filter(fn (User $user) => Hash::check($seedPassword, (string) $user->password))->count();

        $this->info('Export XLSX berhasil dibuat.');
        $this->line('Lokasi: ' . $outputPath);
        $this->line('Total akun: ' . $users->count());
        $this->line('Password terdeteksi cocok dengan seed: ' . $knownPasswordCount);
        $this->line('Password kosong berarti akun tersebut sudah diubah dan tidak bisa diekspor dari hash.');

        return self::SUCCESS;
    }

    private function resolveOutputPath(string $optionPath): string
    {
        if ($optionPath !== '') {
            return str_starts_with($optionPath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $optionPath)
                ? $optionPath
                : base_path($optionPath);
        }

        $filename = 'regional-credentials-' . now()->format('Ymd-His') . '.xlsx';

        return storage_path('app/exports/' . $filename);
    }

    private function buildRows(Collection $users, string $seedPassword): array
    {
        $rows = [[
            'Role',
            'Username',
            'Password',
            'Email',
            'Provinsi',
            'Kota/Kab',
            'Kecamatan',
            'Status Password',
            'Catatan',
        ]];

        foreach ($users as $user) {
            $isSeedPassword = Hash::check($seedPassword, (string) $user->password);

            $rows[] = [
                $user->role?->display_name ?? $user->role?->name ?? '-',
                $user->username,
                $isSeedPassword ? $seedPassword : '',
                $user->email ?? '',
                $user->provinsi?->nama ?? '',
                $user->kotaKab?->nama ?? '',
                $user->kecamatan?->nama ?? '',
                $isSeedPassword ? 'Cocok dengan SEED_USER_PASSWORD' : 'Tidak dapat diekspor',
                $isSeedPassword
                    ? 'Password masih sesuai default seed.'
                    : 'Password tersimpan sebagai hash dan tampaknya sudah berbeda dari seed password.',
            ];
        }

        return $rows;
    }

    private function roleOrder(?string $role): int
    {
        return match ($role) {
            'admin_dpw' => 1,
            'admin_dpd' => 2,
            'admin_dpc' => 3,
            default => 99,
        };
    }
}
