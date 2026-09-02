@extends('layouts.admin')

@section('title', 'Migrasi Basis Data')

@section('content')
    <div>
        <p class="eyebrow">Pemeliharaan sistem</p>
        <h1 class="page-title mt-2">Migrasi basis data</h1>
        <p class="mt-3 max-w-3xl">Jalankan seluruh migrasi Laravel yang masih tertunda tanpa Terminal cPanel.</p>
    </div>

    <section class="card mt-8 max-w-3xl p-6 lg:p-8" aria-labelledby="migration-heading">
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950">
            <h2 id="migration-heading" class="text-lg font-black">Tindakan berisiko tinggi</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6">
                <li>Pastikan database sudah dicadangkan melalui phpMyAdmin.</li>
                <li>Jangan tutup halaman sampai proses selesai.</li>
                <li>Setelah berhasil, ubah <code class="rounded bg-amber-100 px-1.5 py-0.5 font-mono">DUKCAPIL_WEB_MIGRATIONS_ENABLED=false</code> di <code class="rounded bg-amber-100 px-1.5 py-0.5 font-mono">.env</code>.</li>
            </ul>
        </div>

        <form class="mt-6 grid gap-5" action="{{ route('admin.database-migrations.store') }}" method="post">
            @csrf
            <div>
                <label class="form-label" for="confirmation">Ketik <strong>JALANKAN MIGRASI</strong> untuk mengonfirmasi</label>
                <input class="form-input mt-2" id="confirmation" name="confirmation" type="text" autocomplete="off" required>
            </div>
            <button class="btn-primary w-fit" type="submit">Jalankan migrasi</button>
        </form>
    </section>
@endsection
