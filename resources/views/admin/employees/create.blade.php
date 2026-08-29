@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
    <div class="mb-8">
        <p class="eyebrow">Profil pegawai</p>
        <h1 class="page-title mt-2">Tambah pegawai</h1>
    </div>
    <form action="{{ route('admin.employees.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('admin.employees._form')
    </form>
@endsection
