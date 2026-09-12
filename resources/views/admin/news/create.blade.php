@extends('layouts.admin')
@section('title', 'Tambah Berita')
@section('content')
<p class="eyebrow">Konten baru</p><h1 class="page-title mt-2">Tambah berita</h1><form action="{{ route('admin.news-articles.store') }}" method="post" enctype="multipart/form-data" class="mt-8">@csrf @include('admin.news._form')</form>
@endsection
