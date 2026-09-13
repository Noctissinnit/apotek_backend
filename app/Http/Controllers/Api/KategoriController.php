<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategori = Kategori::withCount('obat')
            ->when($request->filled('search'), fn ($q) => $q->where('nama', 'like', '%'.$request->query('search').'%'))
            ->orderBy('nama')
            ->paginate(min((int) $request->query('per_page', 50), 100))
            ->withQueryString();

        return KategoriResource::collection($kategori)->additional(['success' => true]);
    }

    public function store(KategoriRequest $request): JsonResponse
    {
        $kategori = Kategori::create($request->validated());

        return (new KategoriResource($kategori))
            ->additional(['success' => true, 'message' => 'Kategori berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Kategori $kategori)
    {
        return (new KategoriResource($kategori->loadCount('obat')))
            ->additional(['success' => true]);
    }

    public function update(KategoriRequest $request, Kategori $kategori)
    {
        $kategori->update($request->validated());

        return (new KategoriResource($kategori->fresh()))
            ->additional(['success' => true, 'message' => 'Kategori berhasil diperbarui.']);
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        abort_if(
            $kategori->obat()->exists(),
            409,
            'Kategori masih dipakai oleh data obat, tidak bisa dihapus.'
        );

        $kategori->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }
}
