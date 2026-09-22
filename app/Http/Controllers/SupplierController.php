<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $supplier = Supplier::withCount('obat')
            ->when($request->filled('search'), function ($q) use ($request) {
                $like = '%'.$request->query('search').'%';
                $q->where(fn ($sub) => $sub->where('nama', 'like', $like)->orWhere('nama_kontak', 'like', $like));
            })
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('supplier.index', ['supplier' => $supplier]);
    }

    public function create(): View
    {
        return view('supplier.create', ['supplier' => new Supplier(['aktif' => true])]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($this->dataTervalidasi($request));

        return redirect()->route('supplier.index')->with('success', "Supplier {$supplier->nama} berhasil ditambahkan.");
    }

    public function edit(Supplier $supplier): View
    {
        return view('supplier.edit', ['supplier' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->dataTervalidasi($request));

        return redirect()->route('supplier.index')->with('success', "Supplier {$supplier->nama} berhasil diperbarui.");
    }

    /** Obat yang memakai supplier ini tidak ikut terhapus; kolom supplier-nya jadi kosong. */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', "Supplier {$supplier->nama} berhasil dihapus.");
    }

    private function dataTervalidasi(SupplierRequest $request): array
    {
        return array_merge($request->validated(), ['aktif' => $request->boolean('aktif')]);
    }
}
