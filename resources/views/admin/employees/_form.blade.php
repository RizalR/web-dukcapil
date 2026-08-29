@php($editing = isset($employee))
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    <div class="card grid gap-5 p-6">
        <div>
            <label class="form-label" for="name">Nama lengkap</label>
            <input class="form-input" id="name" name="name" value="{{ old('name', $employee->name ?? '') }}" required maxlength="160" autocomplete="off">
        </div>
        <div>
            <label class="form-label" for="position">Jabatan</label>
            <input class="form-input" id="position" name="position" value="{{ old('position', $employee->position ?? '') }}" required maxlength="160">
        </div>
        <div>
            <label class="form-label" for="unit">Unit kerja (opsional)</label>
            <input class="form-input" id="unit" name="unit" value="{{ old('unit', $employee->unit ?? '') }}" maxlength="180">
        </div>
        <div>
            <label class="form-label" for="photo">Foto pegawai {{ $editing ? 'baru (opsional)' : '' }}</label>
            <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)>
            <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, atau WebP dengan ukuran maksimal 1 MB.</p>
        </div>
    </div>

    <aside class="grid content-start gap-5">
        @if ($editing && $employee->photoUrl())
            <div class="card overflow-hidden p-4">
                <p class="form-label mb-3">Foto saat ini</p>
                <img class="aspect-[4/5] w-full rounded-xl bg-slate-100 object-contain" src="{{ $employee->photoUrl() }}" alt="Foto {{ $employee->name }}">
            </div>
        @endif
        <div class="card p-5">
            <label class="form-label" for="sort_order">Urutan tampil</label>
            <input class="form-input" id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $employee->sort_order ?? 0) }}" required>
            <input name="is_published" type="hidden" value="0">
            <label class="mt-5 flex items-center gap-3 text-sm font-semibold"><input type="checkbox" name="is_published" value="1" class="size-4" @checked(old('is_published', $employee->is_published ?? true))> Tampilkan di situs publik</label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">Simpan</button>
            <a href="{{ route('admin.employees.index') }}" class="btn-secondary">Batal</a>
        </div>
    </aside>
</div>
