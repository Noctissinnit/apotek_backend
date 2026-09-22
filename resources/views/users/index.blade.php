@extends('layouts.app')

@section('title', 'User')

@section('content')
@include('partials.page-header', [
    'judul' => 'User',
    'ikon' => 'people',
    'sub' => 'Akun admin dan kasir',
    'aksi' => '<a href="'.route('users.create').'" class="btn btn-apotek"><i class="bi bi-person-plus me-1"></i> Tambah User</a>',
])

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-7">
                <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama atau email">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Semua role</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="kasir" @selected(request('role') === 'kasir')>Kasir</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-apotek"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Terakhir login</th><th class="text-end">Transaksi</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($users as $u)
                <tr class="{{ $u->aktif ? '' : 'table-secondary' }}">
                    <td class="fw-semibold">
                        {{ $u->name }}
                        @if($u->is(auth()->user())) <span class="badge text-bg-light">Anda</span> @endif
                    </td>
                    <td class="small">{{ $u->email }}</td>
                    <td>
                        <span class="badge {{ $u->isAdmin() ? 'text-bg-primary' : 'text-bg-info' }} text-uppercase">{{ $u->role }}</span>
                    </td>
                    <td>
                        @if($u->aktif)
                            <span class="badge text-bg-success">Aktif</span>
                        @else
                            <span class="badge text-bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="small text-body-secondary">{{ $u->terakhir_login?->diffForHumans() ?? 'belum pernah' }}</td>
                    <td class="text-end">
                        <a href="{{ route('penjualan.index', ['user_id' => $u->id]) }}" class="badge text-bg-light text-decoration-none">{{ $u->penjualan_count }}</a>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary" title="Ubah"><i class="bi bi-pencil"></i></a>
                        @unless($u->is(auth()->user()))
                            <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}? Riwayat penjualannya tetap tersimpan.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-body-secondary py-4">Tidak ada user.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>
@endsection
