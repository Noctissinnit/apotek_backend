<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenjualanRequest;
use App\Http\Resources\PenjualanResource;
use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjualanController extends Controller
{
    /** GET /api/penjualan?search=&tanggal_dari=&tanggal_sampai= */
    public function index(Request $request)
    {
        $penjualan = Penjualan::query()
            ->withCount('detail')
            ->when($request->filled('search'), function ($q) use ($request) {
                $like = '%'.$request->query('search').'%';
                $q->where(fn ($sub) => $sub->where('kode_transaksi', 'like', $like)
                    ->orWhere('nama_pelanggan', 'like', $like));
            })
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->query('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->query('tanggal_sampai')))
            ->when($request->filled('metode_bayar'), fn ($q) => $q->where('metode_bayar', $request->query('metode_bayar')))
            ->latest('tanggal')
            ->paginate(min((int) $request->query('per_page', 15), 100))
            ->withQueryString();

        return PenjualanResource::collection($penjualan)->additional(['success' => true]);
    }

    /**
     * POST /api/penjualan
     * Harga diambil dari master obat (bukan dari client) dan baris stok dikunci
     * selama transaksi supaya tidak terjadi oversell saat request bersamaan.
     */
    public function store(PenjualanRequest $request): JsonResponse
    {
        $data = $request->validated();

        $penjualan = DB::transaction(function () use ($data) {
            // Gabungkan item dengan obat_id sama supaya stok dihitung sekali.
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
                        'items' => "Obat dengan id {$item['obat_id']} tidak tersedia untuk dijual.",
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
                    'bayar' => 'Jumlah bayar kurang dari total belanja ('.number_format($total, 2, ',', '.').').',
                ]);
            }

            $penjualan = Penjualan::create([
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

        return (new PenjualanResource($penjualan->load('detail')))
            ->additional(['success' => true, 'message' => 'Transaksi penjualan berhasil disimpan.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/penjualan/{penjualan} */
    public function show(Penjualan $penjualan)
    {
        return (new PenjualanResource($penjualan->load('detail.obat')))
            ->additional(['success' => true]);
    }

    /** DELETE /api/penjualan/{penjualan} - batalkan transaksi dan kembalikan stok. */
    public function destroy(Penjualan $penjualan): JsonResponse
    {
        DB::transaction(function () use ($penjualan) {
            foreach ($penjualan->detail as $detail) {
                Obat::whereKey($detail->obat_id)->increment('stok', $detail->jumlah);
            }

            $penjualan->detail()->delete();
            $penjualan->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi dibatalkan dan stok obat dikembalikan.',
        ]);
    }
}
