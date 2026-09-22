@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
@include('partials.page-header', ['judul' => 'Tambah Kategori', 'ikon' => 'tags'])

<form method="POST" action="{{ route('kategori.store') }}" novalidate>
    @include('kategori._form')
</form>
@endsection
