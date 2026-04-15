<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\Provinsi;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class AnggotaController extends Controller
{
    /**
     * Tampilkan daftar anggota
     */
    public function index()
    {
        $anggota = Anggota::with(['cabang', 'kelurahan'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggota,
        ]);
    }

    /**
     * Form create anggota
     */
    public function create()
    {
        return Inertia::render('Anggota/Create', [
            'cabang' => Cabang::select('id', 'nama_cabang')->get(),
            'provinsi' => Provinsi::select('id', 'nama')->get(),
        ]);
    }

    /**
     * Simpan data anggota baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'nik'            => 'required|string|size:16|unique:anggota',
            'tanggal_lahir'  => 'required|date',
            'alamat'         => 'nullable|string',
            'kelurahan_id'   => 'nullable|uuid|exists:kelurahan,id',
            'telepon'        => 'nullable|string|max:20',
            'foto'           => 'nullable|image|max:2048',
            'tanggal_gabung' => 'required|date',
            'masa_berlaku'   => 'nullable|date',
            'perguruan'      => 'nullable|string|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'cabang_id'      => 'required|uuid|exists:cabang,id',
        ]);

        try {
            // Upload foto jika ada
            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('anggota/foto', 'public');
            }

            // Tambahkan barcode otomatis
            $validated['barcode_data'] = (string) Str::uuid();

            // Simpan data anggota
            Anggota::create($validated);

            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan anggota', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form edit anggota
     */
    public function edit(Anggota $anggota)
    {
        return Inertia::render('Anggota/Edit', [
            'anggota' => $anggota,
            'cabang' => Cabang::select('id', 'nama_cabang')->get(),
            'kelurahan' => Kelurahan::select('id', 'nama')->get(),
        ]);
    }

    /**
     * Update data anggota
     */
    public function update(Request $request, Anggota $anggota)
    {
        $validated = $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'nik'            => 'required|string|size:16|unique:anggota,nik,' . $anggota->id,
            'tanggal_lahir'  => 'required|date',
            'alamat'         => 'nullable|string',
            'kelurahan_id'   => 'nullable|uuid|exists:kelurahan,id',
            'telepon'        => 'nullable|string|max:20',
            'foto'           => 'nullable|image|max:2048',
            'tanggal_gabung' => 'required|date',
            'masa_berlaku'   => 'nullable|date',
            'perguruan'      => 'nullable|string|max:255',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'barcode_data'   => 'nullable|string',
            'cabang_id'      => 'required|uuid|exists:cabang,id',
        ]);

        try {
            if ($request->hasFile('foto')) {
                $validated['foto'] = $request->file('foto')->store('anggota/foto', 'public');
            }

            $anggota->update($validated);

            return redirect()->route('anggota.index')
                ->with('success', 'Data anggota berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal update anggota', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus anggota
     */
    public function destroy(Anggota $anggota)
    {
        try {
            $anggota->delete();
            return redirect()->route('anggota.index')
                ->with('success', 'Anggota berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal hapus anggota', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus.');
        }
    }

public function print($id)
{
    $anggota = Anggota::with([
        'cabang',
        'kelurahan.kecamatan.kotaKab.provinsi'
    ])->findOrFail($id);

    return Inertia::render('Anggota/PrintCard', [
        'anggota' => $anggota
    ]);
}
public function cetak($id)
{
    $anggota = Anggota::with([
        'cabang',
        'kelurahan.kecamatan.kotaKab.provinsi'
    ])->findOrFail($id);

    return Inertia::render('Anggota/cetakKartu', [
        'anggota' => $anggota
    ]);
}

}
