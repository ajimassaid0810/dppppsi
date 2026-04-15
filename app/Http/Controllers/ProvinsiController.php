<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProvinsiController extends Controller
{
   public function index(Request $request)
{
    $search = $request->input('search');

    $provinsi = Provinsi::query()
        ->when($search, fn($q) => $q->where('nama', 'like', "%{$search}%"))
        ->orderBy('nama')
        ->paginate(10) // ← jumlah per halaman
        ->withQueryString(); // ← agar search tetap aktif saat pindah halaman

    return Inertia::render('DPW/Index', [
        'provinsi' => $provinsi,
        'filters' => [
            'search' => $search,
        ],
    ]);
}
    public function create()
    {
        return Inertia::render('DPW/Create');
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'nama_ketua_dpw' => 'nullable|string|max:255',
        'ttd_ketua' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // file upload
    ]);

    // Handle upload tanda tangan
    if ($request->hasFile('ttd_ketua')) {
        $path = $request->file('ttd_ketua')->store('ttd_ketua', 'public');
        $validated['url_ttd_ketua'] = "/storage/" . $path;
    }

    Provinsi::create($validated);

    return redirect()->route('dpw-provinsi.index')
        ->with('success', 'Provinsi berhasil ditambahkan');
}

    public function edit(Provinsi $provinsi)
    {
        return Inertia::render('DPW/Edit', [
            'provinsi' => $provinsi,
        ]);
    }

public function update(Request $request, Provinsi $provinsi)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'nama_ketua_dpw' => 'nullable|string|max:255',
        'ttd_ketua' => 'nullable|file|image|max:2048',
    ]);

    $data = [
        'nama' => $validated['nama'],
        'nama_ketua_dpw' => $validated['nama_ketua_dpw'] ?? null,
    ];

    // kalau ada file baru, simpan path
    if ($request->hasFile('ttd_ketua')) {
        $path = $request->file('ttd_ketua')->store('ttd', 'public');
        $data['url_ttd_ketua'] = "/storage/{$path}";
    }

    $provinsi->update($data);

    return redirect()->route('dpw-provinsi.index')
        ->with('success', 'Provinsi berhasil diperbarui');
}


    public function destroy(Provinsi $provinsi)
    {
        $provinsi->delete();

        return redirect()->route('dpw-provinsi.index')->with('success', 'Provinsi dihapus');
    }
}