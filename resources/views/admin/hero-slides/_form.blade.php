@php($editing = isset($heroSlide))
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    <div class="card p-6">
        <div>
            <label class="form-label" for="label">Nama slide</label>
            <input class="form-input" id="label" name="label" value="{{ old('label', $heroSlide->label ?? '') }}" required maxlength="160">
            <p class="mt-1 text-xs text-slate-500">Nama ini digunakan untuk mengenali gambar di panel admin.</p>
        </div>
        <div class="mt-5">
            <label class="form-label" for="image">Gambar {{ $editing ? 'baru (opsional)' : '' }}</label>
            <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)>
            <p class="mt-1 text-xs text-slate-500">Maksimal 1 MB dan harus berukuran tepat 1000 × 1208 piksel.</p>
        </div>
        @if ($editing)
            <img class="mt-4 aspect-video w-full rounded-2xl bg-ink object-contain" src="{{ $heroSlide->imageUrl() }}" alt="Pratinjau {{ $heroSlide->label }}">
        @endif
        <div class="mt-5">
            <label class="form-label" for="alt_text">Teks alternatif</label>
            <input class="form-input" id="alt_text" name="alt_text" value="{{ old('alt_text', $heroSlide->alt_text ?? '') }}" required maxlength="255">
            <p class="mt-1 text-xs text-slate-500">Jelaskan isi penting gambar untuk pengguna pembaca layar.</p>
        </div>
        <div class="mt-5">
            <label class="form-label" for="caption">Keterangan publik (opsional)</label>
            <textarea class="form-input min-h-24" id="caption" name="caption" maxlength="320">{{ old('caption', $heroSlide->caption ?? '') }}</textarea>
        </div>
    </div>

    <aside class="grid content-start gap-5">
        <div class="card p-5">
            <label class="form-label" for="sort_order">Urutan tampil</label>
            <input class="form-input" id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $heroSlide->sort_order ?? 0) }}" required>
            <label class="mt-5 flex items-center gap-3 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" class="size-4" @checked(old('is_active', $heroSlide->is_active ?? true))> Aktifkan slide</label>
        </div>
        <div class="card p-5">
            <p class="form-label">Jadwal tayang (opsional)</p>
            <label class="mt-4 block text-sm font-semibold text-ink" for="starts_at">Mulai</label>
            <input class="form-input mt-2" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', isset($heroSlide) ? $heroSlide->starts_at?->format('Y-m-d\TH:i') : '') }}">
            <label class="mt-4 block text-sm font-semibold text-ink" for="ends_at">Selesai</label>
            <input class="form-input mt-2" id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', isset($heroSlide) ? $heroSlide->ends_at?->format('Y-m-d\TH:i') : '') }}">
        </div>
        <div class="flex gap-3"><button type="submit" class="btn-primary flex-1">Simpan</button><a href="{{ route('admin.homepage.edit') }}" class="btn-secondary">Batal</a></div>
    </aside>
</div>
