<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public const METODE_BAYAR = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
        'debit' => 'Kartu Debit',
        'kredit' => 'Kartu Kredit',
        'transfer' => 'Transfer',
    ];

    /** Kasir hanya melihat transaksinya sendiri; admin melihat semua. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $penjualan = Penjualan::query()
            ->with('kasir')
            ->withCount('detail')
            ->when($user->isKasir(), fn ($q) => $q->where('user_id', $user->id))
            ->when($user->isAdmin() && $request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $like = '%'.$request->query('search').'%';
                $q->where(fn ($sub) => $sub->where('kode_transaksi', 'like', $like)->orWhere('nama_pelanggan', 'like', $like));
            })
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->query('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->query('tanggal_sampai')))
            ->when($request->filled('metode_bayar'), fn ($q) => $q->where('metode_bayar', $request->query('metode_bayar')))
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('penjualan.index', [
            'penjualan' => $penjualan,
            'kasir' => $user->isAdmin() ? User::orderBy('name')->get(['id', 'name']) : collect(),
            'metodeBayar' => self::METODE_BAYAR,
        ]);
    }

    /** Halaman kasir. */
    public function create(): View
    {
        $obat = Obat::where('aktif', true)
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->get(['id', 'kode_obat', 'nama', 'satuan', 'harga_jual', 'stok'])
            ->map(fn (Obat $o) => [
                'id' => $o->id,
                'kode' => $o->kode_obat,
                'nama' => $o->nama,
                'satuan' => $o->satuan,
                'harga' => (float) $o->harga_jual,
                'stok' => $o->stok,
            ]);

        return view('penjualan.create', [
            'obat' => $obat,
            'metodeBayar' => self::METODE_BAYAR,
        ]);
    }

    /**
     * Harga diambil dari master obat (bukan dari form) dan baris stok dikunci
     * selama transaksi supaya tidak terjadi oversell saat dua kasir bersamaan.
     */
    public function store(PenjualanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $kasirId = $request->user()->id;

        $penjualan = DB::transaction(function () use ($data, $kasirId) {
            // Gabungkan item dengan obat yang sama supaya stok dihitung sekali.
            $items = collect($data['items'])
                ->groupBy('obat_id')
                ->map(fn ($grup, $obatId) => [
                    'obat_id' => (int) $obatId,
                    'jumlah' => (int) collect($grup)->sum('jumlah'),
                ]);

            $daftarObat = Obat::whereIn('id', $items->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $baris = [];
            $total = 0.0;

            foreach ($items as $item) {
                $obat = $daftarObat->get($item['obat_id']);

                if (! $obat || ! $obat->aktif) {
                    throw ValidationException::withMessages([
                        'items' => 'Salah satu obat sudah tidak tersedia untuk dijual.',
                    ]);
                }

                if ($obat->stok < $item['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$obat->nama} tidak mencukupi (tersisa {$obat->stok}, diminta {$item['jumlah']}).",
                    ]);
                }

                $subtotal = (float) $obat->harga_jual * $item['jumlah'];
                $total += $subtotal;

                $baris[] = [
                    'obat_id' => $obat->id,
                    'nama_obat' => $obat->nama,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => (float) $obat->harga_jual,
                    'subtotal' => $subtotal,
                ];

                $obat->decrement('stok', $item['jumlah']);
            }

            if ((float) $data['bayar'] < $total) {
                throw ValidationException::withMessages([
                    'bayar' => 'Jumlah bayar kurang dari total belanja (Rp '.number_format($total, 0, ',', '.').').',
                ]);
            }

            $penjualan = Penjualan::create([
                'user_id' => $kasirId,
                'kode_transaksi' => Penjualan::generateKode(),
                'tanggal' => now(),
                'nama_pelanggan' => $data['nama_pelanggan'] ?? null,
                'total' => $total,
                'bayar' => $data['bayar'],
                'kembalian' => (float) $data['bayar'] - $total,
                'metode_bayar' => $data['metode_bayar'] ?? 'tunai',
                'catatan' => $data['catatan'] ?? null,
            ]);

            $penjualan->detail()->createMany($baris);

            return $penjualan;
        });

        return redirect()->route('penjualan.show', $penjualan)
            ->with('success', "Transaksi {$penjualan->kode_transaksi} berhasil disimpan.");
    }

    /** Detail transaksi sekaligus struk yang bisa dicetak. */
    public function show(Request $request, Penjualan $penjualan): View
    {
        $user = $request->user();

        // 404 (bukan 403) supaya kasir tidak bisa menebak transaksi kasir lain.
        abort_if($user->isKasir() && (int) $penjualan->user_id !== $user->id, 404);

        return view('penjualan.show', [
            'penjualan' => $penjualan->load(['detail', 'kasir']),
            'metodeBayar' => self::METODE_BAYAR,
        ]);
    }

    /** Batalkan transaksi dan kembalikan stok obatnya. */
    public function destroy(Penjualan $penjualan): RedirectResponse
    {
        DB::transaction(function () use ($penjualan) {
            foreach ($penjualan->detail as $detail) {
                Obat::withTrashed()->whereKey($detail->obat_id)->increment('stok', $detail->jumlah);
            }

            $penjualan->detail()->delete();
            $penjualan->delete();
        });

        return redirect()->route('penjualan.index')
            ->with('success', "Transaksi {$penjualan->kode_transaksi} dibatalkan dan stok obat dikembalikan.");
    }
}
