<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Kecamatan;
use App\Models\KotaKab;
use App\Models\Pagoruan;
use App\Models\Provinsi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnggotaController extends Controller
{
    public function index(): Response
    {
        $anggota = Anggota::with([
            'kotaKab.provinsi',
            'kecamatan',
            'pagoruan',
        ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $anggota->through(function (Anggota $item) {
            $item->setAttribute('foto_url', $this->buildFotoUrl($item));

            return $item;
        });

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggota,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Anggota/Create', [
            'provinsi' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($request, $validated) {
            $kotaKab = KotaKab::with('provinsi')->findOrFail($validated['kota_kab_id']);
            $nomorUrut = ((int) Anggota::where('kota_kab_id', $kotaKab->id)->max('nomor_urut_dpd')) + 1;

            $payload = $this->buildPayload(
                request: $request,
                validated: $validated,
                kotaKab: $kotaKab,
                nomorUrut: $nomorUrut
            );

            Anggota::create($payload);
        });

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Anggota $anggota): Response
    {
        $anggota->load(['kotaKab.provinsi', 'kecamatan', 'pagoruan']);
        $anggota->setAttribute('foto_url', $this->buildFotoUrl($anggota));

        return Inertia::render('Anggota/Edit', [
            'anggota' => $anggota,
            'provinsi' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
        ]);
    }

    public function update(Request $request, Anggota $anggota): RedirectResponse
    {
        $validated = $this->validateRequest($request, $anggota);

        DB::transaction(function () use ($request, $validated, $anggota) {
            $kotaKab = KotaKab::with('provinsi')->findOrFail($validated['kota_kab_id']);

            $nomorUrut = $anggota->nomor_urut_dpd;
            if ((int) $anggota->kota_kab_id !== (int) $kotaKab->id) {
                $nomorUrut = ((int) Anggota::where('kota_kab_id', $kotaKab->id)
                    ->where('id', '!=', $anggota->id)
                    ->max('nomor_urut_dpd')) + 1;
            }

            $payload = $this->buildPayload(
                request: $request,
                validated: $validated,
                kotaKab: $kotaKab,
                nomorUrut: $nomorUrut,
                anggota: $anggota
            );

            $anggota->update($payload);
        });

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        foreach ([$anggota->foto_path, $anggota->kk_path, $anggota->dokumen_identitas_path] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

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
        $anggota->setAttribute('foto_url', $this->buildFotoUrl($anggota));
        $this->attachProvinsiTtdUrl($anggota);

        return Inertia::render('Anggota/PrintCard', [
            'anggota' => $anggota,
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
        $anggota->setAttribute('foto_url', $this->buildFotoUrl($anggota));
        $this->attachProvinsiTtdUrl($anggota);

        return Inertia::render('Anggota/cetakKartu', [
            'anggota' => $anggota,
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
        $anggota->setAttribute('foto_url', $this->buildFotoUrl($anggota));
        $this->attachProvinsiTtdUrl($anggota);

        return Inertia::render('Public/AnggotaShow', [
            'anggota' => $anggota,
        ]);
    }

    public function foto(string $id)
    {
        $anggota = Anggota::findOrFail($id);

        abort_if(!$anggota->foto_path || !Storage::disk('public')->exists($anggota->foto_path), 404);

        return Storage::disk('public')->response($anggota->foto_path);
    }

    private function validateRequest(Request $request, ?Anggota $anggota = null): array
    {
        return $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_identitas' => ['required', Rule::in(['ktp', 'kartu_pelajar'])],
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'kota_kab_id' => ['required', 'integer', 'exists:kota_kab,id'],
            'kecamatan_id' => [
                'nullable',
                'integer',
                Rule::exists('kecamatan', 'id')->where(fn ($query) => $query->where('kota_kab_id', $request->input('kota_kab_id'))),
            ],
            'pagoruan_id' => [
                'nullable',
                'integer',
                Rule::exists('pagoruan', 'id')->where(fn ($query) => $query->where('kecamatan_id', $request->input('kecamatan_id'))),
            ],
            'telepon' => ['nullable', 'string', 'max:20'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'dokumen_identitas' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'golongan_darah' => ['nullable', Rule::in(['A', 'B', 'AB', 'O'])],
            'tanggal_gabung' => ['nullable', 'date'],
            'masa_berlaku_sampai' => ['nullable', 'date'],
            'status_pengajuan' => [
                'nullable',
                Rule::in([
                    'draft_dpd',
                    'diajukan_ke_dpw',
                    'diverifikasi_dpw',
                    'diajukan_ke_dpp',
                    'disetujui_dpp',
                    'ditolak',
                ]),
            ],
            'catatan_verifikasi' => ['nullable', 'string'],
        ]);
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
            'status_pengajuan' => $validated['status_pengajuan'] ?? 'draft_dpd',
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

    private function buildFotoUrl(Anggota $anggota): ?string
    {
        return $anggota->foto_path ? route('anggota.foto', $anggota->id) : null;
    }

    private function attachProvinsiTtdUrl(Anggota $anggota): void
    {
        $provinsi = $anggota->kotaKab?->provinsi;

        if (! $provinsi) {
            return;
        }

        $provinsi->setAttribute(
            'ttd_public_url',
            $provinsi->url_ttd_ketua ? route('dpw.ttd.public', $provinsi->id) : null
        );
    }
}
