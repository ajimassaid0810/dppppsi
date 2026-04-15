<?php

namespace App\Http\Controllers;

use App\Models\KotaKab;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KotaKabController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $provinsiId = $request->input('provinsi_id');

        // Ambil provinsi pertama sebagai default
        $defaultProvinsi = Provinsi::orderBy('nama')->first();
        if (!$provinsiId && $defaultProvinsi) {
            $provinsiId = $defaultProvinsi->id;
        }

        $provinsiList = Provinsi::orderBy('nama')->get();

        $kotaKab = KotaKab::query()
            ->when($search, fn($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($provinsiId, fn($q) => $q->where('provinsi_id', $provinsiId))
            ->with('provinsi')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('DpdKotaKab/Index', [
            'kotaKab' => $kotaKab,
            'provinsiList' => $provinsiList,
            'filters' => [
                'search' => $search,
                'provinsi_id' => $provinsiId,
            ],
        ]);
    }

    public function create()
    {
        $provinsiList = Provinsi::orderBy('nama')->get();

        return Inertia::render('DpdKotaKab/Create', [
            'provinsiList' => $provinsiList,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'provinsi_id' => 'required|exists:provinsi,id',
        ]);

        KotaKab::create($request->only([ 'nama', 'provinsi_id']));

        return redirect()->route('dpd-kota-kab.index')->with('success', 'Kota/Kab berhasil ditambahkan');
    }

    public function edit(KotaKab $kotaKab)
    {
        $provinsiList = Provinsi::orderBy('nama')->get();

        return Inertia::render('DpdKotaKab/Edit', [
            'kotaKab' => $kotaKab,
            'provinsiList' => $provinsiList,
        ]);
    }

    public function update(Request $request, KotaKab $kotaKab)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'provinsi_id' => 'required|exists:provinsi,id',
        ]);

        $kotaKab->update($request->only(['nama', 'provinsi_id']));

        return redirect()->route('dpd-kota-kab.index')->with('success', 'Kota/Kab berhasil diperbarui');
    }

    public function destroy(KotaKab $kotaKab)
    {
        $kotaKab->delete();

        return redirect()->route('dpd-kota-kab.index')->with('success', 'Kota/Kab berhasil dihapus');
    }
}
