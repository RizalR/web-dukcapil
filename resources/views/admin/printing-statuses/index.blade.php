@extends('layouts.admin')

@section('title', 'Status pencetakan')

@section('content')
<p class="eyebrow">Informasi publik</p>
<h1 class="page-title mt-2">Status pencetakan KTP-el dan KIA</h1>
<p class="mt-3 max-w-3xl">Status berikut tampil langsung di beranda dan halaman Informasi. Pilih Ada apabila pencetakan sedang dilayani, atau Tidak ada apabila pencetakan belum dapat dilayani.</p>

<div class="mt-8 grid gap-4 md:grid-cols-2">
    @foreach($printingStatuses as $printingStatus)
        <article class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink">{{ $printingStatus->document->label() }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $printingStatus->location }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-sm font-bold {{ $printingStatus->checked_at === null ? 'bg-slate-100 text-slate-700' : ($printingStatus->is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">{{ $printingStatus->availabilityLabel() }}</span>
            </div>
            @if($printingStatus->note)<p class="mt-4 text-sm leading-6">{{ $printingStatus->note }}</p>@endif
            <p class="mt-3 text-xs text-slate-500">{{ $printingStatus->checked_at ? 'Diperiksa '.$printingStatus->checked_at->translatedFormat('d F Y, H.i').' WITA' : 'Belum pernah diperbarui' }}</p>
            <a href="{{ route('admin.printing-statuses.edit', $printingStatus) }}" class="btn-secondary mt-5">Perbarui status</a>
        </article>
    @endforeach
</div>
@endsection
