@extends('layouts.public')
@section('title', 'Informasi Pelayanan')
@section('meta_description', 'Informasi pelayanan administrasi kependudukan dan akses menuju layanan resmi SI PRIMA Kabupaten Gorontalo.')
@section('content')
<section class="container-page py-10 sm:py-14">
    <div class="mx-auto max-w-5xl">
        <div class="max-w-3xl">
            <p class="eyebrow">Informasi pelayanan</p>
            <h1 class="page-title mt-3">Pelayanan dokumen berada di SI PRIMA</h1>
            <p class="mt-5 text-base leading-7 sm:text-lg sm:leading-8">Portal ini tidak menerima pengajuan dokumen. Untuk Kartu Keluarga, akta pencatatan sipil, dan layanan administrasi kependudukan lainnya, gunakan aplikasi layanan resmi.</p>
        </div>

        <article class="card mt-8 sm:mt-10 flex flex-col border-t-4 border-t-ocean p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <span class="flex size-16 sm:size-20 shrink-0 items-center justify-center rounded-2xl bg-sky-soft text-ocean ring-1 ring-sky-200 ring-inset" aria-hidden="true">
                    <svg class="size-9 sm:size-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 11.5a8 8 0 0 1-11.82 7.03L4 19.75l1.2-4.08A8 8 0 1 1 20 11.5Z" />
                        <path d="M8.25 8.25c.3 3.85 3.65 7.2 7.5 7.5l1-1.7-2.45-1.15-.85 1c-1.45-.63-2.72-1.9-3.35-3.35l1-.85-1.15-2.45z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold tracking-[.16em] text-ocean uppercase">Validasi dan konsultasi</p>
                    <h2 class="mt-2 text-xl font-bold text-ink">Validasi NIK dan konsultasi adminduk</h2>
                </div>
            </div>
            <p class="mt-3 flex-1 text-sm leading-6">Hubungi petugas untuk memperoleh arahan validasi NIK atau berkonsultasi mengenai administrasi kependudukan.</p>
            <p class="mt-5 text-2xl font-black text-ink">{{ config('dukcapil.complaints.identity_consultation_number') }}</p>
            <a href="{{ config('dukcapil.complaints.identity_consultation_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5 w-full sm:w-auto sm:self-start" aria-label="Hubungi WhatsApp validasi NIK dan konsultasi administrasi kependudukan di 081243425557">Hubungi nomor konsultasi ↗</a>
        </article>

        <section class="mt-8 rounded-3xl bg-sky-soft p-5 sm:mt-10 sm:p-7 lg:p-8" aria-labelledby="printing-status-title">
            <p class="eyebrow">Informasi pelayanan terkini</p>
            <h2 id="printing-status-title" class="mt-2 text-2xl font-bold tracking-tight text-ink sm:text-3xl">Status pencetakan KTP-el dan KIA</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Status menunjukkan apakah pencetakan dokumen sedang tersedia pada waktu pemeriksaan terakhir.</p>
            <div class="mt-6 grid gap-4 sm:mt-8 md:grid-cols-2">
                @forelse($printingStatuses as $printingStatus)
                    <x-printing-status-card :printing-status="$printingStatus" />
                @empty
                    <div class="card col-span-full p-6 text-center sm:p-8">Status pencetakan belum tersedia.</div>
                @endforelse
            </div>

            <section class="mt-8 border-t border-sky-200 pt-8 sm:mt-10 sm:pt-10" aria-labelledby="supply-status-title">
                <p class="eyebrow">Ketersediaan bahan</p>
                <h2 id="supply-status-title" class="mt-2 text-2xl font-bold tracking-tight text-ink sm:text-3xl">Status bahan cetak</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Ketersediaan bahan ditampilkan secara tekstual per lokasi berdasarkan pemeriksaan terakhir petugas.</p>
                <div class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2">
                    @forelse($supplyStatuses as $supplyStatus)
                        <x-supply-status-card :supply-status="$supplyStatus" />
                    @empty
                        <div class="card col-span-full p-6 text-center sm:p-8">Status bahan cetak belum tersedia.</div>
                    @endforelse
                </div>
            </section>
        </section>

        <figure class="card mt-8 overflow-hidden sm:mt-10" aria-labelledby="pengumuman-layanan-title">
            <figcaption class="border-b border-slate-200 p-5 sm:p-7">
                <p class="eyebrow">Pengumuman penting</p>
                <h2 id="pengumuman-layanan-title" class="mt-2 text-xl font-bold text-ink sm:text-2xl">Pengumuman perubahan layanan</h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 sm:text-base sm:leading-7">Mulai 1 Maret 2026, pelayanan administrasi kependudukan dilakukan secara daring melalui SI PRIMA. Pelayanan langsung tetap tersedia untuk perekaman dan pencetakan KTP-el serta KIA.</p>
            </figcaption>
            <img src="{{ asset('images/pengumuman-layanan-dukcapil.jpeg') }}" alt="Poster pengumuman perubahan layanan Dukcapil Kabupaten Gorontalo mulai 1 Maret 2026" width="1000" height="1000" class="h-auto w-full bg-slate-100" loading="lazy" decoding="async">
        </figure>

        <div class="card mt-8 border-l-4 border-l-sun p-5 sm:mt-10 sm:p-7">
            <h2 class="text-xl font-bold text-ink">Sebelum melanjutkan</h2>
            <ul class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <li>✓ Siapkan dokumen persyaratan yang terbaca jelas.</li>
                <li>✓ Pastikan nomor telepon dan email aktif.</li>
                <li>✓ Periksa kembali data sebelum dikirim.</li>
                <li>✓ Hubungi costumer service jika ada kendala.</li>
            </ul>
            <a href="{{ config('dukcapil.service_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-7 w-full sm:w-auto">Buka layanan SI PRIMA ↗</a>
        </div>

        <figure class="card mt-8 overflow-hidden sm:mt-10" aria-labelledby="alur-siprima-title">
            <figcaption class="border-b border-slate-200 p-5 sm:p-7">
                <p class="eyebrow">Panduan SI PRIMA</p>
                <h2 id="alur-siprima-title" class="mt-2 text-xl font-bold text-ink sm:text-2xl">Alur pengajuan melalui SI PRIMA</h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 sm:text-base sm:leading-7">Diagram ini menjelaskan proses mulai dari pendaftaran akun, pengajuan permohonan, pemeriksaan berkas, hingga dokumen diterima.</p>
            </figcaption>
            <img src="{{ asset('images/alur-pengajuan-siprima.png') }}" alt="Diagram alur pengajuan dan penerimaan dokumen melalui SI PRIMA" width="1000" height="562" class="h-auto w-full bg-white" loading="lazy" decoding="async">
            <div class="grid gap-4 border-t border-slate-200 p-5 sm:flex sm:items-center sm:justify-between sm:p-7">
                <p class="min-w-0 text-sm leading-6">Buka gambar ukuran penuh jika tulisan pada diagram belum terbaca di perangkat Anda.</p>
                <a href="{{ asset('images/alur-pengajuan-siprima.png') }}" target="_blank" rel="noopener noreferrer" class="btn-secondary shrink-0">Buka gambar ukuran penuh ↗</a>
            </div>
        </figure>

        <div class="mt-8 grid gap-5 sm:mt-10 md:grid-cols-2 lg:grid-cols-3">
            <article class="card p-5 sm:p-6">
                <p class="eyebrow">Jam pelayanan</p>
                <p class="mt-3 text-xl font-bold text-ink">{{ config('dukcapil.office.hours') }}</p>
                <p class="mt-2 text-sm">Jam dapat berubah pada hari libur nasional atau kebijakan khusus.</p>
            </article>
            <article class="card p-5 sm:p-6">
                <p class="eyebrow">Layanan daring</p>
                <p class="mt-3 text-xl font-bold text-ink">Jam Pelayanan SI PRIMA</p>
                <p class="mt-2 text-sm font-semibold text-ink">{{ config('dukcapil.service_hours') }}</p>
            </article>
            <article class="card p-5 sm:p-6">
                <p class="eyebrow">Bantuan</p>
                <p class="mt-3 text-xl font-bold text-ink">Hubungi kantor Dukcapil</p>
                <p class="mt-2 text-sm">{{ config('dukcapil.office.phone') }} · {{ config('dukcapil.office.email') }}</p>
            </article>
        </div>
    </div>
</section>
@endsection
