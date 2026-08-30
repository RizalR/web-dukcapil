@extends('layouts.admin')

@section('title', 'Tambah Profil Pimpinan')

@section('content')
    <div class="mb-8">
        <p class="eyebrow">Profil pimpinan</p>
        <h1 class="page-title mt-2">Tambah profil pimpinan</h1>
    </div>
    <form action="{{ route('admin.leadership-profiles.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('admin.leadership-profiles._form')
    </form>
@endsection
