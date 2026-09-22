<?php

namespace App\Http\Controllers;

use App\Http\Requests\ObatRequest;
use App\Http\Requests\StokRequest;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ObatController extends Controller
{
    /** Kolom yang boleh dipakai untuk ?sort_by= */
    private const SORTABLE = ['nama', 'kode_obat', 'harga_jual', 'stok', 'tanggal_kadaluarsa'];

    public const GOLONGAN = [
        'bebas' => 'Bebas',
        'bebas_terbatas' => 'Bebas Terbatas',
        'keras' => 'Keras',
        'narkotika' => 'Narkotika',
        'psikotropika' => 'Psikotropika',
        'herbal' => 'Herbal',
    ];

    public function index(Request $request): View
    {
        $sortBy = in_array($request->query('sort_by'), self::SORTABLE, true) ? $request->query('sort_by') : 'nama';
        $sortDir = $request->query('sort_dir') === 'desc' ? 'desc' : 'asc';

        $obat = Obat::query()
            ->with(['kategori', 'supplier'])
            ->search($request->query('search'))
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('golongan'), fn ($q) => $q->where('golongan', $request->query('golongan')))
            ->when($request->boolean('stok_menipis'), fn ($q) => $q->stokMenipis())
            ->when($request->boolean('akan_kadaluarsa'), fn ($q) => $q->akanKadaluarsa(90))
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        return view('obat.index', [
            'obat' => $obat,
            'kategori' => Kategori::orderBy('nama')->get(),
            'golongan' => self::GOLONGAN,
        ]);
    }

    public function show(Obat $obat): View
    {
        return view('obat.show', [
            'obat' => $obat->load(['kategori', 'supplier']),
            'golongan' => self::GOLONGAN,
        ]);
    }

    public function create(): View
    {
        return view('obat.create', $this->dataForm(new Obat([
            'golongan' => 'bebas',
            'satuan' => 'strip',
            'stok' => 0,
            'stok_minimum' => 10,
            'aktif' => true,
        ])));
    }

    public function store(ObatRequest $request): RedirectResponse
    {
        $obat = Obat::create($this->dataTervalidasi($request));

        return redirect()->route('obat.show', $obat)->with('success', "Obat {$obat->nama} berhasil ditambahkan.");
    }

    public function edit(Obat $obat): View
    {
        return view('obat.edit', $this->dataForm($obat));
    }

    public function update(ObatRequest $request, Obat $obat): RedirectResponse
    {
        // Stok tidak ikut diubah dari form ini, supaya tidak menimpa penjualan
        // yang terjadi selama form dibuka. Pakai Atur Stok untuk mengubahnya.
        $obat->update(Arr::except($this->dataTervalidasi($request), ['stok']));

        return redirect()->route('obat.show', $obat)->with('success', "Obat {$obat->nama} berhasil diperbarui.");
    }

    /** Soft delete: riwayat penjualan yang memakai obat ini tetap utuh. */
    public function destroy(Obat $obat): RedirectResponse
    {
        $obat->delete();

        return redirect()->route('obat.index')->with('success', "Obat {$obat->nama} berhasil dihapus.");
    }

    public function editStok(Obat $obat): View
    {
        return view('obat.stok', ['obat' => $obat]);
    }

    /** tambah = barang masuk, kurang = keluar/rusak, set = stok opname. */
    public function updateStok(StokRequest $request, Obat $obat): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($obat, $data) {
            // Kunci baris supaya dua petugas yang bersamaan tidak saling menimpa stok.
            $terkunci = Obat::whereKey($obat->id)->lockForUpdate()->firstOrFail();

            $stokBaru = match ($data['tipe']) {
                'tambah' => $terkunci->stok + $data['jumlah'],
                'kurang' => $terkunci->stok - $data['jumlah'],
                'set' => $data['jumlah'],
            };

            if ($stokBaru < 0) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok tidak mencukupi. Stok saat ini {$terkunci->stok}.",
                ]);
            }

            $terkunci->update(['stok' => $stokBaru]);
        });

        return redirect()->route('obat.show', $obat)
            ->with('success', "Stok {$obat->nama} sekarang {$obat->fresh()->stok} {$obat->satuan}.");
    }

    private function dataForm(Obat $obat): array
    {
        return [
            'obat' => $obat,
            'kategori' => Kategori::orderBy('nama')->get(),
            'supplier' => Supplier::where('aktif', true)->orderBy('nama')->get(),
            'golongan' => self::GOLONGAN,
        ];
    }

    /** Checkbox yang tidak dicentang tidak ikut terkirim, jadi "aktif" diisi manual. */
    private function dataTervalidasi(ObatRequest $request): array
    {
        return array_merge($request->validated(), ['aktif' => $request->boolean('aktif')]);
    }
}
