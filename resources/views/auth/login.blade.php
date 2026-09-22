<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · {{ config('apotek.nama') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-body-tertiary">
<div class="container login-wrap d-flex align-items-center justify-content-center py-5">
    <div class="w-100" style="max-width: 24rem;">
        <div class="text-center mb-4">
            <div class="stat-card d-inline-block mb-2"><span class="stat-ikon"><i class="bi bi-capsule-pill"></i></span></div>
            <h1 class="h4 mb-0">{{ config('apotek.nama') }}</h1>
            <div class="text-body-secondary small">Masuk untuk melanjutkan</div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                @include('partials.alert')

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="username" autofocus required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="remember" value="0">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn btn-apotek w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
