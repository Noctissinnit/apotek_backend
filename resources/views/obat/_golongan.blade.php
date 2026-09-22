@php
    $warna = [
        'bebas' => 'text-bg-success',
        'bebas_terbatas' => 'text-bg-primary',
        'keras' => 'text-bg-danger',
        'narkotika' => 'text-bg-dark',
        'psikotropika' => 'text-bg-dark',
        'herbal' => 'text-bg-info',
    ][$nilai] ?? 'text-bg-secondary';
@endphp
<span class="badge {{ $warna }}">{{ \App\Http\Controllers\ObatController::GOLONGAN[$nilai] ?? $nilai }}</span>
