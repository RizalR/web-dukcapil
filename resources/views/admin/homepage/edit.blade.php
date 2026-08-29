@extends('layouts.admin')

@section('title', 'Tampilan Beranda')

@section('content')
    <div>
        <p class="eyebrow">Konten beranda</p>
        <h1 class="page-title mt-2">Background dan slider</h1>
        <p class="mt-3 max-w-3xl">Ganti background hero tanpa mengubah teksnya, lalu kelola gambar yang tampil pada section slider terpisah.</p>
    </div>

    <section class="card mt-8 overflow-hidden" aria-labelledby="background-heading">
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:p-8">
            <div>
                <p class="eyebrow">Background hero</p>
                <h2 id="background-heading" class="section-title mt-2">Gambar utama beranda</h2>
                <p class="mt-3 text-sm leading-6">Format JPG, PNG, atau WebP; maksimal 1 MB dan minimal 1200 × 500 piksel. Overlay gelap tetap digunakan agar teks mudah dibaca.</p>

                <form class="mt-6 grid gap-4" action="{{ route('admin.homepage.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="form-label" for="background_image">Pilih gambar background baru</label>
                        <input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:font-bold file:text-ocean" id="background_image" name="background_image" type="file" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <button class="btn-primary w-fit" type="submit">Perbarui background</button>
                </form>

                @if ($homepageSetting?->background_image_path)
                    <form class="mt-3" action="{{ route('admin.homepage.background.destroy') }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-bold text-rose-700" type="submit">Kembalikan ke dukcapil_.jpeg</button>
                    </form>
                @endif
            </div>

            <div>
                <p class="mb-2 text-sm font-bold text-ink">Pratinjau saat ini</p>
                <img class="aspect-[16/7] w-full rounded-2xl object-cover" src="{{ $backgroundPreviewUrl }}" alt="Pratinjau background halaman utama">
                <p class="mt-2 text-xs text-slate-500">{{ $homepageSetting?->background_image_path ? 'Background unggahan Super Admin' : 'Background bawaan dukcapil_.jpeg' }}</p>
            </div>
        </div>
    </section>

    <section class="mt-12" aria-labelledby="slider-heading">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="eyebrow">Section baru</p>
                <h2 id="slider-heading" class="section-title mt-2">Slider gambar</h2>
                <p class="mt-3">Urutan paling kecil ditampilkan lebih dahulu.</p>
            </div>
            <a href="{{ route('admin.hero-slides.create') }}" class="btn-primary">Tambah slide</a>
        </div>

        <div class="card mt-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-5 py-4">Gambar</th>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Urutan</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Jadwal</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($slides as $slide)
                            <tr>
                                <td class="px-5 py-4"><img class="h-16 w-28 rounded-lg object-cover" src="{{ $slide->imageUrl() }}" alt=""></td>
                                <td class="px-5 py-4"><p class="font-bold text-ink">{{ $slide->label }}</p><p class="mt-1 max-w-xs text-xs text-slate-500">{{ $slide->alt_text }}</p></td>
                                <td class="px-5 py-4">{{ $slide->sort_order }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $slide->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-4 text-xs text-slate-600"><p>Mulai: {{ $slide->starts_at?->translatedFormat('d M Y, H.i') ?? 'Langsung' }}</p><p class="mt-1">Selesai: {{ $slide->ends_at?->translatedFormat('d M Y, H.i') ?? 'Tanpa batas' }}</p></td>
                                <td class="px-5 py-4"><div class="flex justify-end gap-3"><a class="font-bold text-ocean" href="{{ route('admin.hero-slides.edit', $slide) }}">Edit</a><form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="post">@csrf @method('DELETE')<button class="font-bold text-rose-700" type="submit">Hapus</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center">Belum ada gambar slider.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $slides->links() }}</div>
    </section>
@endsection
