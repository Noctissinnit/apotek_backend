@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
@include('partials.page-header', ['judul' => 'Tambah Supplier', 'ikon' => 'truck'])

<form method="POST" action="{{ route('supplier.store') }}" novalidate>
    @include('supplier._form')
</form>
@endsection
