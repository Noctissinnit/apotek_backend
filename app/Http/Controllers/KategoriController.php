<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $kategori = Kategori::withCount('obat')
            ->when($request->filled('search'), fn ($q) => $q->where('nama', 'like', '%'.$request->query('search').'%'))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('kategori.index', ['kategori' => $kategori]);
    }

    public function create(): View
    {
        return view('kategori.create', ['kategori' => new Kategori]);
    }

    public function store(KategoriRequest $request): RedirectResponse
    {
        $kategori = Kategori::create($request->validated());

        return redirect()->route('kategori.index')->with('success', "Kategori {$kategori->nama} berhasil ditambahkan.");
    }

    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', ['kategori' => $kategori]);
    }

    public function update(KategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        // Slug ikut diperbarui kalau nama berubah.
        $kategori->fill($request->validated());
        if ($kategori->isDirty('nama')) {
            $kategori->slug = null;
        }
        $kategori->save();

        return redirect()->route('kategori.index')->with('success', "Kategori {$kategori->nama} berhasil diperbarui.");
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->obat()->withTrashed()->exists()) {
            return back()->with('error', "Kategori {$kategori->nama} masih dipakai oleh data obat, tidak bisa dihapus.");
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', "Kategori {$kategori->nama} berhasil dihapus.");
    }
}
