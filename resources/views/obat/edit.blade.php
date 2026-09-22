@extends('layouts.app')

@section('title', 'Ubah '.$obat->nama)

@section('content')
@include('partials.page-header', ['judul' => 'Ubah Obat', 'ikon' => 'pencil-square', 'sub' => $obat->kode_obat.' · '.$obat->nama])

<form method="POST" action="{{ route('obat.update', $obat) }}" novalidate>
    @method('PUT')
    @include('obat._form')
</form>
@endsection
