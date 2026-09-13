<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ObatResource;
use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /** GET /api/statistik - ringkasan untuk dashboard apotek. */
    public function ringkasan(): JsonResponse
    {
        $terlaris = DetailPenjualan::query()
            ->select('obat_id', 'nama_obat', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('obat_id', 'nama_obat')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'obat_id' => $row->obat_id,
                'nama_obat' => $row->nama_obat,
                'total_terjual' => (int) $row->total_terjual,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_obat' => Obat::count(),
                'obat_aktif' => Obat::where('aktif', true)->count(),
                'nilai_stok' => round((float) Obat::sum(DB::raw('stok * harga_beli')), 2),
                'stok_menipis' => Obat::stokMenipis()->count(),
                'akan_kadaluarsa_90_hari' => Obat::akanKadaluarsa(90)->count(),
                'transaksi_hari_ini' => Penjualan::whereDate('tanggal', today())->count(),
                'omzet_hari_ini' => round((float) Penjualan::whereDate('tanggal', today())->sum('total'), 2),
                'omzet_bulan_ini' => round((float) Penjualan::whereYear('tanggal', now()->year)
                    ->whereMonth('tanggal', now()->month)
                    ->sum('total'), 2),
                'obat_terlaris' => $terlaris,
            ],
        ]);
    }

    /** GET /api/obat-stok-menipis - daftar obat yang perlu segera dipesan ulang. */
    public function stokMenipis()
    {
        $obat = Obat::with('kategori')
            ->stokMenipis()
            ->where('aktif', true)
            ->orderBy('stok')
            ->limit(50)
            ->get();

        return ObatResource::collection($obat)->additional(['success' => true]);
    }
}
