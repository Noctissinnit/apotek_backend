@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
@include('partials.page-header', ['judul' => 'Ubah Kategori', 'ikon' => 'tags', 'sub' => $kategori->nama])

<form method="POST" action="{{ route('kategori.update', $kategori) }}" novalidate>
    @method('PUT')
    @include('kategori._form')
</form>
@endsection
