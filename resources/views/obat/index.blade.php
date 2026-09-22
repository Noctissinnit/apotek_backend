@extends('layouts.app')

@section('title', 'Data Obat')

@php
    $isAdmin = auth()->user()->isAdmin();

    // Link header kolom: klik sekali urut naik, klik lagi urut turun.
    $sortLink = function (string $kolom, string $label) {
        $aktif = request('sort_by', 'nama') === $kolom;
        $arah = $aktif && request('sort_dir') !== 'desc' ? 'desc' : 'asc';
        $ikon = $aktif ? (request('sort_dir') === 'desc' ? ' <i class="bi bi-sort-down"></i>' : ' <i class="bi bi-sort-up"></i>') : '';
        $url = request()->fullUrlWithQuery(['sort_by' => $kolom, 'sort_dir' => $arah, 'page' => null]);

        return '<a class="sort" href="'.e($url).'">'.e($label).$ikon.'</a>';
    };
@endphp

@section('content')
@include('partials.page-header', [
    'judul' => 'Data Obat',
    'ikon' => 'capsule',
    'sub' => $obat->total().' obat',
    'aksi' => $isAdmin ? '<a href="'.route('obat.create').'" class="btn btn-apotek"><i class="bi bi-plus-lg me-1"></i> Tambah Obat</a>' : null,
])

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('obat.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1" for="search">Cari</label>
                <input type="search" class="form-control" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Nama, kode, kandungan, produsen">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="kategori_id">Kategori</label>
                <select class="form-select" id="kategori_id" name="kategori_id">
                    <option value="">Semua kategori</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1" for="golongan">Golongan</label>
                <select class="form-select" id="golongan" name="golongan">
                    <option value="">Semua</option>
                    @foreach($golongan as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('golongan') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-apotek flex-fill"><i class="bi bi-search"></i> Cari</button>
                <a href="{{ route('obat.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-12 d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="stok_menipis" value="1" id="stok_menipis"
                           @checked(request()->boolean('stok_menipis')) onchange="this.form.submit()">
                    <label class="form-check-label small" for="stok_menipis">Hanya stok menipis</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="akan_kadaluarsa" value="1" id="akan_kadaluarsa"
                           @checked(request()->boolean('akan_kadaluarsa')) onchange="this.form.submit()">
                    <label class="form-check-label small" for="akan_kadaluarsa">Kadaluarsa ≤ 90 hari</label>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>{!! $sortLink('kode_obat', 'Kode') !!}</th>
                <th>{!! $sortLink('nama', 'Nama Obat') !!}</th>
                <th>Kategori</th>
                <th>Golongan</th>
                <th class="text-end">{!! $sortLink('harga_jual', 'Harga Jual') !!}</th>
                <th class="text-end">{!! $sortLink('stok', 'Stok') !!}</th>
                <th>{!! $sortLink('tanggal_kadaluarsa', 'Kadaluarsa') !!}</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($obat as $o)
                <tr class="{{ $o->aktif ? '' : 'table-secondary' }}">
                    <td class="text-nowrap small">{{ $o->kode_obat }}</td>
                    <td>
                        <a href="{{ route('obat.show', $o) }}" class="fw-semibold text-decoration-none">{{ $o->nama }}</a>
                        @unless($o->aktif) <span class="badge text-bg-secondary">nonaktif</span> @endunless
                        @if($o->kandungan)
                            <div class="small text-body-secondary">{{ $o->kandungan }}</div>
                        @endif
                    </td>
                    <td class="small">{{ $o->kategori?->nama }}</td>
                    <td>@include('obat._golongan', ['nilai' => $o->golongan])</td>
                    <td class="text-end text-nowrap">@rupiah($o->harga_jual)</td>
                    <td class="text-end text-nowrap">
                        <span class="badge {{ $o->stok <= $o->stok_minimum ? 'text-bg-danger' : 'text-bg-light' }}">
                            {{ $o->stok }} {{ $o->satuan }}
                        </span>
                    </td>
                    <td class="small text-nowrap">
                        @if($o->tanggal_kadaluarsa)
                            <span class="{{ $o->tanggal_kadaluarsa->lte(now()->addDays(90)) ? 'text-danger fw-semibold' : '' }}">
                                {{ $o->tanggal_kadaluarsa->format('d/m/Y') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('obat.show', $o) }}" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                        @if($isAdmin)
                            <a href="{{ route('obat.stok.edit', $o) }}" class="btn btn-sm btn-outline-success" title="Atur stok"><i class="bi bi-box-seam"></i></a>
                            <a href="{{ route('obat.edit', $o) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-body-secondary py-4">Tidak ada obat yang cocok dengan filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($obat->hasPages())
        <div class="card-footer bg-white">{{ $obat->links() }}</div>
    @endif
</div>
@endsection
