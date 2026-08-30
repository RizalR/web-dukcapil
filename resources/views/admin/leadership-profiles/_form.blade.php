@php($editing = isset($leadershipProfile))
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    <div class="card grid gap-5 p-6">
        <div>
            <label class="form-label" for="role">Jabatan</label>
            <select class="form-input" id="role" name="role" required>
                <option value="">Pilih jabatan</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $leadershipProfile->role->value ?? '') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Setiap jabatan hanya dapat memiliki satu profil.</p>
        </div>
        <div>
            <label class="form-label" for="name">Nama lengkap dan gelar</label>
            <input class="form-input" id="name" name="name" value="{{ old('name', $leadershipProfile->name ?? '') }}" required maxlength="160" autocomplete="off">
        </div>
        <div>
            <label class="form-label" for="photo">Foto resmi {{ $editing ? 'baru (opsional)' : '' }}</label>
            <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)>
            <p class="mt-1 text-xs text-slate-500">Rasio 4:5, format JPG, PNG, atau WebP, maksimal 1 MB.</p>
        </div>
    </div>

    <aside class="grid content-start gap-5">
        @if ($editing)
            <div class="card overflow-hidden p-4">
                <p class="form-label mb-3">Foto saat ini</p>
                <img class="aspect-[4/5] w-full rounded-xl bg-slate-100 object-cover object-top" src="{{ $leadershipProfile->photoUrl() }}" alt="Foto {{ $leadershipProfile->name }}">
            </div>
        @endif
        <div class="card p-5">
            <input name="is_published" type="hidden" value="0">
            <label class="flex items-center gap-3 text-sm font-semibold"><input type="checkbox" name="is_published" value="1" class="size-4" @checked(old('is_published', $leadershipProfile->is_published ?? true))> Tampilkan di situs publik</label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">Simpan</button>
            <a href="{{ route('admin.leadership-profiles.index') }}" class="btn-secondary">Batal</a>
        </div>
    </aside>
</div>
