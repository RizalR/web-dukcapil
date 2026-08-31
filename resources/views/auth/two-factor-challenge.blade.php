<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Verifikasi Dua Faktor — Dukcapil Kabupaten Gorontalo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-ink p-4">
    <main class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl sm:p-9">
        <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-xl bg-ocean font-black text-white">DG</span><span class="font-bold text-ink">Dukcapil Kabupaten Gorontalo</span></a>
        <p class="eyebrow mt-9">Langkah keamanan</p>
        <h1 class="mt-2 text-3xl font-black text-ink">Verifikasi dua faktor</h1>
        <p class="mt-2 text-sm leading-6">Masukkan kode 6 digit dari Google Authenticator atau aplikasi TOTP lainnya.</p>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('two-factor.challenge.store') }}" method="post" class="mt-7 grid gap-5">
            @csrf
            <div>
                <label class="form-label" for="code">Kode authenticator</label>
                <input class="form-input font-mono tracking-[0.3em]" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" autofocus>
            </div>
            <button type="submit" class="btn-primary w-full">Verifikasi dan masuk</button>
        </form>

        <details class="mt-6 rounded-xl border border-slate-200 p-4">
            <summary class="cursor-pointer text-sm font-bold text-ocean">Gunakan recovery code</summary>
            <form action="{{ route('two-factor.challenge.store') }}" method="post" class="mt-4 grid gap-4">
                @csrf
                <div>
                    <label class="form-label" for="recovery_code">Recovery code</label>
                    <input class="form-input font-mono" id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code" maxlength="100">
                </div>
                <button type="submit" class="btn-secondary w-full">Masuk dengan recovery code</button>
            </form>
        </details>

        <a href="{{ route('login') }}" class="mt-6 block text-center text-sm font-bold text-ocean">← Kembali ke halaman masuk</a>
    </main>
</body>
</html>
