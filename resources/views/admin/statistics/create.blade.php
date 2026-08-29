@extends('layouts.admin')
@section('title', 'Tambah Statistik')
@section('content')
<p class="eyebrow">Data baru</p><h1 class="page-title mt-2">Tambah statistik</h1><form action="{{ route('admin.population-statistics.store') }}" method="post" class="mt-8">@csrf @include('admin.statistics._form')</form>
@endsection
