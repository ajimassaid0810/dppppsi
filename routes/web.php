<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\KotaKabController;
use App\Http\Controllers\KecamatanController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('anggota', AnggotaController::class);
})->name('anggota');
Route::get('/anggota/{id}/print', [AnggotaController::class, 'print'])->name('anggota.print');
Route::get('/anggota/{id}/cetak', [AnggotaController::class, 'cetak'])->name('anggota.cetak');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dpw-provinsi', [ProvinsiController::class, 'index'])->name('dpw-provinsi.index');
    Route::get('/dpw-provinsi/create', [ProvinsiController::class, 'create'])->name('dpw-provinsi.create');
    Route::post('/dpw-provinsi', [ProvinsiController::class, 'store'])->name('dpw-provinsi.store');
    Route::get('/dpw-provinsi/{provinsi}/edit', [ProvinsiController::class, 'edit'])->name('dpw-provinsi.edit');
    Route::put('/dpw-provinsi/{provinsi}', [ProvinsiController::class, 'update'])->name('dpw-provinsi.update');
    Route::delete('/dpw-provinsi/{provinsi}', [ProvinsiController::class, 'destroy'])->name('dpw-provinsi.destroy');
});



Route::resource('dpd-kota-kab', KotaKabController::class);
Route::resource('dpc-kecamatan', KecamatanController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
