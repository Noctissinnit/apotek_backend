@extends('layouts.app')

@section('title', 'Struk '.$penjualan->kode_transaksi)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
    <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Riwayat</a>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak Struk</button>
        <a href="{{ route('penjualan.create') }}" class="btn btn-apotek"><i class="bi bi-cart-plus me-1"></i> Transaksi Baru</a>
    </div>
</div>

<div class="card struk">
    <div class="card-body">
        <div class="text-center">
            <div class="fw-bold fs-5">{{ config('apotek.nama') }}</div>
            <div class="small">{{ config('apotek.alamat') }}</div>
            @if(config('apotek.telepon') && config('apotek.telepon') !== '-')
                <div class="small">Telp. {{ config('apotek.telepon') }}</div>
            @endif
        </div>
        <hr>
        <div class="small">
            <div class="d-flex justify-content-between"><span>No</span><span>{{ $penjualan->kode_transaksi }}</span></div>
            <div class="d-flex justify-content-between"><span>Tanggal</span><span>{{ $penjualan->tanggal->format('d/m/Y H:i') }}</span></div>
            <div class="d-flex justify-content-between"><span>Kasir</span><span>{{ $penjualan->kasir?->name ?? '-' }}</span></div>
            @if($penjualan->nama_pelanggan)
                <div class="d-flex justify-content-between"><span>Pelanggan</span><span>{{ $penjualan->nama_pelanggan }}</span></div>
            @endif
        </div>
        <hr>
        @foreach($penjualan->detail as $d)
            <div class="mb-1">
                <div>{{ $d->nama_obat }}</div>
                <div class="d-flex justify-content-between small">
                    <span>{{ $d->jumlah }} x {{ number_format($d->harga_satuan, 0, ',', '.') }}</span>
                    <span>{{ number_format($d->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
        <hr>
        <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span>@rupiah($penjualan->total)</span></div>
        <div class="d-flex justify-content-between"><span>Bayar ({{ $metodeBayar[$penjualan->metode_bayar] ?? $penjualan->metode_bayar }})</span><span>@rupiah($penjualan->bayar)</span></div>
        <div class="d-flex justify-content-between"><span>Kembalian</span><span>@rupiah($penjualan->kembalian)</span></div>
        @if($penjualan->catatan)
            <hr>
            <div class="small">Catatan: {{ $penjualan->catatan }}</div>
        @endif
        <hr>
        <div class="text-center small">Terima kasih, semoga lekas sembuh.</div>
    </div>
</div>

@if(auth()->user()->isAdmin())
    <div class="struk mt-3 d-print-none">
        <form method="POST" action="{{ route('penjualan.destroy', $penjualan) }}"
              onsubmit="return confirm('Batalkan transaksi {{ $penjualan->kode_transaksi }}? Stok obat akan dikembalikan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i> Batalkan Transaksi</button>
        </form>
    </div>
@endif
@endsection
