<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ObatRequest;
use App\Http\Requests\StokRequest;
use App\Http\Resources\ObatResource;
use App\Models\Obat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObatController extends Controller
{
    /** Kolom yang boleh dipakai untuk ?sort_by= */
    private const SORTABLE = ['nama', 'kode_obat', 'harga_jual', 'harga_beli', 'stok', 'tanggal_kadaluarsa', 'created_at'];

    /**
     * GET /api/obat
     * Filter: ?search= &kategori_id= &supplier_id= &golongan= &aktif= &stok_menipis=1 &akan_kadaluarsa=90
     * Sort  : ?sort_by=nama &sort_dir=asc   Paging: ?page=1 &per_page=15
     */
    public function index(Request $request)
    {
        $query = Obat::query()
            ->with(['kategori', 'supplier'])
            ->search($request->query('search'))
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('golongan'), fn ($q) => $q->where('golongan', $request->query('golongan')))
            ->when($request->filled('aktif'), fn ($q) => $q->where('aktif', $request->boolean('aktif')))
            ->when($request->boolean('stok_menipis'), fn ($q) => $q->stokMenipis())
            ->when($request->filled('akan_kadaluarsa'), fn ($q) => $q->akanKadaluarsa($request->integer('akan_kadaluarsa')));

        $sortBy = in_array($request->query('sort_by'), self::SORTABLE, true)
            ? $request->query('sort_by')
            : 'nama';
        $sortDir = $request->query('sort_dir') === 'desc' ? 'desc' : 'asc';

        $obat = $query->orderBy($sortBy, $sortDir)
            ->paginate(min((int) $request->query('per_page', 15), 100))
            ->withQueryString();

        return ObatResource::collection($obat)->additional(['success' => true]);
    }

    /** POST /api/obat */
    public function store(ObatRequest $request): JsonResponse
    {
        $obat = Obat::create($request->validated());

        return (new ObatResource($obat->load(['kategori', 'supplier'])))
            ->additional(['success' => true, 'message' => 'Obat berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/obat/{obat} */
    public function show(Obat $obat)
    {
        return (new ObatResource($obat->load(['kategori', 'supplier'])))
            ->additional(['success' => true]);
    }

    /** PUT|PATCH /api/obat/{obat} */
    public function update(ObatRequest $request, Obat $obat)
    {
        $obat->update($request->validated());

        return (new ObatResource($obat->fresh(['kategori', 'supplier'])))
            ->additional(['success' => true, 'message' => 'Obat berhasil diperbarui.']);
    }

    /** DELETE /api/obat/{obat} — soft delete, riwayat penjualan tetap utuh. */
    public function destroy(Obat $obat): JsonResponse
    {
        $obat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Obat berhasil dihapus.',
        ]);
    }

    /**
     * PATCH /api/obat/{obat}/stok
     * Body: { "tipe": "tambah|kurang|set", "jumlah": 10 }
     */
    public function ubahStok(StokRequest $request, Obat $obat): JsonResponse
    {
        $data = $request->validated();

        $obat = DB::transaction(function () use ($obat, $data) {
            // Kunci baris supaya dua request bersamaan tidak saling menimpa stok.
            $terkunci = Obat::whereKey($obat->id)->lockForUpdate()->firstOrFail();

            $stokBaru = match ($data['tipe']) {
                'tambah' => $terkunci->stok + $data['jumlah'],
                'kurang' => $terkunci->stok - $data['jumlah'],
                'set' => $data['jumlah'],
            };

            abort_if($stokBaru < 0, 422, "Stok tidak mencukupi. Stok saat ini {$terkunci->stok}.");

            $terkunci->update(['stok' => $stokBaru]);

            return $terkunci;
        });

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil diperbarui.',
            'data' => new ObatResource($obat->load(['kategori', 'supplier'])),
        ]);
    }
}
