@extends('layouts.admin')
@section('title', 'Statistik Penduduk')
@section('content')
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div><p class="eyebrow">Agregat</p><h1 class="page-title mt-2">Statistik penduduk</h1></div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.population-statistic-imports.create') }}" class="btn-secondary">Impor Excel</a>
        <a href="{{ route('admin.population-statistics.create') }}" class="btn-primary">Tambah data</a>
    </div>
</div>
<div class="card mt-8 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-4">Indikator</th><th class="px-5 py-4">Wilayah</th><th class="px-5 py-4">Nilai</th><th class="px-5 py-4">Periode</th><th class="px-5 py-4">Publikasi</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($statistics as $statistic)
                    <tr><td class="px-5 py-4 font-bold text-ink">{{ $statistic->label }}</td><td class="px-5 py-4"><span class="block">{{ $statistic->area }}</span>@if($statistic->area_code)<span class="text-xs text-slate-500">{{ $statistic->area_code }}</span>@endif</td><td class="px-5 py-4">{{ number_format($statistic->value,0,',','.') }} {{ $statistic->unit }}</td><td class="px-5 py-4">{{ $statistic->period }}</td><td class="px-5 py-4">{{ $statistic->published_at?->translatedFormat('d M Y') ?? 'Belum terbit' }}</td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a class="font-bold text-ocean" href="{{ route('admin.population-statistics.edit',$statistic) }}">Edit</a><form action="{{ route('admin.population-statistics.destroy',$statistic) }}" method="post">@csrf @method('DELETE')<button class="font-bold text-rose-700">Hapus</button></form></div></td></tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $statistics->links() }}</div>
@endsection
