@extends('layouts.admin')

@section('title', 'Keamanan Akun')

@section('content')
    <p class="eyebrow">Keamanan Super Admin</p>
    <h1 class="page-title mt-2">Autentikasi dua faktor</h1>
    <p class="mt-3 max-w-3xl">Gunakan Google Authenticator atau aplikasi TOTP yang kompatibel. Kode hanya tersimpan terenkripsi di server dan tidak pernah dicatat dalam audit log.</p>

    @if($user->hasConfirmedTwoFactorAuthentication())
        <section class="card mt-8 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Aktif</p>
                    <h2 class="mt-2 text-xl font-bold text-ink">MFA melindungi akun ini</h2>
                    <p class="mt-2 text-sm leading-6">Setiap login Super Admin memerlukan kata sandi dan kode sekali pakai.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-800">TOTP aktif</span>
            </div>
        </section>

        @if($showRecoveryCodes)
            <section class="mt-6 rounded-2xl border-2 border-amber-300 bg-amber-50 p-6" aria-labelledby="recovery-code-title">
                <h2 id="recovery-code-title" class="text-xl font-bold text-amber-950">Simpan recovery code sekarang</h2>
                <p class="mt-2 text-sm leading-6 text-amber-900">Setiap kode hanya dapat dipakai sekali. Simpan secara offline di tempat yang dikendalikan instansi. Setelah halaman ditutup, kode tidak ditampilkan lagi.</p>
                <ul class="mt-5 grid gap-2 font-mono text-sm sm:grid-cols-2">
                    @foreach($user->recoveryCodes() as $recoveryCode)
                        <li class="rounded-lg border border-amber-200 bg-white px-4 py-3">{{ $recoveryCode }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="card mt-6 p-6">
            <h2 class="text-xl font-bold text-ink">Buat recovery code baru</h2>
            <p class="mt-2 text-sm leading-6">Tindakan ini langsung membatalkan seluruh recovery code lama.</p>
            <form action="{{ route('admin.two-factor.recovery-codes.store') }}" method="post" class="mt-5 grid max-w-xl gap-5">
                @csrf
                <div>
                    <label class="form-label" for="password">Kata sandi saat ini</label>
                    <input class="form-input" id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <div>
                    <label class="form-label" for="code">Kode authenticator baru</label>
                    <input class="form-input font-mono tracking-[0.3em]" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code">
                    <p class="mt-2 text-xs text-slate-500">Gunakan kode yang belum dipakai pada langkah sebelumnya.</p>
                </div>
                <button type="submit" class="btn-secondary justify-self-start">Buat recovery code baru</button>
            </form>
        </section>
    @elseif($user->two_factor_secret !== null)
        <section class="card mt-8 p-6">
            <h2 class="text-xl font-bold text-ink">1. Pindai QR code</h2>
            <p class="mt-2 text-sm leading-6">Buka Google Authenticator, pilih tambah akun, lalu pindai QR code berikut.</p>
            <div class="mt-5 inline-block rounded-2xl border border-slate-200 bg-white p-4" aria-label="QR code aktivasi autentikasi dua faktor">{!! $user->twoFactorQrCodeSvg() !!}</div>

            <h2 class="mt-8 text-xl font-bold text-ink">2. Konfirmasi kode</h2>
            <form action="{{ route('admin.two-factor.update') }}" method="post" class="mt-4 grid max-w-md gap-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label" for="code">Kode 6 digit</label>
                    <input class="form-input font-mono tracking-[0.3em]" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code">
                </div>
                <button type="submit" class="btn-primary justify-self-start">Konfirmasi dan aktifkan</button>
            </form>
        </section>
    @else
        <section class="card mt-8 p-6">
            <h2 class="text-xl font-bold text-ink">Mulai aktivasi MFA</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6">Konfirmasikan kata sandi. Setelah itu aplikasi membuat QR code unik untuk akun ini.</p>
            <form action="{{ route('admin.two-factor.store') }}" method="post" class="mt-5 grid max-w-md gap-5">
                @csrf
                <div>
                    <label class="form-label" for="password">Kata sandi saat ini</label>
                    <input class="form-input" id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-primary justify-self-start">Buat QR code MFA</button>
            </form>
        </section>
    @endif
@endsection
