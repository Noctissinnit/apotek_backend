@extends('errors.minimal-apotek')

@section('kode', '403')
@section('judul', 'Akses ditolak')
@section('pesan', $exception->getMessage() ?: 'Anda tidak punya akses ke halaman ini.')
