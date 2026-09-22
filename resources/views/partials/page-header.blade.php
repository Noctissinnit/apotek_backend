{{-- @include('partials.page-header', ['judul' => '...', 'ikon' => 'capsule', 'sub' => '...']) + slot tombol via $aksi --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 d-print-none">
    <div>
        <h1 class="h4 mb-0"><i class="bi bi-{{ $ikon ?? 'circle' }} text-apotek me-1"></i> {{ $judul }}</h1>
        @isset($sub)
            <div class="text-body-secondary small">{{ $sub }}</div>
        @endisset
    </div>
    @isset($aksi)
        <div class="d-flex flex-wrap gap-2">{!! $aksi !!}</div>
    @endisset
</div>
