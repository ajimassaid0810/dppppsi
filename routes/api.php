<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WilayahController;
use App\Models\KotaKab;
use App\Models\Kecamatan;
use App\Models\Kelurahan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Routes ini otomatis prefiks `api/`
| Contoh akses: http://your-app.test/api/kota-kab/{provinsi_id}
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('/kota-kab/{provinsi}', [WilayahController::class, 'getKotaKab']);
Route::get('/kecamatan/{kotaKab}', [WilayahController::class, 'getKecamatan']);
Route::get('/kelurahan/{kecamatan}', [WilayahController::class, 'getKelurahan']);

