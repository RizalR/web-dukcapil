@extends('layouts.admin')

@section('title', 'Impor Statistik Tematik')

@section('content')
<div class="max-w-6xl">
    <p class="eyebrow">Data tematik</p>
    <h1 class="page-title mt-2">Unggah pembaruan workbook</h1>
    <p class="mt-4 max-w-3xl text-slate-600">Pilih satu atau beberapa file. File yang tidak dipilih tidak akan diubah. Nilai kecil tetap disimpan dan ditampilkan tanpa penyamaran.</p>

    <form action="{{ route('admin.thematic-statistic-imports.preview') }}" method="post" enctype="multipart/form-data" class="mt-8 grid gap-8">
        @csrf

        <section class="card grid gap-6 p-6 sm:grid-cols-2" aria-labelledby="thematic-import-metadata">
            <div class="sm:col-span-2">
                <h2 id="thematic-import-metadata" class="text-lg font-bold text-ink">Informasi pembaruan</h2>
                <p class="mt-1 text-sm text-slate-500">Versi baru dapat disimpan sebagai draf atau langsung diterbitkan setelah pratinjau.</p>
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

        @error('workbooks')<p class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror

        @foreach($uploadProfiles as $group => $profiles)
            <section aria-labelledby="group-{{ str($group)->slug() }}">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <h2 id="group-{{ str($group)->slug() }}" class="text-xl font-bold text-ink">{{ $group }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Unggah hanya dataset yang ingin diperbarui pada periode ini.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    @foreach($profiles as $profile)
                        <article class="card p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-ink">{{ $profile['title'] }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $profile['description'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $profile['extension'] }}</span>
                            </div>
                            <label class="form-label mt-5" for="workbook-{{ $profile['slug'] }}">Pilih file {{ $profile['title'] }}</label>
                            <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="workbook-{{ $profile['slug'] }}" name="workbooks[{{ $profile['slug'] }}]" type="file" accept="{{ $profile['extension'] }}">
                            <p class="mt-2 break-all text-xs text-slate-500">Format sumber: {{ $profile['default_filename'] }}</p>
                            @error("workbooks.{$profile['slug']}")<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex flex-wrap gap-3">
            <button class="btn-primary" type="submit">Periksa dan pratinjau</button>
            <a class="btn-secondary" href="{{ route('admin.thematic-statistic-datasets.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
