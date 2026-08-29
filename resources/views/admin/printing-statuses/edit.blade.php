@extends('layouts.admin')

@section('title', 'Perbarui status pencetakan')

@section('content')
<p class="eyebrow">{{ $printingStatus->location }}</p>
<h1 class="page-title mt-2">{{ $printingStatus->document->label() }}</h1>
<p class="mt-3 max-w-2xl">Perubahan tersimpan akan langsung tampil di beranda dan halaman Informasi.</p>

<form action="{{ route('admin.printing-statuses.update', $printingStatus) }}" method="post" class="card mt-8 max-w-2xl space-y-5 p-6">
    @csrf
    @method('PUT')

    <div>
        <label class="form-label" for="is_available">Status pencetakan</label>
        <select class="form-input" id="is_available" name="is_available" required>
            <option value="1" @selected(old('is_available', $printingStatus->is_available ? '1' : '0') === '1')>Ada</option>
            <option value="0" @selected(old('is_available', $printingStatus->is_available ? '1' : '0') === '0')>Tidak ada</option>
        </select>
    </div>

    <div>
        <label class="form-label" for="note">Catatan publik</label>
        <textarea class="form-input min-h-28" id="note" name="note" maxlength="500">{{ old('note', $printingStatus->note) }}</textarea>
        <p class="mt-2 text-xs text-slate-500">Jelaskan secara singkat jika pencetakan belum tersedia. Jangan menuliskan jumlah stok internal.</p>
    </div>

    <div>
        <label class="form-label" for="source">Sumber pembaruan</label>
        <input class="form-input" id="source" name="source" value="{{ old('source', $printingStatus->source) }}" required maxlength="180">
    </div>

    <div>
        <label class="form-label" for="checked_at">Waktu pemeriksaan</label>
        <input class="form-input" id="checked_at" name="checked_at" type="datetime-local" value="{{ old('checked_at', $printingStatus->checked_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="btn-primary">Simpan</button>
        <a href="{{ route('admin.printing-statuses.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
@endsection
