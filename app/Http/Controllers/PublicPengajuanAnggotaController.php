<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicAnggotaSubmissionRequest;
use App\Models\Anggota;
use App\Models\PengajuanAnggota;
use App\Models\Provinsi;
use App\Models\PublicSiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicPengajuanAnggotaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Public/PendaftaranAnggota', [
            'site' => $this->serializeSiteSettings(PublicSiteSetting::query()->first()),
            'options' => [
                'provinsi' => Provinsi::query()->orderBy('nama')->get(['id', 'kode', 'nama']),
                'jenis_identitas' => $this->selectOptionsFromMap(Anggota::JENIS_IDENTITAS_OPTIONS),
                'golongan_darah' => $this->selectOptionsFromMap(Anggota::GOLONGAN_DARAH_OPTIONS),
            ],
        ]);
    }

    public function store(PublicAnggotaSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storedPaths = [];

        try {
            $storedPaths['foto_path'] = $request->file('foto')->store('pengajuan-anggota/foto', 'public');
            $storedPaths['kk_path'] = $request->file('kk')->store('pengajuan-anggota/kk', 'public');
            $storedPaths['dokumen_identitas_path'] = $request->file('dokumen_identitas')->store('pengajuan-anggota/identitas', 'public');

            $kodePengajuan = $this->generateKodePengajuan();

            DB::transaction(function () use ($validated, $storedPaths, $kodePengajuan, $request) {
                PengajuanAnggota::create([
                    'kode_pengajuan' => $kodePengajuan,
                    'nama_lengkap' => $validated['nama_lengkap'],
                    'jenis_identitas' => $validated['jenis_identitas'],
                    'nomor_identitas' => $validated['nomor_identitas'],
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                    'alamat' => $validated['alamat'],
                    'telepon' => $validated['telepon'],
                    'email' => $validated['email'] ?? null,
                    'golongan_darah' => $validated['golongan_darah'] ?? null,
                    'provinsi_id' => $validated['provinsi_id'],
                    'kota_kab_id' => $validated['kota_kab_id'],
                    'kecamatan_id' => $validated['kecamatan_id'] ?? null,
                    'pagoruan_id' => $validated['pagoruan_id'] ?? null,
                    'foto_path' => $storedPaths['foto_path'],
                    'kk_path' => $storedPaths['kk_path'],
                    'dokumen_identitas_path' => $storedPaths['dokumen_identitas_path'],
                    'status' => 'diajukan',
                    'submitted_ip' => $request->ip(),
                ]);
            }, 3);

            return redirect()
                ->route('pengajuan-anggota.show', $kodePengajuan)
                ->with('success', 'Pengajuan berhasil dikirim. Simpan kode pengajuan Anda untuk pelacakan.');
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.');
        }
    }

    public function check(): Response
    {
        return Inertia::render('Public/CekPengajuanForm', [
            'site' => $this->serializeSiteSettings(PublicSiteSetting::query()->first()),
        ]);
    }

    public function show(string $kode): Response
    {
        $normalizedCode = strtoupper(trim($kode));

        $pengajuan = PengajuanAnggota::query()
            ->with(['provinsi', 'kotaKab', 'kecamatan', 'pagoruan', 'anggota'])
            ->where('kode_pengajuan', $normalizedCode)
            ->firstOrFail();

        return Inertia::render('Public/CekPengajuanShow', [
            'site' => $this->serializeSiteSettings(PublicSiteSetting::query()->first()),
            'pengajuan' => $this->serializePengajuan($pengajuan),
        ]);
    }

    private function generateKodePengajuan(): string
    {
        do {
            $code = 'PPSI-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (PengajuanAnggota::query()->where('kode_pengajuan', $code)->exists());

        return $code;
    }

    private function serializePengajuan(PengajuanAnggota $pengajuan): array
    {
        return [
            'id' => $pengajuan->id,
            'kode_pengajuan' => $pengajuan->kode_pengajuan,
            'nama_lengkap' => $pengajuan->nama_lengkap,
            'jenis_identitas' => $pengajuan->jenis_identitas,
            'nomor_identitas' => $pengajuan->nomor_identitas,
            'tanggal_lahir' => optional($pengajuan->tanggal_lahir)->toDateString(),
            'alamat' => $pengajuan->alamat,
            'telepon' => $pengajuan->telepon,
            'email' => $pengajuan->email,
            'golongan_darah' => $pengajuan->golongan_darah,
            'status' => $pengajuan->status,
            'catatan_admin' => $pengajuan->catatan_admin,
            'submitted_at' => optional($pengajuan->created_at)->toIso8601String(),
            'ditinjau_at' => optional($pengajuan->ditinjau_at)->toIso8601String(),
            'dikonversi_at' => optional($pengajuan->dikonversi_at)->toIso8601String(),
            'anggota_id' => $pengajuan->anggota_id,
            'anggota_public_url' => $pengajuan->anggota_id ? route('anggota.public.show', $pengajuan->anggota_id, false) : null,
            'provinsi' => $pengajuan->provinsi ? [
                'id' => $pengajuan->provinsi->id,
                'nama' => $pengajuan->provinsi->nama,
            ] : null,
            'kota_kab' => $pengajuan->kotaKab ? [
                'id' => $pengajuan->kotaKab->id,
                'nama' => $pengajuan->kotaKab->nama,
            ] : null,
            'kecamatan' => $pengajuan->kecamatan ? [
                'id' => $pengajuan->kecamatan->id,
                'nama' => $pengajuan->kecamatan->nama,
            ] : null,
            'pagoruan' => $pengajuan->pagoruan ? [
                'id' => $pengajuan->pagoruan->id,
                'nama' => $pengajuan->pagoruan->nama,
            ] : null,
        ];
    }

    private function selectOptionsFromMap(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    private function serializeSiteSettings(?PublicSiteSetting $settings): array
    {
        $socialLinks = $settings?->social_links ?? [];

        return [
            'site_name' => $settings?->site_name ?? 'Persatuan Pencak Silat Indonesia',
            'site_tagline' => $settings?->site_tagline ?? 'Pendaftaran anggota publik dan landing page organisasi.',
            'logo_url' => $settings?->logo_path ? route('landing.media', ['collection' => 'settings', 'id' => $settings->id, 'field' => 'logo_path'], false) : null,
            'telepon' => $settings?->telepon,
            'email' => $settings?->email,
            'social_links' => [
                'facebook' => $socialLinks['facebook'] ?? null,
                'instagram' => $socialLinks['instagram'] ?? null,
                'youtube' => $socialLinks['youtube'] ?? null,
                'linkedin' => $socialLinks['linkedin'] ?? null,
            ],
        ];
    }
}
