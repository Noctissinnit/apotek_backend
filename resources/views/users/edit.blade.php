@extends('layouts.app')

@section('title', 'Ubah User')

@section('content')
@include('partials.page-header', ['judul' => 'Ubah User', 'ikon' => 'person-gear', 'sub' => $user->name.' · '.$user->email])

<form method="POST" action="{{ route('users.update', $user) }}" novalidate>
    @method('PUT')
    @include('users._form')
</form>
@endsection
