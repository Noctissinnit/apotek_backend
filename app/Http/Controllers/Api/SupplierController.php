<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $supplier = Supplier::withCount('obat')
            ->when($request->filled('search'), function ($q) use ($request) {
                $like = '%'.$request->query('search').'%';
                $q->where(fn ($sub) => $sub->where('nama', 'like', $like)->orWhere('nama_kontak', 'like', $like));
            })
            ->when($request->filled('aktif'), fn ($q) => $q->where('aktif', $request->boolean('aktif')))
            ->orderBy('nama')
            ->paginate(min((int) $request->query('per_page', 25), 100))
            ->withQueryString();

        return SupplierResource::collection($supplier)->additional(['success' => true]);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return (new SupplierResource($supplier))
            ->additional(['success' => true, 'message' => 'Supplier berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Supplier $supplier)
    {
        return (new SupplierResource($supplier->loadCount('obat')))
            ->additional(['success' => true]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return (new SupplierResource($supplier->fresh()))
            ->additional(['success' => true, 'message' => 'Supplier berhasil diperbarui.']);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['success' => true, 'message' => 'Supplier berhasil dihapus.']);
    }
}
