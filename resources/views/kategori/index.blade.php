@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
@include('partials.page-header', [
    'judul' => 'Kategori Obat',
    'ikon' => 'tags',
    'sub' => $kategori->total().' kategori',
    'aksi' => '<a href="'.route('kategori.create').'" class="btn btn-apotek"><i class="bi bi-plus-lg me-1"></i> Tambah Kategori</a>',
])

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama kategori">
            <button class="btn btn-apotek"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Deskripsi</th><th class="text-end">Jumlah obat</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($kategori as $k)
                <tr>
                    <td class="fw-semibold">{{ $k->nama }}</td>
                    <td class="small text-body-secondary">{{ $k->deskripsi }}</td>
                    <td class="text-end">
                        <a href="{{ route('obat.index', ['kategori_id' => $k->id]) }}" class="badge text-bg-light text-decoration-none">{{ $k->obat_count }}</a>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('kategori.edit', $k) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('kategori.destroy', $k) }}" class="d-inline"
                              onsubmit="return confirm('Hapus kategori {{ addslashes($k->nama) }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-body-secondary py-4">Belum ada kategori.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($kategori->hasPages())
        <div class="card-footer bg-white">{{ $kategori->links() }}</div>
    @endif
</div>
@endsection
