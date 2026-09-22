@extends('layouts.app')

@section('title', 'Ubah Supplier')

@section('content')
@include('partials.page-header', ['judul' => 'Ubah Supplier', 'ikon' => 'truck', 'sub' => $supplier->nama])

<form method="POST" action="{{ route('supplier.update', $supplier) }}" novalidate>
    @method('PUT')
    @include('supplier._form')
</form>
@endsection
