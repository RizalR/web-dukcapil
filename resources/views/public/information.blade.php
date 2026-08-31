@extends('layouts.public')
@section('title', 'Data Agregat Kependudukan')
@section('meta_description', 'Dashboard data agregat kependudukan Kabupaten Gorontalo menurut periode, kategori, dan wilayah.')
@section('content')
<section class="container-page py-14">
    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
        <div>
            <p class="eyebrow">Data terbuka</p>
            <h1 class="page-title mt-3">Data agregat kependudukan</h1>
            <p class="mt-4 max-w-3xl leading-7">Statistik penduduk, kepala keluarga, umur, agama, pendidikan, status perkawinan, disabilitas, dan KTP-el yang telah diterbitkan Dinas Dukcapil Kabupaten Gorontalo.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('thematic-statistics', array_filter(['period' => $selectedPeriod])) }}" class="btn-primary">Statistik tematik</a>
            <a href="{{ route('statistics.export', array_filter(['period' => $selectedPeriod, 'kategori' => $selectedCategory?->slug()])) }}" class="btn-secondary">Unduh CSV</a>
        </div>
    </div>

    @if($periods->isNotEmpty() && $selectedCategory)
        <nav class="mt-8" aria-label="Kategori data agregat">
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('information', ['period' => $selectedPeriod, 'kategori' => $category->slug()]) }}" @if($category === $selectedCategory) aria-current="page" @endif class="flex h-full gap-3 rounded-2xl border p-4 transition {{ $category === $selectedCategory ? 'border-ocean bg-ocean text-white shadow-sm' : 'border-slate-200 bg-white text-ink hover:border-ocean hover:text-ocean' }}">
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg {{ $category === $selectedCategory ? 'bg-white/15' : 'bg-sky-soft text-ocean' }} text-xs font-black">{{ str($loop->iteration)->padLeft(2, '0') }}</span>
                            <span><strong class="block text-sm">{{ $category->value }}</strong><span class="mt-1 block text-xs {{ $category === $selectedCategory ? 'text-sky-100' : 'text-slate-500' }}">{{ $category->description() }}</span></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form action="{{ route('information') }}" method="get" class="card mt-8 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
            <input type="hidden" name="kategori" value="{{ $selectedCategory->slug() }}">
            <div>
                <label class="form-label" for="period">Periode data</label>
                <select class="form-input" id="period" name="period">
                    @foreach($periods as $period)
                        <option value="{{ $period }}" @selected($selectedPeriod === $period)>{{ $period }}</option>
                    @endforeach
                </select>
            </div>
            @if($selectedCategory !== \App\PopulationStatisticCategory::Gender)
                <div>
                    <label class="form-label" for="wilayah">Wilayah</label>
                    <select class="form-input" id="wilayah" name="wilayah">
                        @foreach($areas as $area)
                            <option value="{{ $area['area_code'] }}" @selected($selectedArea && $selectedArea['area_code'] === $area['area_code'])>{{ $area['area_level']->label() }} · {{ $area['area'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button class="btn-primary sm:col-span-2 lg:col-span-1" type="submit">Tampilkan data</button>
        </form>

        @if($selectedCategory === \App\PopulationStatisticCategory::Gender)
            @if($regency)
                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    @foreach([['Jumlah penduduk', $regency['total']], ['Laki-laki', $regency['male']], ['Perempuan', $regency['female']]] as [$label, $value])
                        <article class="card p-6"><p class="text-sm font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-black tracking-tight text-ink">{{ number_format($value, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">jiwa · {{ $selectedPeriod }}</p></article>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-slate-600">Sumber: {{ $regency['source'] }} · diperbarui {{ $regency['published_at']?->translatedFormat('d F Y') }}</p>

                <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)] lg:items-start">
                    <section class="card p-6" aria-labelledby="gender-composition-title" data-gender-composition-chart>
                        <p class="eyebrow">Komposisi kabupaten</p>
                        <h2 id="gender-composition-title" class="mt-2 text-xl font-bold text-ink">Penduduk menurut jenis kelamin</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Persentase penduduk laki-laki dan perempuan pada {{ $selectedPeriod }}.</p>
                        @if($genderComposition)
                            <div class="mt-6 grid place-items-center">
                                <div class="relative grid size-56 place-items-center">
                                    <svg class="size-56 -rotate-90" viewBox="0 0 160 160" role="img" aria-labelledby="gender-chart-title gender-chart-description">
                                        <title id="gender-chart-title">Komposisi jenis kelamin Kabupaten Gorontalo</title>
                                        <desc id="gender-chart-description">Laki-laki {{ number_format($genderComposition['male_percentage'], 1, ',', '.') }} persen dan perempuan {{ number_format($genderComposition['female_percentage'], 1, ',', '.') }} persen.</desc>
                                        <circle cx="80" cy="80" r="56" pathLength="100" fill="none" stroke-width="22" class="stroke-sun" />
                                        <circle cx="80" cy="80" r="56" pathLength="100" fill="none" stroke-width="22" stroke-dasharray="{{ $genderComposition['male_percentage'] }} {{ $genderComposition['female_percentage'] }}" class="stroke-ocean" />
                                    </svg>
                                    <div class="pointer-events-none absolute text-center" aria-hidden="true"><span class="block text-3xl font-black tracking-tight text-ink">{{ number_format($regency['total'], 0, ',', '.') }}</span><span class="text-xs font-semibold text-slate-500">jiwa</span></div>
                                </div>
                            </div>
                            <dl class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div class="rounded-xl bg-sky-50 p-4"><dt class="flex items-center gap-2 text-sm font-bold text-ink"><span class="size-3 rounded-sm bg-ocean" aria-hidden="true"></span>Laki-laki</dt><dd class="mt-2 text-sm"><strong class="text-lg text-ink">{{ number_format($genderComposition['male_percentage'], 1, ',', '.') }}%</strong> · {{ number_format($regency['male'], 0, ',', '.') }} jiwa</dd></div>
                                <div class="rounded-xl bg-amber-50 p-4"><dt class="flex items-center gap-2 text-sm font-bold text-ink"><span class="size-3 rounded-sm bg-sun" aria-hidden="true"></span>Perempuan</dt><dd class="mt-2 text-sm"><strong class="text-lg text-ink">{{ number_format($genderComposition['female_percentage'], 1, ',', '.') }}%</strong> · {{ number_format($regency['female'], 0, ',', '.') }} jiwa</dd></div>
                            </dl>
                        @else
                            <p class="mt-6 rounded-xl bg-slate-50 p-5 text-sm">Komposisi belum dapat dihitung karena nilai laki-laki dan perempuan masih kosong.</p>
                        @endif
                    </section>

                    <section class="card overflow-hidden" aria-labelledby="district-chart-title" data-district-population-chart>
                        <div class="border-b border-slate-200 px-5 py-4"><p class="eyebrow">Perbandingan wilayah</p><h2 id="district-chart-title" class="mt-2 text-xl font-bold text-ink">Penduduk per kecamatan</h2><p class="mt-1 text-sm text-slate-500">Panjang batang menggunakan skala yang sama.</p></div>
                        <div class="flex flex-wrap gap-5 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold"><span class="flex items-center gap-2"><span class="size-3 rounded-sm bg-ocean"></span>Laki-laki</span><span class="flex items-center gap-2"><span class="size-3 rounded-sm bg-sun"></span>Perempuan</span><span>Maksimum: {{ number_format($districtChartMaximum, 0, ',', '.') }} jiwa</span></div>
                        <ol class="divide-y divide-slate-200">
                            @forelse($districts as $district)
                                @php
                                    $maleWidth = ($district['male'] / $districtChartMaximum) * 100;
                                    $femaleWidth = ($district['female'] / $districtChartMaximum) * 100;
                                @endphp
                                <li class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(9rem,0.7fr)_minmax(12rem,1.3fr)] sm:items-center">
                                    <div><a class="font-bold text-ink hover:text-ocean" href="{{ route('information', ['period' => $selectedPeriod, 'kategori' => $selectedCategory->slug(), 'kecamatan' => $district['area_code']]) }}#desa-kelurahan">{{ $district['area'] }}</a><p class="mt-1 text-xs text-slate-500">{{ number_format($district['total'], 0, ',', '.') }} jiwa</p></div>
                                    <svg class="h-5 w-full" viewBox="0 0 100 12" preserveAspectRatio="none" role="img" aria-label="{{ $district['area'] }}: laki-laki {{ number_format($district['male'], 0, ',', '.') }} jiwa, perempuan {{ number_format($district['female'], 0, ',', '.') }} jiwa, jumlah {{ number_format($district['total'], 0, ',', '.') }} jiwa"><title>{{ $district['area'] }} — {{ number_format($district['total'], 0, ',', '.') }} jiwa</title><rect width="100" height="12" rx="6" class="fill-slate-100"/><rect width="{{ number_format($maleWidth, 4, '.', '') }}" height="12" class="fill-ocean"/><rect x="{{ number_format($maleWidth, 4, '.', '') }}" width="{{ number_format($femaleWidth, 4, '.', '') }}" height="12" class="fill-sun"/></svg>
                                </li>
                            @empty
                                <li class="px-5 py-10 text-center">Data kecamatan belum tersedia.</li>
                            @endforelse
                        </ol>
                    </section>
                </div>
            @endif

            <div class="mt-10 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-5 py-4"><h2 class="text-xl font-bold text-ink">Tabel penduduk per kecamatan</h2><p class="mt-1 text-sm text-slate-500">Pilih kecamatan untuk melihat rincian desa dan kelurahan.</p></div>
                <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-ink text-white"><tr><th class="px-5 py-4">Kecamatan</th><th class="px-5 py-4 text-right">Laki-laki</th><th class="px-5 py-4 text-right">Perempuan</th><th class="px-5 py-4 text-right">Jumlah</th><th class="px-5 py-4"><span class="sr-only">Rincian</span></th></tr></thead><tbody class="divide-y divide-slate-200">
                    @forelse($districts as $district)
                        <tr class="{{ $selectedDistrict && $selectedDistrict['area_code'] === $district['area_code'] ? 'bg-sky-50' : '' }}"><td class="px-5 py-4"><span class="block font-bold text-ink">{{ $district['area'] }}</span><span class="text-xs text-slate-500">{{ $district['area_code'] }}</span></td><td class="px-5 py-4 text-right">{{ number_format($district['male'], 0, ',', '.') }}</td><td class="px-5 py-4 text-right">{{ number_format($district['female'], 0, ',', '.') }}</td><td class="px-5 py-4 text-right font-bold">{{ number_format($district['total'], 0, ',', '.') }}</td><td class="px-5 py-4 text-right"><a class="font-bold text-ocean" href="{{ route('information', ['period' => $selectedPeriod, 'kategori' => $selectedCategory->slug(), 'kecamatan' => $district['area_code']]) }}#desa-kelurahan">Lihat rincian</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center">Data kecamatan belum tersedia.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>

            @if($selectedDistrict)
                <div id="desa-kelurahan" class="mt-10 scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4"><p class="eyebrow">Rincian wilayah</p><h2 class="mt-2 text-xl font-bold text-ink">Desa/kelurahan di {{ $selectedDistrict['area'] }}</h2></div>
                    <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-100 text-xs uppercase tracking-wider text-slate-600"><tr><th class="px-5 py-4">Desa/Kelurahan</th><th class="px-5 py-4 text-right">Laki-laki</th><th class="px-5 py-4 text-right">Perempuan</th><th class="px-5 py-4 text-right">Jumlah</th></tr></thead><tbody class="divide-y divide-slate-200">
                        @forelse($villages as $village)
                            <tr><td class="px-5 py-4"><span class="block font-bold text-ink">{{ $village['area'] }}</span><span class="text-xs text-slate-500">{{ $village['area_code'] }}</span></td><td class="px-5 py-4 text-right">{{ number_format($village['male'], 0, ',', '.') }}</td><td class="px-5 py-4 text-right">{{ number_format($village['female'], 0, ',', '.') }}</td><td class="px-5 py-4 text-right font-bold">{{ number_format($village['total'], 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center">Data desa/kelurahan belum tersedia.</td></tr>
                        @endforelse
                    </tbody></table></div>
                </div>
            @endif
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <article class="card p-6"><p class="text-sm font-semibold text-slate-500">{{ $selectedCategory->title() }}</p><p class="mt-2 text-3xl font-black tracking-tight text-ink">{{ number_format($headlineValue, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">{{ $selectedCategory->unit() }} · {{ $selectedPeriod }}</p></article>
                <article class="card p-6"><p class="text-sm font-semibold text-slate-500">Wilayah dipilih</p><p class="mt-2 text-xl font-black tracking-tight text-ink">{{ $selectedArea['area'] ?? '—' }}</p><p class="mt-1 text-xs text-slate-500">{{ $selectedArea ? $selectedArea['area_level']->label() : 'Wilayah belum tersedia' }}</p></article>
                <article class="card p-6"><p class="text-sm font-semibold text-slate-500">Jumlah indikator</p><p class="mt-2 text-3xl font-black tracking-tight text-ink">{{ $categoryChartRows->count() }}</p><p class="mt-1 text-xs text-slate-500">kategori rincian</p></article>
            </div>

            <section class="card mt-10 overflow-hidden" aria-labelledby="aggregate-chart-title" data-aggregate-category-chart>
                <div class="border-b border-slate-200 px-5 py-5"><p class="eyebrow">Visualisasi kategori</p><h2 id="aggregate-chart-title" class="mt-2 text-xl font-bold text-ink">{{ $selectedCategory->title() }} — {{ $selectedArea['area'] ?? 'wilayah belum tersedia' }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">{{ $selectedCategory->description() }} Batang menggunakan skala yang sama dalam wilayah terpilih.</p></div>
                <ol class="divide-y divide-slate-200">
                    @forelse($categoryChartRows as $row)
                        <li class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(12rem,0.8fr)_minmax(12rem,1.2fr)_6.5rem] sm:items-center">
                            <span class="font-semibold text-ink">{{ $row['label'] }}</span>
                            <svg class="h-5 w-full" viewBox="0 0 100 12" preserveAspectRatio="none" role="img" aria-label="{{ $row['label'] }}: {{ $row['display_value'] }} {{ $selectedCategory->unit() }}"><title>{{ $row['label'] }} — {{ $row['display_value'] }} {{ $selectedCategory->unit() }}</title><rect width="100" height="12" rx="6" class="fill-slate-100"/><rect width="{{ number_format($row['bar_percentage'], 4, '.', '') }}" height="12" rx="6" class="fill-ocean"/></svg>
                            <span class="text-right text-sm font-bold text-ink">{{ $row['display_value'] }} <span class="text-xs font-normal text-slate-500">{{ $selectedCategory->unit() }}</span></span>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center">Data belum tersedia untuk wilayah ini.</li>
                    @endforelse
                </ol>
            </section>

            @if($categorySource)
                <p class="mt-4 text-sm text-slate-600">Sumber: {{ $categorySource->source }} · diperbarui {{ $categorySource->published_at?->translatedFormat('d F Y') }}</p>
            @endif
        @endif

        @if($legacyStatistics->isNotEmpty())
            <div class="mt-10 overflow-hidden rounded-2xl border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h2 class="text-xl font-bold text-ink">Indikator lainnya</h2></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-100"><tr><th class="px-5 py-4">Indikator</th><th class="px-5 py-4">Wilayah</th><th class="px-5 py-4 text-right">Nilai</th></tr></thead><tbody class="divide-y divide-slate-200">@foreach($legacyStatistics as $statistic)<tr><td class="px-5 py-4 font-bold text-ink">{{ $statistic->label }}</td><td class="px-5 py-4">{{ $statistic->area }}</td><td class="px-5 py-4 text-right">{{ number_format($statistic->value, 0, ',', '.') }} {{ $statistic->unit }}</td></tr>@endforeach</tbody></table></div></div>
        @endif
    @else
        <div class="card mt-8 p-8 text-center">Data agregat belum diterbitkan.</div>
    @endif
</section>

<section class="bg-sky-soft py-14">
    <div class="container-page">
        <p class="eyebrow">Informasi pelayanan terkini</p>
        <h2 class="section-title mt-2">Status pencetakan KTP-el dan KIA</h2>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Status menunjukkan apakah pencetakan dokumen sedang tersedia pada waktu pemeriksaan terakhir.</p>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @forelse($printingStatuses as $printingStatus)
                <x-printing-status-card :printing-status="$printingStatus" />
            @empty
                <div class="card col-span-full p-8 text-center">Status pencetakan belum tersedia.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
