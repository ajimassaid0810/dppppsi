<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\KotaKab;
use App\Models\Pagoruan;
use App\Models\Provinsi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PagoruanController extends Controller
{
    public function index(Request $request): Response
    {
        $fixedKecamatan = $this->resolveFixedKecamatan($request);

        $search = $request->string('search')->toString();
        $provinsiId = $request->filled('provinsi_id') ? (int) $request->input('provinsi_id') : null;
        $kotaKabId = $request->filled('kota_kab_id') ? (int) $request->input('kota_kab_id') : null;
        $kecamatanId = $request->filled('kecamatan_id') ? (int) $request->input('kecamatan_id') : null;

        if ($fixedKecamatan) {
            $provinsiId = $fixedKecamatan->kotaKab?->provinsi?->id;
            $kotaKabId = $fixedKecamatan->kota_kab_id;
            $kecamatanId = $fixedKecamatan->id;
        }

        $pagoruan = Pagoruan::query()
            ->with(['kecamatan.kotaKab.provinsi'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%")
                        ->orWhere('nama_pimpinan', 'like', "%{$search}%")
                        ->orWhere('nama_pelatih', 'like', "%{$search}%");
                });
            })
            ->when($provinsiId, fn ($query) => $query->whereHas('kecamatan.kotaKab', fn ($nested) => $nested->where('provinsi_id', $provinsiId)))
            ->when($kotaKabId, fn ($query) => $query->whereHas('kecamatan', fn ($nested) => $nested->where('kota_kab_id', $kotaKabId)))
            ->when($kecamatanId, fn ($query) => $query->where('kecamatan_id', $kecamatanId))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        [$kotaKabList, $kecamatanList] = $this->buildLocationLists($provinsiId, $kotaKabId, $fixedKecamatan);

        return Inertia::render('Pagoruan/Index', [
            'pagoruan' => $pagoruan,
            'provinsiList' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
            'kotaKabList' => $kotaKabList,
            'kecamatanList' => $kecamatanList,
            'filters' => [
                'search' => $search,
                'provinsi_id' => $provinsiId,
                'kota_kab_id' => $kotaKabId,
                'kecamatan_id' => $kecamatanId,
            ],
            'fixedKecamatan' => $this->serializeKecamatan($fixedKecamatan),
        ]);
    }

    public function create(Request $request): Response
    {
        $fixedKecamatan = $this->resolveFixedKecamatan($request);
        [$kotaKabList, $kecamatanList] = $this->buildLocationLists(
            $fixedKecamatan?->kotaKab?->provinsi?->id,
            $fixedKecamatan?->kota_kab_id,
            $fixedKecamatan
        );

        return Inertia::render('Pagoruan/Create', [
            'provinsiList' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
            'kotaKabList' => $kotaKabList,
            'kecamatanList' => $kecamatanList,
            'fixedKecamatan' => $this->serializeKecamatan($fixedKecamatan),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePagoruan($request);
        $this->ensureKecamatanAccessible($request, (int) $validated['kecamatan_id']);

        Pagoruan::create($validated);

        return redirect()->route('pagoruan.index')->with('success', 'Pagoruan berhasil ditambahkan.');
    }

    public function edit(Request $request, Pagoruan $pagoruan): Response
    {
        $this->ensureKecamatanAccessible($request, (int) $pagoruan->kecamatan_id);

        $pagoruan->load(['kecamatan.kotaKab.provinsi']);

        $provinsiId = $pagoruan->kecamatan?->kotaKab?->provinsi?->id;
        $kotaKabId = $pagoruan->kecamatan?->kota_kab_id;
        $fixedKecamatan = $this->resolveFixedKecamatan($request);
        [$kotaKabList, $kecamatanList] = $this->buildLocationLists($provinsiId, $kotaKabId, $fixedKecamatan);

        return Inertia::render('Pagoruan/Edit', [
            'pagoruan' => $pagoruan,
            'provinsiList' => Provinsi::orderBy('nama')->get(['id', 'kode', 'nama']),
            'kotaKabList' => $kotaKabList,
            'kecamatanList' => $kecamatanList,
            'fixedKecamatan' => $this->serializeKecamatan($fixedKecamatan),
        ]);
    }

    public function update(Request $request, Pagoruan $pagoruan): RedirectResponse
    {
        $validated = $this->validatePagoruan($request, $pagoruan);
        $this->ensureKecamatanAccessible($request, (int) $validated['kecamatan_id']);

        $pagoruan->update($validated);

        return redirect()->route('pagoruan.index')->with('success', 'Pagoruan berhasil diperbarui.');
    }

    public function destroy(Request $request, Pagoruan $pagoruan): RedirectResponse
    {
        $this->ensureKecamatanAccessible($request, (int) $pagoruan->kecamatan_id);

        $pagoruan->delete();

        return redirect()->route('pagoruan.index')->with('success', 'Pagoruan berhasil dihapus.');
    }

    private function validatePagoruan(Request $request, ?Pagoruan $pagoruan = null): array
    {
        $validated = $request->validate([
            'kode' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('pagoruan', 'kode')->ignore($pagoruan?->id, 'id'),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pagoruan', 'nama')
                    ->where(fn ($query) => $query->where('kecamatan_id', $request->input('kecamatan_id')))
                    ->ignore($pagoruan?->id, 'id'),
            ],
            'kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'nama_pimpinan' => ['nullable', 'string', 'max:255'],
            'nama_pelatih' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    private function resolveFixedKecamatan(Request $request): ?Kecamatan
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin_dpc')) {
            return null;
        }

        abort_if(! $user->kecamatan_id, 403, 'Akun admin DPC belum terhubung ke kecamatan.');

        return Kecamatan::with('kotaKab.provinsi')->findOrFail($user->kecamatan_id);
    }

    private function ensureKecamatanAccessible(Request $request, int $kecamatanId): void
    {
        $fixedKecamatan = $this->resolveFixedKecamatan($request);

        if ($fixedKecamatan && $fixedKecamatan->id !== $kecamatanId) {
            abort(403, 'Anda hanya dapat mengelola pagoruan pada kecamatan Anda.');
        }
    }

    private function buildLocationLists(?int $provinsiId, ?int $kotaKabId, ?Kecamatan $fixedKecamatan = null): array
    {
        if ($fixedKecamatan) {
            return [
                [$this->serializeKotaKab($fixedKecamatan->kotaKab)],
                [$this->serializeKecamatan($fixedKecamatan)],
            ];
        }

        return [
            $provinsiId
                ? KotaKab::where('provinsi_id', $provinsiId)->orderBy('nama')->get(['id', 'kode', 'nama'])->all()
                : [],
            $kotaKabId
                ? Kecamatan::where('kota_kab_id', $kotaKabId)->orderBy('nama')->get(['id', 'kode', 'nama'])->all()
                : [],
        ];
    }

    private function serializeKecamatan(?Kecamatan $kecamatan): ?array
    {
        if (! $kecamatan) {
            return null;
        }

        $kecamatan->loadMissing('kotaKab.provinsi');

        return [
            'id' => $kecamatan->id,
            'kode' => $kecamatan->kode,
            'nama' => $kecamatan->nama,
            'kota_kab' => $this->serializeKotaKab($kecamatan->kotaKab),
        ];
    }

    private function serializeKotaKab(?KotaKab $kotaKab): ?array
    {
        if (! $kotaKab) {
            return null;
        }

        $kotaKab->loadMissing('provinsi');

        return [
            'id' => $kotaKab->id,
            'kode' => $kotaKab->kode,
            'nama' => $kotaKab->nama,
            'provinsi' => $kotaKab->provinsi
                ? [
                    'id' => $kotaKab->provinsi->id,
                    'kode' => $kotaKab->provinsi->kode,
                    'nama' => $kotaKab->provinsi->nama,
                ]
                : null,
        ];
    }
}
