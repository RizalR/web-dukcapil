@extends('layouts.public')
@section('title', 'Statistik Tematik Kependudukan')
@section('meta_description', 'Visualisasi APAK, pendidikan menurut umur tunggal, serta kepemilikan Akta Kelahiran, Akta Kematian, dan KIA Kabupaten Gorontalo.')
@section('content')
<section class="container-page py-14">
    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="eyebrow">Data tematik</p>
            <h1 class="page-title mt-3">Penduduk, angkatan kerja, dan dokumen</h1>
            <p class="mt-4 max-w-3xl leading-7 text-slate-600">Data dinamis untuk membaca partisipasi angkatan kerja, pendidikan per umur, serta kepemilikan dokumen kependudukan. Semua angka ditampilkan apa adanya, termasuk nilai yang kecil.</p>
        </div>
        <a href="{{ route('information') }}" class="btn-secondary">Lihat data agregat</a>
    </div>

    @if($selectedPeriod)
        <form action="{{ route('thematic-statistics') }}" method="get" class="card mt-8 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
            <div>
                <label class="form-label" for="period">Periode data</label>
                <select class="form-input" id="period" name="period">
                    @foreach($periods as $period)
                        <option value="{{ $period }}" @selected($selectedPeriod === $period)>{{ $period }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="wilayah">Wilayah</label>
                <select class="form-input" id="wilayah" name="wilayah">
                    @foreach($areas as $area)
                        <option value="{{ $area['area_code'] }}" @selected(($selectedArea['area_code'] ?? null) === $area['area_code'])>{{ $area['area_level'] === \App\AdministrativeAreaLevel::Regency->value ? 'Kabupaten' : 'Kecamatan' }} · {{ $area['area'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary sm:col-span-2 lg:col-span-1">Tampilkan data</button>
        </form>

        <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-600">
            <strong class="text-ink">{{ $selectedArea['area'] ?? 'Wilayah belum tersedia' }}</strong>
            <span>{{ $selectedPeriod }}</span>
            <span>{{ $datasets->count() }} kelompok data diterbitkan</span>
        </div>

        <section class="mt-12" aria-labelledby="apak-title">
            <div class="max-w-3xl">
                <p class="eyebrow">Angkatan kerja</p>
                <h2 id="apak-title" class="section-title mt-2">Angka Partisipasi Angkatan Kerja (APAK)</h2>
                <p class="mt-3 leading-7 text-slate-600">APAK menunjukkan persentase penduduk yang termasuk angkatan kerja dibandingkan penduduk usia kerja pada kelompok yang sama.</p>
            </div>

            @if($apakHeadline)
                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    @foreach([['Keseluruhan', $apakHeadline['total'], 'bg-ocean'], ['Laki-laki', $apakHeadline['male'], 'bg-sky-500'], ['Perempuan', $apakHeadline['female'], 'bg-sun']] as [$label, $value, $color])
                        <article class="card overflow-hidden">
                            <div class="h-1.5 {{ $color }}"></div>
                            <div class="p-6">
                                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                                <p class="mt-2 text-4xl font-black tracking-tight text-ink">{{ number_format($value, 2, ',', '.') }}<span class="ml-1 text-xl">%</span></p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-slate-600">{{ number_format($apakHeadline['labor_force'], 0, ',', '.') }} orang dalam angkatan kerja dari {{ number_format($apakHeadline['population'], 0, ',', '.') }} penduduk pada dasar perhitungan. Sumber: {{ $apakHeadline['source'] }} · diterbitkan {{ $apakHeadline['published_at']?->translatedFormat('d F Y') }}.</p>
            @else
                <div class="card mt-7 p-7 text-center text-slate-600">Ringkasan APAK belum tersedia untuk wilayah ini.</div>
            @endif

            @php
                $breakdownSections = [
                    ['title' => 'Menurut kelompok umur', 'description' => 'Perbandingan persentase pada setiap rentang umur.', 'rows' => $apakByAge],
                    ['title' => 'Menurut pendidikan', 'description' => 'Perbandingan persentase berdasarkan pendidikan yang tercatat.', 'rows' => $apakByEducation],
                    ['title' => 'Menurut status dalam keluarga', 'description' => 'Perbandingan persentase berdasarkan status hubungan keluarga.', 'rows' => $apakByFamilyRelationship],
                ];
            @endphp
            <div class="mt-8 grid gap-6 xl:grid-cols-3">
                @foreach($breakdownSections as $section)
                    <article class="card overflow-hidden">
                        <div class="border-b border-slate-200 px-5 py-4">
                            <h3 class="text-lg font-bold text-ink">{{ $section['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $section['description'] }}</p>
                        </div>
                        <div class="max-h-[34rem] overflow-y-auto">
                            @forelse($section['rows'] as $row)
                                <div class="border-b border-slate-100 px-5 py-4 last:border-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-sm font-semibold text-ink">{{ $row['label'] }}</span>
                                        <strong class="shrink-0 text-sm text-ocean">{{ number_format($row['total'], 2, ',', '.') }}%</strong>
                                    </div>
                                    <svg class="mt-3 h-2 w-full" viewBox="0 0 100 8" preserveAspectRatio="none" role="img" aria-label="APAK keseluruhan {{ $row['label'] }} sebesar {{ number_format($row['total'], 2, ',', '.') }} persen">
                                        <rect width="100" height="8" rx="4" class="fill-slate-100" />
                                        <rect width="{{ number_format(min(100, $row['total']), 4, '.', '') }}" height="8" rx="4" class="fill-ocean" />
                                    </svg>
                                    <p class="mt-2 text-xs text-slate-500">Laki-laki {{ number_format($row['male'], 2, ',', '.') }}% · Perempuan {{ number_format($row['female'], 2, ',', '.') }}%</p>
                                </div>
                            @empty
                                <p class="p-6 text-center text-sm text-slate-500">Data belum tersedia.</p>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-16" aria-labelledby="education-age-title">
            <div class="max-w-3xl">
                <p class="eyebrow">Pendidikan</p>
                <h2 id="education-age-title" class="section-title mt-2">Pendidikan menurut umur tunggal</h2>
                <p class="mt-3 leading-7 text-slate-600">Setiap kotak menampilkan jumlah penduduk persis pada umur dan jenjang pendidikan tersebut. Warna membantu melihat komposisi dalam satu umur; angka nol dan angka kecil tetap ditampilkan.</p>
            </div>

            @if($educationAgeRows->isNotEmpty())
                <div class="mt-7 flex flex-wrap gap-4 text-xs font-semibold text-slate-600" aria-label="Legenda persentase">
                    @foreach([['bg-slate-50', '0%'], ['bg-sky-100', '>0–10%'], ['bg-sky-200', '>10–25%'], ['bg-sky-300', '>25–50%'], ['bg-ocean text-white', '>50%']] as [$class, $label])
                        <span class="flex items-center gap-2"><span class="size-5 rounded border border-slate-200 {{ $class }}"></span>{{ $label }}</span>
                    @endforeach
                </div>
                <div class="card mt-4 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[78rem] border-collapse text-center text-xs">
                            <thead class="sticky top-0 z-10 bg-ink text-white">
                                <tr>
                                    <th class="sticky left-0 z-20 min-w-20 bg-ink px-3 py-4 text-left">Umur</th>
                                    @foreach($educationLevels as $education)
                                        <th class="min-w-28 px-2 py-4 align-bottom"><span class="block leading-4">{{ $education }}</span></th>
                                    @endforeach
                                    <th class="min-w-24 px-3 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($educationAgeRows as $row)
                                    <tr class="border-b border-white">
                                        <th class="sticky left-0 z-[5] bg-white px-3 py-3 text-left text-sm font-bold text-ink">{{ $row['age'] }} tahun</th>
                                        @foreach($educationLevels as $education)
                                            @php
                                                $cell = $row['cells'][$education];
                                                $cellClass = match (true) {
                                                    $cell['percentage'] > 50 => 'bg-ocean text-white',
                                                    $cell['percentage'] > 25 => 'bg-sky-300 text-ink',
                                                    $cell['percentage'] > 10 => 'bg-sky-200 text-ink',
                                                    $cell['percentage'] > 0 => 'bg-sky-100 text-ink',
                                                    default => 'bg-slate-50 text-slate-500',
                                                };
                                            @endphp
                                            <td class="border-x border-white px-2 py-3 font-bold tabular-nums {{ $cellClass }}" title="Umur {{ $row['age'] }} tahun, {{ $education }}: {{ number_format($cell['value'], 0, ',', '.') }} orang ({{ number_format($cell['percentage'], 2, ',', '.') }}%)">{{ number_format($cell['value'], 0, ',', '.') }}</td>
                                        @endforeach
                                        <td class="bg-slate-100 px-3 py-3 text-right font-black tabular-nums text-ink">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Visualisasi pendidikan tersedia pada tingkat Kabupaten Gorontalo dan tidak berubah saat filter kecamatan dipilih.</p>
            @else
                <div class="card mt-7 p-7 text-center text-slate-600">Data pendidikan menurut umur tunggal belum diterbitkan untuk periode ini.</div>
            @endif
        </section>

        <section class="mt-16" aria-labelledby="documents-title">
            <div class="max-w-3xl">
                <p class="eyebrow">Kepemilikan dokumen</p>
                <h2 id="documents-title" class="section-title mt-2">Akta Kelahiran dan KIA</h2>
                <p class="mt-3 leading-7 text-slate-600">Persentase dihitung ulang dari jumlah sasaran dan jumlah pemilik pada data dinamis. Jumlah yang belum memiliki dokumen ditampilkan tanpa penyamaran.</p>
            </div>

            <div class="mt-7 grid gap-5 lg:grid-cols-2">
                @forelse($documentCards as $card)
                    <article class="card p-6">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <h3 class="text-lg font-bold text-ink">{{ $card['title'] }}</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $card['description'] }}</p>
                            </div>
                            <p class="shrink-0 text-3xl font-black tracking-tight text-ocean">{{ number_format($card['coverage'], 2, ',', '.') }}%</p>
                        </div>
                        <svg class="mt-5 h-4 w-full" viewBox="0 0 100 12" preserveAspectRatio="none" role="img" aria-label="{{ number_format($card['owned'], 0, ',', '.') }} memiliki dan {{ number_format($card['not_owned'], 0, ',', '.') }} belum memiliki dari {{ number_format($card['target'], 0, ',', '.') }} sasaran">
                            <rect width="100" height="12" rx="6" class="fill-rose-200" />
                            <rect width="{{ number_format(min(100, $card['coverage']), 4, '.', '') }}" height="12" rx="6" class="fill-ocean" />
                        </svg>
                        <dl class="mt-5 grid grid-cols-3 gap-3">
                            <div><dt class="text-xs font-semibold text-slate-500">Sasaran</dt><dd class="mt-1 text-lg font-black tabular-nums text-ink">{{ number_format($card['target'], 0, ',', '.') }}</dd></div>
                            <div><dt class="text-xs font-semibold text-slate-500">Memiliki</dt><dd class="mt-1 text-lg font-black tabular-nums text-ocean">{{ number_format($card['owned'], 0, ',', '.') }}</dd></div>
                            <div><dt class="text-xs font-semibold text-slate-500">Belum memiliki</dt><dd class="mt-1 text-lg font-black tabular-nums text-rose-700">{{ number_format($card['not_owned'], 0, ',', '.') }}</dd></div>
                        </dl>
                        <p class="mt-5 border-t border-slate-100 pt-4 text-xs text-slate-500">Sumber: {{ $card['source'] }} · diterbitkan {{ $card['published_at']?->translatedFormat('d F Y') }}</p>
                    </article>
                @empty
                    <div class="card p-7 text-center text-slate-600 lg:col-span-2">Data kepemilikan Akta Kelahiran dan KIA belum tersedia untuk wilayah ini.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-16" aria-labelledby="death-title">
            <div class="max-w-3xl">
                <p class="eyebrow">Pencatatan kematian</p>
                <h2 id="death-title" class="section-title mt-2">Akta Kematian</h2>
                <p class="mt-3 leading-7 text-slate-600">Data sumber tidak menyediakan populasi sasaran, sehingga bagian ini menyajikan jumlah dan tidak membuat persentase yang berpotensi menyesatkan.</p>
            </div>
            @if($deathCertificate)
                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    @foreach([['Akta terbit', $deathCertificate['issued']], ['Akta dibatalkan', $deathCertificate['cancelled']], ['Kematian tercatat', $deathCertificate['deaths']]] as [$label, $value])
                        <article class="card p-6">
                            <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight text-ink">{{ number_format($value, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500">dokumen/rekaman</p>
                        </article>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-slate-600">Sumber: {{ $deathCertificate['source'] }} · diterbitkan {{ $deathCertificate['published_at']?->translatedFormat('d F Y') }}.</p>
            @else
                <div class="card mt-7 p-7 text-center text-slate-600">Data Akta Kematian belum tersedia untuk wilayah ini.</div>
            @endif
        </section>
    @else
        <div class="card mt-8 p-10 text-center">
            <h2 class="text-xl font-bold text-ink">Data tematik belum diterbitkan</h2>
            <p class="mt-2 text-slate-600">Super Admin dapat mengunggah, memeriksa pratinjau, lalu menerbitkan workbook melalui panel pengelolaan.</p>
        </div>
    @endif
</section>
@endsection
