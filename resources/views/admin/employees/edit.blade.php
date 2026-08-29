@extends('layouts.admin')

@section('title', 'Edit Pegawai')

@section('content')
    <div class="mb-8">
        <p class="eyebrow">Profil pegawai</p>
        <h1 class="page-title mt-2">Edit pegawai</h1>
    </div>
    <form action="{{ route('admin.employees.update', $employee) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.employees._form')
    </form>
@endsection
