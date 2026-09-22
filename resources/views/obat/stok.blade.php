@extends('layouts.app')

@section('title', 'Atur Stok '.$obat->nama)

@section('content')
@include('partials.page-header', ['judul' => 'Atur Stok', 'ikon' => 'box-seam', 'sub' => $obat->kode_obat.' · '.$obat->nama])

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded bg-body-tertiary">
                    <span class="text-body-secondary">Stok saat ini</span>
                    <span class="fs-4 fw-bold">{{ $obat->stok }} {{ $obat->satuan }}</span>
                </div>

                <form method="POST" action="{{ route('obat.stok.update', $obat) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label d-block">Jenis penyesuaian</label>
                        @foreach([
                            'tambah' => ['Barang masuk', 'Tambah stok dari kiriman supplier', 'plus-circle'],
                            'kurang' => ['Barang keluar', 'Rusak, hilang, retur, atau kadaluarsa', 'dash-circle'],
                            'set' => ['Stok opname', 'Set stok sesuai hasil hitung fisik', 'clipboard-check'],
                        ] as $nilai => [$label, $keterangan, $ikon])
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipe" id="tipe-{{ $nilai }}" value="{{ $nilai }}"
                                       @checked(old('tipe', 'tambah') === $nilai)>
                                <label class="form-check-label" for="tipe-{{ $nilai }}">
                                    <i class="bi bi-{{ $ikon }} me-1"></i><strong>{{ $label }}</strong>
                                    <span class="d-block small text-body-secondary">{{ $keterangan }}</span>
                                </label>
                            </div>
                        @endforeach
                        @error('tipe') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="jumlah">Jumlah ({{ $obat->satuan }})</label>
                        <input type="number" id="jumlah" name="jumlah" min="0" step="1" required autofocus
                               value="{{ old('jumlah') }}" class="form-control @error('jumlah') is-invalid @enderror">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan</button>
                        <a href="{{ route('obat.show', $obat) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
