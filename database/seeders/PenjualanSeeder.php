<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    /** Beberapa transaksi contoh 7 hari terakhir supaya /api/statistik ada isinya. */
    public function run(): void
    {
        $pelanggan = [
            'Umum', 'Ibu Wulan', 'Bpk. Andi', 'Klinik Sehat Bersama',
            'Ibu Sri Rahayu', 'Umum', 'Bpk. Joko', 'Ibu Nur Aini',
        ];
        $metode = ['tunai', 'tunai', 'qris', 'debit', 'tunai', 'qris', 'transfer', 'tunai'];

        $urutan = [];

        foreach ($pelanggan as $i => $nama) {
            $tanggal = now()->subDays(7 - intdiv($i, 2))->setTime(9 + ($i % 8), rand(0, 59));

            $obatDijual = Obat::where('aktif', true)
                ->where('stok', '>', 20)
                ->inRandomOrder()
                ->limit(rand(1, 4))
                ->get();

            if ($obatDijual->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($obatDijual, $nama, $tanggal, $metode, $i, &$urutan) {
                $baris = [];
                $total = 0.0;

                foreach ($obatDijual as $obat) {
                    $jumlah = rand(1, 3);
                    $subtotal = (float) $obat->harga_jual * $jumlah;
                    $total += $subtotal;

                    $baris[] = [
                        'obat_id' => $obat->id,
                        'nama_obat' => $obat->nama,
                        'jumlah' => $jumlah,
                        'harga_satuan' => (float) $obat->harga_jual,
                        'subtotal' => $subtotal,
                    ];

                    $obat->decrement('stok', $jumlah);
                }

                // Pembayaran dibulatkan ke atas per 5.000 supaya ada kembalian.
                $bayar = ceil($total / 5000) * 5000;
                $kunci = $tanggal->format('Ymd');
                $urutan[$kunci] = ($urutan[$kunci] ?? 0) + 1;

                $penjualan = Penjualan::create([
                    'kode_transaksi' => 'TRX-'.$kunci.'-'.str_pad((string) $urutan[$kunci], 4, '0', STR_PAD_LEFT),
                    'tanggal' => $tanggal,
                    'nama_pelanggan' => $nama,
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembalian' => $bayar - $total,
                    'metode_bayar' => $metode[$i],
                    'catatan' => null,
                ]);

                $penjualan->detail()->createMany($baris);
            });
        }
    }
}
