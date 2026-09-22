@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
@include('partials.page-header', ['judul' => 'Tambah User', 'ikon' => 'person-plus'])

<form method="POST" action="{{ route('users.store') }}" novalidate>
    @include('users._form')
</form>
@endsection
