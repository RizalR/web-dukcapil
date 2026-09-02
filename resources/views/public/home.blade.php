@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', 'Informasi kependudukan, berita, status pencetakan KTP-el dan KIA, serta dokumen publik Dukcapil Kabupaten Gorontalo.')
c:\Users\ASUS\Downloads\web\favicon-logo-dukcapil.png
@section('content')
<section class="relative overflow-hidden bg-ink text-white">
    <img src="{{ $homepageBackgroundUrl }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 size-full object-cover object-center">
    <div class="absolute inset-0 bg-ink/80"></div>
    <div class="hero-glow absolute inset-0"></div>
    <div class="container-page relative grid gap-10 py-16 lg:grid-cols-[1.25fr_.75fr] lg:py-24">
        <div>
            <p class="text-sm font-bold tracking-[0.2em] text-amber-300 uppercase">Sistem Informasi Pelayanan Publik</p>
            <!--<h1 class="mt-5 max-w-4xl text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">Data kependudukan yang jelas, layanan yang tepat arah.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">Temukan berita, agregat kependudukan, ketersediaan bahan pencetakan, dokumen publik, dan informasi Dinas Dukcapil Kabupaten Gorontalo.</p>-->
            <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('information') }}" class="btn-primary bg-white text-ocean hover:bg-sky-100">Lihat data kependudukan</a><a href="{{ $serviceLink?->url ?? config('dukcapil.service_url') }}" target="_blank" rel="noopener noreferrer" class="btn-secondary border-white/30 bg-white/10 text-white hover:border-white hover:text-white">Urus dokumen di SI PRIMA ↗</a></div>
        </div>
        <div class="card self-end border-white/10 bg-white/10 p-6 text-white backdrop-blur">
            <p class="text-xs font-bold tracking-widest text-amber-300 uppercase">Jam pelayanan</p><p class="mt-3 text-xl font-bold">{{ config('dukcapil.office.hours') }}</p><p class="mt-3 text-sm leading-6 text-slate-300">Situs ini adalah media informasi. Pengajuan dokumen dilayani pada aplikasi terpisah.</p>
        </div>
    </div>
</section>

