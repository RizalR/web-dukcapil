@extends('layouts.admin')

@section('title', 'Edit Profil Pimpinan')

@section('content')
    <div class="mb-8">
        <p class="eyebrow">Profil pimpinan</p>
        <h1 class="page-title mt-2">Edit profil pimpinan</h1>
    </div>
    <form action="{{ route('admin.leadership-profiles.update', $leadershipProfile) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.leadership-profiles._form')
    </form>
@endsection
