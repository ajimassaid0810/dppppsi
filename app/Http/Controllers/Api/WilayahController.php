<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KotaKab;
use App\Models\Kecamatan;
use App\Models\Pagoruan;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function getKotaKab($provinsiId)
    {
        return KotaKab::where('provinsi_id', $provinsiId)
            ->select('id', 'kode', 'nama')
            ->orderBy('nama')
            ->get();
    }

    public function getKecamatan(Request $request, $kotaKabId)
{
    $paginate = $request->boolean('paginate', true); // default true
    $perPage = $request->input('per_page', 10);

    $query = Kecamatan::with(['kotaKab.provinsi'])
        ->where('kota_kab_id', $kotaKabId)
        ->orderBy('nama');

    if ($paginate) {
        $data = $query->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'links' => [
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ],
        ]);
    } else {
        $data = $query->get();
        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }
}

    public function getPagoruan($kecamatanId)
    {
        return Pagoruan::where('kecamatan_id', $kecamatanId)
            ->select('id', 'kode', 'nama')
            ->orderBy('nama')
            ->get();
    }

    public function getKelurahan($kecamatanId)
    {
        return $this->getPagoruan($kecamatanId);
    }
}
