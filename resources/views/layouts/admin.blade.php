<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-logo-dukcapil.png') }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Panel Admin') — Dukcapil Kabupaten Gorontalo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="bg-ink p-5 text-slate-300 lg:min-h-screen">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-white"><span class="grid size-10 place-items-center rounded-xl bg-ocean font-black">DG</span><span><strong class="block">Panel Dukcapil</strong><small class="text-slate-400">Pengelolaan informasi</small></span></a>
            <nav class="mt-8 grid gap-1" aria-label="Navigasi admin">
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Ringkasan</a>
                <a href="{{ route('admin.news-articles.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.news-articles.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Berita</a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.homepage.edit') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.homepage.*', 'admin.hero-slides.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Tampilan beranda</a>
                    <a href="{{ route('admin.leadership-profiles.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.leadership-profiles.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Profil pimpinan</a>
                    <a href="{{ route('admin.employees.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.employees.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Profil pegawai</a>
                    <a href="{{ route('admin.population-statistics.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.population-statistics.*', 'admin.population-statistic-imports.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Statistik penduduk</a>
                    <a href="{{ route('admin.thematic-statistic-datasets.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.thematic-statistic-datasets.*', 'admin.thematic-statistic-imports.*', 'admin.published-thematic-statistic-datasets.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Statistik tematik</a>
                    <a href="{{ route('admin.printing-statuses.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.printing-statuses.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Status pencetakan</a>
                    <a href="{{ route('admin.supply-statuses.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.supply-statuses.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Status bahan</a>
                    <a href="{{ route('admin.audit-logs') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.audit-logs') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Audit log</a>
                    @if(config('dukcapil.maintenance.web_migrations_enabled'))
                        <a href="{{ route('admin.database-migrations.create') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.database-migrations.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Migrasi database</a>
                    @endif
                    <a href="{{ route('admin.two-factor.edit') }}" class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.two-factor.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">Keamanan akun</a>
                @endif
            </nav>
            <div class="mt-8 border-t border-white/10 pt-5 text-sm"><p class="font-bold text-white">{{ auth()->user()->name }}</p><p class="mt-1 text-xs text-slate-400">{{ auth()->user()->role->label() }}</p><div class="mt-4 grid gap-2"><a href="{{ route('home') }}" target="_blank" class="text-sky-300 hover:text-white">Lihat situs ↗</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="text-rose-300 hover:text-white">Keluar</button></form></div></div>
        </aside>
        <main class="min-w-0 p-4 sm:p-7 lg:p-10">
            @if(session('status'))<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800" role="alert"><p class="font-bold">Periksa kembali data berikut:</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</body>
</html>
