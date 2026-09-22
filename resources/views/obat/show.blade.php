@extends('layouts.app')

@section('title', $obat->nama)

@section('content')
@php($isAdmin = auth()->user()->isAdmin())

@include('partials.page-header', [
    'judul' => $obat->nama,
    'ikon' => 'capsule',
    'sub' => $obat->kode_obat,
    'aksi' => '<a href="'.route('obat.index').'" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>'
        .($isAdmin
            ? '<a href="'.route('obat.stok.edit', $obat).'" class="btn btn-outline-success"><i class="bi bi-box-seam me-1"></i> Atur Stok</a>'
              .'<a href="'.route('obat.edit', $obat).'" class="btn btn-apotek"><i class="bi bi-pencil me-1"></i> Ubah</a>'
            : ''),
])

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kategori</dt>
                    <dd class="col-sm-8">{{ $obat->kategori?->nama ?? '-' }}</dd>

                    <dt class="col-sm-4">Golongan</dt>
                    <dd class="col-sm-8">@include('obat._golongan', ['nilai' => $obat->golongan])</dd>

                    <dt class="col-sm-4">Bentuk sediaan</dt>
                    <dd class="col-sm-8">{{ $obat->bentuk_sediaan ?: '-' }}</dd>

                    <dt class="col-sm-4">Kandungan</dt>
                    <dd class="col-sm-8">{{ $obat->kandungan ?: '-' }}</dd>

                    <dt class="col-sm-4">Produsen</dt>
                    <dd class="col-sm-8">{{ $obat->produsen ?: '-' }}</dd>

                    <dt class="col-sm-4">Supplier</dt>
                    <dd class="col-sm-8">{{ $obat->supplier?->nama ?? '-' }}</dd>

                    <dt class="col-sm-4">Kadaluarsa</dt>
                    <dd class="col-sm-8">
                        @if($obat->tanggal_kadaluarsa)
                            {{ $obat->tanggal_kadaluarsa->translatedFormat('d F Y') }}
                            @if($obat->tanggal_kadaluarsa->isPast())
                                <span class="badge text-bg-danger ms-1">sudah kadaluarsa</span>
                            @elseif($obat->tanggal_kadaluarsa->lte(now()->addDays(90)))
                                <span class="badge text-bg-warning ms-1">≤ 90 hari lagi</span>
                            @endif
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($obat->aktif)
                            <span class="badge text-bg-success">Aktif dijual</span>
                        @else
                            <span class="badge text-bg-secondary">Nonaktif</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Deskripsi</dt>
                    <dd class="col-sm-8 mb-0">{{ $obat->deskripsi ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="text-body-secondary small">Stok</div>
                <div class="stat-nilai {{ $obat->stok <= $obat->stok_minimum ? 'text-danger' : '' }}" style="font-size:2rem;font-weight:700">
                    {{ $obat->stok }} <span class="fs-6 fw-normal">{{ $obat->satuan }}</span>
                </div>
                <div class="small text-body-secondary">Minimum {{ $obat->stok_minimum }} {{ $obat->satuan }}</div>
                @if($obat->stok <= $obat->stok_minimum)
                    <div class="alert alert-warning py-2 small mt-2 mb-0">Stok menipis, segera pesan ulang.</div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between"><span class="text-body-secondary">Harga jual</span><strong>@rupiah($obat->harga_jual)</strong></div>
                @if($isAdmin)
                    <div class="d-flex justify-content-between"><span class="text-body-secondary">Harga beli</span><span>@rupiah($obat->harga_beli)</span></div>
                    <div class="d-flex justify-content-between"><span class="text-body-secondary">Margin</span><span>@rupiah($obat->harga_jual - $obat->harga_beli)</span></div>
                @endif
            </div>
        </div>

        @if($isAdmin)
            <form method="POST" action="{{ route('obat.destroy', $obat) }}" class="mt-3"
                  onsubmit="return confirm('Hapus obat {{ addslashes($obat->nama) }}? Riwayat penjualannya tetap tersimpan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Hapus Obat</button>
            </form>
        @endif
    </div>
</div>
@endsection
