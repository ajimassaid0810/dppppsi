<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KotaKabController;
use App\Http\Controllers\LandingCmsController;
use App\Http\Controllers\PagoruanController;
use App\Http\Controllers\PengajuanAnggotaReviewController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\PublicPengajuanAnggotaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', [PublicLandingController::class, 'index'])->name('home');
Route::get('/landing-media/{collection}/{id}/{field}', [PublicLandingController::class, 'media'])->name('landing.media');
Route::get('/pendaftaran-anggota', [PublicPengajuanAnggotaController::class, 'create'])->name('pengajuan-anggota.create');
Route::post('/pendaftaran-anggota', [PublicPengajuanAnggotaController::class, 'store'])->name('pengajuan-anggota.store');
Route::get('/cek-pengajuan', [PublicPengajuanAnggotaController::class, 'check'])->name('pengajuan-anggota.check');
Route::get('/cek-pengajuan/{kode}', [PublicPengajuanAnggotaController::class, 'show'])->name('pengajuan-anggota.show');

Route::get('/cek-anggota/{id}', [AnggotaController::class, 'publicShow'])->name('anggota.public.show');
Route::get('/anggota/{id}/foto', [AnggotaController::class, 'foto'])->name('anggota.foto');
Route::get('/dpw-ttd/{id}', [ProvinsiController::class, 'ttdPublic'])->name('dpw.ttd.public');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'dpw' => DB::table('provinsi')->count(),
            'dpd' => DB::table('kota_kab')->count(),
            'dpc' => DB::table('kecamatan')->count(),
            'anggota' => DB::table('anggota')->count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw,admin_dpd'])->group(function () {
    Route::resource('anggota', AnggotaController::class)
        ->parameters(['anggota' => 'anggota'])
        ->except(['show']);
    Route::get('/anggota/{id}/kk', [AnggotaController::class, 'kk'])->name('anggota.kk');
    Route::get('/anggota/{id}/dokumen-identitas', [AnggotaController::class, 'dokumenIdentitas'])->name('anggota.dokumen-identitas');
})->name('anggota');
Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw,admin_dpd'])->group(function () {
    Route::get('/anggota/{id}/print', [AnggotaController::class, 'print'])->name('anggota.print');
    Route::get('/anggota/{id}/cetak', [AnggotaController::class, 'cetak'])->name('anggota.cetak');
});

Route::middleware(['auth', 'verified', 'role:superadmin'])->prefix('landing-cms')->name('landing-cms.')->group(function () {
    Route::get('/', [LandingCmsController::class, 'index'])->name('index');
    Route::put('/settings', [LandingCmsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/sections/{section}', [LandingCmsController::class, 'storeSection'])->name('sections.store');
    Route::put('/sections/{section}/{id}', [LandingCmsController::class, 'updateSection'])->name('sections.update');
    Route::delete('/sections/{section}/{id}', [LandingCmsController::class, 'destroySection'])->name('sections.destroy');

    Route::get('/pengajuan-publik', [PengajuanAnggotaReviewController::class, 'index'])->name('pengajuan.index');
    Route::patch('/pengajuan-publik/{pengajuan}/status', [PengajuanAnggotaReviewController::class, 'updateStatus'])->name('pengajuan.status');
    Route::post('/pengajuan-publik/{pengajuan}/convert', [PengajuanAnggotaReviewController::class, 'convert'])->name('pengajuan.convert');
    Route::get('/pengajuan-publik/{pengajuan}/foto', [PengajuanAnggotaReviewController::class, 'foto'])->name('pengajuan.foto');
    Route::get('/pengajuan-publik/{pengajuan}/kk', [PengajuanAnggotaReviewController::class, 'kk'])->name('pengajuan.kk');
    Route::get('/pengajuan-publik/{pengajuan}/dokumen-identitas', [PengajuanAnggotaReviewController::class, 'dokumenIdentitas'])->name('pengajuan.dokumen-identitas');
});


Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw'])->group(function () {
    Route::get('/dpw-provinsi', [ProvinsiController::class, 'index'])->name('dpw-provinsi.index');
    Route::get('/dpw-provinsi/create', [ProvinsiController::class, 'create'])->name('dpw-provinsi.create');
    Route::post('/dpw-provinsi', [ProvinsiController::class, 'store'])->name('dpw-provinsi.store');
    Route::get('/dpw-provinsi/{provinsi}/edit', [ProvinsiController::class, 'edit'])->name('dpw-provinsi.edit');
    Route::put('/dpw-provinsi/{provinsi}', [ProvinsiController::class, 'update'])->name('dpw-provinsi.update');
    Route::delete('/dpw-provinsi/{provinsi}', [ProvinsiController::class, 'destroy'])->name('dpw-provinsi.destroy');
});


Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw,admin_dpd'])->group(function () {
    Route::resource('dpd-kota-kab', KotaKabController::class);
    Route::resource('dpc-kecamatan', KecamatanController::class);
});

Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw,admin_dpd,admin_dpc'])->group(function () {
    Route::resource('pagoruan', PagoruanController::class)->except(['show']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
