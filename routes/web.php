<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hak akses
|--------------------------------------------------------------------------
| tamu        : halaman login
| admin+kasir : dashboard, lihat obat, kasir (catat penjualan), riwayat
|               penjualan (kasir hanya miliknya), ganti password
| admin       : kelola obat/stok, kategori, supplier, user, batalkan transaksi
*/

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
});

Route::middleware(['auth', 'aktif'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/profil/password', [PasswordController::class, 'update'])->name('password.update');

    /*
    | Route admin didaftarkan lebih dulu supaya /obat/create tidak tertangkap
    | oleh /obat/{obat} milik route bersama di bawahnya.
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('obat', ObatController::class)->except(['index', 'show']);
        Route::get('/obat/{obat}/stok', [ObatController::class, 'editStok'])->name('obat.stok.edit');
        Route::put('/obat/{obat}/stok', [ObatController::class, 'updateStok'])->name('obat.stok.update');

        Route::resource('kategori', KategoriController::class)->except('show');
        Route::resource('supplier', SupplierController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');

        Route::delete('/penjualan/{penjualan}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
    });

    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/obat', [ObatController::class, 'index'])->name('obat.index');
        Route::get('/obat/{obat}', [ObatController::class, 'show'])->name('obat.show');

        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
        Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
        Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'show'])->name('penjualan.show');
    });
});
