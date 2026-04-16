<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KotaKabController;
use App\Http\Controllers\ProvinsiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

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
    Route::resource('anggota', AnggotaController::class)->except(['show']);
})->name('anggota');
Route::middleware(['auth', 'verified', 'role:superadmin,admin_dpp,admin_dpw,admin_dpd'])->group(function () {
    Route::get('/anggota/{id}/print', [AnggotaController::class, 'print'])->name('anggota.print');
    Route::get('/anggota/{id}/cetak', [AnggotaController::class, 'cetak'])->name('anggota.cetak');
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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
