@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
@include('partials.page-header', [
    'judul' => 'Supplier',
    'ikon' => 'truck',
    'sub' => $supplier->total().' supplier',
    'aksi' => '<a href="'.route('supplier.create').'" class="btn btn-apotek"><i class="bi bi-plus-lg me-1"></i> Tambah Supplier</a>',
])

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama supplier atau kontak">
            <button class="btn btn-apotek"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Kontak</th><th>Alamat</th><th class="text-end">Obat</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($supplier as $s)
                <tr>
                    <td class="fw-semibold">{{ $s->nama }}</td>
                    <td class="small">
                        {{ $s->nama_kontak ?: '-' }}
                        @if($s->telepon)<div class="text-body-secondary"><i class="bi bi-telephone"></i> {{ $s->telepon }}</div>@endif
                        @if($s->email)<div class="text-body-secondary"><i class="bi bi-envelope"></i> {{ $s->email }}</div>@endif
                    </td>
                    <td class="small text-body-secondary">{{ $s->alamat }}</td>
                    <td class="text-end"><span class="badge text-bg-light">{{ $s->obat_count }}</span></td>
                    <td>
                        @if($s->aktif)
                            <span class="badge text-bg-success">Aktif</span>
                        @else
                            <span class="badge text-bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('supplier.edit', $s) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('supplier.destroy', $s) }}" class="d-inline"
                              onsubmit="return confirm('Hapus supplier {{ addslashes($s->nama) }}? Obat terkait tidak ikut terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-body-secondary py-4">Belum ada supplier.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($supplier->hasPages())
        <div class="card-footer bg-white">{{ $supplier->links() }}</div>
    @endif
</div>
@endsection
