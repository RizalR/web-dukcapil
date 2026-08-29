@extends('layouts.admin')

@section('title', 'Tambah Slide')

@section('content')
    <div class="mb-8"><p class="eyebrow">Slider beranda</p><h1 class="page-title mt-2">Tambah slide</h1></div>
    <form action="{{ route('admin.hero-slides.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @include('admin.hero-slides._form')
    </form>
@endsection
