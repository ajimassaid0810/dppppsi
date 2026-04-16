<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\KotaKab;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $provinsiId = $request->filled('provinsi_id') ? (int) $request->get('provinsi_id') : null;
        $kotaKabId = $request->filled('kota_kab_id') ? (int) $request->get('kota_kab_id') : null;

        $kecamatan = Kecamatan::with(['kotaKab.provinsi'])
            ->when($provinsiId, fn($q) => $q->whereHas('kotaKab', fn($q) => $q->where('provinsi_id', $provinsiId)))
            ->when($kotaKabId, fn($q) => $q->where('kota_kab_id', $kotaKabId))
            ->paginate(10);

        return Inertia::render('Kecamatan/Index', [
            'kecamatan' => $kecamatan,
            'provinsiList' => Provinsi::all(['id', 'kode', 'nama']),
            'kotaKabList' => $provinsiId
                ? KotaKab::where('provinsi_id', $provinsiId)->get(['id', 'kode', 'nama'])
                : [],
            'filters' => [
                'provinsi_id' => $provinsiId,
                'kota_kab_id' => $kotaKabId,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Kecamatan/Create', [
            'provinsiList' => Provinsi::all(['id', 'kode', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kecamatan', 'kode'),
            ],
            'nama' => 'required|string|max:255',
            'kota_kab_id' => 'required|exists:kota_kab,id',
        ]);

        Kecamatan::create($request->only(['kode', 'nama','kota_kab_id']));

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil ditambahkan');
    }

    public function edit(Kecamatan $kecamatan)
    {
        return Inertia::render('Kecamatan/Edit', [
            'kecamatan' => $kecamatan,
            'provinsiList' => Provinsi::all(['id', 'kode', 'nama']),
            'kotaKabList' => KotaKab::where('provinsi_id', $kecamatan->kotaKab->provinsi_id)->get(['id', 'kode', 'nama']),
        ]);
    }
    public function update(Request $request, Kecamatan $kecamatan)
    {
        $request->validate([
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('kecamatan', 'kode')->ignore($kecamatan->id, 'id'),
            ],
            'nama' => 'required|string|max:255',
            'kota_kab_id' => 'required|exists:kota_kab,id',
        ]);

        $kecamatan->update($request->only(['kode', 'nama','kota_kab_id']));

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil diperbarui');
    }
    public function destroy(Kecamatan $kecamatan)
    {
        $kecamatan->delete();

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil dihapus');
    }

}
