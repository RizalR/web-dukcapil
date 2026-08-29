@extends('layouts.admin')

@section('title', 'Edit Slide')

@section('content')
    <div class="mb-8"><p class="eyebrow">Slider beranda</p><h1 class="page-title mt-2">Edit slide</h1></div>
    <form action="{{ route('admin.hero-slides.update', $heroSlide) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.hero-slides._form')
    </form>
@endsection
