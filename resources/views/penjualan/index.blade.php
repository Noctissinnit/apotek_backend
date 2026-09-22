@extends('layouts.app')

@section('title', 'Riwayat Penjualan')

@section('content')
@php($isAdmin = auth()->user()->isAdmin())

@include('partials.page-header', [
    'judul' => 'Riwayat Penjualan',
    'ikon' => 'receipt',
    'sub' => $isAdmin ? 'Semua transaksi' : 'Transaksi yang Anda catat',
    'aksi' => '<a href="'.route('penjualan.create').'" class="btn btn-apotek"><i class="bi bi-cart-plus me-1"></i> Transaksi Baru</a>',
])

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1" for="search">Cari</label>
                <input type="search" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. transaksi / pelanggan">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="tanggal_dari">Dari tanggal</label>
                <input type="date" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="tanggal_sampai">Sampai</label>
                <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1" for="metode_bayar">Metode</label>
                <select id="metode_bayar" name="metode_bayar" class="form-select">
                    <option value="">Semua</option>
                    @foreach($metodeBayar as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('metode_bayar') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($isAdmin)
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="user_id">Kasir</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="">Semua kasir</option>
                        @foreach($kasir as $k)
                            <option value="{{ $k->id }}" @selected(request('user_id') == $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-1 d-grid">
                <button class="btn btn-apotek" title="Terapkan filter"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                @if($isAdmin)<th>Kasir</th>@endif
                <th>Pelanggan</th>
                <th class="text-end">Item</th>
                <th class="text-end">Total</th>
                <th>Metode</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($penjualan as $p)
                <tr>
                    <td class="fw-semibold text-nowrap"><a href="{{ route('penjualan.show', $p) }}" class="text-decoration-none">{{ $p->kode_transaksi }}</a></td>
                    <td class="small text-nowrap">{{ $p->tanggal->format('d/m/Y H:i') }}</td>
                    @if($isAdmin)<td class="small">{{ $p->kasir?->name ?? '(dihapus)' }}</td>@endif
                    <td class="small">{{ $p->nama_pelanggan ?: '-' }}</td>
                    <td class="text-end">{{ $p->detail_count }}</td>
                    <td class="text-end text-nowrap fw-semibold">@rupiah($p->total)</td>
                    <td><span class="badge text-bg-light">{{ $metodeBayar[$p->metode_bayar] ?? $p->metode_bayar }}</span></td>
                    <td class="text-end"><a href="{{ route('penjualan.show', $p) }}" class="btn btn-sm btn-outline-secondary" title="Lihat struk"><i class="bi bi-receipt"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-center text-body-secondary py-4">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($penjualan->hasPages())
        <div class="card-footer bg-white">{{ $penjualan->links() }}</div>
    @endif
</div>
@endsection