<section class="container-page -mt-7 relative z-10">
    <div class="grid gap-4 sm:grid-cols-3">
        @forelse ($statistics as $statistic)
            <article class="card p-6"><p class="text-sm font-semibold text-slate-500">{{ $statistic->label }}</p><p class="mt-2 text-3xl font-black tracking-tight text-ink">{{ number_format($statistic->value, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">{{ $statistic->unit }} · {{ $statistic->period }}</p></article>
        @empty
            <div class="card col-span-full p-6 text-center">Data agregat belum diterbitkan.</div>
        @endforelse
    </div>
</section>

@if ($leadershipProfiles->isNotEmpty())
    <section class="container-page py-16" aria-labelledby="leadership-heading">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="eyebrow">Pemerintah Kabupaten Gorontalo</p>
                <h2 id="leadership-heading" class="section-title mt-2">Pimpinan daerah dan perangkat daerah</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Mengenal pimpinan daerah serta Kepala Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo.</p>
            </div>
            <a href="{{ route('profile') }}" class="font-bold text-ocean hover:text-ocean-deep">Lihat profil dinas →</a>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($leadershipProfiles as $leadershipProfile)
                <x-leadership-profile-card :profile="$leadershipProfile" />
            @endforeach
        </div>
    </section>
@endif

@if ($heroSlides->isNotEmpty())
    <section class="container-page py-16" aria-labelledby="gallery-heading">
        <div>
            <p class="eyebrow">Galeri Dukcapil</p>
            <!-- <h2 id="gallery-heading" class="section-title mt-2">Informasi dalam gambar</h2> -->
        </div>

        <div class="mt-8" data-image-slider role="region" aria-roledescription="carousel" aria-label="Slider informasi Dukcapil" @if($heroSlides->count() > 1) tabindex="0" @endif>
            <div class="relative aspect-video min-h-64 overflow-hidden rounded-3xl bg-ink shadow-xl">
                @foreach ($heroSlides as $slide)
                    <figure data-slide class="absolute inset-0 transition-opacity duration-700 {{ $loop->first ? 'opacity-100' : 'pointer-events-none opacity-0' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}" aria-label="Slide {{ $loop->iteration }} dari {{ $heroSlides->count() }}">
                        <img class="pointer-events-none absolute inset-0 size-full scale-110 object-cover opacity-30 blur-2xl" src="{{ $slide->imageUrl() }}" alt="" aria-hidden="true">
                        <div class="pointer-events-none absolute inset-0 bg-slate-950/35"></div>
                        <img class="pointer-events-none relative z-10 size-full object-contain" src="{{ $slide->imageUrl() }}" alt="{{ $slide->alt_text }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async">
                        @if ($slide->caption)
                            <figcaption class="absolute inset-x-0 bottom-0 z-20 bg-gradient-to-t from-slate-950/90 to-transparent px-6 pt-20 pb-6 text-base font-semibold text-white sm:px-8 sm:pb-8 sm:text-lg">{{ $slide->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach

                @if ($heroSlides->count() > 1)
                    <button type="button" data-slide-previous class="absolute top-1/2 left-4 z-30 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-slate-950/65 text-xl font-bold text-white transition hover:bg-slate-950" aria-label="Tampilkan gambar sebelumnya">‹</button>
                    <button type="button" data-slide-next class="absolute top-1/2 right-4 z-30 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-slate-950/65 text-xl font-bold text-white transition hover:bg-slate-950" aria-label="Tampilkan gambar berikutnya">›</button>
                @endif
            </div>

            @if ($heroSlides->count() > 1)
                <div class="mt-4 flex justify-center gap-2" aria-label="Pilih gambar slider">
                    @foreach ($heroSlides as $slide)
                        <button type="button" data-slide-dot class="size-3 rounded-full transition {{ $loop->first ? 'bg-ocean' : 'bg-slate-300 hover:bg-slate-400' }}" aria-label="Tampilkan slide {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

<section class="container-page py-16">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="eyebrow">Pembaruan terkini</p><h2 class="section-title mt-2">Berita dan informasi terbaru</h2></div><a href="{{ route('news.index') }}" class="font-bold text-ocean hover:text-ocean-deep">Semua berita →</a></div>
    <div class="mt-8 grid gap-5 lg:grid-cols-3">
        @forelse ($latestNews as $article)
            <article class="card flex flex-col overflow-hidden"><div class="h-2 bg-gradient-to-r from-ocean to-leaf"></div><div class="flex flex-1 flex-col p-6"><p class="text-xs font-bold text-ocean">{{ $article->published_at->translatedFormat('d F Y') }}</p><h3 class="mt-3 text-xl font-bold text-ink"><a href="{{ route('news.show', $article) }}" class="hover:text-ocean">{{ $article->title }}</a></h3><p class="mt-3 line-clamp-3 text-sm leading-6">{{ $article->excerpt }}</p><a href="{{ route('news.show', $article) }}" class="mt-6 text-sm font-bold text-ocean">Baca selengkapnya →</a></div></article>
        @empty
            <div class="card col-span-full p-8 text-center">Belum ada berita yang diterbitkan.</div>
        @endforelse
    </div>
</section>

<section class="bg-sky-soft py-16">
    <div class="container-page">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="eyebrow">Informasi pelayanan terkini</p><h2 class="section-title mt-2">Status pencetakan dokumen</h2><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Periksa apakah pencetakan KTP-el dan KIA sedang tersedia sebelum datang ke lokasi pelayanan.</p></div><a href="{{ route('information') }}" class="font-bold text-ocean">Rincian informasi →</a></div>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @forelse ($printingStatuses as $printingStatus)
                <x-printing-status-card :printing-status="$printingStatus" />
            @empty
                <div class="card col-span-full p-8 text-center">Status pencetakan belum tersedia.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="container-page py-16"><div class="rounded-3xl bg-gradient-to-br from-ocean to-ocean-deep p-8 text-white shadow-xl sm:p-10"><div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center"><div><p class="text-sm font-bold tracking-widest text-amber-300 uppercase">Butuh mengurus dokumen?</p><h2 class="mt-3 text-3xl font-black">Pelayanan administrasi ada di SI PRIMA.</h2><p class="mt-3 max-w-2xl text-sky-100">Anda akan meninggalkan situs informasi ini dan menuju aplikasi pelayanan resmi Kabupaten Gorontalo.</p></div><a href="{{ config('dukcapil.service_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary bg-white text-ocean hover:bg-sky-100">Buka SI PRIMA ↗</a></div></div></section>
@endsection
