@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php($user = auth()->user())

@include('partials.page-header', [
    'judul' => 'Dashboard',
    'ikon' => 'speedometer2',
    'sub' => 'Halo, '.$user->name.' · '.now()->translatedFormat('l, d F Y'),
    'aksi' => '<a href="'.route('penjualan.create').'" class="btn btn-apotek"><i class="bi bi-cart-plus me-1"></i> Buka Kasir</a>',
])

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
            <span class="stat-ikon"><i class="bi bi-receipt"></i></span>
            <div>
                <div class="text-body-secondary small">Transaksi hari ini{{ $user->isKasir() ? ' (Anda)' : '' }}</div>
                <div class="stat-nilai">{{ $transaksiHariIni }}</div>
            </div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
            <span class="stat-ikon"><i class="bi bi-cash-coin"></i></span>
            <div>
                <div class="text-body-secondary small">Omzet hari ini{{ $user->isKasir() ? ' (Anda)' : '' }}</div>
                <div class="stat-nilai">@rupiah($omzetHariIni)</div>
            </div>
        </div></div>
    </div>

    @if($user->isAdmin())
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
                <span class="stat-ikon"><i class="bi bi-graph-up-arrow"></i></span>
                <div>
                    <div class="text-body-secondary small">Omzet bulan ini</div>
                    <div class="stat-nilai">@rupiah($omzetBulanIni)</div>
                </div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
                <span class="stat-ikon"><i class="bi bi-box-seam"></i></span>
                <div>
                    <div class="text-body-secondary small">Nilai stok ({{ $totalObat }} obat)</div>
                    <div class="stat-nilai">@rupiah($nilaiStok)</div>
                </div>
            </div></div>
        </div>
    @else
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
                <span class="stat-ikon"><i class="bi bi-exclamation-triangle"></i></span>
                <div>
                    <div class="text-body-secondary small">Stok menipis</div>
                    <div class="stat-nilai">{{ $jumlahStokMenipis }}</div>
                </div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100"><div class="card-body d-flex gap-3 align-items-center">
                <span class="stat-ikon"><i class="bi bi-calendar-x"></i></span>
                <div>
                    <div class="text-body-secondary small">Kadaluarsa ≤ 90 hari</div>
                    <div class="stat-nilai">{{ $jumlahAkanKadaluarsa }}</div>
                </div>
            </div></div>
        </div>
    @endif
</div>

<div class="row g-3">
    <div class="{{ $user->isAdmin() ? 'col-lg-4' : 'col-lg-6' }}">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle text-warning me-1"></i> Stok menipis</span>
                <a href="{{ route('obat.index', ['stok_menipis' => 1]) }}" class="small">Lihat semua ({{ $jumlahStokMenipis }})</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($stokMenipis as $o)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('obat.show', $o) }}" class="text-decoration-none text-body">{{ $o->nama }}</a>
                        <span class="badge text-bg-danger">{{ $o->stok }} / min {{ $o->stok_minimum }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-body-secondary">Semua stok aman.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="{{ $user->isAdmin() ? 'col-lg-4' : 'col-lg-6' }}">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-x text-danger me-1"></i> Kadaluarsa ≤ 90 hari</span>
                <a href="{{ route('obat.index', ['akan_kadaluarsa' => 1]) }}" class="small">Lihat semua ({{ $jumlahAkanKadaluarsa }})</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($akanKadaluarsa as $o)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('obat.show', $o) }}" class="text-decoration-none text-body">{{ $o->nama }}</a>
                        <span class="badge {{ $o->tanggal_kadaluarsa->isPast() ? 'text-bg-danger' : 'text-bg-warning' }}">
                            {{ $o->tanggal_kadaluarsa->format('d/m/Y') }}
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-body-secondary">Tidak ada obat yang mendekati kadaluarsa.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if($user->isAdmin())
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-trophy text-apotek me-1"></i> Obat terlaris</div>
                <ul class="list-group list-group-flush">
                    @forelse($obatTerlaris as $i => $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="text-body-secondary me-2">{{ $i + 1 }}.</span>{{ $row->nama_obat }}</span>
                            <span class="badge text-bg-light">{{ (int) $row->total_terjual }} terjual</span>
                        </li>
                    @empty
                        <li class="list-group-item text-body-secondary">Belum ada penjualan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
