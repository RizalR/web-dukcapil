@extends('layouts.admin')

@section('title', 'Kelola Berita')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Konten</p>
            <h1 class="page-title mt-2">Berita</h1>
        </div>
        <a href="{{ route('admin.news-articles.create') }}" class="btn-primary">Tambah berita</a>
    </div>

    <div class="card mt-8 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-4">Judul</th>
                        <th class="px-5 py-4">Penulis</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Diperbarui</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($articles as $article)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold text-ink">{{ $article->title }}</p>
                                @if ($article->is_featured)
                                    <span class="mt-1 inline-block text-xs font-bold text-amber-700">Berita utama</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $article->author->name }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $article->status->value === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $article->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $article->updated_at->diffForHumans() }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a class="font-bold text-ocean" href="{{ route('admin.news-articles.edit', $article) }}">Edit</a>

                                    @if (auth()->user()->isSuperAdmin())
                                        @if ($article->status->value === 'draft')
                                            <form action="{{ route('admin.news-articles.publish', $article) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <button class="font-bold text-emerald-700">Terbitkan</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.news-articles.unpublish', $article) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="font-bold text-amber-700">Tarik</button>
                                            </form>
                                        @endif
                                    @endif

                                    <form action="{{ route('admin.news-articles.destroy', $article) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-bold text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">Belum ada berita.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
