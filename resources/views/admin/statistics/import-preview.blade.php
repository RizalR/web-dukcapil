@extends('layouts.admin')

@section('title', 'Pratinjau Impor Statistik')

@section('content')
<div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
    <div>
        <p class="eyebrow">Pratinjau impor</p>
        <h1 class="page-title mt-2">Periksa data sebelum disimpan</h1>
        <p class="mt-3 text-slate-600">{{ $pendingImport['period'] }} · {{ $pendingImport['source'] }}</p>
    </div>
    <a href="{{ route('admin.population-statistic-imports.create') }}" class="btn-secondary">Unggah ulang</a>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-3">
    @foreach([
        ['Kategori dipilih', $pendingImport['dataset_count'], 'kategori'],
        ['Wilayah unik', $pendingImport['area_count'], 'wilayah'],
        ['Nilai statistik', $pendingImport['statistic_count'], 'nilai'],
    ] as [$label, $value, $unit])
        <article class="card p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-black text-ink">{{ number_format($value, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $unit }}</p>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6">
    @foreach($pendingImport['datasets'] as $dataset)
        @php($regency = collect($dataset['rows'])->firstWhere('area_level', 'kabupaten'))
        <article class="card overflow-hidden">
            <div class="border-b border-slate-200 p-5 sm:flex sm:items-start sm:justify-between sm:gap-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ocean">{{ $dataset['category'] }}</p>
                    <h2 class="mt-1 text-lg font-bold text-ink">{{ $dataset['filename'] }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ number_format($dataset['area_count'], 0, ',', '.') }} wilayah · {{ number_format($dataset['statistic_count'], 0, ',', '.') }} nilai</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-center sm:mt-0 sm:grid-cols-4">
                    @foreach([
                        ['Baru', $dataset['comparison']['new'], 'bg-sky-50 text-ocean'],
                        ['Berubah', $dataset['comparison']['updated'], 'bg-amber-50 text-amber-800'],
                        ['Tetap', $dataset['comparison']['unchanged'], 'bg-emerald-50 text-emerald-800'],
                        ['Tersisa', $dataset['comparison']['retained'], 'bg-slate-100 text-slate-700'],
                    ] as [$label, $value, $classes])
                        <div class="rounded-xl px-3 py-2 {{ $classes }}">
                            <p class="text-lg font-black">{{ number_format($value, 0, ',', '.') }}</p>
                            <p class="text-xs font-bold">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($dataset['comparison']['retained'] > 0)
                <p class="border-b border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-900">{{ number_format($dataset['comparison']['retained'], 0, ',', '.') }} data lama tidak ditemukan dalam file ini dan akan tetap disimpan, tidak dihapus otomatis.</p>
            @endif

            @if(is_array($regency))
                <div class="p-5">
                    <h3 class="text-sm font-bold text-ink">Ringkasan {{ $regency['area'] }}</h3>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($regency['values'] as $label => $value)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs font-semibold leading-5 text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 text-lg font-black text-ink">{{ number_format($value, 0, ',', '.') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </article>
    @endforeach
</div>

<form action="{{ route('admin.population-statistic-imports.store') }}" method="post" class="card mt-8 p-6">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <input class="mt-1 size-4 rounded border-slate-300 text-ocean focus:ring-ocean" type="checkbox" name="publish_now" value="1">
        <span>
            <span class="block font-bold text-ink">Terbitkan setelah impor</span>
            <span class="mt-1 block text-sm text-slate-600">Jika tidak dicentang, seluruh data pada file yang dipilih disimpan sebagai draf dan tidak terlihat oleh publik.</span>
        </span>
    </label>
    @error('token')<p class="mt-3 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
    <div class="mt-6 flex flex-wrap gap-3">
        <button class="btn-primary" type="submit">Konfirmasi impor</button>
        <a class="btn-secondary" href="{{ route('admin.population-statistic-imports.create') }}">Batal</a>
    </div>
</form>
@endsection
