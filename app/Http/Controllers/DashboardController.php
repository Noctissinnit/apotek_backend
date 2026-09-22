<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Kasir melihat angka transaksinya sendiri; admin melihat seluruh apotek.
        $penjualanHariIni = Penjualan::whereDate('tanggal', today())
            ->when($user->isKasir(), fn ($q) => $q->where('user_id', $user->id));

        $data = [
            'transaksiHariIni' => (clone $penjualanHariIni)->count(),
            'omzetHariIni' => (float) (clone $penjualanHariIni)->sum('total'),
            'stokMenipis' => Obat::with('kategori')->stokMenipis()->where('aktif', true)
                ->orderBy('stok')->limit(8)->get(),
            'akanKadaluarsa' => Obat::akanKadaluarsa(90)->where('aktif', true)
                ->orderBy('tanggal_kadaluarsa')->limit(8)->get(),
            'jumlahStokMenipis' => Obat::stokMenipis()->where('aktif', true)->count(),
            'jumlahAkanKadaluarsa' => Obat::akanKadaluarsa(90)->where('aktif', true)->count(),
        ];

        if ($user->isAdmin()) {
            $data += [
                'totalObat' => Obat::count(),
                'nilaiStok' => (float) Obat::sum(DB::raw('stok * harga_beli')),
                'omzetBulanIni' => (float) Penjualan::whereYear('tanggal', now()->year)
                    ->whereMonth('tanggal', now()->month)
                    ->sum('total'),
                'obatTerlaris' => DetailPenjualan::query()
                    ->select('obat_id', 'nama_obat', DB::raw('SUM(jumlah) as total_terjual'))
                    ->groupBy('obat_id', 'nama_obat')
                    ->orderByDesc('total_terjual')
                    ->limit(5)
                    ->get(),
            ];
        }

        return view('dashboard', $data);
    }
}
