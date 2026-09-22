<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('apotek.nama') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="bg-body-tertiary">
@php($user = auth()->user())

<nav class="navbar navbar-expand-lg navbar-dark bg-apotek shadow-sm d-print-none">
    <div class="container-xl">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
            <i class="bi bi-capsule-pill me-1"></i> {{ config('apotek.nama') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-utama"
                aria-controls="menu-utama" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu-utama">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('penjualan.create')) active @endif" href="{{ route('penjualan.create') }}">
                        <i class="bi bi-cart-plus"></i> Kasir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('penjualan.index', 'penjualan.show')) active @endif" href="{{ route('penjualan.index') }}">
                        <i class="bi bi-receipt"></i> Penjualan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('obat.*')) active @endif" href="{{ route('obat.index') }}">
                        <i class="bi bi-capsule"></i> Obat
                    </a>
                </li>
                @if($user->isAdmin())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle @if(request()->routeIs('kategori.*', 'supplier.*', 'users.*')) active @endif"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Master Data
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('kategori.index') }}"><i class="bi bi-tags me-2"></i>Kategori</a></li>
                            <li><a class="dropdown-item" href="{{ route('supplier.index') }}"><i class="bi bi-truck me-2"></i>Supplier</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="bi bi-people me-2"></i>User</a></li>
                        </ul>
                    </li>
                @endif
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> {{ $user->name }}
                        <span class="badge text-bg-light text-uppercase ms-1">{{ $user->role }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('password.edit') }}"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container-xl py-4">
    @include('partials.alert')
    @yield('content')
</main>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
