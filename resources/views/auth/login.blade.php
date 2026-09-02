<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-logo-dukcapil.png') }}">
    <title>Masuk Pengelola — Dukcapil Kabupaten Gorontalo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-ink p-4">
    <main class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl sm:p-9">
        <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-xl bg-ocean font-black text-white">DG</span><span class="font-bold text-ink">Dukcapil Kabupaten Gorontalo</span></a>
        <p class="eyebrow mt-9">Akses terbatas</p><h1 class="mt-2 text-3xl font-black text-ink">Masuk pengelola</h1><p class="mt-2 text-sm">Gunakan akun super admin atau editor berita. Super Admin wajib melanjutkan dengan kode autentikasi dua faktor.</p>
        @if($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{ $errors->first() }}</div>@endif
        <form action="{{ route('login') }}" method="post" class="mt-7 space-y-5">@csrf
            <div><label class="form-label" for="email">Email</label><input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" maxlength="255"></div>
            <div><label class="form-label" for="password">Kata sandi</label><input class="form-input" id="password" name="password" type="password" required autocomplete="current-password"></div>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-ocean"> Ingat saya pada perangkat ini</label>
            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>
        <a href="{{ route('home') }}" class="mt-6 block text-center text-sm font-bold text-ocean">← Kembali ke situs</a>
    </main>
</body>
</html>
