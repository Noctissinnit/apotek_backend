<?php

use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\ObatController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Apotek
|--------------------------------------------------------------------------
| Semua route di file ini otomatis berprefix /api.
| Endpoint tulis (POST/PUT/PATCH/DELETE) dilindungi middleware "apikey",
| yang hanya aktif kalau env API_KEY diisi.
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'nama' => config('app.name'),
            'versi' => '1.0.0',
            'waktu_server' => now()->toIso8601String(),
            'endpoint' => [
                'obat' => '/api/obat',
                'kategori' => '/api/kategori',
                'supplier' => '/api/supplier',
                'penjualan' => '/api/penjualan',
                'statistik' => '/api/statistik',
                'stok_menipis' => '/api/obat-stok-menipis',
            ],
        ],
    ]);
});

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $database = 'connected';
    } catch (\Throwable $e) {
        $database = 'error: '.$e->getMessage();
    }

    return response()->json([
        'success' => $database === 'connected',
        'data' => [
            'status' => 'ok',
            'database' => $database,
            'waktu' => now()->toIso8601String(),
        ],
    ], $database === 'connected' ? 200 : 503);
});

/* ---------------------------------------------------------------- baca */

Route::get('/statistik', [StatistikController::class, 'ringkasan']);
Route::get('/obat-stok-menipis', [StatistikController::class, 'stokMenipis']);

Route::get('/obat', [ObatController::class, 'index']);
Route::get('/obat/{obat}', [ObatController::class, 'show']);
Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/kategori/{kategori}', [KategoriController::class, 'show']);
Route::get('/supplier', [SupplierController::class, 'index']);
Route::get('/supplier/{supplier}', [SupplierController::class, 'show']);
Route::get('/penjualan', [PenjualanController::class, 'index']);
Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'show']);

/* --------------------------------------------------------------- tulis */

Route::middleware('apikey')->group(function () {
    Route::post('/obat', [ObatController::class, 'store']);
    Route::match(['put', 'patch'], '/obat/{obat}', [ObatController::class, 'update']);
    Route::patch('/obat/{obat}/stok', [ObatController::class, 'ubahStok']);
    Route::delete('/obat/{obat}', [ObatController::class, 'destroy']);

    Route::post('/kategori', [KategoriController::class, 'store']);
    Route::match(['put', 'patch'], '/kategori/{kategori}', [KategoriController::class, 'update']);
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy']);

    Route::post('/supplier', [SupplierController::class, 'store']);
    Route::match(['put', 'patch'], '/supplier/{supplier}', [SupplierController::class, 'update']);
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy']);

    Route::post('/penjualan', [PenjualanController::class, 'store']);
    Route::delete('/penjualan/{penjualan}', [PenjualanController::class, 'destroy']);
});
