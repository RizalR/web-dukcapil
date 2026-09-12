@extends('layouts.admin')
@section('title', 'Edit Berita')
@section('content')
<p class="eyebrow">Perbarui konten</p><h1 class="page-title mt-2">Edit berita</h1><form action="{{ route('admin.news-articles.update',$article) }}" method="post" enctype="multipart/form-data" class="mt-8">@csrf @method('PUT') @include('admin.news._form')</form>
@endsection
