<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\KotaKab;
use App\Models\Provinsi;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AnggotaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $anggota = Anggota::with([
            'kotaKab.provinsi',
            'kecamatan',
            'pagoruan',
        ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('nomor_anggota', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nomor_identitas', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%")
                        ->orWhereHas('kotaKab', fn ($kotaKab) => $kotaKab->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('kecamatan', fn ($kecamatan) => $kecamatan->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('pagoruan', fn ($pagoruan) => $pagoruan->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status_pengajuan', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $anggota->through(function (Anggota $item) {
            return $this->serializeAnggota($item);
        });

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggota,
            'filters' => [
                'search' => $search,
                'status' => $status !== '' ? $status : null,
            ],
            'statusOptions' => $this->selectOptionsFromMap(Anggota::STATUS_PENGAJUAN_OPTIONS),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Anggota/Create', [
            'options' => $this->buildFormOptions(),
        ]);
    }

    public function store(AnggotaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $kotaKab = KotaKab::with('provinsi')
                ->lockForUpdate()
                ->findOrFail($validated['kota_kab_id']);
            $nomorUrut = $this->nextNomorUrutDpd($kotaKab->id);

            $payload = $this->buildPayload(
                request: $request,
                validated: $validated,
                kotaKab: $kotaKab,
                nomorUrut: $nomorUrut
            );

            Anggota::create($payload);
        }, 3);

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Anggota $anggota): Response
    {
        return Inertia::render('Anggota/Edit', [
            'anggota' => $this->serializeAnggota($anggota),
            'options' => $this->buildFormOptions(),
        ]);
    }

    public function update(AnggotaRequest $request, Anggota $anggota): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $anggota) {
            $kotaKab = KotaKab::with('provinsi')
                ->lockForUpdate()
                ->findOrFail($validated['kota_kab_id']);

            $nomorUrut = $anggota->nomor_urut_dpd;
            if ((int) $anggota->kota_kab_id !== (int) $kotaKab->id) {
                $nomorUrut = $this->nextNomorUrutDpd($kotaKab->id, $anggota->id);
            }

            $payload = $this->buildPayload(
                request: $request,
                validated: $validated,
                kotaKab: $kotaKab,
                nomorUrut: $nomorUrut,
                anggota: $anggota
            );

            $anggota->update($payload);
        }, 3);

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        collect([$anggota->foto_path, $anggota->kk_path, $anggota->dokumen_identitas_path])
            ->filter()
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        $anggota->delete();

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus.');
    }

    public function print(string $id): Response
    {
        $anggota = Anggota::with([
            'kotaKab.provinsi',
            'kecamatan',
            'pagoruan.kecamatan.kotaKab.provinsi',
        ])->findOrFail($id);

        return Inertia::render('Anggota/PrintCard', [
            'anggota' => $this->serializeAnggota($anggota),
            'publicUrl' => route('anggota.public.show', $anggota->id),
        ]);
    }

    public function cetak(string $id): Response
    {
        $anggota = Anggota::with([
            'kotaKab.provinsi',
            'kecamatan',
            'pagoruan.kecamatan.kotaKab.provinsi',
        ])->findOrFail($id);

        return Inertia::render('Anggota/cetakKartu', [
            'anggota' => $this->serializeAnggota($anggota),
            'publicUrl' => route('anggota.public.show', $anggota->id),
        ]);
    }

    public function publicShow(string $id): Response
    {
        $anggota = Anggota::with([
            'kotaKab.provinsi',
            'kecamatan',
            'pagoruan.kecamatan.kotaKab.provinsi',
        ])->findOrFail($id);

        return Inertia::render('Public/AnggotaShow', [
            'anggota' => $this->serializeAnggota($anggota),
        ]);
    }

    public function foto(string $id)
    {
        $anggota = Anggota::findOrFail($id);

        $path = $this->normalizePublicStoragePath($anggota->foto_path);

        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function kk(string $id)
    {
        $anggota = Anggota::findOrFail($id);
        $path = $this->normalizePublicStoragePath($anggota->kk_path);

        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function dokumenIdentitas(string $id)
    {
        $anggota = Anggota::findOrFail($id);
        $path = $this->normalizePublicStoragePath($anggota->dokumen_identitas_path);

        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    private function buildPayload(
        Request $request,
        array $validated,
        KotaKab $kotaKab,
        int $nomorUrut,
        ?Anggota $anggota = null
    ): array {
        $payload = [
            'nomor_anggota' => $this->buildNomorAnggota($kotaKab, $nomorUrut),
            'nomor_urut_dpd' => $nomorUrut,
            'kota_kab_id' => $kotaKab->id,
            'kecamatan_id' => $validated['kecamatan_id'] ?? null,
            'pagoruan_id' => $validated['pagoruan_id'] ?? null,
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_identitas' => $validated['jenis_identitas'],
            'nomor_identitas' => $validated['nomor_identitas'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'golongan_darah' => $validated['golongan_darah'] ?? null,
            'tanggal_gabung' => $validated['tanggal_gabung'] ?? null,
            'masa_berlaku_sampai' => $validated['masa_berlaku_sampai'] ?? null,
            'status_pengajuan' => $validated['status_pengajuan'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'barcode_data' => $this->buildNomorAnggota($kotaKab, $nomorUrut),
            'dibuat_oleh' => $anggota?->dibuat_oleh ?? $request->user()?->id,
        ];

        if ($request->hasFile('foto')) {
            if ($anggota?->foto_path) {
                Storage::disk('public')->delete($anggota->foto_path);
            }

            $payload['foto_path'] = $request->file('foto')->store('anggota/foto', 'public');
        } elseif ($anggota) {
            $payload['foto_path'] = $anggota->foto_path;
        }

        if ($request->hasFile('kk')) {
            if ($anggota?->kk_path) {
                Storage::disk('public')->delete($anggota->kk_path);
            }

            $payload['kk_path'] = $request->file('kk')->store('anggota/kk', 'public');
        } elseif ($anggota) {
            $payload['kk_path'] = $anggota->kk_path;
        }

        if ($request->hasFile('dokumen_identitas')) {
            if ($anggota?->dokumen_identitas_path) {
                Storage::disk('public')->delete($anggota->dokumen_identitas_path);
            }

            $payload['dokumen_identitas_path'] = $request->file('dokumen_identitas')->store('anggota/identitas', 'public');
        } elseif ($anggota) {
            $payload['dokumen_identitas_path'] = $anggota->dokumen_identitas_path;
        }

        return $payload;
    }

    private function buildNomorAnggota(KotaKab $kotaKab, int $nomorUrut): string
    {
        $provinsiKode = $kotaKab->provinsi?->kode ?? '00';
        $kotaKode = $kotaKab->kode ?? '00';

        return sprintf('%s.%s.%06d', $provinsiKode, $kotaKode, $nomorUrut);
    }

    private function nextNomorUrutDpd(int $kotaKabId, ?string $ignoreAnggotaId = null): int
    {
        return (int) Anggota::query()
            ->where('kota_kab_id', $kotaKabId)
            ->when($ignoreAnggotaId, fn ($query) => $query->where('id', '!=', $ignoreAnggotaId))
            ->lockForUpdate()
            ->max('nomor_urut_dpd') + 1;
    }

    private function buildFormOptions(): array
    {
        return [
            'provinsi' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
            'jenis_identitas' => $this->selectOptionsFromMap(Anggota::JENIS_IDENTITAS_OPTIONS),
            'golongan_darah' => $this->selectOptionsFromMap(Anggota::GOLONGAN_DARAH_OPTIONS),
            'status_pengajuan' => $this->selectOptionsFromMap(Anggota::STATUS_PENGAJUAN_OPTIONS),
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

    private function serializeAnggota(Anggota $anggota): array
    {
        $anggota->loadMissing(['kotaKab.provinsi', 'kecamatan', 'pagoruan.kecamatan.kotaKab.provinsi']);

        return [
            'id' => $anggota->id,
            'nomor_anggota' => $anggota->nomor_anggota,
            'nomor_urut_dpd' => $anggota->nomor_urut_dpd,
            'nama_lengkap' => $anggota->nama_lengkap,
            'jenis_identitas' => $anggota->jenis_identitas,
            'nomor_identitas' => $anggota->nomor_identitas,
            'tanggal_lahir' => $this->formatDateValue($anggota->tanggal_lahir),
            'alamat' => $anggota->alamat,
            'telepon' => $anggota->telepon,
            'golongan_darah' => $anggota->golongan_darah,
            'tanggal_gabung' => $this->formatDateValue($anggota->tanggal_gabung),
            'masa_berlaku_sampai' => $this->formatDateValue($anggota->masa_berlaku_sampai),
            'status_pengajuan' => $anggota->status_pengajuan,
            'catatan_verifikasi' => $anggota->catatan_verifikasi,
            'foto_path' => $anggota->foto_path,
            'foto_url' => $this->buildFotoUrl($anggota),
            'kk_path' => $anggota->kk_path,
            'kk_url' => $this->buildKkUrl($anggota),
            'dokumen_identitas_path' => $anggota->dokumen_identitas_path,
            'dokumen_identitas_url' => $this->buildDokumenIdentitasUrl($anggota),
            'kota_kab_id' => $anggota->kota_kab_id,
            'kecamatan_id' => $anggota->kecamatan_id,
            'pagoruan_id' => $anggota->pagoruan_id,
            'kota_kab' => $anggota->kotaKab ? [
                'id' => $anggota->kotaKab->id,
                'kode' => $anggota->kotaKab->kode,
                'nama' => $anggota->kotaKab->nama,
                'provinsi' => $anggota->kotaKab->provinsi ? [
                    'id' => $anggota->kotaKab->provinsi->id,
                    'kode' => $anggota->kotaKab->provinsi->kode,
                    'nama' => $anggota->kotaKab->provinsi->nama,
                    'nama_ketua_dpw' => $anggota->kotaKab->provinsi->nama_ketua_dpw,
                    'url_ttd_ketua' => $anggota->kotaKab->provinsi->url_ttd_ketua,
                    'ttd_public_url' => $anggota->kotaKab->provinsi->url_ttd_ketua
                        ? route('dpw.ttd.public', $anggota->kotaKab->provinsi->id, false)
                        : null,
                ] : null,
            ] : null,
            'kecamatan' => $anggota->kecamatan ? [
                'id' => $anggota->kecamatan->id,
                'kode' => $anggota->kecamatan->kode,
                'nama' => $anggota->kecamatan->nama,
            ] : null,
            'pagoruan' => $anggota->pagoruan ? [
                'id' => $anggota->pagoruan->id,
                'kode' => $anggota->pagoruan->kode,
                'nama' => $anggota->pagoruan->nama,
            ] : null,
        ];
    }

    private function formatDateValue(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        return $value ? (string) $value : null;
    }

    private function buildFotoUrl(Anggota $anggota): ?string
    {
        return $anggota->foto_path ? route('anggota.foto', $anggota->id, false) : null;
    }

    private function buildKkUrl(Anggota $anggota): ?string
    {
        return $anggota->kk_path ? route('anggota.kk', $anggota->id, false) : null;
    }

    private function buildDokumenIdentitasUrl(Anggota $anggota): ?string
    {
        return $anggota->dokumen_identitas_path ? route('anggota.dokumen-identitas', $anggota->id, false) : null;
    }

    private function normalizePublicStoragePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $resolvedPath = parse_url($path, PHP_URL_PATH) ?: $path;
        $resolvedPath = ltrim($resolvedPath, '/');

        if (str_starts_with($resolvedPath, 'storage/')) {
            return substr($resolvedPath, 8);
        }

        return $resolvedPath;
    }
}
