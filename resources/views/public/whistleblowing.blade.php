@extends('layouts.public')
@section('title', 'Informasi Tindak Lanjut Whistleblowing')
@section('meta_description', 'Ringkasan anonim hasil tindak lanjut whistleblowing yang telah selesai dan disetujui untuk dipublikasikan oleh Dukcapil Kabupaten Gorontalo.')
@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-page py-14 sm:py-18">
        <p class="eyebrow">Transparansi pelayanan</p>
        <div class="mt-3 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <h1 class="page-title">Informasi tindak lanjut whistleblowing</h1>
                <p class="mt-4 max-w-3xl text-lg leading-8">Halaman ini hanya memuat ringkasan anonim dari aduan yang telah selesai ditindaklanjuti dan secara khusus disetujui untuk publikasi. Identitas pelapor, pihak terlapor, kronologi asli, dan bukti tidak ditampilkan.</p>
            </div>
            <a href="{{ config('dukcapil.service_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary">Sampaikan aduan melalui SI PRIMA ↗</a>
        </div>
    </div>
</section>

<section class="container-page py-12">
    <div class="card border-l-4 border-l-ocean p-5 sm:flex sm:items-center sm:justify-between sm:gap-6">
        <div>
            <p class="font-bold text-ink">Data publik yang telah dianonimkan</p>
            <p class="mt-1 text-sm leading-6">Publikasi bukan salinan aduan asli dan tidak dapat digunakan untuk mencari identitas pelapor.</p>
        </div>
        <div class="mt-3 shrink-0 text-sm sm:mt-0 sm:text-right">
            @if($syncState?->last_succeeded_at)
                <span class="block font-bold text-ink">Terakhir diperbarui</span>
                <time datetime="{{ $syncState->last_succeeded_at->toAtomString() }}">{{ $syncState->last_succeeded_at->translatedFormat('d F Y, H.i') }} WITA</time>
            @else
                <span class="font-semibold text-amber-700">Data belum pernah disinkronkan.</span>
            @endif
        </div>
    </div>

    @if($isStale && $syncState?->last_succeeded_at)
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900" role="status">Pembaruan terbaru dari SI PRIMA sedang tertunda. Informasi terakhir yang telah berhasil diverifikasi tetap ditampilkan.</div>
    @endif

    <form method="get" action="{{ route('whistleblowing.index') }}" class="card mt-8 grid gap-4 p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <label>
            <span class="form-label">Kategori</span>
            <select name="kategori" class="form-input">
                <option value="">Semua kategori</option>
                @foreach($categories as $code => $label)
                    <option value="{{ $code }}" @selected($selectedCategory === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="form-label">Tahun publikasi</span>
            <select name="tahun" class="form-input">
                <option value="">Semua tahun</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Terapkan</button>
            <a href="{{ route('whistleblowing.index') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        @forelse($publications as $publication)
            <article class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <div>
                        <span class="inline-flex rounded-full bg-sky-soft px-3 py-1 text-xs font-bold text-ocean">{{ $publication->category_label }}</span>
                        <p class="mt-2 font-mono text-xs font-semibold tracking-wide text-slate-500">{{ $publication->publication_id }}</p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <span class="block font-bold text-leaf">Selesai ditindaklanjuti</span>
                        <time datetime="{{ $publication->published_at->toAtomString() }}">Dipublikasikan {{ $publication->published_at->translatedFormat('d M Y') }}</time>
                    </div>
                </div>
                <div class="space-y-6 p-6">
                    <section>
                        <h2 class="text-base font-bold text-ink">Ringkasan aduan</h2>
                        <p class="mt-2 whitespace-pre-line leading-7">{{ $publication->summary }}</p>
                    </section>
                    <section class="rounded-2xl bg-sky-soft/70 p-5">
                        <h2 class="text-base font-bold text-ink">Hasil tindak lanjut</h2>
                        <p class="mt-2 whitespace-pre-line leading-7">{{ $publication->follow_up_result }}</p>
                    </section>
                </div>
            </article>
        @empty
            <div class="card p-10 text-center lg:col-span-2">
                <h2 class="text-xl font-bold text-ink">Belum ada hasil yang dapat ditampilkan</h2>
                <p class="mt-3 leading-7">Hasil akan tampil setelah aduan selesai, telah dianonimkan, dan mendapat persetujuan publikasi.</p>
            </div>
        @endforelse
    </div>

    @if($publications->hasPages())
        <div class="mt-8">{{ $publications->links() }}</div>
    @endif
</section>
@endsection
