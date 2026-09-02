@extends('layouts.public')
@section('title', 'Aduan Masyarakat')
@section('meta_description', 'Kanal resmi pengaduan masyarakat untuk layanan Dukcapil Kabupaten Gorontalo melalui WhatsApp, SP4N-LAPOR!, dan Whistleblowing SI PRIMA.')
@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-page py-14 sm:py-18">
        <p class="eyebrow">Pengaduan masyarakat</p>
        <h1 class="page-title mt-3">Sampaikan aduan melalui kanal resmi</h1>
        <p class="mt-4 max-w-3xl text-lg leading-8">Pilih kanal yang paling sesuai dengan jenis aduan Anda. Setiap aduan akan diteruskan melalui layanan resmi agar dapat ditangani oleh petugas yang berwenang.</p>
    </div>
</section>

<section class="container-page py-12">
    <div class="grid gap-6 lg:grid-cols-3">
        <article class="card flex flex-col p-6">
            <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-black text-emerald-700" aria-hidden="true">WA</span>
            <p class="mt-5 text-sm font-bold uppercase tracking-[.16em] text-emerald-700">WhatsApp Dukcapil</p>
            <h2 class="mt-2 text-xl font-bold text-ink">Aduan layanan Dukcapil</h2>
            <p class="mt-3 flex-1 leading-7">Hubungi petugas untuk menyampaikan kendala atau aduan terkait pelayanan administrasi kependudukan.</p>
            <p class="mt-5 text-lg font-bold text-ink">{{ config('dukcapil.complaints.whatsapp_number') }}</p>
            <a href="{{ config('dukcapil.complaints.whatsapp_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Hubungi melalui WhatsApp ↗</a>
        </article>

        <article class="card flex flex-col p-6">
            <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-sky-soft text-xl font-black text-ocean" aria-hidden="true">!</span>
            <p class="mt-5 text-sm font-bold uppercase tracking-[.16em] text-ocean">SP4N-LAPOR!</p>
            <h2 class="mt-2 text-xl font-bold text-ink">Pengaduan pelayanan publik</h2>
            <p class="mt-3 flex-1 leading-7">Gunakan Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional untuk menyampaikan aspirasi atau pengaduan kepada instansi pemerintah.</p>
            <a href="{{ config('dukcapil.complaints.sp4n_lapor_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Buka SP4N-LAPOR! ↗</a>
        </article>

        <article class="card flex flex-col p-6">
            <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-amber-100 text-xl font-black text-amber-700" aria-hidden="true">WBS</span>
            <p class="mt-5 text-sm font-bold uppercase tracking-[.16em] text-amber-700">SI PRIMA</p>
            <h2 class="mt-2 text-xl font-bold text-ink">Whistleblowing</h2>
            <p class="mt-3 flex-1 leading-7">Sampaikan dugaan pelanggaran, penyalahgunaan wewenang, atau perilaku tidak patut melalui fitur whistleblowing SI PRIMA.</p>
            <a href="{{ config('dukcapil.complaints.siprima_whistleblowing_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5">Buka Whistleblowing SI PRIMA ↗</a>
        </article>
    </div>

    <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-950">
        <h2 class="text-lg font-bold">Siapkan informasi aduan</h2>
        <p class="mt-2 leading-7">Jelaskan jenis layanan, waktu kejadian, lokasi, kronologi singkat, dan bukti pendukung bila tersedia. Jangan pernah memberikan PIN, kata sandi, atau kode OTP kepada siapa pun.</p>
    </div>
</section>
@endsection
