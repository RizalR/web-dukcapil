@extends('layouts.admin')

@section('title', 'Profil Pegawai')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Sumber daya manusia</p>
            <h1 class="page-title mt-2">Profil pegawai</h1>
            <p class="mt-3">Kelola nama, jabatan, foto, dan urutan pegawai pada situs publik.</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn-primary">Tambah pegawai</a>
    </div>

    <div class="card mt-8 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-4">Foto</th>
                        <th class="px-5 py-4">Pegawai</th>
                        <th class="px-5 py-4">Urutan</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="px-5 py-4">
                                @if ($employee->photoUrl())
                                    <img class="size-16 rounded-xl bg-slate-100 object-contain" src="{{ $employee->photoUrl() }}" alt="Foto {{ $employee->name }}">
                                @else
                                    <div class="grid size-16 place-items-center rounded-xl bg-sky-soft font-black text-ocean" aria-hidden="true">{{ str($employee->name)->explode(' ')->take(2)->map(fn($word) => str($word)->substr(0,1))->join('') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-ink">{{ $employee->name }}</p>
                                <p class="mt-1 font-semibold text-ocean">{{ $employee->position }}</p>
                                @if ($employee->unit)
                                    <p class="mt-1 text-xs text-slate-500">{{ $employee->unit }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $employee->sort_order }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $employee->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $employee->is_published ? 'Tampil' : 'Disembunyikan' }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a class="font-bold text-ocean" href="{{ route('admin.employees.edit', $employee) }}">Edit</a>
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-bold text-rose-700" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center">Belum ada profil pegawai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $employees->links() }}</div>
@endsection
