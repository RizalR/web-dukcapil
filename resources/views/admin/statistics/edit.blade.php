@extends('layouts.admin')
@section('title', 'Edit Statistik')
@section('content')
<p class="eyebrow">Perbarui data</p><h1 class="page-title mt-2">Edit statistik</h1><form action="{{ route('admin.population-statistics.update',$statistic) }}" method="post" class="mt-8">@csrf @method('PUT') @include('admin.statistics._form')</form>
@endsection
