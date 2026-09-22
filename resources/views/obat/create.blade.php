@extends('layouts.app')

@section('title', 'Tambah Obat')

@section('content')
@include('partials.page-header', ['judul' => 'Tambah Obat', 'ikon' => 'plus-circle'])

<form method="POST" action="{{ route('obat.store') }}" novalidate>
    @include('obat._form')
</form>
@endsection
