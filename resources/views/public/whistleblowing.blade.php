@extends('layouts.public')
@section('title', 'Pengaduan dan Whistleblowing')
@section('meta_description', 'Kanal resmi pengaduan Dukcapil Kabupaten Gorontalo melalui WhatsApp, SI PRIMA, dan SP4N-LAPOR!.')
@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-page py-12 sm:py-16">
        <div class="max-w-4xl">
            <p class="eyebrow">Pengaduan masyarakat</p>
            <h1 class="page-title mt-3">Sampaikan dan pantau pengaduan melalui kanal resmi</h1>
            <p class="mt-5 max-w-3xl text-base leading-7 sm:text-lg sm:leading-8">Pilih kanal sesuai kebutuhan Anda. Gunakan WhatsApp pengaduan untuk menyampaikan keluhan, SI PRIMA untuk whistleblowing, dan SP4N-LAPOR! untuk melaporkan instansi.</p>
        </div>
    </div>
</section>

<section class="container-page py-10 sm:py-14">
    <figure class="card overflow-hidden" aria-labelledby="alur-pengaduan-title">
        <figcaption class="border-b border-slate-200 p-5 sm:p-7">
            <p class="eyebrow">Panduan lengkap</p>
            <h2 id="alur-pengaduan-title" class="mt-2 text-xl font-bold text-ink sm:text-2xl">Alur pengaduan whistleblowing SI PRIMA</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 sm:text-base sm:leading-7">Ikuti panduan mulai dari membuka SI PRIMA, mengirim aduan rahasia, memantau status, hingga publikasi hasil secara anonim.</p>
        </figcaption>
        <img src="{{ asset('images/alur-pengaduan-whistleblowing.png') }}" alt="Diagram sebelas langkah alur pengaduan whistleblowing SI PRIMA, mulai dari membuka aplikasi hingga publikasi hasil secara anonim" width="1000" height="666" class="h-auto w-full bg-white" loading="lazy" decoding="async">
        <div class="grid gap-4 border-t border-slate-200 p-5 sm:flex sm:items-center sm:justify-between sm:p-7">
            <p class="min-w-0 text-sm leading-6">Buka gambar ukuran penuh jika tulisan pada panduan belum terbaca di perangkat Anda.</p>
            <a href="{{ asset('images/alur-pengaduan-whistleblowing.png') }}" target="_blank" rel="noopener noreferrer" class="btn-secondary shrink-0">Buka gambar ukuran penuh ↗</a>
        </div>
    </figure>

    <section class="mt-8 sm:mt-10" aria-labelledby="kontak-whatsapp-title">
        <div class="max-w-3xl">
            <p class="eyebrow">Kontak WhatsApp</p>
            <h2 id="kontak-whatsapp-title" class="section-title mt-2">Hubungi nomor sesuai kebutuhan</h2>
            <p class="mt-3 text-sm leading-6 sm:text-base sm:leading-7">Gunakan nomor pengaduan hanya untuk menyampaikan keluhan. Validasi NIK dan konsultasi administrasi kependudukan dilayani melalui nomor konsultasi.</p>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <article class="card flex flex-col border-t-4 border-t-emerald-500 p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-[100px] w-[100px] shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 ring-inset" aria-hidden="true">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 11.5a8 8 0 0 1-11.82 7.03L4 19.75l1.2-4.08A8 8 0 1 1 20 11.5Z" />
                            <path d="M8.25 8.25c.3 3.85 3.65 7.2 7.5 7.5l1-1.7-2.45-1.15-.85 1c-1.45-.63-2.72-1.9-3.35-3.35l1-.85-1.15-2.45z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[.16em] text-emerald-700 uppercase">Khusus pengaduan</p>
                        <h3 class="mt-2 text-xl font-bold text-ink">WhatsApp pengaduan Dukcapil</h3>
                    </div>
                </div>
                <p class="mt-3 flex-1 text-sm leading-6">Sampaikan keluhan atau kendala terkait pelayanan Dukcapil melalui nomor pengaduan resmi.</p>
                <p class="mt-5 text-2xl font-black text-ink">{{ config('dukcapil.complaints.whatsapp_number') }}</p>
                <a href="{{ config('dukcapil.complaints.whatsapp_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5" aria-label="Hubungi WhatsApp khusus pengaduan Dukcapil di 0811434356">Hubungi nomor pengaduan ↗</a>
            </article>

            <article class="card flex flex-col border-t-4 border-t-ocean p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-[100px] w-[100px] shrink-0 items-center justify-center rounded-2xl bg-sky-soft text-ocean ring-1 ring-sky-200 ring-inset" aria-hidden="true">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 11.5a8 8 0 0 1-11.82 7.03L4 19.75l1.2-4.08A8 8 0 1 1 20 11.5Z" />
                            <path d="M8.25 8.25c.3 3.85 3.65 7.2 7.5 7.5l1-1.7-2.45-1.15-.85 1c-1.45-.63-2.72-1.9-3.35-3.35l1-.85-1.15-2.45z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[.16em] text-ocean uppercase">Validasi dan konsultasi</p>
                        <h3 class="mt-2 text-xl font-bold text-ink">Validasi NIK dan konsultasi adminduk</h3>
                    </div>
                </div>
                <p class="mt-3 flex-1 text-sm leading-6">Hubungi petugas untuk memperoleh arahan validasi NIK atau berkonsultasi mengenai administrasi kependudukan.</p>
                <p class="mt-5 text-2xl font-black text-ink">{{ config('dukcapil.complaints.identity_consultation_number') }}</p>
                <a href="{{ config('dukcapil.complaints.identity_consultation_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5" aria-label="Hubungi WhatsApp validasi NIK dan konsultasi administrasi kependudukan di 081243425557">Hubungi nomor konsultasi ↗</a>
            </article>
        </div>
    </section>

    <section class="mt-8 sm:mt-10" aria-labelledby="kanal-pengaduan-title">
        <div class="max-w-3xl">
            <p class="eyebrow">Kanal pengaduan resmi</p>
            <h2 id="kanal-pengaduan-title" class="section-title mt-2">Buat dan pantau laporan secara daring</h2>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article class="card flex flex-col p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-[100px] w-[100px] shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 ring-1 ring-amber-200 ring-inset" aria-hidden="true">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3.75h8.25L18 7.5v12.75H6z" />
                            <path d="M14.25 3.75V7.5H18M9 11.25h6M9 15h4.5" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[.16em] text-amber-700 uppercase">Tata cara pengaduan</p>
                        <h3 class="mt-2 text-xl font-bold text-ink">Buat aduan di SI PRIMA</h3>
                    </div>
                </div>
                <p class="mt-3 flex-1 text-sm leading-6">Masuk atau daftar akun SI PRIMA, buka menu Whistleblowing, kemudian lengkapi dan kirim aduan.</p>
                <a href="{{ config('dukcapil.complaints.siprima_whistleblowing_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Buka SI PRIMA ↗</a>
            </article>

            <article class="card flex flex-col p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-[100px] w-[100px] shrink-0 items-center justify-center rounded-2xl bg-sky-soft text-ocean ring-1 ring-sky-200 ring-inset" aria-hidden="true">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5.5 3.75h8.25l3.75 3.75v5.25M13.75 3.75V7.5h3.75M8.5 11h5" />
                            <circle cx="15.25" cy="16.25" r="3.25" />
                            <path d="m17.75 18.75 2 2" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[.16em] text-ocean uppercase">Pantau publikasi</p>
                        <h3 class="mt-2 text-xl font-bold text-ink">Lihat hasil yang dipublikasikan</h3>
                    </div>
                </div>
                <p class="mt-3 flex-1 text-sm leading-6">Pantau ringkasan anonim pengaduan yang telah selesai dan disetujui untuk dipublikasikan di SI PRIMA.</p>
                <a href="{{ config('dukcapil.complaints.siprima_whistleblowing_publications_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Pantau di SI PRIMA ↗</a>
            </article>

            <article class="card flex flex-col p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-[100px] w-[100px] shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700 ring-1 ring-red-200 ring-inset" aria-hidden="true">
                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 10v4h3l9 4V6l-9 4zM7 14l1 5h3l-1-4" />
                            <path d="M19 9.25a4.5 4.5 0 0 1 0 5.5" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[.16em] text-red-700 uppercase">SP4N-LAPOR!</p>
                        <h3 class="mt-2 text-xl font-bold text-ink">Laporkan instansi</h3>
                    </div>
                </div>
                <p class="mt-3 flex-1 text-sm leading-6">Sampaikan aspirasi atau pengaduan pelayanan publik kepada instansi pemerintah melalui kanal nasional.</p>
                <a href="{{ config('dukcapil.complaints.sp4n_lapor_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Buka SP4N-LAPOR! ↗</a>
            </article>
        </div>
    </section>

    <aside class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-950 sm:mt-10 sm:p-6" aria-labelledby="keamanan-pengaduan-title">
        <h2 id="keamanan-pengaduan-title" class="text-lg font-bold">Jaga keamanan informasi Anda</h2>
        <p class="mt-2 max-w-4xl text-sm leading-6 sm:text-base sm:leading-7">Gunakan kanal resmi di atas. Jangan pernah memberikan PIN, kata sandi, atau kode OTP kepada siapa pun. Ikuti arahan petugas sebelum mengirimkan data atau dokumen kependudukan melalui WhatsApp.</p>
    </aside>
</section>
@endsection
