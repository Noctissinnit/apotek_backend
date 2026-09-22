<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') · {{ config('apotek.nama') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-body-tertiary">
<div class="container login-wrap d-flex align-items-center justify-content-center">
    <div class="text-center" style="max-width: 28rem;">
        <div class="display-4 fw-bold text-apotek">@yield('kode')</div>
        <h1 class="h4">@yield('judul')</h1>
        <p class="text-body-secondary">@yield('pesan')</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-apotek">Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
