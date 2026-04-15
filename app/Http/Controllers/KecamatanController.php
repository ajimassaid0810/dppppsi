<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\KotaKab;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $provinsiId = $request->get('provinsi_id', Provinsi::first()?->id);
        $kotaKabId = $request->get('kota_kab_id', KotaKab::where('provinsi_id', $provinsiId)->first()?->id);

        $kecamatan = Kecamatan::with(['kotaKab.provinsi'])
            ->when($provinsiId, fn($q) => $q->whereHas('kotaKab', fn($q) => $q->where('provinsi_id', $provinsiId)))
            ->when($kotaKabId, fn($q) => $q->where('kota_kab_id', $kotaKabId))
            ->paginate(10);

        return Inertia::render('Kecamatan/Index', [
            'kecamatan' => $kecamatan,
            'provinsiList' => Provinsi::all(['id','nama']),
            'kotaKabList' => KotaKab::where('provinsi_id', $provinsiId)->get(['id','nama']),
            'filters' => [
                'provinsi_id' => $provinsiId,
                'kota_kab_id' => $kotaKabId,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Kecamatan/Create', [
            'provinsiList' => Provinsi::all(['id','nama']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kota_kab_id' => 'required|exists:kota_kab,id',
        ]);

        Kecamatan::create($request->only(['nama','kota_kab_id']));

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil ditambahkan');
    }

    public function edit(Kecamatan $kecamatan)
    {
        return Inertia::render('Kecamatan/Edit', [
            'kecamatan' => $kecamatan,
            'provinsiList' => Provinsi::all(['id','nama']),
            'kotaKabList' => KotaKab::where('provinsi_id', $kecamatan->kotaKab->provinsi_id)->get(['id','nama']),
        ]);
    }
    public function update(Request $request, Kecamatan $kecamatan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kota_kab_id' => 'required|exists:kota_kab,id',
        ]);

        $kecamatan->update($request->only(['nama','kota_kab_id']));

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil diperbarui');
    }
    public function destroy(Kecamatan $kecamatan)
    {
        $kecamatan->delete();

        return redirect()->route('dpc-kecamatan.index')->with('success','Kecamatan berhasil dihapus');
    }

}
