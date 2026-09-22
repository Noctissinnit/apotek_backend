@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
@include('partials.page-header', ['judul' => 'Ganti Password', 'ikon' => 'key', 'sub' => auth()->user()->email])

<div class="card" style="max-width: 32rem;">
    <div class="card-body">
        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="password_lama">Password lama</label>
                <input type="password" id="password_lama" name="password_lama" autocomplete="current-password" required
                       class="form-control @error('password_lama') is-invalid @enderror">
                @error('password_lama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password baru</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Minimal 8 karakter, berisi huruf dan angka.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Ulangi password baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required
                       class="form-control">
            </div>
            <div class="alert alert-light small">Perangkat lain yang sedang login dengan akun ini akan otomatis logout.</div>
            <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan Password</button>
        </form>
    </div>
</div>
@endsection
