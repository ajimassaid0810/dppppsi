<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\KotaKab;
use App\Models\PengajuanAnggota;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PengajuanAnggotaReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $pengajuan = PengajuanAnggota::query()
            ->with(['provinsi', 'kotaKab', 'kecamatan', 'pagoruan', 'reviewer'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('kode_pengajuan', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nomor_identitas', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pengajuan->through(fn (PengajuanAnggota $item) => $this->serializePengajuan($item));

        return Inertia::render('LandingCms/PengajuanPublik', [
            'pengajuan' => $pengajuan,
            'filters' => [
                'search' => $search,
                'status' => $status !== '' ? $status : null,
            ],
            'statusOptions' => $this->selectOptionsFromMap(PengajuanAnggota::STATUS_OPTIONS),
        ]);
    }

    public function updateStatus(Request $request, PengajuanAnggota $pengajuan): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['ditinjau', 'ditolak'])],
            'catatan_admin' => ['nullable', 'string', Rule::requiredIf($request->input('status') === 'ditolak')],
        ]);

        if ($pengajuan->status === 'dikonversi') {
            return back()->with('error', 'Pengajuan yang sudah dikonversi tidak bisa diubah statusnya.');
        }

        $pengajuan->update([
            'status' => $validated['status'],
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'ditinjau_oleh' => $request->user()?->id,
            'ditinjau_at' => now(),
        ]);

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function convert(Request $request, PengajuanAnggota $pengajuan): RedirectResponse
    {
        if ($pengajuan->anggota_id || $pengajuan->status === 'dikonversi') {
            return back()->with('error', 'Pengajuan ini sudah pernah dikonversi menjadi anggota.');
        }

        DB::transaction(function () use ($request, $pengajuan) {
            $kotaKab = KotaKab::query()
                ->with('provinsi')
                ->lockForUpdate()
                ->findOrFail($pengajuan->kota_kab_id);

            $nomorUrut = $this->nextNomorUrutDpd($kotaKab->id);
            $nomorAnggota = $this->buildNomorAnggota($kotaKab, $nomorUrut);

            $anggota = Anggota::query()->create([
                'nomor_anggota' => $nomorAnggota,
                'nomor_urut_dpd' => $nomorUrut,
                'kota_kab_id' => $pengajuan->kota_kab_id,
                'kecamatan_id' => $pengajuan->kecamatan_id,
                'pagoruan_id' => $pengajuan->pagoruan_id,
                'dibuat_oleh' => $request->user()?->id,
                'nama_lengkap' => $pengajuan->nama_lengkap,
                'jenis_identitas' => $pengajuan->jenis_identitas,
                'nomor_identitas' => $pengajuan->nomor_identitas,
                'tanggal_lahir' => $pengajuan->tanggal_lahir,
                'alamat' => $pengajuan->alamat,
                'telepon' => $pengajuan->telepon,
                'foto_path' => $pengajuan->foto_path,
                'kk_path' => $pengajuan->kk_path,
                'dokumen_identitas_path' => $pengajuan->dokumen_identitas_path,
                'tanggal_gabung' => now()->toDateString(),
                'masa_berlaku_sampai' => now()->addYear()->toDateString(),
                'golongan_darah' => $pengajuan->golongan_darah,
                'status_pengajuan' => 'Pengajuan_anggota',
                'catatan_verifikasi' => $pengajuan->catatan_admin,
                'barcode_data' => $nomorAnggota,
            ]);

            $pengajuan->update([
                'status' => 'dikonversi',
                'anggota_id' => $anggota->id,
                'ditinjau_oleh' => $request->user()?->id,
                'ditinjau_at' => $pengajuan->ditinjau_at ?? now(),
                'dikonversi_at' => now(),
            ]);
        }, 3);

        return back()->with('success', 'Pengajuan berhasil dikonversi menjadi anggota resmi.');
    }

    public function foto(PengajuanAnggota $pengajuan)
    {
        abort_if(! $pengajuan->foto_path || ! Storage::disk('public')->exists($pengajuan->foto_path), 404);

        return Storage::disk('public')->response($pengajuan->foto_path);
    }

    public function kk(PengajuanAnggota $pengajuan)
    {
        abort_if(! $pengajuan->kk_path || ! Storage::disk('public')->exists($pengajuan->kk_path), 404);

        return Storage::disk('public')->response($pengajuan->kk_path);
    }

    public function dokumenIdentitas(PengajuanAnggota $pengajuan)
    {
        abort_if(! $pengajuan->dokumen_identitas_path || ! Storage::disk('public')->exists($pengajuan->dokumen_identitas_path), 404);

        return Storage::disk('public')->response($pengajuan->dokumen_identitas_path);
    }

    private function serializePengajuan(PengajuanAnggota $pengajuan): array
    {
        return [
            'id' => $pengajuan->id,
            'kode_pengajuan' => $pengajuan->kode_pengajuan,
            'nama_lengkap' => $pengajuan->nama_lengkap,
            'jenis_identitas' => $pengajuan->jenis_identitas,
            'nomor_identitas' => $pengajuan->nomor_identitas,
            'tanggal_lahir' => $this->formatDateValue($pengajuan->tanggal_lahir),
            'alamat' => $pengajuan->alamat,
            'telepon' => $pengajuan->telepon,
            'email' => $pengajuan->email,
            'golongan_darah' => $pengajuan->golongan_darah,
            'status' => $pengajuan->status,
            'catatan_admin' => $pengajuan->catatan_admin,
            'submitted_at' => optional($pengajuan->created_at)?->toIso8601String(),
            'ditinjau_at' => optional($pengajuan->ditinjau_at)?->toIso8601String(),
            'dikonversi_at' => optional($pengajuan->dikonversi_at)?->toIso8601String(),
            'anggota_id' => $pengajuan->anggota_id,
            'anggota_public_url' => $pengajuan->anggota_id ? route('anggota.public.show', $pengajuan->anggota_id, false) : null,
            'foto_url' => route('landing-cms.pengajuan.foto', $pengajuan->id, false),
            'kk_url' => route('landing-cms.pengajuan.kk', $pengajuan->id, false),
            'dokumen_identitas_url' => route('landing-cms.pengajuan.dokumen-identitas', $pengajuan->id, false),
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
            'reviewer' => $pengajuan->reviewer ? [
                'id' => $pengajuan->reviewer->id,
                'username' => $pengajuan->reviewer->username,
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

    private function formatDateValue(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        return $value ? (string) $value : null;
    }

    private function nextNomorUrutDpd(int $kotaKabId): int
    {
        return (int) Anggota::query()
            ->where('kota_kab_id', $kotaKabId)
            ->lockForUpdate()
            ->max('nomor_urut_dpd') + 1;
    }

    private function buildNomorAnggota(KotaKab $kotaKab, int $nomorUrut): string
    {
        $provinsiKode = $kotaKab->provinsi?->kode ?? '00';
        $kotaKode = $kotaKab->kode ?? '00';

        return sprintf('%s.%s.%06d', $provinsiKode, $kotaKode, $nomorUrut);
    }
}
