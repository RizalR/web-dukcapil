@extends('layouts.admin')

@section('title', 'Impor Statistik Penduduk')

@section('content')
<div class="max-w-6xl">
    <p class="eyebrow">Agregat</p>
    <h1 class="page-title mt-2">Impor data dari Excel</h1>
    <p class="mt-4 max-w-3xl text-slate-600">Pilih satu atau beberapa workbook agregat. Setiap file akan dibaca sesuai kategori yang dipilih, diperiksa konsistensinya, lalu ditampilkan sebagai pratinjau sebelum disimpan.</p>

    <form action="{{ route('admin.population-statistic-imports.preview') }}" method="post" enctype="multipart/form-data" class="mt-8 grid gap-8">
        @csrf

        <section class="card grid gap-6 p-6 sm:grid-cols-2" aria-labelledby="import-metadata-heading">
            <div class="sm:col-span-2">
                <h2 id="import-metadata-heading" class="text-lg font-bold text-ink">Informasi pembaruan</h2>
                <p class="mt-1 text-sm text-slate-500">Periode yang sama akan memperbarui data yang sudah ada. Periode baru akan disimpan sebagai riwayat baru.</p>
            </div>
            <div>
                <label class="form-label" for="period">Periode</label>
                <input class="form-input" id="period" name="period" value="{{ old('period', 'Semester I 2026') }}" maxlength="80" required>
                @error('period')<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="source">Sumber data</label>
                <input class="form-input" id="source" name="source" value="{{ old('source', 'Dinas Dukcapil Kabupaten Gorontalo') }}" maxlength="180" required>
                @error('source')<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="form-label" for="source_url">Tautan sumber <span class="font-normal text-slate-500">(opsional)</span></label>
                <input class="form-input" id="source_url" name="source_url" type="url" value="{{ old('source_url', 'https://dukcapil.gorontalokab.go.id/') }}" maxlength="2048">
                @error('source_url')<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
            </div>
        </section>

        <section aria-labelledby="aggregate-files-heading">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <h2 id="aggregate-files-heading" class="text-xl font-bold text-ink">File agregat</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih minimal satu file. Kategori yang tidak dipilih tidak akan diubah.</p>
                </div>
                <p class="rounded-full bg-sky-100 px-3 py-1.5 text-xs font-bold text-ocean">.xlsx · maksimal 2 MB per file</p>
            </div>

            @error('workbooks')<p class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror

            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                @foreach($uploadProfiles as $profile)
                    <article class="card p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-ink">{{ $profile['category'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $profile['description'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Sheet {{ $profile['sheet'] }}</span>
                        </div>
                        <label class="form-label mt-5" for="workbook-{{ $profile['slug'] }}">Pilih file {{ $profile['category'] }}</label>
                        <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="workbook-{{ $profile['slug'] }}" name="workbooks[{{ $profile['slug'] }}]" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <p class="mt-2 break-all text-xs text-slate-500">Contoh format: {{ $profile['default_filename'] }}</p>
                        @error("workbooks.{$profile['slug']}")<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                    </article>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            <button class="btn-primary" type="submit">Periksa dan pratinjau</button>
            <a class="btn-secondary" href="{{ route('admin.population-statistics.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
