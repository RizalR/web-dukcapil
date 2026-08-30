@extends('layouts.admin')

@section('title', 'Profil Pimpinan')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Konten beranda</p>
            <h1 class="page-title mt-2">Profil pimpinan</h1>
            <p class="mt-3 max-w-3xl">Kelola Bupati, Wakil Bupati, dan Kepala Dinas yang ditampilkan pada beranda serta halaman profil dinas.</p>
        </div>
        <a href="{{ route('admin.leadership-profiles.create') }}" class="btn-primary">Tambah profil</a>
    </div>

    <div class="card mt-8 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-4">Foto</th>
                        <th class="px-5 py-4">Pimpinan</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($leadershipProfiles as $leadershipProfile)
                        <tr>
                            <td class="px-5 py-4">
                                <img class="aspect-[4/5] w-16 rounded-xl bg-slate-100 object-cover object-top" src="{{ $leadershipProfile->photoUrl() }}" alt="Foto {{ $leadershipProfile->name }}">
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-ink">{{ $leadershipProfile->name }}</p>
                                <p class="mt-1 font-semibold text-ocean">{{ $leadershipProfile->role->label() }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $leadershipProfile->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $leadershipProfile->is_published ? 'Tampil' : 'Disembunyikan' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a class="font-bold text-ocean" href="{{ route('admin.leadership-profiles.edit', $leadershipProfile) }}">Edit</a>
                                    <form action="{{ route('admin.leadership-profiles.destroy', $leadershipProfile) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-bold text-rose-700" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center">Belum ada profil pimpinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $leadershipProfiles->links() }}</div>
@endsection
