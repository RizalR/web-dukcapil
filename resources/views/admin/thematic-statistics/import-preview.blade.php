@extends('layouts.admin')

@section('title', 'Pratinjau Statistik Tematik')

@section('content')
<div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
    <div>
        <p class="eyebrow">Pratinjau impor</p>
        <h1 class="page-title mt-2">Periksa data tematik</h1>
        <p class="mt-3 text-slate-600">{{ $pendingImport['period'] }} · {{ $pendingImport['source'] }}</p>
    </div>
    <a href="{{ route('admin.thematic-statistic-imports.create') }}" class="btn-secondary">Unggah ulang</a>
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Dataset', $pendingImport['dataset_count']],
        ['Wilayah unik', $pendingImport['area_count']],
        ['Baris publik', $pendingImport['row_count']],
        ['Observasi', $pendingImport['observation_count']],
    ] as [$label, $value])
        <article class="card p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-black text-ink">{{ number_format($value, 0, ',', '.') }}</p>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6">
    @foreach($pendingImport['datasets'] as $dataset)
        @php($sample = collect($dataset['observations'])->where('area_code', '75.01')->take(12))
        <article class="card overflow-hidden">
            <div class="border-b border-slate-200 p-5 sm:flex sm:items-start sm:justify-between sm:gap-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ocean">{{ $dataset['title'] }}</p>
                    <h2 class="mt-1 break-all text-lg font-bold text-ink">{{ $dataset['filename'] }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ number_format($dataset['area_count'], 0, ',', '.') }} wilayah · {{ number_format($dataset['row_count'], 0, ',', '.') }} baris · {{ number_format($dataset['observation_count'], 0, ',', '.') }} observasi</p>
                </div>
                <span class="mt-4 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800 sm:mt-0">Valid</span>
            </div>

            @if($dataset['warnings'] !== [])
                <div class="border-b border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                    <p class="font-bold">Peringatan yang tidak menghalangi impor:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($dataset['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Wilayah/dimensi</th><th class="px-5 py-3">Metrik</th><th class="px-5 py-3">Jenis kelamin</th><th class="px-5 py-3 text-right">Nilai</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($sample as $observation)
                            <tr>
                                <td class="px-5 py-3"><span class="block font-semibold text-ink">{{ $observation['area'] }}</span><span class="text-xs text-slate-500">{{ collect([$observation['dimension_one_value'], $observation['dimension_two_value']])->filter()->join(' · ') ?: 'Agregat' }}</span></td>
                                <td class="px-5 py-3">{{ str($observation['metric'])->replace('_', ' ')->title() }}</td>
                                <td class="px-5 py-3">{{ match($observation['sex']) { 'male' => 'Laki-laki', 'female' => 'Perempuan', default => 'Jumlah' } }}</td>
                                <td class="px-5 py-3 text-right font-bold text-ink">{{ number_format((float) $observation['value'], $observation['unit'] === 'percent' ? 2 : 0, ',', '.') }} {{ $observation['unit'] === 'percent' ? '%' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    @endforeach
</div>

<form action="{{ route('admin.thematic-statistic-imports.store') }}" method="post" class="card mt-8 p-6">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <input class="mt-1 size-4 rounded border-slate-300 text-ocean focus:ring-ocean" type="checkbox" name="publish_now" value="1">
        <span><span class="block font-bold text-ink">Terbitkan setelah impor</span><span class="mt-1 block text-sm text-slate-600">Versi aktif lama untuk jenis dan periode yang sama akan ditandai sebagai digantikan.</span></span>
    </label>
    <div class="mt-6 flex flex-wrap gap-3">
        <button class="btn-primary" type="submit">Konfirmasi impor</button>
        <a class="btn-secondary" href="{{ route('admin.thematic-statistic-imports.create') }}">Batal</a>
    </div>
</form>
@endsection
