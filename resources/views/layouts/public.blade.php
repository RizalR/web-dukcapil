<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $siteUrl = rtrim((string) config('dukcapil.public_url'), '/');
        $pageTitle = trim($__env->yieldContent('title', 'Beranda')).' — Dukcapil Kabupaten Gorontalo';
        $metaDescription = trim($__env->yieldContent('meta_description', (string) config('dukcapil.seo.description')));
        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: $siteUrl.(request()->getPathInfo() === '/' ? '/' : request()->getPathInfo());
        $robots = trim($__env->yieldContent('robots', 'index,follow,max-image-preview:large'));
        $openGraphType = trim($__env->yieldContent('og_type', 'website'));
        $socialImage = (string) config('dukcapil.seo.social_image');
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => config('dukcapil.name'),
            'url' => $siteUrl.'/',
            'logo' => $siteUrl.'/images/logo_kabgor.png',
            'email' => config('dukcapil.office.email'),
            'telephone' => config('dukcapil.office.phone'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('dukcapil.office.address'),
                'addressLocality' => 'Limboto',
                'addressRegion' => 'Gorontalo',
                'postalCode' => '96212',
                'addressCountry' => 'ID',
            ],
        ];
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $openGraphType }}">
    <meta property="og:site_name" content="Dukcapil Kabupaten Gorontalo">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:alt" content="Dinas Dukcapil Kabupaten Gorontalo">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">
    @hasSection('article_published_time')
        <meta property="article:published_time" content="@yield('article_published_time')">
    @endif
    <title>{{ $pageTitle }}</title>
    <script type="application/ld+json" nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <a href="#konten-utama" class="sr-only z-50 rounded bg-white px-4 py-2 focus:not-sr-only focus:fixed focus:top-4 focus:left-4">Lewati ke konten utama</a>

    <div class="bg-ocean-deep text-white">
        <div class="container-page flex min-h-10 items-center justify-between gap-4 py-2 text-xs sm:text-sm">
            <p>Portal informasi resmi Dukcapil Kabupaten Gorontalo</p>
            <a href="{{ config('dukcapil.service_url') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-amber-300 hover:text-white">Buka layanan SI PRIMA ↗</a>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="container-page flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Beranda Dukcapil Kabupaten Gorontalo">
                <img src="{{ asset('images/logo_kabgor.png') }}" alt="Lambang Kabupaten Gorontalo" width="40" height="46" class="shrink-0">
                <span class="leading-tight"><strong class="block text-sm text-ink sm:text-base">Dukcapil</strong><span class="text-xs text-slate-500 sm:text-sm">Kabupaten Gorontalo</span></span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                @foreach ([['home','Beranda'],['services','Pelayanan'],['profile','Profil'],['news.index','Berita'],['information','Data'],['documents','Dokumen'],['employees','Pegawai'],['employee-apps','Aplikasi'],['contact','Kontak']] as [$routeName, $label])
                    <a href="{{ route($routeName) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs($routeName) ? 'bg-sky-soft text-ocean' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" class="grid size-11 place-items-center rounded-xl border border-slate-300 text-ink lg:hidden">
                <span class="sr-only">Buka navigasi</span><span class="text-xl">☰</span>
            </button>
        </div>
        <nav id="mobile-menu" data-mobile-menu class="container-page hidden border-t border-slate-200 py-3 lg:hidden" aria-label="Navigasi seluler">
            <div class="grid grid-cols-2 gap-2">
                @foreach ([['home','Beranda'],['services','Pelayanan'],['profile','Profil'],['news.index','Berita'],['information','Data'],['documents','Dokumen'],['employees','Pegawai'],['employee-apps','Aplikasi'],['contact','Kontak']] as [$routeName, $label])
                    <a href="{{ route($routeName) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs($routeName) ? 'bg-sky-soft text-ocean' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main id="konten-utama">@yield('content')</main>

    <footer class="mt-20 bg-ink text-slate-300">
        <div class="container-page grid gap-10 py-12 md:grid-cols-[1.2fr_1fr_1fr]">
            <div><p class="text-lg font-bold text-white">Dukcapil Kabupaten Gorontalo</p><p class="mt-3 max-w-md text-sm leading-6">Media informasi kependudukan, berita, dokumen publik, dan profil Dinas Kependudukan dan Pencatatan Sipil.</p></div>
            <div><p class="font-bold text-white">Kontak</p><p class="mt-3 text-sm leading-6">{{ config('dukcapil.office.address') }}<br>{{ config('dukcapil.office.phone') }}<br>{{ config('dukcapil.office.email') }}</p></div>
            <div><p class="font-bold text-white">Akses cepat</p><div class="mt-3 flex flex-col gap-2 text-sm"><a href="{{ route('documents') }}" class="hover:text-white">Dokumen publik</a><a href="{{ route('statistics.export') }}" class="hover:text-white">Unduh data CSV</a><a href="{{ route('login') }}" class="hover:text-white">Masuk pengelola</a></div></div>
        </div>
        <div class="border-t border-white/10"><div class="container-page py-4 text-xs">© {{ date('Y') }} Pemerintah Kabupaten Gorontalo. Informasi diperbarui oleh Dinas Dukcapil.</div></div>
    </footer>
</body>
</html>
