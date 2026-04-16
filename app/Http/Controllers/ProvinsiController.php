<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Throwable;

class ProvinsiController extends Controller
{
    public function ttdPublic(string $id)
    {
        $provinsi = Provinsi::findOrFail($id);
        $path = $this->normalizeStoragePath($provinsi->url_ttd_ketua);

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

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
        'kode' => ['required', 'string', 'size:2', 'unique:provinsi,kode'],
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
    $validator = Validator::make(
        $request->all(),
        [
            'nama' => ['sometimes', 'string', 'max:255'],
            'nama_ketua_dpw' => ['nullable', 'string', 'max:255'],
            'ttd_ketua' => ['nullable', 'file', 'image', 'max:2048'],
        ],
        [
            'nama.string' => 'Nama provinsi harus berupa teks.',
            'nama.max' => 'Nama provinsi maksimal 255 karakter.',
            'nama_ketua_dpw.string' => 'Nama Ketua DPW harus berupa teks.',
            'nama_ketua_dpw.max' => 'Nama Ketua DPW maksimal 255 karakter.',
            'ttd_ketua.image' => 'File tanda tangan harus berupa gambar.',
            'ttd_ketua.max' => 'Ukuran tanda tangan maksimal 2 MB.',
        ]
    );

    if ($validator->fails()) {
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal. Periksa kembali input Anda.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        return back()
            ->withErrors($validator)
            ->withInput()
            ->with('error', 'Validasi gagal. Periksa kembali data DPW.');
    }

    try {
        $validated = $validator->validated();

        $data = [
            'kode' => $provinsi->kode,
            'nama' => $request->filled('nama') ? $validated['nama'] : $provinsi->nama,
            'nama_ketua_dpw' => $validated['nama_ketua_dpw'] ?? null,
        ];

        if ($request->hasFile('ttd_ketua')) {
            $path = $request->file('ttd_ketua')->store('ttd', 'public');
            $data['url_ttd_ketua'] = "/storage/{$path}";
        }

        $provinsi->fill($data);

        if (! $provinsi->isDirty()) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada perubahan data untuk disimpan.',
                    'data' => $provinsi,
                ], 200);
            }

            return back()->with('info', 'Tidak ada perubahan data untuk disimpan.');
        }

        $dirtyAttributes = array_keys($provinsi->getDirty());
        $provinsi->save();

        Log::info('Data DPW provinsi berhasil diperbarui.', [
            'provinsi_id' => $provinsi->id,
            'updated_fields' => $dirtyAttributes,
        ]);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data provinsi berhasil disimpan.',
                'data' => $provinsi->fresh(),
            ], 200);
        }

        return redirect()
            ->route('dpw-provinsi.index')
            ->with('success', 'Data provinsi berhasil disimpan.');
    } catch (Throwable $exception) {
        Log::error('Gagal memperbarui data DPW provinsi.', [
            'provinsi_id' => $provinsi->id,
            'request_payload' => $request->except(['ttd_ketua']),
            'exception_message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data provinsi.',
                'data' => null,
            ], 500);
        }

        return back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat menyimpan perubahan. Silakan coba lagi.');
    }
}


    public function destroy(Provinsi $provinsi)
    {
        $provinsi->delete();

        return redirect()->route('dpw-provinsi.index')->with('success', 'Provinsi dihapus');
    }

    private function normalizeStoragePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return substr($path, 8);
        }

        return $path;
    }
}
