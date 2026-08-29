@extends('layouts.public')
@section('title', 'Profil Pegawai')
@section('meta_description', 'Profil kedinasan pegawai Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo.')
@section('content')
<section class="container-page py-14">
    <p class="eyebrow">Sumber daya manusia</p>
    <h1 class="page-title mt-3">Profil pegawai</h1>
    <p class="mt-4 max-w-3xl text-lg leading-8">Pejabat dan pegawai Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo.</p>

    <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($employees as $employee)
            <article class="card overflow-hidden">
                @if ($employee->photoUrl())
                    <img class="aspect-[4/5] w-full bg-slate-100 object-contain" src="{{ $employee->photoUrl() }}" alt="Foto {{ $employee->name }}" loading="lazy" decoding="async">
                @else
                    <div class="grid aspect-[4/5] w-full place-items-center bg-sky-soft text-5xl font-black text-ocean" aria-hidden="true">{{ str($employee->name)->explode(' ')->take(2)->map(fn($word) => str($word)->substr(0,1))->join('') }}</div>
                @endif
                <div class="p-5">
                    <h2 class="font-bold text-ink">{{ $employee->name }}</h2>
                    <p class="mt-1 text-sm font-semibold text-ocean">{{ $employee->position }}</p>
                    @if ($employee->unit)
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $employee->unit }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="card col-span-full p-8 text-center">Profil pegawai sedang diperbarui.</div>
        @endforelse
    </div>
</section>
@endsection
